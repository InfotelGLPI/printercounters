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

/**
 * Update from 1.0.4 to 1.0.5
 *
 * @return bool for success (will die for most error)
 * */
function update104to105() {
   global $DB;

   $migration = new Migration(105);

   // Toner level
   $migration->addField('glpi_plugin_printercounters_recordmodels', 'enable_toner_level', 'bool', ['value' => '0']);
   $migration->addField('glpi_plugin_printercounters_recordmodels', 'enable_printer_info', 'bool', ['value' => '0']);
   $migration->addField('glpi_plugin_printercounters_configs', 'enable_toner_alert', 'bool', ['value' => '0']);
   $migration->addField('glpi_plugin_printercounters_configs', 'toner_alert_repeat', 'integer', ['value' => '0']);
   $migration->addField('glpi_plugin_printercounters_configs', 'toner_treshold', 'integer', ['value' => '0']);

   // Create additional_datas table
   $query_snmpset = "CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_additionals_datas` (
                        `id` int(11) NOT NULL auto_increment,
                        `name` varchar(255) default NULL,
                        `type` varchar(255) default NULL,
                        `sub_type` varchar(255) default NULL,
                        `value` varchar(255) default NULL,
                        `plugin_printercounters_items_recordmodels_id` int(11) NOT NULL default '0',
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `unicity` (`plugin_printercounters_items_recordmodels_id`, `sub_type`),
                        KEY `type` (`type`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

   $DB->queryOrDie($query_snmpset, "Create additional datas table");

   // Add record notification
   include_once(PLUGIN_PRINTERCOUNTERS_DIR ."/inc/notificationtargetadditional_data.class.php");
   call_user_func(["PluginPrintercountersNotificationTargetAdditional_Data",'install']);
   $migration->displayMessage("Add record notifications");

   $migration->executeMigration();

   return true;
}

