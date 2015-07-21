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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginPrintercountersMenu
 * 
 * This class shows the plugin main page
 * 
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersMenu extends CommonDBTM {

   static function getTypeName($nb=0) {
      return __('Printercounters menu', 'printercounters');
   }
   
   // Printercounter's authorized profiles have right
   static function canCreate() {
      return plugin_printercounters_haveRight('printercounters', 'w');
   }
   
   // Printercounter's authorized profiles have right
   static function canView() {
      return plugin_printercounters_haveRight('printercounters', 'r');
   }

   /**
    * Show config menu
    */
   function showMenu() {
      global $CFG_GLPI;
      
      if(!$this->canView()) return false;
      
      echo "<div align='center'>";
      echo "<table class='tab_cadre' cellpadding='5' height='150'>";
      echo "<tr>";
      echo "<th colspan='5'>".__('Counters followup', 'printercounters')."</th>";
      echo "</tr>";
      echo "<tr class='tab_bg_1' style='background-color:white;'>";

      // Record models
      echo "<td class='center printercounters_menu_item'>";
      echo "<a  class='printercounters_menu_a' href=\"./recordmodel.php\">";
      echo "<img class='printercounters_menu_img' src='".$CFG_GLPI["root_doc"]."/plugins/printercounters/pics/recordmodel.png' alt=\"".PluginPrintercountersRecordmodel::getTypeName(2)."\">";
      echo "<br>".PluginPrintercountersRecordmodel::getTypeName(2)."</a>";
      echo "</td>";
      
      // Counter types
      echo "<td class='center printercounters_menu_item'>";
      echo "<a  class='printercounters_menu_a' href=\"./countertype.php\">";
      echo "<img class='printercounters_menu_img' src='".$CFG_GLPI["root_doc"]."/plugins/printercounters/pics/countertype.png' alt=\"".PluginPrintercountersCountertype::getTypeName(2)."\">";
      echo "<br>".PluginPrintercountersCountertype::getTypeName(2)."</a>";
      echo "</td>";

      // Record planification
      echo "<td class='center printercounters_menu_item'>";
      echo "<a  class='printercounters_menu_a' href=\"./record.form.php\">";
      echo "<img class='printercounters_menu_img' src='".$CFG_GLPI["root_doc"]."/plugins/printercounters/pics/planification.png' alt=\"".__('Record planning', 'printercounters')."\">";
      echo "<br>".__('Record planning', 'printercounters')."</a>";
      echo "</td>";
      
      // Configure SNMP authentication
      echo "<td class='center printercounters_menu_item'>";
      echo "<a  class='printercounters_menu_a' href=\"./snmpauthentication.php\">";
      echo "<img class='printercounters_menu_img' src='".$CFG_GLPI["root_doc"]."/plugins/printercounters/pics/snmpauthentication.png' alt=\"".PluginPrintercountersSnmpauthentication::getTypeName(2)."\">";
      echo "<br>".PluginPrintercountersSnmpauthentication::getTypeName(2)."</a>";
      echo "</td>";

       // Plugin management
      echo "<td class='center printercounters_menu_item'>";
      echo "<a  class='printercounters_menu_a' href=\"./config.form.php\">";
      echo "<img class='printercounters_menu_img' src='".$CFG_GLPI["root_doc"]."/plugins/printercounters/pics/process.png' alt=\"".PluginPrintercountersConfig::getTypeName()."\">";
      echo "<br>".PluginPrintercountersConfig::getTypeName()."</a>";
      echo "</td>";
      echo "</tr>";
      
      echo "<tr>";
      echo "<th colspan='5'>".__('Budget followup', 'printercounters')."</th>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1' style='background-color:white;'>";
      // Billing
      echo "<td class='center printercounters_menu_item'>";
      echo "<a  class='printercounters_menu_a' href=\"./billingmodel.php\">";
      echo "<img class='printercounters_menu_img' src='".$CFG_GLPI["root_doc"]."/plugins/printercounters/pics/billingmodel.png' alt=\"".PluginPrintercountersBillingmodel::getTypeName(2)."\">";
      echo "<br>".PluginPrintercountersBillingmodel::getTypeName(2)."</a>";
      echo "</td>";
      
      // Budget
      echo "<td class='center printercounters_menu_item'>";
      echo "<a  class='printercounters_menu_a' href=\"./budget.php\">";
      echo "<img class='printercounters_menu_img' src='".$CFG_GLPI["root_doc"]."/plugins/printercounters/pics/budget.png' alt=\"".PluginPrintercountersBudget::getTypeName(2)."\">";
      echo "<br>".PluginPrintercountersBudget::getTypeName(2)."</a>";
      echo "</td>";
      echo "</tr>";
      
      echo "</table></div>";
   }
}
?>