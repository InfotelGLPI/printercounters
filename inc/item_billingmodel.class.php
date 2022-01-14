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
 * Class PluginPrintercountersItem_Billingmodel
 *
 * This class allows to add and manage billing models on the items
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersItem_Billingmodel extends CommonDBTM {

   static $types = ['Printer'];
   static $rightname = 'plugin_printercounters';

   public $dohistory = false;
   protected $itemtype;
   protected $items_id;


   /**
    * Constructor
    *
    * @param type $itemtype
    * @param type $items_id
    */
   public function __construct($itemtype = 'printer', $items_id = 0) {

      $this->setItemtype($itemtype);
      $this->setItems_id($items_id);

      parent::__construct();
   }

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   static function getTypeName($nb = 0) {
      return _n('Linked billing model', 'Linked billing models', $nb, 'printercounters');
   }

   static function canUpdateRecords() {
      return Session::haveRight('update_records', 1);
   }

   /**
    * Function sets itemtype id
    *
    * @param string $itemtype
    * @throws Exception
    */
   public function setItemtype($itemtype) {

      if (empty($itemtype)) {
         throw new PluginPrintercountersException(__('Invalid itemtype', 'printercounters'));
      }

      $this->itemtype = $itemtype;
   }

   /**
    * Function sets items id
    *
    * @param string $items_id
    */
   public function setItems_id($items_id) {

      $this->items_id = $items_id;
   }

   /**
    * getFieldName
    *
    * @param type $field
    * @return type
    */
   function getFieldName($field) {

      switch ($field) {
         case 'date': return __("Application date", 'printercounters');
         case 'entities_id': return __('Entity');
         case 'result': return __('Result', 'printercounters');
         case 'locations_id': return __('Location');
         case 'contracts_id': return __('Contract');
         case 'counters_name': return __('Counter type', 'printercounters');
         case 'counters_value': return __('Counter value', 'printercounters');
         case 'budgets_id': return __('Budget');
         case 'suppliers_id': return __('Supplier');
         case 'cost': return __('Cost');
      }
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
            case 'PluginPrintercountersBillingmodel' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  $dbu = new DbUtils();
                  return self::createTabEntry(__('Linked items', 'printercounters'),
                                              $dbu->countElementsInTable($this->getTable(),
                                                                         ["plugin_printercounters_billingmodels_id" => $item->getID()]));
               }
               return __('Linked items', 'printercounters');
               break;
            case 'Printer' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry(PluginPrintercountersBillingmodel::getTypeName(2));
               }
               return PluginPrintercountersBillingmodel::getTypeName(2);
               break;
         }
      }
      return '';
   }

   /**
    * Display tab content
    *
    * @static
    * @param CommonGLPI $item
    * @param int $tabnum
    * @param int $withtemplate
    * @return bool|true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      switch ($item->getType()) {
         case 'PluginPrintercountersBillingmodel' :
            $item_recordmodel = new self('Printer', $item->getID());
            $item_recordmodel->showForBillingmodel($item);
            break;
         case 'Printer' :
            $item_recordmodel = new self($item->getType(), $item->getID());
            $item_recordmodel->showForItem($item);
            break;
      }
      return true;
   }

   /**
    * showForItem
    *
    * @param type $item
    * @return boolean
    */
   function showForItem($item) {

      $used = [];

      $data = $this->find(['items_id' => $item->getID(), 'itemtype' => $item->getType()]);
      if (!empty($data)) {
         foreach ($data as $values) {
            $used[] = $values['plugin_printercounters_billingmodels_id'];
         }
      }
      if ($this->canCreate()) {
         // Link to a billing model
         echo "<form name='form' method='post' action='".Toolbox::getItemTypeFormURL($this->getType())."'>";
         echo "<div class='center'>";
         echo "<table border='0' class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th>".__('Link the item to a billing model', 'printercounters')."</th>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td class='center'>";
         echo PluginPrintercountersBillingmodel::getTypeName()."&nbsp;";
         Dropdown::show("PluginPrintercountersBillingmodel", ['name'    => 'plugin_printercounters_billingmodels_id',
                                                                    'entity' => $item->fields['entities_id'],
                                                                    'used'   => $used]);
         echo Html::hidden('items_id', ['value' => $this->items_id]);
         echo Html::hidden('itemtype', ['value' => $this->itemtype]);
         echo Html::submit(_sx('button', 'Add'), ['name' => 'add', 'class' => 'btn btn-primary']);
         echo "</td>";
         echo "</tr>";
         echo "</table>";
         echo '</div>';
         Html::closeForm();
      }

      // Record history
      if (!empty($data)) {
         $search = new PluginPrintercountersSearch();
         $search->showSearch($this, ['massiveaction' => true]);
      }
   }

   /**
    * showForBillingmodel
    *
    * @param type $item
    * @return boolean
    */
   function showForBillingmodel($item) {
      global $DB;

      $billingmodel = new PluginPrintercountersBillingmodel();
      $canedit = ($billingmodel->can($item->fields['id'], UPDATE) && $this->canCreate());

      $itemtype = $this->itemtype;

      if (isset($_POST["start"])) {
         $start = $_POST["start"];
      } else {
         $start = 0;
      }
      $data = $this->getItems($item->fields['id'], ['start' => $start, 'addLimit' => true]);
      $rows = count($this->getItems($item->fields['id'], ['addLimit' => false]));

      if ($canedit) {
         echo "<form name='form' method='post' action='".Toolbox::getItemTypeFormURL($this->getType())."'>";

         echo "<div align='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='6'>".__('Add an item', 'printercounters')."</th></tr>";

         echo "<tr class='tab_bg_1'>";
         // Dropdown item
         echo "<td class='center'>";
         echo $itemtype::getTypeName(2).'&nbsp;';
         $dbu = new DbUtils();

         $iterator = $DB->request(['SELECT' => 'items_id',
                                   'FROM'  => $this->getTable(),
                                   'WHERE' => [
                                      'itemtype' => $itemtype,
                                   ],
                                  ]);
         $used = [];
         foreach ($iterator as $row) {
            $used[] = $row['items_id'];
         }

         Dropdown::show($itemtype, ['name'        => 'items_id',
                                    'entity'      => $item->fields['entities_id'],
                                    'entity_sons' => true,
                                    'used'        => $used]);
         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         echo "<td class='tab_bg_2 center' colspan='6'>";
         echo Html::hidden('plugin_printercounters_billingmodels_id', ['value' => $item->fields['id']]);
         echo Html::hidden('itemtype', ['value' => $this->itemtype]);
         echo Html::submit(_sx('button', 'Add'), ['name' => 'add', 'class' => 'btn btn-primary']);
         echo "</td>";
         echo "</tr>";
         echo "</table></div>";
         Html::closeForm();
      }

      if (!empty($data)) {
         $this->listItems($data, $canedit, $start, $rows);
      }
   }

   /**
    * listItems
    *
    * @param array $data
    * @param bool $canedit
    * @param int $start
    */
   private function listItems($data, $canedit, $start, $rows) {

      $rand = mt_rand();

      $itemtype = $this->itemtype;

      echo "<div class='left'>";
      if ($canedit) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass'.__CLASS__.$rand];
         Html::showMassiveActions($massiveactionparams);
      }

      Html::printAjaxPager($itemtype::getTypeName(2), $start, $rows);
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th width='10'>";
      if ($canedit) {
         echo Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
      }
      echo "</th>";
      echo "<th>".__('Name')."</th>";
      echo "<th>".__('Entity')."</th>";
      echo "<th>".__('Status')."</th>";
      echo "<th>".__('Type')."</th>";
      echo "<th>".__('Model')."</th>";
      echo "<th>".__('Location')."</th>";
      echo "<th>".__('Last record date', 'printercounters')."</th>";
      echo "<th>".__('Last record type', 'printercounters')."</th>";
      echo "</tr>";

      foreach ($data as $field) {
         echo "<tr class='tab_bg_1'>";
         echo "<td width='10'>";
         if ($canedit) {
            Html::showMassiveActionCheckBox(__CLASS__, $field['id']);
         }
         echo "</td>";
         // Name
         $link = Toolbox::getItemTypeFormURL($this->itemtype).'?id='.$field['items_id'];
         echo "<td><a href='$link' target='_blank'>".$field['items_name']."</a></td>";
         // Entity name
         echo "<td>".$field['entities_name']."</td>";
         // State name
         echo "<td>".$field['states_name']."</td>";
         // Type name
         echo "<td>".$field['printertypes_name']."</td>";
         // Model name
         echo "<td>".$field['models_name']."</td>";
         // Location name
         echo "<td>".$field['locations_name']."</td>";
         // Last record date
         echo "<td>".html::convDateTime($field['last_record_date'])."</td>";
         // Last record type
         echo "<td>".PluginPrintercountersRecord::getRecordType($field['last_record_type'])."</td>";
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
    * Get item billingmodel items
    *
    * @global type $DB
    * @param type $billingmodels_id
    * @param type $options : - bool addLimit : add limit to the search
    *                        - int start     : start line
    *                        - int limit     : number of lines
    *
    * @return type
    */
   function getItems($billingmodels_id = 0, $options = []) {
      global $DB;

      $params['start'] = 0;
      $params['limit'] = $_SESSION['glpilist_limit'];
      $params['addLimit'] = true;

      if (!empty($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      $output = [];
      $dbu    = new DbUtils();

      $itemjoin   = $dbu->getTableForItemType($this->itemtype);
      $itemjoin2  = $dbu->getTableForItemType($this->itemtype . 'Model');
      $itemjoin3  = $dbu->getTableForItemType('State');
      $itemjoin4  = $dbu->getTableForItemType($this->itemtype . 'Type');
      $itemjoin5  = $dbu->getTableForItemType('Location');
      $itemjoin6  = $dbu->getTableForItemType('Entity');
      $itemjoin7  = $dbu->getTableForItemType('PluginPrintercountersItem_Recordmodel');
      $itemjoin8  = $dbu->getTableForItemType('PluginPrintercountersRecord');
      $itemjoin9  = $dbu->getTableForItemType('PluginPrintercountersBillingmodel');
      $itemjoin10 = $dbu->getTableForItemType('PluginPrintercountersPageCost');

      $query = "SELECT `".$itemjoin."`.`name` as items_name,
                       `".$itemjoin."`.`id` as items_id, 
                       `".$itemjoin6."`.`name` as entities_name,
                       `".$itemjoin3."`.`name` as states_name,
                       `".$itemjoin4."`.`name` as printertypes_name,
                       `".$itemjoin2."`.`name` as models_name,
                       `".$itemjoin5."`.`completename` as locations_name,
                       `".$this->getTable()."`.`id`,
                        `".$itemjoin9."`.`id` as billingmodels_id,
                        `".$itemjoin9."`.`application_date`,
                        `".$itemjoin7."`.`plugin_printercounters_recordmodels_id` as recordmodels_id,
                       `glpi_plugin_printercounters_records`.`date` as last_record_date,
                       `glpi_plugin_printercounters_records`.`record_type` as last_record_type
          FROM ".$this->getTable()."
          LEFT JOIN `".$itemjoin."` 
             ON (`".$this->getTable()."`.`items_id` = `".$itemjoin."`.`id`)
          LEFT JOIN `".$itemjoin2."` 
             ON (`".$itemjoin2."`.`id` = `".$itemjoin."`.`".strtolower($this->itemtype)."models_id`)
          LEFT JOIN `".$itemjoin3."` 
             ON (`".$itemjoin."`.`states_id` = `".$itemjoin3."`.`id`)  
          LEFT JOIN `".$itemjoin4."` 
             ON (`".$itemjoin4."`.`id` = `".$itemjoin."`.`".strtolower($this->itemtype)."types_id`) 
          LEFT JOIN `".$itemjoin5."` 
             ON (`".$itemjoin5."`.`id` = `".$itemjoin."`.`locations_id`) 
          LEFT JOIN `".$itemjoin6."` 
             ON (`".$itemjoin6."`.`id` = `".$itemjoin."`.`entities_id`) 
          LEFT JOIN `".$itemjoin9."` 
             ON (`".$itemjoin9."`.`id` = `".$this->getTable()."`.`plugin_printercounters_billingmodels_id`) 
          LEFT JOIN `".$itemjoin10."` 
             ON (`".$itemjoin9."`.`id` = `".$itemjoin10."`.`plugin_printercounters_billingmodels_id`)
          LEFT JOIN `".$itemjoin7."` 
             ON (`".$itemjoin7."`.`plugin_printercounters_recordmodels_id` = `".$itemjoin9."`.`plugin_printercounters_recordmodels_id` 
                  AND `".$itemjoin7."`.`items_id` = `$itemjoin`.`id` 
                  AND LOWER(`".$itemjoin7."`.`itemtype`) = LOWER('".$this->itemtype."')
                ) 
          LEFT JOIN `$itemjoin8`
             ON (`$itemjoin7`.`id` = `$itemjoin8`.`plugin_printercounters_items_recordmodels_id`
                  AND `$itemjoin8`.`date` = (
                       SELECT max(`$itemjoin8`.`date`) 
                       FROM $itemjoin8 
                       WHERE `$itemjoin8`.`plugin_printercounters_items_recordmodels_id` = `$itemjoin7`.`id`
                     )
                ) 
          WHERE 1";

      if ($billingmodels_id) {
         $query .= " AND`".$this->getTable()."`.`plugin_printercounters_billingmodels_id` = ".$billingmodels_id;
      }

      $query .= " GROUP BY `".$itemjoin."`.`id`  
          ORDER BY `".$itemjoin."`.`name` ASC";

      if ($params['addLimit']) {
         $query .= " LIMIT ".intval($params['start']).",".intval($params['limit']);
      }

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $output[$data['id']] = $data;
         }
      }

      return $output;
   }

   /**
    * Get billingmodel data for items
    *
    * @global type $DB
    * @param type $items_id
    * @param type $itemtype
    * @param type $options : - bool addLimit : add limit to the search
    *                        - int start     : start line
    *                        - int limit     : number of lines
    *
    * @return type
    */
   function getBillingmodelForItem($items_id = 0, $itemtype = null, $options = []) {
      global $DB;

      $params['start'] = 0;
      $params['limit'] = $_SESSION['glpilist_limit'];
      $params['order'] = null;
      $params['addLimit'] = true;
      $params['addItemDetails'] = true;

      if (!empty($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      $output = [];
      $dbu    = new DbUtils();

      $itemjoin   = $dbu->getTableForItemType($itemtype);
      $itemjoin2  = $dbu->getTableForItemType($itemtype . 'Model');
      $itemjoin3  = $dbu->getTableForItemType('State');
      $itemjoin4  = $dbu->getTableForItemType($itemtype . 'Type');
      $itemjoin5  = $dbu->getTableForItemType('Location');
      $itemjoin6  = $dbu->getTableForItemType('Entity');
      $itemjoin7  = $dbu->getTableForItemType('PluginPrintercountersItem_Recordmodel');
      $itemjoin8  = $dbu->getTableForItemType('PluginPrintercountersRecord');
      $itemjoin9  = $dbu->getTableForItemType('PluginPrintercountersBillingmodel');
      $itemjoin10 = $dbu->getTableForItemType('PluginPrintercountersPageCost');

      $query = "SELECT `".$itemjoin."`.`name` as items_name,
                       `".$itemjoin."`.`entities_id`,";

      if ($params['addItemDetails']) {
         $query .= "   `".$itemjoin6."`.`name` as entities_name,
                       `".$itemjoin3."`.`name` as states_name,
                       `".$itemjoin4."`.`name` as printertypes_name,
                       `".$itemjoin2."`.`name` as models_name,
                       `".$itemjoin5."`.`name` as locations_name,";
      }

      $query .= "      `".$this->getTable()."`.`id`,
                        GROUP_CONCAT(`".$itemjoin."`.`id` SEPARATOR '$$$$') as items_id,
                        GROUP_CONCAT(DISTINCT CONCAT(`".$itemjoin10."`.`cost`,'$$',`".$itemjoin10."`.`plugin_printercounters_countertypes_id`) SEPARATOR '$$$$') as counters_cost,
                       `".$itemjoin9."`.`id` as billingmodels_id,
                       `".$itemjoin9."`.`name` as billingmodels_name,
                       `".$itemjoin9."`.`entities_id` as billingmodels_entity,
                       `".$itemjoin9."`.`application_date`,
                       `".$itemjoin9."`.`plugin_printercounters_recordmodels_id` as recordmodels_id,
                       `".$itemjoin9."`.`is_recursive` as billingmodels_recursivity,
                       `".$itemjoin7."`.`plugin_printercounters_recordmodels_id` as currentitem_recordmodels_id,
                       `".$itemjoin7."`.`itemtype`";

      if ($params['addItemDetails']) {
         $query .= "      ,`glpi_plugin_printercounters_records`.`date` as last_record_date,
                          `glpi_plugin_printercounters_records`.`record_type` as last_record_type";
      }

      $query .= "
          FROM ".$this->getTable()."
          LEFT JOIN `".$itemjoin."` 
             ON (`".$this->getTable()."`.`items_id` = `".$itemjoin."`.`id`)";

      if ($params['addItemDetails']) {
         $query .= "
             LEFT JOIN `".$itemjoin2."` 
                ON (`".$itemjoin2."`.`id` = `".$itemjoin."`.`".strtolower($itemtype)."models_id`)
             LEFT JOIN `".$itemjoin3."` 
                ON (`".$itemjoin."`.`states_id` = `".$itemjoin3."`.`id`)  
             LEFT JOIN `".$itemjoin4."` 
                ON (`".$itemjoin4."`.`id` = `".$itemjoin."`.`".strtolower($itemtype)."types_id`) 
             LEFT JOIN `".$itemjoin5."` 
                ON (`".$itemjoin5."`.`id` = `".$itemjoin."`.`locations_id`) 
             LEFT JOIN `".$itemjoin6."` 
                ON (`".$itemjoin6."`.`id` = `".$itemjoin."`.`entities_id`)";
      }

      $query .= "
          LEFT JOIN `".$itemjoin9."` 
             ON (`".$itemjoin9."`.`id` = `".$this->getTable()."`.`plugin_printercounters_billingmodels_id`) 
          LEFT JOIN `".$itemjoin10."` 
             ON (`".$itemjoin9."`.`id` = `".$itemjoin10."`.`plugin_printercounters_billingmodels_id`)
          LEFT JOIN `".$itemjoin7."` 
             ON (`".$itemjoin7."`.`items_id` = `$itemjoin`.`id` 
                 AND LOWER(`".$itemjoin7."`.`itemtype`) = LOWER('".$itemtype."')
                )";

      if ($params['addItemDetails']) {
         $query .= "
             LEFT JOIN `$itemjoin8`
                ON (`$itemjoin7`.`id` = `$itemjoin8`.`plugin_printercounters_items_recordmodels_id`
                     AND `$itemjoin8`.`date` = (
                          SELECT max(`$itemjoin8`.`date`) 
                          FROM $itemjoin8 
                          WHERE `$itemjoin8`.`plugin_printercounters_items_recordmodels_id` = `$itemjoin7`.`id`
                        )
                   )";
      }

      $query .= "
          WHERE 1";

      // Where
      if (!empty($items_id) && !empty($itemtype)) {
         if (is_array($items_id)) {
            $items_id = implode(",", $items_id);
         }
         $query .= " AND `".$this->getTable()."`.`items_id` IN (".$items_id.") AND LOWER(`".$this->getTable()."`.`itemtype`)=LOWER('".$itemtype."')";
      }

      $query .= " GROUP BY `".$this->getTable()."`.`plugin_printercounters_billingmodels_id`";

      // Order
      if (!empty($params['order'])) {
         $query .= "ORDER BY ".$params['order'];
      } else {
         $query .= "ORDER BY `".$itemjoin."`.`name` ASC";
      }

      // Limit
      if ($params['addLimit']) {
         $query .= " LIMIT ".intval($params['start']).",".intval($params['limit']);
      }

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            // Manage group by
            $explode1 = explode("$$$$", $data['counters_cost']);
            foreach ($explode1 as $explode) {
               list($counters_cost, $countertypes_id) = explode("$$", $explode);
               $data['counters'][$countertypes_id] = $counters_cost;
            }

            $data['items_id'] = array_unique(explode("$$$$", $data['items_id']));

            $output[$data['id']] = $data;
         }
      }

      return $output;
   }

   /**
    * Check if the item can be added in the billingmodel
    *
    * @global type $DB
    * @param type $billingmodels_id
    * @param type $items_id
    * @return boolean
    */
   function checkItem($billingmodels_id, $items_id = 0) {
      global $DB;

      $output = [];
      $dbu    = new DbUtils();

      $itemjoin  = $dbu->getTableForItemType('PluginPrintercountersBillingmodel');
      $itemjoin2 = $dbu->getTableForItemType('PluginPrintercountersRecordmodel');
      $itemjoin3 = $dbu->getTableForItemType('PluginPrintercountersItem_Recordmodel');

      $query = "SELECT `".$itemjoin3."`.`items_id`
          FROM ".$itemjoin3."
          INNER JOIN `".$itemjoin2."` 
             ON (`".$itemjoin3."`.`plugin_printercounters_recordmodels_id` = `".$itemjoin2."`.`id`)
          INNER JOIN `".$itemjoin."` 
             ON (`".$itemjoin."`.`plugin_printercounters_recordmodels_id` = `".$itemjoin2."`.`id`)
          WHERE `".$itemjoin."`.`id` = $billingmodels_id";

      if ($items_id > 0) {
         $query .= " AND `".$itemjoin3."`.`items_id` = ".$items_id;
      }

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $output[$data['items_id']] = $data;
         }
         return $output;
      }

      return false;
   }

   /**
    * Clean if the item cannot be in the billingmodel
    *
    * @param type $billingmodels_id
    */
   function cleanItems($billingmodels_id) {

      $items = $this->getItems($billingmodels_id, ['addLimit' => false]);
      $check_items = $this->checkItem($billingmodels_id);
      if (!empty($items) && !empty($check_items)) {
         foreach ($items as $data) {
            if (!in_array($data['items_id'], array_keys($check_items))) {
               $this->deleteByCriteria(['items_id' => $data['items_id']], 1);
            }
         }
      }
   }

   /**
    * Compute records costs
    *
    * @param type $records
    * @param type $oid_type
    * @return type
    */
   function computeRecordCost($records, $oid_type = []) {

      $total_record_cost = 0;
      $total_page_number = 0;
      // Specific oid
      $total_oid_type = [];
      if (!empty($oid_type)) {
         foreach ($oid_type as $type) {
            $total_oid_type[$type]['page_number'] = 0;
            $total_oid_type[$type]['record_cost'] = 0;
         }
      }

      $itemtype = strtolower($this->itemtype);

      if (!empty($records)) {
         $used = [];
         $all = [];

         // Get counter costs with billing
         $billings = $this->getBillingmodelForItem($this->items_id, $this->itemtype, ['addLimit' => false, 'addItemDetails' => false, 'order' => "`glpi_plugin_printercounters_billingmodels`.`application_date` DESC"]);

         // Set counter costs for each records according to the application date
         $successLabel = PluginPrintercountersRecord::getResult(PluginPrintercountersRecord::$SUCCESS);

         foreach ($records as $id => &$record) {
            $record['record_cost'] = 0;
            $record['page_number'] = 0;
            // Specific oid
            if (!empty($oid_type)) {
               foreach ($oid_type as $type) {
                  $record['record_cost_'.$type] = 0;
                  $record['page_number_'.$type] = 0;
               }
            }

            $record['application_date'] = null;

            // Get only successful records for computation
            if (($record['result'] == PluginPrintercountersRecord::$SUCCESS || $record['result'] == $successLabel)) {
               $all[$itemtype][$record['items_id']][] = $id;
               sort($all[$itemtype][$record['items_id']]);
            }

            if (!empty($billings)) {
               foreach ($billings as $billing) {
                  // Check recordmodel and application date
                  if ($billing['recordmodels_id'] == $record['recordmodels_id']
                          && $billing['currentitem_recordmodels_id'] == $billing['recordmodels_id']
                          && in_array($record['items_id'], $billing['items_id'])
                          && strtolower($billing['itemtype']) == $itemtype
                          && ($record['result'] == PluginPrintercountersRecord::$SUCCESS || $record['result'] == $successLabel)
                          && $billing['application_date'] <= $record['date']) {

                     // Get counters costs
                     foreach ($record['counters'] as $countertypes_id => &$counters) {
                        if (in_array($countertypes_id, array_keys($billing['counters']))) {
                           $counters['cost'] = $billing['counters'][$countertypes_id];
                        }
                     }

                     $record['application_date'] = $billing['application_date'];

                     $used[$itemtype][$record['items_id']][] = $id;
                     sort($used[$itemtype][$record['items_id']]);
                     break;
                  }
               }
            }
         }

         // Compute page number
         if (!empty($all)) {
            foreach ($all as $itemtype => $items) {
               foreach ($items as $items_id => $records_id) {
                  foreach ($records_id as $key => $id) {
                     $volume = 0;
                     foreach ($records[$id]['counters'] as $countertypes_id => &$counter) {
                        // Compare with previous record
                        if (isset($records_id[$key - 1])
                                && isset($records[$records_id[$key - 1]]['counters'][$countertypes_id])) {

                           // Volume = diff between 2 records
                           $volume = ($counter['counters_value'] - $records[$records_id[$key - 1]]['counters'][$countertypes_id]['counters_value']);
                           if ($volume < 0) {
                              $volume = 0;
                           }

                           $records[$id]['page_number'] += $volume;

                           if (!empty($oid_type)) {
                              foreach ($oid_type as $type) {
                                 if (isset($counter['oid_type']) && $type == $counter['oid_type']) {
                                    $records[$id]['page_number_'.$type] += $volume;
                                 }
                              }
                           }
                        }
                     }

                     // Total of page number for all
                     $total_page_number += $records[$id]['page_number'];

                     // Total of page number for specific oid types
                     if (!empty($oid_type)) {
                        foreach ($oid_type as $type) {
                           $total_oid_type[$type]['page_number'] += $records[$id]['page_number_'.$type];
                        }
                     }
                  }
               }
            }
         }

         // Compute record costs for each items
         if (!empty($used)) {
            foreach ($used as $itemtype => $items) {
               foreach ($items as $items_id => $records_id) {
                  foreach ($records_id as $key => $id) {
                     $volume = 0;
                     foreach ($records[$id]['counters'] as $countertypes_id => &$counter) {
                           // Compare with previous record
                        if (isset($records_id[$key - 1])
                                && isset($records[$records_id[$key - 1]]['counters'][$countertypes_id])
                                && isset($counter['cost'])) {

                           // Volume = diff between 2 records
                           $volume = ($counter['counters_value'] - $records[$records_id[$key - 1]]['counters'][$countertypes_id]['counters_value']);
                           if ($volume < 0) {
                              $volume = 0;
                           }

                           // If application date is between 2 records : PRORATA
                           if ($records[$id]['application_date'] <= $records[$id]['date'] && $records[$id]['application_date'] >= $records[$records_id[$key - 1]]['date']) {
                              $old_billing = (($volume * 2) / 6) * $records[$records_id[$key - 1]]['counters'][$countertypes_id]['cost'];
                              $new_billing = (($volume * 4) / 6) * $counter['cost'];
                              $counter['counters_cost'] = $old_billing + $new_billing;
                              // Else
                           } else {
                              $counter['counters_cost'] = $volume * $counter['cost'];
                           }
                        } else {
                           $counter['counters_cost'] = 0;
                        }

                        // Current recrod cost : cumulate counters cost
                        $records[$id]['record_cost'] += $counter['counters_cost'];

                        if (!empty($oid_type)) {
                           foreach ($oid_type as $type) {
                              if (isset($counter['oid_type']) && $type == $counter['oid_type']) {
                                 $records[$id]['record_cost_'.$type] += $counter['counters_cost'];
                              }
                           }
                        }
                     }

                     // Total of record cost for all
                     $total_record_cost += $records[$id]['record_cost'];

                     // Total of record cost for specific oid types
                     if (!empty($oid_type)) {
                        foreach ($oid_type as $type) {
                           $total_oid_type[$type]['record_cost'] += $records[$id]['record_cost_'.$type];
                        }
                     }
                  }
               }
            }
         }
      }

      return ['records'           => $records,
                   'total_record_cost' => $total_record_cost,
                   'total_page_number' => $total_page_number,
                   'total_oid_type'    => $total_oid_type];
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

      $itemtype = $this->itemtype;
      $dbu      = new DbUtils();
      $item     = $dbu->getItemForItemtype($itemtype);

      $tab[] = [
         'id'                 => '100',
         'table'              => 'glpi_plugin_printercounters_billingmodels',
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '101',
         'table'              => 'glpi_plugin_printercounters_billingmodels',
         'field'              => 'application_date',
         'name'               => $this->getFieldName('date'),
         'datatype'           => 'datetime',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '102',
         'table'              => 'glpi_entities',
         'field'              => 'name',
         'name'               => __('Entity'),
         'massiveaction'      => false,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_plugin_printercounters_billingmodels'
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '103',
         'table'              => 'glpi_plugin_printercounters_countertypes',
         'field'              => 'name',
         'name'               => $this->getFieldName('counters_name'),
         'massiveaction'      => false,
         'datatype'           => 'dropdown',
         'forcegroupby'       => true,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_plugin_printercounters_pagecosts',
               'joinparams'         => [
                  'jointype'           => 'child',
                  'beforejoin'         => [
                     'table'              => 'glpi_plugin_printercounters_billingmodels'
                  ]
               ]
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '104',
         'table'              => 'glpi_plugin_printercounters_pagecosts',
         'field'              => 'cost',
         'name'               => $this->getFieldName('cost'),
         'massiveaction'      => false,
         'forcegroupby'       => true,
         'datatype'           => 'specific',
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_plugin_printercounters_billingmodels'
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '106',
         'table'              => 'glpi_contracts',
         'field'              => 'name',
         'name'               => $this->getFieldName('contracts_id'),
         'datatype'           => 'dropdown',
         'massiveaction'      => false,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_plugin_printercounters_billingmodels'
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '107',
         'table'              => 'glpi_suppliers',
         'field'              => 'name',
         'name'               => $this->getFieldName('suppliers_id'),
         'datatype'           => 'dropdown',
         'massiveaction'      => false,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_plugin_printercounters_billingmodels'
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '108',
         'table'              => 'glpi_budgets',
         'field'              => 'name',
         'name'               => $this->getFieldName('budgets_id'),
         'datatype'           => 'dropdown',
         'massiveaction'      => false,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_plugin_printercounters_billingmodels'
            ]
         ]
      ];

      $tab[] = [
         'id'                 => '109',
         'table'              => $dbu->getTableForItemType($itemtype),
         'field'              => 'id',
         'name'               => $item::getTypeName().' ID',
         'datatype'           => 'number',
         'massiveaction'      => false,
         'linkfield'          => 'items_id',
         'nosearch'           => true,
         'nodisplay'          => '1'
      ];

      $tab[] = [
         'id'                 => '110',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'datatype'           => 'number',
         'massiveaction'      => false,
         'nosearch'           => true,
         'nodisplay'          => true
      ];

      return $tab;
   }

   /**
    * Add search options for an item
    *
    * @return array
    */
   function getAddSearchOptions() {
      $dbu = new DbUtils();

      $tab[6099]['table'] = 'glpi_plugin_printercounters_billingmodels';
      $tab[6099]['field'] = 'name';
      $tab[6099]['name'] = __('Printer counters', 'printercounters').' - '.PluginPrintercountersBillingmodel::getTypeName(1);
      $tab[6099]['datatype'] = 'dropdown';
      $tab[6099]['massiveaction'] = false;
      $tab[6099]['joinparams'] = ['beforejoin' => ['table' => 'glpi_plugin_printercounters_items_billingmodels',
              'joinparams' => ['jointype' => 'itemtype_item',
                  'beforejoin' => ['table' => $dbu->getTableForItemType($this->itemtype)]]]
      ];

      return $tab;
   }

   /**
    * Massive actions to be added
    *
    * @param $input array of input datas
    *
    * @return array of results (nbok, nbko, nbnoright counts)
    * */
   function massiveActions() {

      $prefix = $this->getType().MassiveAction::CLASS_ACTION_SEPARATOR;

      if ($this->canCreate()) {
         switch ($this->itemtype) {
            case "Printer":
               return [
                   $prefix."plugin_printercounters_billingmodel" => __('Printer counters', 'printercounters').' - '.__('Set billing model', 'printercounters'),
               ];
         }
      }
   }

   /**
    * Massive actions display
    *
    * @param $input array of input datas
    *
    * @return array of results (nbok, nbko, nbnoright counts)
    * */
   static function showMassiveActionsSubForm(MassiveAction $ma) {

      $itemtype         = $ma->getItemtype(false);
      $item_recordmodel = new PluginPrintercountersItem_Recordmodel();

      if ($item_recordmodel->canCreate()) {
         switch (strtolower($itemtype)) {
            case 'printer':
               switch ($ma->getAction()) {
                  case "plugin_printercounters_billingmodel":
                     Dropdown::show("PluginPrintercountersBillingmodel", ['name' => 'plugin_printercounters_billingmodels_id']);
                     break;
               }
               return parent::showMassiveActionsSubForm($ma);
         }
      }
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {
      $itemtype         = $ma->getItemtype(false);
      $item_billingmodel = new self();

      //      $data = $item_billingmodel->find("`items_id` IN ('".implode("','", $ids)."') AND LOWER(`itemtype`)=LOWER('".$itemtype."')");
      //      $item_data = array();
      //      foreach ($data as $key => $val) {
      //         $item_data[$val['items_id']] = $val;
      //      }

      foreach ($ids as $key => $val) {
         if ($item->can($key, UPDATE)) {
            $result = false;
            switch ($ma->getAction()) {
               case "plugin_printercounters_billingmodel":
                  //                  if (isset($item_data[$key])) {
                  //                     $result = $item_billingmodel->update(array('id' => $item_data[$key]['id'], 'plugin_printercounters_billingmodels_id' => $ma->POST['plugin_printercounters_billingmodels_id']));
                  //                  } else {
                     $result = $item_billingmodel->add(['plugin_printercounters_billingmodels_id' => $ma->POST['plugin_printercounters_billingmodels_id'],
                                                             'items_id'                                => $key,
                                                             'itemtype'                                => $itemtype]);
                  //                  }
                  break;
               default :
                  return parent::doSpecificMassiveActions($ma->POST);
            }

            if ($result) {
               $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
            } else {
               $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
               $ma->addMessage($item->getErrorMessage(ERROR_COMPAT));
            }
         } else {
            $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_NORIGHT);
            $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
         }
      }
   }

   /**
    * Search function : addRestriction
    *
    * @return string
    */
   function addRestriction() {

      $options     = Search::getCleanedOptions($this->getType());
      $restriction = '';
      $dbu         = new DbUtils();
      foreach ($options as $num => $val) {
         if (isset($val['table']) && isset($val['field'])
             && $val['table'] == $dbu->getTableForItemType($this->itemtype)
             && $val['field'] == 'id') {
            $restriction .= PluginPrintercountersSearch::addWhere('', 0, $this->getType(), $num,
                                                                  'equals', $this->items_id);
         }
      }

      return $restriction;
   }

   /**
    * Search function : addOrder
    *
    * @return string
    */
   function addOrder() {

      $order_by = [];
      $options  = Search::getCleanedOptions($this->getType());
      $dbu      = new DbUtils();
      foreach ($options as $num => $val) {
         if ($val['table'] == $dbu->getTableForItemType('PluginPrintercountersBillingmodel') && $val['field'] == 'application_date') {
            $order_by = [$num, 'DESC'];
            break;
         }
      }

      return $order_by;
   }

   /**
    * Actions done before add
    *
    * @param type $input
    * @return boolean
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
    * @return boolean
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
      $dbu     = new DbUtils();

      $item = $dbu->getItemForItemtype($this->itemtype);

      $mandatory_fields = ['plugin_printercounters_billingmodels_id' => PluginPrintercountersBillingmodel::getTypeName(),
                           'items_id'                                => $item::getTypeName()];

      foreach ($input as $key => $value) {
         if (array_key_exists($key, $mandatory_fields)) {
            switch ($key) {
               case 'items_id':
                  if (!$this->checkItem($input['plugin_printercounters_billingmodels_id'], $value) || empty($value)) {
                     $msg[$key] = $mandatory_fields[$key];
                     $checkKo = true;
                  }
                  break;
               default:
                  if (empty($value)) {
                     $msg[$key] = $mandatory_fields[$key];
                     $checkKo = true;
                  }
                  break;
            }
         }
      }

      if ($checkKo) {
         foreach ($msg as $key => $value) {
            switch ($key) {
               case 'items_id':
                  Session::addMessageAfterRedirect(sprintf(__("Item cannot be added, check the record model", 'printercounters')), true, ERROR);
                  return false;
               default:
                  Session::addMessageAfterRedirect(sprintf(__("Mandatory fields are not filled. Please correct: %s"), implode(', ', $msg)), true, ERROR);
                  return false;
            }
         }
      }

      return true;
   }

}

