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
 * Class PluginPrintercountersBillingmodel
 * 
 * This class allows to manage the billing models
 * 
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersBillingmodel extends CommonDropdown {

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
   
   static function getTypeName($nb = 0) {
      return _n("Billing model", "Billing models", $nb, 'printercounters');
   }
   
  /** 
   * Get search options
   * 
   * @return array
   */
   function getSearchOptions() {

      $tab = parent::getSearchOptions();

      $tab[32]['table']          = 'glpi_plugin_printercounters_recordmodels';
      $tab[32]['field']          = 'name';
      $tab[32]['name']           = PluginPrintercountersRecordmodel::getTypeName(2);
      $tab[32]['datatype']       = 'dropdown';
      $tab[32]['massiveaction']  = false;

      $tab[33]['table']          = $this->getTable();
      $tab[33]['field']          = 'application_date';
      $tab[33]['name']           = __('Application date', 'printercounters');
      $tab[33]['datatype']       = 'datetime';
      $tab[33]['massiveaction']  = true;

      $tab[34]['table']          = 'glpi_budgets';
      $tab[34]['field']          = 'name';
      $tab[34]['name']           = __('Budget');
      $tab[34]['datatype']       = 'dropdown';
      $tab[34]['massiveaction']  = true;

      $tab[35]['table']          = 'glpi_contracts';
      $tab[35]['field']          = 'name';
      $tab[35]['name']           = __('Contract');
      $tab[35]['datatype']       = 'dropdown';
      $tab[35]['massiveaction']  = true;

      $tab[36]['table']          = 'glpi_suppliers';
      $tab[36]['field']          = 'name';
      $tab[36]['name']           = __('Supplier');
      $tab[36]['datatype']       = 'dropdown';
      $tab[36]['massiveaction']  = true;
      
      $tab[37]['table']          = 'glpi_plugin_printercounters_countertypes';
      $tab[37]['field']          = 'name';
      $tab[37]['name']           = __('Counter type', 'printercounters');
      $tab[37]['massiveaction']  = false;
      $tab[37]['datatype']       = 'dropdown';
      $tab[37]['forcegroupby']   = true;
      $tab[37]['joinparams']     = array('beforejoin'
                                          => array('table'      => 'glpi_plugin_printercounters_pagecosts',
                                                   'joinparams' => array('jointype'   => 'child'))
                                        );
      
      $tab[38]['table']          = 'glpi_plugin_printercounters_pagecosts';
      $tab[38]['field']          = 'cost';
      $tab[38]['name']           = __('Cost');
      $tab[38]['massiveaction']  = false;
      $tab[38]['forcegroupby']   = true;
      $tab[38]['datatype']       = 'specific';
      
      $tab[39]['table']          = getTableForItemType($this->itemtype);
      $tab[39]['field']          = 'name';
      $tab[39]['forcegroupby']   = true;
      $tab[39]['name']           = __('Linked items', 'printercounters');
      $tab[39]['massiveaction']  = false;
      $tab[39]['datatype']       = 'itemlink';
      $tab[39]['linkfield']      = 'items_id';
      $tab[39]['joinparams']     = array('condition' => " AND `glpi_plugin_printercounters_items_billingmodels`.`itemtype` = '".$this->itemtype."'",
                                          'beforejoin'
                                          => array('table'      => 'glpi_plugin_printercounters_items_billingmodels',
                                                   'joinparams' => array('jointype'   => 'child')
                                              )
                                        );

      return $tab;
   }

  /** 
   * Get additional fields in form
   * 
   * @return array
   */
   function getAdditionalFields() {

      $tab = array(
          array('name'  => 'plugin_printercounters_recordmodels_id',
                'label' => PluginPrintercountersRecordmodel::getTypeName(2),
                'type'  => 'specific',
                'list'  => false),
          array('name'  => 'application_date',
                'label' => __('Application date', 'printercounters'),
                'type'  => 'datetime',
                'list'  => true),
          array('name'  => 'budgets_id',
                'label' => __('Budget'),
                'type'  => 'dropdownValue',
                'list'  => true),
          array('name'  => 'contracts_id',
                'label' => __('Contract'),
                'type'  => 'dropdownValue',
                'list'  => true),
          array('name'  => 'suppliers_id',
                'label' => __('Supplier'),
                'type'  => 'dropdownValue',
                'list'  => true),
      );

      return $tab;
   }
   
  /** 
   * Form header
   */
   function displayHeader() {
      Html::header($this->getTypeName(), '', "tools", "pluginprintercountersmenu", "billingmodel");
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
   * @param type $input
   * @return type
   */
   function setSessionValues(){
      if(isset($_SESSION['plugin_printercounters']['billingmodel']) && !empty($_SESSION['plugin_printercounters']['billingmodel'])){
         foreach($_SESSION['plugin_printercounters']['billingmodel'] as $key => $val){
            $this->fields[$key] = $val;
         }
      }
      unset($_SESSION['plugin_printercounters']['billingmodel']);
   }
   
  /** 
   * Display specific fields
   * 
   * @param type $ID
   * @param type $field
   */
   function displaySpecificTypeField($ID, $field=array()) {

      $this->getFromDB($ID);
      
      $value = '';
      if(isset($this->fields[$field['name']])){
         $value = $this->fields[$field['name']];
      }

      switch($field['name']){        
         case 'plugin_printercounters_recordmodels_id':
            Dropdown::show('PluginPrintercountersRecordmodel', 
                    array('value'     => $value, 
                          'on_change' => "printercounters_setConfirmation(\"".__('Are you sure to change the recordmodel ?', 'printercounters')."\", \"".$this->fields[$field['name']]."\", this.value);"));
            break;
         
//         case 'plugin_printercounters_budgets_id':
//            Dropdown::show('PluginPrintercountersBudget', array('value' => $value));
//            break;
      }
   }
   
   
  /** 
   * Check if application date is not already used
   * 
   * @param type $input
   * @return boolean
   */
   function checkApplicationDate($input){
      $data = $this->find();
      if (!empty($data)) {
         foreach ($data as $value) {
            if (strtotime($value['application_date']) == strtotime($input['application_date']) 
                  && $value['plugin_printercounters_recordmodels_id'] == $input['plugin_printercounters_recordmodels_id'] 
                  && (isset($input['id']) && $input['id'] > 0 && $input['id'] != $value['id'])) {
               return false;
            }
         }
      }
      
      return true;
   }
   
   /** 
    * Get billingmodels linked to recordmodel
    * 
    * @param type $recordmodels_id
    * @return boolean
    */
   function getBillingModelsForRecordmodel($recordmodels_id){
      $data = $this->find("`plugin_printercounters_recordmodels_id`=".$recordmodels_id);
      
      if(!empty($data)){
         return $data;
      }
      
      return false;
   }
   
   /**
    * Duplicate billingmodel if needed
    * 
    * @param type $itemtype
    * @param type $items_id
    * @param type $entities_id
    */
   function duplicateBillingmodelForItem($itemtype, $items_id, $entities_id){
      $item_billingmodel = new PluginPrintercountersItem_Billingmodel();
      $billings = $item_billingmodel->getBillingmodelForItem($items_id, $itemtype, array('addLimit' => false));

      if (!empty($billings)) {
         foreach ($billings as $data) {
            // Get anscestors of the item entity
            $entities_ancestors = getAncestorsOf('glpi_entities', $entities_id);
            $entities_ancestors[$entities_id] = $entities_id;

            // If billingmodel is not in parent item entities
            if (!in_array($data['billingmodels_entity'], $entities_ancestors)) {
               // Duplicate the billingmodel
               $item_billingmodels_id = $data['id'];
               unset($data['id']);
               $data['entities_id'] = $entities_id;
               $data['name'] = $data['billingmodels_name'].' - '.__('Copy', 'printercounters').' '.PluginPrintercountersToolbox::getCopyNumber($data['billingmodels_name'], $this->getTable());
               $data['is_recursive'] = $data['billingmodels_recursivity'];
               $data['plugin_printercounters_recordmodels_id'] = $data['currentitem_recordmodels_id'];
               
               $item_billingmodel->delete(array('id' => $item_billingmodels_id), 1);
               
               if ($newId = $this->add($data)) {
                  // Update item_billingmodels id
                  $item_billingmodel->add(array('items_id' => $items_id, 'itemtype' => $itemtype, 'plugin_printercounters_billingmodels_id' => $newId));

                  // Duplicate costs
                  $pagecosts = new PluginPrintercountersPagecost();
                  $countertypes = $pagecosts->getCounterTypes($newId);
                  foreach ($countertypes as $value) {
                     if (in_array($value['countertypes_id'], array_keys($data['counters']))) {
                        $pagecosts->update(array('id'                                      => $value['id'],
                                                 'plugin_printercounters_billingmodels_id' => $newId,
                                                 'cost'                                    => $data['counters'][$value['countertypes_id']]));
                     }
                  }
               }
            }
         }
      }
   }
   
      
/** 
    * Check if a recordmodel is linked to billingmodels
    * 
    * @param type $recordmodels_id
    * @return boolean
    */
   function checkLinkedRecordModels(PluginPrintercountersCountertype_Recordmodel $countertype_recordmodel){
      $links = array(); 
      
      if ($data = $this->getBillingModelsForRecordmodel($countertype_recordmodel->fields['plugin_printercounters_recordmodels_id'])) {
         $pagecosts = new PluginPrintercountersPagecost();

         foreach ($data as $value) {
            // check if counter is used in billing models
            $billingCounters = $pagecosts->getCounterTypes($value['id']);
            $counterUsed     = false;
            foreach ($billingCounters as $billingCounter) {
               if ($countertype_recordmodel->fields['plugin_printercounters_countertypes_id'] == $billingCounter['countertypes_id']) {
                  $counterUsed = true;
                  break;
               }
            }
            
            // Used in this billing model
            if ($counterUsed) {
               $links[] = "<a href='".Toolbox::getItemTypeFormURL($this->getType())."?id=".$value['id']."'>".$value['name']."</a>";
            }
         }
      }

      return $links;
   }


   /** 
   * Actions done before add
   * 
   * @param type $input
   * @return boolean
   */
   function prepareInputForAdd($input) {
      if(!$this->checkMandatoryFields($input)){
         $_SESSION['plugin_printercounters']['billingmodel'] = $input;
         return false;
      }
      
      return $input;
   }
   
  /** 
   * Actions done before update
   * 
   * @param type $input
   * @return boolean
   */
   function prepareInputForUpdate($input) {
      if(!$this->checkMandatoryFields($input)){
         return false;
      }

      return $input;
   }
   
   
  /** 
   * Actions done before update in db
   */
   function pre_updateInDB() {
      
      if (in_array('plugin_printercounters_recordmodels_id', $this->updates)) {
         // Add recordmodels countertypes
         $pagecost = new PluginPrintercountersPagecost();
         $pagecost->deleteByCriteria(array('plugin_printercounters_billingmodels_id' => $this->fields['id']));
         $pagecost->addRecordmodelCounterTypesForBilling($this->fields['plugin_printercounters_recordmodels_id'], $this->fields['id']);
      }

      parent::pre_updateInDB();
   }
   
  /** 
   * Actions done after update
   */
   function post_updateItem($history = 1) {
      
      if (in_array('plugin_printercounters_recordmodels_id', $this->updates)) {
         // Clean item linked to billing model
         $item_billingmodel = new PluginPrintercountersItem_Billingmodel();
         $item_billingmodel->cleanItems($this->fields['id']);
      }
      
      parent::post_updateItem($history);
   }
   
  /** 
   * Actions done after add
   */
   function post_addItem($history = 1) {
      
      // Add recordmodels countertypes
      $pagecost = new PluginPrintercountersPagecost();
      $pagecost->addRecordmodelCounterTypesForBilling($this->fields['plugin_printercounters_recordmodels_id'], $this->fields['id']);
      
      parent::post_updateItem($history);
   }
   
  /** 
   * Check mandatory fields
   * 
   * @param type $input
   * @return boolean
   */
   function checkMandatoryFields(&$input){
      $msg     = array();
      $checkKo = false;
      
      $mandatory_fields = array('plugin_printercounters_recordmodels_id'  => PluginPrintercountersRecordmodel::getTypeName(2),
                                'application_date'                        => __('Application date', 'printercounters'));
      
      foreach($input as $key => $value){
         if (array_key_exists($key, $mandatory_fields)) {
            switch ($key) {
               case 'application_date':
                  if (!$this->checkApplicationDate($input) || (empty($value) || $value == 'NULL')) {
                     $msg[$key] = $mandatory_fields[$key];
                     $checkKo = true;
                     unset($input[$key]);
                  }
                  break;
               default:
                  if (empty($value)) {
                     $msg[$key] = $mandatory_fields[$key];
                     $checkKo = true;
                  }
                  break;
            }
         }
      }
      
      if ($checkKo) {
         foreach($msg as $key => $value){
            switch ($key) {
               case 'application_date':
                  Session::addMessageAfterRedirect(__("Application date is already used. Please select another", 'printercounters'), true, ERROR);
                  return false;
               default:
                  Session::addMessageAfterRedirect(sprintf(__("Mandatory fields are not filled. Please correct: %s"), implode(', ', $msg)), true, ERROR);
                  return false;
            }
         }
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