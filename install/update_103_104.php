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
 * Update from 1.0.3 to 1.0.4
 *
 * @return bool for success (will die for most error)
 * */
function update103to104() {
   global $DB;

   $migration = new Migration(104);

   // Snmp authentications
   $migration->addKey('glpi_plugin_printercounters_snmpauthentications', 'entities_id', 'entities_id');
   $migration->addField('glpi_plugin_printercounters_snmpauthentications', 'community_write', 'string', ['value' => 'private']);

   // Profile
   $migration->addField('glpi_plugin_printercounters_profiles', 'snmpset', 'bool', ['value' => 0]);
   $query_profile = "ALTER TABLE `glpi_plugin_printercounters_profiles`
                        MODIFY COLUMN `add_lower_records` tinyint(1) NOT NULL default '0',
                        MODIFY COLUMN `update_records` tinyint(1) NOT NULL default '0';";

   $DB->queryOrDie($query_profile, "Change profile column types");

   // Create snmpset table
   $query_snmpset = "CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_snmpsets` (
                        `id` int(11) NOT NULL auto_increment,
                        `entities_id` int(11) NOT NULL default '0',
                        `is_recursive` tinyint(1) NOT NULL default '0',
                        `set_location` tinyint(1) NOT NULL default '1',
                        `set_contact` tinyint(1) NOT NULL default '1',
                        `set_name` tinyint(1) NOT NULL default '1',
                        `contact` text COLLATE utf8_unicode_ci,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `unicity` (`entities_id`),
                        KEY `entities_id` (`entities_id`)
                     ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

   $DB->queryOrDie($query_snmpset, "Create snmpset table");

   $migration->executeMigration();

   return true;
}

