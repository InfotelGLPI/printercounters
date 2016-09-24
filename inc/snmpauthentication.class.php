<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 printercounters plugin for GLPI
 Copyright (C) 2014-2016 by the printercounters Development Team.

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

   static function getTypeName($nb=0) {
      return _n("SNMP authentication", "SNMP authentications", $nb, 'printercounters');
   }
   
  /** 
   * Get search options
   * 
   * @return array
   */
   function getSearchOptions() {
      $tab = parent::getSearchOptions();

      $tab[62]['table']          = $this->getTable();
      $tab[62]['field']          = 'version';
      $tab[62]['name']           = __('Version');
      $tab[62]['datatype']       = 'specific';
      $tab[62]['massiveaction']  = true;
      
      $tab[43]['table']          = $this->getTable();
      $tab[43]['field']          = 'community';
      $tab[43]['name']           = __('Community', 'printercounters');
      $tab[43]['datatype']       = 'specific';
      $tab[43]['massiveaction']  = true;
      
      $tab[44]['table']          = $this->getTable();
      $tab[44]['field']          = 'authentication_encrypt';
      $tab[44]['name']           = __('Authentication encryption', 'printercounters');
      $tab[44]['datatype']       = 'specific';
      $tab[44]['massiveaction']  = true;
      
      $tab[45]['table']          = $this->getTable();
      $tab[45]['field']          = 'data_encrypt';
      $tab[45]['name']           = __('Data encryption', 'printercounters');
      $tab[45]['datatype']       = 'specific';
      $tab[45]['massiveaction']  = true;
      
      $tab[46]['table']          = $this->getTable();
      $tab[46]['field']          = 'user';
      $tab[46]['name']           = __('User');
      $tab[46]['datatype']       = 'text';
      $tab[46]['massiveaction']  = true;
      
      $tab[47]['table']          = $this->getTable();
      $tab[47]['field']          = 'authentication_password';
      $tab[47]['name']           = __('Authentication password', 'printercounters');
      $tab[47]['datatype']       = 'specific';
      $tab[47]['massiveaction']  = true;
      
      $tab[48]['table']          = $this->getTable();
      $tab[48]['field']          = 'data_password';
      $tab[48]['name']           = __('Data password', 'printercounters');
      $tab[48]['datatype']       = 'specific';
      $tab[48]['massiveaction']  = true;
      
      $tab[49]['table']          = $this->getTable();
      $tab[49]['field']          = 'community_write';
      $tab[49]['name']           = __('Community write', 'printercounters');
      $tab[49]['datatype']       = 'specific';
      $tab[49]['massiveaction']  = true;

      $tab[50]['table']          = $this->getTable();
      $tab[50]['field']          = 'is_default';
      $tab[50]['name']           = __('Is default', 'printercounters');
      $tab[50]['datatype']       = 'bool';
      $tab[50]['massiveaction']  = true;
      
      return $tab;
   }
   
  /** 
   * Get additional fields in form
   * 
   * @return array
   */
   function getAdditionalFields() {

      $tab = array(
                   array('name'  => 'version',
                         'label' => __('Version'),
                         'type'  => 'specific',
                         'list'  => true),
                   array('name'  => 'community',
                         'label' => __('Community' , 'printercounters'),
                         'type'  => 'text',
                         'list'  => true),
                   array('name'  => 'community_write',
                         'label' => __('Community write' , 'printercounters'),
                         'type'  => 'text',
                         'list'  => true),
                   array('name'  => 'authentication_encrypt',
                         'label' => __('Authentication encryption', 'printercounters'),
                         'type'  => 'specific',
                         'list'  => true),
                   array('name'  => 'data_encrypt',
                         'label' => __('Data encryption', 'printercounters'),
                         'type'  => 'specific',
                         'list'  => true),
                   array('name'  => 'user',
                         'label' => __('User'),
                         'type'  => 'text',
                         'list'  => true),
                   array('name'  => 'authentication_password',
                         'label' => __('Authentication password', 'printercounters'),
                         'type'  => 'specific',
                         'list'  => true),
                   array('name'  => 'data_password',
                         'label' => __('Data password', 'printercounters'),
                         'type'  => 'specific',
                         'list'  => true),
                   array('name'  => 'is_default',
                         'label' => __('Is default', 'printercounters'),
                         'type'  => 'bool',
                         'list'  => true),
                   );

      return $tab;
   }
   
  /** 
   * Display specific fields
   * 
   * @global type $CFG_GLPI
   * @param type $ID
   * @param type $field
   */
   function displaySpecificTypeField($ID, $field=array()) {
      global $CFG_GLPI;

      $this->getFromDB($ID);
      
      $value = '';
      if(isset($this->fields[$field['name']])){
         $value = $this->fields[$field['name']];
      }
      
      switch($field['name']){        
         case 'version':
            self::dropdownVersion(array('value' => $value));
            break;
         case 'authentication_encrypt':
            self::dropdownAuthenticationEncryption(array('value' => $value));
            break;
         case 'data_encrypt':
            self::dropdownDataEncryption(array('value' => $value));
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
   static function getSpecificValueToDisplay($field, $values, array $options=array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
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
   static function getSpecificValueToSelect($field, $name='', $values='', array $options=array()) {
      if (!is_array($values)) {
         $values = array($field => $values);
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
   function setSessionValues(){
      if(isset($_SESSION['plugin_printercounters']['snmpauthentication']) && !empty($_SESSION['plugin_printercounters']['snmpauthentication'])){
         foreach($_SESSION['plugin_printercounters']['snmpauthentication'] as $key => $val){
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
    static function dropdownVersion(array $options=array()) {
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
      $tab = array(self::SNMPV1  => __('SNMP v1', 'printercounters'),
                   self::SNMPV2  => __('SNMP v2c', 'printercounters'),
                   self::SNMPV3  => __('SNMP v3', 'printercounters'));

      return $tab;
   }
    
   /**
    * Show the SNMP authentication encryption dropdown
    *
    * @return an array
    */
    static function dropdownAuthenticationEncryption(array $options=array()) {
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
      $tab = array(0          => Dropdown::EMPTY_VALUE,
                   self::SHA  => __('SHA', 'printercounters'), 
                   self::MD5  => __('MD5', 'printercounters'));

      return $tab;
   }
   
   /**
    * Show the SNMP data encryption dropdown
    * 
    * @param array $options
    * @return type
    */
    static function dropdownDataEncryption(array $options=array()) {
       return Dropdown::showFromArray('data_encrypt', self::getAllDataEncryptionArray(), $options);
    }
    
   /**
    * Function get the SNMP data encryption
    * 
    * @param type $value
    * @return type
    */
    static function getDataEncryption($value) {
       if(!empty($value)){
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
      $tab = array(0             => Dropdown::EMPTY_VALUE,
                   self::DES     => __('DES', 'printercounters'), 
                   self::AES128  => __('AES128', 'printercounters'),
                   self::AES192  => __('AES192', 'printercounters'),
                   self::AES256  => __('AES256', 'printercounters'));

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
   function getItemAuthentication($items_id, $itemtype){
      global $DB;
      
      $output = array();
         
      if(!empty($items_id) && !empty($itemtype)){
         $itemjoin = "PluginPrintercountersItem_Recordmodel";


         $query = "SELECT `".getTableForItemType($itemjoin)."`.`items_id`,
                          `".$this->getTable()."`.`version`,
                          `".$this->getTable()."`.`community`,
                          `".$this->getTable()."`.`community_write`,
                          `".$this->getTable()."`.`authentication_encrypt`,
                          `".$this->getTable()."`.`data_encrypt`,
                          `".$this->getTable()."`.`user`,
                          `".$this->getTable()."`.`authentication_password`,
                          `".$this->getTable()."`.`data_password`
             FROM ".$this->getTable()."
             LEFT JOIN `".getTableForItemType($itemjoin)."` 
                ON (`".getTableForItemType($itemjoin)."`.`plugin_printercounters_snmpauthentications_id` = `".$this->getTable()."`.`id`)
             WHERE `".getTableForItemType($itemjoin)."`.`items_id` IN ('".implode("','", $items_id)."')
             AND LOWER(`".getTableForItemType($itemjoin)."`.`itemtype`)='".$itemtype."'";

         $result = $DB->query($query);
         if ($DB->numrows($result)) {
            while ($data = $DB->fetch_assoc($result)) {
               $output[$data['items_id']] = array('version'                 => $data['version'], 
                                                  'community'               => !empty($data['community']) ? $data['community'] : '', 
                                                  'community_write'         => !empty($data['community_write']) ? $data['community_write'] : '', 
                                                  'authentication_encrypt'  => !empty($data['authentication_encrypt']) ? self::getAuthenticationEncryption($data['authentication_encrypt']) : '', 
                                                  'data_encrypt'            => !empty($data['data_encrypt']) ? self::getDataEncryption($data['data_encrypt']) : '', 
                                                  'user'                    => $data['user'], 
                                                  'authentication_password' => $data['authentication_password'], 
                                                  'data_password'           => $data['data_password']);
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
      
      if(!$this->checkMandatoryFields($input)){
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
      
      if(!$this->checkMandatoryFields($input)){
         return false;
      }

      return $input;
   }
   
  /** 
   * Get default authenticiation
   *
   * @return id
   */
   function getDefaultAuthentication(){
      $defaultId = 0;
      
      $default = $this->find("`is_default` = 1");
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
         $default = $this->find("`is_default` = 1 AND `id` != '".$input['id']."'");
         if (!empty($default)) {
            foreach ($default as $authentication) {
               $snmpAuthentification = new self();
               $snmpAuthentification->getFromDB($authentication['id']);
               $snmpAuthentification->fields['is_default'] = 0;
               $snmpAuthentification->updateInDB(array('is_default'));
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
   function checkMandatoryFields($input){
      $msg     = array();
      $checkKo = false;

      $mandatory_fields = array('name'            => __('Name'),
                                'community'       => __('Community' , 'printercounters'),
                                'community_write' => __('Community write' , 'printercounters'));

      foreach($input as $key => $value){
         if (array_key_exists($key, $mandatory_fields)) {
            if (empty($value)) {
               $msg[] = $mandatory_fields[$key];
               $checkKo = true;
            }
         }
      }
      
      // SNMP V3
      if($input['version'] == self::SNMPV3  && empty($input['authentication_encrypt']) 
              && empty($input['authentication_password']) 
              && empty($input['data_encrypt']) 
              && empty($input['data_password'])){
         
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