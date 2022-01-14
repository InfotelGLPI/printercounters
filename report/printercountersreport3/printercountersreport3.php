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

$title = __('Printercounters', 'printercounters')." - ".$LANG['plugin_printercounters']['printercountersreport3'];

// Instantiate Report with Name
$report = new PluginReportsAutoReport($title);

//Report's search criterias
$datecriteria = New PluginReportsDateIntervalCriteria($report, 'date');
$datecriteria->setStartDate(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' - 1 YEAR')));
$datecriteria->setEndDate(date('Y-m-d H:i:s'));

//Display criterias form is needed
$report->displayCriteriasForm();

//colname with sort allowed
$columns = ['name'         => ['sorton' => 'name'],
                 'entity'       => ['sorton' => 'entity'],
                 'serial'       => ['sorton' => 'serial'],
                 'location'     => ['sorton' => 'location'],
                 'manufacturer' => ['sorton' => 'manufacturer'],
                 'model'        => ['sorton' => 'model'],
                 'date'         => ['sorton' => 'date']];

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

// Title
if ($output_type == Search::HTML_OUTPUT) {
   echo "<div class='center'>";
   echo "<table class='tab_cadre_fixe'>";
   echo "<tr class='tab_bg_1'><th>".$report->getFullTitle()."</th></tr>\n";
   echo "</table></div>";
}

$datas = [];

$endDate   = $datecriteria->getEndDate();
$startDate = $datecriteria->getEndDate();

if ($endDate != 'NULL' && $startDate != 'NULL') {
   // Get all dates between begin and end
   $format    = 'My';
   $dbu       = new DbUtils();
   $tmp_datas = getDatesBetween2Dates($datecriteria->getStartDate(), $datecriteria->getEndDate(), $format);

   $entity_restrict = $dbu->getEntitiesRestrictRequest("AND", "glpi_plugin_printercounters_budgets", "", $_SESSION['glpiactiveentities']);

   $query = "SELECT `glpi_plugin_printercounters_budgets`.*, 
                    `glpi_entities`.`level` as entities_level, 
                    `glpi_entities`.`id` as entities_id, 
                    `glpi_entities`.`entities_id` as entities_parent, 
                    `glpi_entities`.`completename` as entities_name
               FROM glpi_plugin_printercounters_budgets
               LEFT JOIN `glpi_entities` 
                  ON (`glpi_entities`.`id` = `glpi_plugin_printercounters_budgets`.`entities_id`)
               WHERE NOT (('".$datecriteria->getStartDate()."' < `glpi_plugin_printercounters_budgets`.`begin_date` 
                           AND '".$datecriteria->getEndDate()."' < `glpi_plugin_printercounters_budgets`.`begin_date`) 
                         OR 
                         ('".$datecriteria->getStartDate()."' > `glpi_plugin_printercounters_budgets`.`end_date` 
                          AND '".$datecriteria->getEndDate()."' > `glpi_plugin_printercounters_budgets`.`end_date`))
              $entity_restrict";

   $result = $DB->query($query);
   $output = [];
   if ($DB->numrows($result)) {
      while ($data = $DB->fetchAssoc($result)) {
         $data['total_page_number'] = 0;
         $data['record_amount'] = 0;
         $data['record_detail'] = [];
         $data['budgets_id'] = $data['id'];
         $output[$data['id']] = $data;
      }

      // Get record amount
      $budget = new PluginPrintercountersBudget();
      list($output, $total) = $budget->getRecordsAmountForBudget($output, PluginPrintercountersCountertype_Recordmodel::COLOR, false);

      // Records amount
      $datas = getRecordAmount($output, $tmp_datas, []);

      // Budgets amount sampled
      $sample = getBudgetAmount($output);

      if (!empty($sample)) {
         foreach ($sample as $begin => $val) {
            if (isset($tmp_datas[$begin])) {
               $datas['datas'][__('Budget')][$begin] = $val['amount'];
            }
         }
      }
   }
}

if (!empty($datas)) {
   if (!isset($datas['datas'][__('Budget')])) {
      $datas['datas'][__('Budget')] = [];
   }
   if (!isset($datas['datas'][_n('Record amount', 'Records amount', 2, 'printercounters')])) {
      $datas['datas'][_n('Record amount', 'Records amount', 2, 'printercounters')] = [];
   }

   $datas['datas'][__('Budget')]                                                = fillEmptyValues($datas['datas'][__('Budget')], $tmp_datas);
   $datas['datas'][_n('Record amount', 'Records amount', 2, 'printercounters')] = fillEmptyValues($datas['datas'][_n('Record amount', 'Records amount', 2, 'printercounters')], $tmp_datas);

   $total_record_amount = 0;

   foreach ($tmp_datas as $date => $label) {
      // Confidence rate
      $total_items   = 0;
      $success_items = 0;
      if (isset($datas['datas']['successRecord'][$date])) {
         $total_items = count($datas['datas']['successRecord'][$date]);
         foreach ($datas['datas']['successRecord'][$date] as $val) {
            if ($val > 0) {
               $success_items++;
            }
         }
      }

      if (!empty($total_items)) {
         $datas['datas'][__($LANG['plugin_printercounters']['printercountersreport3_confidencerate'], 'printercounters')][$date] = Html::formatNumber(($success_items / $total_items) * 100)." %";
      } else {
         $datas['datas'][__($LANG['plugin_printercounters']['printercountersreport3_confidencerate'], 'printercounters')][$date] = Html::formatNumber(0)." %";
      }

      // Total record amount
      $total_record_amount += $datas['datas'][_n('Record amount', 'Records amount', 2, 'printercounters')][$date];

      // Consumption rate
      if (!empty($datas['datas'][__('Budget')][$date])) {
         $datas['datas'][__($LANG['plugin_printercounters']['printercountersreport3_consumptionrate'], 'printercounters')][$date] = Html::formatNumber((($datas['datas'][_n('Record amount', 'Records amount', 2, 'printercounters')][$date] / $datas['datas'][__('Budget')][$date]) * 100)).' %';
      } else {
         $datas['datas'][__($LANG['plugin_printercounters']['printercountersreport3_consumptionrate'], 'printercounters')][$date] = Html::formatNumber((0)).' %';
      }

      // Budget
      $datas['datas'][__('Budget')][$date] = Html::formatNumber($datas['datas'][__('Budget')][$date]);

      // Record amount
      $datas['datas'][_n('Record amount', 'Records amount', 2, 'printercounters')][$date] = Html::formatNumber($datas['datas'][_n('Record amount', 'Records amount', 2, 'printercounters')][$date]);
   }

   // Extrapolation
   $extrapolation = [__($LANG['plugin_printercounters']['printercountersreport3_extrapolation'], 'printercounters') => Html::formatNumber(($total_record_amount / count($tmp_datas)) * 12)];

   // Sort values by dates
   ksort($datas['datas'][__('Budget')]);
   ksort($datas['datas'][_n('Record amount', 'Records amount', 2, 'printercounters')]);
   ksort($datas['datas'][__($LANG['plugin_printercounters']['printercountersreport3_consumptionrate'], 'printercounters')]);
   ksort($datas['datas'][__($LANG['plugin_printercounters']['printercountersreport3_confidencerate'], 'printercounters')]);

   unset($datas['datas']['successRecord']);

   // Set labels for abscissa
   $datas['labels2'] = $tmp_datas;
}

// Printer pager
if (empty($datas)) {
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
   echo "<form method='POST' action='".$_SERVER["PHP_SELF"]."'>\n";

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
}

// Show results
if (!empty($datas)) {
   $nbCols   = count($datas['labels2']);
   $nbrows   = 6;
   $num      = 1;
   $row_num  = 1;

   // Header
   echo Search::showHeader($output_type, $nbrows, $nbCols, true);
   echo Search::showNewLine($output_type);
   showTitle($output_type, $num, null, '', false);
   foreach ($datas['labels2'] as $key => $label) {
      showTitle($output_type, $num, $label, $key, false);
   }
   echo Search::showEndLine($output_type);

   // Rows
   foreach ($datas['datas'] as $key => $line) {
      $row_num++;
      $num = 1;
      echo Search::showNewLine($output_type, false);
      echo Search::showItem($output_type, $key, $num, $row_num);
      foreach ($line as $date => $data) {
         echo Search::showItem($output_type, $data, $num, $row_num);
      }
      echo Search::showEndLine($output_type);
   }

   // Extrapolation
   echo Search::showNewLine($output_type, false);
   foreach ($extrapolation as $title => $val) {
      $row_num++;
      $num = 1;
      echo Search::showItem($output_type, $title, $num, $row_num);
      echo Search::showItem($output_type, $val, $num, $row_num);
      for ($i=0; $i<=count($line)-2; $i++) {
         echo Search::showItem($output_type, '', $num, $row_num);
      }
   }
   echo Search::showEndLine($output_type);

   // Footer
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
 * Get all dates between an interval with the format
 *
 * @param type $startTime
 * @param type $endTime
 * @return type
 */
function getDatesBetween2Dates($startTime, $endTime) {

   $day = 86400;
   $startTime = strtotime($startTime);
   $endTime = strtotime($endTime);
   $numDays = round(($endTime - $startTime) / $day) + 1;
   $days = [];

   for ($i = 0; $i < $numDays; $i++) {
      $days[date('ym', ($startTime + ($i * $day)))] = __(date('F', ($startTime + ($i * $day)))).' '.date('y', ($startTime + ($i * $day)));
   }

   return $days;
}

/**
 * Fill empty values
 *
 * @global type $DB
 * @param type $area
 * @param type $configs
 * @return array
 */
function fillEmptyValues($datas, $dates) {

   foreach ($dates as $key => $val) {
      if (!isset($datas[$key])) {
         $datas[$key] = 0;
      }
   }

   return $datas;
}

 /**
 * Get budget values recursively
 *
 * @param type $budgets
 * @param type $tmp_datas
 * @param type $datas
 * @return type
 */
function getRecordAmount($budgets, $tmp_datas, $datas = []) {

   if (!empty($budgets)) {
      foreach ($budgets as $value) {
         // Get records amount
         if (!empty($value['record_detail'])) {
            foreach ($value['record_detail'] as $record) {
               $begin = date('ym', strtotime($record['date']));
               if (isset($tmp_datas[$begin])) {
                  // Get record costs
                  if (isset($datas['datas'][_n('Record amount', 'Records amount', 2, 'printercounters')][$begin])) {
                     $datas['datas'][_n('Record amount', 'Records amount', 2, 'printercounters')][$begin] += $record['record_cost'];
                  } else {
                     $datas['datas'][_n('Record amount', 'Records amount', 2, 'printercounters')][$begin] = $record['record_cost'];
                  }

                  // Get success / fail counters
                  if ($record['result'] == PluginPrintercountersRecord::$SUCCESS) {
                     if (isset($datas['datas']['successRecord'][$begin][$record['items_id']])) {
                        $datas['datas']['successRecord'][$begin][$record['items_id']]++;
                     } else {
                        $datas['datas']['successRecord'][$begin][$record['items_id']] = 1;
                     }
                  } else {
                     $datas['datas']['successRecord'][$begin][$record['items_id']] = 0;
                  }
               }
            }
         }

         // search in sons
         if (isset($value['sons']) && !isset($value['son_display'])) {
            $datas = getRecordAmount($value['sons'], $tmp_datas, $datas);
         }
      }
   }

   return $datas;
}

/**
 * Get budget amounts recursively
 *
 * @param type $budgets
 * @return type
 */
function getBudgetAmount($budgets) {

   $sample = [];

   $budgetObject = new PluginPrintercountersBudget();
   $budgets = $budgetObject->getNonRecursiveBudget($budgets);

   // Sort by entity levels ASC
   uasort($budgets, function($a, $b) {
      return ($a["budgets_level"] - $b["budgets_level"]);
   });

   foreach ($budgets as $budget) {
      // Get number of month in budget period
      $ts1 = strtotime($budget['begin_date']);
      $ts2 = strtotime($budget['end_date']);

      $monthDiff = ((date('Y', $ts2) - date('Y', $ts1)) * 12) + (date('m', $ts2) - date('m', $ts1));
      if ($monthDiff == 0) {
         $monthDiff = 1;
      }

      // Budget repartition in month
      $budget['amount'] = $budget['amount'] / $monthDiff;

      // Sample budgets amount
      for ($begin = $ts1; $begin <= $ts2; $begin += (86400 * cal_days_in_month(CAL_GREGORIAN, date('m', $begin), date('Y', $begin)))) {
         $begin_date = date('ym', $begin);

         if (isset($sample[$begin_date])) {
            switch (canAddAmount($begin, $budget, $budgets, $sample)) {
               case 3:
                  $sample[$begin_date]['amount'] += $budget['amount'];
                  $sample[$begin_date]['last_budget_added'] = $budget['budgets_id'];
                  break;

               case 2:
                  $sample[$begin_date]['amount'] = $budget['amount'];
                  $sample[$begin_date]['last_budget_added'] = $budget['budgets_id'];
                  break;
            }

         } else {
            $sample[$begin_date]['amount'] = $budget['amount'];
            $sample[$begin_date]['last_budget_added'] = $budget['budgets_id'];
         }
      }
   }

   return $sample;
}

/**
 * Check if a date can be added in budget sample
 *
 * @param type $date
 * @param type $budget
 * @param type $budgets
 * @param type $sample
 * @return int
 */
function canAddAmount($date, $budget, $budgets, $sample) {

   $budgetObject = new PluginPrintercountersBudget();

   $parents = [];
   if (isset($budgets[$budget['budgets_id']]['parent_budget'])) {
      $parents = $budgetObject->getParentTree($budgets, $budget['budgets_id']);
   }

   if (empty($parents)) {
      $parents = [0];
   }

   $last   = $sample[date('ym', $date)]['last_budget_added'];
   $amount = $sample[date('ym', $date)]['amount'];

   if (!empty($budgets)) {
      // Handle parents
      if ($date >= strtotime($budget['begin_date'])
              && $date <= strtotime($budget['end_date'])) {
         // Parent
         if (in_array($last, $parents)
                 && $budget['amount'] >= $amount) {

            return 2;

         } else if ($budget['budgets_id'] != $budgets[$last]['budgets_id']
                 && $budget['budgets_level'] == $budgets[$last]['budgets_level']) {

            return 3;
         }
      }
   }

   return 0;
}

