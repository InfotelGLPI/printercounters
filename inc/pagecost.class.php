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
 * Class PluginPrintercountersPagecost
 *
 * This class allows to add and manage the counter type costs on the billing models
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersPagecost extends CommonDBTM {

   static $types = ['PluginPrintercountersBillingmodel'];
   static $rightname = 'plugin_printercounters';

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   static function getTypeName($nb = 0) {
      return _n('Counter types of billing model', 'Counter types of billing models', $nb, 'printercounters');
   }

   /**
    * Display tab for each users
    *
    * @param CommonGLPI $item
    * @param int $withtemplate
    * @return array|string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate) {
         if ($item->getType() == 'PluginPrintercountersBillingmodel') {
            if ($_SESSION['glpishow_count_on_tabs']) {
               $dbu = new DbUtils();
               return self::createTabEntry(self::getTypeName(1),
                                           $dbu->countElementsInTable($this->getTable(),
                                                                      ["plugin_printercounters_billingmodels_id" => $item->getID()]));
            }
            return self::getTypeName(1);
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
      $field = new self();

      if (in_array($item->getType(), self::$types)) {
         $field->showForBillingmodel($item);
      }
      return true;
   }

   /**
    * Show pagecost form
    *
    * @param $ID        integer  ID of the item
    * @param $options   array    options used
    */
   function showForm($ID, $options = []) {

      if ($ID > 0) {
         $script = "$('#printercounters_viewAddPagecost').show();";
      } else {
         $script = "$('#printercounters_viewAddPagecost').hide();";
         $options['plugin_printercounters_billingmodels_id'] = $options['parent']->getField('id');
      }

      $this->initForm($ID, $options);

      echo html::scriptBlock($script);

      $data = $this->getCounterTypes($options['parent']->getField('id'));

      $used_countertypes = [];
      if (!empty($data)) {
         foreach ($data as $field) {
            $used_countertypes[] = $field['countertypes_id'];
         }
      }

      $this->showFormHeader($options);
      echo "<tr class='tab_bg_1'>";
      // Dropdown countertype
      echo "<td class='center'>";
      echo PluginPrintercountersCountertype::getTypeName(2).'&nbsp;';
      Dropdown::show("PluginPrintercountersCountertype",
              ['name'  => 'plugin_printercounters_countertypes_id',
                    'value' => $this->fields['plugin_printercounters_countertypes_id'],
                    'used'  => $used_countertypes]);
      echo "</td>";
      // Cost
      echo "<td class='center' colspan='3'>";
      echo __('Cost').'&nbsp;';
      self::showCostInput($this, $this->fields['cost']);
      echo Html::hidden('plugin_printercounters_billingmodels_id', ['value' => $options['parent']->getField('id')]);
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }

   /**
    * Show for billing model
    *
    * @param type $item
    */
   function showForBillingmodel($item) {

      $recordmodel = new PluginPrintercountersBillingmodel();
      $canedit = ($recordmodel->can($item->fields['id'], UPDATE) && $this->canCreate());

      $data = $this->getCounterTypes($item->fields['id']);

      $rand = mt_rand();

      // JS edition
      if ($canedit) {
         echo "<div id='viewcountertype".$item->fields['id']."_$rand'></div>\n";
         PluginPrintercountersAjax::getJSEdition("viewcountertype".$item->fields['id']."_$rand",
                                                 "viewAddCounterType".$item->fields['id']."_$rand",
                                                 $this->getType(),
                                                 -1,
                                                 'PluginPrintercountersBillingmodel',
                                                 $item->fields['id']);
         echo "<div class='center firstbloc'>".
               "<a class='submit btn btn-primary' id='printercounters_viewAddPagecost' href='javascript:viewAddCounterType".$item->fields['id']."_$rand();'>";
         echo __('Add a new counter', 'printercounters')."</a></div>\n";
      }

      if (!empty($data)) {
         $this->listItems($item->fields['id'], $data, $canedit, $rand);
      }

   }

   /**
    * List pagecosts
    *
    * @param type $ID
    * @param type $data
    * @param type $canedit
    * @param type $rand
    */
   private function listItems($ID, $data, $canedit, $rand) {

      echo "<div class='left'>";
      if ($canedit) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass'.__CLASS__.$rand];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4'>".PluginPrintercountersCountertype::getTypeName(2)."</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th width='10'>";
      if ($canedit) {
         echo Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
      }
      echo "</th>";
      echo "<th>".__('Name')."</th>";
      echo "<th>".__('Cost')."</th>";
      echo "<th>".__('OID type', 'printercounters')."</th>";
      echo "</tr>";

      foreach ($data as $field) {
         $onclick = ($canedit
                      ? "style='cursor:pointer' onClick=\"viewEditCounterType".$field['plugin_printercounters_billingmodels_id']."_".
                        $field['id']."_$rand();\"": '');

         echo "<tr class='tab_bg_2'>";
         echo "<td width='10'>";
         if ($canedit) {
            Html::showMassiveActionCheckBox(__CLASS__, $field['id']);
            // JS edition
            PluginPrintercountersAjax::getJSEdition("viewcountertype".$ID."_$rand",
                                                    "viewEditCounterType".$field['plugin_printercounters_billingmodels_id']."_".$field["id"]."_$rand",
                                                    $this->getType(),
                                                    $field["id"],
                                                    'PluginPrintercountersBillingmodel',
                                                    $field["plugin_printercounters_billingmodels_id"]);
         }
         echo "</td>";
         // Name
         $link = Toolbox::getItemTypeFormURL('PluginPrintercountersCountertype').'?id='.$field['countertypes_id'];
         echo "<td $onclick><a href='$link' target='_blank'>".$field['countertypes_name']."</a></td>";
         // Cost
         echo "<td $onclick>".self::getCost($field['cost'])."</td>";
         // OID type
         $alloidtypes = PluginPrintercountersCountertype_Recordmodel::getAllOidTypeArray();
         echo "<td $onclick>".$alloidtypes[$field['oid_type']]."</td>";
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
    * Get counter types for a billing model
    *
    * @global type $DB
    * @param type $billingmodels_id
    * @return type
    */
   function getCounterTypes($billingmodels_id) {
      global $DB;

      $output = [];

      $query = "SELECT `glpi_plugin_printercounters_countertypes`.`name` as countertypes_name, 
                       `glpi_plugin_printercounters_countertypes`.`id` as countertypes_id, 
                       `".$this->getTable()."`.`plugin_printercounters_billingmodels_id`,
                       `".$this->getTable()."`.`id`, 
                       `".$this->getTable()."`.`cost`,
                       `glpi_plugin_printercounters_countertypes_recordmodels`.`oid_type`
          FROM ".$this->getTable()."
          LEFT JOIN `glpi_plugin_printercounters_countertypes` 
             ON (`".$this->getTable()."`.`plugin_printercounters_countertypes_id` = `glpi_plugin_printercounters_countertypes`.`id`)
          LEFT JOIN `glpi_plugin_printercounters_billingmodels` 
             ON (`".$this->getTable()."`.`plugin_printercounters_billingmodels_id` = `glpi_plugin_printercounters_billingmodels`.`id`)
          LEFT JOIN `glpi_plugin_printercounters_countertypes_recordmodels` 
             ON (`glpi_plugin_printercounters_billingmodels`.`plugin_printercounters_recordmodels_id` = `glpi_plugin_printercounters_countertypes_recordmodels`.`plugin_printercounters_recordmodels_id` 
                  AND `".$this->getTable()."`.`plugin_printercounters_countertypes_id` =  `glpi_plugin_printercounters_countertypes_recordmodels`.`plugin_printercounters_countertypes_id`)
          WHERE `".$this->getTable()."`.`plugin_printercounters_billingmodels_id` = ".Toolbox::cleanInteger($billingmodels_id);

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $output[$data['id']] = $data;
         }
      }

      return $output;
   }

   /**
    * Add counter types of the recordmodel for a billingmodel
    *
    * @param type $recordmodels_id
    * @param type $billingmodels_id
    */
   function addRecordmodelCounterTypesForBilling($recordmodels_id, $billingmodels_id) {

      $countertype_recordmodel = new PluginPrintercountersCountertype_Recordmodel();
      $data = $countertype_recordmodel->getCounterTypes($recordmodels_id);
      if (!empty($data)) {
         foreach ($data as $values) {
            if ($values['oid_type'] != PluginPrintercountersCountertype_Recordmodel::SERIAL
                    && $values['oid_type'] != PluginPrintercountersCountertype_Recordmodel::SYSDESCR
                        && $values['oid_type'] != PluginPrintercountersCountertype_Recordmodel::NAME
                           && $values['oid_type'] != PluginPrintercountersCountertype_Recordmodel::NUMBER_OF_PRINTED_PAPERS
                              && $values['oid_type'] != PluginPrintercountersCountertype_Recordmodel::MODEL) {

               $this->add(['plugin_printercounters_countertypes_id'  => $values['countertypes_id'],
                                'plugin_printercounters_billingmodels_id' => $billingmodels_id]);
            }
         }
      }
   }

   /**
    * Add a counter type for the billingmodels associated to a same recordmodel
    *
    * @param type $recordmodels_id
    * @param type $countertypes_id
    */
   function addCounterTypeForBillings($recordmodels_id, $countertypes_id) {

      $billingmodel = new PluginPrintercountersBillingmodel();
      $data = $billingmodel->find(['plugin_printercounters_items_recordmodels_id' => $recordmodels_id]);
      if (!empty($data)) {
         foreach ($data as $values) {
            $this->add(['plugin_printercounters_countertypes_id'  => $countertypes_id,
                             'plugin_printercounters_billingmodels_id' => $values['id']]);
         }
      }
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

      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '72',
         'table'              => 'glpi_plugin_printercounters_countertypes',
         'field'              => 'name',
         'name'               => PluginPrintercountersCountertype::getTypeName(),
         'datatype'           => 'dropdown',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '73',
         'table'              => $this->getTable(),
         'field'              => 'cost',
         'datatype'           => 'specific',
         'name'               => __('Cost'),
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '75',
         'table'              => 'glpi_plugin_printercounters_billingmodels',
         'field'              => 'name',
         'name'               => PluginPrintercountersBillingmodel::getTypeName(),
         'datatype'           => 'dropdown',
         'massiveaction'      => false
      ];

      return $tab;
   }

   /**
    * @since version 0.84
    *
    * @param $field
    * @param $values
    * @param $options   array
    */
   static function getSpecificValueToDisplay($field, $values, array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'cost' :
            return self::getCost($values[$field]);
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }


   /**
    * @since version 0.84
    *
    * @param $field
    * @param $name            (default '')
    * @param $values          (default '')
    * @param $options   array
    *
    * @return string
    */
   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }

      $item = new self();

      $options['display'] = false;
      $options['value']   = $values[$field];
      switch ($field) {
         case 'cost':
            $item->getEmpty();
            return self::showCostInput($item, $options['value'], $options);
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }

   /**
    * Show the cost
    *
    * @return an array
    */
   static function showCostInput($item, $value, array $options = []) {
      return Html::input('cost', ['value' => self::getCost($value), 'size' => 40]);
   }


   /**
    * Function get cost
    *
    * @return an array
    */
   static function getCost($value) {
      return Html::formatNumber($value, false, 5);
   }

   /**
    * Actions done before add
    *
    * @param type $input
    * @return boolean
    */
   function prepareInputForAdd($input) {
      if (isset($input['cost'])) {
         $input['cost'] = str_replace(',', '.', $input['cost']);
      }
      if (!$this->checkMandatoryFields($input)) {
         return false;
      }

      return $input;
   }

   /**
    * Actions done before update
    *
    * @param type $input
    * @return boolean
    */
   function prepareInputForUpdate($input) {
      if (isset($input['cost'])) {
         $input['cost'] = str_replace(',', '.', $input['cost']);
      }
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

      $mandatory_fields = ['cost'                                   => __('OID', 'printercounters'),
                                'plugin_printercounters_countertypes_id' => PluginPrintercountersCountertype::getTypeName()];

      foreach ($input as $key => $value) {
         if (array_key_exists($key, $mandatory_fields)) {
            if (empty($value)) {
               $msg[] = $mandatory_fields[$key];
               $checkKo = true;
            }

            switch ($key) {
               case 'cost':
                  if (!is_numeric($value)) {
                     $msg[] = $mandatory_fields[$key];
                     $checkKo = true;
                  }
                  break;
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
