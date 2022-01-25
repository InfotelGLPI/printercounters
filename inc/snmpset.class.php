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
 * Class PluginPrintercountersSnmpset
 *
 * This class allows to add and manage the snmpsets used for the conforimty check of the items
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersSnmpset extends CommonDBTM {

   static $rightname = 'plugin_printercounters';

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   static function getTypeName($nb = 0) {
      return __('Snmpset', 'Snmpsets', $nb, 'printercounters');
   }

   // Printercounter's authorized profiles have right
   static function canSnmpSet() {
      return Session::haveRight('plugin_printercounters_snmpset', 1);
   }


   /**
    * Display tab for item
    *
    * @param CommonGLPI $item
    * @param int $withtemplate
    * @return array|string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate) {
         switch ($item->getType()) {
            case 'PluginPrintercountersConfig' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  $dbu = new DbUtils();
                  return self::createTabEntry(self::getTypeName(),
                                              $dbu->countElementsInTable($this->getTable(),
                                                                         ["plugin_printercounters_configs_id" => $item->getID()]));
               }
               return self::getTypeName();
               break;
         }
      }
      return '';
   }

   /**
    * Display content for each users
    *
    * @static
    * @param CommonGLPI $item
    * @param int $tabnum
    * @param int $withtemplate
    * @return bool|true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $snmpset = new self();

      switch ($item->getType()) {
         case 'PluginPrintercountersConfig' :
            $snmpset->showForRecordmodel($item);
            break;
      }
      return true;
   }

   /**
    * Function show item
    *
    * @param $ID        integer  ID of the item
    * @param $options   array    options used
    */
   function showForm($ID, $options = []) {

      if ($ID > 0) {
         $script = "$('#printercounters_viewAddSnmpset').show();";
      } else {
         $script = "$('#printercounters_viewAddSnmpset').hide();";
         $options['plugin_printercounters_configs_id'] = $options['parent']->getField('id');
      }

      $this->initForm($ID, $options);

      echo html::scriptBlock($script);

      $this->showFormHeader($options);

      // Tags available
      echo "<tr class='tab_bg_1'>";
      echo "<td rowspan='4'>";
      echo "<table class='tab_cadre'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2'>".__('List of available tags')."</th>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<th>".__('Label')."</th>";
      echo "<th>".__('Tag')."</th>";
      echo "</tr>";
      foreach ($this->getTags() as $title => $tag) {
         echo "<tr class='tab_bg_1'><td>".$title."</td><td>$tag</td></tr>";
      }
      echo "</table>";
      echo "</td>";
      echo "</tr>";

      // Name
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Set printer name', 'printercounters');
      echo "</td>";
      echo "<td colspan='2'>";
      $value = $this->getField('set_name');
      $checked = (!empty($value) || $ID <= 0) ? "checked" : "";
      echo "<input type='checkbox' $checked value='1' name='set_name' value='1'/>";
      echo "</td>";
      echo "</tr>";

      // Location
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Set printer location', 'printercounters');
      echo "</td>";
      echo "<td colspan='2'>";
      $value = $this->getField('set_location');
      $checked = (!empty($value) || $ID <= 0) ? "checked" : "";
      echo "<input type='checkbox' $checked value='1' name='set_location' value='1'/>";
      echo "</td>";
      echo "</tr>";

      // Contact
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Set printer contact informations', 'printercounters');
      echo "</td>";
      echo "<td>";
      $value = $this->getField('set_contact');
      $checked = (!empty($value) || $ID <= 0) ? "checked" : "";
      echo "<input type='checkbox' $checked value='1' name='set_contact' value='1'/>";
      echo "</td>";
      echo "<td>";

      $content = "";
      $value = $this->getField('contact');
      if (empty($value) && $ID <= 0) {
         foreach ($this->getTags() as $title => $tag) {
            $content .= $tag."\n";
         }
      } else {
         $content = $value;
      }
      Html::textarea(['name'            => 'contact',
                      'value'       => $content,
                      'cols'       => 35,
                      'rows'       => 7,
                      'enable_richtext' => false]);
      echo "</td>";
      echo "</tr>";

      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }

   /**
    * Function show for record model
    *
    * @param type $item
    * @return boolean
    */
   function showSnmpSet($config) {

      if (!$this->canView()) {
         return false;
      }
      if (!$canedit = $this->canCreate()) {
         return false;
      }

      $rand = mt_rand();

      if (isset($_POST["start"])) {
         $start = $_POST["start"];
      } else {
         $start = 0;
      }
      $dbu = new DbUtils();
      $data = $this->getItems($start,
                              $dbu->getEntitiesRestrictRequest("AND", $this->getTable(), "", $_SESSION['glpiactiveentities'], true));

      if ($canedit) {
         echo "<div id='viewsnmpset".$config['configs_id']."_$rand'></div>\n";
         PluginPrintercountersAjax::getJSEdition("viewsnmpset".$config['configs_id']."_$rand",
                                                 "viewAddSnmpset".$config['configs_id']."_$rand",
                                                 $this->getType(),
                                                 -1,
                                                 'PluginPrintercountersConfig',
                                                 $config['configs_id']);
         echo "<div class='center firstbloc'>".
               "<a class='submit btn btn-primary' id='printercounters_viewAddSnmpset' href='javascript:viewAddSnmpset".$config['configs_id']."_$rand();'>";
         echo __('Add a new snmpset', 'printercounters')."</a></div>\n";
         echo "<script type='text/javascript'>viewAddSnmpset".$config['configs_id']."_$rand();</script>";
      }

      if (!empty($data)) {
         $this->listItems($config['configs_id'], $data, $canedit, $rand);
      }
   }

   /**
    * Function list items
    *
    * @global type $CFG_GLPI
    * @param type $ID
    * @param type $data
    * @param type $canedit
    * @param type $rand
    */
   private function listItems($ID, $data, $canedit, $rand) {
      global $CFG_GLPI;

      echo "<div class='left'>";
      if ($canedit) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass'.__CLASS__.$rand];
         Html::showMassiveActions($massiveactionparams);
      }

      //      Html::printAjaxPager(self::getTypeName(2), $start, countElementsInTable($this->getTable()));
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th width='10'>";
      if ($canedit) {
         echo Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
      }
      echo "</th>";
      echo "<th>".__('Entity')."</th>";
      echo "<th>".__('Set printer name', 'printercounters')."</th>";
      echo "<th>".__('Set printer location', 'printercounters')."</th>";
      echo "<th>".__('Set printer contact informations', 'printercounters')."</th>";
      echo "<th>".__('Tag')."</th>";
      echo "</tr>";

      foreach ($data as $field) {
         $onclick = ($canedit
                      ? "style='cursor:pointer' onClick=\"viewEditSnmpset".$ID."_".
                        $field['id']."_$rand();\"": '');

         echo "<tr class='tab_bg_2'>";
         echo "<td width='10'>";
         if ($canedit) {
            Html::showMassiveActionCheckBox(__CLASS__, $field['id']);
            PluginPrintercountersAjax::getJSEdition("viewsnmpset".$ID."_$rand",
                                                    "viewEditSnmpset".$ID."_".$field["id"]."_$rand",
                                                    $this->getType(),
                                                    $field["id"],
                                                    'PluginPrintercountersConfig',
                                                    $ID);
         }
         echo "</td>";
         // Snmpset
         echo "<td $onclick>".Dropdown::getDropdownName('glpi_entities', $field['entities_id'])."</td>";
         echo "<td $onclick>".Dropdown::getYesNo($field['set_name'])."</td>";
         echo "<td $onclick>".Dropdown::getYesNo($field['set_location'])."</td>";
         echo "<td $onclick>".Dropdown::getYesNo($field['set_contact'])."</td>";
         echo "<td $onclick>".nl2br($field['contact'])."</td>";
         echo "</tr>";
      }
      echo "</table>";
      if ($canedit) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }

      echo "</div>";
   }

   /**
    * Function get items for record models
    *
    * @global type $DB
    * @param type $start
    * @return type
    */
   function getItems($start = 0, $condition = null) {
      global $DB;

      $output = [];

      $query = "SELECT `".$this->getTable()."`.`id`, 
                       `".$this->getTable()."`.`set_name`,
                       `".$this->getTable()."`.`set_location`,
                       `".$this->getTable()."`.`set_contact`,
                       `".$this->getTable()."`.`contact`,
                       `".$this->getTable()."`.`entities_id`
                FROM ".$this->getTable()."
                WHERE 1 $condition";
      //          LIMIT ".intval($start).",".intval($_SESSION['glpilist_limit']);

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $output[$data['id']] = $data;
         }
      }

      return $output;
   }

   /**
    * Function set values on printer
    *
    * @param type $items_id
    * @param type $itemtype
    * @return type
    */
   function snmpSet($items_id, $itemtype) {

      $messages = [];
      $error    = false;
      $dbu      = new DbUtils();

      $additional_data = new PluginPrintercountersAdditional_data();

      // Get items ip addresses for each processes
      $process      = new PluginPrintercountersProcess(0, 1, $itemtype, $items_id);
      $ip_addresses = $process->getIPAddressesForProcess();

      if (!empty($ip_addresses)) {
         // Get SNMP authentication by items
         $snmpauthentication  = new PluginPrintercountersSnmpauthentication();
         $snmp_auth = $snmpauthentication->getItemAuthentication(array_keys($ip_addresses), $itemtype);
         $snmp_auth[$items_id]['community'] = $snmp_auth[$items_id]['community_write'];// Set write community

         // Get record config by items (timeout, nb retries)
         $item_recordmodel  = new PluginPrintercountersItem_Recordmodel();
         $record_config     = $item_recordmodel->getItemRecordConfig(array_keys($ip_addresses), $itemtype);

         // Init counters search
         switch (strtolower($itemtype)) {
            case 'printer':
               foreach ($ip_addresses as $printers_id => $cards) {
                  foreach ($cards as $addresses) {
                     foreach ($addresses['ip'] as $ip) {
                        if (!empty($ip)) {
                           try {
                              $printer = new PluginPrintercountersPrinter($printers_id,
                                                                          $itemtype,
                                                                          $ip,
                                                                          $addresses['mac'],
                                                                          isset($record_config[$printers_id]) ? $record_config[$printers_id] : [],
                                                                          isset($snmp_auth[$printers_id])     ? $snmp_auth[$printers_id]     : []);

                              // Set values on printer
                              $SNMPsetValues = $this->getItems(0,
                                                               $dbu->getEntitiesRestrictRequest("AND",
                                                                                                $this->getTable(), "",
                                                                                                $record_config[$printers_id]['entities_id'],
                                                                                                true));
                              $SNMPsetValues = reset($SNMPsetValues);
                              if (!empty($SNMPsetValues)) {
                                 foreach ($SNMPsetValues as $key => &$val) {
                                    switch ($key) {
                                       case 'set_contact':
                                          if (!$val) {
                                             unset($SNMPsetValues[$key]);
                                             continue 2;
                                          }
                                          $SNMPsetValues['contact'] = $this->getTagTraduction($SNMPsetValues['contact'], $itemtype, $printers_id);
                                          break;

                                       case 'set_name':
                                          if (!$val) {
                                             unset($SNMPsetValues[$key]);
                                             continue 2;
                                          }
                                          $val = $record_config[$printers_id]['name'];
                                          break;

                                       case 'set_location':
                                          if (!$val) {
                                             unset($SNMPsetValues[$key]);
                                             continue 2;
                                          }
                                          $val = Dropdown::getDropdownName('glpi_locations', $record_config[$printers_id]['locations_id']);
                                          $val = str_replace(' > ', ', ', $val);
                                          break;
                                    }
                                    $val = Toolbox::substr($val, 0, 255);
                                 }

                                 $SNMPsetValues = $printer->setValues($SNMPsetValues);
                                 if (!empty($SNMPsetValues)) {
                                    $SNMPsetValues['items_recordmodels_id'] = $record_config[$printers_id]['plugin_items_recordmodels_id'];
                                    $additional_data->setAdditionalData($SNMPsetValues);
                                 }

                                 break 2;

                              } else {
                                 $messages[] = __('SNMPset configuration is not found in the printer entity', 'printercounters');
                                 $error      = true;
                              }

                              // Close session
                              $printer->closeSNMPSession();

                           } catch (PluginPrintercountersException $e) {
                              $messages[] = $e->getPrintercountersMessage();
                              $error      = true;
                           }
                        }
                     }
                  }
               }
               break;
         }

      } else {
         $messages[] = __('IP not found', 'printercounters');
         $error      = true;
      }

      return [$messages, $error];
   }

   /**
    * Function get tag traduction
    *
    * @param type $input
    * @return type
    */
   function getTagTraduction($input, $itemtype, $items_id) {

      if (!empty($input)) {
         preg_match_all("/##printer\.(\w*)##/", $input, $tags);

         $input = str_replace("\n", " - ", Html::cleanPostForTextArea($input));
         $dbu   = new DbUtils();
         $item  = $dbu->getItemForItemtype($itemtype);
         $item->getFromDB($items_id);

         $replaceValue = [];
         foreach ($tags[1] as $tag) {
            $value = $item->getField($tag);
            switch ($tag) {
               case 'users_id': case 'users_id_tech':
                     if (!empty($value)) {
                        $replaceValue[] = Dropdown::getDropdownName('glpi_users', $item->getField($tag));
                     }
                  break;
               case 'contact': case 'contact_num':
                     if (!empty($value)) {
                        $replaceValue[] = $item->getField($tag);
                     }
                  break;
               case 'groups_id':case 'groups_id_tech':
                     if (!empty($value)) {
                        $replaceValue[] = Dropdown::getDropdownName('glpi_groups', $item->getField($tag));
                     }
                  break;
               case 'user_num':
                  $value = $item->getField('users_id');
                  if (!empty($value)) {
                     $user = new User();
                     $user->getFromDB($item->getField('users_id'));
                     $replaceValue[] = $user->getField('phone');
                  }
                  break;
            }
            //            $input = preg_replace("/##printer\.".$tag."##/", $replaceValue, $input);
         }

         return implode(" - ", $replaceValue);
      }

      return false;
   }

   /**
    * Function get tags
    *
    * @return type
    */
   function getTags() {

      return [__('User')                                  => '##printer.users_id##',
                   __('Alternate username')                    => '##printer.contact##',
                   __('Technician in charge of the hardware')  => '##printer.users_id_tech##',
                   __('Group')                                 => '##printer.groups_id##',
                   __('Group in charge of the hardware')       => '##printer.groups_id_tech##',
                   __('User phone', 'printercounters')         => '##printer.user_num##',
                   __('Alternate username number')             => '##printer.contact_num##'];
   }


   /**
    * Provides search options configuration. Do not rely directly
    * on this, @see CommonDBTM::searchOptions instead.
    *
    * @since 9.3
    *
    * This should be overloaded in Class
    *
    * @return array a *not indexed* array of search options
    *
    * @see https://glpi-developer-documentation.rtfd.io/en/master/devapi/search.html
    **/
   public function rawSearchOptions() {

      $tab[] = [
         'id'                 => '111',
         'table'              => $this->getTable(),
         'field'              => 'set_name',
         'name'               => __('Set printer name', 'printercounters'),
         'massiveaction'      => true,
         'datatype'           => 'bool'
      ];

      $tab[] = [
         'id'                 => '112',
         'table'              => $this->getTable(),
         'field'              => 'set_location',
         'name'               => __('Set printer location', 'printercounters'),
         'massiveaction'      => true,
         'datatype'           => 'bool'
      ];

      $tab[] = [
         'id'                 => '113',
         'table'              => $this->getTable(),
         'field'              => 'set_contact',
         'name'               => __('Set printer contact informations', 'printercounters'),
         'massiveaction'      => true,
         'datatype'           => 'bool'
      ];

      $tab[] = [
         'id'                 => '114',
         'table'              => $this->getTable(),
         'field'              => 'contact',
         'name'               => __('Tag'),
         'massiveaction'      => true,
         'datatype'           => 'text'
      ];

      return $tab;
   }

   /**
   * Actions done before add
   *
   * @param type $input
   * @return type
   */
   function prepareInputForAdd($input) {
      if (!$this->checkDuplicateFields($input)) {
         return false;
      }

      return $input;
   }

   /**
   * checkMandatoryFields
   *
   * @param type $input
   * @return boolean
   */
   function checkDuplicateFields($input) {
      $dbu = new DbUtils();
      $data = $this->getItems(0,
                              $dbu->getEntitiesRestrictRequest("AND", $this->getTable(), "",
                                                               $_SESSION['glpiactiveentities'], true));

      $msg     = [];
      $checkKo = false;

      $fields = ['entities_id' => __('SNMPset is already configured for this entity', 'printercounters')];

      foreach ($data as $key => $value) {
         if ($value['entities_id'] == $input['entities_id']) {
            $msg[] = $fields['entities_id'];
            $checkKo = true;
         }
      }

      if ($checkKo) {
         Session::addMessageAfterRedirect(implode(', ', $msg), true, ERROR);
         return false;
      }
      return true;
   }
}
