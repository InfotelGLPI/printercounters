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

function plugin_printercounters_install() {
   global $DB;

   include_once (PLUGIN_PRINTERCOUNTERS_DIR. "/inc/profile.class.php");

   // SQL creation
   if (!$DB->tableExists("glpi_plugin_printercounters_records")) {
      $DB->runFile(PLUGIN_PRINTERCOUNTERS_DIR. "/install/sql/empty-2.0.0.sql");

      // Add record notification
      include_once(PLUGIN_PRINTERCOUNTERS_DIR. "/inc/notificationtargetadditional_data.class.php");
      call_user_func(["PluginPrintercountersNotificationTargetAdditional_Data",'install']);
   }

   // Update 100 to 101
   if ($DB->tableExists("glpi_plugin_printercounters_billingmodels")
         && !$DB->fieldExists('glpi_plugin_printercounters_billingmodels', 'budgets_id')) {
      include(PLUGIN_PRINTERCOUNTERS_DIR. "/install/update_100_101.php");
      update100to101();
   }

   // Update 101 to 102
   if ($DB->tableExists("glpi_plugin_printercounters_configs")
         && !$DB->fieldExists('glpi_plugin_printercounters_configs', 'set_first_record')) {
      include(PLUGIN_PRINTERCOUNTERS_DIR. "/install/update_101_102.php");
      update101to102();
   }

   // Update 102 to 103
   $dbu = new DbUtils();
   if ($DB->tableExists("glpi_plugin_printercounters_records")
         && !$dbu->isIndex('glpi_plugin_printercounters_records', 'date')) {
      include(PLUGIN_PRINTERCOUNTERS_DIR."/install/update_102_103.php");
      update102to103();
   }

   // Update 103 to 104
   if ($DB->tableExists("glpi_plugin_printercounters_snmpauthentications")
         && !$DB->fieldExists('glpi_plugin_printercounters_snmpauthentications', 'community_write')) {
      include(PLUGIN_PRINTERCOUNTERS_DIR. "/install/update_103_104.php");
      update103to104();
   }

   // Update 104 to 105
   if (!$DB->tableExists('glpi_plugin_printercounters_additionals_datas')) {
      include(PLUGIN_PRINTERCOUNTERS_DIR. "/install/update_104_105.php");
      update104to105();
   }

   // Update 105 to 106
   if (!$DB->fieldExists('glpi_plugin_printercounters_snmpauthentications', 'is_default')) {
      include(PLUGIN_PRINTERCOUNTERS_DIR. "/install/update_105_106.php");
      update105to106();
   }

   // Update 106 to 107
   if (!$DB->tableExists("glpi_plugin_printercounters_errorrecords")) {
      include(PLUGIN_PRINTERCOUNTERS_DIR. "/install/update_106_107.php");
      update106to107();
   }

   CronTask::Register('PluginPrintercountersItem_Ticket', 'PrintercountersCreateTicket', DAY_TIMESTAMP);
   CronTask::Register('PluginPrintercountersErrorItem', 'PluginPrintercountersErrorItem', DAY_TIMESTAMP);

   PluginPrintercountersProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   PluginPrintercountersProfile::initProfile();
   $DB->query("DROP TABLE IF EXISTS `glpi_plugin_printercounters_profiles`;");

   return true;
}

// Uninstall process for plugin : need to return true if succeeded
function plugin_printercounters_uninstall() {
   global $DB;

   // Plugin tables deletion
   $tables = ["glpi_plugin_printercounters_items_tickets",
                   "glpi_plugin_printercounters_configs",
                   "glpi_plugin_printercounters_countertypes",
                   "glpi_plugin_printercounters_countertypes_recordmodels",
                   "glpi_plugin_printercounters_recordmodels",
                   "glpi_plugin_printercounters_items_recordmodels",
                   "glpi_plugin_printercounters_counters",
                   "glpi_plugin_printercounters_records",
                   "glpi_plugin_printercounters_snmpauthentications",
                   "glpi_plugin_printercounters_billingmodels",
                   "glpi_plugin_printercounters_pagecosts",
                   "glpi_plugin_printercounters_budgets",
                   "glpi_plugin_printercounters_items_billingmodels",
                   "glpi_plugin_printercounters_sysdescrs",
                   "glpi_plugin_printercounters_additionals_datas",
                   "glpi_plugin_printercounters_snmpsets"];

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   CronTask::Unregister('printercounters');

   // Add record notification
   include_once(PLUGIN_PRINTERCOUNTERS_DIR. "/inc/notificationtargetadditional_data.class.php");
   call_user_func(["PluginPrintercountersNotificationTargetAdditional_Data",'uninstall']);

   return true;
}

function plugin_datainjection_populate_printercounters() {
   global $INJECTABLE_TYPES;

   $INJECTABLE_TYPES['PluginPrintercountersCountertype_RecordmodelInjection'] = "printercounters";
   $INJECTABLE_TYPES['PluginPrintercountersItem_RecordmodelInjection']        = "printercounters";
   $INJECTABLE_TYPES['PluginPrintercountersCountertypeInjection']             = "printercounters";
   $INJECTABLE_TYPES['PluginPrintercountersRecordmodelInjection']             = "printercounters";
}

// Hook done on purge item case
function plugin_pre_item_purge_printercounters($item) {
   switch (get_class($item)) {
      case 'PluginPrintercountersRecordmodel' :
         $billingmodel = new PluginPrintercountersBillingmodel();
         $links = $billingmodel->checkLinkedRecordModels($item->getField('id'));
         if (!empty($links)) {
            Session::addMessageAfterRedirect(__('Record model cannot be deleted, it is linked to a billing model', 'printercounters').' : </br>'.implode('</br>', $links), true, ERROR);
            $item->input = false;
            return false;
         }

         $temp = new PluginPrintercountersItem_Recordmodel();
         $temp->deleteByCriteria(['plugin_printercounters_recordmodels_id' => $item->getField('id')], 1);

         $temp = new PluginPrintercountersCountertype_Recordmodel();
         $temp->deleteByCriteria(['plugin_printercounters_recordmodels_id' => $item->getField('id')], 1);

         $temp = new PluginPrintercountersBillingmodel();
         $temp->deleteByCriteria(['plugin_printercounters_recordmodels_id' => $item->getField('id')], 1);

         $temp = new PluginPrintercountersSysdescr();
         $temp->deleteByCriteria(['plugin_printercounters_recordmodels_id' => $item->getField('id')], 1);
         break;

      case 'PluginPrintercountersBillingmodel' :
         $temp = new PluginPrintercountersItem_Billingmodel();
         $temp->deleteByCriteria(['plugin_printercounters_billingmodels_id' => $item->getField('id')], 1);

         $temp = new PluginPrintercountersPagecost();
         $temp->deleteByCriteria(['plugin_printercounters_billingmodels_id' => $item->getField('id')], 1);
         break;

      case 'PluginPrintercountersCountertype' :
         $temp = new PluginPrintercountersPagecost();
         $temp->deleteByCriteria(['plugin_printercounters_countertypes_id' => $item->getField('id')], 1);

         $temp = new PluginPrintercountersCountertype_Recordmodel();
         $temp->deleteByCriteria(['plugin_printercounters_countertypes_id' => $item->getField('id')], 1);
         break;

      case 'PluginPrintercountersItem_Recordmodel' :
         $temp = new PluginPrintercountersRecord();
         $temp->deleteByCriteria(['plugin_printercounters_items_recordmodels_id' => $item->getField('id')], 1);

         $temp = new PluginPrintercountersAdditional_data();
         $temp->deleteByCriteria(['plugin_printercounters_items_recordmodels_id' => $item->getField('id')], 1);
         break;

      case 'PluginPrintercountersRecord' :
         $temp = new PluginPrintercountersCounter();
         $temp->deleteByCriteria(['plugin_printercounters_records_id' => $item->getField('id')], 1);
         break;

      case 'PluginPrintercountersCountertype_Recordmodel' :
         $billingmodel = new PluginPrintercountersBillingmodel();
         $links        = $billingmodel->checkLinkedRecordModels($item->getField('id'));
         if (!empty($links)) {
            Session::addMessageAfterRedirect(__('Counter type cannot be deleted, the record model is linked to a billing model', 'printercounters').' : </br>'.implode('</br>', $links), true, ERROR);
            $item->input = false;
            return false;
         }

         $temp = new PluginPrintercountersCounter();
         $temp->deleteByCriteria(['plugin_printercounters_countertypes_recordmodels_id' => $item->getField('id')], 1);

         $temp = new PluginPrintercountersPagecost();
         if ($data = $billingmodel->getBillingModelsForRecordmodel($item->getField('plugin_printercounters_recordmodels_id'))) {
            foreach ($data as $value) {
               $temp->deleteByCriteria(['plugin_printercounters_countertypes_id'  => $item->getField('plugin_printercounters_countertypes_id'),
                                             'plugin_printercounters_billingmodels_id' => $value['id']], 1);
            }
         }
         break;

      case 'Printer' :
         $temp = new PluginPrintercountersItem_Recordmodel();
         $temp->deleteByCriteria(['items_id' => $item->getField('id')], 1);

         $temp = new PluginPrintercountersItem_Billingmodel();
         $temp->deleteByCriteria(['items_id' => $item->getField('id')], 1);

         $temp = new PluginPrintercountersItem_Ticket();
         $temp->deleteByCriteria(['items_id' => $item->getField('id')], 1);
         break;

      case 'Ticket' :
         $temp = new PluginPrintercountersItem_Ticket();
         $temp->deleteByCriteria(['tickets_id' => $item->getField('id')], 1);
         break;

      case 'Entity' :
         $temp = new PluginPrintercountersBudget();
         $temp->deleteByCriteria(['entities_id' => $item->getField('id')], 1);
         break;
   }
}

// Hook done on purge item case
function plugin_item_purge_printercounters($item) {
   switch (get_class($item)) {
      case 'PluginPrintercountersCounter' :
         // If no counter delete record associated
         $dbu = new DbUtils();
         if ($dbu->countElementsInTable($dbu->getTableForItemType("PluginPrintercountersCounter"),
                                        ["plugin_printercounters_records_id" => $item->getField('plugin_printercounters_records_id')]) == 0) {
            $temp = new PluginPrintercountersRecord();
            $temp->deleteByCriteria(['id' => $item->getField('plugin_printercounters_records_id')], 1);
         }
         break;
   }
}

// Hook done on delete item case
function plugin_item_delete_printercounters($item) {
   switch (get_class($item)) {
      case 'Printer' :
         $temp = new PluginPrintercountersItem_Recordmodel($item->getType(), $item->getField('id'));
         $data = $temp->getItem_RecordmodelForItem();
         $data = reset($data);
         $temp->update(['id' => $data['id'], 'enable_automatic_record' => 0]);
         break;
   }
}

// Hook done on transfer item case
function plugin_item_transfer_printercounters($input) {
   switch ($input['type']) {
      case 'Printer' :
         // Recordmodel
         $recordmodel = new PluginPrintercountersRecordmodel();
         $recordmodel->duplicateRecordmodelForItem($input['type'], $input['id'], $input['entities_id']);

         // Billingmodel
         $billingmodel = new PluginPrintercountersBillingmodel();
         $billingmodel->duplicateBillingmodelForItem($input['type'], $input['id'], $input['entities_id']);
         break;
   }
}

// Define dropdown relations
function plugin_printercounters_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("printercounters")) {
      return ["glpi_entities"                                         => ["glpi_plugin_printercounters_countertypes"              => "entities_id",
                                                                                    "glpi_plugin_printercounters_recordmodels"              => "entities_id",
                                                                                    "glpi_plugin_printercounters_records"                   => "entities_id",
                                                                                    "glpi_plugin_printercounters_snmpauthentications"       => "entities_id",
                                                                                    "glpi_plugin_printercounters_billingmodels"             => "entities_id",
                                                                                    "glpi_plugin_printercounters_budgets"                   => "entities_id",
                                                                                    "glpi_plugin_printercounters_snmpsets"                  => "entities_id"],

                   "glpi_tickets"                                          => ["glpi_plugin_printercounters_items_tickets"             => "tickets_id"],

                   "glpi_printers"                                         => ["glpi_plugin_printercounters_items_billingmodels"       => "items_id",
                                                                                    "glpi_plugin_printercounters_items_recordmodels"        => "items_id"],

                   "glpi_plugin_printercounters_billingmodels"             => ["glpi_plugin_printercounters_items_billingmodels"       => "plugin_printercounters_billingmodels_id",
                                                                                    "glpi_plugin_printercounters_pagecosts"                 => "plugin_printercounters_billingmodels_id"],
                   "glpi_plugin_printercounters_recordmodels"              => ["glpi_plugin_printercounters_items_recordmodels"        => "plugin_printercounters_recordmodels_id",
                                                                                    "glpi_plugin_printercounters_sysdescrs"                 => "plugin_printercounters_recordmodels_id",
                                                                                    "glpi_plugin_printercounters_countertypes_recordmodels" => "plugin_printercounters_recordmodels_id"],

                   "glpi_plugin_printercounters_snmpauthentications"       => ["glpi_plugin_printercounters_items_recordmodels"        => "plugin_printercounters_snmpauthentications_id"],

                   "glpi_plugin_printercounters_items_recordmodels"        => ["glpi_plugin_printercounters_records"                   => "plugin_printercounters_items_recordmodels_id",
                                                                                    "glpi_plugin_printercounters_additionals_datas"          => "plugin_printercounters_items_recordmodels_id"],

                   "glpi_plugin_printercounters_records"                   => ["glpi_plugin_printercounters_counters"                  => "plugin_printercounters_records_id"],

                   "glpi_plugin_printercounters_countertypes_recordmodels" => ["glpi_plugin_printercounters_counters"                  => "plugin_printercounters_countertypes_recordmodels_id"],

                   "glpi_plugin_printercounters_countertypes"              => ["glpi_plugin_printercounters_countertypes_recordmodels" => "plugin_printercounters_countertypes_id",
                                                                                    "glpi_plugin_printercounters_pagecosts"                 => "plugin_printercounters_countertypes_id"]];
   } else {
      return [];
   }
}

// Define Dropdown tables to be manage in GLPI
function plugin_printercounters_getDropdown() {

   $plugin = new Plugin();

   if ($plugin->isActivated("printercounters")) {
      return ['PluginPrintercountersRecordmodel'         => PluginPrintercountersRecordmodel::getTypeName(2),
                   'PluginPrintercountersBillingmodel'        => PluginPrintercountersBillingmodel::getTypeName(2),
                   'PluginPrintercountersCountertype'         => PluginPrintercountersCountertype::getTypeName(2),
                   'PluginPrintercountersBudget'              => PluginPrintercountersBudget::getTypeName(2),
                   'PluginPrintercountersSnmpauthentication'  => PluginPrintercountersSnmpauthentication::getTypeName(2)];
   } else {
      return [];
   }
}

function plugin_printercounters_getAddSearchOptions($itemtype) {

   $tab = [];
   if (in_array($itemtype, PluginPrintercountersItem_Recordmodel::$types)) {
      $item_recordmodel = new PluginPrintercountersItem_Recordmodel();
      $tab = $item_recordmodel->getAddSearchOptions();
   }

   $tab2 = [];
   if (in_array($itemtype, PluginPrintercountersItem_Billingmodel::$types)) {
      $item_billingmodel = new PluginPrintercountersItem_Billingmodel();
      $tab2 = $item_billingmodel->getAddSearchOptions();
   }

   return array_replace($tab, $tab2);
}

function plugin_printercounters_MassiveActions($type) {

   switch ($type) {
      case 'Printer':
         $item_recordmodel = new PluginPrintercountersItem_Recordmodel($type);
         $output = $item_recordmodel->massiveActions();

         $item_billingmodel = new PluginPrintercountersItem_Billingmodel($type);
         $output2 = $item_billingmodel->massiveActions();

         if (!empty($output) && !empty($output2)) {
            return array_merge($output, $output2);

         } else if (!empty($output)) {
            return $output;
         }
         break;

      case 'PluginPrintercountersRecordmodel':
         $recordmodel = new PluginPrintercountersRecordmodel($type);
         $output = $recordmodel->massiveActions();
         return $output;
   }
}

function plugin_printercounters_addLeftJoin($type, $ref_table, $new_table, $linkfield, &$already_link_tables) {

   switch ($type) {
      case 'PluginPrintercountersItem_Recordmodel' :
         $item_recordmodel = new PluginPrintercountersItem_Recordmodel();
         return $item_recordmodel->addLeftJoin($type, $ref_table, $new_table, $linkfield, $already_link_tables);
   }
}

