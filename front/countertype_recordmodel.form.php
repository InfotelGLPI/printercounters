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

include ('../../../inc/includes.php');

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

$countertype_recordmodel = new PluginPrintercountersCountertype_Recordmodel();

if (isset($_POST["add"])) {
   // Check update rights for fields
   $countertype_recordmodel->check(-1, 'w', $_POST);
   $countertype_recordmodel->add($_POST);
   Html::back();

} elseif (isset($_POST["update"])) {
   // Check update rights for fields
   $countertype_recordmodel->check($_POST['id'], 'w', $_POST);
   $countertype_recordmodel->update($_POST);

   Html::back();

} elseif (isset($_POST["delete"])) {
   // Check update rights for fields
   $countertype_recordmodel->check($_POST['id'], 'w', $_POST);
   $countertype_recordmodel->delete($_POST, 1);
   Html::back();
   
} else {
   $countertype_recordmodel->checkGlobal("r");
   Html::header(PluginPrintercountersCountertype::getTypeName(1), '', "plugins", "printercounters");
   $countertype_recordmodel->showForm($_GET["id"]);
   Html::footer();
}
?>