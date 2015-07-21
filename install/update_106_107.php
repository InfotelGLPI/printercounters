<?php

/*
  -------------------------------------------------------------------------
  Printercounters plugin for GLPI
  Copyright (C) 2003-2012 by the Printercounters Development Team.

  https://forge.indepnet.net/projects/Printercounters
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Printercounters.

  Printercounters is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Printercounters is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Printercounters. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

/**
 * Update from 1.0.6 to 1.0.7
 *
 * @return bool for success (will die for most error)
 * */
function update106to107() {

   $migration = new Migration(107);
   
   // Add item_recordmodel fields for items in error
   $migration->addField('glpi_plugin_printercounters_items_recordmodels', 'status', 'int', array('value' => '0'));
   $migration->addField('glpi_plugin_printercounters_items_recordmodels', 'error_counter', 'int', array('value' => '0'));
   
   // Maximum number of interrogation for records in error
   $migration->addField('glpi_plugin_printercounters_configs', 'max_error_counter', 'int', array('value' => '3'));
   $migration->addField('glpi_plugin_printercounters_configs', 'enable_error_handler', 'int', array('value' => '0'));

   $migration->executeMigration();

   return true;
}

?>