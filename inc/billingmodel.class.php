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
   public function __construct($itemtype = 'printer', $items_id = 0) {
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
         'id'                 => '32',
         'table'              => 'glpi_plugin_printercounters_recordmodels',
         'field'              => 'name',
         'name'               => PluginPrintercountersRecordmodel::getTypeName(2),
         'datatype'           => 'dropdown',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '33',
         'table'              => $this->getTable(),
         'field'              => 'application_date',
         'name'               => __('Application date', 'printercounters'),
         'datatype'           => 'datetime',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '34',
         'table'              => 'glpi_budgets',
         'field'              => 'name',
         'name'               => __('Budget'),
         'datatype'           => 'dropdown',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '35',
         'table'              => 'glpi_contracts',
         'field'              => 'name',
         'name'               => __('Contract'),
         'datatype'           => 'dropdown',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '36',
         'table'              => 'glpi_suppliers',
         'field'              => 'name',
         'name'               => __('Supplier'),
         'datatype'           => 'dropdown',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '37',
         'table'              => 'glpi_plugin_printercounters_countertypes',
         'field'              => 'name',
         'name'               => __('Counter type', 'printercounters'),
         'massiveaction'      => false,
         'datatype'           => 'dropdown',
         'forcegroupby'       => true,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_plugin_printercounters_pagecosts',
               'joinparams'         => [
                  'jointype'           => 'child'
               ]
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '38',
         'table'              => 'glpi_plugin_printercounters_pagecosts',
         'field'              => 'cost',
         'name'               => __('Cost'),
         'massiveaction'      => false,
         'forcegroupby'       => true,
         'datatype'           => 'specific'
      ];

      $tab[] = [
         'id'                 => '39',
         'table'              => 'glpi_printers',
         'field'              => 'name',
         'forcegroupby'       => true,
         'name'               => __('Linked items', 'printercounters'),
         'massiveaction'      => false,
         'datatype'           => 'itemlink',
         'linkfield'          => 'items_id',
         'joinparams'         => [
            'condition'  => ' AND `glpi_plugin_printercounters_items_billingmodels`.`itemtype` = "Printer"',
            'beforejoin' => [
               'table'      => 'glpi_plugin_printercounters_items_billingmodels',
               'joinparams' => [
                  'jointype' => 'child'
               ]
            ]
         ]
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
          ['name'  => 'plugin_printercounters_recordmodels_id',
                'label' => PluginPrintercountersRecordmodel::getTypeName(2),
                'type'  => 'specific',
                'list'  => false],
          ['name'  => 'application_date',
                'label' => __('Application date', 'printercounters'),
                'type'  => 'datetime',
                'list'  => true],
          ['name'  => 'budgets_id',
                'label' => __('Budget'),
                'type'  => 'dropdownValue',
                'list'  => true],
          ['name'  => 'contracts_id',
                'label' => __('Contract'),
                'type'  => 'dropdownValue',
                'list'  => true],
          ['name'  => 'suppliers_id',
                'label' => __('Supplier'),
                'type'  => 'dropdownValue',
                'list'  => true],
      ];

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
   function setSessionValues() {
      if (isset($_SESSION['plugin_printercounters']['billingmodel']) && !empty($_SESSION['plugin_printercounters']['billingmodel'])) {
         foreach ($_SESSION['plugin_printercounters']['billingmodel'] as $key => $val) {
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
   function displaySpecificTypeField($ID, $field = [], array $options = []) {

      $this->getFromDB($ID);

      $value = '';
      if (isset($this->fields[$field['name']])) {
         $value = $this->fields[$field['name']];
      }

      switch ($field['name']) {
         case 'plugin_printercounters_recordmodels_id':
            Dropdown::show('PluginPrintercountersRecordmodel',
                    ['value'     => $value,
                          'on_change' => "printercounters_setConfirmation(\"".__('Are you sure to change the recordmodel ?', 'printercounters')."\", \"".$this->fields[$field['name']]."\", this.value);"]);
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
   function checkApplicationDate($input) {
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
   function getBillingModelsForRecordmodel($recordmodels_id) {
      $data = $this->find(['plugin_printercounters_recordmodels_id' => $recordmodels_id]);

      if (!empty($data)) {
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
   function duplicateBillingmodelForItem($itemtype, $items_id, $entities_id) {
      $item_billingmodel = new PluginPrintercountersItem_Billingmodel();
      $billings          = $item_billingmodel->getBillingmodelForItem($items_id, $itemtype,
                                                                      ['addLimit' => false]);
      $dbu               = new DbUtils();
      if (!empty($billings)) {
         foreach ($billings as $data) {
            // Get anscestors of the item entity

            $entities_ancestors = $dbu->getAncestorsOf('glpi_entities', $entities_id);
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

               $item_billingmodel->delete(['id' => $item_billingmodels_id], 1);

               if ($newId = $this->add($data)) {
                  // Update item_billingmodels id
                  $item_billingmodel->add(['items_id' => $items_id, 'itemtype' => $itemtype, 'plugin_printercounters_billingmodels_id' => $newId]);

                  // Duplicate costs
                  $pagecosts = new PluginPrintercountersPagecost();
                  $countertypes = $pagecosts->getCounterTypes($newId);
                  foreach ($countertypes as $value) {
                     if (in_array($value['countertypes_id'], array_keys($data['counters']))) {
                        $pagecosts->update(['id'                                      => $value['id'],
                                                 'plugin_printercounters_billingmodels_id' => $newId,
                                                 'cost'                                    => $data['counters'][$value['countertypes_id']]]);
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
   function checkLinkedRecordModels($plugin_printercounters_recordmodels_id) {
      $links = [];

      if ($data = $this->getBillingModelsForRecordmodel($plugin_printercounters_recordmodels_id)) {
         $pagecosts = new PluginPrintercountersPagecost();

         foreach ($data as $value) {
            // check if counter is used in billing models
            $billingCounters = $pagecosts->getCounterTypes($value['id']);
            $counterUsed     = false;
            foreach ($billingCounters as $billingCounter) {
               if ($plugin_printercounters_recordmodels_id == $billingCounter['countertypes_id']) {
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
      if (!$this->checkMandatoryFields($input)) {
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
      if (!$this->checkMandatoryFields($input)) {
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
         $pagecost->deleteByCriteria(['plugin_printercounters_billingmodels_id' => $this->fields['id']]);
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
   function checkMandatoryFields(&$input) {
      $msg     = [];
      $checkKo = false;

      $mandatory_fields = ['plugin_printercounters_recordmodels_id'  => PluginPrintercountersRecordmodel::getTypeName(2),
                                'application_date'                        => __('Application date', 'printercounters')];

      foreach ($input as $key => $value) {
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
         foreach ($msg as $key => $value) {
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
