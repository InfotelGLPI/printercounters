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
 * Class PluginPrintercountersAdditional_data
 *
 * This class allows to add additional data of printers (like toner level, drum level ...)
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersAdditional_data extends CommonDBTM {

   var $items_id;
   var $itemtype;

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

   static function getTypeName($nb = 2) {
      return __('Printer counters', 'printercounters')." : "._n("Additional data", "Additional datas", $nb, 'printercounters');
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
    * Function send notification for additional data
    *
    * @global type $DB
    * @global type $CFG_GLPI
    * @param type $additionalData
    */
   public function alertAdditionalData($additionalData) {
      global $DB, $CFG_GLPI;

      $config = PluginPrintercountersConfig::getInstance();

      if ($CFG_GLPI["notifications_mailing"] && $config['enable_toner_alert']) {
         $alert   = new Alert();

         $types = [PluginPrintercountersPrinter::TONER_TYPE, PluginPrintercountersPrinter::DRUM_TYPE];

         // if you change this query, please don't forget to also change in showDebug()
         $query_alert = "SELECT `glpi_plugin_printercounters_additionals_datas`.`id` as additional_datas_id,
                                `glpi_alerts`.`id` as alerts_id,
                                `glpi_alerts`.`date` as alerts_date
                        FROM `glpi_plugin_printercounters_additionals_datas`
                        LEFT JOIN `glpi_alerts`
                           ON (`glpi_plugin_printercounters_additionals_datas`.`id` = `glpi_alerts`.`items_id`
                               AND `glpi_alerts`.`itemtype` = 'PluginPrintercountersAdditional_Data')
                        WHERE `glpi_plugin_printercounters_additionals_datas`.`type` IN ('".implode("','", $types)."');";

         $items   = [];

         foreach ($DB->request($query_alert) as $data) {
            $alert_date = strtotime($data['alerts_date']);
            foreach ($additionalData['additional_datas'] as $val) {
               switch ($val['type']) {
                  case PluginPrintercountersPrinter::TONER_TYPE :
                     $repeat   = $config['toner_alert_repeat'];
                     $treshold = $config['toner_treshold'];
                     break;
               }
               if ($val['id'] == $data['additional_datas_id']
                       && $val['value'] <= $treshold
                       && ($alert_date+$repeat) < time()) {

                  $items[$val['id']] = $val;
                  // if alert exists -> delete
                  if (!empty($data['alerts_id'])) {
                     $alert->delete(["id" => $data['alerts_id']]);
                  }
                  break;
               }
            }
         }

         // Send notification
         if (!empty($items)) {
            foreach ($types as $type) {
               switch ($type) {
                  case PluginPrintercountersPrinter::TONER_TYPE:
                     $options['items']    = $items;
                     $options['items_id'] = $additionalData['items_id'];
                     $options['itemtype'] = $additionalData['itemtype'];
                     if (NotificationEvent::raiseEvent($type.'_alert', new PluginPrintercountersAdditional_data(),
                                                       $options)) {
                        $input["type"]     = Alert::THRESHOLD;
                        $input["itemtype"] = 'PluginPrintercountersAdditional_Data';

                        // add alerts
                        foreach ($items as $ID => $data) {
                           $input["items_id"] = $ID;
                           $alert->add($input);
                        }
                     }
                     break;
               }
            }
         }
      }
   }

   /**
    * Function set additional data for a printer
    *
    * @param array    $input : array('type'
     'name'
     'value'
     'plugin_printecounters_items_recordmodels_id')
    */
   public function setAdditionalData($input) {

      if (!empty($input['additional_datas'])) {
         // Find previous data of printer
         $found_datas = $this->find(['plugin_printercounters_items_recordmodels_id' => $input['items_recordmodels_id']]);

         foreach ($input['additional_datas'] as $key => &$val) {
            $found = 0;
            if (!empty($found_datas)) {
               foreach ($found_datas as $data) {
                  if ($data['type'] == $val['type']
                          && $data['sub_type'] == $val['sub_type']
                          && $data['plugin_printercounters_items_recordmodels_id'] == $input['items_recordmodels_id']) {
                     $found = $data['id'];
                     break;
                  }
               }
            }

            if ($found) {
               // Update
               $val['id'] = $found;
               $this->update(['id'                                           => $val['id'],
                                   'type'                                         => $val['type'],
                                   'sub_type'                                     => $val['sub_type'],
                                   'name'                                         => addslashes($val['name']),
                                   'value'                                        => addslashes($val['value']),
                                   'plugin_printercounters_items_recordmodels_id' => $input['items_recordmodels_id']]);
            } else {
               // Add
               if ($val['type'] == PluginPrintercountersPrinter::OTHER_TYPE && empty($val['value'])) {
                  $val['id'] = 0;
                  continue;
               }
               $val['id'] = $this->add(['type'                                         => $val['type'],
                                             'sub_type'                                     => $val['sub_type'],
                                             'name'                                         => addslashes($val['name']),
                                             'value'                                        => addslashes($val['value']),
                                             'plugin_printercounters_items_recordmodels_id' => $input['items_recordmodels_id']]);
            }
         }
         $this->alertAdditionalData($input);
      }

      return false;
   }

   /**
    * Function show additional data
    */
   function showAdditionalData() {
      global $DB;

      $dbu       = new DbUtils();
      $itemjoin  = "glpi_plugin_printercounters_additionals_datas";
      $itemjoin2 = $dbu->getTableForItemType('PluginPrintercountersItem_Recordmodel');
      $itemjoin3 = $dbu->getTableForItemType('PluginPrintercountersRecordmodel');

      $query = "SELECT `".$itemjoin."`.`id`,
                       `".$itemjoin."`.`name`,
                       `".$itemjoin."`.`type`,
                       `".$itemjoin."`.`sub_type`,
                       `".$itemjoin."`.`value`,
                       `".$itemjoin."`.`plugin_printercounters_items_recordmodels_id`,
                       `".$itemjoin3."`.`enable_toner_level`,
                       `".$itemjoin3."`.`enable_printer_info`
          FROM $itemjoin
          LEFT JOIN `".$itemjoin2."` 
             ON(`".$itemjoin2."`.`id` = `".$itemjoin."`.`plugin_printercounters_items_recordmodels_id`)
          LEFT JOIN `".$itemjoin3."` 
             ON(`".$itemjoin3."`.`id` = `".$itemjoin2."`.`plugin_printercounters_recordmodels_id`)
          WHERE `".$itemjoin2."`.`items_id` = ".$this->items_id."
          AND `".$itemjoin2."`.`itemtype`='".$this->itemtype."'
          ORDER BY sub_type ASC";

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         $colors = [PluginPrintercountersPrinter::CARTRIDGE_COLOR_BLACK,
                         PluginPrintercountersPrinter::CARTRIDGE_COLOR_CYAN,
                         PluginPrintercountersPrinter::CARTRIDGE_COLOR_MAGENTA,
                         PluginPrintercountersPrinter::CARTRIDGE_COLOR_YELLOW];

         $toner  = [];
         $other  = [];
         $enbale = [];
         while ($data = $DB->fetchAssoc($result)) {
            switch ($data['type']) {
               case PluginPrintercountersPrinter::TONER_TYPE;
                  $toner[] = $data;
                  break;
               case PluginPrintercountersPrinter::OTHER_TYPE;
                  $other[] = $data;
                  break;
            }
            $enbale['enable_toner_level']  = $data['enable_toner_level'];
            $enbale['enable_printer_info'] = $data['enable_printer_info'];
         }

         if ($enbale['enable_toner_level'] || $enbale['enable_printer_info']) {
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'>";
            if ($enbale['enable_toner_level']) {
               echo "<th>"._n('Toner level', 'Toner levels', 2, 'printercounters')."</th>";
            }
            if ($enbale['enable_printer_info']) {
               echo "<th>".__('Printer informations', 'printercounters')."</th>";
            }
            echo "</tr>";
            echo "<tr class='tab_bg_1'>";
            if ($enbale['enable_toner_level']) {
               echo "<td>";
               // Toner level
               if (!empty($toner)) {
                  echo "<table class='tab_cadre'>";
                  foreach ($toner as $data) {
                     if ($data['type'] == PluginPrintercountersPrinter::TONER_TYPE) {
                        if (empty($data['value'])) {
                           $data['value'] = 0;
                        }
                        echo "<tr class='tab_bg_1'>";
                        echo "<td>".$data['name']."</td>";
                        $class = 'printercounters_toner_level_other';
                        foreach ($colors as $color) {
                           if (preg_match('/('.$color.')/i', $data['sub_type'], $matches)) {
                              $class = 'printercounters_toner_level_'.strtolower($matches[1]);
                              break;
                           }
                        }
                        echo "<td><div class='printercounters_toner_level'><div class='printercounters_toner_level $class' style='width:".$data['value']."%'></div></div></td>";
                        echo "<td>".$data['value']." %</td>";
                        echo "</tr>";
                     }
                  }
                  echo "</table>";
               }
               echo "</td>";
            }

            if ($enbale['enable_printer_info']) {
               echo "<td>";
               // Other informations
               if (!empty($other)) {
                  echo "<table class='tab_cadre'>";
                  foreach ($other as $data) {
                     if ($data['type'] == PluginPrintercountersPrinter::OTHER_TYPE) {
                        echo "<tr class='tab_bg_1'>";
                        echo "<td>".$data['name']."</td>";
                        switch ($data['sub_type']) {
                           case PluginPrintercountersPrinter::PRINTER_UPTIME:
                              if (preg_match('/\((\d+\))/i', $data['value'], $matches)) {
                                 echo "<td>".$this->convertTime(intval($matches[1])/100)."</td>";
                              } else {
                                 echo "<td>".$data['value']."</td>";
                              }
                              break;
                           default:
                              echo "<td>".$data['value']."</td>";
                              break;
                        }
                        echo "</tr>";
                     }
                  }
                  echo "</table>";
               }
               echo "</td>";
            }
            echo "</tr>";
            echo "</table>";
         }
      }
   }

   /**
    * Function convert seconds to time string
    */
   function convertTime($timestamp) {

      $units = Toolbox::getTimestampTimeUnits($timestamp);

      return sprintf(__('%1$s%2$d days %3$d hours %4$d minutes %5$d seconds'),
              '', $units['day'], $units['hour'], $units['minute'], $units['second']);
   }

}
