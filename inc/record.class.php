<?php

/*
  -------------------------------------------------------------------------
  Printercounters plugin for GLPI
  Copyright (C) 2014 by the Printercounters Development Team.
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Printercounters.

  Printercounters is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Printercounters is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Printercounters. If not, see <http://www.gnu.org/licenses/>.
  -------------------------------------------------------------------------- */

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
   
   var $items_id;
   var $itemtype;
   var $tags;

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

   static function getTypeName($nb = 0) {
      return _n("Record", "Records", $nb, 'printercounters');
   }

   // Printercounter's authorized profiles have right
   static function canView() {
      return plugin_printercounters_haveRight('printercounters', 'r');
   }

   // Printercounter's authorized profiles have right
   static function canCreate() {
      return plugin_printercounters_haveRight('printercounters', 'w');
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
    * Function show record actions
    * 
    * @global type $CFG_GLPI
    * @param type $rand
    * @return boolean
    */
   function showActions($rand) {
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
         $onclick = "onclick='printercountersActions(\"".$CFG_GLPI['root_doc']."\", \"immediateRecord\", \"\", \"record_action_result\", 
                  ".json_encode(array('items_id'        => $this->items_id,
                                      'itemtype'        => $this->itemtype,
                                      'formName'        => 'search_form'.$rand,
                                      'updates'         => array('record' => 'history_showForm'.$rand, 'additionalData' => 'additional_datas'))).");'";
         echo "<a $onclick class='vsubmit printercounters_action_button'>".__('Immediate record', 'printercounters')."</a>";

         // Manual record
         $onclick = "onclick='printercountersActions(\"".$CFG_GLPI['root_doc']."\", \"showManualRecord\", \"\", \"\", 
                  ".json_encode(array('items_id'        => $this->items_id,
                                      'itemtype'        => $this->itemtype,
                                      'addLowerRecord'  => 0,
                                      'formName'        => 'search_form'.$rand,
                                      'updates'         => array('record' => 'history_showForm'.$rand),
                                      'rand'            => $rand)).");'";
         echo "<a $onclick class='vsubmit printercounters_action_button'>".__('Add a manual record', 'printercounters')."</a>";

         // Manual record : add less record
         if (PluginPrintercountersItem_Recordmodel::canAddLessRecords()) {
            $onclick = "onclick='printercountersActions(\"".$CFG_GLPI['root_doc']."\", \"showManualRecord\", \"\", \"\", 
                     ".json_encode(array('items_id'        => $this->items_id,
                                         'itemtype'        => $this->itemtype,
                                         'addLowerRecord'  => 1,
                                         'formName'        => 'search_form'.$rand,
                                         'updates'         => array('record' => 'history_showForm'.$rand),
                                         'rand'            => $rand)).");'";
            echo "<a $onclick class='vsubmit printercounters_action_button'>".__('Add a lower record', 'printercounters')."</a>";
         }
         echo "</div>";
      }

      if ($this->canCreate()) {
         echo "<div class='printercounters_action_group'>";
         // Update global TCO
         $onclick = "onclick='printercountersActions(\"".$CFG_GLPI['root_doc']."\", \"updateGlobalTco\", \"\", \"record_action_result\", 
                  ".json_encode(array('items_id'        => $this->items_id,
                                      'itemtype'        => $this->itemtype,
                                      'formName'        => 'search_form'.$rand,
                                      'updates'         => array('globalTco' => 'update_global_tco'))).");'";
         echo "<a $onclick class='vsubmit printercounters_action_button'>".__('Update global TCO', 'printercounters')."</a>";

         // Update counter position
         $onclick = "onclick='printercountersActions(\"".$CFG_GLPI['root_doc']."\", \"updateCounterPosition\", \"\", \"record_action_result\", 
                  ".json_encode(array('items_id'        => $this->items_id,
                                      'itemtype'        => $this->itemtype,
                                      'formName'        => 'search_form'.$rand,
                                      'updates'         => array('record' => 'history_showForm'.$rand))).");'";
         echo "<a $onclick class='vsubmit printercounters_action_button'>".__('Update counter position', 'printercounters')."</a>";

         // SNMP set
         if (PluginPrintercountersSnmpset::canSnmpSet()) {
            $onclick = "onclick='printercountersActions(\"".$CFG_GLPI['root_doc']."\", \"SNMPSet\", \"\", \"record_action_result\", 
                     ".json_encode(array('items_id'        => $this->items_id,
                                         'itemtype'        => $this->itemtype,
                                         'formName'        => 'search_form'.$rand,
                                         'updates'         => array('additionalData' => 'additional_datas'),
                                         'rand'            => $rand)).");'";
            echo "<a $onclick class='vsubmit printercounters_action_button'>".__('Set printer values', 'printercounters')."</a>";
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
    * Function Show the record planning
    *
    * @return an array
    */
   function showRecordPlanning() {
      $search = new PluginPrintercountersSearch();
      $search->showSearch($this);
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
    * Search function : set restricition
    *
    * @return an array
    */
   function addRestriction() {
      $options = Search::getCleanedOptions($this->getType());
      foreach ($options as $num => $val) {
         if ($val['table'] == getTableForItemType($this->itemtype) && $val['field'] == 'name') {
            return PluginPrintercountersSearch::addWhere('', 1, $this->getType(), $num, 'equals', NULL);
         }
      }
   }

   /**
    * Search function : getSearchTitle
    * 
    * @return string
    */
   function getSearchTitle() {
      return __('Record planning', 'printercounters');
   }

   /**
    * Search function : set default search
    *
    * @return an array
    */
   function getDefaultSearch() {

      $default_search = array();
      $options        = Search::getCleanedOptions($this->getType());
      foreach ($options as $num => $val) {
         if ($val['field'] == 'state') {
            $fields_num = $num;
            break;
         }
      }
      foreach ($options as $num => $val) {
         if ($val['table'] == $this->getTable() && $val['field'] == 'date') {
            $default_search['sort'] = $num;
            break;
         }
      }
      $default_search['fields'][] = array('field' => $fields_num, 'searchtype' => 'equal', 'value' => self::$PROGRAMMED_STATE, 'search_link' => '');
      $default_search['fields'][] = array('field' => $fields_num, 'searchtype' => 'equal', 'value' => self::$PROGRESS_STATE, 'search_link' => 'OR');
      $default_search['order'] = 'ASC';
      
      return $default_search;
   }

   /**
    * Search function : show record history data
    * 
    * @param PluginPrintercountersSearch $search
    */
   function showSearchData(PluginPrintercountersSearch $search) {

      $input = array();

      if ($search->current_search['limit'] > count($search->dataSearch)) {
         $search->current_search['limit'] = count($search->dataSearch) - $search->current_search['start'];
      }

      for ($i = $search->current_search['start']; $i <= $search->current_search['start'] + $search->current_search['limit']; $i++) {
         if (isset($search->dataSearch[$i])) {
            $input[] = $search->dataSearch[$i];
         }
      }

      $row_num = 1;
      foreach ($input as $row) {
         $row_num++;
         $col_num = 1;
         echo Search::showNewLine($search->output_type);
         foreach ($row as $val) {
            echo Search::showItem($search->output_type, $val, $col_num, $row_num);
         }
         echo Search::showEndLine($search->output_type);
      }
   }

   /**
    * Function format record history data
    * 
    * @param PluginPrintercountersSearch $search
    * @return \PluginPrintercountersSearch
    */
   function formatSearchData(PluginPrintercountersSearch $search) {

      $searchopt = array();
      $searchopt = &Search::getOptions($this->getType());

      $types      = array();
      $search_num = array();
      $give_item  = array();
      $count      = 0;
      $sort       = null;
      $order      = "ASC";

      foreach ($searchopt as $num => $val) {
         if ($val['table'] == 'glpi_plugin_printercounters_records' && $val['field'] == 'date') {
            $types['date']      = 'ITEM_'.$count;
            $search_num['date'] = $num;
            
         } elseif ($val['table'] == getTableForItemType($this->itemtype) && $val['field'] == 'id') {
            $types['id']      = 'ITEM_'.$count;
            $search_num['id'] = $num;
            
         } elseif ($val['table'] == getTableForItemType($this->itemtype) && $val['field'] == 'name') {
            $types['name']      = 'ITEM_'.$count;
            $search_num['name'] = $num;
            
         } elseif ($val['table'] == 'glpi_entities' && $val['field'] == 'name') {
            $types['entity']      = 'ITEM_'.$count;
            $search_num['entity'] = $num;
            
         } elseif ($val['table'] == $this->getTable() && $val['field'] == 'state') {
            $types['state']      = 'ITEM_'.$count;
            $search_num['state'] = $num;
            
         } elseif ($val['table'] == $this->getTable() && $val['field'] == 'result') {
            $types['result']      = 'ITEM_'.$count;
            $search_num['result'] = $num;
            
         } elseif ($val['table'] == 'glpi_plugin_printercounters_items_recordmodels' && $val['field'] == 'periodicity') {
            $types['periodicity']      = 'ITEM_'.$count;
            $search_num['periodicity'] = $num;
         }
         
         $count++;
      }
      
      // Get sort params
      foreach ($search_num as $field => $num) {
         if ($search->current_search['sort'] == $num) {
            $sort = $types[$field];
            break;
         }
      }
      $order = $search->current_search['order'];

      //Inject programmed record to data list
      // Manage search parameters
      $condition = array();
      $criteria  = array();

      foreach ($search->current_search['search_item'] as $key => $search_item) {
         if (!empty($search->current_search['contains'][$key])) {
            $LINK    = " ";
            $NOT     = 0;
            $tmplink = "";

            if (is_array($search->current_search['search_link']) && isset($search->current_search['search_link'][$key])) {
               if (strstr($search->current_search['search_link'][$key], "NOT")) {
                  $tmplink = " ".str_replace(" NOT", "", $search->current_search['search_link'][$key]);
                  $NOT     = 1;
               } else {
                  $tmplink = " ".$search->current_search['search_link'][$key];
               }
            } else {
               $tmplink = " AND ";
            }
            // Manage Link if not first item
            if (!empty($condition)) {
               $LINK = $tmplink;
            }

            // Condition cannot be state or date or result
            if ($search->current_search['search_item'][$key] != $search_num['result'] 
                    && $search->current_search['search_item'][$key] != $search_num['state'] 
                    && $search->current_search['search_item'][$key] != $search_num['date']) {

               $condition[$key] = PluginPrintercountersSearch::addWhere($LINK, $NOT, $this->getType(), $search->current_search['search_item'][$key], $search->current_search['searchtype'][$key], $search->current_search['contains'][$key]);
            }

            $criteria[$key] = array('LINK'        => $tmplink,
                                    'NOT'         => $NOT,
                                    'search_item' => $search->current_search['search_item'][$key],
                                    'contains'    => $search->current_search['contains'][$key],
                                    'searchtype'  => $search->current_search['searchtype'][$key]);
         }
      }

      // Inject planned record
      $query = '';
      if (!empty($condition)) {
         $query .= " AND ( ";
         foreach ($condition as $value) {
            $query .= $value;
         }
         $query .= " )";
      }

      $select_items = $this->getRecordsPlanification($query);
      foreach ($select_items as $value) {
         // Compare search data
         if (!$search->compareData($this->getType(), $criteria, array($search_num['date']   => $value['date'],
                     $search_num['state']  => $value['state'],
                     $search_num['result'] => $value['result']))) {
            continue;
         }

         $search->dataSearch[] = array($types['name']        => $value['name'],
                                       $types['name'].'_2'   => $value['id'],
                                       $types['entity']      => $value['entities_name'],
                                       $types['date']        => $value['date'],
                                       $types['periodicity'] => $value['periodicity'],
                                       $types['state']       => $value['state'],
                                       $types['result']      => $value['result']);
      }
      
      
      // Sort
      if (!empty($sort)) {
         switch ($order) {
            case "ASC":
               usort($search->dataSearch, function($a, $b) use ($sort) {
                  if (is_numeric($a[$sort])) {
                     return $a[$sort] - $b[$sort];
                  } else {
                     return strcmp($a[$sort], $b[$sort]);
                  }
               });
               break;
            case "DESC":
               usort($search->dataSearch, function($a, $b) use ($sort) {
                  if (is_numeric($a[$sort])) {
                     return $b[$sort] - $a[$sort];
                  } else {
                     return strcmp($b[$sort], $a[$sort]);
                  }
               });
               break;
         }
      }

      //Format normal output
      if (!empty($search->dataSearch)) {
         foreach ($search->dataSearch as $i => $row) {
            $count = 0;
            foreach ($searchopt as $num => $val) {
               if (!isset($val['nodisplay']) || !$val['nodisplay']) {
                  $give_item[$i]['ITEM_'.$count] = Search::giveItem($this->getType(), $num, $row, $count);
               }
               $count++;
            }
         }
      }

      $search->dataSearch = $give_item;

      return $search;
   }

   /**
    * Function gets items to be query on next interrogation
    * 
    * @param type $condition
    * @return null
    */
   function getRecordsPlanification($condition = '') {

      $output = array();

      $process      = new PluginPrintercountersProcess();
      $select_items = $process->selectPrinterToSearch(true, $condition);

      if (!empty($select_items)) {
         foreach ($select_items as $value) {
            $state = self::$PROGRAMMED_STATE;
            $date  = $value['next_record'];
            // If mutex is valid search is in progress on the item
            if (!empty($value['active_mutex']) && $value['mutex_delay'] < PluginPrintercountersProcess::MUTEX_TIMEOUT) {
               $state = self::$PROGRESS_STATE;
               $date  = strtotime($value['active_mutex']);
            }

            $output[$value['items_id']]['id']            = $value['items_id'];
            $output[$value['items_id']]['name']          = $value['items_name'];
            $output[$value['items_id']]['entities_name'] = $value['entities_name'];
            $output[$value['items_id']]['date']          = date('Y-m-d H:i:s', $date);
            $output[$value['items_id']]['periodicity']   = $value['periodicity_seconds'];
            $output[$value['items_id']]['state']         = $state;
            $output[$value['items_id']]['result']        = null;
            $output[$value['items_id']]['active_mutex']  = $value['active_mutex'];
         }
      }

      return $output;
   }

   /**
    * Search function : countLines
    * 
    * @param type $search
    * @return type
    */
   function countLines(PluginPrintercountersSearch $search) {

      $this->formatSearchData($search);

      return count($search->dataSearch);
   }

   /**
    * Function Show the record type dropdown
    * 
    * @param type $name
    * @param array $options
    * @return type
    */
   static function dropdownRecordType($name = 'record_type', array $options = array()) {
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
      $tab = array(0                        => Dropdown::EMPTY_VALUE,
                   self::$HOST_ERROR_TYPE   => __('Host error', 'printercounters'),
                   self::$RECORD_ERROR_TYPE => __('Record error', 'printercounters'),
                   self::$AUTOMATIC_TYPE    => __('Automatic', 'printercounters'),
                   self::$MANUAL_TYPE       => __('Manual', 'printercounters'));

      return $tab;
   }

   /**
    * Function Show the state dropdown
    *
    * @return an array
    */
   static function dropdownState($name = 'state', array $options = array()) {
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
      $tab = array(0                       => Dropdown::EMPTY_VALUE,
                   self::$PROGRAMMED_STATE => __('Planned', 'printercounters'),
                   self::$FINISHED_STATE   => __('Finished', 'printercounters'),
                   self::$PROGRESS_STATE   => __('In progress', 'printercounters'));

      return $tab;
   }

   /**
    * Function Show the state dropdown
    * 
    * @param type $name
    * @param array $options
    * @return type
    */
   static function dropdownResult($name = 'result', array $options = array()) {
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
      $tab = array(0                    => Dropdown::EMPTY_VALUE,
                   self::$SUCCESS       => __('Success', 'printercounters'),
                   self::$IP_FAIL       => __('IP fail', 'printercounters'),
                   self::$OID_FAIL      => __('OID fail', 'printercounters'),
                   self::$UNKNOWN_FAIL  => __('Unknown error', 'printercounters'),
                   self::$MAC_FAIL      => __('Mac fail', 'printercounters'),
                   self::$SYSDESCR_FAIL => __('Sysdescr fail', 'printercounters'),
                   self::$SERIAL_FAIL   => __('Serial fail', 'printercounters'));

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
   function initRecord($itemtype = 'Printer', $items_id = 0, $sonprocess_id = -1, $sonprocess_nbr = -1, $specific_oid = array()) {

      $messages                = array();
      $error                   = false;
      $search_results          = array();
      $additional_datas        = array();
      $specific_search_results = array();
      
      $additional_data = new PluginPrintercountersAdditional_data();

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
                  $search_results   = array();
                  $additional_datas = array();
                  foreach ($cards as $addresses) {
                     foreach ($addresses['ip'] as $ip) {
                        if (!empty($ip)) {
                           try {
                              $printer = new PluginPrintercountersPrinter($printers_id, 
                                                                          $itemtype, 
                                                                          $ip, 
                                                                          $addresses['mac'], 
                                                                          isset($record_config[$printers_id]) ? $record_config[$printers_id] : array(), 
                                                                          isset($snmp_auth[$printers_id]) ? $snmp_auth[$printers_id] : array());

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
                                    $specific_search_results[] = $printer->get($oid);
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
                     $search_result_ok = array();
                     foreach ($search_results as $results) {
                        if ($results['record_result'] == self::$SUCCESS) {
                           $search_result_ok[] = $results;
                        }
                     }

                     // If all records are wrong set all them in database !
                     if (empty($search_result_ok)) {
                        foreach ($search_results as $key => $results) {
                           $this->setRecord($results['counters'], $results['record_result'], $results['record_type'], date('Y-m-d H:i:s', time() + $key));
                        }

                        // If at least one record is successfull set it in database
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
                     return array($specific_search_results, $error);
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

      return array($messages, $error);
   }

   /**
    * Function clean all empty successful records 
    */
   function cleanRecords() {
      global $DB;

      // Get records with all counters to 0
      $output = array();
      $query  = "SELECT `glpi_plugin_printercounters_counters`.`plugin_printercounters_records_id` as records_id
                  FROM glpi_plugin_printercounters_counters
                  GROUP BY  `glpi_plugin_printercounters_counters`.`plugin_printercounters_records_id`
                  HAVING SUM(`glpi_plugin_printercounters_counters`.`value`) = 0";

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $output[$data['records_id']] = $data['records_id'];
         }

         // Delete automatic successful records
         $records = new PluginPrintercountersRecord();
         $data    = $records->find("`glpi_plugin_printercounters_records`.`id` IN ('".implode("','", $output)."') 
                                    AND `glpi_plugin_printercounters_records`.`result` = ".self::$SUCCESS."
                                    AND `glpi_plugin_printercounters_records`.`record_type` = ".self::$AUTOMATIC_TYPE."");

         $success = 0;
         foreach ($data as $id => $val) {
            if ($records->delete(array('id' => $id), 1)) {
               $success++;
            }
         }
         if (count($data) == $success) {
            return true;
         }

         return false;
      }

      return false;
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
         if ($records_id = $this->add(array('date'                                         => $date,
                                            'result'                                       => $record_result,
                                            'state'                                        => self::$FINISHED_STATE,
                                            'record_type'                                  => $record_type,
                                            'last_recordmodels_id'                         => $input['recordmodels_id'],
                                            'entities_id'                                  => $input['entities_id'],
                                            'locations_id'                                 => $input['locations_id'],
                                            'plugin_printercounters_items_recordmodels_id' => $input['items_recordmodels_id']))) {

            $counter = new PluginPrintercountersCounter();
            foreach ($input['counters'] as $countertypes_recordmodels_id => $counters) {
               foreach ($counters as $counters_id => $value) {
                  $counter->add(array('plugin_printercounters_countertypes_recordmodels_id' => $countertypes_recordmodels_id,
                                      'plugin_printercounters_records_id'                   => $records_id,
                                      'value'                                               => $value));
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

      $records = $this->find("`plugin_printercounters_items_recordmodels_id` = ".$item_recordmodel);

      if (empty($records)) {
         $counters = array();

         $countertype_recordmodel = new PluginPrintercountersCountertype_Recordmodel();
         $oid                     = $countertype_recordmodel->getCounterTypes($recordmodel);

         // Set counters to 0
         if (!empty($oid)) {
            foreach ($oid as $val) {
               if ($val['oid_type'] != PluginPrintercountersCountertype_Recordmodel::SERIAL && $val['oid_type'] != PluginPrintercountersCountertype_Recordmodel::SYSDESCR) {
                  $counters['counters'][$val['id']][0] = 0;
               }
            }
         }

         $item = getItemForItemtype($this->itemtype);
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
         if ($this->update(array('id'          => $records_id,
                                 'result'      => $record_result,
                                 'state'       => self::$FINISHED_STATE,
                                 'record_type' => $record_type))) {

            $counter = new PluginPrintercountersCounter();
            foreach ($input['counters'] as $countertypes_recordmodels_id => $counters) {
               foreach ($counters as $counters_id => $value) {
                  if ($counters_id > 0) {
                     $counter->update(array('id' => $counters_id, 'value' => $value));
                  } else {
                     $counter->add(array('value'                                               => $value,
                                         'plugin_printercounters_records_id'                   => $records_id,
                                         'plugin_printercounters_countertypes_recordmodels_id' => $countertypes_recordmodels_id));
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
   function getRecords($items_id, $itemtype, $options = array()) {
      global $DB;

      $params['condition']   = "";
      $params['last_record'] = false;
      $params['next_record'] = false;
      $params['record_date'] = null;
      $params['order']       = null;
      $params['records_id']  = 0;

      foreach ($options as $key => $val) {
         $params[$key] = $val;
      }

      $itemjoin  = getTableForItemType($itemtype);
      $itemjoin1 = getTableForItemType("PluginPrintercountersCounter");
      $itemjoin2 = getTableForItemType("PluginPrintercountersItem_Recordmodel");
      $itemjoin3 = getTableForItemType("PluginPrintercountersCountertype_Recordmodel");
      $itemjoin4 = getTableForItemType("PluginPrintercountersCountertype");

      $output = array();

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
            while ($data = $DB->fetch_assoc($result)) {
               $output[$data['records_id']]['recordmodels_id']                    = $data['recordmodels_id'];
               $output[$data['records_id']]['items_id']                           = $data['items_id'];
               $output[$data['records_id']]['itemtype']                           = $itemtype;
               $output[$data['records_id']]['date']                               = $data['date'];
               $output[$data['records_id']]['record_type']                        = $data['record_type'];
               $output[$data['records_id']]['entities_id']                        = $data['entities_id'];
               $output[$data['records_id']]['result']                             = $data['result'];
               $output[$data['records_id']]['counters'][$data['countertypes_id']] = array('counters_name'                => $data['counters_name'],
                                                                                          'counters_value'               => $data['counters_value'],
                                                                                          'counters_id'                  => $data['counters_id'],
                                                                                          'oid_type'                     => $data['oid_type'],
                                                                                          'countertypes_recordmodels_id' => $data['countertypes_recordmodels_id']);
            }
         } else {
            while ($data = $DB->fetch_assoc($result)) {
               $output[$itemtype][$data['items_id']][$data['records_id']]['recordmodels_id']                    = $data['recordmodels_id'];
               $output[$itemtype][$data['items_id']][$data['records_id']]['items_id']                           = $data['items_id'];
               $output[$itemtype][$data['items_id']][$data['records_id']]['itemtype']                           = $itemtype;
               $output[$itemtype][$data['items_id']][$data['records_id']]['date']                               = $data['date'];
               $output[$itemtype][$data['items_id']][$data['records_id']]['entities_id']                        = $data['entities_id'];
               $output[$itemtype][$data['items_id']][$data['records_id']]['result']                             = $data['result'];
               $output[$itemtype][$data['items_id']][$data['records_id']]['counters'][$data['countertypes_id']] = array('counters_name'                => $data['counters_name'],
                                                                                                                        'counters_value'               => $data['counters_value'],
                                                                                                                        'counters_id'                  => $data['counters_id'],
                                                                                                                        'oid_type'                     => $data['oid_type'],
                                                                                                                        'countertypes_recordmodels_id' => $data['countertypes_recordmodels_id']);
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
   function manualRecord($options = array()) {
      global $CFG_GLPI;

      $p['ontop']         = false;
      $p['num_displayed'] = -1;
      $p['fixed']         = false;
      $p['extraparams']   = array();
      $p['width']         = 800;
      $p['height']        = 400;
      $p['rand']          = mt_rand();
      $p['title']         = __('Printer counters', 'printercounters');
      $p['url']           = $CFG_GLPI['root_doc']."/plugins/printercounters/ajax/record.php";

      foreach ($options as $key => $val) {
         if (isset($p[$key])) {
            $p[$key] = $val;
         }
      }

      PluginPrintercountersAjax::createModalWindow('manual_record_window', $p['url'], array('title'       => $p['title'],
          'extraparams' => $p['extraparams'],
          'width'       => $p['width'],
          'height'      => $p['height']));
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
         $records = $this->getRecords($items_id, $itemtype, array('records_id' => $records_id,
             'order'      => "`glpi_plugin_printercounters_countertypes`.`name` ASC"));

         // Fill counters if recordmodel has changed
         $recordmodels_data        = current($records);
         $countertype_recordmodels = new PluginPrintercountersCountertype_Recordmodel();
         $records                  = $countertype_recordmodels->fillCountersGap($records, array($recordmodels_data['recordmodels_id']));

         foreach ($records as $record) {
            foreach ($record['counters'] as $counter) {
               echo "<tr class='tab_bg_1'>";
               echo "<td>".$counter['counters_name'].'</td>';
               echo '<td><input type="text" value="'.$counter['counters_value'].'" name="counters[counters]['.$counter['countertypes_recordmodels_id'].']['.$counter['counters_id'].']">
                         <input type="hidden" value="'.$record['date'].'" name="counters[date]"></td>';
               echo "</tr>";
            }
         }

         echo "<tr class='tab_bg_2'>";
         echo "<td colspan='2' class='center'>";
         $onclick = "onclick='printercountersActions(\"".$CFG_GLPI['root_doc']."\", \"setManualRecord\", document.getElementById(\"manual_record_form\"), \"manual_record_error\", 
               ".json_encode(array('items_id'        => $items_id,
                     'itemtype'        => $itemtype,
                     'addLowerRecord'  => $addLowerRecord,
                     'records_id'      => $records_id,
                     'formName'        => 'search_form'.$rand,
                     'updates'         => array('record' => 'history_showForm'.$rand))).");'";
         echo "<input type='button' $onclick class='submit' value='"._sx('button', 'Update')."'>";
         echo "</td></tr>";

         //ADD
      } else {
         $countertype_recordmodel = new PluginPrintercountersCountertype_Recordmodel();
         $counters                = $countertype_recordmodel->getRecordmodelCountersForItem($items_id, $itemtype, "`glpi_plugin_printercounters_countertypes`.`id` ASC");
         foreach ($counters as $counter) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>".$counter['counters_name'].'</td>';
            echo '<td><input type="text" value="" name="counters[counters]['.$counter['countertypes_recordmodels_id'].'][0]"></td>';
            echo "</tr>";
         }

         echo "<tr class='tab_bg_2'>";
         echo "<td colspan='2' class='center'>";
         $onclick = "onclick='printercountersActions(\"".$CFG_GLPI['root_doc']."\", \"setManualRecord\", document.getElementById(\"manual_record_form\"), \"manual_record_error\", 
               ".json_encode(array('items_id'        => $items_id,
                     'itemtype'        => $itemtype,
                     'addLowerRecord'  => $addLowerRecord,
                     'records_id'      => $records_id,
                     'formName'        => 'search_form'.$rand,
                     'updates'         => array('record' => 'history_showForm'.$rand))).");'";
         echo "<input type='button' $onclick class='submit' value='"._sx('button', 'Add')."'>";
         echo "</td></tr>";
      }

      echo "</table>";
      echo "<input type='hidden' value='".$item_recordmodels_data['id']."' name='counters[items_recordmodels_id]'>";
      echo "<input type='hidden' value='".$item_recordmodels_data['plugin_printercounters_recordmodels_id']."' name='counters[recordmodels_id]'>";
      echo "<input type='hidden' value='".$item_recordmodels_data['entities_id']."' name='counters[entities_id]'>";
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
         if ($error)
            return array($messages, $error);
      }

      // UPDATE
      if ($records_id > 0) {
         // Check if counters are not higher than the next
         if (!$addLowerRecord) {
            list($messages, $error) = $this->checkValues($items_id, $itemtype, $counters, 'next_record');
            if ($error)
               return array($messages, $error);
         }

         // Check if counters are numeric
         list($messages, $error) = $this->checkValues($items_id, $itemtype, $counters, 'numeric_or_empty_counters');
         if ($error)
            return array($messages, $error);
         $this->updateRecord($counters, self::$SUCCESS, self::$MANUAL_TYPE, $records_id);

         // ADD  
      } else {
         // Check if counters are numeric
         list($messages, $error) = $this->checkValues($items_id, $itemtype, $counters, 'numeric_or_empty_counters');
         if ($error)
            return array($messages, $error);
         $this->setRecord($counters, self::$SUCCESS, self::$MANUAL_TYPE, date('Y-m-d H:i:s'));
      }

      return array($messages, $error);
   }

   /**
    * Function update total number of pages for item
    * 
    * @param int $items_id
    * @param string $itemtype
    */
   function updateCounterPosition($items_id, $itemtype) {
      $item = getItemForItemtype($itemtype);

      // Init record for specific OID
      list($result, $error) = $this->initRecord($itemtype, $items_id, -1, -1, array(PluginPrintercountersPrinter::SNMP_NUMBER_OF_PRINTED_PAPERS));
      if ($error)
         return array($result, $error);

      // Check value
      list($messages, $error) = $this->checkValues($items_id, $itemtype, $result[0], 'counter_position');
      if ($error)
         return array($messages, $error);

      $item->update(array('id' => $items_id, 'last_pages_counter' => $result[0]));

      return array($result, $error);
   }

   /**
    * Function update global tco of item
    * 
    * @param int $items_id
    * @param string $itemtype
    */
   function updateGlobalTco($items_id, $itemtype) {
      
      $result  = null;
      $message = null;
      $error   = false;

      $tco = $this->getItemTco($items_id, $itemtype);

      $item_recordmodel = new PluginPrintercountersItem_Recordmodel($itemtype, $items_id);
      $item_recordmodel->getFromDBByQuery("WHERE LOWER(`itemtype`) = LOWER('$itemtype') AND `items_id`=$items_id LIMIT 1");

      $search  = new PluginPrintercountersSearch();
      $search->showSearch($item_recordmodel, array('display' => false));
      $records = $item_recordmodel->formatSearchData($search->dataSearch);

      if ($item_recordmodel->update(array('id' => $item_recordmodel->getField('id'), 'global_tco' => ($records['total_record_cost'] + $tco)))) {
         $result = Html::formatNumber($records['total_record_cost'] + $tco);
      } else {
         $error   = true;
         $message = __('TCO update error');
      }

      return array($message, $result, $error);
   }

   /**
    * Function get items tco
    * 
    * @param int $items_id
    * @param string $itemtype
    */
   function getItemTco($items_id, $itemtype) {
      global $DB;

      $output = array();

      $itemjoin  = getTableForItemType($itemtype);
      $itemjoin2 = getTableForItemType('Infocom');

      $query = "SELECT `".$itemjoin2."`.`value` + `".$itemjoin."`.`ticket_tco` as tco
          FROM ".$itemjoin."
          LEFT JOIN `$itemjoin2` 
             ON (`".$itemjoin2."`.`items_id` = `$itemjoin`.`id` AND LOWER(`".$itemjoin2."`.`itemtype`) = LOWER('".$itemtype."'))
          WHERE `".$itemjoin."`.`id` = ".Toolbox::cleanInteger($items_id);

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
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
      $msg     = array();
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
               $last_record = $this->getRecords($items_id, $itemtype, array('last_record' => true,
                   'record_date' => $record_date));

               if (!empty($last_record) && !empty($input['counters'])) {
                  foreach ($last_record as $record) {
                     foreach ($record['counters'] as $last_counter) {
                        foreach ($input['counters'] as $countertypes_recordmodels_id => $counters) {
                           foreach ($counters as $counters_id => $value) {
                              if ($last_counter['countertypes_recordmodels_id'] == $countertypes_recordmodels_id && $value < $last_counter['counters_value']) {
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
               $next_record = $this->getRecords($items_id, $itemtype, array('next_record' => true,
                   'record_date' => $record_date));
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

      return array(array_unique($msg), $checkKo);
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

      echo "<form name='form' method='post' action='".
      Toolbox::getItemTypeFormURL('PluginPrintercountersConfig')."'>";

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>".__('Item records', 'printercounters')."</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Disable automatic records', 'printercounters');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo('disable_autosearch', $config['disable_autosearch']);
      echo "</td>";

      echo "<td>";
      echo __('Set first record when record model change', 'printercounters');
      echo "</td>";
      echo "<td class='center'>";
      Dropdown::showYesNo('set_first_record', $config['set_first_record']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Enable toner level alert', 'printercounters')."</td><td>";
      $rand = Dropdown::showYesNo('enable_toner_alert', $config['enable_toner_alert'], -1, array('on_change' => 'enableAlertConfig();'));
      echo "<script type='text/javascript'>";
      echo "function enableAlertConfig(){";
      echo "   Ext.onReady(function() {
                  var enable = Ext.get('dropdown_enable_toner_alert$rand').getValue();
                  if (enable == '1') {
                     Ext.get('enable_alert_config').setStyle('display', 'table');
                  } else {
                     Ext.get('enable_alert_config').setStyle('display', 'none');
                  }
               });";
      echo "}";
      if($config['enable_toner_alert']) {
         echo "enableAlertConfig();";
      }
      echo "</script>";
      echo "</td>";
      
      echo "<td colspan='2'>";
      echo "<table class='tab_cadre' style='margin:0px;display:none;' id='enable_alert_config'>";
      echo "<tr><th colspan='2'>".__('Toner level alert', 'printercounters')."</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Reminders frequency for printer toner level', 'printercounters')."</td><td>";
      Alert::dropdown(array('name'      => 'toner_alert_repeat',
                            'value'     => $config['toner_alert_repeat']));
      
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Toner alert treshold', 'printercounters')."</td>";
      echo "<td>";
      Dropdown::showNumber("toner_treshold",  array('value' => $config['toner_treshold'],
                                                    'min'   => 0,
                                                    'max'   => 100));
      echo " % ";
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      echo "</td>";
      echo "</tr>";
      
      echo "<tr><td class='tab_bg_2 center' colspan='6'><input type=\"submit\" name=\"update_config\" class=\"submit\"
         value=\""._sx('button', 'Update')."\" ></td></tr>";

      echo "</table></div>";
      Html::closeForm();
   }

   /**
    * Get search options
    * 
    * @return array
    */
   function getSearchOptions() {

      $itemtype = $this->itemtype;
      $item     = getItemForItemtype($itemtype);

      $tab[89]['table']         = getTableForItemType($itemtype);
      $tab[89]['field']         = 'name';
      $tab[89]['name']          = $item::getTypeName();
      $tab[89]['datatype']      = 'itemlink';
      $tab[89]['massiveaction'] = false;
      $tab[89]['nosearch']      = true;
      $tab[89]['linkfield']     = 'items_id';
      $tab[89]['joinparams']    = array('condition'  => "AND REFTABLE.`itemtype`='".$itemtype."'",
                                        'beforejoin'
                                             => array('table' => 'glpi_plugin_printercounters_items_recordmodels')
                                         );

      $tab[90]['table']         = 'glpi_entities';
      $tab[90]['field']         = 'name';
      $tab[90]['name']          = $this->getFieldName('entities_id');
      $tab[90]['massiveaction'] = false;
      $tab[90]['datatype']      = 'dropdown';

      $tab[91]['table']         = $this->getTable();
      $tab[91]['field']         = 'date';
      $tab[91]['name']          = $this->getFieldName('date');
      $tab[91]['datatype']      = 'datetime';
      $tab[91]['massiveaction'] = false;

      $tab[92]['table']           = 'glpi_plugin_printercounters_items_recordmodels';
      $tab[92]['field']           = 'periodicity';
      $tab[92]['name']            = $this->getFieldName('periodicity');
      $tab[92]['datatype']        = 'timestamp';
      $tab[92]['searchtype']      = array('equals', 'notequals');
      $tab[92]['min']             = DAY_TIMESTAMP;
      $tab[92]['max']             = 15 * DAY_TIMESTAMP;
      $tab[92]['step']            = DAY_TIMESTAMP;
      $tab[92]['addfirstminutes'] = false;
      $tab[92]['inhours']         = false;
      $tab[92]['massiveaction']   = false;

      $tab[93]['table']         = $this->getTable();
      $tab[93]['field']         = 'state';
      $tab[93]['name']          = $this->getFieldName('state');
      $tab[93]['datatype']      = 'specific';
      $tab[93]['searchtype']    = array('equals', 'notequals');
      $tab[93]['massiveaction'] = false;

      $tab[94]['table']         = $this->getTable();
      $tab[94]['field']         = 'result';
      $tab[94]['name']          = $this->getFieldName('result');
      $tab[94]['datatype']      = 'specific';
      $tab[94]['searchtype']    = array('equals', 'notequals');
      $tab[94]['massiveaction'] = false;

      $tab[95]['table']         = $this->getTable();
      $tab[95]['field']         = 'record_type';
      $tab[95]['name']          = $this->getFieldName('record_type');
      $tab[95]['datatype']      = 'specific';
      $tab[95]['nosearch']      = true;
      $tab[95]['nodisplay']     = true;
      $tab[95]['massiveaction'] = true;

      $tab[96]['table']         = getTableForItemType($itemtype);
      $tab[96]['field']         = 'id';
      $tab[96]['name']          = $item::getTypeName();
      $tab[96]['massiveaction'] = false;
      $tab[96]['nosearch']      = true;
      $tab[96]['nodisplay']     = true;
      $tab[96]['linkfield']     = 'items_id';
      $tab[96]['joinparams']    = array('condition'  => "AND REFTABLE.`itemtype`='".$itemtype."'",
                                        'beforejoin'
                                             => array('table' => 'glpi_plugin_printercounters_items_recordmodels')
                                       );

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
         case 'date': return __('Date');
         case 'entities_id': return __('Entity');
         case 'periodicity': return __('Periodicity', 'printercounters');
         case 'state': return __('State', 'printercounters');
         case 'result': return __('Result', 'printercounters');
         case 'items_id': return __('Entity');
         case 'record_type': return __('Record type', 'printercounters');
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
   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = array()) {
      if (!is_array($values)) {
         $values = array($field => $values);
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
   static function getSpecificValueToDisplay($field, $values, array $options = array()) {

      if (!is_array($values)) {
         $values = array($field => $values);
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

}
