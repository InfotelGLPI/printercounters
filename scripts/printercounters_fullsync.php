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

ini_set("memory_limit", "-1");
ini_set("max_execution_time", "0");

// Converts cli parameter to web parameter for compatibility
if (isset($_SERVER["argv"]) && !isset($argv)) {
   $argv = $_SERVER["argv"];
}
if ($argv) {
   for ($i = 1; $i < count($argv); $i++) {
      $it = explode("=", $argv[$i], 2);
      $it[0] = preg_replace('/^--/', '', $it[0]);
      $_GET[$it[0]] = $it[1];
   }
}

// Can't run on MySQL replicate
$USEDBREPLICATE = 0;
$DBCONNECTION_REQUIRED = 1;


include ('../../../inc/includes.php');

$_SESSION["glpicronuserrunning"] = $_SESSION["glpiname"] = 'printercounters';
// Check PHP Version - sometime (debian) cli version != module version
if (version_compare(phpversion(), '5.4', 'lt')) {
   die("PHP version:".phpversion()." - "."You must install at least PHP 5.4\n\n");
}
// Chech Memory_limit - sometine cli limit (php-cli.ini) != module limit (php.ini)
$mem = Toolbox::getMemoryLimit();
if (($mem > 0) && ($mem < (64 * 1024 * 1024))) {
   die("PHP memory_limit = ".$mem." - "."A minimum of 64Mio is commonly required for GLPI.'\n\n");
}

//Check if plugin is installed
$plugin = new Plugin();
$config = PluginPrintercountersConfig::getInstance();

if ($plugin->isActivated("printercounters") && !$config['disable_autosearch']) {
   $sonprocess_nbr = $_GET['sonprocess_nbr'];
   $sonprocess_id  = $_GET['sonprocess_id'];
   $itemtype       = $_GET['itemtype'];
   $record_type    = $_GET['record_type'];

   switch ($record_type) {
      case 'error':
         // Init error record
         $record = new PluginPrintercountersErrorItem($itemtype, 0);
         list($messages, $error) = $record->initRecord($sonprocess_id, $sonprocess_nbr);
         break;
      case 'normal':
         // Init record
         $record = new PluginPrintercountersRecord($itemtype);
         list($messages, $error) = $record->initRecord($itemtype, 0, $sonprocess_id, $sonprocess_nbr);
         break;
   }

   echo implode("\n", $messages);

} else {
   echo __('Plugin disabled or automatic record disabled', 'printercounters');
   exit(1);
}

