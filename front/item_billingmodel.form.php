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

$item_billingmodel = new PluginPrintercountersItem_Billingmodel();

if (isset($_POST["add"])) {
   // Check update rights for fields
   $item_billingmodel->check(-1, CREATE, $_POST);
   $newID = $item_billingmodel->add($_POST);

      Html::back();

} else if (isset($_POST["update"]) || isset($_POST["update_config"])) {
   // Check update rights for fields
   $item_billingmodel->check($_POST['id'], UPDATE, $_POST);
   if ($item_billingmodel->update($_POST) && isset($_POST["update_config"])) {
      $item_billingmodel->addLog();
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   // Check update rights for fields
   $item_billingmodel->check($_POST['id'], DELETE, $_POST);
   $item_billingmodel->delete($_POST, 1);
   $item_billingmodel->redirectToList();
}

