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

// Init the hooks of the plugins -Needed
function plugin_init_printercounters() {
   global $PLUGIN_HOOKS, $CFG_GLPI;
                                          
   $PLUGIN_HOOKS['csrf_compliant']['printercounters'] = true;
   $PLUGIN_HOOKS['change_profile']['printercounters'] = array('PluginPrintercountersProfile','changeProfile');
   
   $PLUGIN_HOOKS['add_css']['printercounters'] = array('printercounters.css');
   $PLUGIN_HOOKS['add_javascript']['printercounters'][] = 'printercounters.js';

   if (Session::getLoginUserID()) {
      if (class_exists('PluginPrintercountersItem_Recordmodel')) {
         foreach (PluginPrintercountersItem_Recordmodel::$types as $item) {
            if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], strtolower($item)) !== false) {
               $PLUGIN_HOOKS['add_javascript']['printercounters'][] = 'printercounters.js.php';
            }
         }
      }
      
      // Add tabs
      Plugin::registerClass('PluginPrintercountersProfile',                 array('addtabon' => 'Profile'));
      Plugin::registerClass('PluginPrintercountersCountertype_Recordmodel', array('addtabon' => 'PluginPrintercountersRecordmodel'));
      Plugin::registerClass('PluginPrintercountersItem_Recordmodel',        array('addtabon' => 'PluginPrintercountersRecordmodel'));
      Plugin::registerClass('PluginPrintercountersSysdescr',                array('addtabon' => 'PluginPrintercountersRecordmodel'));
      Plugin::registerClass('PluginPrintercountersPagecost',                array('addtabon' => 'PluginPrintercountersBillingmodel'));
      Plugin::registerClass('PluginPrintercountersItem_Billingmodel',       array('addtabon' => 'PluginPrintercountersBillingmodel'));
      Plugin::registerClass('PluginPrintercountersItem_Ticket',             array('addtabon' => 'PluginPrintercountersConfig'));
      Plugin::registerClass('PluginPrintercountersProcess',                 array('addtabon' => 'PluginPrintercountersConfig'));
      Plugin::registerClass('PluginPrintercountersAdditional_data',         array('notificationtemplates_types' => true));
                                  
      if (plugin_printercounters_haveRight("printercounters","r") && class_exists('PluginPrintercountersProfile')) {
         Plugin::registerClass('PluginPrintercountersItem_Recordmodel', array('addtabon' => 'Printer'));
         Plugin::registerClass('PluginPrintercountersItem_Billingmodel', array('addtabon' => 'Printer'));
               
         $PLUGIN_HOOKS['use_massive_action']['printercounters'] = 1;
         
         // Injection
         $PLUGIN_HOOKS['plugin_datainjection_populate']['printercounters'] = 'plugin_datainjection_populate_printercounters';
         
         // Menu
         $PLUGIN_HOOKS['helpdesk_menu_entry']['printercounters'] = '/front/menu.php';
         $PLUGIN_HOOKS['menu_entry']['printercounters']          = 'front/menu.php';
         
         // Recordmodel
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['recordmodel']['title']            = _n("Record model", "Record models", 2, 'printercounters');
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['recordmodel']['page']             = '/plugins/printercounters/front/recordmodel.php';
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['recordmodel']['links']['add']     = '/front/setup.templates.php?itemtype=PluginPrintercountersRecordmodel&add=1';
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['recordmodel']['links']['template']= '/front/setup.templates.php?itemtype=PluginPrintercountersRecordmodel&add=0';
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['recordmodel']['links']['search']  = '/plugins/printercounters/front/recordmodel.php';
         
         // Countertype
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['countertype']['title']            = _n("Counter type", "Counter types", 2, 'printercounters');
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['countertype']['page']             = '/plugins/printercounters/front/countertype.php';
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['countertype']['links']['add']     = '/plugins/printercounters/front/countertype.form.php';
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['countertype']['links']['search']  = '/plugins/printercounters/front/countertype.php';
         
         // Billingmodel
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['billingmodel']['title']           = _n("Billing model", "Billing models", 2, 'printercounters');
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['billingmodel']['page']            = '/plugins/printercounters/front/billingmodel.php';
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['billingmodel']['links']['add']    = '/plugins/printercounters/front/billingmodel.form.php';
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['billingmodel']['links']['search'] = '/plugins/printercounters/front/billingmodel.php';
         
         // Budget
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['budget']['title']                 = __("Budget");
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['budget']['page']                  = '/plugins/printercounters/front/budget.php';
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['budget']['links']['add']          = '/plugins/printercounters/front/budget.form.php';
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['budget']['links']['search']       = '/plugins/printercounters/front/budget.php';
         
         // Record planning
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['record']['title']                 = __("Record planning", 'printercounters');
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['record']['page']                  = '/plugins/printercounters/front/record.form.php';
         
         // Snmpauthentication
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['snmpauthentication']['title']           = _n("SNMP authentication", "SNMP authentications", 2, 'printercounters');
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['snmpauthentication']['page']            = '/plugins/printercounters/front/snmpauthentication.php';
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['snmpauthentication']['links']['add']    = '/plugins/printercounters/front/snmpauthentication.form.php';
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['snmpauthentication']['links']['search'] = '/plugins/printercounters/front/snmpauthentication.php';
         
         // Config
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['config']['title'] = __('Plugin management', 'printercounters');
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['options']['config']['page']  = '/plugins/printercounters/front/config.form.php';
         $PLUGIN_HOOKS['submenu_entry']['printercounters']['config'] = 'front/config.form.php';
         $PLUGIN_HOOKS['config_page']['printercounters']             = 'front/config.form.php';
      }

      $PLUGIN_HOOKS['post_init']['printercounters'] = 'plugin_printercounters_postinit';

      // Pre item purge
      $PLUGIN_HOOKS['pre_item_purge']['printercounters']  = array(
         'Profile'                                      => array('PluginPrintercountersProfile', 'purgeProfiles'),
         'PluginPrintercountersRecordmodel'             => 'plugin_pre_item_purge_printercounters',
         'PluginPrintercountersBillingmodel'            => 'plugin_pre_item_purge_printercounters',
         'PluginPrintercountersCountertype'             => 'plugin_pre_item_purge_printercounters',
         'PluginPrintercountersItem_Recordmodel'        => 'plugin_pre_item_purge_printercounters',
         'PluginPrintercountersRecord'                  => 'plugin_pre_item_purge_printercounters',
         'PluginPrintercountersCountertype_Recordmodel' => 'plugin_pre_item_purge_printercounters',
         'Printer'                                      => 'plugin_pre_item_purge_printercounters',
         'Ticket'                                       => 'plugin_pre_item_purge_printercounters',
         'Entity'                                       => 'plugin_pre_item_purge_printercounters');
      
      // Post item purge
      $PLUGIN_HOOKS['item_purge']['printercounters']  = array(
         'PluginPrintercountersCounter' => 'plugin_item_purge_printercounters');
      
      // Pre item delete
      $PLUGIN_HOOKS['pre_item_delete']['printercounters']  = array(
         'Printer' => 'plugin_item_delete_printercounters');
      
      // Item transfer
      $PLUGIN_HOOKS['item_transfer']['printercounters']  = 'plugin_item_transfer_printercounters';
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_printercounters() {
   return array (
      'name'           => __('Printer counters', 'printercounters'),
      'version'        => '1.0.6',
      'author'         => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
      'license'        => 'GPLv2+',
      'homepage'       => '',
      'minGlpiVersion' => '0.84');// For compatibility / no install in version < 0.83
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_printercounters_check_prerequisites() {

   if (version_compare(GLPI_VERSION, '0.84', 'lt') || version_compare(GLPI_VERSION, '0.85', 'ge')) {
      _e('This plugin requires GLPI >= 0.84', 'printercounters');
      return false;
   }

   if (version_compare(phpversion(), '5.4', 'lt')) {
      _e('This plugin requires PHP >= 5.4', 'printercounters');
      return false;
   }

   if (!extension_loaded('snmp')) {
      _e('This plugin requires SNMP php extension', 'printercounters');
      return false;
   }

   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_printercounters_check_config() {
   return true;
}

//general right
function plugin_printercounters_haveRight($module,$right) {
   $matches=array(
      ""  => array("","r","w"), // ne doit pas arriver normalement
      "r" => array("r","w"),
      "w" => array("w"),
      "1" => array("1"),
      "0" => array("0","1"), // ne doit pas arriver non plus
   );

   if (isset($_SESSION["glpi_plugin_printercounters_profile"][$module]) &&
           in_array($_SESSION["glpi_plugin_printercounters_profile"][$module], $matches[$right])) {
      
      return true;
   } else {
      return false;
   }
}

?>