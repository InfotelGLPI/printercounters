<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 printercounters plugin for GLPI
 Copyright (C) 2014-2022 by the printercounters Development Team.

 https://github.com/InfotelGLPI/printercounters
 -------------------------------------------------------------------------

 LICENSE

 This file is part of printercounters.

 printercounters is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 printercounters is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with printercounters. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginPrintercountersCommonSNMPObject
 *
 * Parent class that provides common function for the SNMP queries
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
abstract class PluginPrintercountersCommonSNMPObject {

   /**
    * @var item variables
    */
   var $ip;
   var $mac;
   var $itemtype;
   var $items_id;
   var $entities_id;
   var $locations_id;
   var $item_recordmodel;
   var $recordmodel;


   /**
    * @var object SNMP session
    */
   public $session;

   /**
    * The max number of microseconds for SNMP call
    * Default value is set to 0,1 second
    *
    * @var int microseconds
    */
   var $maxTimeout = 100000;

   /**
    * The max number of retries for SNMP call
    * Default value is set to 5 tries
    *
    * @var int
    */
   var $maxRetries = 5;

   const ERROR_BOOL    = 3;
   const ERROR_NUMBER  = 2;
   const ERROR_MESSAGE = 1;

   /**
    * Contructor
    *
    * @param string $items_id item id
    * @param int $itemtype itemtype
    * @param string $ip IP address
    * @param string $mac MAC address
    *
    * @param array $record_config :
    *                               - nb_retries
    *                               - max_timeout
    *                               - item recordmodel : id of the link between item and recordmodel
    *                               - recordmodel : id of the recordmodel
    *                               - entities_id : entity of the printer
    *
    * @param array $snmp_auth :
    *                           - version,
    *                           - community,
    *                           - authentication_encrypt,
    *                           - authentication_password,
    *                           - data_encrypt,
    *                           - data_password
    *
    * @throws Exception if PHP SNMP extension is not loaded, if no record configuration, if no SNMP authentication
    */
   public function __construct($items_id = null, $itemtype = null, $ip = null, $mac = null, $record_config = [], $snmp_auth = []) {

      if (!extension_loaded('snmp')) {
         throw new PluginPrintercountersException(__('SNMP extension is not loaded', 'printercounters'), 0, null, $this->items_id, $this->itemtype);
      }

      $this->setItems_id($items_id);
      $this->setItemtype($itemtype);
      $this->setIPAddress($ip);
      $this->setMac($mac);

      // Set record config
      $this->setRecordConfig($record_config);

      // Set SNMP authentication
      $this->setSNMPAuth($snmp_auth);

      set_error_handler([$this, 'handleSNMPError']);
   }

   /**
    * Function set SNMP Config
    *
    * @param type $record_config
    */
   public function setRecordConfig($record_config) {

      if (!empty($record_config)) {
         if (isset($record_config['nb_retries'])) {
            $this->setMaxRetries($record_config['nb_retries']);
         }
         if (isset($record_config['max_timeout'])) {
            $this->setMaxTimeout($record_config['max_timeout']);
         }
         if (isset($record_config['plugin_items_recordmodels_id'])) {
            $this->setItem_Recordmodel($record_config['plugin_items_recordmodels_id']);
         }
         if (isset($record_config['plugin_recordmodels_id'])) {
            $this->setRecordmodel($record_config['plugin_recordmodels_id']);
         }
         if (isset($record_config['entities_id'])) {
            $this->setEntity($record_config['entities_id']);
         }
         if (isset($record_config['locations_id'])) {
            $this->setLocation($record_config['locations_id']);
         }

      } else {
         throw new PluginPrintercountersException(__('No SNMP configuration', 'printercounters'), 0, null, $this->items_id, $this->itemtype);
      }
   }

   /**
    * Function set SNMP Auth
    *
    * @param type $snmp_auth
    */
   public function setSNMPAuth($snmp_auth) {

      if (!empty($snmp_auth)) {
         switch ($snmp_auth['version']) {
            case PluginPrintercountersSnmpauthentication::SNMPV3:
               $this->session = new SNMP($snmp_auth['version'],
                                         $this->ip,
                                         $snmp_auth['community'],
                                         $this->maxTimeout,
                                         $this->maxRetries);

               if (!empty($snmp_auth['authentication_encrypt'])
                       && !empty($snmp_auth['authentication_password'])
                       && !empty($snmp_auth['data_encrypt'])
                       && !empty($snmp_auth['data_password'])) {
                  $this->session->setSecurity('authPriv',
                                              $snmp_auth['authentication_encrypt'],
                                              $snmp_auth['authentication_password'],
                                              $snmp_auth['data_encrypt'],
                                              $snmp_auth['data_password'], '', '');
               } else {
                  throw new PluginPrintercountersException(__('Bad SNMP V3 parameters', 'printercounters'), 0, null, $this->items_id, $this->itemtype);
               }
               break;

            default :
               $this->session = new SNMP($snmp_auth['version'],
                                         $this->ip,
                                         $snmp_auth['community'],
                                         $this->maxTimeout,
                                         $this->maxRetries);
               break;
         }

      } else {
         throw new PluginPrintercountersException(__('No SNMP authentication', 'printercounters'), 0, null, $this->items_id, $this->itemtype);
      }
   }

   /**
    * Function sets item recordmodel
    *
    * @param string $item_recordmodel item_recordmodel
    * @throws Exception if passed item_recordmodel is not in numeric format
    */
   public function setItem_Recordmodel($item_recordmodel) {

      if (!is_numeric($item_recordmodel)) {
         throw new PluginPrintercountersException(__('Passed item recordmodel is not correct', 'printercounters'), 0, null, $this->items_id, $this->itemtype);
      }

      $this->item_recordmodel = $item_recordmodel;
   }

   /**
    * Function sets recordmodel
    *
    * @param string $recordmodel recordmodel
    * @throws Exception if passed $recordmodel is not in numeric format
    */
   public function setRecordmodel($recordmodel) {

      if (!is_numeric($recordmodel)) {
         throw new PluginPrintercountersException(__('Passed recordmodel is not correct', 'printercounters'), 0, null, $this->items_id, $this->itemtype);
      }

      $this->recordmodel = $recordmodel;
   }

   /**
    * Function sets Itemtype
    *
    * @param string $itemtype Itemtype
    * @throws Exception if passed Itemtype is not in string format
    */
   public function setItemtype($itemtype) {

      if (!is_string($itemtype)) {
         throw new PluginPrintercountersException(__('Passed Itemtype is not correct', 'printercounters'));
      }

      $this->itemtype = $itemtype;
   }

   /**
    * Function gets itemtype
    *
    * @return string
    */
   public function getItemtype() {

      return $this->itemtype;
   }

   /**
    * Function sets Items id
    *
    * @param string $items_id Items id
    * @throws Exception if passed Items id is not in string format
    */
   public function setItems_id($items_id) {

      if (!is_numeric($items_id)) {
         throw new PluginPrintercountersException(__('Passed Items id is not correct', 'printercounters'));
      }

      $this->items_id = $items_id;
   }

   /**
    * Function gets items id
    *
    * @return string
    */
   public function getItems_id() {

      return $this->items_id;
   }

   /**
    * Function sets IP address
    *
    * @param string $ip IP address
    * @throws Exception if passed IP address is not in string format
    */
   public function setIPAddress($ip) {

      if (!is_string($ip) || empty($ip) || $ip == 'NULL') {
         throw new PluginPrintercountersException(__('Passed IP address is not correct', 'printercounters'), 0, null, $this->items_id, $this->itemtype);
      }

      $this->ip = $ip;
   }

   /**
    * Function gets IP address
    *
    * @return string
    */
   public function getIPAddress() {

      return $this->ip;
   }

   /**
    * Function sets MAC address
    *
    * @param string $mac MAC address
    */
   public function setMac($mac) {

      $this->mac = $mac;
   }

   /**
    * Function gets MAC address
    *
    * @return string
    */
   public function getMac() {

      return $this->mac;
   }

   /**
    * Function sets entity
    *
    * @param string $entities_id entity
    */
   public function setEntity($entities_id) {

      $this->entities_id = $entities_id;
   }

   /**
    * Function gets entity
    *
    * @return string
    */
   public function getEntity() {
      return $this->entities_id;
   }

   /**
    * Function sets location
    *
    * @param string $locations_id entity
    */
   public function setLocation($locations_id) {

      $this->locations_id = $locations_id;
   }

   /**
    * Function gets location
    *
    * @return string
    */
   public function getLocation() {
      return $this->locations_id;
   }

   /**
    * Function sets maximum timeout in microseconds for SNMP calls
    *
    * @param int $microseconds
    * @throws Exception if passed timeout in microseconds is not in integer format
    */
   public function setMaxTimeout($microseconds) {

      if (!is_numeric($microseconds)) {
         throw new PluginPrintercountersException(__('Passed timeout is not int', 'printercounters'), 0, null, $this->items_id, $this->itemtype);
      }

      $this->maxTimeout = $microseconds;
   }

   /**
    * Function sets maximum timeout in microseconds for SNMP calls
    *
    * @param int $microseconds
    * @throws Exception if passed timeout in microseconds is not in integer format
    */
   public function setMaxRetries($retries) {

      if (!is_numeric($retries)) {
         throw new PluginPrintercountersException(__('Passed retry is not int', 'printercounters'), 0, null, $this->items_id, $this->itemtype);
      }

      $this->maxRetries = $retries;
   }

   /**
    * Function gets maxTimeout
    *
    * @return int Microseconds
    */
   public function getMaxTimeout() {
      return $this->maxTimeout;
   }

   /**
    * Function gets result of SNMP object id,
    * or returns false if call failed
    *
    * @param string $snmpObjectId
    * @param int $error_type : 1 = error message, 2 = error code, 3 = error bool
    * @return int|string|boolean
    * @throws Exception if IP address is not set
    * @throws Exception if $snmpObjectId is not in string format
    */
   public function set($snmpObjectId, $type = 's', $value = null, $error_type = 1) {
      $error_bool    = true;
      $error_message = '';
      $error_number  = 0;
      /**
       * Check if IP address is set
       */
      if ($this->ip === null) {
         $error_bool    = false;
         $error_message = __('IP address was not set', 'printercounters');
      }

      /**
       * Check if SNMP object ID is in string format
       */
      if (!is_string($snmpObjectId)) {
         $error_bool    = false;
         $error_message = __('SNMP Object ID is not string', 'printercounters');
      }

      try {
         $result = $this->session->set($snmpObjectId, $type, PluginPrintercountersToolbox::replaceAccents($value));

         if (!is_array($result)) {
            return trim(str_replace(['"', 'Hex-STRING: ', 'STRING: ', 'INTEGER: ', 'Counter32: '], '', $result));
         } else {
            foreach ($result as &$value) {
               $value = trim(str_replace(['"', 'Hex-STRING: ', 'STRING: ', 'INTEGER: ', 'Counter32: '], '', $value));
            }
            return $result;
         }

      } catch (PluginPrintercountersException $e) {
         $error_bool = false;
         $error_message = $e->getMessage();
      }

      // Display error
      if (!$error_bool) {
         switch ($error_type) {
            case self::ERROR_NUMBER : return $error_number;
            case self::ERROR_BOOL   : return $error_bool;
            default : throw new PluginPrintercountersException($error_message, 0, null,
                                                               $this->items_id, $this->itemtype);
         }
      }
   }

   /**
    * Function gets result of SNMP object id,
    * or returns false if call failed
    *
    * @param string $snmpObjectId
    * @param int $error_type : 1 = error message, 2 = error code, 3 = error bool
    * @return int|string|boolean
    * @throws Exception if IP address is not set
    * @throws Exception if $snmpObjectId is not in string format
    */
   public function get($snmpObjectId, $error_type = 1) {
      $error_bool    = true;
      $error_message = '';
      $error_number  = 0;
      /**
       * Check if IP address is set
       */
      if ($this->ip === null) {
         $error_bool    = false;
         $error_message = __('IP address was not set', 'printercounters');
      }

      /**
       * Check if SNMP object ID is in string format
       */
      if (!is_string($snmpObjectId)) {
         $error_bool    = false;
         $error_message = __('SNMP Object ID is not string', 'printercounters');
      }

      try {
         $result = $this->session->get($snmpObjectId);
         if (!is_array($result)) {
            return trim(str_replace(['"', 'Hex-STRING: ', 'STRING: ', 'INTEGER: ', 'Counter32: '], '', $result));

         } else {
            foreach ($result as &$value) {
               $value = trim(str_replace(['"', 'Hex-STRING: ', 'STRING: ', 'INTEGER: ', 'Counter32: '], '', $value));
            }
            return $result;
         }

      } catch (PluginPrintercountersException $e) {
         $error_bool    = false;
         $error_message = $e->getMessage();
      }

      // Display error
      if (!$error_bool) {
         switch ($error_type) {
            case self::ERROR_NUMBER  : return $error_number;
            case self::ERROR_BOOL    : return $error_bool;
            default : throw new PluginPrintercountersException($error_message, 0, null, $this->items_id, $this->itemtype);
         }
      }
   }

   /**
    * Function walks through SNMP object id and returns result in array,
    * or returns false of call failed
    *
    * @param string $snmpObjectId
    * @param int $error_type : 1 = error message, 2 = error code, 3 = error bool
    * @return array
    * @throws Exception if IP address is not set
    * @throws Exception if $snmpObjectId is not in string format
    */
   public function walk($snmpObjectId, $error_type = 1) {
      $error_bool    = true;
      $error_message = '';
      $error_number  = 0;
      /**
       * Check if IP address is set
       */
      if ($this->ip === null) {
         $error_bool    = false;
         $error_message = __('IP address was not set', 'printercounters');
      }

      /**
       * Check if SNMP object ID is in string format
       */
      if (!is_string($snmpObjectId)) {
         $error_bool    = false;
         $error_message = __('SNMP Object ID is not string', 'printercounters');
      }

      try {
         $result = $this->session->walk($snmpObjectId, true);
         foreach ($result as &$value) {
            $value = trim(str_replace(['"', 'Hex-STRING: ', 'STRING: ', 'INTEGER: ', 'Counter32: '], '', $value));
         }
         return $result;

      } catch (PluginPrintercountersException $e) {
         $error_bool    = false;
         $error_message = $e->getMessage();
      }

      // Display error
      if (!$error_bool) {
         switch ($error_type) {
            case self::ERROR_NUMBER  : return $error_number;
            case self::ERROR_BOOL    : return $error_bool;
            default : throw new PluginPrintercountersException($error_message, 0, null, $this->items_id, $this->itemtype);
         }
      }
   }

   /**
    * Function gets result of SNMP object id with deleted quotation marks,
    * or returns false if call failed
    *
    * @param string $snmpObjectId
    * @return string|boolean
    */
   public function getSNMPString($snmpObjectId) {
      $result = $this->get($snmpObjectId);

      return ($result !== false) ? str_replace(['"', 'STRING: '], '', $result) : false;
   }

   /**
    * Function returns result of SNMP last error
    *
    * @return string
    */
   public function getErrorMessage() {
      return $this->session->getError();
   }

   /**
    * Function returns number of SNMP last error
    *
    * @return int
    */
   public function getErrorNumber() {
      return $this->session->getErrno();
   }

   /**
    * Function handles SNMP errors
    *
    * @return int
    */
   public function handleSNMPError($errno, $errstr, $errfile, $errline) {
      if (!(error_reporting() & $errno)) {
         // This error code is not included in error_reporting
         return;
      }

      throw new PluginPrintercountersException($errstr, 0, null, $this->items_id, $this->itemtype);

      /* Don't execute PHP internal error handler */
      return true;
   }

   /**
    * Function closes SNMP session
    *
    * @return int
    */
   public function closeSNMPSession() {
      $this->session->close();
   }

}
