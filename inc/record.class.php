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
 * Class PluginPrintercountersRecord
 *
 * This class allows to add and manage the counter records of the items
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersRecord extends CommonDBTM {

   // Record result
   static $SUCCESS           = 1;
   static $IP_FAIL           = 2;
   static $MAC_FAIL          = 3;
   static $SYSDESCR_FAIL     = 4;
   static $SERIAL_FAIL       = 5;
   static $OID_FAIL          = 6;
   static $UNKNOWN_FAIL      = 7;

   // Record state
   static $PROGRAMMED_STATE  = 1;
   static $FINISHED_STATE    = 2;
   static $PROGRESS_STATE    = 3;

   // Record type
   static $HOST_ERROR_TYPE   = 1;
   static $RECORD_ERROR_TYPE = 2;
   static $AUTOMATIC_TYPE    = 3;
   static $MANUAL_TYPE       = 4;

   // Clean type
   static $CLEAN_EMTPY_RECORDS = 1;
   static $CLEAN_ERROR_RECORDS = 2;

   var $items_id;
   var $itemtype;
   var $tags;
   var $rand = 0;

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
      $this->setRand();

      parent::__construct();
   }

   static function getTypeName($nb = 0) {
      return _n("Record", "Records", $nb, 'printercounters');
   }

   static function canUpdateRecords() {
      return Session::haveRight('plugin_printercounters_update_records', 1);
   }

   static function canAddLessRecords() {
      return Session::haveRight('plugin_printercounters_add_lower_records', 1);
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

   /**
    * Function sets rand
    */
   public function setRand() {

      $this->rand = mt_rand();
   }

   /**
    * Function show record actions
    *
    * @global type $CFG_GLPI
    * @param type $rand
    * @return boolean
    */
   function showActions() {
      global $CFG_GLPI;

      $this->manualRecord();

      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='6'>".__('Actions', 'printercounters')."</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td class='center' width='650px'>";
      if ($this->canView()) {
         echo "<div class='printercounters_action_group'>";
         // Immediate record
         $onclick = "onclick='printercountersAction.printercountersActions(\"".PLUGIN_PRINTERCOUNTERS_WEBDIR."\", \"immediateRecord\", \"\", \"record_action_result\", 
                  ".json_encode(['items_id'        => $this->items_id,
                                      'itemtype'        => $this->itemtype,
                                      'formName'        => 'search_form'.$this->rand,
                                      'updates'         => ['record' => 'history_showForm'.$this->rand, 'additionalData' => 'additional_datas', 'errorItem' => 'error_item']]).");'";
         echo "<a $onclick class='submit btn btn-primary printercounters_action_button'>".__('Immediate record', 'printercounters')."</a>";

         // Manual record
         $onclick = "onclick='printercountersAction.printercountersActions(\"".PLUGIN_PRINTERCOUNTERS_WEBDIR."\", \"showManualRecord\", \"\", \"\", 
                  ".json_encode(['items_id'        => $this->items_id,
                                      'itemtype'        => $this->itemtype,
                                      'addLowerRecord'  => 0,
                                      'formName'        => 'search_form'.$this->rand,
                                      'updates'         => ['record' => 'history_showForm'.$this->rand],
                                      'rand'            => $this->rand]).");'";
         echo "<a $onclick class='submit btn btn-primary printercounters_action_button'>".__('Add a manual record', 'printercounters')."</a>";

         // Manual record : add less record
         if (self::canAddLessRecords()) {
            $onclick = "onclick='printercountersAction.printercountersActions(\"".PLUGIN_PRINTERCOUNTERS_WEBDIR."\", \"showManualRecord\", \"\", \"\", 
                     ".json_encode(['items_id'        => $this->items_id,
                                         'itemtype'        => $this->itemtype,
                                         'addLowerRecord'  => 1,
                                         'formName'        => 'search_form'.$this->rand,
                                         'updates'         => ['record' => 'history_showForm'.$this->rand],
                                         'rand'            => $this->rand]).");'";
            echo "<a $onclick class='submit btn btn-primary printercounters_action_button'>".__('Add a lower record', 'printercounters')."</a>";
         }
         echo "</div>";
      }

      if ($this->canCreate()) {
         echo "<div class='printercounters_action_group'>";
         // Update global TCO
         $onclick = "onclick='printercountersAction.printercountersActions(\"".PLUGIN_PRINTERCOUNTERS_WEBDIR."\", \"updateGlobalTco\", \"\", \"record_action_result\", 
                  ".json_encode(['items_id'        => $this->items_id,
                                      'itemtype'        => $this->itemtype,
                                      'formName'        => 'search_form'.$this->rand,
                                      'updates'         => ['globalTco' => 'update_global_tco']]).");'";
         echo "<a $onclick class='submit btn btn-primary printercounters_action_button'>".__('Update global TCO', 'printercounters')."</a>";

         // Update printer data
         $onclick = "onclick='printercountersAction.printercountersActions(\"".PLUGIN_PRINTERCOUNTERS_WEBDIR."\", \"updatePrinterData\", \"\", \"record_action_result\", 
                  ".json_encode(['items_id'        => $this->items_id,
                                      'itemtype'        => $this->itemtype,
                                      'formName'        => 'search_form'.$this->rand,
                                      'updates'         => ['record' => 'history_showForm'.$this->rand]]).");'";
         echo "<a $onclick class='submit btn btn-primary printercounters_action_button'>".__('Update printer data', 'printercounters')."</a>";

         // SNMP set
         if (PluginPrintercountersSnmpset::canSnmpSet()) {
            $onclick = "onclick='printercountersAction.printercountersActions(\"".PLUGIN_PRINTERCOUNTERS_WEBDIR."\", \"SNMPSet\", \"\", \"record_action_result\", 
                     ".json_encode(['items_id'        => $this->items_id,
                                         'itemtype'        => $this->itemtype,
                                         'formName'        => 'search_form'.$this->rand,
                                         'updates'         => ['additionalData' => 'additional_datas'],
                                         'rand'            => $this->rand]).");'";
            echo "<a $onclick class='submit btn btn-primary printercounters_action_button'>".__('Update the values of the physical printer', 'printercounters')."</a>";
         }
         echo "</div>";
      }
      echo "</td>";
      echo "</tr>";

      // Results
      echo "<tr class='tab_bg_1'>";
      echo "<td class='center'><div id='record_action_result'></div></td>";
      echo "</tr>";
      echo "</table></div>";
   }


   /**
   * Init printercoutners JS
   *
   */
   static function initPrintercountersActionsJS() {

      Html::requireJs('printercounters');

      echo Html::scriptBlock('
         var printercountersAction = $(document).printercountersAction();
         ');

   }

   /**
    * Search function : set group by
    *
    * @return an array
    */
   function addGroupBy() {
      return $this->getTable().".`id`";
   }

   /**
    * Search function : addRestriction
    *
    * @return string
    */
   function addRestriction() {

      $options     = Search::getOptions($this->getType());
      $restriction = '';
      $dbu         = new DbUtils();
      foreach ($options as $num => $val) {
         if ($val['table'] == $dbu->getTableForItemType('PluginPrintercountersRecord') && $val['field'] == 'id') {
            $restriction .= PluginPrintercountersSearch::addWhere('', 1, $this->getType(), $num, 'equals', null);
         }
         if ($val['table'] == $dbu->getTableForItemType($this->itemtype) && $val['field'] == 'id') {
            $restriction .= PluginPrintercountersSearch::addWhere('AND', 0, $this->getType(), $num, 'equals', $this->items_id);
         }
      }

      return $restriction;
   }

   /**
    * Search function : set default search
    *
    * @return an array
    */
   function getDefaultSearch() {

      $default_search = [];
      $dbu            = new DbUtils();
      $options        = Search::getCleanedOptions($this->getType());
      foreach ($options as $num => $val) {
         if ($val['table'] == 'glpi_entities' && $val['field'] == 'name') {
            $fields_num = $num;
            break;
         }
      }
      foreach ($options as $num => $val) {
         if ($val['table'] == $dbu->getTableForItemType($this->getType()) && $val['field'] == 'date') {
            $default_search['sort'] = $num;
            break;
         }
      }
      $default_search['criteria'][] = ['field' => $fields_num, 'searchtype' => '', 'value' => '', 'link' => ''];
      $default_search['order']    = 'DESC';

      return $default_search;
   }

   /**
    * Search function : show record history data
    *
    * @global type $CFG_GLPI
    * @param PluginPrintercountersSearch $search
    */
   function showSearchData(PluginPrintercountersSearch $search) {
      global $CFG_GLPI;

      // Format data
      $input = $this->formatSearchData($search->input);

      // Line break
      switch ($search->output_type) {
         case search::HTML_OUTPUT:
            $lineBreak = "<hr style='margin: 0px!important;'>";
            break;
         default:
            $lineBreak = "\n";
            break;
      }

      // Fill counters if recordmodel has changed
      $recordmodels_id = [];

      foreach ($input['records'] as $records_id => $history) {
         $recordmodels_id[$history['recordmodels_id']] = $history['recordmodels_id'];
      }
      $countertype_recordmodels = new PluginPrintercountersCountertype_Recordmodel();

      $input['records'] = $countertype_recordmodels->fillCountersGap($input['records'], $recordmodels_id);

      // Display data
      $row_num  = 1;
      foreach ($input['records'] as $records_id => $history) {
         $row_num++;
         $col_num = 1;
         echo Search::showNewLine($search->output_type);
         $onclick = '';
         $style = " style='cursor:pointer;'";
         if ($this->canUpdateRecords()) {
            $onclick = " onclick='printercountersAction.printercountersActions(\"".PLUGIN_PRINTERCOUNTERS_WEBDIR."\", \"showManualRecord\", \"\", \"\", 
               ".json_encode(['items_id'        => $this->items_id,
                                   'itemtype'        => $this->itemtype,
                                   'addLowerRecord'  => $this->canAddLessRecords() ? 1 : 0,
                                   'records_id'      => $records_id,
                                   'formName'        => 'search_form'.$this->rand,
                                   'historyFormName' => 'history_showForm'.$this->rand,
                                   'rand'            => $this->rand]).");'";
         }

         echo Search::showItem($search->output_type, $history['formated_date'], $col_num, $row_num, $onclick.$style);
         echo Search::showItem($search->output_type, $history['recordmodels_name'], $col_num, $row_num, $onclick.$style);
         echo Search::showItem($search->output_type, $history['entities_name'], $col_num, $row_num, $onclick.$style);
         $counters = [];
         foreach ($history['counters'] as $val) {
            $counters[$val['counters_name']] = $val['counters_value'];
         }
         echo Search::showItem($search->output_type, implode($lineBreak, array_keys($counters)), $col_num, $row_num, $onclick." style='cursor:pointer;width:25%'");
         echo Search::showItem($search->output_type, implode($lineBreak, $counters), $col_num, $row_num, $onclick.$style);
         echo Search::showItem($search->output_type, $history['record_type'], $col_num, $row_num, $onclick.$style);
         echo Search::showItem($search->output_type, $history['result'], $col_num, $row_num, $onclick.$style);
         echo Search::showItem($search->output_type, $history['location'], $col_num, $row_num, $onclick.$style);
         echo Search::showItem($search->output_type, $history['budget'], $col_num, $row_num, $onclick.$style);
         echo Search::showItem($search->output_type, PluginPrintercountersPagecost::getCost($history['record_cost']), $col_num, $row_num, $onclick.$style);
         echo Search::showEndLine($search->output_type);
      }

      // Total
      $row_num++;
      $col_num = 1;
      echo Search::showNewLine($search->output_type);
      $searchopt = [];
      $searchopt = &Search::getOptions($this->getType());
      $count = 0;
      foreach ($searchopt as $val) {
         if (!isset($val['nodisplay']) || !$val['nodisplay']) {
            $count++;
         }
      }
      for ($i = 2; $i < $count; $i++) {
         echo Search::showItem($search->output_type, '', $col_num, $row_num, "class='tab_bg_1'");
      }
      echo Search::showItem($search->output_type, "<b>".__('Total')."</b>", $col_num, $row_num, "class='tab_bg_1'");
      echo Search::showItem($search->output_type, "<b>".Html::formatNumber($input['total_record_cost'])."</b>", $col_num, $row_num, "class='tab_bg_1'");
      echo Search::showEndLine($search->output_type);
   }

   /**
    * Function format record history data
    *
    * @param array $input
    */
   function formatSearchData($input) {

      $searchopt = [];
      $searchopt = &Search::getOptions($this->getType());

      $output = [];
      $types  = [];
      $separator = Search::LBBR;

      foreach ($searchopt as $num => $val) {
         if (is_array($val) && (!isset($val['nosql']) || $val['nosql'] == false)) {
            if ($val['table'] == 'glpi_plugin_printercounters_records' && $val['field'] == 'date') {
               $types['date'] = $num;

            } else if ($val['table'] == 'glpi_plugin_printercounters_records' && $val['field'] == 'id') {
               $types['records_id'] = $num;

            } else if ($val['table'] == 'glpi_plugin_printercounters_recordmodels' && $val['field'] == 'name') {
               $types['recordmodels_name'] = $num;

            } else if ($val['table'] == 'glpi_plugin_printercounters_records' && $val['field'] == 'last_recordmodels_id') {
               $types['recordmodels_id'] = $num;

            } else if ($val['table'] == 'glpi_entities' && $val['field'] == 'name') {
               $types['entities_name'] = $num;

            } else if ($val['table'] == 'glpi_entities' && $val['field'] == 'id') {
               $types['entities_id'] = $num;

            } else if ($val['table'] == 'glpi_plugin_printercounters_countertypes' && $val['field'] == 'name') {
               $types['counters_name'] = $num;

            } else if ($val['table'] == 'glpi_plugin_printercounters_countertypes' && $val['field'] == 'id') {
               $types['countertypes_id'] = $num;

            } else if ($val['table'] == 'glpi_locations' && $val['field'] == 'completename') {
               $types['location'] = $num;

            } else if ($val['table'] == 'glpi_plugin_printercounters_counters' && $val['field'] == 'value') {
               $types['counters_value'] = $num;

            } else if ($val['table'] == 'glpi_plugin_printercounters_records' && $val['field'] == 'record_type') {
               $types['record_type'] = $num;

            } else if ($val['table'] == 'glpi_plugin_printercounters_records' && $val['field'] == 'result') {
               $types['result'] = $num;

            } else if ($val['table'] == 'glpi_plugin_printercounters_pagecosts' && $val['field'] == 'cost') {
               $types['cost'] = $num;

            } else if ($val['table'] == 'glpi_plugin_printercounters_billingmodels' && $val['field'] == 'application_date') {
               $types['application_date'] = $num;

            } else if ($val['table'] == 'glpi_plugin_printercounters_budgets' && $val['field'] == 'name') {
               $types['budget'] = $num;
            }
         }
      }

      if (!empty($input)) {
         $give_item = [];
         $line['raw'] = [];
         foreach ($input as $i => $row) {
            $count = 0;
            $line['raw'] = $row;
            PluginPrintercountersSearch::parseData($line,$this->getType());
            foreach ($searchopt as $num => $val) {
               if (is_array($val) && (!isset($val['nosql']) || $val['nosql'] == false)) {
                  $give_item[$i][$num] = Search::giveItem($this->getType(), $num, $line, $count);
                  $count++;
               }
            }
         }

         $itemtype = strtolower($this->itemtype);

         foreach ($give_item as $row) {
            if (!empty($row[$types['records_id']])) {
               $output[$row[$types['records_id']]]['formated_date']     = $row[$types['date']];
               $output[$row[$types['records_id']]]['date']              = date('Y-m-d H:i:s', strtotime($row[$types['date']]));
               $output[$row[$types['records_id']]]['items_id']          = $this->items_id;
               $output[$row[$types['records_id']]]['itemtype']          = $itemtype;
               $output[$row[$types['records_id']]]['recordmodels_name'] = $row[$types['recordmodels_name']];
               $output[$row[$types['records_id']]]['recordmodels_id']   = $row[$types['recordmodels_id']];
               $output[$row[$types['records_id']]]['entities_name']     = $row[$types['entities_name']];
               $output[$row[$types['records_id']]]['entities_id']       = $row[$types['entities_id']];
               $output[$row[$types['records_id']]]['location']          = $row[$types['location']];
               $output[$row[$types['records_id']]]['record_type']       = $row[$types['record_type']];
               $output[$row[$types['records_id']]]['result']            = $row[$types['result']];
               $output[$row[$types['records_id']]]['budget']            = $row[$types['budget']];

               // Set counters
               $countertypes  = explode((isset($searchopt[$types['countertypes_id']]['splititems']) && $searchopt[$types['countertypes_id']]['splititems'])
                                           ? Search::LBHR
                                           : Search::LBBR, $row[$types['countertypes_id']]);
               $counternames  = explode((isset($searchopt[$types['counters_name']]['splititems']) && $searchopt[$types['counters_name']]['splititems'])
                                           ? Search::LBHR
                                           : Search::LBBR, $row[$types['counters_name']]);
               $countervalues = explode((isset($searchopt[$types['counters_value']]['splititems']) && $searchopt[$types['counters_value']]['splititems'])
                                           ? Search::LBHR
                                           : Search::LBBR, $row[$types['counters_value']]);
               foreach ($countertypes as $key => $countertype) {
                  $output[$row[$types['records_id']]]['counters'][$countertype] = ['counters_name'  => $counternames[$key],
                                                                                        'counters_value' => $countervalues[$key]];
               }
            }
         }
      }

      // Get record costs
      $item_billingmodel = new PluginPrintercountersItem_Billingmodel($this->itemtype, $this->items_id);
      $output = $item_billingmodel->computeRecordCost($output);

      return $output;
   }

   /**
    * Search function : getSearchTitle
    *
    * @return string
    */
   function getSearchTitle() {
      return __('Record history', 'printercounters');
   }


   /**
    * Function Show the record type dropdown
    *
    * @param type $name
    * @param array $options
    * @return type
    */
   static function dropdownRecordType($name = 'record_type', array $options = []) {
      return Dropdown::showFromArray($name, self::getAllRecordTypeArray(), $options);
   }

   /**
    * Function get the record type
    *
    * @param type $value
    * @return type
    */
   static function getRecordType($value) {
      if (!empty($value)) {
         $data = self::getAllRecordTypeArray();
         return $data[$value];
      }
   }

   /**
    * Function Get the record type list
    *
    * @return an array
    */
   static function getAllRecordTypeArray() {

      // To be overridden by class
      $tab = [0                        => Dropdown::EMPTY_VALUE,
                   self::$HOST_ERROR_TYPE   => __('Host error', 'printercounters'),
                   self::$RECORD_ERROR_TYPE => __('Record error', 'printercounters'),
                   self::$AUTOMATIC_TYPE    => __('Automatic', 'printercounters'),
                   self::$MANUAL_TYPE       => __('Manual', 'printercounters')];

      return $tab;
   }

   /**
    * Function Show the state dropdown
    *
    * @return an array
    */
   static function dropdownState($name = 'state', array $options = []) {
      return Dropdown::showFromArray($name, self::getAllStateArray(), $options);
   }

   /**
    * Function get the state
    *
    * @param type $value
    * @return type
    */
   static function getState($value) {
      if (!empty($value)) {
         $data = self::getAllStateArray();
         return $data[$value];
      }
   }

   /**
    * Function Get the state list
    *
    * @return an array
    */
   static function getAllStateArray() {

      // To be overridden by class
      $tab = [0                       => Dropdown::EMPTY_VALUE,
                   self::$PROGRAMMED_STATE => __('Planned', 'printercounters'),
                   self::$FINISHED_STATE   => __('Finished', 'printercounters'),
                   self::$PROGRESS_STATE   => __('In progress', 'printercounters')];

      return $tab;
   }

   /**
    * Function Show the state dropdown
    *
    * @param type $name
    * @param array $options
    * @return type
    */
   static function dropdownResult($name = 'result', array $options = []) {
      return Dropdown::showFromArray($name, self::getAllResultArray(), $options);
   }

   /**
    * Function get the result
    *
    * @param type $value
    * @return type
    */
   static function getResult($value) {
      if (!empty($value)) {
         $data = self::getAllResultArray();
         return $data[$value];
      }
   }

   /**
    * Function Get the state list
    *
    * @return an array
    */
   static function getAllResultArray() {

      // To be overridden by class
      $tab = [0                    => Dropdown::EMPTY_VALUE,
                   self::$SUCCESS       => __('Success', 'printercounters'),
                   self::$IP_FAIL       => __('IP fail', 'printercounters'),
                   self::$OID_FAIL      => __('OID fail', 'printercounters'),
                   self::$UNKNOWN_FAIL  => __('Unknown error', 'printercounters'),
                   self::$MAC_FAIL      => __('MAC fail', 'printercounters'),
                   self::$SYSDESCR_FAIL => __('Sysdescr fail', 'printercounters'),
                   self::$SERIAL_FAIL   => __('Serial fail', 'printercounters')];

      return $tab;
   }



   /**
    * Function init record for items
    *
    * @param string $itemtype
    * @param int $items_id
    * @param int $sonprocess_id
    * @param int $sonprocess_nbr
    * @param array $specific_oid : search specific oids
    */
   function initRecord($itemtype = 'Printer', $items_id = 0, $sonprocess_id = -1, $sonprocess_nbr = -1, $specific_oid = []) {

      $messages                = [];
      $error                   = false;
      $search_results          = [];
      $additional_datas        = [];
      $specific_search_results = [];

      $additional_data = new PluginPrintercountersAdditional_data();

      // Get plugin config
      $config      = new PluginPrintercountersConfig();
      $config_data = $config->getInstance();

      // Get items ip addresses for each processes
      $process      = new PluginPrintercountersProcess($sonprocess_id, $sonprocess_nbr, $itemtype, $items_id);
      $ip_addresses = $process->getIPAddressesForProcess();

      if (!empty($ip_addresses)) {
         // Get SNMP authentication by items
         $snmpauthentication = new PluginPrintercountersSnmpauthentication();
         $snmp_auth          = $snmpauthentication->getItemAuthentication(array_keys($ip_addresses), $itemtype);

         // Get record config by items (timeout, nb retries)
         $item_recordmodel = new PluginPrintercountersItem_Recordmodel();
         $record_config    = $item_recordmodel->getItemRecordConfig(array_keys($ip_addresses), $itemtype);

         // Get record model config for items
         $recordmodel        = new PluginPrintercountersRecordmodel();
         $recordmodel_config = $recordmodel->getRecordModelConfig(array_keys($ip_addresses), $itemtype);

         // Init counters search
         switch (strtolower($itemtype)) {
            case 'printer':
               foreach ($ip_addresses as $printers_id => $cards) {
                  $search_results   = [];
                  $additional_datas = [];
                  foreach ($cards as $addresses) {
                     foreach ($addresses['ip'] as $ip) {
                        if (!empty($ip)) {
                           try {
                              $printer = new PluginPrintercountersPrinter($printers_id,
                                                                          $itemtype,
                                                                          $ip,
                                                                          $addresses['mac'],
                                                                          isset($record_config[$printers_id]) ? $record_config[$printers_id] : [],
                                                                          isset($snmp_auth[$printers_id]) ? $snmp_auth[$printers_id] : []);

                              // Search all oid of the record model
                              if (empty($specific_oid)) {
                                 // Get OIDs
                                 $printer->getOID();
                                 $init_search = $printer->initSearch($recordmodel_config[$printers_id]);
                                 if (isset($init_search['counters']['counters']) && !empty($init_search['counters']['counters'])) {
                                    $search_results[] = $init_search;
                                 }

                                 // Additional datas
                                 if (isset($init_search['counters']['additional_datas']) && !empty($init_search['counters']['additional_datas'])) {
                                    $additional_datas = $init_search;
                                 }

                                 // Search specific oid
                              } else {
                                 foreach ($specific_oid as $oid) {
                                    if (false === $oid) {
                                       $specific_search_results[] = "";
                                    } else {
                                       $specific_search_results[] = $printer->get($oid);
                                    }
                                 }
                              }

                              // Unset mutex / close session
                              $item_recordmodel->unsetMutex($printer->item_recordmodel);
                              $printer->closeSNMPSession();

                           } catch (PluginPrintercountersException $e) {
                              $messages[] = $e->getPrintercountersMessage();
                              $error      = true;
                           }
                        }
                     }
                  }

                  // Set record
                  if (!empty($search_results)) {
                     $search_result_ok = [];
                     foreach ($search_results as $results) {
                        if ($results['record_result'] == self::$SUCCESS) {
                           $search_result_ok[] = $results;
                        }
                     }

                     // If all records are wrong set all them in database !
                     if (empty($search_result_ok)) {
                        // Error handler
                        if ($config_data['enable_error_handler']) {
                           $errorItem = new PluginPrintercountersErrorItem($itemtype, $printers_id);
                           $errorItem->addToErrorItems();
                           // Normal
                        } else {
                           foreach ($search_results as $key => $results) {
                              $this->setRecord($results['counters'], $results['record_result'], $results['record_type'], date('Y-m-d H:i:s', time() + $key));
                           }
                        }
                        // Record sucessful
                     } else {
                        $this->setRecord($search_result_ok[0]['counters'], $search_result_ok[0]['record_result'], $search_result_ok[0]['record_type'], date('Y-m-d H:i:s'));
                     }

                  } else {
                     $messages[] = __('No results, please check OIDs of your record model', 'printercounters').' (itemtype : '.$itemtype.', items_id : '.$printers_id.')';
                     $error      = true;
                  }

                  // Set additional datas
                  if (!empty($additional_datas['counters'])) {
                     $additional_data->setAdditionalData($additional_datas['counters']);
                  }
               }

               // Return specific oid result
               if (!empty($specific_oid)) {
                  if (!empty($specific_search_results)) {
                     $error = false;
                     return [$specific_search_results, $error];
                  } else {
                     $messages[] = __('No results, please check OIDs of your record model', 'printercounters').' (itemtype : '.$itemtype.', items_id : '.$printers_id.')';
                     $error      = true;
                  }
               }
               break;

            default:
               $messages[] = __('This item type is not correct', 'printercounters');
               $error      = true;
               break;
         }
      } else {
         $messages[] = __('No printers to search', 'printercounters');
         $error      = true;
      }

      return [$messages, $error];
   }
   /**
    * Function get records to clean
    *
    * @global type $DB
    * @param type $cleanType
    * @return boolean
    */
   function getRecordsToClean($cleanType, $options = []) {
      global $DB;

      // Get records with all counters to 0
      $output = [];
      $query  = "SELECT `glpi_plugin_printercounters_counters`.`plugin_printercounters_records_id` as records_id
                 FROM glpi_plugin_printercounters_counters
                 LEFT JOIN `glpi_plugin_printercounters_records` 
                    ON (`glpi_plugin_printercounters_counters`.`plugin_printercounters_records_id` = `glpi_plugin_printercounters_records`.`id`)";

      switch ($cleanType) {
         case self::$CLEAN_EMTPY_RECORDS:
            $query .= " WHERE `glpi_plugin_printercounters_records`.`result` = ".self::$SUCCESS."
                        AND `glpi_plugin_printercounters_records`.`record_type` = ".self::$AUTOMATIC_TYPE;

            // Do not delete first records
            $firstRecords = [];
            $queryFirst = "SELECT min(`glpi_plugin_printercounters_records`.`date`) as min_date
                           FROM `glpi_plugin_printercounters_records`
                           GROUP BY  `glpi_plugin_printercounters_records`.`plugin_printercounters_items_recordmodels_id`";
            $resultFirst = $DB->query($queryFirst);
            if ($DB->numrows($resultFirst)) {
               while ($data = $DB->fetchAssoc($resultFirst)) {
                  $firstRecords[] = $data['min_date'];
               }
               $query .= " AND `glpi_plugin_printercounters_records`.`date` NOT IN('".implode("','", $firstRecords)."') ";
            }
            break;

         case self::$CLEAN_ERROR_RECORDS:
            $query .= " WHERE `glpi_plugin_printercounters_records`.`result` != ".self::$SUCCESS."
                        AND `glpi_plugin_printercounters_records`.`record_type` = ".self::$HOST_ERROR_TYPE;
            break;
      }

      if (isset($options['date'])) {
         $query .= " AND `glpi_plugin_printercounters_records`.`date` < '".$options['date']."'";
      }

      $query .= " GROUP BY  `glpi_plugin_printercounters_counters`.`plugin_printercounters_records_id`
                  HAVING SUM(`glpi_plugin_printercounters_counters`.`value`) = 0";

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $output[$data['records_id']] = $data['records_id'];
         }

         return $output;
      }

      return false;
   }

   /**
    * Function init clean records
    *
    * @global type $CFG_GLPI
    * @param type $records
    */
   function initCleanRecords($title, $records) {
      global $CFG_GLPI;

      $_SESSION['plugin_printercounters']['clean_records'] = $records;

      // Launch ajax massive action
      echo "<script type='text/javascript'>";
      echo "printecounters_ajaxMassiveAction('".PLUGIN_PRINTERCOUNTERS_WEBDIR."', 'loadCleanErrorRecords', ".ini_get('max_execution_time').");";
      echo "</script>";

      echo "<table class='tab_cadrehov'>";
      echo "<tr class='tab_bg_1'><th>".$title."</th></tr>";
      echo "<tr class='tab_bg_1'><td><br/>";
      Html::createProgressBar(__('Work in progress...'));
      echo"</td></tr>";
      echo "</table>";
   }

   /**
    * Function clean records
    *
    * @global type $CFG_GLPI
    * @global type $DB
    */
   function cleanRecords() {
      global $CFG_GLPI, $DB;

      if (!empty($_SESSION['plugin_printercounters']['clean_records'])) {
         $success      = 0;
         $totalRecords = count($_SESSION['plugin_printercounters']['clean_records']);
         $records      = array_slice($_SESSION['plugin_printercounters']['clean_records'], 0, 5000);

         $query = "DELETE FROM `glpi_plugin_printercounters_records` WHERE `id` IN ('".implode("','", $records)."');";
         $DB->queryOrDie($query, null);

         $query = "DELETE FROM `glpi_plugin_printercounters_counters` WHERE `plugin_printercounters_records_id` IN ('".implode("','", $records)."');";
         $DB->queryOrDie($query, null);

         // Unset cleaned record from list
         foreach ($records as $id) {
            $success++;
            unset($_SESSION['plugin_printercounters']['clean_records'][$id]);
         }

         // Update progress bar
         Html::changeProgressBarPosition(
               $success,
               $totalRecords,
               round((($success/$totalRecords)*100), 2)." % (".sprintf(__('%s records remaining', 'printercounters'), count($_SESSION['plugin_printercounters']['clean_records'])).")"
         );

         echo "<script type='text/javascript'>";
         echo "printecounters_ajaxMassiveAction('".PLUGIN_PRINTERCOUNTERS_WEBDIR."', 'loadCleanErrorRecords', ".ini_get('max_execution_time').");";
         echo "</script>";

      } else {
         $config = new PluginPrintercountersConfig();
         Html::redirect($config->getFormURL(true));
      }
   }

   /**
    * Function set record for an item
    *
    * @param array    $input         : array of counters array(countertypes_id => counter_value)
    * @param int      $record_result : Sucess, IP error, Mac error, Sysdescr error, Serial error
    * @param int      $record_type   : Host error, Record error
    * @param datetime $date          : record date
    */
   public function setRecord($input, $record_result, $record_type, $date) {

      if (!empty($input['counters'])) {
         if ($records_id = $this->add(['date'                                         => $date,
                                            'result'                                       => $record_result,
                                            'state'                                        => self::$FINISHED_STATE,
                                            'record_type'                                  => $record_type,
                                            'last_recordmodels_id'                         => $input['recordmodels_id'],
                                            'entities_id'                                  => $input['entities_id'],
                                            'locations_id'                                 => $input['locations_id'],
                                            'plugin_printercounters_items_recordmodels_id' => $input['items_recordmodels_id']])) {

            $counter = new PluginPrintercountersCounter();
            foreach ($input['counters'] as $countertypes_recordmodels_id => $counters) {
               foreach ($counters as $counters_id => $value) {
                  $counter->add(['plugin_printercounters_countertypes_recordmodels_id' => $countertypes_recordmodels_id,
                                      'plugin_printercounters_records_id'                   => $records_id,
                                      'value'                                               => $value]);
               }
            }

            return $records_id;
         }
      }

      return false;
   }

   /**
    * Function set first record on item
    *
    * @param type $item_recordmodel
    * @param type $recordmodel
    */
   function setFirstRecord($item_recordmodel, $recordmodel) {

      $records = $this->find(['plugin_printercounters_items_recordmodels_id' => $item_recordmodel]);
      $dbu     = new DbUtils();

      if (empty($records)) {
         $counters = [];

         $countertype_recordmodel = new PluginPrintercountersCountertype_Recordmodel();
         $oid                     = $countertype_recordmodel->getCounterTypes($recordmodel);

         // Set counters to 0
         if (!empty($oid)) {
            foreach ($oid as $val) {
               if ($val['oid_type'] != PluginPrintercountersCountertype_Recordmodel::SERIAL
                  && $val['oid_type'] != PluginPrintercountersCountertype_Recordmodel::SYSDESCR
                     && $val['oid_type'] != PluginPrintercountersCountertype_Recordmodel::NAME
                        && $val['oid_type'] != PluginPrintercountersCountertype_Recordmodel::MODEL
                           && $val['oid_type'] != PluginPrintercountersCountertype_Recordmodel::NUMBER_OF_PRINTED_PAPERS) {
                  $counters['counters'][$val['id']][0] = 0;
               }
            }
         }

         $item = $dbu->getItemForItemtype($this->itemtype);
         $item->getFromDB($this->items_id);

         $counters['items_recordmodels_id'] = $item_recordmodel;
         $counters['recordmodels_id']       = $recordmodel;
         $counters['entities_id']           = $item->fields["entities_id"];
         $counters['locations_id']          = $item->fields["locations_id"];

         return $this->setRecord($counters, self::$SUCCESS, self::$AUTOMATIC_TYPE, date('Y-m-d H:i:s', time()));
      }

      return false;
   }

   /**
    * Function update a record
    *
    * @param array $input         : array of counters array(countertypes_id => counter_value)
    * @param int   $record_result : Sucess, IP error, Mac error, Sysdescr error, Serial error
    * @param int   $record_type   : Host error, Record error
    * @param int   $records_id    : id of the record
    */
   public function updateRecord($input, $record_result, $record_type, $records_id) {

      if (!empty($input['counters'])) {
         if ($this->update(['id'          => $records_id,
                                 'result'      => $record_result,
                                 'entities_id' => $input['entities_id'],
                                 'state'       => self::$FINISHED_STATE,
                                 'record_type' => $record_type])) {

            $counter = new PluginPrintercountersCounter();
            foreach ($input['counters'] as $countertypes_recordmodels_id => $counters) {
               foreach ($counters as $counters_id => $value) {
                  if ($counters_id > 0) {
                     $counter->update(['id' => $counters_id, 'value' => $value]);
                  } else {
                     $counter->add(['value'                                               => $value,
                                         'plugin_printercounters_records_id'                   => $records_id,
                                         'plugin_printercounters_countertypes_recordmodels_id' => $countertypes_recordmodels_id]);
                  }
               }
            }
         }
      }
   }

   /**
    * Function get records for an item
    *
    * @global type $DB
    * @param int $items_id
    * @param string $itemtype
    * @param array $options :
    *                             - int      records_id   : record ID
    *                             - bool     last_record  : get last record
    *                             - bool     next_record  : get first record
    *                             - date     record_date  : get last record or first record according to a date
    *                             - string   order        : add order condition
    *                             - string   condition    : add where condition
    * @return array
    */
   function getRecords($items_id, $itemtype, $options = []) {
      global $DB;

      $params['condition']   = "";
      $params['sub_condition'] = "";
      $params['last_record'] = false;
      $params['next_record'] = false;
      $params['record_date'] = null;
      $params['order']       = null;
      $params['records_id']  = 0;

      foreach ($options as $key => $val) {
         $params[$key] = $val;
      }
      $dbu = new DbUtils();

      $itemjoin  = $dbu->getTableForItemType($itemtype);
      $itemjoin1 = $dbu->getTableForItemType("PluginPrintercountersCounter");
      $itemjoin2 = $dbu->getTableForItemType("PluginPrintercountersItem_Recordmodel");
      $itemjoin3 = $dbu->getTableForItemType("PluginPrintercountersCountertype_Recordmodel");
      $itemjoin4 = $dbu->getTableForItemType("PluginPrintercountersCountertype");

      $output = [];

      $query = "SELECT `".$itemjoin1."`.`id` as counters_id, 
                       `".$itemjoin1."`.`value` as counters_value, 
                       `".$itemjoin2."`.`items_id`, 
                       `".$itemjoin2."`.`itemtype`, 
                       `".$itemjoin4."`.`name` as counters_name, 
                       `".$itemjoin4."`.`id` as countertypes_id,
                       `".$itemjoin3."`.`id` as countertypes_recordmodels_id,
                       `".$itemjoin3."`.`oid_type`,
                       `".$this->getTable()."`.`date`,
                       `".$this->getTable()."`.`record_type`,
                       `".$this->getTable()."`.`entities_id`,
                       `".$this->getTable()."`.`last_recordmodels_id` as recordmodels_id,
                       `".$this->getTable()."`.`id` as records_id,
                       `".$this->getTable()."`.`result`
          FROM ".$this->getTable()."
          LEFT JOIN `".$itemjoin2."` 
             ON (`".$itemjoin2."`.`id` = `".$this->getTable()."`.`plugin_printercounters_items_recordmodels_id`)
          LEFT JOIN `".$itemjoin."` 
             ON (`".$itemjoin."`.`id` = `".$itemjoin2."`.`items_id`  AND LOWER(`".$itemjoin2."`.`itemtype`) = '".strtolower($itemtype)."')
          LEFT JOIN `".$itemjoin1."` 
             ON (`".$itemjoin1."`.`plugin_printercounters_records_id` = `".$this->getTable()."`.`id`)
          LEFT JOIN `".$itemjoin3."` 
             ON (`".$itemjoin1."`.`plugin_printercounters_countertypes_recordmodels_id` = `".$itemjoin3."`.`id`)
          LEFT JOIN `".$itemjoin4."` 
             ON (`".$itemjoin3."`.`plugin_printercounters_countertypes_id` = `".$itemjoin4."`.`id`)
          WHERE 1 ";

      $query .= $params['condition'];

      if ($items_id > 0) {
         $query .= " AND `".$itemjoin2."`.`items_id` = ".Toolbox::cleanInteger($items_id);
      }

      if (!empty($itemtype)) {
         $query .= " AND LOWER(`".$itemjoin2."`.`itemtype`) = '".strtolower($itemtype)."'";
      }

      if ($params['records_id'] > 0) {
         $query .= " AND `".$this->getTable()."`.`id` = '".$params['records_id']."'";
      }

      // Get last record
      if ($params['last_record']) {
         $query .= " AND `".$this->getTable()."`.`date` IN (SELECT max(`".$this->getTable()."`.`date`) FROM ".$this->getTable();
         $query .= " LEFT JOIN `".$itemjoin2."` 
                        ON (`".$itemjoin2."`.`id` = `".$this->getTable()."`.`plugin_printercounters_items_recordmodels_id`)";
         $query .= " WHERE `".$itemjoin2."`.`items_id` = ".Toolbox::cleanInteger($items_id);
         $query .= " ".$params['sub_condition'];
         if (!empty($params['record_date'])) {
            $query .= " AND `".$this->getTable()."`.`date` < '".$params['record_date']."'";
         }
         $query .= ")";
      }

      // Get first record
      if ($params['next_record']) {
         $query .= " AND `".$this->getTable()."`.`date` IN (SELECT min(`".$this->getTable()."`.`date`) FROM ".$this->getTable();
         $query .= " LEFT JOIN `".$itemjoin2."` 
                        ON (`".$itemjoin2."`.`id` = `".$this->getTable()."`.`plugin_printercounters_items_recordmodels_id`)";
         $query .= " WHERE `".$itemjoin2."`.`items_id` = ".Toolbox::cleanInteger($items_id);
         $query .= " ".$params['sub_condition'];
         if (!empty($params['record_date'])) {
            $query .= " AND `".$this->getTable()."`.`date` > '".$params['record_date']."'";
         }
         $query .= ")";
      }

      // Get order
      if ($params['order'] != null) {
         $query .= " ORDER BY ".$params['order'];
      } else {
         $query .= " ORDER BY `".$this->getTable()."`.`date`";
      }

      $itemtype = strtolower($itemtype);

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         if ($items_id > 0) {
            while ($data = $DB->fetchAssoc($result)) {
               $output[$data['records_id']]['recordmodels_id']                    = $data['recordmodels_id'];
               $output[$data['records_id']]['items_id']                           = $data['items_id'];
               $output[$data['records_id']]['itemtype']                           = $itemtype;
               $output[$data['records_id']]['date']                               = $data['date'];
               $output[$data['records_id']]['record_type']                        = $data['record_type'];
               $output[$data['records_id']]['entities_id']                        = $data['entities_id'];
               $output[$data['records_id']]['result']                             = $data['result'];
               $output[$data['records_id']]['counters'][$data['countertypes_id']] = ['counters_name'                => $data['counters_name'],
                                                                                          'counters_value'               => $data['counters_value'],
                                                                                          'counters_id'                  => $data['counters_id'],
                                                                                          'oid_type'                     => $data['oid_type'],
                                                                                          'countertypes_recordmodels_id' => $data['countertypes_recordmodels_id']];
            }
         } else {
            while ($data = $DB->fetchAssoc($result)) {
               $output[$itemtype][$data['items_id']][$data['records_id']]['recordmodels_id']                    = $data['recordmodels_id'];
               $output[$itemtype][$data['items_id']][$data['records_id']]['items_id']                           = $data['items_id'];
               $output[$itemtype][$data['items_id']][$data['records_id']]['itemtype']                           = $itemtype;
               $output[$itemtype][$data['items_id']][$data['records_id']]['date']                               = $data['date'];
               $output[$itemtype][$data['items_id']][$data['records_id']]['entities_id']                        = $data['entities_id'];
               $output[$itemtype][$data['items_id']][$data['records_id']]['result']                             = $data['result'];
               $output[$itemtype][$data['items_id']][$data['records_id']]['counters'][$data['countertypes_id']] = ['counters_name'                => $data['counters_name'],
                                                                                                                        'counters_value'               => $data['counters_value'],
                                                                                                                        'counters_id'                  => $data['counters_id'],
                                                                                                                        'oid_type'                     => $data['oid_type'],
                                                                                                                        'countertypes_recordmodels_id' => $data['countertypes_recordmodels_id']];
            }
         }
      }

      return $output;
   }

   /**
    * Function init immediate record for an item
    *
    * @param string $items_id
    * @param int $itemtype
    */
   function immediateRecord($items_id, $itemtype) {
      return $this->initRecord($itemtype, $items_id);
   }

   /**
    * Function init manual record in a modal window
    *
    * @global type $CFG_GLPI
    * @param array $options
    */
   function manualRecord($options = []) {
      global $CFG_GLPI;

      $p['ontop']         = false;
      $p['num_displayed'] = -1;
      $p['fixed']         = false;
      $p['extraparams']   = [];
      $p['width']         = 800;
      $p['height']        = 400;
      $p['rand']          = mt_rand();
      $p['title']         = __('Printer counters', 'printercounters');
      $p['url']           = PLUGIN_PRINTERCOUNTERS_WEBDIR."/ajax/record.php";

      foreach ($options as $key => $val) {
         if (isset($p[$key])) {
            $p[$key] = $val;
         }
      }

      echo Ajax::createIframeModalWindow('manual_record_window',
                                         $p['url'],
                                         ['title'   => $p['title'],
                                          'display' => false]);

//      PluginPrintercountersAjax::createFixedModalWindow('manual_record_window', ['title'       => $p['title'],
//
//                                                                 'extraparams' => $p['extraparams'],
//                                                                 'width'       => $p['width'],
//                                                                 'height'      => $p['height']]);
   }

   /**
    * Function show manual record form
    *
    * @global type $CFG_GLPI
    * @param type $items_id
    * @param type $itemtype
    * @param type $records_id
    * @param type $rand
    */
   function showManualRecord($items_id, $itemtype, $records_id, $rand = null, $addLowerRecord = 0) {
      global $CFG_GLPI;

      echo "<form id='manual_record_form' style='padding:10px;text-align:center;'>";
      echo "<div id='manual_record_error' style='padding:10px;text-align:center;'></div>";

      echo "<table class='tab_cadre'>";
      echo "<tr>";
      if (!$addLowerRecord) {
         echo "<th colspan='2'>".__('Add a manual record', 'printercounters')."</th>";
      } else {
         if ($records_id > 0) {
            echo "<th colspan='2'>".__('Update record', 'printercounters')."</th>";
         } else {
            echo "<th colspan='2'>".__('Add a lower record', 'printercounters')."</th>";
         }
      }
      echo "</tr>";
      echo "<tr>";
      echo "<th>".PluginPrintercountersCountertype::getTypeName()."</th>";
      echo "<th>".__('Counter value', 'printercounters')."</th>";
      echo "</tr>";

      $item_recordmodel       = new PluginPrintercountersItem_Recordmodel($itemtype, $items_id);
      $item_recordmodels_data = $item_recordmodel->getItem_RecordmodelForItem();
      $item_recordmodels_data = reset($item_recordmodels_data);

      // UPDATE
      if ($records_id > 0) {
         $records = $this->getRecords($items_id, $itemtype, ['records_id' => $records_id,
             'order'      => "`glpi_plugin_printercounters_countertypes`.`name` ASC"]);

         // Fill counters if recordmodel has changed
         $recordmodels_data        = current($records);
         $countertype_recordmodels = new PluginPrintercountersCountertype_Recordmodel();
         $records                  = $countertype_recordmodels->fillCountersGap($records, [$recordmodels_data['recordmodels_id']]);

         foreach ($records as $record) {
            foreach ($record['counters'] as $counter) {
               echo "<tr class='tab_bg_1'>";
               echo "<td>".$counter['counters_name'].'</td>';
               echo '<td><input class="form-control" type="text" value="'.$counter['counters_value'].'" name="counters[counters]['.$counter['countertypes_recordmodels_id'].']['.$counter['counters_id'].']">
                         <input type="hidden" value="'.$record['date'].'" name="counters[date]"></td>';
               echo "</tr>";
            }
         }

         // Entity
         echo "<tr>";
         echo "<th colspan='2'>".__('Entity')."</th>";
         echo "</tr>";
         echo "<tr class='tab_bg_2'>";
         echo "<td>";
         echo __('Record entity', 'printercounters');
         echo "</td>";
         echo "<td>";
         Dropdown::show('Entity', ['name'        => 'counters[entities_id]',
                                        'entity'      => $_SESSION['glpiactive_entity'],
                                        'value'       => $item_recordmodels_data['entities_id'],
                                        'entity_sons' => true]);
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_2'>";
         echo "<td colspan='2' class='center'>";
         $onclick = "onclick='printercountersAction.printercountersActions(\"".PLUGIN_PRINTERCOUNTERS_WEBDIR."\", \"setManualRecord\", \"manual_record_form\", \"manual_record_error\", 
               ".json_encode(['items_id'        => $items_id,
                                   'itemtype'        => $itemtype,
                                   'addLowerRecord'  => $addLowerRecord,
                                   'records_id'      => $records_id,
                                   'formName'        => 'search_form'.$rand,
                                   'updates'         => ['record' => 'history_showForm'.$rand]]).");'";
         echo "<input type='button' $onclick class='submit btn btn-primary' value='"._sx('button', 'Update')."'>";
         echo "</td></tr>";

         //ADD
      } else {
         $countertype_recordmodel = new PluginPrintercountersCountertype_Recordmodel();
         $counters                = $countertype_recordmodel->getRecordmodelCountersForItem($items_id, $itemtype, "`glpi_plugin_printercounters_countertypes`.`id` ASC");
         foreach ($counters as $counter) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>".$counter['counters_name'].'</td>';
            echo '<td><input type="text"class="form-control" value="" name="counters[counters]['.$counter['countertypes_recordmodels_id'].'][0]"></td>';
            echo "</tr>";
         }

         echo "<tr class='tab_bg_2'>";
         echo "<td colspan='2' class='center'>";
         $onclick = "onclick='printercountersAction.printercountersActions(\"".PLUGIN_PRINTERCOUNTERS_WEBDIR."\", \"setManualRecord\", \"manual_record_form\", \"manual_record_error\", 
               ".json_encode(['items_id'        => $items_id,
                                   'itemtype'        => $itemtype,
                                   'addLowerRecord'  => $addLowerRecord,
                                   'records_id'      => $records_id,
                                   'formName'        => 'search_form'.$rand,
                                   'updates'         => ['record' => 'history_showForm'.$rand]]).");'";
         echo "<input type='button' $onclick class='submit btn btn-primary' value='"._sx('button', 'Add')."'>";
         echo "<input type='hidden' value='".$item_recordmodels_data['entities_id']."' name='counters[entities_id]'>";
         echo "</td></tr>";
      }

      echo "</table>";
      echo "<input type='hidden' value='".$item_recordmodels_data['id']."' name='counters[items_recordmodels_id]'>";
      echo "<input type='hidden' value='".$item_recordmodels_data['plugin_printercounters_recordmodels_id']."' name='counters[recordmodels_id]'>";
      echo "<input type='hidden' value='".$item_recordmodels_data['locations_id']."' name='counters[locations_id]'>";
      Html::closeForm();
   }

   /**
    * Function set manual record
    *
    * @param int $items_id
    * @param string $itemtype
    * @param array $counters
    * @param int $records_id
    */
   function setManualRecord($items_id, $itemtype, $counters, $records_id, $addLowerRecord = 0) {

      // Check if counters are not lower than previous
      if (!$addLowerRecord) {
         list($messages, $error) = $this->checkValues($items_id, $itemtype, $counters, 'last_record');
         if ($error) {
            return [$messages, $error];
         }
      }

      // UPDATE
      if ($records_id > 0) {
         // Check if counters are not higher than the next
         if (!$addLowerRecord) {
            list($messages, $error) = $this->checkValues($items_id, $itemtype, $counters, 'next_record');
            if ($error) {
               return [$messages, $error];
            }
         }

         // Check if counters are numeric
         list($messages, $error) = $this->checkValues($items_id, $itemtype, $counters, 'numeric_or_empty_counters');
         if ($error) {
            return [$messages, $error];
         }
         $this->updateRecord($counters, self::$SUCCESS, self::$MANUAL_TYPE, $records_id);

         // ADD
      } else {
         // Check if counters are numeric
         list($messages, $error) = $this->checkValues($items_id, $itemtype, $counters, 'numeric_or_empty_counters');
         if ($error) {
            return [$messages, $error];
         }
         $this->setRecord($counters, self::$SUCCESS, self::$MANUAL_TYPE, date('Y-m-d H:i:s'));
      }

      return [$messages, $error];
   }

   /**
    * Function update total number of pages for item
    *
    * @param int $items_id
    * @param string $itemtype
    */
   function updatePrinterData($items_id, $itemtype) {
      $dbu  = new DbUtils();
      $item = $dbu->getItemForItemtype($itemtype);

      $recordmodel       = new PluginPrintercountersCountertype_Recordmodel();
      $oid_model         = $recordmodel->getOIDRecordmodelCountersForItem($items_id, $itemtype, PluginPrintercountersCountertype_Recordmodel::MODEL);
      $oid_serial        = $recordmodel->getOIDRecordmodelCountersForItem($items_id, $itemtype, PluginPrintercountersCountertype_Recordmodel::SERIAL);
      $oid_number_papers = $recordmodel->getOIDRecordmodelCountersForItem($items_id, $itemtype, PluginPrintercountersCountertype_Recordmodel::NUMBER_OF_PRINTED_PAPERS);

      $specific_oid = [$oid_number_papers, $oid_model, $oid_serial];
      // Init record for specific OID
      list($result, $error) = $this->initRecord($itemtype, $items_id, -1, -1, $specific_oid);
      if ($error) {
         return [$result, $error];
      }

      // Check value
      list($messages, $error) = $this->checkValues($items_id, $itemtype, $result[0], 'counter_position');
      if ($error) {
         return [$messages, $error];
      }

      $model_printer = new PrinterModel();
      if ($model_printer->getFromDBByCrit(['name' => $result[1]])) {
         $model_printer_id = $model_printer->getID();
      } else {
         // add
         $model_printer_id = $model_printer->add(['name' => $result[1]]);
      }

      $item->update(['id'                 => $items_id,
                          'last_pages_counter' => $result[0],
                          'printermodels_id'   => $model_printer_id,
                          'serial'             => $result[2]]);

      return [$result, $error];
   }

   /**
    * Function update global tco of item
    */
   function updateGlobalTco() {

      $result  = null;
      $message = null;
      $error   = false;

      // Get current TCO of item
      $tco = $this->getItemTco();

      // Get all records cost for this item
      $record = new PluginPrintercountersRecord($this->itemtype, $this->items_id);
      $search  = new PluginPrintercountersSearch();
      $search->showSearch($record, ['display' => false]);
      $records = $record->formatSearchData($search->input);

      // Update TCO
      $item_recordmodel = new PluginPrintercountersItem_Recordmodel($this->itemtype, $this->items_id);
      $item_recordmodel->getFromDBByCrit(['itemtype' => ['LIKE', $this->itemtype],
                                          'items_id' => $this->items_id]);

      if ($item_recordmodel->update(['id' => $item_recordmodel->getField('id'),
                                     'global_tco' => ($records['total_record_cost'] + $tco)])) {
         $result = Html::formatNumber($records['total_record_cost'] + $tco);
      } else {
         $error   = true;
         $message = __('TCO update error', 'printercounters');
      }

      return [$message, $result, $error];
   }

   /**
    * Function get items tco
    */
   function getItemTco() {
      global $DB;

      $output = [];
      $dbu    = new DbUtils();

      $itemjoin  = $dbu->getTableForItemType($this->itemtype);
      $itemjoin2 = $dbu->getTableForItemType('Infocom');

      $query = "SELECT `".$itemjoin2."`.`value` + `".$itemjoin."`.`ticket_tco` as tco
          FROM ".$itemjoin."
          LEFT JOIN `$itemjoin2` 
             ON (`".$itemjoin2."`.`items_id` = `$itemjoin`.`id` 
             AND LOWER(`".$itemjoin2."`.`itemtype`) = LOWER('".$this->itemtype."'))
          WHERE `".$itemjoin."`.`id` = ".Toolbox::cleanInteger($this->items_id);

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            return $data['tco'];
         }
      }

      return $output;
   }

   /**
    * Function checks record values
    *
    *
    * @param int $items_id
    * @param string $itemtype
    * @param array $input
    * @param string $type : couters, last record, counter position
    * @return boolean
    */
   function checkValues($items_id, $itemtype, $input, $type) {
      $msg     = [];
      $checkKo = false;

      if (!empty($input)) {
         switch ($type) {
            case 'counters':
               if (!empty($input['counters'])) {
                  foreach ($input['counters'] as $counters) {
                     foreach ($counters as $counters_id => $value) {
                        if (empty($value) || !is_numeric($value)) {
                           $msg[]   = __('Error : Counters are not valid', 'printercounters');
                           $checkKo = true;
                           break 2;
                        }
                     }
                  }
               }
               break;

            case 'numeric_or_empty_counters':
               if (!empty($input['counters'])) {
                  foreach ($input['counters'] as $counters) {
                     foreach ($counters as $counters_id => $value) {
                        if (!is_numeric($value)) {
                           $msg[]   = __('Error : Counters are not valid', 'printercounters');
                           $checkKo = true;
                           break 2;
                        }
                     }
                  }
               }
               break;

            case 'last_record':
               $record_date = null;
               if (isset($input['date'])) {
                  $record_date = $input['date'];
               }
               $last_record = $this->getRecords($items_id, $itemtype, ['last_record' => true,
                   'record_date' => $record_date]);

               if (!empty($last_record) && !empty($input['counters'])) {
                  foreach ($last_record as $record) {
                     foreach ($record['counters'] as $last_counter) {
                        foreach ($input['counters'] as $countertypes_recordmodels_id => $counters) {
                           foreach ($counters as $value) {
                              if (($last_counter['countertypes_recordmodels_id'] == $countertypes_recordmodels_id)
                                  && $value < $last_counter['counters_value']) {
                                 $msg[]   = __('Error : Counters cannot be lower than the previous', 'printercounters');
                                 $checkKo = true;
                                 break 3;
                              }
                           }
                        }
                     }
                  }
               }
               break;

            case 'next_record':
               $record_date = null;
               if (isset($input['date'])) {
                  $record_date = $input['date'];
               }
               $next_record = $this->getRecords($items_id, $itemtype, ['next_record' => true,
                   'record_date' => $record_date]);
               if (!empty($next_record) && !empty($input['counters'])) {
                  foreach ($next_record as $record) {
                     foreach ($record['counters'] as $next_counter) {
                        foreach ($input['counters'] as $countertypes_recordmodels_id => $counters) {
                           foreach ($counters as $counters_id => $value) {
                              if ($next_counter['countertypes_recordmodels_id'] == $countertypes_recordmodels_id && $value > $next_counter['counters_value']) {
                                 $msg[]   = __('Error : Counters cannot be higher than the next', 'printercounters');
                                 $checkKo = true;
                                 break 3;
                              }
                           }
                        }
                     }
                  }
               }
               break;

            case 'counter_position':
               if (!is_numeric($input)) {
                  $msg[]   = __('Error : Counter position is not valid', 'printercounters');
                  $checkKo = true;
               }
               break;
         }
      }

      return [array_unique($msg), $checkKo];
   }

   /**
    * Show general records config
    *
    * @param type $config
    */
   function showRecordConfig($config) {

      if (!$this->canCreate()) {
         return false;
      }

      $width = 75;

      echo "<form name='form' method='post' action='".
      Toolbox::getItemTypeFormURL('PluginPrintercountersConfig')."'>";

      echo "<div align='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>".__('Item records', 'printercounters')."</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Disable automatic records', 'printercounters');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo('disable_autosearch', $config['disable_autosearch'], -1, ['width' => $width]);
      echo "</td>";

      echo "<td>";
      echo __('Set first record when record model change', 'printercounters');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo('set_first_record', $config['set_first_record'], -1, ['width' => $width]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Enable toner level alert', 'printercounters')."</td><td>";
      $rand = Dropdown::showYesNo('enable_toner_alert', $config['enable_toner_alert'], -1, ['on_change' => 'enableAlertConfig();', 'width' => $width]);
      echo "<script type='text/javascript'>";
      echo "function enableAlertConfig(){";
      echo "   $(document).ready(function() {
                  var enable = $('#dropdown_enable_toner_alert$rand').val();
                  if (enable == '1') {
                     $('#enable_alert_config').css({'display' : 'table'});
                  } else {
                     $('#enable_alert_config').css({'display' : 'none'});
                  }
               });";
      echo "}";
      if ($config['enable_toner_alert']) {
         echo "enableAlertConfig();";
      }
      echo "</script>";
      echo "</td>";
      echo "<td colspan='2'></td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td colspan='4'>";
      echo "<table class='tab_cadre' style='margin:0px;display:none;' id='enable_alert_config'>";
      echo "<tr><th colspan='2'>".__('Toner level alert', 'printercounters')."</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Reminders frequency for printer toner level', 'printercounters')."</td><td>";
      Alert::dropdown(['name'      => 'toner_alert_repeat',
                            'value'     => $config['toner_alert_repeat']]);

      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Toner alert treshold', 'printercounters')."</td>";
      echo "<td>";
      Dropdown::showNumber("toner_treshold", ['value' => $config['toner_treshold'],
                                                    'min'   => 0,
                                                    'max'   => 100,
                                                    'width' => $width]);
      echo " % ";
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo" <td class='tab_bg_2 center' colspan='6'>";
      echo Html::submit(_sx('button', 'Update'), ['name' => 'update_config', 'class' => 'btn btn-primary']);
      echo "</td>";
      echo "</tr>";
      echo "</table></div>";
      Html::closeForm();

      // Record cleaning
      echo "<form name='form' method='post' action='".
      Toolbox::getItemTypeFormURL('PluginPrintercountersConfig')."' onsubmit='return printercounters_clean_records();'>";
      echo "<div align='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'><th colspan='2'>".__('Cleaning', 'printercounters')."</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo" <td class='right'>";
      echo __('Delete records before', 'printercounters');
      echo "</td>";
      echo "<td class='left'>";
      Html::showDateTimeField('date', ['value' => date('Y-m-d H:i:s', strtotime("- 3 MONTH"))]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo" <td class='center'>";
      echo Html::submit(__('Clean records in error', 'printercounters'), ['name' => 'clean_error_records', 'class' => 'btn btn-primary']);
      echo "</td>";
      echo" <td class='center'>";
      echo Html::submit(__('Clean empty records', 'printercounters'), ['name' => 'clean_empty_records', 'class' => 'btn btn-primary']);
      echo "</td>";
      echo "</tr>";
      echo "</table></div>";
      echo "<script type='text/javascript'>
               function printercounters_clean_records(){
                  if (window.confirm('".__('Do you want to clean all records ?', 'printercounters') . "')){
                     return true;
                  } else {
                     return false;
                  }
               }
            </script>";

      Html::closeForm();
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

      $tab = [];
      $dbu = new DbUtils();

      $itemtype = $this->itemtype;
      $item     = $dbu->getItemForItemtype($itemtype);

      $tab[] = [
         'id'                 => '79',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => $this->getFieldName('record_id'),
         'datatype'           => 'numeric',
         'massiveaction'      => false,
         'nosearch'           => true,
         'nodisplay'          => '1',
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '80',
         'table'              => $this->getTable(),
         'field'              => 'date',
         'name'               => $this->getFieldName('date'),
         'datatype'           => 'datetime',
         'massiveaction'      => false,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '81',
         'table'              => 'glpi_plugin_printercounters_recordmodels',
         'field'              => 'name',
         'linkfield'          => 'last_recordmodels_id',
         'name'               => $this->getFieldName('recordmodels'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '82',
         'table'              => 'glpi_entities',
         'field'              => 'name',
         'name'               => $this->getFieldName('entities_id'),
         'massiveaction'      => false,
         'datatype'           => 'itemlink',
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '83',
         'table'              => 'glpi_plugin_printercounters_countertypes',
         'field'              => 'name',
         'name'               => $this->getFieldName('counters_name'),
         'datatype'           => 'dropdown',
         'massiveaction'      => false,
         'nosearch'           => true,
         'nosort'             => true,
         'forcegroupby'       => true,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_plugin_printercounters_countertypes_recordmodels',
               'joinparams'         => [
                  'beforejoin'         => [
                     'table'              => 'glpi_plugin_printercounters_counters',
                     'joinparams'         => [
                        'jointype'           => 'child',
                        'beforejoin'         => [
                           'table'              => $this->getTable()
                        ]
                     ]
                  ]
               ]
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '84',
         'table'              => 'glpi_plugin_printercounters_counters',
         'field'              => 'value',
         'name'               => $this->getFieldName('counters_value'),
         'datatype'           => 'dropdown',
         'massiveaction'      => false,
         'joinparams'         => [
            'jointype'           => 'child'
         ],
         'nosearch'           => true,
         'forcegroupby'       => true,
         'nosort'             => true,
         'splititems'         => '1'
      ];

      $tab[] = [
         'id'                 => '85',
         'table'              => $this->getTable(),
         'field'              => 'record_type',
         'name'               => $this->getFieldName('record_type'),
         'datatype'           => 'specific',
         'searchequalsonfield' => '1',
         'searchtype'         => 'equals',
         'massiveaction'      => false,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '86',
         'table'              => $this->getTable(),
         'field'              => 'result',
         'name'               => $this->getFieldName('result'),
         'datatype'           => 'specific',
         'searchequalsonfield' => '1',
         'searchtype'         => 'equals',
         'massiveaction'      => false,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '87',
         'table'              => 'glpi_locations',
         'field'              => 'completename',
         'name'               => $this->getFieldName('locations_id'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
         'nosort'             => true,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_printers',
               'linkfield'          => 'items_id',
               'joinparams'         => [
                  'beforejoin'         => [
                     'table'              => 'glpi_plugin_printercounters_items_recordmodels'
                  ]
               ]
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '88',
         'table'              => 'glpi_plugin_printercounters_budgets',
         'field'              => 'name',
         'name'               => $this->getFieldName('budgets_id'),
         'massiveaction'      => false,
         'datatype'           => 'itemlink',
         'linkfield'          => 'entities_id',
         'nosort'             => true,
         'joinparams'         => [
            'condition'          => 'AND NEWTABLE.`begin_date` <= REFTABLE.`date` AND NEWTABLE.`end_date` >= REFTABLE.`date`'
         ]
      ];

      $tab[] = [
         'id'                 => '89',
         'table'              => $dbu->getTableForItemType($itemtype),
         'field'              => 'name',
         'name'               => $item::getTypeName(),
         'datatype'           => 'dropdown',
         'massiveaction'      => false,
         'linkfield'          => 'items_id',
         'nosearch'           => true,
         'nodisplay'          => '1',
         'nosort'             => true,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_plugin_printercounters_items_recordmodels'
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '91',
         'table'              => $dbu->getTableForItemType($itemtype),
         'field'              => 'id',
         'name'               => $item::getTypeName().' ID',
         'datatype'           => 'number',
         'massiveaction'      => false,
         'linkfield'          => 'items_id',
         'nosearch'           => true,
         'nodisplay'          => '1',
         'nosort'             => true,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_plugin_printercounters_items_recordmodels'
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '92',
         'table'              => $dbu->getTableForItemType($this->itemtype.'Model'),
         'field'              => 'name',
         'name'               => __('Model'),
         'massiveaction'      => false,
         'datatype'           => 'dropdown',
         'nosearch'           => true,
         'nodisplay'          => '1',
         'nosort'             => true,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => $dbu->getTableForItemType($itemtype),
               'linkfield'          => 'items_id',
               'joinparams'         => [
                  'beforejoin'         => [
                     'table'              => 'glpi_plugin_printercounters_items_recordmodels'
                  ]
               ]
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '93',
         'table'              => $this->getTable(),
         'field'              => 'last_recordmodels_id',
         'name'               => $this->getFieldName('last_recordmodels_id'),
         'datatype'           => 'number',
         'massiveaction'      => false,
         'nosearch'           => true,
         'nodisplay'          => '1',
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '94',
         'table'              => 'glpi_plugin_printercounters_countertypes',
         'field'              => 'id',
         'name'               => $this->getFieldName('counters_name'),
         'datatype'           => 'number',
         'massiveaction'      => false,
         'nosearch'           => true,
         'nodisplay'          => '1',
         'nosort'             => true,
         'forcegroupby'       => true,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_plugin_printercounters_countertypes_recordmodels',
               'joinparams'         => [
                  'beforejoin'         => [
                     'table'              => 'glpi_plugin_printercounters_counters',
                     'joinparams'         => [
                        'jointype'           => 'child',
                        'beforejoin'         => [
                           'table'              => $this->getTable()
                        ]
                     ]
                  ]
               ]
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '95',
         'table'              => 'glpi_plugin_printercounters_items_recordmodels',
         'field'              => 'itemtype',
         'name'               => __('Type'),
         'massiveaction'      => false,
         'nosearch'           => true,
         'nodisplay'          => '1',
         'nosql'              => '1',
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '96',
         'table'              => 'glpi_plugin_printercounters_recordmodels',
         'field'              => 'name',
         'name'               => $this->getFieldName('recordmodels'),
         'datatype'           => 'dropdown',
         'massiveaction'      => false,
         'nosearch'           => true,
         'nodisplay'          => '1',
         'nosql'              => '1',
         'nosort'             => true,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_plugin_printercounters_items_recordmodels'
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '97',
         'table'              => 'glpi_plugin_printercounters_pagecosts',
         'field'              => 'cost',
         'name'               => $this->getFieldName('cost'),
         'datatype'           => 'specific',
         'massiveaction'      => false,
         'nosearch'           => true,
         'nosql'              => '1',
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '98',
         'table'              => 'glpi_plugin_printercounters_items_recordmodels',
         'field'              => 'id',
         'name'               => __('ID'),
         'datatype'           => 'number',
         'massiveaction'      => false,
         'nosearch'           => true,
         'nodisplay'          => '1',
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '99',
         'table'              => 'glpi_entities',
         'field'              => 'id',
         'name'               => __('Entity'),
         'massiveaction'      => false,
         'nosearch'           => true,
         'nodisplay'          => '1',
         'datatype'           => 'dropdown',
         'nosort'             => true
      ];

      return $tab;
   }

   /**
    * getFieldName
    *
    * @param type $field
    * @return type
    */
   function getFieldName($field) {

      switch ($field) {
         case 'date':                  return __('Date');
         case 'entities_id':           return __('Entity');
         case 'record_type':           return __('Record type', 'printercounters');
         case 'recordmodels':          return PluginPrintercountersRecordmodel::getTypeName();
         case 'result':                return __('Result', 'printercounters');
         case 'record_id':             return __('Record ID', 'printercounters');
         case 'locations_id':          return __('Location');
         case 'counters_name':         return __('Counter type', 'printercounters');
         case 'counters_value':        return __('Counter value', 'printercounters');
         case 'budgets_id':            return __('Budget');
         case 'cost':                  return __('Cost');
         case 'application_date':      return __('Application date', 'printercounters');
         case 'last_recordmodels_id' : return __('Last record model ID');
      }
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
    * */
   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;
      $options['value']   = $values[$field];
      switch ($field) {
         case 'record_type':
            return self::dropdownRecordType($name, $options);

         case 'state':
            return self::dropdownState($name, $options);

         case 'result':
            return self::dropdownResult($name, $options);
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }

   /**
    * @since version 0.84
    *
    * @param $field
    * @param $values
    * @param $options   array
    * */
   static function getSpecificValueToDisplay($field, $values, array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'record_type' :
            return self::getRecordType($values[$field]);

         case 'state' :
            return self::getState($values[$field]);

         case 'result' :
            return self::getResult($values[$field]);
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   /**
    * Get rights
    */
   function getRights($interface = 'central') {
      if ($interface == 'central') {
         $values = [CREATE  => __('Create'),
                         READ    => __('Read'),
                         UPDATE  => __('Update'),
                         PURGE   => ['short' => __('Purge'),
                                          'long'  => _x('button', 'Delete permanently')]];
      } else {
          $values = [READ    => __('Read')];
      }

      return $values;
   }

}
