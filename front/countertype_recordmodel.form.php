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

include ('../../../inc/includes.php');

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

$countertype_recordmodel = new PluginPrintercountersCountertype_Recordmodel();

if (isset($_POST["add"])) {
   // Check update rights for fields
   $countertype_recordmodel->check(-1, CREATE, $_POST);
   $newID = $countertype_recordmodel->add($_POST);

   Html::back();

} else if (isset($_POST["update"])) {
   // Check update rights for fields
   $countertype_recordmodel->check($_POST['id'], UPDATE, $_POST);
   $countertype_recordmodel->update($_POST);

   Html::back();

} else if (isset($_POST["purge"])) {
   // Check update rights for fields
   $countertype_recordmodel->check($_POST['id'], DELETE, $_POST);
   $countertype_recordmodel->delete($_POST, 1);
   Html::back();

} else {
   $countertype_recordmodel->checkGlobal(READ);
   Html::header(PluginPrintercountersCountertype::getTypeName(1), '', "tools", "pluginprintercountersmenu", "countertype");
   $countertype_recordmodel->display(['id' => $_GET["id"]]);
   Html::footer();
}
