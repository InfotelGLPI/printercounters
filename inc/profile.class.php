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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginPrintercountersProfile
 *
 * This class manages the profile rights of the plugin
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersProfile extends Profile {

   static function getTypeName($nb = 0) {
      return __('Rights management', 'printercounters');
   }


   /**
   * Get tab name for item
   *
   * @param CommonGLPI $item
   * @param type $withtemplate
   * @return string
   */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType()=='Profile' && $item->getField('interface') != 'helpdesk') {
         return PluginPrintercountersPrinter::getTypeName(2);
      }
      return '';
   }

   /**
   * display tab content for item
   *
   * @global type $CFG_GLPI
   * @param CommonGLPI $item
   * @param type $tabnum
   * @param type $withtemplate
   * @return boolean
   */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      global $CFG_GLPI;

      if ($item->getType()=='Profile') {
         $ID = $item->getID();
         $prof = new self();

         self::addDefaultProfileInfos($ID,
                                 ['plugin_printercounters'                   => ALLSTANDARDRIGHT,
                                       'plugin_printercounters_update_records'    => 0,
                                       'plugin_printercounters_add_lower_records' => 0,
                                       'plugin_printercounters_snmpset'           => 0]);
         $prof->showForm($ID);
      }

      return true;
   }

   /**
   * show profile form
   *
   * @param type $ID
   * @param type $options
   * @return boolean
   */
   function showForm ($profiles_id = 0, $openform = true, $closeform = true) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);

      $rights = $this->getAllRights();
      $profile->displayRightsChoiceMatrix($rights, ['canedit'       => $canedit,
                                                         'default_class' => 'tab_bg_2',
                                                         'title'         => __('General')]);

      echo "<table class='tab_cadre_fixehov'>";
      $effective_rights = ProfileRight::getProfileRights($profiles_id, ['plugin_printercounters_update_records',
                                                                             'plugin_printercounters_add_lower_records',
                                                                             'plugin_printercounters_snmpset']);
      echo "<tr class='tab_bg_2'>";
      echo "<td width='20%'>".__('Right to update records', 'printercounters')."</td>";
      echo "<td colspan='5'>";
      Html::showCheckbox(['name'    => '_plugin_printercounters_update_records',
                               'checked' => $effective_rights['plugin_printercounters_update_records']]);
      echo "</td>";
      echo "<td width='20%'>".__('Right to add lower records', 'printercounters')."</td>";
      echo "<td colspan='5'>";
      Html::showCheckbox(['name'    => '_plugin_printercounters_add_lower_records',
                               'checked' => $effective_rights['plugin_printercounters_add_lower_records']]);
      echo "</td>";
      echo "</tr>\n";
      echo "<tr class='tab_bg_2'>";
      echo "<td width='20%'>".__('Right to update printers values', 'printercounters')."</td>";
      echo "<td colspan='5'>";
      Html::showCheckbox(['name'    => '_plugin_printercounters_snmpset',
                               'checked' => $effective_rights['plugin_printercounters_snmpset']]);
      echo "</td>";
      echo "<td colspan='6'></td>";
      echo "</tr>\n";
      echo "</table>";

      if ($canedit
          && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $profiles_id]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update', 'class' => 'btn btn-primary']);
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";

      $this->showLegend();
   }

   /**
   * Get all rights
   *
   * @param type $all
   * @return array
   */
   static function getAllRights($all = false) {

      $rights = [
          ['itemtype'  => 'PluginPrintercountersMenu',
                'label'     => __('Printer counters', 'printercounters'),
                'field'     => 'plugin_printercounters'
          ]
      ];

      if ($all) {
         $rights[] = ['itemtype'  => 'PluginPrintercountersRecord',
                           'label'     => __('Right to update records', 'printercounters'),
                           'field'     => 'plugin_printercounters_update_records'
                     ];
         $rights[] = ['itemtype'  => 'PluginPrintercountersRecord',
                           'label'     => __('Right to add lower records', 'printercounters'),
                           'field'     => 'plugin_printercounters_add_lower_records'
                     ];
         $rights[] = ['itemtype'  => 'PluginPrintercountersRecord',
                           'label'     => __('Right to update printers values', 'printercounters'),
                           'field'     => 'plugin_printercounters_snmpset'
                     ];
      }

      return $rights;
   }

   /**
    * Init profiles
    *
    **/

   static function translateARight($old_right) {
      switch ($old_right) {
         case '':
            return 0;
         case 'r' :
            return READ;
         case 'w':
            return ALLSTANDARDRIGHT;
         case '0':
         case '1':
            return $old_right;

         default :
            return 0;
      }
   }

   /**
   * @since 0.85
   * Migration rights from old system to the new one for one profile
   * @param $profiles_id the profile ID
   */
   static function migrateOneProfile() {
      global $DB;
      //Cannot launch migration if there's nothing to migrate...
      if (!$DB->tableExists('glpi_plugin_printercounters_profiles')) {
         return true;
      }
      $dbu   = new DbUtils();
      $datas = $dbu->getAllDataFromTable('glpi_plugin_printercounters_profiles');

      foreach ($datas as $profile_data) {
         $matching = ['printercounters'    => 'plugin_printercounters',
                           'update_records'     => 'plugin_printercounters_update_records',
                           'add_lower_records'  => 'plugin_printercounters_add_lower_records',
                           'snmpset'            => 'plugin_printercounters_snmpset'];
         // Search existing rights
         $used = [];
         $existingRights = $dbu->getAllDataFromTable('glpi_profilerights',
                                                     ["profiles_id" => $profile_data['profiles_id']]);
         foreach ($existingRights as $right) {
            $used[$right['profiles_id']][$right['name']] = $right['rights'];
         }

         // Add or update rights
         foreach ($matching as $old => $new) {
            if (isset($used[$profile_data['profiles_id']][$new])) {
               $query = "UPDATE `glpi_profilerights` 
                         SET `rights`='".self::translateARight($profile_data[$old])."' 
                         WHERE `name`='$new' AND `profiles_id`='".$profile_data['profiles_id']."'";
               $DB->query($query);
            } else {
               $query = "INSERT INTO `glpi_profilerights` (`profiles_id`, `name`, `rights`) VALUES ('".$profile_data['profiles_id']."', '$new', '".self::translateARight($profile_data[$old])."');";
               $DB->query($query);
            }
         }
      }
   }


   /**
   * Initialize profiles, and migrate it necessary
   */
   static function initProfile() {
      global $DB;
      $profile = new self();
      $dbu     = new DbUtils();

      //Add new rights in glpi_profilerights table
      foreach ($profile->getAllRights(true) as $data) {
         if ($dbu->countElementsInTable("glpi_profilerights",
                                  ["name" => $data['field']]) == 0) {
            ProfileRight::addProfileRights([$data['field']]);
         }
      }

      // Migration old rights in new ones
      self::migrateOneProfile();

      foreach ($DB->request("SELECT *
                           FROM `glpi_profilerights` 
                           WHERE `profiles_id`='".$_SESSION['glpiactiveprofile']['id']."' 
                              AND `name` LIKE '%plugin_printercounters%'") as $prof) {
         $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
      }
   }

   /**
   * Initialize profiles, and migrate it necessary
   */
   static function changeProfile() {
      global $DB;

      foreach ($DB->request("SELECT *
                           FROM `glpi_profilerights` 
                           WHERE `profiles_id`='".$_SESSION['glpiactiveprofile']['id']."' 
                              AND `name` LIKE '%plugin_printercounters%'") as $prof) {
         $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
      }

   }

   static function createFirstAccess($profiles_id) {

      $rights = ['plugin_printercounters'                    => ALLSTANDARDRIGHT,
                      'plugin_printercounters_update_records'     => 1,
                      'plugin_printercounters_add_lower_records'  => 1,
                      'plugin_printercounters_snmpset'            => 1];

      self::addDefaultProfileInfos($profiles_id,
                                   $rights, true);

   }

   /**
    * @param $profile
   **/
   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {

      $dbu          = new DbUtils();
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if ($dbu->countElementsInTable('glpi_profilerights',
                                   ["profiles_id" => $profiles_id, "name" => $right]) && $drop_existing) {
            $profileRight->deleteByCriteria(['profiles_id' => $profiles_id, 'name' => $right]);
         }
         if (!$dbu->countElementsInTable('glpi_profilerights',
                                   ["profiles_id" => $profiles_id, "name" => $right])) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

}

