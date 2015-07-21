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

$plugin = new Plugin();

if ($plugin->isActivated("printercounters")) {

   $config = new PluginPrintercountersConfig();
   if (!$config->canCreate()) {
      Html::displayRightError();
   }

   if (isset($_POST['items_status']) && !empty($_POST['items_status'])) {
      $_POST['items_status'] = json_encode($_POST['items_status']);
   } else {
      $_POST['items_status'] = "";
   }
   
   if (isset($_POST["update_config"])) {
      if(!countElementsInTable("glpi_plugin_printercounters_configs", "`id` = 1")){
         $config->add($_POST);
      } else {
         $_POST['id'] = 1;
         $config->update($_POST);
      }
      Html::back();

   } else {
      Html::header(PluginPrintercountersConfig::getTypeName(), "", "plugins", "printercounters", "config");
      $config->show();
      Html::footer();
   }

} else {
   Html::header(PluginPrintercountersConfig::getTypeName(), "", "plugins", "printercounters", "config");
   echo "<div align='center'><br><br><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br>";
   echo "<b>Please activate the plugin</b></div>";
   Html::footer();
}

?>