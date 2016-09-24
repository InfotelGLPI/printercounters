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
 * Class PluginPrintercountersRecordmodel
 * 
 * This class allows to manage the record models
 * 
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersRecordmodel extends CommonDropdown {
   
   protected $itemtype;
   protected $items_id;
   
   static $rightname = 'plugin_printercounters';
   
   /**
    * Constructor
    * 
    * @param type $itemtype
    * @param type $items_id
    */
   public function __construct($itemtype = 'printer', $items_id=0) {
      $this->setItemtype($itemtype);
      $this->setItems_id($items_id);
      
      parent::__construct();
   }
   
   /**
    * Function sets itemtype id
    *
    * @param string $itemtype 
    * @throws Exception 
    */
   public function setItemtype($itemtype) {

      if (empty($itemtype)) {
         throw new PluginPrintercountersException(__('Invalid itemtype', 'printercounters'));
      }

      $this->itemtype = $itemtype;
   }
   
   /**
    * Function sets items id
    *
    * @param string $items_id
    */
   public function setItems_id($items_id) {

      $this->items_id = $items_id;
   }

   static function getTypeName($nb=0) {
      return _n("Record model", "Record models", $nb, 'printercounters');
   }
      
  /** 
   * Get search options
   * 
   * @return array
   */
   function getSearchOptions() {
      
      $tab[1]['table']             = $this->getTable();
      $tab[1]['field']             = 'name';
      $tab[1]['name']              = __('Name');
      $tab[1]['datatype']          = 'itemlink';
      $tab[1]['massiveaction']     = false;

      $tab[2]['table']             = $this->getTable();
      $tab[2]['field']             = 'id';
      $tab[2]['name']              = __('ID');
      $tab[2]['massiveaction']     = false;
      $tab[2]['datatype']          = 'number';
      
      $tab[3]['table']             = 'glpi_entities';
      $tab[3]['field']             = 'name';
      $tab[3]['name']              = __('Entity');
      $tab[3]['massiveaction']     = true;
      $tab[3]['datatype']          = 'dropdown';

      $tab[12]['table']          = $this->getTable();
      $tab[12]['field']          = 'mac_address_conformity';
      $tab[12]['name']           = __('MAC Address Conformity', 'printercounters');
      $tab[12]['datatype']       = 'bool';
      $tab[12]['massiveaction']  = true;
      
      $tab[13]['table']          = $this->getTable();
      $tab[13]['field']          = 'sysdescr_conformity';
      $tab[13]['name']           = __('Sysdescr Conformity', 'printercounters');
      $tab[13]['datatype']       = 'bool';
      $tab[13]['massiveaction']  = true;
      
      $tab[14]['table']          = $this->getTable();
      $tab[14]['field']          = 'serial_conformity';
      $tab[14]['name']           = __('Serial Conformity', 'printercounters');
      $tab[14]['datatype']       = 'bool';
      $tab[14]['massiveaction']  = true;
      
      $tab[15]['table']          = getTableForItemType($this->itemtype);
      $tab[15]['field']          = 'name';
      $tab[15]['forcegroupby']   = true;
      $tab[15]['name']           = __('Linked items', 'printercounters');
      $tab[15]['massiveaction']  = false;
      $tab[15]['datatype']       = 'itemlink';
      $tab[15]['linkfield']      = 'items_id';
      $tab[15]['joinparams']     = array('condition' => " AND `glpi_plugin_printercounters_items_recordmodels`.`itemtype` = '".$this->itemtype."'",
                                          'beforejoin'
                                          => array('table'      => 'glpi_plugin_printercounters_items_recordmodels',
                                                   'joinparams' => array('jointype'   => 'child')
                                              )
                                        );
      
      $tab[16]['table']         = 'glpi_plugin_printercounters_countertypes';
      $tab[16]['field']         = 'name';
      $tab[16]['name']          = __('Counter type', 'printercounters');
      $tab[16]['forcegroupby']  = true;
      $tab[16]['massiveaction'] = false;
      $tab[16]['joinparams']     = array(
                                    'beforejoin'
                                    => array('table'      => 'glpi_plugin_printercounters_countertypes_recordmodels',
                                             'joinparams' => array('jointype'   => 'child')
                                        )
                                  );
      
      $tab[17]['table']         = 'glpi_plugin_printercounters_countertypes_recordmodels';
      $tab[17]['field']         = 'oid';
      $tab[17]['name']          = __('OID', 'printercounters');
      $tab[17]['forcegroupby']  = true;
      $tab[17]['massiveaction'] = false;
      $tab[17]['joinparams']    = array('jointype'   => 'child');
      
      $tab[18]['table']         = 'glpi_plugin_printercounters_countertypes_recordmodels';
      $tab[18]['field']         = 'oid_type';
      $tab[18]['name']          = __('OID type', 'printercounters');
      $tab[18]['forcegroupby']  = true;
      $tab[18]['massiveaction'] = false;
      $tab[18]['datatype']       = 'specific';
      $tab[18]['joinparams']    = array('jointype'   => 'child');
      
      $tab[19]['table']          = $this->getTable();
      $tab[19]['field']          = 'enable_toner_level';
      $tab[19]['name']           = __('Enable toner level', 'printercounters');
      $tab[19]['datatype']       = 'bool';
      $tab[19]['massiveaction']  = true;

      
      $tab[20]['table']          = $this->getTable();
      $tab[20]['field']          = 'enable_printer_info';
      $tab[20]['name']           = __('Enable printer informations', 'printercounters');
      $tab[20]['datatype']       = 'bool';
      $tab[20]['massiveaction']  = true;
      
      return $tab;
   }
   
  /** 
   * Get additional fields in form
   * 
   * @return array
   */
   function getAdditionalFields() {

      $tab = array(
                   array('name'  => 'mac_address_conformity',
                         'label' => __('MAC Address Conformity', 'printercounters'),
                         'type'  => 'bool',
                         'list'  => true),
                   array('name'  => 'sysdescr_conformity',
                         'label' => __('Sysdescr Conformity', 'printercounters'),
                         'type'  => 'bool',
                         'list'  => true),
                   array('name'  => 'serial_conformity',
                         'label' => __('Serial Conformity', 'printercounters'),
                         'type'  => 'bool',
                         'list'  => true),
                   array('name'  => 'enable_toner_level',
                         'label' => __('Enable toner level', 'printercounters'),
                         'type'  => 'bool',
                         'list'  => true),
                   array('name'  => 'enable_printer_info',
                         'label' => __('Enable printer informations', 'printercounters'),
                         'type'  => 'bool',
                         'list'  => true),
                   );

      return $tab;
   }
      
  /** 
   * Form header
   */
   function displayHeader() {
      Html::header($this->getTypeName(), '', "tools", "pluginprintercountersmenu", "recordmodel");
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
   * @return type
   */
   function setSessionValues(){
      if(isset($_SESSION['plugin_printercounters']['recordmodel']) && !empty($_SESSION['plugin_printercounters']['recordmodel'])){
         foreach($_SESSION['plugin_printercounters']['recordmodel'] as $key => $val){
            $this->fields[$key] = $val;
         }
      }
      unset($_SESSION['plugin_printercounters']['recordmodel']);
   }
   
   /**
    * get conformtity configuration
    *
    * 
    * @global type $DB
    * @param array $items_id
    * @param int $itemtype
    * @return array
    */
   public function getRecordModelConfig(array $items_id, $itemtype){
      global $DB;
      
      $output = array();
      
      // Get conformity configuration
      $itemjoin  = getTableForItemType("PluginPrintercountersItem_Recordmodel");
      $itemjoin2 = getTableForItemType("PluginPrintercountersCountertype_Recordmodel");
      $itemjoin3 = getTableForItemType("PluginPrintercountersSysdescr");
      $itemjoin4 = getTableForItemType($itemtype);

      $query = "SELECT `".$this->getTable()."`.`mac_address_conformity`,
                       `".$this->getTable()."`.`sysdescr_conformity`,
                       `".$this->getTable()."`.`serial_conformity`,
                       `".$this->getTable()."`.`enable_toner_level`,
                       `".$this->getTable()."`.`enable_printer_info`,
                       `".$itemjoin3."`.`sysdescr`,
                       `".$itemjoin2."`.`oid`,
                       `".$itemjoin2."`.`oid_type`,
                       `".$itemjoin4."`.`serial` as serial,
                       `".$itemjoin4."`.`id` as items_id
          FROM ".$this->getTable()."
          LEFT JOIN  `".$itemjoin."`
             ON (`".$itemjoin."`.`plugin_printercounters_recordmodels_id` = `".$this->getTable()."`.`id`)
          LEFT JOIN  `".$itemjoin4."`
             ON (`".$itemjoin."`.`items_id` = `".$itemjoin4."`.`id`)
          LEFT JOIN  `".$itemjoin2."`
             ON (`".$itemjoin2."`.`plugin_printercounters_recordmodels_id` = `".$this->getTable()."`.`id` 
                AND (`".$itemjoin2."`.`oid_type` = '".PluginPrintercountersCountertype_Recordmodel::SERIAL."' 
                OR `".$itemjoin2."`.`oid_type` ='".PluginPrintercountersCountertype_Recordmodel::SYSDESCR."'))
          LEFT JOIN  `".$itemjoin3."`
             ON (`".$itemjoin3."`.`plugin_printercounters_recordmodels_id` = `".$this->getTable()."`.`id`)
          WHERE `".$itemjoin."`.`items_id` IN ('".implode("','", $items_id)."')
          AND LOWER(`".$itemjoin."`.`itemtype`) = '".$itemtype."'";

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $output[$data['items_id']]['sysdescr'][]   = $data['sysdescr'];
            $output[$data['items_id']]['oid'][$data['oid_type']][] = $data['oid'];

            $output[$data['items_id']]['serial'] = $data['serial'];
            
            $output[$data['items_id']]['mac_address_conformity'] = $data['mac_address_conformity'];
            $output[$data['items_id']]['sysdescr_conformity']    = $data['sysdescr_conformity'];
            $output[$data['items_id']]['serial_conformity']      = $data['serial_conformity'];
            $output[$data['items_id']]['enable_toner_level']     = $data['enable_toner_level'];
            $output[$data['items_id']]['enable_printer_info']    = $data['enable_printer_info'];
         }
      }

      return $output;
   }
   
   /**
    * Duplicate recordmodel for an item if needed
    * 
    * @param type $itemtype
    * @param type $items_id
    * @param type $entities_id
    */
   function duplicateRecordmodelForItem($itemtype, $items_id, $entities_id){
      $item_recordmodel = new PluginPrintercountersItem_Recordmodel($itemtype, $items_id);
      $data = $item_recordmodel->getItem_RecordmodelForItem();
      if (!empty($data)) {
         $data = reset($data);

         // Get anscestors of the item entity
         $entities_ancestors = getAncestorsOf('glpi_entities', $entities_id);
         $entities_ancestors[$entities_id] = $entities_id;

         // If recordmodel is not in parent item entities
         if (!in_array($data['recordmodels_entity'], $entities_ancestors)) {
            // Duplicate the recordmodel
            $item_recordmodels_id = $data['id'];
            unset($data['id']);
            $data['entities_id'] = $entities_id;
            $data['name'] = $data['recordmodels_name'].' - '.__('Copy', 'printercounters').' '.PluginPrintercountersToolbox::getCopyNumber($data['recordmodels_name'], $this->getTable());
            $data['is_recursive'] = $data['recordmodels_recursivity'];
            if ($newId = $this->add($data)) {
               // Update item_recordmodels id
               $item_recordmodel->update(array('id' => $item_recordmodels_id, 'plugin_printercounters_recordmodels_id' => $newId));

               // Duplicate OID
               $countertype_recordmodel = new PluginPrintercountersCountertype_Recordmodel();
               $data_oid = $countertype_recordmodel->getCounterTypes($data['plugin_printercounters_recordmodels_id']);
               foreach ($data_oid as $value) {
                  unset($value['id']);
                  $value['plugin_printercounters_recordmodels_id'] = $newId;
                  $value['plugin_printercounters_countertypes_id'] = $value['countertypes_id'];
                  $countertype_recordmodel->add($value);
               }
            }
         }
      }
   }
   
   /**
    * Duplicate recordmodel
    * 
    * @param type $recordmodels_id
    */
   function duplicateRecordmodel($recordmodels_id){

      if ($this->getFromDB($recordmodels_id)) {
         // Duplicate the recordmodel
         unset($this->fields['id']);
         $this->fields['name'] = $this->fields['name'].' - '.__('Copy', 'printercounters').' '.PluginPrintercountersToolbox::getCopyNumber($this->fields['name'], $this->getTable());
         
         if ($newId = $this->add($this->fields)) {
            // Duplicate OID
            $countertype_recordmodel = new PluginPrintercountersCountertype_Recordmodel();
            $data_oid = $countertype_recordmodel->getCounterTypes($recordmodels_id);
            foreach ($data_oid as $value) {
               unset($value['id']);
               $value['plugin_printercounters_recordmodels_id'] = $newId;
               $value['plugin_printercounters_countertypes_id'] = $value['countertypes_id'];
               $countertype_recordmodel->add($value);
            }
            
            return true;
         }
      }
      
      return false;
   }

   /**
    * Check conformtity rules
    * 
    * @param PluginPrintercountersCommonSNMPObject $snmpObject
    * @param type $conformity_conf
    * @return type
    */
   public function checkConformity(PluginPrintercountersCommonSNMPObject $snmpObject, $conformity_conf){
      
      $record_result = PluginPrintercountersRecord::$SUCCESS;
      $record_type = PluginPrintercountersRecord::$AUTOMATIC_TYPE;

      // Check IP response
      $oid_ok = true;
      if (!$snmpObject->get($snmpObject->oid, PluginPrintercountersPrinter::ERROR_BOOL)) {
         $oid_ok = false;
      }

      $snmp = $snmpObject->session;

      if (!$oid_ok) {
         switch ($snmpObject->getErrorNumber()) {
            // Check ip timeout
            case $snmp::ERRNO_TIMEOUT :
               $record_result = PluginPrintercountersRecord::$IP_FAIL;
               $record_type = PluginPrintercountersRecord::$HOST_ERROR_TYPE;
               return array($record_result, $record_type);
         }
      }
      
      // Check sysdescr
      $sysdescr_ok = array();
      if ($conformity_conf['sysdescr_conformity']) {
         if (isset($conformity_conf['oid'][PluginPrintercountersCountertype_Recordmodel::SYSDESCR])) {
            foreach (array_unique($conformity_conf['oid'][PluginPrintercountersCountertype_Recordmodel::SYSDESCR]) as $sysdescr_oid) {
               foreach ($conformity_conf['sysdescr'] as $sysdescr) {
                  if (trim(strtolower($snmpObject->get($sysdescr_oid, PluginPrintercountersPrinter::ERROR_BOOL))) == trim(strtolower($sysdescr))) {
                     $sysdescr_ok[] = 1;
                  }
               }
            }
            if (!in_array(1, $sysdescr_ok)) {
               $record_result = PluginPrintercountersRecord::$SYSDESCR_FAIL;
               $record_type = PluginPrintercountersRecord::$HOST_ERROR_TYPE;
               return array($record_result, $record_type);
            }
         
         } else {
            throw new PluginPrintercountersException(__('Please set the sysdescr OID in your record model', 'printercounters'), 0, null, $this->items_id, $this->itemtype);
         }
      }

      // Check MAC address
      if ($conformity_conf['mac_address_conformity']) {
         $printer_mac = strtolower(PluginPrintercountersToolbox::getValidMacAddress($snmpObject->mac));
        
         if ($oid_mac = $snmpObject->walk(PluginPrintercountersPrinter::SNMP_PRINTER_MAC_ADDRESS, PluginPrintercountersPrinter::ERROR_BOOL)) {
            foreach ($oid_mac as $key => $value) {
               $valid_mac = PluginPrintercountersToolbox::getValidMacAddress($value);
               if ($valid_mac) {
                  $oid_mac[$key] = $valid_mac;
               } else {
                  unset($oid_mac[$key]);
               }
            }
         }
         if (!$printer_mac || !$oid_mac || !in_array($printer_mac, $oid_mac)) {
            $record_result = PluginPrintercountersRecord::$MAC_FAIL;
            $record_type = PluginPrintercountersRecord::$HOST_ERROR_TYPE;
            return array($record_result, $record_type);
         }
      }

      // Check serial
      $serial_ok = array();
      if ($conformity_conf['serial_conformity']) {
         if (isset($conformity_conf['oid'][PluginPrintercountersCountertype_Recordmodel::SERIAL])) {
            foreach (array_unique($conformity_conf['oid'][PluginPrintercountersCountertype_Recordmodel::SERIAL]) as $serial_oid) {
               if (!empty($serial_oid)) {
                  if (strtolower($snmpObject->get($serial_oid, PluginPrintercountersPrinter::ERROR_BOOL)) == strtolower($conformity_conf['serial'])) {
                     $serial_ok[] = 1;
                  }
               }
            }
            if (!in_array(1, $serial_ok)) {
               $record_result = PluginPrintercountersRecord::$SERIAL_FAIL;
               $record_type = PluginPrintercountersRecord::$HOST_ERROR_TYPE;
               return array($record_result, $record_type);
            }
            
         } else {
            throw new PluginPrintercountersException(__('Please set the serial OID in your record model', 'printercounters'), 0, null, $this->items_id, $this->itemtype);
         }
      }

      // Check OID
      if (!$oid_ok) {
         switch ($snmpObject->getErrorNumber()) {
            // Check if all oids are good
            case $snmp::ERRNO_ERROR_IN_REPLY: case $snmp::ERRNO_OID_NOT_INCREASING: case $snmp::ERRNO_OID_PARSING_ERROR : case $snmp::ERRNO_MULTIPLE_SET_QUERIES :
               $record_result = PluginPrintercountersRecord::$OID_FAIL;
               $record_type = PluginPrintercountersRecord::$HOST_ERROR_TYPE;
               return array($record_result, $record_type);
            // Unknown error
            default :
               $record_result = PluginPrintercountersRecord::$UNKNOWN_FAIL;
               $record_type = PluginPrintercountersRecord::$HOST_ERROR_TYPE;
               return array($record_result, $record_type);
         }
      }
      
      return array($record_result, $record_type);
   }
   
  /** 
   * Actions done before add
   * 
   * @param type $input
   * @return type
   */
   function prepareInputForAdd($input) {
      
      if (!$this->checkMandatoryFields($input)) {
         $_SESSION['plugin_printercounters']['recordmodel'] = $input;
         return false;
      }

      if (isset($input["id"]) && ($input["id"] > 0)) {
         $input["_oldID"]=$input["id"];
      }
      unset($input['id']);
      unset($input['withtemplate']);

      return $input;
   }
   
  /** 
   * Actions done before update
   * 
   * @param type $input
   * @return type
   */
   function prepareInputForUpdate($input) {
      
      if(!$this->checkMandatoryFields($input)){
         return false;
      }

      return $input;
   }
   
  /** 
   * Check mandatory fields 
   * 
   * @param type $input
   * @return boolean
   */
   function checkMandatoryFields($input){
      $msg     = array();
      $checkKo = false;
      
      $mandatory_fields = array('name'     => __('Name'));
      
      foreach($input as $key => $value){
         if (array_key_exists($key, $mandatory_fields)) {
            if (empty($value)) {
               $msg[] = $mandatory_fields[$key];
               $checkKo = true;
            }
         }
      }
      
      if ($checkKo) {
         Session::addMessageAfterRedirect(sprintf(__("Mandatory fields are not filled. Please correct: %s"), implode(', ', $msg)), false, ERROR);
         return false;
      }
      return true;
   }
   
   /**
    * Massive actions to be added
    * 
    * @param $input array of input datas
    *
    * @return array of results (nbok, nbko, nbnoright counts)
    **/
   function massiveActions(){
      
      $prefix = $this->getType().MassiveAction::CLASS_ACTION_SEPARATOR;
      
      switch ($this->itemtype) {
         case "PluginPrintercountersRecordmodel":
            $output = array();
            if ($this->canCreate()) {
               $output = array (
                  $prefix."plugin_printercounters_duplicate_recordmodel" => __('Duplicate recordmodel', 'printercounters')
               );
            }
            return $output;
      }
   }
   
   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {

      $recordmodel = new self();
      
      foreach ($ids as $key => $val) {
         if ($recordmodel->can($key, UPDATE)) {
            $result = false;
            switch ($ma->getAction()) {
               case "plugin_printercounters_duplicate_recordmodel":
                  if ($key) {
                     $result = $recordmodel->duplicateRecordmodel($key);
                  }
                  break;

               default :
                  return parent::doSpecificMassiveActions($ma->POST);
            }

            if ($result) {
               $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
            } else {
               $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
               $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
            }

         } else {
            $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_NORIGHT);
            $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
         }
      }
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