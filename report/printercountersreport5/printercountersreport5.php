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
$DBCONNECTION_REQUIRED = 0;

include ("../../../../inc/includes.php");

$title = __('Printercounters', 'printercounters')." - ".$LANG['plugin_printercounters']['printercountersreport5'];

// Instantiate Report with Name
$report = new PluginReportsAutoReport($title);

//Report's search criterias
$datecriteria = new PluginReportsDateIntervalCriteria($report, 'date');
$datecriteria->setStartDate(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' - 1 MONTH')));
$datecriteria->setEndDate(date('Y-m-d H:i:s'));

$printer = new PluginReportsDropdownCriteria($report, 'glpi_printers.id', "glpi_printers", __('Printer'));
$printerModel = new PluginReportsDropdownCriteria($report, 'glpi_printermodels.id', "glpi_printermodels", __('Printer model'));
$printerLocation = new PluginReportsDropdownCriteria($report, 'glpi_locations.id', "glpi_locations", __('Location'),'ID IN (SELECT locations_id FROM glpi_printers group by locations_id)');
$printerIp = New PluginReportsDropdownCriteria($report, 'glpi_ipnetworks.id', "glpi_ipnetworks", __('IP network'));

//Display criterias form is needed
$report->displayCriteriasForm();

$display_type = Search::HTML_OUTPUT;

//colname with sort allowed
$columns = ['name'          => ['sorton' => 'name'],
            'printer_model' => ['sorton' => 'printer_model'],
            'counter_type'  => ['sorton' => 'counter_type'],
            'IP'            => ['sorton' => 'IP'],
            'location'      => ['sorton' => 'location'],
            'counter'       => ['sorton' => 'counter'],
            'date'          => ['sorton' => 'date']];

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

    $ip = "(SELECT name 
            FROM `glpi_ipaddresses` 
            WHERE mainitemtype='Printer' and mainitems_id=`glpi_printers`.`id`)";
    $where = "";
    if(isset($_POST['glpi_printers_id']) && $_POST['glpi_printers_id'] > 0){
        $where .= " AND `glpi_printers`.`id` = ".$_POST['glpi_printers_id'];
        $ip = "(SELECT name 
            FROM `glpi_ipaddresses` 
            WHERE mainitemtype='Printer' and mainitems_id=" . $_POST['glpi_printers_id'] .")";
    }
    if(isset($_POST['glpi_printermodels_id']) && $_POST['glpi_printermodels_id'] > 0){
        $where .= " AND `glpi_printermodels`.`id` = ".$_POST['glpi_printermodels_id'];
    }
    if(isset($_POST['glpi_locations_id']) && $_POST['glpi_locations_id'] > 0){
        $where .= " AND `glpi_printers`.`locations_id` = ".$_POST['glpi_locations_id'];
    }
    if(isset($_POST['glpi_ipnetworks_id']) && $_POST['glpi_ipnetworks_id'] > 0) {

        $where .= " AND SUBSTRING_INDEX((SELECT SUBSTR(name,1,LOCATE('/',name)-1) 
                                         FROM glpi_ipnetworks 
                                         WHERE id = " . $_POST['glpi_ipnetworks_id'] ."), '.',3) = 
                        SUBSTRING_INDEX(" . $ip . ", '.', 3)";
    }
    if(isset($_POST['date_1']) && $_POST['date_1'] > 0 &&
       isset($_POST['date_2']) && $_POST['date_2'] > 0 ) {
        $where .= " AND `glpi_plugin_printercounters_records`.`date` BETWEEN '" . $_POST['date_1'] . " 00:00:00' AND '" . $_POST['date_2'] ." 23:59:59'";
    }

$dbu = new DbUtils();
    $query = "SELECT `glpi_printers`.`id`,
                 `glpi_printers`.`name` as name,
                 `glpi_printermodels`.`name` as printer_model,
                 `glpi_printertypes`.`name` as type,
                 `glpi_plugin_printercounters_records`.`date`,
                 `glpi_printers`.`is_deleted`,
                 `glpi_plugin_printercounters_countertypes`.`name` as counter_type,
                 `glpi_plugin_printercounters_countertypes_recordmodels`.`oid_type`,              
                 $ip as IP,
                 `glpi_locations`.`completename` as location,
                 MAX(`glpi_plugin_printercounters_counters`.`value`) as counter   
          FROM `glpi_plugin_printercounters_items_recordmodels`
          LEFT JOIN `glpi_plugin_printercounters_records`
             ON (`glpi_plugin_printercounters_records`.`plugin_printercounters_items_recordmodels_id` = `glpi_plugin_printercounters_items_recordmodels`.`id`)
          LEFT JOIN `glpi_printers`
             ON (`glpi_printers`.`id` = `glpi_plugin_printercounters_items_recordmodels`.`items_id`)
          LEFT JOIN `glpi_infocoms`
             ON (`glpi_infocoms`.`items_id` = `glpi_printers`.`id` AND `glpi_infocoms`.`itemtype` = 'Printer')
          LEFT JOIN `glpi_printermodels` 
             ON (`glpi_printermodels`.`id` = `glpi_printers`.`printermodels_id`)
          LEFT JOIN `glpi_printertypes` 
             ON (`glpi_printertypes`.`id` = `glpi_printers`.`printertypes_id`)
          LEFT JOIN `glpi_plugin_printercounters_counters`
          	 ON (`glpi_plugin_printercounters_counters`.`plugin_printercounters_records_id` = `glpi_plugin_printercounters_records`.`id`)
          LEFT JOIN `glpi_plugin_printercounters_countertypes_recordmodels` 
          	 ON (`glpi_plugin_printercounters_countertypes_recordmodels`.`id` = `glpi_plugin_printercounters_counters`.`plugin_printercounters_countertypes_recordmodels_id`)    
          LEFT JOIN `glpi_plugin_printercounters_countertypes` 
          	 ON (`glpi_plugin_printercounters_countertypes`.`id` = `glpi_plugin_printercounters_countertypes_recordmodels`.`plugin_printercounters_countertypes_id`)    
          LEFT JOIN `glpi_networks`
             ON (`glpi_networks`.`id` = `glpi_printers`.`networks_id`)
          LEFT JOIN `glpi_locations`
             ON (`glpi_locations`.`id` = `glpi_printers`.`locations_id`)    
          WHERE 1 $where
          GROUP BY `glpi_plugin_printercounters_items_recordmodels`.`items_id`,`glpi_plugin_printercounters_countertypes`.`name`".
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

      echo Search::showHeader($output_type, $nbrows, $nbCols, true);
      echo Search::showNewLine($output_type);
      showTitle($output_type, $num, __('Printer'), 'name', true);
      showTitle($output_type, $num, __('Printer model'), 'printer_model', true);
      showTitle($output_type, $num,  __('Counter type','printercounters'), 'counter_type', true);
      showTitle($output_type, $num,  __('OID type','printercounters'), 'counter_type', true);
      showTitle($output_type, $num, __('IP address'), 'IP', true);
      showTitle($output_type, $num, __('Location'), 'location', true);
      showTitle($output_type, $num, __('Counter'), 'counter', true);
      showTitle($output_type, $num, __('Last record date','printercounters'), 'date', true);
      echo Search::showEndLine($output_type);


      $items = [];
      $datas = [];
      while ($data = $DB->fetchAssoc($res)) {
         $items[] = $data['id'];
         $datas[] = $data;
      }

      foreach ($datas as $data) {
         $row_num++;
         $num = 1;
         echo Search::showNewLine($output_type, false, $data['is_deleted']);
         echo Search::showItem($output_type, "<a href='".$CFG_GLPI['root_doc']."/front/printer.form.php?id=".$data['id']."' target='_blank'>".$data['name']."</a>", $num, $row_num);
         echo Search::showItem($output_type, $data['printer_model'], $num, $row_num);
         echo Search::showItem($output_type, $data['counter_type'], $num, $row_num);
         echo Search::showItem($output_type, PluginPrintercountersCountertype_Recordmodel::getOidType($data['oid_type']), $num, $row_num);
         echo Search::showItem($output_type, $data['IP'], $num, $row_num);
         echo Search::showItem($output_type, $data['location'], $num, $row_num);
         echo Search::showItem($output_type, $data['counter'], $num, $row_num);
         echo Search::showItem($output_type, $data['date'], $num, $row_num);
         echo Search::showEndLine($output_type);
      }
      echo Search::showFooter($output_type, $title);
//   }

//   if ($output_type == Search::HTML_OUTPUT) {
//      Html::footer();
//   }
}
if ($display_type == Search::HTML_OUTPUT) {
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
