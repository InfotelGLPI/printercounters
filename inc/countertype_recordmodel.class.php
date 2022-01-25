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
 * Class PluginPrintercountersCountertype_Recordmodel
 *
 * This class allows to add and manage counter types and OIDs on the record model
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersCountertype_Recordmodel extends CommonDBTM {

   static $types = ['PluginPrintercountersRecordmodel'];

   // OID types
   const COLOR                    = 1;
   const MONOCHROME               = 2;
   const SERIAL                   = 3;
   const OTHER                    = 4;
   const SYSDESCR                 = 5;
   const BLACKANDWHITE            = 6;
   const BICOLOR                  = 7;
   const MODEL                    = 8;
   const NAME                     = 9;
   const NUMBER_OF_PRINTED_PAPERS = 10;

   static $rightname = 'plugin_printercounters';

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   static function getTypeName($nb = 0) {
      return _n('Counter type of record model', 'Counter types of record model', $nb, 'printercounters');
   }

   /**
    * Display tab for each users
    *
    * @param CommonGLPI $item
    * @param int $withtemplate
    * @return array|string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate) {
         if ($item->getType() == 'PluginPrintercountersRecordmodel') {
            if ($_SESSION['glpishow_count_on_tabs']) {
               $dbu = new DbUtils();
               return self::createTabEntry(self::getTypeName(),
                                           $dbu->countElementsInTable($this->getTable(),
                                                                      ["plugin_printercounters_recordmodels_id" => $item->getID()]));
            }
            return self::getTypeName();
         }
      }
      return '';
   }

   /**
    * Display content for each users
    *
    * @static
    * @param CommonGLPI $item
    * @param int $tabnum
    * @param int $withtemplate
    * @return bool|true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $field = new self();

      if (in_array($item->getType(), self::$types)) {
         $field->showForRecordmodel($item);
      }
      return true;
   }

   /**
    * Show countertypes form
    *
    * @param $ID        integer  ID of the item
    * @param $options   array    options used
    */
   function showForm($ID, $options = []) {

      if ($ID > 0) {
         $script = "$('#printercounters_viewAddCounters').show();";
      } else {
         $script = "$('#printercounters_viewAddCounters').hide();";
         $options['plugin_printercounters_recordmodels_id'] = $options['parent']->getField('id');
      }

      $this->initForm($ID, $options);

      echo html::scriptBlock($script);

      $this->initForm($ID, $options);

      $data = $this->getCounterTypes($options['parent']->getField('id'));

      $used_countertypes = [];
      if (!empty($data)) {
         foreach ($data as $field) {
            $used_countertypes[] = $field['countertypes_id'];
         }
      }

      $this->showFormHeader($options);
      echo "<tr class='tab_bg_1'>";
      // Dropdown countertype
      echo "<td class='center'>";
      echo PluginPrintercountersCountertype::getTypeName(2).'&nbsp;';
      Dropdown::show("PluginPrintercountersCountertype",
              ['name'  => 'plugin_printercounters_countertypes_id',
                    'value' => $this->fields['plugin_printercounters_countertypes_id'],
                    'used'  => $used_countertypes]);
      echo "</td>";
      // OID
      echo "<td class='center'>";
      echo __('OID', 'printercounters').'&nbsp;';
      echo Html::input('oid', ['value' => $this->fields['oid'], 'size' => 40]);
      echo "</td>";
      // OID type
      echo "<td class='center'>";
      echo __('OID type', 'printercounters').'&nbsp;';
      self::dropdownOidType(['value'                                  => $this->fields['oid_type'],
                                  'plugin_printercounters_recordmodels_id' => $options['parent']->getField('id')]);
      echo Html::hidden('plugin_printercounters_recordmodels_id', ['value' => $options['parent']->getField('id')]);
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }

   /**
    * Show countertypes for recordmodels
    *
    * @param type $item
    */
   function showForRecordmodel($item) {

      $recordmodel = new PluginPrintercountersRecordmodel();
      $canedit = ($recordmodel->can($item->fields['id'], UPDATE) && $this->canCreate());

      $data = $this->getCounterTypes($item->fields['id']);

      $rand = mt_rand();

      // JS edition
      if ($canedit) {
         echo "<div id='viewcountertype".$item->fields['id']."_$rand'></div>\n";
         PluginPrintercountersAjax::getJSEdition("viewcountertype".$item->fields['id']."_$rand",
                                                 "viewAddCounterType".$item->fields['id']."_$rand",
                                                 $this->getType(),
                                                 -1,
                                                 'PluginPrintercountersRecordmodel',
                                                 $item->fields['id']);
         echo "<div class='center firstbloc'>".
               "<a class='submit btn btn-primary' id='printercounters_viewAddCounters' href='javascript:viewAddCounterType".$item->fields['id']."_$rand();'>";
         echo __('Add a new counter', 'printercounters')."</a></div>\n";
      }

      if (!empty($data)) {
         $this->listItems($item->fields['id'], $data, $canedit, $rand);
      }

   }

   /**
    * List countertypes data for recordmodel
    *
    * @param type $ID
    * @param type $data
    * @param type $canedit
    * @param type $rand
    */
   private function listItems($ID, $data, $canedit, $rand) {

      echo "<div class='left'>";
      if ($canedit) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass'.__CLASS__.$rand];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4'>".PluginPrintercountersCountertype::getTypeName(2)."</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th width='10'>";
      if ($canedit) {
         echo Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
      }
      echo "</th>";
      echo "<th>".__('Name')."</th>";
      echo "<th>".__('OID', 'printercounters')."</th>";
      echo "<th>".__('OID type', 'printercounters')."</th>";
      echo "</tr>";

      foreach ($data as $field) {
         $onclick = ($canedit
                      ? "style='cursor:pointer' onClick=\"viewEditCounterType".$field['plugin_printercounters_recordmodels_id']."_".
                        $field['id']."_$rand();\"": '');

         echo "<tr class='tab_bg_2'>";
         echo "<td width='10'>";
         if ($canedit) {
            Html::showMassiveActionCheckBox(__CLASS__, $field['id']);
            // JS edition
            PluginPrintercountersAjax::getJSEdition("viewcountertype".$ID."_$rand",
                                                    "viewEditCounterType".$field['plugin_printercounters_recordmodels_id']."_".$field["id"]."_$rand",
                                                    $this->getType(),
                                                    $field["id"],
                                                    'PluginPrintercountersRecordmodel',
                                                    $field["plugin_printercounters_recordmodels_id"]);
         }
         echo "</td>";
         // Name
         $link = Toolbox::getItemTypeFormURL('PluginPrintercountersCountertype').'?id='.$field['countertypes_id'];
         echo "<td $onclick><a href='$link' target='_blank'>".$field['countertypes_name']."</a></td>";
         // OID
         echo "<td $onclick>".$field['oid']."</td>";
         // OID type
         $alloidtypes = self::getAllOidTypeArray();
         echo "<td $onclick>".$alloidtypes[$field['oid_type']]."</td>";
         echo "</tr>";
      }
      echo "</table>";
      if ($canedit) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }


      echo "</div>";
   }

   /**
    * Get counter types data for recordmodels
    *
    * @global type $DB
    * @param type $recordmodels_id
    * @return type
    */
   function getCounterTypes($recordmodels_id) {
      global $DB;

      $output = [];

      $query = "SELECT `glpi_plugin_printercounters_countertypes`.`name` as countertypes_name, 
                       `glpi_plugin_printercounters_countertypes`.`id` as countertypes_id, 
                       `".$this->getTable()."`.`plugin_printercounters_recordmodels_id`,
                       `".$this->getTable()."`.`id`, 
                       `".$this->getTable()."`.`oid`,
                       `".$this->getTable()."`.`oid_type`
          FROM ".$this->getTable()."
          LEFT JOIN `glpi_plugin_printercounters_countertypes` 
             ON (`".$this->getTable()."`.`plugin_printercounters_countertypes_id` = `glpi_plugin_printercounters_countertypes`.`id`)
          WHERE `".$this->getTable()."`.`plugin_printercounters_recordmodels_id` = ".Toolbox::cleanInteger($recordmodels_id);

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $output[$data['id']] = $data;
         }
      }

      return $output;
   }

   /**
   * Function get all record model counters
   *
   * @global type $DB
   * @param type $items_id
   * @param type $itemtype
   * @param type $order
   * @return type
   */
   function getRecordmodelCountersForItem($items_id, $itemtype, $order = null) {
      global $DB;

      $dbu       = new DbUtils();
      $itemjoin  = $dbu->getTableForItemType("PluginPrintercountersCountertype");
      $itemjoin2 = $dbu->getTableForItemType("PluginPrintercountersRecordmodel");
      $itemjoin3 = $dbu->getTableForItemType("PluginPrintercountersItem_Recordmodel");

      $output = [];

      $query = "SELECT `".$itemjoin."`.`name` as counters_name,
                       `".$itemjoin."`.`id` as countertypes_id,
                       `".$this->getTable()."`.`id` as countertypes_recordmodels_id,
                       `".$this->getTable()."`.`plugin_printercounters_recordmodels_id` as recordmodels_id
          FROM ".$this->getTable()."
          LEFT JOIN `".$itemjoin."` 
             ON (`".$itemjoin."`.`id` = `".$this->getTable()."`.`plugin_printercounters_countertypes_id`)
          LEFT JOIN `".$itemjoin2."` 
             ON (`".$itemjoin2."`.`id` = `".$this->getTable()."`.`plugin_printercounters_recordmodels_id`)      
          LEFT JOIN `".$itemjoin3."` 
             ON (`".$itemjoin3."`.`plugin_printercounters_recordmodels_id` = `".$itemjoin2."`.`id`)          
          WHERE `".$itemjoin3."`.`items_id` = ".Toolbox::cleanInteger($items_id)." 
          AND LOWER(`".$itemjoin3."`.`itemtype`) = '".strtolower($itemtype)."' 
          AND `".$this->getTable()."`.`oid_type` != '".self::SERIAL."' 
          AND `".$this->getTable()."`.`oid_type` != '".self::MODEL."' 
          AND `".$this->getTable()."`.`oid_type` != '".self::NUMBER_OF_PRINTED_PAPERS."' 
          AND `".$this->getTable()."`.`oid_type` != '".self::NAME."' 
          AND `".$this->getTable()."`.`oid_type` != '".self::SYSDESCR."'";

      if ($order != null) {
         $query .= " ORDER BY $order";
      }

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $output[] = $data;
         }
      }

      return $output;
   }

    /**
   * Function get all record model counters
   *
   * @global type $DB
   * @param type $items_id
   * @param type $itemtype
   * @param type $order
   * @return type
   */
   function getOIDRecordmodelCountersForItem($items_id, $itemtype, $oid_type) {
      global $DB;

      $dbu       = new DbUtils();
      $itemjoin  = $dbu->getTableForItemType("PluginPrintercountersCountertype");
      $itemjoin2 = $dbu->getTableForItemType("PluginPrintercountersRecordmodel");
      $itemjoin3 = $dbu->getTableForItemType("PluginPrintercountersItem_Recordmodel");

      $query = "SELECT `".$this->getTable()."`.`oid`
               FROM ".$this->getTable()."
               LEFT JOIN `".$itemjoin."` 
                  ON (`".$itemjoin."`.`id` = `".$this->getTable()."`.`plugin_printercounters_countertypes_id`)
               LEFT JOIN `".$itemjoin2."` 
                  ON (`".$itemjoin2."`.`id` = `".$this->getTable()."`.`plugin_printercounters_recordmodels_id`)      
               LEFT JOIN `".$itemjoin3."` 
                  ON (`".$itemjoin3."`.`plugin_printercounters_recordmodels_id` = `".$itemjoin2."`.`id`)          
               WHERE `".$itemjoin3."`.`items_id` = ".Toolbox::cleanInteger($items_id)." 
               AND LOWER(`".$itemjoin3."`.`itemtype`) = '".strtolower($itemtype)."' 
               AND `".$this->getTable()."`.`oid_type` = '".$oid_type."'";

      if ($oid_type == self::SERIAL) {
         $query .= "AND `".$itemjoin2."`.`serial_conformity` = '0'";
      }

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         return $DB->result($result, 0, 'oid');
      }
      return false;
   }

   /**
    * Function get counters data for multiple record models
    *
    * @global type $DB
    * @param array $recordmodels_id
    * @param type $order
    * @return type
    */
   function getRecordmodelCounters(array $recordmodels_id, $order = null) {
      global $DB;

      $dbu       = new DbUtils();
      $itemjoin  = $dbu->getTableForItemType("PluginPrintercountersCountertype");
      $itemjoin2 = $dbu->getTableForItemType("PluginPrintercountersRecordmodel");

      $output = [];

      $query = "SELECT `".$itemjoin."`.`name` as counters_name,
                       `".$itemjoin."`.`id` as countertypes_id,
                       `".$this->getTable()."`.`id` as countertypes_recordmodels_id,
                       `".$this->getTable()."`.`plugin_printercounters_recordmodels_id` as recordmodels_id
          FROM ".$this->getTable()."
          LEFT JOIN `".$itemjoin."` 
             ON (`".$itemjoin."`.`id` = `".$this->getTable()."`.`plugin_printercounters_countertypes_id`)
          LEFT JOIN `".$itemjoin2."` 
             ON (`".$itemjoin2."`.`id` = `".$this->getTable()."`.`plugin_printercounters_recordmodels_id`)         
          WHERE ";

      if (is_array($recordmodels_id)) {
         $query .= "`".$this->getTable()."`.`plugin_printercounters_recordmodels_id` IN ('".implode("','", $recordmodels_id)."')";
         $query .= "AND ";
      }

          $query .= " `".$this->getTable()."`.`oid_type` != '".self::SERIAL."' 
          AND `".$this->getTable()."`.`oid_type` != '".self::MODEL."' 
          AND `".$this->getTable()."`.`oid_type` != '".self::NUMBER_OF_PRINTED_PAPERS."' 
          AND `".$this->getTable()."`.`oid_type` != '".self::NAME."' 
          AND `".$this->getTable()."`.`oid_type` != '".self::SYSDESCR."'";

      if ($order != null) {
         $query .= " ORDER BY $order";
      }

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $output[] = $data;
         }
      }

      return $output;
   }

   /**
    * Show the OID type dropdown
    *
    * @return an array
    */
   static function dropdownOidType(array $options = []) {
      global $DB;

      if (isset($options['plugin_printercounters_recordmodels_id'])) {
         $query = "SELECT `glpi_plugin_printercounters_countertypes_recordmodels`.`oid_type` as oid_type
                   FROM `glpi_plugin_printercounters_countertypes_recordmodels`
                   WHERE `oid_type` = ". self::NAME ." 
                   AND `plugin_printercounters_recordmodels_id` = ".$options['plugin_printercounters_recordmodels_id'];

         $result = $DB->query($query);
         if ($DB->numrows($result)) {
            $options['used'][self::NAME] = self::NAME;
         }
      }

      return Dropdown::showFromArray('oid_type', self::getAllOidTypeArray(), $options);
   }

   /**
    * Function get OID type
    *
    * @return an array
    */
   static function getOidType($value) {
      if (!empty($value)) {
         $data = self::getAllOidTypeArray();
         return $data[$value];
      }
   }


   /**
    * Get the OID type list
    *
    * @return an array
    */
   static function getAllOidTypeArray() {

      // To be overridden by class
      $tab = [0                              => Dropdown::EMPTY_VALUE,
              self::COLOR                    => __('Color', 'printercounters'),
              self::MONOCHROME               => __('Monochrome', 'printercounters'),
              self::BLACKANDWHITE            => __('Black and white', 'printercounters'),
              self::BICOLOR                  => __('Bichromie', 'printercounters'),
              self::SERIAL                   => __('Serial number', 'printercounters'),
              self::SYSDESCR                 => __('Sysdescr', 'printercounters'),
              self::MODEL                    => __('Printer model'),
              self::NUMBER_OF_PRINTED_PAPERS => __('Number of printed papers', 'printercounters'),
              self::NAME                     => __('Name') . " " . strtolower(__('Printer')),
              self::OTHER                    => __('Other', 'printercounters')];

      return $tab;
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
         'id'                 => '72',
         'table'              => 'glpi_plugin_printercounters_countertypes',
         'field'              => 'name',
         'name'               => PluginPrintercountersCountertype::getTypeName(),
         'datatype'           => 'dropdown',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '73',
         'table'              => $this->getTable(),
         'field'              => 'oid',
         'name'               => __('OID', 'printercounters'),
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '74',
         'table'              => $this->getTable(),
         'field'              => 'oid_type',
         'name'               => __('OID type', 'printercounters'),
         'datatype'           => 'specific',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '75',
         'table'              => 'glpi_plugin_printercounters_recordmodels',
         'field'              => 'name',
         'name'               => PluginPrintercountersRecordmodel::getTypeName(),
         'datatype'           => 'dropdown',
         'massiveaction'      => false
      ];

      return $tab;
   }

   /**
    * @since version 0.84
    *
    * @param $field
    * @param $values
    * @param $options   array
    */
   static function getSpecificValueToDisplay($field, $values, array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'oid_type' :
            return self::getOidType($values[$field]);
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
    */
   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;
      $options['value']   = $values[$field];
      switch ($field) {
         case 'oid_type':
            return self::dropdownOidType($options);
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }



   /**
   * Function fill counters if recordmodel has changed
   *
   * @param array $input
   * @param array $recordmodels_id
   * @return type
   */
   function fillCountersGap(array $input, array $recordmodels_id) {

      $recordmodels_data = [];
      foreach ($recordmodels_id as $val) {
         $recordmodels_data[$val] = [];
      }
      $array = [];
      foreach ($recordmodels_id as $k => $v) {
         $k = strip_tags($k);
         $v = strip_tags($v);
         $array[$k] = $v;
      }
      $recordmodels_id = $array;

      $data = $this->getRecordmodelCounters($recordmodels_id);
      if (!empty($data)) {
         foreach ($data as $val) {
            $recordmodels_data[$val['recordmodels_id']][] = $val;
         }
      }

      if (!empty($recordmodels_data) && !empty($input)) {
         foreach ($input as $records_id => $row) {
            foreach ($recordmodels_data as $recordmodels_id => $counters) {
               foreach ($counters as $value) {
                  if ($recordmodels_id == $row['recordmodels_id'] && !in_array($value['countertypes_id'], array_keys($row['counters']))) {
                     $input[$records_id]['counters'][$value['countertypes_id']] = ['counters_name'                 => $value['counters_name'],
                                                                                        'countertypes_recordmodels_id'  => $value['countertypes_recordmodels_id'],
                                                                                        'counters_value'                =>  0,
                                                                                        'counters_id'                   =>  0];
                  }
               }
            }
            ksort($input[$records_id]['counters']);
         }
      }

      return $input;
   }

   /**
   * Actions done before add
   *
   * @param type $input
   * @return boolean
   */
   function prepareInputForAdd($input) {
      if (!$this->checkMandatoryFields($input)) {
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

      $this->checkCounterOidUpdate($input);

      return $input;
   }

   /**
   * Check if OID change, then delete old record counters
   *
   * @param type $input
   * @return boolean
   */
   function checkCounterOidUpdate($input) {

      if ($this->fields['oid'] != $input['oid']) {
         $counter = new PluginPrintercountersCounter();
         $counter->deleteByCriteria(['plugin_printercounters_countertypes_recordmodels_id' => $input['id']], 1);
      }
   }

   /**
   * Check mandatory fields
   *
   * @param type $input
   * @return boolean
   */
   function checkMandatoryFields($input) {
      $msg     = [];
      $checkKo = false;

      $mandatory_fields = ['oid_type'                               => __('OID type', 'printercounters'),
                                'oid'                                    => __('OID', 'printercounters'),
                                'plugin_printercounters_countertypes_id' => PluginPrintercountersCountertype::getTypeName()];

      foreach ($input as $key => $value) {
         if (array_key_exists($key, $mandatory_fields)) {
            if (empty($value)) {
               $msg[] = $mandatory_fields[$key];
               $checkKo = true;
            }
         }
      }

      if ($checkKo) {
         Session::addMessageAfterRedirect(sprintf(__("Mandatory fields are not filled. Please correct: %s"), implode(', ', $msg)), true, ERROR);
         return false;
      }
      return true;
   }

   /**
   * Actions done after update
   */
   function post_addItem($history = 1) {

      if ($this->fields['oid_type'] != self::SERIAL
         && $this->fields['oid_type'] != self::SYSDESCR
            && $this->fields['oid_type'] != self::NAME
               && $this->fields['oid_type'] != self::NUMBER_OF_PRINTED_PAPERS
                  && $this->fields['oid_type'] != self::MODEL) {
         // Add countertype for billingmodels liked to the recordmodel
         $pagecost = new PluginPrintercountersPagecost();
         $pagecost->addCounterTypeForBillings($this->fields['plugin_printercounters_recordmodels_id'], $this->fields['plugin_printercounters_countertypes_id']);
      }

      parent::post_addItem($history);
   }

}
