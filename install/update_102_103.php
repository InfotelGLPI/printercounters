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
 * Update from 1.0.2 to 1.0.3
 *
 * @return bool for success (will die for most error)
 * */
function update102to103() {
   global $DB;

   $migration = new Migration(103);

   // Coutertypes recordmodel
   $migration->addKey('glpi_plugin_printercounters_countertypes_recordmodels', 'oid_type', 'oid_type');
   $migration->addKey('glpi_plugin_printercounters_countertypes_recordmodels', 'plugin_printercounters_recordmodels_id', 'plugin_printercounters_recordmodels_id');

   // Record
   $migration->addKey('glpi_plugin_printercounters_records', 'date', 'date');
   $migration->addKey('glpi_plugin_printercounters_records', 'result', 'result');
   $migration->addKey('glpi_plugin_printercounters_records', 'state', 'state');
   $migration->addKey('glpi_plugin_printercounters_records', 'record_type', 'record_type');
   $migration->addKey('glpi_plugin_printercounters_records', 'last_recordmodels_id', 'last_recordmodels_id');
   $migration->addKey('glpi_plugin_printercounters_records', 'locations_id', 'locations_id');
   $migration->addKey('glpi_plugin_printercounters_records', 'entities_id', 'entities_id');

   // Billingmodel
   $migration->addKey('glpi_plugin_printercounters_billingmodels', 'budgets_id', 'budgets_id');
   $migration->addKey('glpi_plugin_printercounters_billingmodels', 'entities_id', 'entities_id');

   $migration->executeMigration();

   return true;
}

