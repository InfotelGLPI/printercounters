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
 * Class PluginPrintercountersProcess
 *
 * This class manages the partition of the printers to query on each process
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersProcess extends CommonDBTM {

   protected $process_id;
   protected $process_nbr;
   protected $itemtype;
   protected $items_id;

   const MUTEX_TIMEOUT = 3600;
   const SIGKILL = 9;
   const SIGTERM = 15;

   static $rightname = 'plugin_printercounters';


   /**
    * Contructor
    *
    * @param int $process_id process id
    * @param int $process_nbr number of processs
    * @param int $itemtype item type concerned by the process
    * @param int $items_id item id concerned by the process
    */
   public function __construct($process_id = null, $process_nbr = null, $itemtype = 'printer', $items_id = null) {

      if ($itemtype !== null) {
         $this->setItemtype($itemtype);
      }

      if ($items_id !== null) {
         $this->setItems_id($items_id);
      }

      if ($process_id !== null) {
         $this->setProcessId($process_id);
      }

      if ($process_nbr !== null) {
         $this->setProcessNumber($process_nbr);
      }
   }

   static function getTypeName($nb = 0) {
      return _n('Process', 'Processes', $nb, 'printercounters');
   }

   /**
    * Function sets process id
    *
    * @param string $process_id process id
    * @throws Exception
    */
   public function setProcessId($process_id) {

      if (!is_numeric($process_id)) {
         throw new PluginPrintercountersException(__('Invalid process id', 'printercounters'), 0, null, $this->items_id, $this->itemtype);
      }

      $this->process_id = $process_id;
   }

   /**
    * Function sets process number
    *
    * @param string $process_nbr number of processs
    * @throws Exception
    */
   public function setProcessNumber($process_nbr) {

      if (!is_numeric($process_nbr)) {
         throw new PluginPrintercountersException(__('Invalid process number', 'printercounters'), 0, null, $this->items_id, $this->itemtype);
      }

      $this->process_nbr = $process_nbr;
   }

   /**
    * Function sets item type
    *
    * @param string $itemtype item type
    * @throws Exception
    */
   public function setItemtype($itemtype) {

      if (!is_string($itemtype)) {
         throw new PluginPrintercountersException(__('Invalid process itemtype', 'printercounters'));
      }

      $this->itemtype = $itemtype;
   }

   /**
    * Function sets Items id
    *
    * @param string $items_id Items id
    * @throws Exception if passed Items id is not in string format
    */
   public function setItems_id($items_id) {
      /**
       * Check if Items id is string
       */
      if (!is_numeric($items_id)) {
         throw new PluginPrintercountersException(__('Passed Items id is not correct', 'printercounters'));
      }

      $this->items_id = $items_id;
   }

   /**
    * Function gets ip adresses for an item type shared by processs
    *
    * @global type $DB
    * @return type
    */
   public function getIPAddressesForProcess($errorHandler = false) {
      global $DB;

      $ip = [];

      // Filter printers according to their periodicity
      if ($this->items_id <= 0) {
         $items_id = $this->selectPrinterToSearch(false, $errorHandler);
      } else {
         $items_id = [$this->items_id];
         $error_item = new PluginPrintercountersErrorItem($this->itemtype, $this->items_id);
         if ($error_item->isInError() > 0 && !$errorHandler) {
            return $ip;
         }
      }

      $dbu      = new DbUtils();
      $itemjoin = $dbu->getTableForItemType($this->itemtype);

      $query = "SELECT `glpi_networkports`.`id` as cards_id,
                       `glpi_ipaddresses`.`name` as ip,
                       `glpi_networkports`.`items_id`,
                       `glpi_networkports`.`mac`
                FROM `glpi_networkports`
                LEFT JOIN `".$itemjoin."` ON (`".$itemjoin."`.`id` = `glpi_networkports`.`items_id`)
                LEFT JOIN `glpi_networknames` ON (`glpi_networkports`.`id` = `glpi_networknames`.`items_id`)
                LEFT JOIN `glpi_ipaddresses` ON (`glpi_networknames`.`id` = `glpi_ipaddresses`.`items_id`)
                WHERE LOWER(`glpi_networkports`.`itemtype`) = LOWER('".$this->itemtype."')
                AND `".$itemjoin."`.`id` IN ('".implode("','", $items_id)."')";

      if ($this->items_id > 0) {
         $query .= " AND `".$itemjoin."`.`id` = ".$this->items_id;
      }

      $result_ocs = $DB->query($query);
      if ($DB->numrows($result_ocs) > 0) {
         while ($data = $DB->fetchArray($result_ocs)) {
            $ip[$data['items_id']][$data['cards_id']]['ip'][] = $data['ip'];
            $ip[$data['items_id']][$data['cards_id']]['mac']  = $data['mac'];
         }
      }

      // SET MUTEX
      $items_id = array_keys($ip);
      $item_recordmodels = new PluginPrintercountersItem_Recordmodel();
      $item_recordmodels->setMutex($items_id, $this->process_id);

      return $ip;
   }

   /**
    * Function select items to search according to their periodicity
    *
    * @global type $DB
    * @param bool $more_data : get all data, default false : get only items_id
    * @param string $condition
    * @return type
    */
   function selectPrinterToSearch($more_data = false, $errorHandler = false, $condition = '') {
      global $DB;

      $output = [];
      $dbu    = new DbUtils();

      $itemjoin  = $dbu->getTableForItemType("PluginPrintercountersItem_Recordmodel");
      $itemjoin3 = $dbu->getTableForItemType("PluginPrintercountersRecord");
      $itemjoin4 = $dbu->getTableForItemType("Entity");
      $itemjoin5 = $dbu->getTableForItemType($this->itemtype);

      // Repartition between all processes
      $where_multi_process = '';
      if (($this->process_nbr != -1)
              && ($this->process_id != -1)
              && ($this->process_nbr > 1)) {

         $where_multi_process = " AND `".$itemjoin."`.`items_id` % $this->process_nbr = ".($this->process_id - 1);
      }

      $query = "SELECT `".$itemjoin3."`.`id` as records_id,
                       `".$itemjoin."`.`items_id`,
                       `".$itemjoin5."`.`name` as items_name,
                       `".$itemjoin."`.`enable_automatic_record`,
                       `".$itemjoin."`.`active_mutex`,
                       TIME_TO_SEC(TIMEDIFF(NOW(),`".$itemjoin."`.`active_mutex`)) as mutex_delay,
                       max(`".$itemjoin3."`.`date`) as last_record,
                       `".$itemjoin4."`.`name` as entities_name,
                       `".$itemjoin."`.`periodicity` as periodicity_seconds,
                       (`".$itemjoin."`.`periodicity`/24/3600) as periodicity,
                       TIME_TO_SEC(TIMEDIFF(NOW(),max(`".$itemjoin3."`.`date`))) as delay,
                      `glpi_plugin_printercounters_items_recordmodels`.`status` as status
          FROM ".$itemjoin."
          LEFT JOIN `".$itemjoin3."` 
             ON (`".$itemjoin3."`.`plugin_printercounters_items_recordmodels_id` = `".$itemjoin."`.`id`)
          LEFT JOIN `".$itemjoin4."` 
             ON (`".$itemjoin3."`.`entities_id` = `".$itemjoin4."`.`id`)
          INNER JOIN `".$itemjoin5."` 
             ON (`".$itemjoin."`.`items_id` = `".$itemjoin5."`.`id`)
          WHERE LOWER(`".$itemjoin."`.`itemtype`) = LOWER('".$this->itemtype."')
          AND `".$itemjoin5."`.`is_deleted` = 0 ";

      $query .= $where_multi_process;

      $query .= " $condition GROUP BY `".$itemjoin."`.`items_id`";

      // Items in error
      $config      = new PluginPrintercountersConfig();
      $config_data = $config->getInstance();
      if ($config_data['enable_error_handler']) {
         if (!$errorHandler) {
            $query .= " HAVING (status IS NULL OR status='".PluginPrintercountersErrorItem::$NO_ERROR."')";
         } else {
            $query .= " HAVING (status IN ('".PluginPrintercountersErrorItem::$HARD_STATE."', '".PluginPrintercountersErrorItem::$SOFT_STATE."'))";
         }
      }

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         // Items in error
         if ($config_data['enable_error_handler'] && $errorHandler) {
            while ($data = $DB->fetchAssoc($result)) {
               $output[] = $data['items_id'];
            }
            // Normal
         } else {
            while ($data = $DB->fetchAssoc($result)) {
               // Is item can be fetch ?
               if (($data['delay'] >= $data['periodicity_seconds'] || $data['delay'] == null) && $data['enable_automatic_record']) {
                  // Get next record
                  if (!empty($data['last_record']) && $data['last_record'] != 'NULL') {
                     $next_record = strtotime($data['last_record']) + $data['periodicity_seconds'];
                     if ($next_record < time()) {
                        $seconds = round(((time() - $next_record) / 3600)) * 3600;
                        $next_record = $next_record + $seconds;
                     }
                  } else {
                     $next_record = time();
                  }
                  $data['next_record'] = $next_record;

                  if ($more_data) {
                     $output[] = $data;
                  } else {
                     if ($data['mutex_delay'] > self::MUTEX_TIMEOUT || $data['mutex_delay'] == null) {
                        $output[] = $data['items_id'];
                     }
                  }
               }
            }
         }
      }

      return $output;
   }

   /**
    * Function shows running processes form
    *
    * @param type $config
    * @return boolean
    */
   public function showProcesses($config) {

      if (!$this->canCreate()) {
         return false;
      }

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='6'>".self::getTypeName(2)."</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td class='center'>";
      echo "<div id='process_action_result'></div>";
      echo "<div id='process_display'>";
      $this->getProcesses();
      echo "</div>";
      echo "</td>";
      echo "</tr>";
      echo "</table></div>";
   }

   /**
    * Function get running printercounters processes
    *
    * @global type $CFG_GLPI
    */
   public function getProcesses() {
      global $CFG_GLPI;

      if (function_exists("sys_get_temp_dir")) {
         // PHP > 5.2.x
         $pidfile = sys_get_temp_dir()."/printercounters_fullsync.pid";
      } else if (DIRECTORY_SEPARATOR=='/') {
         // Unix/Linux
         $pidfile = "/tmp/printercounters_fullsync.pid";
      }

      // Sow processes only for UNIX
      if (file_exists($pidfile) && DIRECTORY_SEPARATOR=='/') {
         $nb_col = 8;

         // Get all processes data
         $pids = explode(';', file_get_contents($pidfile));
         $pids_id = [];

         $rows = [];
         foreach ($pids as &$pid) {
            if (strpos($pid, '$$$')) {
               list($id, $pid) = explode('$$$', $pid);
               $pids_id[$pid] = $id;
            }
            for ($i=1;$i<=$nb_col;$i++) {
               $output = [];
               exec("ps -f -p $pid | egrep $pid | tr -s ' ' | cut -d ' ' -f $i", $output);
               if (!empty($output)) {
                  $rows[$pid][$i] = $output;
               }
            }
         }

         // Display all processes data
         if (!empty($rows)) {
            // Get process items
            $process_items = $this->getProcessItems();

            echo "<table class='tab_cadre'>";
            // Header
            echo "<tr class='tab_bg_1'>";
            for ($i = 1; $i <= $nb_col; $i++) {
               echo "<th>".$this->getFieldName($i)."</th>";
            }
            echo "<th>".$this->getFieldName('item')."</th>";
            echo "<th></th>";
            echo "</tr>";

            // Rows
            foreach ($rows as $pid => $row) {
               echo "<tr class='tab_bg_1'>";
               foreach ($row as $cols) {
                  echo "<td>";
                  echo $cols[0];
                  echo "</td>";
               }
               echo "<td>";
               if (isset($pids_id[$pid]) && isset($process_items[$pids_id[$pid]])) {
                  foreach ($process_items[$pids_id[$pid]] as $item) {
                     echo "</br>".$item['items_link'];
                  }
               }
               echo "</td>";
               echo "<td><a onclick='printercountersActions(\"".PLUGIN_PRINTERCOUNTERS_WEBDIR."/ajax/process.php\", \"".PLUGIN_PRINTERCOUNTERS_WEBDIR."\", \"killProcess\", \"\", \"process_action_result\", $pid, \"".$this->getType()."\");' class='submit btn btn-primary printercounters_action_button'>".__('Kill process', 'printercounters')."</a></td>";
               echo "</tr>";
            }

            echo "<tr>";
            echo "<td class='tab_bg_2 center' colspan='".($nb_col+2)."'>";
            echo "<a onclick='printercountersActions(\"".PLUGIN_PRINTERCOUNTERS_WEBDIR."/ajax/process.php\", \"".PLUGIN_PRINTERCOUNTERS_WEBDIR."\", \"killProcess\", \"\", \"process_action_result\", $pids[0], \"".$this->getType()."\");' class='submit btn btn-primary printercounters_action_button'>".__('Kill all processes', 'printercounters')."</a>";
            echo "</td>";
            echo "</tr>";
            echo "</table>";

         } else {
            $this->cleanup($pidfile);
            echo __('No processes', 'printercounters');
         }

      } else {
         echo __('No processes', 'printercounters');
      }
   }

   /**
    * getFieldName
    *
    * @param type $field
    * @return type
    */
   function getFieldName($field) {

      switch ($field) {
         case 1:       return __('UID', 'printercounters');
         case 2:       return __('PID', 'printercounters');
         case 3:       return __('PPID', 'printercounters');
         case 4:       return __('C', 'printercounters');
         case 5:       return __('STIME', 'printercounters');
         case 6:       return __('TTY', 'printercounters');
         case 7:       return __('TIME', 'printercounters');
         case 8:       return __('CMD', 'printercounters');
         case 'item' : return __('Items in progress', 'printercounters');
         default : return '';
      }
   }

   /**
    * Function kill processes with sons
    *
    * @param int $pid
    * @param type $signal
    * @return string
    */
   public function killProcesses($pid, $signal) {

      $error   = false;
      $message = '';

      if (DIRECTORY_SEPARATOR=='/') {
         // Unix/Linux
         exec("ps -ef| awk '\$3 == '$pid' { print  \$2 }'", $output, $ret);
         if ($ret) {
            return 'you need ps, grep, and awk';
         }
         foreach ($output as $t) {
            if ($t != $pid) {
               $this->killProcesses($t, $signal);
            }
         }
         if (!posix_kill($pid, $signal)) {
            $message = posix_strerror(posix_get_last_error());
            $error   = true;
         }
      }

      return [$message, $error];
   }

   /**
    * Function clean pid file
    *
    * @param int $pid
    * @param type $signal
    * @return string
    */
   function cleanup($pidfile) {

      @unlink($pidfile);

      $dir = opendir(GLPI_LOCK_DIR);
      if ($dir) {
         while ($name = readdir($dir)) {
            if (strpos($name, "lock_entity") === 0) {
               unlink(GLPI_LOCK_DIR."/".$name);
            }
         }
      }
   }

   /**
   * Function get records for an item
   *
   * @global type $DB
   * @return string
   */
   function getProcessItems() {
      global $DB;

      $dbu       = new DbUtils();
      $itemjoin  = $dbu->getTableForItemType("PluginPrintercountersItem_Recordmodel");
      $itemjoin2 = $dbu->getTableForItemType($this->itemtype);

      $output = [];

      $query = "SELECT `".$itemjoin."`.`process_id`, 
                       `".$itemjoin2."`.`name` as items_name,
                       `".$itemjoin."`.`items_id`
                FROM ".$itemjoin."
                LEFT JOIN `".$itemjoin2."` 
                   ON (`".$itemjoin2."`.`id` = `".$itemjoin."`.`items_id`)";

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $link = Toolbox::getItemTypeFormURL($this->itemtype).'?id='.$data['items_id'];
            $output[$data['process_id']][] =  ['items_name' => $data['items_name'], 'items_id' => $data['items_id'], 'items_link' => "<a href='$link' target='_blank'>".$data['items_name']."</a>"];
         }
      }

      return $output;
   }

}
