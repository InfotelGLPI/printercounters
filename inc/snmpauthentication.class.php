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
 * Class PluginPrintercountersSnmpauthentication
 *
 * This class allows to add and manage the SNMP authentications
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersSnmpauthentication extends CommonDropdown {

   // SNMP Version
   const SNMPV1 = 0;
   const SNMPV2 = 1;
   const SNMPV3 = 3;

   // Auth encryption
   const SHA = 1;
   const MD5 = 2;

   // Data encryption
   const DES    = 1;
   const AES128 = 2;
   const AES192 = 3;
   const AES256 = 4;

   static $rightname = 'plugin_printercounters';

   static function getTypeName($nb = 0) {
      return _n("SNMP authentication", "SNMP authentications", $nb, 'printercounters');
   }

   /**
    * Provides search options configuration. Do not rely directly
    * on this, @see CommonDBTM::searchOptions instead.
    *
    * @since 9.3
    *
    * This should be overloaded in Class
    *
    * @return array a *not indexed* array of search options
    *
    * @see https://glpi-developer-documentation.rtfd.io/en/master/devapi/search.html
    **/
   public function rawSearchOptions() {

      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '62',
         'table'              => $this->getTable(),
         'field'              => 'version',
         'name'               => __('Version'),
         'datatype'           => 'specific',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '43',
         'table'              => $this->getTable(),
         'field'              => 'community',
         'name'               => __('Community', 'printercounters'),
         'datatype'           => 'specific',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '44',
         'table'              => $this->getTable(),
         'field'              => 'authentication_encrypt',
         'name'               => __('Authentication encryption', 'printercounters'),
         'datatype'           => 'specific',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '45',
         'table'              => $this->getTable(),
         'field'              => 'data_encrypt',
         'name'               => __('Data encryption', 'printercounters'),
         'datatype'           => 'specific',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '46',
         'table'              => $this->getTable(),
         'field'              => 'user',
         'name'               => __('User'),
         'datatype'           => 'text',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '47',
         'table'              => $this->getTable(),
         'field'              => 'authentication_password',
         'name'               => __('Authentication password', 'printercounters'),
         'datatype'           => 'specific',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '48',
         'table'              => $this->getTable(),
         'field'              => 'data_password',
         'name'               => __('Data password', 'printercounters'),
         'datatype'           => 'specific',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '49',
         'table'              => $this->getTable(),
         'field'              => 'community_write',
         'name'               => __('Community write', 'printercounters'),
         'datatype'           => 'specific',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '50',
         'table'              => $this->getTable(),
         'field'              => 'is_default',
         'name'               => __('Is default', 'printercounters'),
         'datatype'           => 'bool',
         'massiveaction'      => true
      ];

      return $tab;
   }

   /**
   * Get additional fields in form
   *
   * @return array
   */
   function getAdditionalFields() {

      $tab = [
                   ['name'  => 'version',
                         'label' => __('Version'),
                         'type'  => 'specific',
                         'list'  => true],
                   ['name'  => 'community',
                         'label' => __('Community', 'printercounters'),
                         'type'  => 'text',
                         'list'  => true],
                   ['name'  => 'community_write',
                         'label' => __('Community write', 'printercounters'),
                         'type'  => 'text',
                         'list'  => true],
                   ['name'  => 'authentication_encrypt',
                         'label' => __('Authentication encryption', 'printercounters'),
                         'type'  => 'specific',
                         'list'  => true],
                   ['name'  => 'data_encrypt',
                         'label' => __('Data encryption', 'printercounters'),
                         'type'  => 'specific',
                         'list'  => true],
                   ['name'  => 'user',
                         'label' => __('User'),
                         'type'  => 'text',
                         'list'  => true],
                   ['name'  => 'authentication_password',
                         'label' => __('Authentication password', 'printercounters'),
                         'type'  => 'specific',
                         'list'  => true],
                   ['name'  => 'data_password',
                         'label' => __('Data password', 'printercounters'),
                         'type'  => 'specific',
                         'list'  => true],
                   ['name'  => 'is_default',
                         'label' => __('Is default', 'printercounters'),
                         'type'  => 'bool',
                         'list'  => true],
                   ];

      return $tab;
   }

   /**
   * Display specific fields
   *
   * @global type $CFG_GLPI
   * @param type $ID
   * @param type $field
   */
   function displaySpecificTypeField($ID, $field = [], array $options = []) {
      global $CFG_GLPI;

      $this->getFromDB($ID);

      $value = '';
      if (isset($this->fields[$field['name']])) {
         $value = $this->fields[$field['name']];
      }

      switch ($field['name']) {
         case 'version':
            self::dropdownVersion(['value' => $value]);
            break;
         case 'authentication_encrypt':
            self::dropdownAuthenticationEncryption(['value' => $value]);
            break;
         case 'data_encrypt':
            self::dropdownDataEncryption(['value' => $value]);
            break;
         case 'authentication_password':case 'data_password':
               echo "<input type='password' name='".$field['name']."' value='$value'>";
            break;
      }
   }

   /**
    * @since version 0.84
    *
    * @param $field
    * @param $values
    * @param $options   array
   **/
   static function getSpecificValueToDisplay($field, $values, array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'version' :
            return self::getVersion($values[$field]);
         case 'authentication_encrypt' :
            return self::getAuthenticationEncryption($values[$field]);
         case 'data_encrypt' :
            return self::getDataEncryption($values[$field]);
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }


   /**
    * @since version 0.84
    *
    * @param $field
    * @param $name            (default '')
    * @param $values          (default '')
    * @param $options   array
    *
    * @return string
   **/
   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;
      $options['value']   = $values[$field];
      switch ($field) {
         case 'version':
            return self::dropdownVersion($options);
         case 'authentication_encrypt':
            return self::dropdownAuthenticationEncryption($options);
         case 'data_encrypt':
            return self::dropdownDataEncryption($options);
         case 'authentication_password':case 'data_password':
            return "<input type='password' name='".$field."' value='$values[$field]'>";
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }

   /**
   * Form header
   */
   function displayHeader() {
      Html::header($this->getTypeName(), '', "tools", "pluginprintercountersmenu", "snmpauthentication");
   }

   /**
    * Actions done at the end of the getEmpty function
    *
    * @return nothing
   **/
   function post_getEmpty() {
      // Set session saved if exists
      $this->setSessionValues();
   }

   /**
   * Set session values in object
   *
   */
   function setSessionValues() {
      if (isset($_SESSION['plugin_printercounters']['snmpauthentication']) && !empty($_SESSION['plugin_printercounters']['snmpauthentication'])) {
         foreach ($_SESSION['plugin_printercounters']['snmpauthentication'] as $key => $val) {
            $this->fields[$key] = $val;
         }
      }
      unset($_SESSION['plugin_printercounters']['snmpauthentication']);
   }

   /**
    * Show the SNMP version dropdown
    *
    * @param array $options
    * @return type
    */
   static function dropdownVersion(array $options = []) {
      return Dropdown::showFromArray('version', self::getAllVersionArray(), $options);
   }

   /**
    * Function get the version
    *
    * @return an array
    */
   static function getVersion($value) {
      $data = self::getAllVersionArray();
      return $data[$value];
   }

   /**
    * Get the SNMP version list
    *
    * @return an array
    */
   static function getAllVersionArray() {

      // To be overridden by class
      $tab = [self::SNMPV1  => __('SNMP v1', 'printercounters'),
                   self::SNMPV2  => __('SNMP v2c', 'printercounters'),
                   self::SNMPV3  => __('SNMP v3', 'printercounters')];

      return $tab;
   }

   /**
    * Show the SNMP authentication encryption dropdown
    *
    * @return an array
    */
   static function dropdownAuthenticationEncryption(array $options = []) {
      return Dropdown::showFromArray('authentication_encrypt', self::getAllAuthenticationEncryptionArray(), $options);
   }

   /**
    * Function get the SNMP authentication encryption
    *
    * @param type $value
    * @return type
    */
   static function getAuthenticationEncryption($value) {
      if (!empty($value)) {
         $data = self::getAllAuthenticationEncryptionArray();
         return $data[$value];
      }
   }

   /**
    * Get the SNMP authentication encryption list
    *
    * @return an array
    */
   static function getAllAuthenticationEncryptionArray() {

      // To be overridden by class
      $tab = [0          => Dropdown::EMPTY_VALUE,
                   self::SHA  => __('SHA', 'printercounters'),
                   self::MD5  => __('MD5', 'printercounters')];

      return $tab;
   }

   /**
    * Show the SNMP data encryption dropdown
    *
    * @param array $options
    * @return type
    */
   static function dropdownDataEncryption(array $options = []) {
      return Dropdown::showFromArray('data_encrypt', self::getAllDataEncryptionArray(), $options);
   }

   /**
    * Function get the SNMP data encryption
    *
    * @param type $value
    * @return type
    */
   static function getDataEncryption($value) {
      if (!empty($value)) {
         $data = self::getAllDataEncryptionArray();
         return $data[$value];
      }
   }

   /**
    * Get the SNMP data encryption list
    *
    * @return type
    */
   static function getAllDataEncryptionArray() {

      // To be overridden by class
      $tab = [0             => Dropdown::EMPTY_VALUE,
                   self::DES     => __('DES', 'printercounters'),
                   self::AES128  => __('AES128', 'printercounters'),
                   self::AES192  => __('AES192', 'printercounters'),
                   self::AES256  => __('AES256', 'printercounters')];

      return $tab;
   }

   /**
   * Get authentification data for item
   *
   * @global type $DB
   * @param type $items_id
   * @param type $itemtype
   * @return type
   */
   function getItemAuthentication($items_id, $itemtype) {
      global $DB;

      $output = [];
      $dbu    = new DbUtils();

      if (!empty($items_id) && !empty($itemtype)) {
         $itemjoin = "PluginPrintercountersItem_Recordmodel";

         $query = "SELECT `".$dbu->getTableForItemType($itemjoin)."`.`items_id`,
                          `".$this->getTable()."`.`version`,
                          `".$this->getTable()."`.`community`,
                          `".$this->getTable()."`.`community_write`,
                          `".$this->getTable()."`.`authentication_encrypt`,
                          `".$this->getTable()."`.`data_encrypt`,
                          `".$this->getTable()."`.`user`,
                          `".$this->getTable()."`.`authentication_password`,
                          `".$this->getTable()."`.`data_password`
             FROM ".$this->getTable()."
             LEFT JOIN `".$dbu->getTableForItemType($itemjoin)."` 
                ON (`".$dbu->getTableForItemType($itemjoin)."`.`plugin_printercounters_snmpauthentications_id` = `".$this->getTable()."`.`id`)
             WHERE `".$dbu->getTableForItemType($itemjoin)."`.`items_id` IN ('".implode("','", $items_id)."')
             AND LOWER(`".$dbu->getTableForItemType($itemjoin)."`.`itemtype`)='".$itemtype."'";

         $result = $DB->query($query);
         if ($DB->numrows($result)) {
            while ($data = $DB->fetchAssoc($result)) {
               $output[$data['items_id']] = ['version'                 => $data['version'],
                                                  'community'               => !empty($data['community']) ? $data['community'] : '',
                                                  'community_write'         => !empty($data['community_write']) ? $data['community_write'] : '',
                                                  'authentication_encrypt'  => !empty($data['authentication_encrypt']) ? self::getAuthenticationEncryption($data['authentication_encrypt']) : '',
                                                  'data_encrypt'            => !empty($data['data_encrypt']) ? self::getDataEncryption($data['data_encrypt']) : '',
                                                  'user'                    => $data['user'],
                                                  'authentication_password' => $data['authentication_password'],
                                                  'data_password'           => $data['data_password']];
            }
         }
      }

      return $output;
   }

   /**
   * Actions done before add
   *
   * @param type $input
   * @return type
   */
   function prepareInputForAdd($input) {

      $this->setDefaultAuthentication($input);

      if (!$this->checkMandatoryFields($input)) {
         $_SESSION['plugin_printercounters']['snmpauthentication'] = $input;
         return false;
      }

      return $input;
   }

   /**
   * Actions done before update
   *
   * @param type $input
   * @return type
   */
   function prepareInputForUpdate($input) {

      $this->setDefaultAuthentication($input);

      if (!$this->checkMandatoryFields($input)) {
         return false;
      }

      return $input;
   }

   /**
   * Get default authenticiation
   *
   * @return id
   */
   function getDefaultAuthentication() {
      $defaultId = 0;

      $default = $this->find(['is_default' => 1]);
      if (!empty($default)) {
         $default   = reset($default);
         $defaultId = $default['id'];
      }

      return $defaultId;
   }

   /**
   * Set default authenticiation
   *
   * @param type $input
   * @return type
   */
   function setDefaultAuthentication($input) {

      if ($input['is_default']) {
         $condition = isset($input['id']) ? "AND `id` != '".$input['id']."'" : "";
         $default = $this->find("`is_default` = 1 $condition");
         if (!empty($default)) {
            foreach ($default as $authentication) {
               $snmpAuthentification = new self();
               $snmpAuthentification->getFromDB($authentication['id']);
               $snmpAuthentification->fields['is_default'] = 0;
               $snmpAuthentification->updateInDB(['is_default']);
            }
         }
      }
   }


   /**
   * checkMandatoryFields
   *
   * @param type $input
   * @return boolean
   */
   function checkMandatoryFields($input) {
      $msg     = [];
      $checkKo = false;

      $mandatory_fields = ['name'            => __('Name'),
                                'community'       => __('Community', 'printercounters'),
                                'community_write' => __('Community write', 'printercounters')];

      foreach ($input as $key => $value) {
         if (array_key_exists($key, $mandatory_fields)) {
            if (empty($value)) {
               $msg[] = $mandatory_fields[$key];
               $checkKo = true;
            }
         }
      }

      // SNMP V3
      if ($input['version'] == self::SNMPV3  && empty($input['authentication_encrypt'])
              && empty($input['authentication_password'])
              && empty($input['data_encrypt'])
              && empty($input['data_password'])) {

              $msg[] = __('Authentication encryption', 'printercounters');
              $msg[] = __('Data encryption', 'printercounters');
              $msg[] = __('User');
              $msg[] = __('Authentication password', 'printercounters');
              $msg[] = __('Data password', 'printercounters');

              $checkKo = true;
      }

      if ($checkKo) {
         Session::addMessageAfterRedirect(sprintf(__("Mandatory fields are not filled. Please correct: %s"), implode(', ', $msg)), true, ERROR);
         return false;
      }
      return true;
   }

   /**
    * Get the standard massive actions which are forbidden
    *
    * @return an array of massive actions
   **/
   function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'merge';

      return $forbidden;
   }

}
