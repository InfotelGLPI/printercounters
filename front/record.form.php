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

$record = new PluginPrintercountersRecord();

if (isset($_POST["immediate_record"])) {
   $record->check(-1, UPDATE, $_POST);
   $record->immediateRecord($_POST);
   Html::back();
   
} elseif (isset($_POST["manual_record"])) {
   $record->check($_POST['id'], UPDATE, $_POST);
   $record->manualRecord($_POST);
   Html::back();
   
} elseif (isset($_POST["update_counter_position"])) {
   $record->check($_POST['id'], UPDATE, $_POST);
   $record->updateCounterPosition($_POST);
   Html::back();
   
} elseif(isset($_GET["initAjaxMassiveAction"])){
   Html::header(__('Printer'), '', "tools", "pluginprintercountersmenu");
   $item_recordmodel = new PluginPrintercountersItem_Recordmodel();
   $item_recordmodel->initMassiveActionsProcess();
   Html::footer();
}
   



?>