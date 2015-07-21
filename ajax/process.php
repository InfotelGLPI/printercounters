<?php
/*
 -------------------------------------------------------------------------
 Printercounters plugin for GLPI
 Copyright (C) 2014 by the Printercounters Development Team.
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
 --------------------------------------------------------------------------  */

define('GLPI_ROOT', '../../..');

include (GLPI_ROOT."/inc/includes.php");

Session::checkLoginUser();
//Html::header_nocache();

$process = new PluginPrintercountersProcess();

switch($_POST['action']){
   case 'killProcess':
      header('Content-Type: application/json; charset=UTF-8"');
      list($messages, $error) = $process->killProcesses($_POST['items_id'], PluginPrintercountersProcess::SIGKILL);
      echo json_encode(array('message'    => $messages, 
                             'error'      => $error));
      break;
   
   case 'getProcesses':
      header("Content-Type: text/html; charset=UTF-8");
      $process->getProcesses();
      break;
}

?>