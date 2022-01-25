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
 * Class PluginPrintercountersSysdescr
 *
 * This class allows to add and manage the sysdescrs used for the conforimty check of the items
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersSysdescr extends CommonDBTM {

   static $rightname = 'plugin_printercounters';

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   static function getTypeName($nb = 0) {
      return __('Sysdescr', 'Sysdescrs', $nb, 'printercounters');
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
            case 'PluginPrintercountersRecordmodel' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  $dbu = new DbUtils();
                  return self::createTabEntry(self::getTypeName(),
                                              $dbu->countElementsInTable($this->getTable(),
                                                                         ["plugin_printercounters_recordmodels_id" => $item->getID()]));
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
      $sysdescr = new self();

      switch ($item->getType()) {
         case 'PluginPrintercountersRecordmodel' :
            $sysdescr->showForRecordmodel($item);
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
         $script = "$('#printercounters_viewAddSysdescr').show();";
      } else {
         $script = "$('#printercounters_viewAddSysdescr').hide();";
         if(isset($options['parent'])){
            $options['plugin_printercounters_recordmodels_id'] = $options['parent']->getField('id');
         }
      }

      $this->initForm($ID, $options);

      echo html::scriptBlock($script);

      $this->showFormHeader($options);
      echo "<tr class='tab_bg_1'>";
      // Sysdescr
      echo "<td class='center' colspan='4'>";
      echo self::getTypeName().'&nbsp;';
      echo Html::input('sysdescr', ['value' => $this->fields['sysdescr'], 'size' => 40]);
      $p = (isset($options['parent']) ? $options['parent']->getField('id') : "");
      $parent = ((isset($p))? $p : 0);
      echo Html::hidden('plugin_printercounters_recordmodels_id', ['value' => $parent]);
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
   function showForRecordmodel($item) {

      $recordmodel = new PluginPrintercountersRecordmodel();
      $canedit = ($recordmodel->can($item->fields['id'], UPDATE) && $this->canCreate());

      $rand = mt_rand();

      if (isset($_POST["start"])) {
         $start = $_POST["start"];
      } else {
         $start = 0;
      }
      $data = $this->getItems($item->fields['id'], $start);

      if ($canedit) {
         echo "<div id='viewsysdescr".$item->fields['id']."_$rand'></div>\n";
         PluginPrintercountersAjax::getJSEdition("viewsysdescr".$item->fields['id']."_$rand",
                                                 "viewAddSysdescr".$item->fields['id']."_$rand",
                                                 $this->getType(),
                                                 -1,
                                                 'PluginPrintercountersRecordmodel',
                                                 $item->fields['id']);
         echo "<div class='center firstbloc'>".
               "<a class='submit btn btn-primary' id='printercounters_viewAddSysdescr' href='javascript:viewAddSysdescr".$item->fields['id']."_$rand();'>";
         echo __('Add a new sysdescr', 'printercounters')."</a></div>\n";
      }

      if (!empty($data)) {
         $this->listItems($item->fields['id'], $data, $canedit, $rand);
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
      echo "<th>".self::getTypeName(1)."</th>";
      echo "</tr>";

      foreach ($data as $field) {
         $onclick = ($canedit
                      ? "style='cursor:pointer' onClick=\"viewEditSysdescr".$field['plugin_printercounters_recordmodels_id']."_".
                        $field['id']."_$rand();\"": '');

         echo "<tr class='tab_bg_2'>";
         echo "<td width='10'>";
         if ($canedit) {
            Html::showMassiveActionCheckBox(__CLASS__, $field['id']);
            PluginPrintercountersAjax::getJSEdition("viewsysdescr".$ID."_$rand",
                                                    "viewEditSysdescr".$field['plugin_printercounters_recordmodels_id']."_".$field["id"]."_$rand",
                                                    $this->getType(),
                                                    $field["id"],
                                                    'PluginPrintercountersRecordmodel',
                                                    $field["plugin_printercounters_recordmodels_id"]);
         }
         echo "</td>";
         // Sysdescr
         echo "<td $onclick>".$field['sysdescr']."</td>";
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
    * @param type $recordmodels_id
    * @param type $start
    * @return type
    */
   function getItems($recordmodels_id, $start = 0) {
      global $DB;

      $output = [];

      $query = "SELECT `".$this->getTable()."`.`id`, 
                       `".$this->getTable()."`.`sysdescr`,
                       `".$this->getTable()."`.`plugin_printercounters_recordmodels_id`
          FROM ".$this->getTable()."
          WHERE `".$this->getTable()."`.`plugin_printercounters_recordmodels_id` = ".Toolbox::cleanInteger($recordmodels_id)."
          LIMIT ".intval($start).",".intval($_SESSION['glpilist_limit']);

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $output[$data['id']] = $data;
         }
      }

      return $output;
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
         'field'              => 'sysdescr',
         'name'               => self::getTypeName(),
         'massiveaction'      => true
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
      if (!$this->checkMandatoryFields($input)) {
         return false;
      }

      return $input;
   }

   /**
   * Actions done before update
   *
   * @param type $input
   * @return type
   */
   function prepareInputForUpdate($input) {
      if (!$this->checkMandatoryFields($input)) {
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
   function checkMandatoryFields($input) {
      $msg     = [];
      $checkKo = false;

      $mandatory_fields = ['sysdescr' => __('Sysdescr', 'printercounters')];

      foreach ($input as $key => $value) {
         if (array_key_exists($key, $mandatory_fields)) {
            if (empty($value)) {
               $msg[] = $mandatory_fields[$key];
               $checkKo = true;
            }
         }
      }

      if ($checkKo) {
         Session::addMessageAfterRedirect(sprintf(__("Mandatory fields are not filled. Please correct: %s"), implode(', ', $msg)), true, ERROR);
         return false;
      }
      return true;
   }
}
