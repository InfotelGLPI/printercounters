<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 printercounters plugin for GLPI
 Copyright (C) 2014-2016 by the printercounters Development Team.

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

define('GLPI_ROOT', '../..');
include (GLPI_ROOT."/inc/includes.php");

//change mimetype
header("Content-type: application/javascript");

//not executed in self-service interface & right verification
if ($_SESSION['glpiactiveprofile']['interface'] == "central") {
   // Get item type
   $itemtype = PluginPrintercountersItem_Recordmodel::$types;

   if (!empty($itemtype)) {
      $params = array('root_doc'    => $CFG_GLPI['root_doc'],
                      'itemtype'    => $itemtype[0],
                      'itemToShow'  => 'Infocom',
                      'glpi_tab'    => 'Infocom$1',
                      'lang'        => array('global_tco' => __('Global TCO', 'printercounters')));

      echo "printercounters_addelements(".json_encode($params).");";
   }
}
?>
