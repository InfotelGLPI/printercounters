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

include ("../../../inc/includes.php");

Session::checkLoginUser();
//Html::header_nocache();

$_POST['itemtype'] = isset($_POST['itemtype']) ? $_POST['itemtype'] : 'printer';
$_POST['items_id'] = isset($_POST['items_id']) ? $_POST['items_id'] : 0;
$additional_data = new PluginPrintercountersAdditional_data($_POST['itemtype'], $_POST['items_id']);

if (!isset($_POST['action'])) {
   $_POST['action'] = $_GET['action'];
}

switch ($_POST['action']) {
   case 'showAdditionalData':
      header("Content-Type: text/html; charset=UTF-8");
      $additional_data->showAdditionalData();
      break;
}

