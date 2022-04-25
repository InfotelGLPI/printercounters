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

define('PLUGIN_PRINTERCOUNTERS_VERSION', '2.0.0');

if (!defined("PLUGIN_PRINTERCOUNTERS_DIR")) {
   define("PLUGIN_PRINTERCOUNTERS_DIR", Plugin::getPhpDir("printercounters"));
   define("PLUGIN_PRINTERCOUNTERS_NOTFULL_DIR", Plugin::getPhpDir("printercounters",false));
   define("PLUGIN_PRINTERCOUNTERS_WEBDIR", Plugin::getWebDir("printercounters"));
   define("PLUGIN_PRINTERCOUNTERS_NOTFULL_WEBDIR", Plugin::getWebDir("printercounters",false));
}

// Init the hooks of the plugins -Needed
function plugin_init_printercounters() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['printercounters'] = true;
   $PLUGIN_HOOKS['change_profile']['printercounters'] = ['PluginPrintercountersProfile', 'changeProfile'];

   if (isset($_SESSION['glpiactiveprofile']['interface'])
       && $_SESSION['glpiactiveprofile']['interface'] == 'central') {
      $PLUGIN_HOOKS['add_css']['printercounters']          = ['printercounters.css'];
      $PLUGIN_HOOKS['add_javascript']['printercounters'][] = 'printercounters.js';
      $PLUGIN_HOOKS['javascript']['printercounters'][]     = PLUGIN_PRINTERCOUNTERS_NOTFULL_DIR.'/printercounters.js';
   }
   if (Session::getLoginUserID()) {
      if (class_exists('PluginPrintercountersItem_Recordmodel')) {
         foreach (PluginPrintercountersItem_Recordmodel::$types as $item) {
            if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], strtolower($item)) !== false) {
               $PLUGIN_HOOKS['add_javascript']['printercounters'][] = 'printercounters_load_scripts.js.php';
            }
         }
      }

      // Add tabs
      Plugin::registerClass('PluginPrintercountersProfile', ['addtabon' => 'Profile']);
      Plugin::registerClass('PluginPrintercountersCountertype_Recordmodel', ['addtabon' => 'PluginPrintercountersRecordmodel']);
      Plugin::registerClass('PluginPrintercountersItem_Recordmodel', ['addtabon' => 'PluginPrintercountersRecordmodel']);
      Plugin::registerClass('PluginPrintercountersSysdescr', ['addtabon' => 'PluginPrintercountersRecordmodel']);
      Plugin::registerClass('PluginPrintercountersPagecost', ['addtabon' => 'PluginPrintercountersBillingmodel']);
      Plugin::registerClass('PluginPrintercountersItem_Billingmodel', ['addtabon' => 'PluginPrintercountersBillingmodel']);
      Plugin::registerClass('PluginPrintercountersItem_Ticket', ['addtabon' => 'PluginPrintercountersConfig']);
      Plugin::registerClass('PluginPrintercountersProcess', ['addtabon' => 'PluginPrintercountersConfig']);
      Plugin::registerClass('PluginPrintercountersAdditional_data', ['notificationtemplates_types' => true]);

      if (Session::haveRight("plugin_printercounters", READ) && class_exists('PluginPrintercountersProfile')) {
         Plugin::registerClass('PluginPrintercountersItem_Recordmodel', ['addtabon' => 'Printer']);
         Plugin::registerClass('PluginPrintercountersItem_Billingmodel', ['addtabon' => 'Printer']);

         $PLUGIN_HOOKS['use_massive_action']['printercounters'] = 1;

         // Injection
         $PLUGIN_HOOKS['plugin_datainjection_populate']['printercounters'] = 'plugin_datainjection_populate_printercounters';

         $PLUGIN_HOOKS['menu_toadd']['printercounters']          = ['tools' => 'PluginPrintercountersMenu'];
//         $PLUGIN_HOOKS['helpdesk_menu_entry']['printercounters'] = true;
         if (Session::haveRight("plugin_printercounters", UPDATE)) {
            $PLUGIN_HOOKS['config_page']['printercounters'] = 'front/config.form.php';
         }
      }

      $PLUGIN_HOOKS['post_init']['printercounters'] = 'plugin_printercounters_postinit';

      // Pre item purge
      $PLUGIN_HOOKS['pre_item_purge']['printercounters'] = [
         'PluginPrintercountersRecordmodel'             => 'plugin_pre_item_purge_printercounters',
         'PluginPrintercountersBillingmodel'            => 'plugin_pre_item_purge_printercounters',
         'PluginPrintercountersCountertype'             => 'plugin_pre_item_purge_printercounters',
         'PluginPrintercountersItem_Recordmodel'        => 'plugin_pre_item_purge_printercounters',
         'PluginPrintercountersRecord'                  => 'plugin_pre_item_purge_printercounters',
         'PluginPrintercountersCountertype_Recordmodel' => 'plugin_pre_item_purge_printercounters',
         'Printer'                                      => 'plugin_pre_item_purge_printercounters',
         'Ticket'                                       => 'plugin_pre_item_purge_printercounters',
         'Entity'                                       => 'plugin_pre_item_purge_printercounters'];

      // Post item purge
      $PLUGIN_HOOKS['item_purge']['printercounters'] = [
         'PluginPrintercountersCounter' => 'plugin_item_purge_printercounters'];

      // Pre item delete
      $PLUGIN_HOOKS['pre_item_delete']['printercounters'] = [
         'Printer' => 'plugin_item_delete_printercounters'];

      // Item transfer
      $PLUGIN_HOOKS['item_transfer']['printercounters'] = 'plugin_item_transfer_printercounters';
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_printercounters() {
   return [
      'name'         => __('Printer counters', 'printercounters'),
      'version'      => PLUGIN_PRINTERCOUNTERS_VERSION,
      'author'       => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
      'license'      => 'GPLv2+',
      'homepage'     => 'https://github.com/InfotelGLPI/printercounters',
      'requirements' => [
         'glpi' => [
            'min' => '10.0',
            'max' => '11.0',
            'dev' => false
         ],
         'php' => [
            'exts' => ['snmp'],
         ]
      ]
   ];
}

