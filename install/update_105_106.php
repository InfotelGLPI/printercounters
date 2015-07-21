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
 * Update from 1.0.5 to 1.0.6
 *
 * @return bool for success (will die for most error)
 * */
function update105to106() {

   $migration = new Migration(106);

   // Default authentication
   $migration->addField('glpi_plugin_printercounters_snmpauthentications', 'is_default', 'bool', array('value' => '0'));

   $migration->executeMigration();

   return true;
}

?>