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
class PluginPrintercountersErrorItem extends CommonDBTM {

   static $rightname = 'plugin_printercounters';

   // Record result
   static $NO_ERROR    = 0;
   static $SOFT_STATE  = 1;
   static $HARD_STATE  = 2;

   var $items_id;
   var $itemtype;
   var $error_counter;
   var $max_error_retries;

   /**
    * Constructor
    *
    * @param type $itemtype
    * @param type $items_id
    */
   public function __construct($itemtype = 'printer', $items_id = 0, $error_counter = 0, $max_error_retries = 0) {

      $this->forceTable("glpi_plugin_printercounters_items_recordmodels");

      $this->setItemtype($itemtype);
      $this->setItems_id($items_id);
      $this->setError_counter($error_counter);
      $this->setMax_error_retries($max_error_retries);

      parent::__construct();
   }

   static function getTypeName($nb = 0) {
      return _n("Error item", "Error items", $nb, 'printercounters');
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
    * Function sets error_counter id
    *
    * @param string $error_counter
    */
   public function setError_counter($error_counter) {

      $this->error_counter = $error_counter;
   }

   /**
    * Function sets max_error_retries
    *
    * @param string $max_error_retries
    */
   public function setMax_error_retries($max_error_retries) {

      $this->max_error_retries = $max_error_retries;
   }

   /**
    * Function add printer in the error list
    */
   public function addToErrorItems() {
      if ($this->getFromDBByCrit(['items_id' => $this->items_id,
                              'itemtype' => $this->itemtype])) {
         $error_counter = intval($this->fields['error_counter']) + 1;
         $this->update(['id' => $this->fields['id'], 'error_counter' => $error_counter, 'status' => self::$SOFT_STATE]);
      }
   }

   /**
    * Function remove printer of the error list
    */
   public function removeErrorItem() {
      if ($this->getFromDBByCrit(['items_id' => $this->items_id,
                                  'itemtype' => $this->itemtype])) {
         $this->update(['id' => $this->fields['id'], 'error_counter' => 0, 'status' => self::$NO_ERROR]);
      }
   }

   /**
    * Function update printer error counter
    */
   public function incrementErrorCounter() {
      if ($this->getFromDBByCrit(['items_id' => $this->items_id,
                                  'itemtype' => $this->itemtype])) {
         $error_counter = intval($this->fields['error_counter']) + 1;
         $this->update(['id' => $this->fields['id'], 'error_counter' => $error_counter]);
      }
   }

   /**
    * Function get the result
    *
    * @param type $value
    * @return type
    */
   static function getStatus($value) {
      if (!empty($value)) {
         $data = self::getAllStatusArray();
         return $data[$value];
      }
   }

   /**
    * Function Get the state list
    *
    * @return an array
    */
   static function getAllStatusArray() {

      // To be overridden by class
      $tab = [self::$NO_ERROR      => __('No errors', 'printercounters'),
                   self::$SOFT_STATE    => __('Soft state', 'printercounters'),
                   self::$HARD_STATE    => __('Hard state', 'printercounters')];

      return $tab;
   }

   /**
    * Function Show if the item is in error state
    *
    * @global type $CFG_GLPI
    */
   function showErrorItem() {
      global $CFG_GLPI;

      if ($nbErrors = $this->isInError()) {
         $message = self::getTypeName().'. '.__('Number of errors', 'printercounters').' : '.$nbErrors;
         echo "<div class='alert alert-important alert-warning d-flex'>";
         echo $message."</div>";
      }
   }


   /**
    * Function Is item in error
    *
    * @return boolean
    */
   function isInError() {
        // Get config
      $config      = new PluginPrintercountersConfig();
      $config_data = $config->getInstance();

      if ($config_data['enable_error_handler']) {
         $data = $this->find(["items_id" =>  $this->items_id,
                              "itemtype" => strtolower($this->itemtype)]);
         $data = reset($data);

         if ($data['error_counter'] > 0) {
            return $data['error_counter'];
         }
      }

      return false;
   }

   /**
    * Show general records config
    *
    * @param type $config
    */
   function showErrorItemConfig($config) {

      if (!$this->canCreate()) {
         return false;
      }

      echo "<form name='form' method='post' action='".
      Toolbox::getItemTypeFormURL('PluginPrintercountersConfig')."'>";
      echo "<div align='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>".self::getTypeName(2)."</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Enable record error handler', 'printercounters')."</td>";
      echo "<td>";
      $rand = Dropdown::showYesNo('enable_error_handler', $config['enable_error_handler'], -1, ['on_change' => 'enableErrorHandler();']);
      echo "</td>";
      echo "<td class='enable_error_handler_config' style='display:none;'>".__('Maximum number of interrogation for records in error', 'printercounters')."</td>";
      echo "<td class='enable_error_handler_config' style='display:none;'>";
      Dropdown::showNumber("max_error_counter", ['value' => $config['max_error_counter'],
                                                       'min'   => 0,
                                                       'max'   => 10]);
      echo "<script type='text/javascript'>";
      echo "function enableErrorHandler(){";
      echo "   $(document).ready(function () {
                  var enable = $('#dropdown_enable_error_handler$rand').val();
                  if (enable == '1') {
                     var elems = $('td.enable_error_handler_config');
                     $.each(elems, function(index, value) {
                        $(value).css({'display':'table-cell'});
                     });
                  } else {
                     var elems = $('td.enable_error_handler_config');
                     $.each(elems, function(index, value) {
                        $(value).css({'display':'none'});
                     });
                  }
               });";
      echo "}";
      echo "enableErrorHandler();";
      echo "</script>";
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo" <td class='tab_bg_2 center' colspan='6'>";
      echo Html::submit(_sx('button', 'Update'), ['name' => 'update_config', 'class' => 'btn btn-primary']);
      echo "</td>";
      echo "</tr>";
      echo "</table></div>";
      Html::closeForm();

      $this->listItems();
   }

   private function listItems() {
      $fields = $this->find(["error_counter" => ['>', 0]]);

      if (!empty($fields)) {
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th colspan='3'>".__('Error item list', 'printercounters')."</th>";
         echo "</tr>";
         echo "<tr>";
         echo "<th>".__('Name')."</th>";
         echo "<th>".__('Number of host errors', 'printercounters')."</th>";
         echo "<th>".__('Status')."</th>";
         echo "</tr>";
         foreach ($fields as $field) {
            echo "<tr class='tab_bg_1'>";
            $printer = new Printer();
            $printer->getFromDB($field['items_id']);
            echo "<td>".$printer->getLink()."</td>";
            echo "<td>".$field['error_counter']."</td>";
            echo "<td>".$this->getStatus($field['status'])."</td>";
            echo "</tr>";
         }
         echo "</table>";
         echo "</div>";
      }
   }

   //######################### CRON FUNCTIONS #####################################################

   static function cronInfo($name) {

      switch ($name) {
         case 'PrintercountersErrorItem':
            return ['description' => __('Interrogation of printers in host error', 'printercounters')];
      }
      return [];
   }

   /**
    * Cron action on tasks : create a ticket if consecutive errors on records OR no recrods since a defined date
    *
    * @param $task for log, if NULL display
    */
   static function cronPluginPrintercountersErrorItem($task = null) {

      $cron_status   = 1;

      // Get config
      $config     = new PluginPrintercountersConfig();
      $config_data = $config->getInstance();

      if ($config_data['enable_error_handler']) {
         // Get list of items in error
         $errorItem   = new self();
         $errorList   = $errorItem->find(['status' => [self::$HARD_STATE, self::$SOFT_STATE]]);

         if (!empty($errorList)) {
            foreach ($errorList as $error) {
               $errorItem     = new self($error['itemtype'],
                                         $error['items_id'],
                                         $error['error_counter'],
                                         $config_data['max_error_counter']);

               list($message, $error) = $errorItem->initRecord();
               // Display message
               self::displayCronMessage($message, $task);
            }
         }
      }

      return $cron_status;
   }

   /**
    * Display cron messages
    *
    * @param type $message
    * @param type $task
    */
   static function displayCronMessage($message, $task = null) {

      $message = array_unique($message);
      if (!empty($message)) {
         foreach ($message as $value) {
            if ($task) {
               $task->log($value);
               $task->addVolume(1);
            } else {
               Session::addMessageAfterRedirect($value, true, ERROR);
            }
         }
      }
   }

   /**
    * Function init record for items in error
    */
   function initRecord() {
      $messages                = [];
      $error                   = false;
      $search_results          = [];

      $record = new PluginPrintercountersRecord();

      // Get items ip addresses for each processes
      $process      = new PluginPrintercountersProcess(-1, 1, $this->itemtype, $this->items_id);
      $ip_addresses = $process->getIPAddressesForProcess(true);

      if (!empty($ip_addresses)) {
         // Get SNMP authentication by items
         $snmpauthentication = new PluginPrintercountersSnmpauthentication();
         $snmp_auth          = $snmpauthentication->getItemAuthentication(array_keys($ip_addresses), $this->itemtype);

         // Get record config by items (timeout, nb retries)
         $item_recordmodel = new PluginPrintercountersItem_Recordmodel();
         $record_config    = $item_recordmodel->getItemRecordConfig(array_keys($ip_addresses), $this->itemtype);

         // Get record model config for items
         $recordmodel        = new PluginPrintercountersRecordmodel();
         $recordmodel_config = $recordmodel->getRecordModelConfig(array_keys($ip_addresses), $this->itemtype);

         // Init counters search
         switch (strtolower($this->itemtype)) {
            case 'printer':
               foreach ($ip_addresses as $printers_id => $cards) {
                  $search_results   = [];
                  foreach ($cards as $addresses) {
                     foreach ($addresses['ip'] as $ip) {
                        if (!empty($ip)) {
                           try {
                              $printer = new PluginPrintercountersPrinter($printers_id,
                                                                          $this->itemtype,
                                                                          $ip,
                                                                          $addresses['mac'],
                                                                          isset($record_config[$printers_id]) ? $record_config[$printers_id] : [],
                                                                          isset($snmp_auth[$printers_id]) ? $snmp_auth[$printers_id] : []);
                              // Get OIDs
                              $printer->getOID();
                              $init_search = $printer->initSearch($recordmodel_config[$printers_id]);
                              if (isset($init_search['counters']['counters']) && !empty($init_search['counters']['counters'])) {
                                 $search_results[] = $init_search;
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
                        if ($results['record_result'] == PluginPrintercountersRecord::$SUCCESS) {
                           $search_result_ok[] = $results;
                        }
                     }

                     // If all records are wrong
                     if (empty($search_result_ok)) {
                        // Max retries is not reached
                        if ($this->error_counter < $this->max_error_retries) {
                           $this->incrementErrorCounter();
                           $messages[] = __('Error on item, soft state', 'printercounters').' (itemtype : '.$this->itemtype.', items_id : '.$printers_id.')';

                           // Max retry, set record in database, remove from error list
                        } else {
                           foreach ($search_results as $key => $results) {
                              $record->setRecord($results['counters'], $results['record_result'], $results['record_type'], date('Y-m-d H:i:s', time() + $key));
                           }
                           // Printer is in hard state
                           $this->removeErrorItem();
                           $messages[] = __('Error on item, hard state', 'printercounters').' (itemtype : '.$this->itemtype.', items_id : '.$printers_id.')';
                        }

                        // If at least one record is successfull set it in database, remove from error list
                     } else {
                        $record->setRecord($search_result_ok[0]['counters'], $search_result_ok[0]['record_result'], $search_result_ok[0]['record_type'], date('Y-m-d H:i:s'));
                        $this->removeErrorItem();
                        $messages[] = __('Record success', 'printercounters').' (itemtype : '.$this->itemtype.', items_id : '.$printers_id.')';
                     }

                  } else {
                     $messages[] = __('No results, please check OIDs of your record model', 'printercounters').' (itemtype : '.$this->itemtype.', items_id : '.$printers_id.')';
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


}
