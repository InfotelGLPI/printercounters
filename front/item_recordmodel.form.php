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

$item_recordmodel = new PluginPrintercountersItem_Recordmodel();

if (isset($_POST["add"])) {
   // Check update rights for fields
   $item_recordmodel->check(-1, 'w', $_POST);
   $item_recordmodel->add($_POST);

   Html::back();

} elseif (isset($_POST["update"]) || isset($_POST["update_config"])) {
   // Check update rights for fields
   $item_recordmodel->check($_POST['id'], 'w', $_POST);
   if($item_recordmodel->update($_POST) && isset($_POST["update_config"])){
      $item_recordmodel->addLog();
   }
   Html::back();
   
} elseif (isset($_POST["delete"])) {
   // Check update rights for fields
   $item_recordmodel->check($_POST['id'], 'w', $_POST);
   $item_recordmodel->delete($_POST, 1);
   $item_recordmodel->redirectToList();
   
}
?>