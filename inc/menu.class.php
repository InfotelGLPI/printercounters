<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 printercounters plugin for GLPI
 Copyright (C) 2014-2016 by the printercounters Development Team.

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
   
   static $rightname = 'plugin_printercounters';

   static function getTypeName($nb=0) {
      return __('Printer counters', 'printercounters');
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
      echo "<a  class='printercounters_menu_a' href=\"./item_recordmodel.form.php\">";
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
   
   /**
    * Menu content for headers
    */
   static function getMenuContent() {
      $plugin_page = "/plugins/printercounters/front/menu.php";
      $menu = array();
      //Menu entry in helpdesk
      $menu['title']                          = self::getTypeName();
      $menu['page']                           = $plugin_page;
      $menu['links']['search']                = $plugin_page;
      
      if (Session::haveRight(static::$rightname, UPDATE)
            || Session::haveRight("config", UPDATE)) {
         //Entry icon in breadcrumb
         $menu['links']['config']                      = PluginPrintercountersConfig::getFormURL(false);
         //Link to config page in admin plugins list
         $menu['config_page']                          = PluginPrintercountersConfig::getFormURL(false);
      }

      // Recordmodel
      $menu['options']['recordmodel']['title']            = _n("Record model", "Record models", 2, 'printercounters');
      $menu['options']['recordmodel']['page']             = '/plugins/printercounters/front/recordmodel.php';
      $menu['options']['recordmodel']['links']['add']     = '/front/setup.templates.php?itemtype=PluginPrintercountersRecordmodel&add=1';
      $menu['options']['recordmodel']['links']['template']= '/front/setup.templates.php?itemtype=PluginPrintercountersRecordmodel&add=0';
      $menu['options']['recordmodel']['links']['search']  = '/plugins/printercounters/front/recordmodel.php';
      
      // Countertype
      $menu['options']['countertype']['title']            = _n("Counter type", "Counter types", 2, 'printercounters');
      $menu['options']['countertype']['page']             = '/plugins/printercounters/front/countertype.php';
      $menu['options']['countertype']['links']['add']     = '/plugins/printercounters/front/countertype.form.php';
      $menu['options']['countertype']['links']['search']  = '/plugins/printercounters/front/countertype.php';
         
      // Billingmodel
      $menu['options']['billingmodel']['title']           = _n("Billing model", "Billing models", 2, 'printercounters');
      $menu['options']['billingmodel']['page']            = '/plugins/printercounters/front/billingmodel.php';
      $menu['options']['billingmodel']['links']['add']    = '/plugins/printercounters/front/billingmodel.form.php';
      $menu['options']['billingmodel']['links']['search'] = '/plugins/printercounters/front/billingmodel.php';
         
      // Budget
      $menu['options']['budget']['title']                 = __("Budget");
      $menu['options']['budget']['page']                  = '/plugins/printercounters/front/budget.php';
      $menu['options']['budget']['links']['add']          = '/plugins/printercounters/front/budget.form.php';
      $menu['options']['budget']['links']['search']       = '/plugins/printercounters/front/budget.php';
         
      // Record planning
      $menu['options']['record']['title']                 = __("Record planning", 'printercounters');
      $menu['options']['record']['page']                  = '/plugins/printercounters/front/item_recordmodel.form.php';
         
      // Snmpauthentication
      $menu['options']['snmpauthentication']['title']           = _n("SNMP authentication", "SNMP authentications", 2, 'printercounters');
      $menu['options']['snmpauthentication']['page']            = '/plugins/printercounters/front/snmpauthentication.php';
      $menu['options']['snmpauthentication']['links']['add']    = '/plugins/printercounters/front/snmpauthentication.form.php';
      $menu['options']['snmpauthentication']['links']['search'] = '/plugins/printercounters/front/snmpauthentication.php';
      
      // Config
      $menu['options']['config']['title'] = __('Plugin management', 'printercounters');
      $menu['options']['config']['page']  = '/plugins/printercounters/front/config.form.php';

      return $menu;
   }
   
   /**
    * Get rights
    */
   function getRights($interface='central') {
      if ($interface == 'central') {
         $values = array(CREATE  => __('Create'),
                         READ    => __('Read'),
                         UPDATE  => __('Update'),
                         PURGE   => array('short' => __('Purge'),
                                          'long'  => _x('button', 'Delete permanently')));
      } else {
          $values = array(READ    => __('Read'));
      }

      return $values;
   }
   
}
?>