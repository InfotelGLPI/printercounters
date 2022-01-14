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

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE = 1;
$DBCONNECTION_REQUIRED = 1;

include ("../../../../inc/includes.php");

$title = __('Printercounters', 'printercounters')." - ".$LANG['plugin_printercounters']['printercountersreport'];

// Instantiate Report with Name
$report = new PluginReportsAutoReport($title);

//Report's search criterias
$datecriteria = New PluginReportsDateIntervalCriteria($report, 'date');
$manufacturercriteria = New PluginReportsDropdownCriteria($report, '`glpi_manufacturers`.`id`', "glpi_manufacturers", __('Manufacturer'));

$datecriteria->setStartDate(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' - 1 YEAR')));
$datecriteria->setEndDate(date('Y-m-d H:i:s'));

//Display criterias form is needed
$report->displayCriteriasForm();

//colname with sort allowed
$columns = ['name'         => ['sorton' => 'name'],
                 'serial'       => ['sorton' => 'serial'],
                 'location'     => ['sorton' => 'location'],
                 'manufacturer' => ['sorton' => 'manufacturer'],
                 'model'        => ['sorton' => 'model'],
                 'budget'       => ['sorton' => 'budget']];

$output_type = Search::HTML_OUTPUT;

if (isset($_POST['list_limit'])) {
   $_SESSION['glpilist_limit'] = $_POST['list_limit'];
   unset($_POST['list_limit']);
}
if (!isset($_REQUEST['sort'])) {
   $_REQUEST['sort'] = "name";
   $_REQUEST['order'] = "ASC";
}

$limit = $_SESSION['glpilist_limit'];

if (isset($_POST["display_type"])) {
   $output_type = $_POST["display_type"];
   if ($output_type < 0) {
      $output_type = - $output_type;
      $limit = 0;
   }
}

$dbu = new DbUtils();
// SQL statement
$entity_restrict = $dbu->getEntitiesRestrictRequest("AND", "glpi_printers", "", $_SESSION['glpiactiveentities']);
$query = "SELECT `glpi_printers`.`id`,
                 `glpi_printers`.`name` as name,
                 `glpi_printermodels`.`name` as model,
                 `glpi_manufacturers`.`name` as manufacturer,
                 `glpi_locations`.`completename` as location,
                 `glpi_budgets`.`id` as budgets_id,
                 `glpi_budgets`.`name` as budget,
                 `glpi_printers`.`serial` as serial,
                 `glpi_plugin_printercounters_records`.`date`,
                 `glpi_plugin_printercounters_records`.`id` as records_id,
                 `glpi_printers`.`is_deleted`,
                 COUNT(`glpi_plugin_printercounters_records`.`id`) as countRecords
          FROM `glpi_plugin_printercounters_items_recordmodels`
          LEFT JOIN `glpi_plugin_printercounters_records` 
             ON (`glpi_plugin_printercounters_records`.`plugin_printercounters_items_recordmodels_id` = `glpi_plugin_printercounters_items_recordmodels`.`id`)
          LEFT JOIN `glpi_printers` 
             ON (`glpi_printers`.`id` = `glpi_plugin_printercounters_items_recordmodels`.`items_id`)
          LEFT JOIN `glpi_infocoms` 
             ON (`glpi_infocoms`.`items_id` = `glpi_printers`.`id` AND `glpi_infocoms`.`itemtype` = 'Printer')
          LEFT JOIN `glpi_manufacturers` 
             ON (`glpi_manufacturers`.`id` = `glpi_printers`.`manufacturers_id`)
          LEFT JOIN `glpi_printermodels` 
             ON (`glpi_printermodels`.`id` = `glpi_printers`.`printermodels_id`)
          LEFT JOIN `glpi_budgets` 
             ON (`glpi_budgets`.`id` = `glpi_infocoms`.`budgets_id`)
          LEFT JOIN `glpi_locations` 
             ON (`glpi_locations`.`id` = `glpi_printers`.`locations_id`)
          WHERE 1 $entity_restrict ".
          $manufacturercriteria->getSqlCriteriasRestriction('AND')." 
          AND `glpi_plugin_printercounters_records`.`result` = ".PluginPrintercountersRecord::$SUCCESS."
          GROUP BY `glpi_plugin_printercounters_items_recordmodels`.`items_id`
          HAVING 
              `glpi_plugin_printercounters_records`.`date` <= '".date('Y-m-d H:i:s', strtotime($datecriteria->getEndDate()))."'
               AND `glpi_plugin_printercounters_records`.`date` >= '".date('Y-m-d H:i:s', strtotime($datecriteria->getStartDate()." - 3 MONTH"))."'
               AND (`glpi_printers`.`is_deleted` = 0 AND countRecords >= 2)
          ".
          getOrderBy($_REQUEST['sort'], $columns);

$res = $DB->query($query);
$nbtot = ($res ? $DB->numrows($res) : 0);
if ($limit) {
   $start = (isset($_GET["start"]) ? $_GET["start"] : 0);
   if ($start >= $nbtot) {
      $start = 0;
   }
   if ($start > 0 || $start + $limit < $nbtot) {
      $res = $DB->query($query." LIMIT $start,$limit");
   }
} else {
   $start = 0;
}

// Title
if ($output_type == Search::HTML_OUTPUT) {
   echo "<div class='center'>";
   echo "<table class='tab_cadre_fixe'>";
   echo "<tr class='tab_bg_1'><th>".$report->getFullTitle()."</th></tr>\n";
   echo "</table></div>";
}

// Printer pager
if ($nbtot == 0) {
   if (!$HEADER_LOADED) {
      Html::header($title, $_SERVER['PHP_SELF'], "utils", "report");
      Report::title();
   }
   echo "<div class='center'><span style=\"font-weight:bold; color:red\">".__('No item found')."</span></div>";
   Html::footer();

} else if ($output_type == Search::HTML_OUTPUT) {
   if (!$HEADER_LOADED) {
      Html::header($title, $_SERVER['PHP_SELF'], "utils", "report");
      Report::title();
   }
   echo "<div class='center'><table class='tab_cadre_fixe'>";
   echo "<tr class='tab_bg_2 center'><td class='center'>";
   echo "<form method='POST' action='".$_SERVER["PHP_SELF"]."?start=$start'>\n";

   // Keep params
   $param = "";
   foreach ($_POST as $key => $val) {
      if (is_array($val)) {
         foreach ($val as $k => $v) {
            echo "<input type='hidden' name='".$key."[$k]' value='$v' >";
            if (!empty($param)) {
               $param .= "&";
            }
            $param .= $key."[".$k."]=".urlencode($v);
         }
      } else {
         echo "<input type='hidden' name='$key' value='$val' >";
         if (!empty($param)) {
            $param .= "&";
         }
         $param .= "$key=".urlencode($val);
      }
   }
   Dropdown::showOutputFormat();
   Html::closeForm();
   echo "</td></tr>";
   echo "</table></div>";

   Html::printPager($start, $nbtot, $_SERVER['PHP_SELF'], $param);
}

// Show results
if ($res && $nbtot > 0) {
   $nbCols   = $DB->numfields($res);
   $nbrows   = $DB->numrows($res);
   $num      = 1;
   $row_num  = 1;
   $itemtype = 'Printer';

   echo Search::showHeader($output_type, $nbrows, $nbCols, true);
   echo Search::showNewLine($output_type);
   showTitle($output_type, $num, __('Printer'), 'name', true);
   showTitle($output_type, $num, __('Serial'), 'serial', true);
   showTitle($output_type, $num, __('Location'), 'location', true);
   showTitle($output_type, $num, __('Manufacturer'), 'manufacturer', true);
   showTitle($output_type, $num, __('Model'), 'model', true);
   showTitle($output_type, $num, __($LANG['plugin_printercounters']['printercountersreport_budget'], 'printercounters'), 'budget', true);
   showTitle($output_type, $num, __($LANG['plugin_printercounters']['printercountersreport_monochrome1'], 'printercounters'), 'monochrome1', false);
   showTitle($output_type, $num, __($LANG['plugin_printercounters']['printercountersreport_color1'], 'printercounters'), 'color1', false);
   showTitle($output_type, $num, __($LANG['plugin_printercounters']['printercountersreport_monochrome2'], 'printercounters'), 'monochrome2', false);
   showTitle($output_type, $num, __($LANG['plugin_printercounters']['printercountersreport_color2'], 'printercounters'), 'color2', false);
   showTitle($output_type, $num, __('Cost'), 'costs', false);
   echo Search::showEndLine($output_type);

   $record = new PluginPrintercountersRecord();

   $jalon1Minor3Month = date('Y-m-d H:i:s', strtotime($datecriteria->getStartDate()." - 3 MONTH"));
   $jalon1Plus3Month  = date('Y-m-d H:i:s', strtotime($datecriteria->getStartDate()." + 3 MONTH"));

   while ($data = $DB->fetchAssoc($res)) {
      $item_billingmodel = new PluginPrintercountersItem_Billingmodel($itemtype, $data['id']);

      // Get record start ~ 3 month
      $sub_condition = "AND `glpi_plugin_printercounters_records`.`result` = ".PluginPrintercountersRecord::$SUCCESS;
      $condition     = "AND `".$record->getTable()."`.`date` <= '".$jalon1Plus3Month."' 
                        AND `".$record->getTable()."`.`date` >= '".$jalon1Minor3Month."' ".$sub_condition;

      $record1 = $record->getRecords($data['id'], $itemtype, ['condition'     => $condition]);

      $total_monochrome_record1 = 0;
      $total_color_record1      = 0;

      if (!empty($record1)) {
         // Find closest record from start date
         $dates = [];
         foreach ($record1 as $records) {
            $dates[] = $records['date'];
         }

         $closestDate = findClosestDate($dates, $datecriteria->getStartDate());

         foreach ($record1 as $records) {
            if ($closestDate == $records['date']) {
               foreach ($records['counters'] as $counter) {
                  if ($counter['oid_type'] == PluginPrintercountersCountertype_Recordmodel::MONOCHROME || $counter['oid_type'] == PluginPrintercountersCountertype_Recordmodel::BLACKANDWHITE) {
                     $total_monochrome_record1 += $counter['counters_value'];
                  }

                  if ($counter['oid_type'] == PluginPrintercountersCountertype_Recordmodel::COLOR) {
                     $total_color_record1 += $counter['counters_value'];
                  }
               }
               break;
            }
         }
      }

      // Get record end ~ start
      $sub_condition = "AND `glpi_plugin_printercounters_records`.`result` = ".PluginPrintercountersRecord::$SUCCESS;
      $condition     = "AND `".$record->getTable()."`.`date` >= '".$datecriteria->getStartDate()."'
                        AND `".$record->getTable()."`.`date` <= '".$datecriteria->getEndDate()."' ".$sub_condition;

      $record2 = $record->getRecords($data['id'], $itemtype, ['condition'     => $condition,
                                                                   'sub_condition' => $sub_condition,
                                                                   'last_record'   => true,
                                                                   'record_date'   => $datecriteria->getEndDate()]);
      $total_monochrome_record2 = 0;
      $total_color_record2      = 0;
      foreach ($record2 as $records) {
         foreach ($records['counters'] as $counter) {
            if ($counter['oid_type'] == PluginPrintercountersCountertype_Recordmodel::MONOCHROME || $counter['oid_type'] == PluginPrintercountersCountertype_Recordmodel::BLACKANDWHITE) {
               $total_monochrome_record2 += $counter['counters_value'];
            }

            if ($counter['oid_type'] == PluginPrintercountersCountertype_Recordmodel::COLOR) {
               $total_color_record2 += $counter['counters_value'];
            }
         }
      }

      // Get all records
      $condition = " AND `".$record->getTable()."`.`date` <= '".$datecriteria->getEndDate()."' 
                     AND `".$record->getTable()."`.`date` >= '".$datecriteria->getStartDate()."'";
      $records = $record->getRecords($data['id'], $itemtype, ['condition' => $condition]);
      $records = $item_billingmodel->computeRecordCost($records);

      $row_num++;
      $num = 1;
      echo Search::showNewLine($output_type, false, $data['is_deleted']);
      echo Search::showItem($output_type, "<a href='".$CFG_GLPI['root_doc']."/front/printer.form.php?id=".$data['id']."' target='_blank'>".$data['name']."</a>", $num, $row_num);
      echo Search::showItem($output_type, $data['serial'], $num, $row_num);
      echo Search::showItem($output_type, $data['location'], $num, $row_num);
      echo Search::showItem($output_type, $data['manufacturer'], $num, $row_num);
      echo Search::showItem($output_type, $data['model'], $num, $row_num);
      echo Search::showItem($output_type, "<a href='".$CFG_GLPI['root_doc']."/front/budget.form.php?id=".$data['budgets_id']."' target='_blank'>".$data['budget']."</a>", $num, $row_num);
      echo Search::showItem($output_type, $total_monochrome_record1, $num, $row_num);
      echo Search::showItem($output_type, $total_color_record1, $num, $row_num);
      echo Search::showItem($output_type, $total_monochrome_record2, $num, $row_num);
      echo Search::showItem($output_type, $total_color_record2, $num, $row_num);
      echo Search::showItem($output_type, Html::formatNumber($records['total_record_cost']), $num, $row_num);
      echo Search::showEndLine($output_type);
   }
   echo Search::showFooter($output_type, $title);
}

if ($output_type == Search::HTML_OUTPUT) {
   Html::footer();
}

/**
 * Display the column title and allow the sort
 *
 * @param $output_type
 * @param $num
 * @param $title
 * @param $columnname
 * @param bool $sort
 * @return mixed
 */
function showTitle($output_type, &$num, $title, $columnname, $sort = false) {

   if ($output_type != Search::HTML_OUTPUT || $sort == false) {
      echo Search::showHeaderItem($output_type, $title, $num);
      return;
   }
   $order = 'ASC';
   $issort = false;
   if (isset($_REQUEST['sort']) && $_REQUEST['sort'] == $columnname) {
      $issort = true;
      if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'ASC') {
         $order = 'DESC';
      }
   }
   $link = $_SERVER['PHP_SELF'];
   $first = true;
   foreach ($_REQUEST as $name => $value) {
      if (!in_array($name, ['sort', 'order', 'PHPSESSID'])) {
         $link .= ($first ? '?' : '&amp;');
         $link .= $name.'='.urlencode($value);
         $first = false;
      }
   }
   $link .= ($first ? '?' : '&amp;').'sort='.urlencode($columnname);
   $link .= '&amp;order='.$order;
   echo Search::showHeaderItem($output_type, $title, $num, $link, $issort, ($order == 'ASC' ? 'DESC' : 'ASC'));
}

/**
 * Build the ORDER BY clause
 *
 * @param $default string, name of the column used by default
 * @return string
 */
function getOrderBy($default, $columns) {

   if (!isset($_REQUEST['order']) || $_REQUEST['order'] != 'DESC') {
      $_REQUEST['order'] = 'ASC';
   }
   $order = $_REQUEST['order'];

   $tab = getOrderByFields($default, $columns);
   if ((is_array($tab) ? count($tab) : 0) > 0) {
      return " ORDER BY ".$tab." ".$order;
   }
   return '';
}

/**
 * Get the fields used for order
 *
 * @param $default string, name of the column used by default
 *
 * @return array of column names
 */
function getOrderByFields($default, $columns) {

   if (!isset($_REQUEST['sort'])) {
      $_REQUEST['sort'] = $default;
   }
   $colsort = $_REQUEST['sort'];

   foreach ($columns as $colname => $column) {
      if ($colname == $colsort) {
         return $column['sorton'];
      }
   }
   return [];
}

/**
 * Get the closest date
 *
 * @param type $array
 * @param type $date
 */
function findClosestDate($array, $date) {

   $interval = [];
   foreach ($array as $day) {
      $interval[] = abs(strtotime($date) - strtotime($day));
   }

   asort($interval);
   $closest = key($interval);

   return $array[$closest];
}

