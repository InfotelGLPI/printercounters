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
 * Class PluginPrintercountersProfile
 * 
 * This class manages the profile rights of the plugin
 * 
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersProfile extends CommonDBTM {
   
   static function getTypeName($nb=0) {
      return __('Rights management', 'printercounters');
   }
   
   static function canCreate() {
      return Session::haveRight('profile', 'w');
   }

   static function canView() {
      return Session::haveRight('profile', 'r');
   }
   
  /**
   * Get tab name for item
   * 
   * @param CommonGLPI $item
   * @param type $withtemplate
   * @return string
   */
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Profile') {
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
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='Profile') {
         $ID = $item->getField('id');
         $prof = new self();

         if (!$prof->getFromDBByProfile($item->getField('id'))) {
            $prof->createAccess($item->getField('id'));
         }
         $prof->showForm($item->getField('id'), array('target' => $CFG_GLPI["root_doc"].
                                                      "/plugins/printercounters/front/profile.form.php"));
      }
      return true;
   }

   
  /**
   * Purge plugin right if profile is deleted
   * 
   * @param Profile $prof
   */
   static function purgeProfiles(Profile $prof) {
      $plugprof = new self();
      $plugprof->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
   }
   
  /**
   * Get profile rights of the plugin
   * 
   * @global type $DB
   * @param type $profiles_id
   * @return boolean
   */
   function getFromDBByProfile($profiles_id) {
      global $DB;

      $query = "SELECT * FROM `".$this->getTable()."`
                WHERE `profiles_id` = '" . $profiles_id . "' ";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }
  
  /**
   * Create first acces for the current profile
   * 
   * @param type $ID
   */
   static function createFirstAccess($ID) {
      
      $myProf = new self();
      if (!$myProf->getFromDBByProfile($ID)) {

         $myProf->add(array(
            'profiles_id'        => $ID,
            'printercounters'    => 'w', 
            'update_records'     => '1',
            'add_lower_records'  => '1',
            'snmpset'            => '1'));
      }
   }

  /**
   * Create access for a profile
   * 
   * @param type $ID
   */
   function createAccess($ID) {

      $this->add(array(
      'profiles_id'       => $ID,
      'printercounters'   => 'w', 
      'update_records'    => '1',
      'add_lower_records' => '1',
      'snmpset'           => '1'));
   }
   
  /**
   * Change profile
   */
   static function changeProfile() {
      
      $prof = new self();
      if ($prof->getFromDBByProfile($_SESSION['glpiactiveprofile']['id'])) {
         $_SESSION["glpi_plugin_printercounters_profile"]=$prof->fields;

      } else {
         unset($_SESSION["glpi_plugin_printercounters_profile"]);
      }
   }

  /**
   * show profile form
   * 
   * @param type $ID
   * @param type $options
   * @return boolean
   */
   function showForm ($ID, $options=array()) {

      if (!Session::haveRight("profile","r")) return false;

      $prof = new Profile();
      if ($ID) {
         $this->getFromDBByProfile($ID);
         $prof->getFromDB($ID);
      }
      $options['colspan'] = 4;
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_2'>";

      echo "<th colspan='6' class='center b'>".sprintf(__('%1$s - %2$s'), __('Printer counters', 'printercounters'),
         $prof->fields["name"])."</th>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_2'>";
      echo "<td>".PluginPrintercountersPrinter::getTypeName(2)."</td><td>";
      Profile::dropdownNoneReadWrite("printercounters",$this->fields["printercounters"],1,1,1);
      echo "</td>";
      echo "<td>".__('Right to update records', 'printercounters')."</td><td>";
      Dropdown::showYesNo("update_records",$this->fields["update_records"]);
      echo "</td>";
      echo "<td>".__('Right to add lower records', 'printercounters')."</td><td>";
      Dropdown::showYesNo("add_lower_records",$this->fields["add_lower_records"]);
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_2'>";
      echo "<td>".__('Right to update printers values', 'printercounters')."</td><td>";
      Dropdown::showYesNo("snmpset",$this->fields["snmpset"]);
      echo "</td>";
      echo "<td></td><td></td>";
      echo "<td></td><td></td>";
      echo "</tr>";

      echo "<input type='hidden' name='id' value=".$this->fields["id"].">";
      
      $options['candel'] = false;
      $this->showFormButtons($options);

   }
}

?>