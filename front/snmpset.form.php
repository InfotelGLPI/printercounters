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

$snmpset = new PluginPrintercountersSnmpset();

$_POST['set_location'] = isset($_POST['set_location']) ? $_POST['set_location'] : 0;
$_POST['set_name']     = isset($_POST['set_name'])     ? $_POST['set_name']     : 0;
$_POST['set_contact']  = isset($_POST['set_contact'])  ? $_POST['set_contact']  : 0;

if (isset($_POST["add"])) {
   // Check add rights for fields
   $snmpset->check(-1, 'w', $_POST);
   $snmpset->add($_POST);

   Html::back();

} elseif (isset($_POST["update"])) {
   // Check update rights for fields
   $snmpset->check($_POST['id'], 'w', $_POST);
   $snmpset->update($_POST);

   Html::back();

} elseif (isset($_POST["delete"])) {
   // Check delete rights for fields
   $snmpset->check($_POST['id'], 'w', $_POST);
   $snmpset->delete($_POST, 1);
   
   Html::back();
   
} else {
   $snmpset->checkGlobal("r");
   Html::header(PluginPrintercountersItem_Recordmodel::getTypeName(1), '', "plugins", "printercounters");
   $snmpset->showForm($_GET["id"]);
   Html::footer();
}
?>
