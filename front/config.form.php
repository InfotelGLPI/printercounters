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

include ('../../../inc/includes.php');

$plugin = new Plugin();

if ($plugin->isActivated("printercounters")) {
   $config = new PluginPrintercountersConfig();
   if (!$config->canCreate()) {
      Html::displayRightError();
   }

   if (isset($_POST['items_status']) && !empty($_POST['items_status'])) {
      $_POST['items_status'] = json_encode($_POST['items_status']);
   } else {
      $_POST['items_status'] = "";
   }

   if (isset($_POST["update_config"])) {
      $dbu = new DbUtils();
      if (!$dbu->countElementsInTable("glpi_plugin_printercounters_configs", ["id" => 1])) {
         $config->add($_POST);
      } else {
         $_POST['id'] = 1;
         $config->update($_POST);
      }
      Html::back();

   } else if (isset($_POST["clean_error_records"])) {
      $record = new PluginPrintercountersRecord();
      if ($records_id = $record->getRecordsToClean(PluginPrintercountersRecord::$CLEAN_ERROR_RECORDS, ['date' => $_POST['date']])) {
         Html::header(PluginPrintercountersConfig::getTypeName(), '', "tools", "pluginprintercountersmenu", "config");
         $record->initCleanRecords(__('Clean records in error', 'printercounters'), $records_id);
         Html::footer();
         Session::addMessageAfterRedirect(__('Records cleaned', 'printercounters'));

      } else {
         Session::addMessageAfterRedirect(__('No records to clean', 'printercounters'));
         Html::back();
      }

   } else if (isset($_POST["clean_empty_records"])) {
      $record = new PluginPrintercountersRecord();
      if ($records_id = $record->getRecordsToClean(PluginPrintercountersRecord::$CLEAN_EMTPY_RECORDS, ['date' => $_POST['date']])) {
         Html::header(PluginPrintercountersConfig::getTypeName(), '', "tools", "pluginprintercountersmenu", "config");
         $record->initCleanRecords(__('Clean empty records', 'printercounters'), $records_id);
         Html::footer();
         Session::addMessageAfterRedirect(__('Records cleaned', 'printercounters'));

      } else {
         Session::addMessageAfterRedirect(__('No records to clean', 'printercounters'));
         Html::back();
      }

   } else {
      Html::header(PluginPrintercountersConfig::getTypeName(), '', "tools", "pluginprintercountersmenu", "config");
      $data = $config->getInstance();
      $config->display(['id' => $data['configs_id']]);
      Html::footer();
   }

} else {
   Html::header(PluginPrintercountersConfig::getTypeName(), '', "tools", "pluginprintercountersmenu", "config");
   echo "<div class='alert alert-important alert-warning d-flex'>";
   echo "<b>".__('Please activate the plugin', 'printercounters')."</b></div>";
   Html::footer();
}

