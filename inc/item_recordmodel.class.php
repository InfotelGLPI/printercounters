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
 * Class PluginPrintercountersItem_Recordmodel
 * 
 * This class allows to add and manage record models on the items
 * 
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersItem_Recordmodel extends CommonDBTM {

   static $types = array('Printer');
   
   public $dohistory = true; 

   var $rand = 0;
   
   protected $itemtype;
   protected $items_id;

   
   /**
    * Constructor
    * 
    * @param type $itemtype
    * @param type $items_id
    */
   public function __construct($itemtype = 'printer', $items_id=0) {
      $this->setItemtype($itemtype);
      $this->setItems_id($items_id);
      $this->setRand();
      
      parent::__construct();
   }
   
   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   static function getTypeName($nb=0) {
      return _n('Linked record model','Linked record models', $nb, 'printercounters');
   }
   
   // Printercounter's authorized profiles have right
   static function canCreate() {
      return plugin_printercounters_haveRight('printercounters', 'w');
   }
   
   // Printercounter's authorized profiles have right
   static function canView() {
      return plugin_printercounters_haveRight('printercounters', 'r');
   }
   
   // Printercounter's authorized profiles have right
   static function canUpdateRecords() {
      return plugin_printercounters_haveRight('update_records', '1');
   }
   
   // Printercounter's authorized profiles have right
   static function canAddLessRecords() {
      return plugin_printercounters_haveRight('add_lower_records', '1');
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
    * Function sets rand
    */
   public function setRand() {

      $this->rand = mt_rand();
   }
      
   /** 
    * getFieldName 
    * 
    * @param type $field
    * @return type
    */
   function getFieldName($field) {

      switch ($field) {
         case 'date':                  return __('Date');
         case 'entities_id':           return __('Entity');
         case 'record_type':           return __('Record type', 'printercounters');
         case 'recordmodels':          return PluginPrintercountersRecordmodel::getTypeName();
         case 'result':                return __('Result', 'printercounters');
         case 'record_id':             return __('Record ID', 'printercounters');
         case 'locations_id':          return __('Location');
         case 'counters_name':         return __('Counter type', 'printercounters');
         case 'counters_value':        return __('Counter value', 'printercounters');
         case 'budgets_id':            return __('Budget');
         case 'cost':                  return __('Cost');
         case 'application_date':      return __('Application date', 'printercounters');
         case 'last_recordmodels_id' : return __('Last record model ID');
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
         switch($item->getType()){
            case 'PluginPrintercountersRecordmodel' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry(__('Linked items', 'printercounters'), countElementsInTable($this->getTable(), "`plugin_printercounters_recordmodels_id` = '".$item->getID()."'"));
               }
               return __('Linked items', 'printercounters');
               break;
            case 'Printer' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry(PluginPrintercountersRecord::getTypeName(2));
               }
               return PluginPrintercountersRecord::getTypeName(2);
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

      switch($item->getType()){
         case 'PluginPrintercountersRecordmodel' :
            $item_recordmodel = new self('Printer' , $item->getID());
            $item_recordmodel->showForRecordmodel($item);
            break;
         case 'Printer' :
            $item_recordmodel = new self($item->getType() , $item->getID());
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
      
      if (countElementsInTable($this->getTable(), "`items_id` = '".$item->getID()."' AND `itemtype` ='".$item->getType()."'")) {   
         // Record error
         echo "<div class='center' id='error_item'>";
         $errorItem = new PluginPrintercountersErrorItem($item->getType(), $item->getID());
         $errorItem->showErrorItem();
         echo "</div>";
         
         // Show sub unit data
         $additional_data = new PluginPrintercountersAdditional_data($item->getType(), $item->getID());
         echo "<div class='center' id='additional_datas'>";
         $additional_data->showAdditionalData();
         echo "</div>";

         // Record actions
         $record = new PluginPrintercountersRecord($item->getType(), $item->getID());
         $record->showActions($this->rand);

         // Record config
         $this->showRecordConfigForItem();

         // Record history
         $search = new PluginPrintercountersSearch();
         $search->showSearch($this);
      
      // Link to a record model
      } else {
         echo "<form name='form' method='post' action='".Toolbox::getItemTypeFormURL($this->getType())."'>";
         echo "<div class='center'>";
         echo "<table border='0' class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'>";
         echo "<td class='center'>";
         echo __('Link the item to a record model', 'printercounters')."&nbsp;";
         Dropdown::show("PluginPrintercountersRecordmodel", array('name'   => 'plugin_printercounters_recordmodels_id', 
                                                                  'entity' => $item->fields['entities_id']));
         echo "<input type='hidden' name='itemtype' value='".$this->itemtype."' >";
         echo "<input type='hidden' name='items_id' value='".$this->items_id."' >";
         
         echo "&nbsp;<input type='submit' name='add' class='submit' value='"._sx('button', 'Post')."'>";
         echo "</td>";
         echo "</tr>";
         echo "</table>";
         echo '</div>';
         Html::closeForm();
      }
   }
   
  /** 
   * showForRecordmodel
   * 
   * @param type $item
   * @return boolean
   */
   function showForRecordmodel($item) {
      
      $recordmodel = new PluginPrintercountersRecordmodel();
      $canedit = ($recordmodel->can($item->fields['id'],'w') && $this->canCreate());
      
      $itemtype = $this->itemtype;
      
      if (isset($_POST["start"])) {
         $start = $_POST["start"];
      } else {
         $start = 0;
      }
      $data = $this->getItems($item->fields['id'], array('start' => $start, 'addLimit' => true));
      $rows = count($this->getItems($item->fields['id'], array('addLimit' => false)));

      if ($canedit) {
         echo "<form name='form' method='post' action='".Toolbox::getItemTypeFormURL($this->getType())."'>";

         echo "<div align='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='6'>".__('Add an item', 'printercounters')."</th></tr>";

         echo "<tr class='tab_bg_1'>";
         // Dropdown item
         echo "<td class='center'>";
         echo $itemtype::getTypeName(2).'&nbsp;';
         Dropdown::show($itemtype, array('name'        => 'items_id',  
                                         'entity'      => $item->fields['entities_id'], 
                                         'entity_sons' => true, 
                                         'condition'   => " `".getTableForItemType($itemtype)."`.`id` NOT IN (SELECT `items_id` FROM ".$this->getTable()." WHERE itemtype = '".$itemtype."')"));
         echo "</td>";
         echo "</tr>";
         
         echo "<tr>";
         echo "<td class='tab_bg_2 center' colspan='6'>";
         echo "<input type='submit' name='add' class='submit' value='"._sx('button', 'Add')."' >";
         echo "<input type='hidden' name='plugin_printercounters_recordmodels_id' value='".$item->fields['id']."' >";
         echo "<input type='hidden' name='itemtype' value='".$this->itemtype."' >";
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
   private function listItems($data, $canedit, $start, $rows){

      $rand = mt_rand();
      
      $itemtype = $this->itemtype;

      echo "<div class='center'>";
      if ($canedit) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = array();
         Html::showMassiveActions(__CLASS__, $massiveactionparams);
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

      foreach($data as $field){
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
      
      if ($canedit) {
         $paramsma['ontop'] = false;
         Html::showMassiveActions(__CLASS__, $paramsma);
         Html::closeForm(); 
      }
      echo "</table>";
      echo "</div>";
   }
   
   
  /** 
   * Get item billingmodel items
   * 
   * @global type $DB
   * @param type $recordmodels_id
   * @param type $options : - bool addLimit : add limit to the search
   *                        - int start     : start line
   *                        - int limit     : number of lines
   * 
   * @return type
   */
   function getItems($recordmodels_id=0, $options=array()){
      global $DB;
      
      $params['start']    = 0;
      $params['limit']    = $_SESSION['glpilist_limit'];
      $params['addLimit'] = true;
      
      if(!empty($options)){
         foreach($options as $key => $val){
            $params[$key] = $val;
         }
      }
            
      $output = array();
      
      $itemjoin   = getTableForItemType($this->itemtype);
      $itemjoin2  = getTableForItemType($this->itemtype.'Model');
      $itemjoin3  = getTableForItemType('State');
      $itemjoin4  = getTableForItemType($this->itemtype.'Type');
      $itemjoin5  = getTableForItemType('Location');
      $itemjoin6  = getTableForItemType('Entity');
      $itemjoin7  = getTableForItemType('PluginPrintercountersItem_Recordmodel');
      $itemjoin8  = getTableForItemType('PluginPrintercountersRecord');
      
      $query = "SELECT `".$itemjoin."`.`name` as items_name,
                       `".$itemjoin."`.`id` as items_id, 
                       `".$itemjoin6."`.`name` as entities_name,
                       `".$itemjoin3."`.`name` as states_name,
                       `".$itemjoin4."`.`name` as printertypes_name,
                       `".$itemjoin2."`.`name` as models_name,
                       `".$itemjoin5."`.`completename` as locations_name,
                       `".$this->getTable()."`.`id`,
                        `".$itemjoin7."`.`plugin_printercounters_recordmodels_id` as recordmodels_id,
                       `glpi_plugin_printercounters_records`.`date` as last_record_date,
                       `glpi_plugin_printercounters_records`.`record_type` as last_record_type
          FROM ".$this->getTable()."
          LEFT JOIN `".$itemjoin."` 
             ON (`".$this->getTable()."`.`items_id` = `".$itemjoin."`.`id` 
                  AND LOWER(`".$itemjoin7."`.`itemtype`) = LOWER('".$this->itemtype."'))
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
          LEFT JOIN `$itemjoin8`
             ON (`$itemjoin7`.`id` = `$itemjoin8`.`plugin_printercounters_items_recordmodels_id`
                  AND `$itemjoin8`.`date` = (
                       SELECT max(`$itemjoin8`.`date`) 
                       FROM $itemjoin8 
                       WHERE `$itemjoin8`.`plugin_printercounters_items_recordmodels_id` = `$itemjoin7`.`id`
                     )
                ) 
          WHERE 1";
      
      if ($recordmodels_id) {
         $query .= " AND`".$this->getTable()."`.`plugin_printercounters_recordmodels_id` = ".$recordmodels_id;
      }
      
      $query .= " GROUP BY `".$itemjoin."`.`id`  
          ORDER BY `".$itemjoin."`.`name` ASC";
      
      if($params['addLimit']){
         $query .= " LIMIT ".intval($params['start']).",".intval($params['limit']);
      }

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $output[$data['id']] = $data;
         }
      }
      
      return $output;
   }
   
   /** 
    *  Function get item recordmodel for an item
    * 
    * @global type $DB
    * @return type
    */
   function getItem_RecordmodelForItem(){
      global $DB;
      
      $output = array();
      
      $itemjoin = getTableForItemType('PluginPrintercountersRecordmodel');
      $itemjoin2 = getTableForItemType($this->itemtype);
      
      $query = "SELECT `".$this->getTable()."`.`id`, 
                       `".$this->getTable()."`.`plugin_printercounters_snmpauthentications_id`,
                       `".$this->getTable()."`.`enable_automatic_record`,
                       `".$this->getTable()."`.`periodicity`,
                       `".$this->getTable()."`.`max_timeout`,
                       `".$this->getTable()."`.`nb_retries`,
                       `".$itemjoin."`.`id` as plugin_printercounters_recordmodels_id,
                       `".$itemjoin."`.`name` as recordmodels_name,
                       `".$itemjoin."`.`entities_id` as recordmodels_entity,
                       `".$itemjoin."`.`is_recursive` as recordmodels_recursivity,
                       `".$itemjoin."`.`mac_address_conformity`,
                       `".$itemjoin."`.`sysdescr_conformity`,
                       `".$itemjoin."`.`serial_conformity`,
                       `".$itemjoin2."`.`entities_id`,
                       `".$itemjoin2."`.`locations_id`   
          FROM ".$this->getTable()."
          LEFT JOIN `".$itemjoin."` 
             ON(`".$this->getTable()."`.`plugin_printercounters_recordmodels_id` = `".$itemjoin."`.`id`)
          LEFT JOIN `".$itemjoin2."` 
             ON(`".$this->getTable()."`.`items_id` = `".$itemjoin2."`.`id`)
          WHERE `items_id` = ".$this->items_id. " AND `itemtype`='".$this->itemtype."';";

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetch_assoc($result)) {
            $output[$data['id']] = $data;
         }
      }
      
      return $output;
   }
   
   /** 
    * Function show record configuration for an item
    * 
    */
   function showRecordConfigForItem(){
            
      if (!$this->canCreate()) return false;
      
      $data = $this->getItem_RecordmodelForItem($this->items_id, $this->itemtype);
      $data = reset($data);

      if(!empty($data)){
         echo "<form name='form' method='post' action='".
               Toolbox::getItemTypeFormURL($this->getType())."'>";
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='8'>".__('Record configuration', 'printercounters')."</th></tr>";
         echo "<tr class='tab_bg_1'>";
         
         // Record model link
         echo "<td>".PluginPrintercountersRecordmodel::getTypeName(1)."</td>";
         echo "<td>";
         Dropdown::show('PluginPrintercountersRecordmodel', array('name'      => 'plugin_printercounters_recordmodels_id', 
                                                                  'value'     => $data['plugin_printercounters_recordmodels_id'], 
                                                                  'entity'    => $data['entities_id'],
                                                                  'on_change' => "printercounters_setConfirmation(\"".__('Are you sure to change the recordmodel ?', 'printercounters')."\", ".$data['plugin_printercounters_recordmodels_id'].", this.value, \"printercounters_recordConfig\", \"update_config\");"));
         
         echo "</td>";
         
         // SNMP authentication
         echo "<td>".PluginPrintercountersSnmpauthentication::getTypeName()."</td>";
         echo "<td>";
         Dropdown::show('PluginPrintercountersSnmpauthentication', array('value' => $data['plugin_printercounters_snmpauthentications_id']));
         echo "</td>";
         
         // Enable record
         echo "<td>".__('Enable automatic record', 'printercounters')."</td>";
         echo "<td>";
         Dropdown::showYesNo('enable_automatic_record', $data['enable_automatic_record']);
         echo "</td>";
         echo "</tr>";
         echo "<tr class='tab_bg_1'>";
         
         // Periodicity
         echo "<td>".__('Periodicity of automatic record', 'printercounters')."</td>";
         echo "<td>";
         Dropdown::showTimeStamp("periodicity", array('min'             => DAY_TIMESTAMP,
                                                      'max'             => 15*DAY_TIMESTAMP,
                                                      'step'            => DAY_TIMESTAMP,
                                                      'value'           => $data['periodicity'],
                                                      'addfirstminutes' => false,
                                                      'inhours'         => false));
         echo "</td>";
         
         // Retries
         echo "<td>".__('Number of retries', 'printercounters')."</td>";
         echo "<td>";
         Dropdown::showInteger('nb_retries', $data["nb_retries"], 0, 10);
         echo "</td>";
         
         // Timeout
         echo "<td>".__('Timeout', 'printercounters')."</td>";
         echo "<td>";
         Dropdown::showNumber("max_timeout",  array('min'   => 0,
                                                    'max'   => 60,
                                                    'value' => $data["max_timeout"],
                                                    'unit'  => 'second'));
         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         echo "<td class='tab_bg_2 center' colspan='6'>";
         echo "<input type='submit' name='update_config' class='submit' value='"._sx('button', 'Update')."'>";
         echo "<input type='hidden' name='_clean_records' value='1'>";
         echo "<input type='hidden' name='items_id' value='".$this->items_id."'>";
         echo "<input type='hidden' name='itemtype' value='".$this->itemtype."'>";
         echo "<input type='hidden' name='id' value='".$data['id']."'>";
         echo "</td>";
         echo "</tr>";
         echo "</table></div>";
         Html::closeForm();
      }
   }
   
  /** 
   * Get search options
   * 
   * @return array
   */
   function getSearchOptions() {

      $itemtype = $this->itemtype;
      $item = getItemForItemtype($itemtype);
      
      $tab[79]['table']          = 'glpi_plugin_printercounters_records';
      $tab[79]['field']          = 'id';
      $tab[79]['name']           = $this->getFieldName('record_id');
      $tab[79]['datatype']       = 'numeric';
      $tab[79]['massiveaction']  = false;
      $tab[79]['nosearch']       = true; 
      $tab[79]['nodisplay']      = true;
      $tab[79]['joinparams']     = array('jointype'   => 'child');
      $tab[79]['nosort']         = true;
      
      $tab[80]['table']          = 'glpi_plugin_printercounters_records';
      $tab[80]['field']          = 'date';
      $tab[80]['name']           = $this->getFieldName('date');
      $tab[80]['datatype']       = 'datetime';
      $tab[80]['massiveaction']  = false;
      $tab[80]['joinparams']     = array('jointype'   => 'child'); 
      $tab[80]['nosort']         = true;
      
      $tab[81]['table']          = 'glpi_plugin_printercounters_recordmodels';
      $tab[81]['field']          = 'name';
      $tab[81]['linkfield']      = 'last_recordmodels_id';
      $tab[81]['name']           = $this->getFieldName('recordmodels');
      $tab[81]['datatype']       = 'dropdown';
      $tab[81]['massiveaction']  = false;
      $tab[81]['joinparams']     = array('beforejoin'
                                          => array('table' => 'glpi_plugin_printercounters_records')
                                        );
      $tab[81]['nosort']         = true;

      
      $tab[82]['table']          = 'glpi_entities';
      $tab[82]['field']          = 'name';
      $tab[82]['name']           = $this->getFieldName('entities_id');
      $tab[82]['massiveaction']  = false;
      $tab[82]['datatype']       = 'dropdown';
      $tab[82]['joinparams']     = array('beforejoin'
                                          => array('table' => 'glpi_plugin_printercounters_records')
                                        );
      $tab[82]['nosort']         = true;
      
      $tab[83]['table']          = 'glpi_plugin_printercounters_countertypes';
      $tab[83]['field']          = 'name';
      $tab[83]['name']           = $this->getFieldName('counters_name');
      $tab[83]['datatype']       = 'dropdown';
      $tab[83]['massiveaction']  = false;
      $tab[83]['nosearch']       = true;
      $tab[83]['joinparams']     = array('beforejoin'
                                          => array('table'      => 'glpi_plugin_printercounters_countertypes_recordmodels',
                                                   'joinparams' => array('beforejoin' => array('table'      => 'glpi_plugin_printercounters_counters', 
                                                                                               'joinparams' => array('jointype'   => 'child', 
                                                                                                                     'beforejoin' => array('table' => 'glpi_plugin_printercounters_records')))))
                                        );
      $tab[83]['nosort']         = true;
            
      $tab[84]['table']          = 'glpi_plugin_printercounters_counters';
      $tab[84]['field']          = 'value';
      $tab[84]['name']           = $this->getFieldName('counters_value');
      $tab[84]['datatype']       = 'dropdown';
      $tab[84]['massiveaction']  = false;
      $tab[84]['joinparams']     = array('jointype'   => 'child');  
      $tab[84]['nosearch']       = true;
      $tab[84]['joinparams']     = array('beforejoin'
                                          => array('table'      => 'glpi_plugin_printercounters_records',
                                                   'joinparams' => array('jointype'   => 'child'))
                                        );
      $tab[84]['nosort']         = true;
      
      $tab[85]['table']               = 'glpi_plugin_printercounters_records';
      $tab[85]['field']               = 'record_type';
      $tab[85]['name']                = $this->getFieldName('record_type');
      $tab[85]['datatype']            = 'specific';
      $tab[85]['searchequalsonfield'] = true;
      $tab[85]['searchtype']          = 'equals';
      $tab[85]['massiveaction']       = false;
      $tab[85]['joinparams']          = array('jointype'   => 'child'); 
      $tab[85]['nosort']              = true;
      
      $tab[86]['table']               = 'glpi_plugin_printercounters_records';
      $tab[86]['field']               = 'result';
      $tab[86]['name']                = $this->getFieldName('result');
      $tab[86]['datatype']            = 'specific';
      $tab[86]['searchequalsonfield'] = true;
      $tab[86]['searchtype']          = 'equals';
      $tab[86]['massiveaction']       = false;
      $tab[86]['joinparams']          = array('beforejoin'
                                                => array('table'      => 'glpi_plugin_printercounters_counters',
                                                         'joinparams' => array('jointype'   => 'child'))
                                              );
      $tab[86]['nosort']              = true;
      
      $tab[87]['table']          = 'glpi_locations';
      $tab[87]['field']          = 'completename';
      $tab[87]['name']           = $this->getFieldName('locations_id');
      $tab[87]['datatype']       = 'dropdown';
      $tab[87]['massiveaction']  = false;
      $tab[87]['joinparams']     = array('beforejoin'
                                          => array('table' => 'glpi_plugin_printercounters_records')
                                        );
      $tab[87]['nosort']         = true;
      
      $tab[88]['table']          = 'glpi_plugin_printercounters_budgets';
      $tab[88]['field']          = 'name';
      $tab[88]['name']           = $this->getFieldName('budgets_id');
      $tab[88]['massiveaction']  = false;
      $tab[88]['datatype']       = 'itemlink';
      $tab[88]['nosort']         = true;      
            
      $tab[89]['table']          = getTableForItemType($itemtype);
      $tab[89]['field']          = 'name';
      $tab[89]['name']           = $item::getTypeName();
      $tab[89]['datatype']       = 'dropdown';
      $tab[89]['massiveaction']  = false;
      $tab[89]['linkfield']      = 'items_id';
      $tab[89]['nosearch']       = true;
      $tab[89]['nodisplay']      = true;
      $tab[89]['nosort']         = true;
      
      $tab[90]['table']          = getTableForItemType($itemtype);
      $tab[90]['field']          = 'id';
      $tab[90]['name']           = $item::getTypeName().' ID';
      $tab[90]['datatype']       = 'number';
      $tab[90]['massiveaction']  = false;
      $tab[90]['linkfield']      = 'items_id';
      $tab[90]['nosearch']       = true;
      $tab[90]['nodisplay']      = true;
      $tab[90]['nosort']         = true;
       
      $tab[91]['table']          = getTableForItemType($this->itemtype.'Model');
      $tab[91]['field']          = 'name';
      $tab[91]['name']           = __('Model');
      $tab[91]['massiveaction']  = false;
      $tab[91]['datatype']       = 'dropdown';
      $tab[91]['nosearch']       = true; 
      $tab[91]['nodisplay']      = true;
      $tab[91]['joinparams']     = array('beforejoin'
                                          => array('table'     => 'glpi_printers', 
                                                   'linkfield' => 'items_id')
                                        );
      $tab[91]['nosort']         = true;
      
      $tab[92]['table']          = 'glpi_plugin_printercounters_records';
      $tab[92]['field']          = 'last_recordmodels_id';
      $tab[92]['name']           = $this->getFieldName('last_recordmodels_id');
      $tab[92]['datatype']       = 'number';
      $tab[92]['massiveaction']  = false;
      $tab[92]['nosearch']       = true;
      $tab[92]['nodisplay']      = true;
      $tab[92]['joinparams']     = array('jointype'   => 'child');
      $tab[92]['nosort']         = true;
      
      $tab[93]['table']          = 'glpi_plugin_printercounters_countertypes';
      $tab[93]['field']          = 'id';
      $tab[93]['name']           = $this->getFieldName('counters_name');
      $tab[93]['datatype']       = 'number';
      $tab[93]['massiveaction']  = false;
      $tab[93]['nosearch']       = true;
      $tab[93]['nodisplay']      = true;
      $tab[93]['joinparams']     = array('beforejoin'
                                          => array('table'      => 'glpi_plugin_printercounters_countertypes_recordmodels',
                                                   'joinparams' => array('beforejoin' => array('table'      => 'glpi_plugin_printercounters_counters', 
                                                                                               'joinparams' => array('jointype'   => 'child', 
                                                                                                                     'beforejoin' => array('table' => 'glpi_plugin_printercounters_records')))))
                                        );
      $tab[93]['nosort']         = true;
      
      $tab[94]['table']          = $this->getTable();
      $tab[94]['field']          = 'itemtype';
      $tab[94]['name']           = 'itemtype';
      $tab[94]['massiveaction']  = false;
      $tab[94]['nosearch']       = true;
      $tab[94]['nodisplay']      = true;
      $tab[94]['nosql']          = true;
      $tab[94]['nosort']         = true;
      
      $tab[95]['table']          = 'glpi_plugin_printercounters_recordmodels';
      $tab[95]['field']          = 'name';
      $tab[95]['name']           = $this->getFieldName('recordmodels');
      $tab[95]['datatype']       = 'dropdown';
      $tab[95]['massiveaction']  = false;
      $tab[95]['nosearch']       = true;
      $tab[95]['nodisplay']      = true;
      $tab[95]['nosql']          = true;
      $tab[95]['nosort']         = true;
      
      $tab[97]['table']          = 'glpi_plugin_printercounters_pagecosts';
      $tab[97]['field']          = 'cost';
      $tab[97]['name']           = $this->getFieldName('cost');
      $tab[97]['datatype']       = 'specific';
      $tab[97]['massiveaction']  = false;
      $tab[97]['nosearch']       = true;
      $tab[97]['nosql']          = true;
      $tab[97]['nosort']         = true;
      
      $tab[98]['table']           = $this->getTable();
      $tab[98]['field']           = 'id';
      $tab[98]['name']            = __('ID');
      $tab[98]['datatype']        = 'number';
      $tab[98]['massiveaction']   = false;
      $tab[98]['nosearch']        = true;
      $tab[98]['nodisplay']       = true;
      $tab[98]['nosort']         = true;
      
      $tab[99]['table']          = 'glpi_entities';
      $tab[99]['field']          = 'id';
      $tab[99]['name']           = $this->getFieldName('entities_id');
      $tab[99]['massiveaction']  = false;
      $tab[99]['nosearch']       = true;
      $tab[99]['nodisplay']      = true;
      $tab[99]['datatype']       = 'dropdown';
      $tab[99]['joinparams']     = array('beforejoin'
                                          => array('table' => 'glpi_plugin_printercounters_records')
                                        );
      $tab[99]['nosort']         = true;

      return $tab;
   }
   
  /** 
   * Add search options for an item
   * 
   * @return array
   */
   function getAddSearchOptions(){
      
      $tab[6091]['table']          = 'glpi_plugin_printercounters_records';
      $tab[6091]['field']          = 'date';
      $tab[6091]['name']           = __('Printercounters', 'printercounters').' - '.__('Last record date', 'printercounters');
      $tab[6091]['datatype']       = 'datetime';
      $tab[6091]['forcegroupby']   = true;
      $tab[6091]['massiveaction']  = false;
      $tab[6091]['joinparams']     = array('jointype'   => 'child',
                                           'condition'  => "AND NEWTABLE.`date` = (SELECT max(`glpi_plugin_printercounters_records`.`date`) 
                                                                                   FROM glpi_plugin_printercounters_records 
                                                                                   WHERE `glpi_plugin_printercounters_records`.`plugin_printercounters_items_recordmodels_id` = REFTABLE.`id`) ",
                                           'beforejoin'
                                            => array('table'      => 'glpi_plugin_printercounters_items_recordmodels',
                                                     'joinparams' => array('jointype'   => 'itemtype_item'))
                                          );
      
      $tab[6092]['table']          = 'glpi_plugin_printercounters_records';
      $tab[6092]['field']          = 'record_type';
      $tab[6092]['name']           = __('Printercounters', 'printercounters').' - '.__('Last record type', 'printercounters');
      $tab[6092]['datatype']       = 'specific';
      $tab[6092]['forcegroupby']   = true;
      $tab[6092]['searchtype']     = 'equals';
      $tab[6092]['searchequalsonfield'] = true;
      $tab[6092]['massiveaction']  = false;
      $tab[6092]['joinparams']     = array('jointype'   => 'child',
                                           'condition'  => "AND NEWTABLE.`date` = (SELECT max(`glpi_plugin_printercounters_records`.`date`) 
                                                                                   FROM glpi_plugin_printercounters_records 
                                                                                   WHERE `glpi_plugin_printercounters_records`.`plugin_printercounters_items_recordmodels_id` = REFTABLE.`id`) ",
                                           'beforejoin'
                                            => array('table'      => 'glpi_plugin_printercounters_items_recordmodels',
                                                     'joinparams' => array('jointype'   => 'itemtype_item'))
                                          );
      
      $tab[6093]['table']          = 'glpi_plugin_printercounters_items_recordmodels';
      $tab[6093]['field']          = 'enable_automatic_record';
      $tab[6093]['name']           = __('Printercounters', 'printercounters').' - '.__('Enable automatic record', 'printercounters');
      $tab[6093]['massiveaction']  = false;
      $tab[6093]['datatype']       = 'bool';
      $tab[6093]['joinparams']     = array('jointype'   => 'itemtype_item', 
                                          'beforejoin' => array('table' => getTableForItemType($this->itemtype))
                                   );
      
      $tab[6094]['table']          = 'glpi_plugin_printercounters_recordmodels';
      $tab[6094]['field']          = 'name';
      $tab[6094]['name']           = __('Printercounters', 'printercounters').' - '.PluginPrintercountersRecordmodel::getTypeName(1);
      $tab[6094]['datatype']       = 'dropdown';
      $tab[6094]['massiveaction']  = false;
      $tab[6094]['joinparams']     = array('beforejoin' => array('table'      => 'glpi_plugin_printercounters_items_recordmodels',
                                                                 'joinparams' => array('jointype'   => 'itemtype_item', 
                                                                                       'beforejoin' => array('table' => getTableForItemType($this->itemtype))))
                                          );
      
      $tab[6095]['table']          = 'glpi_plugin_printercounters_items_recordmodels';
      $tab[6095]['field']          = 'periodicity';
      $tab[6095]['name']           = __('Printercounters', 'printercounters').' - '.__('Periodicity of automatic record', 'printercounters');
      $tab[6095]['datatype']       = 'timestamp';
      $tab[6095]['searchtype']     = 'equals';
      $tab[6095]['massiveaction']  = false;
      
      $tab[6096]['table']          = 'glpi_plugin_printercounters_snmpauthentications';
      $tab[6096]['field']          = 'name';
      $tab[6096]['name']           = __('Printercounters', 'printercounters').' - '.PluginPrintercountersSnmpauthentication::getTypeName(1);
      $tab[6096]['datatype']       = 'dropdown';
      $tab[6096]['massiveaction']  = false;
      $tab[6096]['joinparams']     = array('beforejoin' => array('table'      => 'glpi_plugin_printercounters_items_recordmodels',
                                                                 'joinparams' => array('jointype'   => 'itemtype_item', 
                                                                                       'beforejoin' => array('table' => getTableForItemType($this->itemtype))))
                                          );
      
      $tab[6097]['table']          = 'glpi_plugin_printercounters_items_recordmodels';
      $tab[6097]['field']          = 'nb_retries';
      $tab[6097]['name']           = __('Printercounters', 'printercounters').' - '.__('Number of retries', 'printercounters');
      $tab[6097]['massiveaction']  = false;
      $tab[6097]['searchtype']     = 'equals';
      $tab[6097]['datatype']       = 'number';
      $tab[6097]['joinparams']     = array('jointype'   => 'itemtype_item', 
                                           'beforejoin' => array('table' => getTableForItemType($this->itemtype))
                                    );
      
      $tab[6098]['table']          = $this->getTable();
      $tab[6098]['field']          = 'global_tco';
      $tab[6098]['name']           = __('Printercounters', 'printercounters').' - '.__('Global TCO', 'printercounters');
      $tab[6098]['massiveaction']  = false;
      $tab[6098]['nosearch']       = true;
      $tab[6098]['datatype']       = 'decimal';
      $tab[6098]['joinparams']     = array('jointype'   => 'itemtype_item', 
                                     'beforejoin' => array('table' => getTableForItemType($this->itemtype))
                              );

      return $tab;
   }
  
   /**
    * Massive actions to be added
    * 
    * @param $input array of input datas
    *
    * @return array of results (nbok, nbko, nbnoright counts)
    **/
   function massiveActions(){
            
      switch (strtolower($this->itemtype)) {
         case "printer":
            $output = array();
            if ($this->canCreate()) {
               $output = array (
                  "plugin_printercounters_automatic_record"        => __('Printercounters','printercounters').' - '.__('Enable automatic record', 'printercounters'),
                  "plugin_printercounters_periodicity"             => __('Printercounters','printercounters').' - '.__('Set periodicity', 'printercounters'),
                  "plugin_printercounters_recordmodel"             => __('Printercounters','printercounters').' - '.__('Set record model', 'printercounters'),
                  "plugin_printercounters_snmp_auth"               => __('Printercounters','printercounters').' - '.__('Set SNMP authentication', 'printercounters'),
                  "plugin_printercounters_retries"                 => __('Printercounters','printercounters').' - '.__('Set retries', 'printercounters'),
                  "plugin_printercounters_max_timeout"             => __('Printercounters','printercounters').' - '.__('Set max timeout', 'printercounters'),
                  "plugin_printercounters_immediate_record"        => __('Printercounters','printercounters').' - '.__('Immediate record', 'printercounters'),
                  "plugin_printercounters_update_counter_position" => __('Printercounters','printercounters').' - '.__('Update counter position', 'printercounters'),
                  "plugin_printercounters_init_counters"           => __('Printercounters','printercounters').' - '.__('Init counters to zero', 'printercounters'),
                  "plugin_printercounters_snmp_set"                => __('Printercounters','printercounters').' - '.__('Set printer values', 'printercounters')
               );
            }

            if ($this->canView() || $this->canCreate()) {
               $output["plugin_printercounters_immediate_record"] = __('Printercounters','printercounters').' - '.__('Immediate record', 'printercounters');
            }
            return $output;
      }
   }
   
   /**
    * Massive actions display
    * 
    * @param $input array of input datas
    *
    * @return array of results (nbok, nbko, nbnoright counts)
    **/
   function massiveActionsDisplay($options=array()){

      switch (strtolower($this->itemtype)) {
         case 'printer':
            switch ($options['action']) {
               case "plugin_printercounters_automatic_record":
                  if ($this->canCreate()){
                     Dropdown::showYesNo('enable_automatic_record');
                     echo "</br></br><input type=\"submit\" name=\"massiveaction\" 
                           class=\"submit\" value=\""._sx('button', 'Post')."\" >";
                  }
                  break;

               case "plugin_printercounters_periodicity":
                  if ($this->canCreate()){
                     Dropdown::showTimeStamp("periodicity", array('min'             => DAY_TIMESTAMP,
                                                                  'max'             => 15*DAY_TIMESTAMP,
                                                                  'step'            => DAY_TIMESTAMP,
                                                                  'addfirstminutes' => false,
                                                                  'inhours'         => false));
                     echo "</br></br><input type=\"submit\" name=\"massiveaction\" 
                           class=\"submit\" value=\""._sx('button', 'Post')."\" >";
                  }
                  break;

               case "plugin_printercounters_retries":
                  if ($this->canCreate()){
                     Dropdown::showInteger('nb_retries', '', 0, 10);
                     echo "</br></br><input type=\"submit\" name=\"massiveaction\" 
                           class=\"submit\" value=\""._sx('button', 'Post')."\" >";
                  }
                  break;

               case "plugin_printercounters_max_timeout":
                  if ($this->canCreate()){
                     Dropdown::showNumber("max_timeout",  array('min'   => 0,
                                                                'max'   => 60,
                                                                'unit'  => 'second'));
                     echo "</br></br><input type=\"submit\" name=\"massiveaction\" 
                           class=\"submit\" value=\""._sx('button', 'Post')."\" >";
                  }
                  break;

               case "plugin_printercounters_recordmodel":
                  if ($this->canCreate()){
                     Dropdown::show("PluginPrintercountersRecordmodel", array('name' => 'plugin_printercounters_recordmodels_id'));
                     echo "</br></br><input type=\"submit\" name=\"massiveaction\" 
                           class=\"submit\" value=\""._sx('button', 'Post')."\" >";
                  }
                  break;

               case "plugin_printercounters_snmp_auth":
                  if ($this->canCreate()){
                     Dropdown::show("PluginPrintercountersSnmpauthentication", array('name' => 'plugin_printercounters_snmpauthentications_id'));
                     echo "</br></br><input type=\"submit\" name=\"massiveaction\" 
                           class=\"submit\" value=\""._sx('button', 'Post')."\" >";
                  }
                  break;

               case "plugin_printercounters_immediate_record":
                  if ($this->canView() || $this->canCreate()){
                     echo "</br></br><input type=\"submit\" name=\"massiveaction\" 
                           class=\"submit\" value=\"".__('Immediate record', 'printercounters')."\" >";
                  }
                  break;

                case "plugin_printercounters_update_counter_position":
                  if ($this->canCreate()){
                     echo "</br></br><input type=\"submit\" name=\"massiveaction\" 
                           class=\"submit\" value=\"".__('Update counter position', 'printercounters')."\" >";
                  }
                  break;
                  
                case "plugin_printercounters_init_counters":
                  if ($this->canCreate()){
                     echo "</br></br><input type=\"submit\" name=\"massiveaction\" 
                           class=\"submit\" value=\"".__('Init counters to zero', 'printercounters')."\" >";
                  }
                  break;
                
               case "plugin_printercounters_snmp_set":
                  if ($this->canCreate()){
                     echo "</br></br><input type=\"submit\" name=\"massiveaction\" 
                           class=\"submit\" value=\"".__('Set printer values', 'printercounters')."\" >";
                  }
                  break;
            }
            break;
      }
   }
   
   
   /**
    *  Massive actions process
    * 
    * @param $input array of input datas
    **/
   function massiveActionsProcess($input = array()) {
      global $CFG_GLPI;
  
      $res = array('ok'      => 0,
                   'ko'      => 0,
                   'noright' => 0);

      if ($this->canCreate()) {
         if (count($input['item']) > 0) {
            $_SESSION['glpi_massiveaction']['POST'] = $input;
            Html::redirect($CFG_GLPI['root_doc'].'/plugins/printercounters/ajax/record.php?action=initAjaxMassiveAction');
         } else {
            $res['ko'] = 1;
         }
      } else {
         $res['noright'] = 1;
      }

      return $res;
   }
   
   /**
    * Init massive actions process
    * 
    **/
   function initMassiveActionsProcess() {
      global $CFG_GLPI;
      
      $input = $_SESSION['glpi_massiveaction']['POST'];
      
      $input["process_count"] = 0;
      $input['res'] = array('ok'       => 0,
                            'ko'       => 0,
                            'noright'  => 0);

      $items_id = array();
      foreach ($input["item"] as $key => $val) {
         if ($val == 1) {
            $items_id[] = $key;
         }
      }

      $data = $this->find("`items_id` IN ('".implode("','", $items_id)."') AND LOWER(`itemtype`)=LOWER('".$this->itemtype."')"); 
      $item_data = array();
      foreach ($data as $key => $val) {
         $item_data[$val['items_id']] = $val;
      }

      foreach ($input['item'] as $key => $val) {
         if ($val) {
            $input["process_count"]++;
         }
      }

      $input['item_data'] = $item_data;
      $_SESSION['glpi_massiveaction']['POST'] = $input;

      // Init progress bar
      $actionTitles = $this->massiveActions();
      echo "<table class='tab_cadrehov'>";
      echo "<tr class='tab_bg_1'><th>".$actionTitles[$input['action']]."</th></tr>";
      echo "<tr class='tab_bg_1'><td><br/>";
      Html::createProgressBar(__('Work in progress...'));
      echo"</td></tr>";
      echo "</table>";

      // Launch ajax massive action
      echo "<script type='text/javascript'>";
      echo "printecounters_ajaxMassiveAction('".$CFG_GLPI['root_doc']."', 'ajaxMassiveAction', ".ini_get('max_execution_time').");";
      echo "</script>";
   }
   
   /**
    * Do massive actions treatments
    * 
    * @return array of results (nbok, nbko, nbnoright counts)
    **/
   function doMassiveActionProcess(){
      global $CFG_GLPI;
      
      $item = getItemForItemtype($this->itemtype);
      
      $input = $_SESSION['glpi_massiveaction']['POST'];
      $item_data = $_SESSION['glpi_massiveaction']['POST']['item_data'];
      
      if ($count = count($input['item'])) {
         $i = $input["process_count"]-$count+1;
         $key = key($input["item"]);
         unset($input["item"][$key]);
         
//         if ($item->can($key, 'w')) {
            $result = false;
            switch ($input['action']) {
               case "plugin_printercounters_automatic_record":
                  if (isset($item_data[$key])) {
                     $result = $this->update(array('id' => $item_data[$key]['id'], 'enable_automatic_record' => $input['enable_automatic_record']));
                  }
                  break;

               case "plugin_printercounters_periodicity":
                  if (isset($item_data[$key])) {
                     $result = $this->update(array('id' => $item_data[$key]['id'], 'periodicity' => $input['periodicity']));
                  }
                  break;

               case "plugin_printercounters_retries":
                  if (isset($item_data[$key])) {
                     $result = $this->update(array('id' => $item_data[$key]['id'], 'nb_retries' => $input['nb_retries']));
                  }
                  break;

               case "plugin_printercounters_max_timeout":
                  if (isset($item_data[$key])) {
                     $result = $this->update(array('id' => $item_data[$key]['id'], 'max_timeout' => $input['max_timeout']));
                  }
                  break;

               case "plugin_printercounters_recordmodel":
                  if (isset($item_data[$key])) {
                     $result = $this->update(array('id' => $item_data[$key]['id'], 'plugin_printercounters_recordmodels_id' => $input['plugin_printercounters_recordmodels_id'], '_clean_records' => 1));
                  } else {
                     $result = $this->add(array('plugin_printercounters_recordmodels_id' => $input['plugin_printercounters_recordmodels_id'],
                                                'items_id'                               => $key,
                                                'itemtype'                               => $this->itemtype));
                  }
                  break;

               case "plugin_printercounters_snmp_auth":
                  if (isset($item_data[$key])) {
                     $result = $this->update(array('id' => $item_data[$key]['id'], 'plugin_printercounters_snmpauthentications_id' => $input['plugin_printercounters_snmpauthentications_id']));
                  }
                  break;

               case "plugin_printercounters_immediate_record":
                  $record = new PluginPrintercountersRecord();
                  list($messages, $error) = $record->immediateRecord($key, $this->itemtype);
                  $result = true;
                  if ($error) {
                     $result = false;
                  }
                  break;

               case "plugin_printercounters_update_counter_position":
                  $record = new PluginPrintercountersRecord();
                  list($messages, $error) = $record->updateCounterPosition($key, $this->itemtype);
                  $result = true;
                  if ($error) {
                     $result = false;
                  }
                  break;
                  
               case "plugin_printercounters_init_counters":
                  $record = new PluginPrintercountersRecord($this->itemtype, $key);
                  $result = false;
                  if ($record->setFirstRecord($item_data[$key]['id'], $item_data[$key]['plugin_printercounters_recordmodels_id'])) {
                     $result = true;
                  }
                  break;
                  
               case "plugin_printercounters_snmp_set":
                  $snmpset = new PluginPrintercountersSnmpset();
                  list($messages, $error) = $snmpset->snmpSet($item_data[$key]['items_id'], $item_data[$key]['itemtype']);
                  $result = true;
                  if ($error) {
                     $result = false;
                  }
                  break;

               default :
                  return parent::doSpecificMassiveActions($input);
            }

            if ($result) {
               $input['res']['ok']++;
            } else {
               $input['res']['ko']++;
            }
//         } else {
//            $input['res']['noright']++;
//         }
      }
         
      if (count($input["item"])) {
         // more to do -> redirect
         $_SESSION['glpi_massiveaction']['POST'] = $input;
         Html::changeProgressBarPosition($i, $input["process_count"], sprintf(__('%1$s/%2$s'), $i, $input["process_count"]));

         echo "<script type='text/javascript'>";
         echo "printecounters_ajaxMassiveAction('".$CFG_GLPI['root_doc']."', 'ajaxMassiveAction', ".ini_get('max_execution_time').");";
         echo "</script>";

      } else { // Nothing to do redirect
         $nbok      = 0;
         $nbnoright = 0;
         $nbko      = 0;

         if (is_array($input['res'])
               && isset($input['res']['ok'])
               && isset($input['res']['ko'])
               && isset($input['res']['noright'])) {
            
            $nbok      = $input['res']['ok'];
            $nbko      = $input['res']['ko'];
            $nbnoright = $input['res']['noright'];
            
         } else {
            if ($input['res']) {
               $nbok++;
            } else {
               $nbko++;
            }
         }

         // Default message : all ok
         $message = __('Operation successful');
         // All failed. operations failed
         if ($nbok == 0) {
            $message = __('Failed operation');
            if ($nbnoright) {
               //TRANS: %$1d and %$2d are numbers
               $message .= "<br>".sprintf(__('(%1$d authorizations problems, %2$d failures)'),
                                           $nbnoright, $nbko);
            }
         } else if ($nbnoright || $nbko) {
            // Partial success
            $message = __('Operation performed partially successful');
            $message .= "<br>".sprintf(__('(%1$d authorizations problems, %2$d failures)'),
                                       $nbnoright, $nbko);
         }

         unset($_SESSION['glpi_massiveaction']['POST']);

         Html::changeProgressBarPosition(100, 100, $message);
         Session::addMessageAfterRedirect($message);
         Html::redirect($CFG_GLPI['root_doc'].'/front/printer.php');
      }
   }
   
  /**
   * Handle massive action timeout
   * 
   */
   function massiveActionTimeOut(){
         
      $input = $_SESSION['glpi_massiveaction']['POST'];
      
      if ($count = count($input['item'])) {
         $i = $input["process_count"]-$count+1;
         $key = key($input["item"]);
         unset($input["item"][$key]);
         $_SESSION['glpi_massiveaction']['POST'] = $input;
         Html::changeProgressBarPosition($i, $input["process_count"], sprintf(__('%1$s/%2$s'), $i, $input["process_count"]));
         $this->doMassiveActionProcess();
      }
   }
   
//    /**
//    * Massive actions process
//    * 
//    * @param $input array of input datas
//    *
//    * @return array of results (nbok, nbko, nbnoright counts)
//    **/
//   function massiveActionsProcess($input = array()) {
//
//      $item = getItemForItemtype($this->itemtype);
//
//      $res = array('ok'      => 0,
//                   'ko'      => 0,
//                   'noright' => 0);
//      
////      if ($this->canCreate()) {
//         $items_id = array();
//         foreach ($input["item"] as $key => $val) {
//            if ($val == 1) {
//               $items_id[] = $key;
//            }
//         }
//
//         $data = $this->find("`items_id` IN ('".implode("','", $items_id)."') AND LOWER(`itemtype`)=LOWER('".$this->itemtype."')"); 
//         $item_data = array();
//         foreach ($data as $key => $val) {
//            $item_data[$val['items_id']] = $val;
//         }
//
//         foreach ($input["item"] as $key => $val) {
////            if ($item->can($key, 'w')) {
//               $result = false;
//               switch ($input['action']) {
//                  case "plugin_printercounters_automatic_record":
//                     if (isset($item_data[$key])) {
//                        $result = $this->update(array('id' => $item_data[$key]['id'], 'enable_automatic_record' => $input['enable_automatic_record']));
//                     }
//                     break;
//
//                  case "plugin_printercounters_periodicity":
//                     if (isset($item_data[$key])) {
//                        $result = $this->update(array('id' => $item_data[$key]['id'], 'periodicity' => $input['periodicity']));
//                     }
//                     break;
//
//                  case "plugin_printercounters_retries":
//                     if (isset($item_data[$key])) {
//                        $result = $this->update(array('id' => $item_data[$key]['id'], 'nb_retries' => $input['nb_retries']));
//                     }
//                     break;
//
//                  case "plugin_printercounters_max_timeout":
//                     if (isset($item_data[$key])) {
//                        $result = $this->update(array('id' => $item_data[$key]['id'], 'max_timeout' => $input['max_timeout']));
//                     }
//                     break;
//
//                  case "plugin_printercounters_recordmodel":
//                     if (isset($item_data[$key])) {
//                        $result = $this->update(array('id' => $item_data[$key]['id'], 'plugin_printercounters_recordmodels_id' => $input['plugin_printercounters_recordmodels_id'], '_clean_records' => 1));
//                     } else {
//                        $result = $this->add(array('plugin_printercounters_recordmodels_id' => $input['plugin_printercounters_recordmodels_id'],
//                                                   'items_id'                               => $key,
//                                                   'itemtype'                               => $this->itemtype));
//                     }
//                     break;
//
//                  case "plugin_printercounters_snmp_auth":
//                     if (isset($item_data[$key])) {
//                        $result = $this->update(array('id' => $item_data[$key]['id'], 'plugin_printercounters_snmpauthentications_id' => $input['plugin_printercounters_snmpauthentications_id']));
//                     }
//                     break;
//
//                  case "plugin_printercounters_immediate_record":
//                     $record = new PluginPrintercountersRecord();
//                     list($messages, $error) = $record->immediateRecord($key, $this->itemtype);
//                     $result = true;
//                     if ($error) {
//                        $result = false;
//                     }
//                     break;
//
//                  case "plugin_printercounters_update_counter_position":
//                     $record = new PluginPrintercountersRecord();
//                     list($messages, $error) = $record->updateCounterPosition($key, $this->itemtype);
//                     $result = true;
//                     if ($error) {
//                        $result = false;
//                     }
//                     break;
//
//                  default :
//                     return parent::doSpecificMassiveActions($input);
//               }
//
//               if ($result) {
//                  $res['ok']++;
//               } else {
//                  $res['ko']++;
//               }
////            } else {
////               $res['noright']++;
////            }
//         }
////      }
//
//      return $res;
//   }
   
   /**
    * Search function : addRestriction
    * 
    * @return string
    */
   function addRestriction(){
      
      $options  = Search::getCleanedOptions($this->getType());
      $restriction = '';
      foreach ($options as $num => $val) {
         if ($val['table'] == getTableForItemType('PluginPrintercountersRecord') && $val['field'] == 'id') {
            $restriction .= PluginPrintercountersSearch::addWhere('', 1, $this->getType(), $num, 'equals', NULL);
         }
         if ($val['table'] == getTableForItemType($this->itemtype) && $val['field'] == 'id') {
            $restriction .= PluginPrintercountersSearch::addWhere('AND', 0, $this->getType(), $num, 'equals', $this->items_id);
         }
      }
   
      return $restriction;
   }
   
   /** 
    * Search function : countLines
    * 
    * @param PluginPrintercountersSearch $search
    * @return int
    */
   function countLines(PluginPrintercountersSearch $search){
      $input = $this->formatSearchData($search->dataSearch);
      return count($input['records']);
   }
   
   /** 
    * Search function : addLimit
    * 
    * @param PluginPrintercountersSearch $search
    * @return string
    */
   function addLimit(PluginPrintercountersSearch $search){

      $input = $this->formatSearchData($search->dataSearch);

      $countRecords  = 0;
      $countStart    = 0;
      $countLimit    = 0;

      foreach($input['records'] as $data){
         if($countRecords < $search->current_search['start']){
            $countStart = $countStart + count($data['counters']);
            $countRecords++;
            continue;
         }
         
         if($countRecords == $search->current_search['limit']+$search->current_search['start']){
            break;
         }
         
         $countLimit = $countLimit + count($data['counters']);
         $countRecords++;
      }
      
      return "$countStart, $countLimit";
   }
   
   /** 
    * Search function : getSearchTitle
    * 
    * @return string
    */
   function getSearchTitle(){
      return __('Record history', 'printercounters');
   }
   
      /**
    * Search function : set default search
    *
    * @return an array
    */
   function getDefaultSearch() {
      
      $default_search = array();
      $options = Search::getCleanedOptions($this->getType());
      foreach ($options as $num => $val) {
         if ($val['table'] == 'glpi_entities' && $val['field'] == 'name') {
            $fields_num = $num;
            break;
         }
      }
      foreach ($options as $num => $val) {
         if ($val['table'] == getTableForItemType('PluginPrintercountersRecord') && $val['field'] == 'date') {
            $default_search['sort'] = $num;
            break;
         }
      }
      $default_search['fields'][] = array('field' => $fields_num, 'searchtype' => '', 'value' => '', 'search_link' => '');
      $default_search['order']    = 'DESC';
      
      return $default_search;
   }
   
   /** 
    * Search function : show record history data
    * 
    * @global type $CFG_GLPI
    * @param PluginPrintercountersSearch $search
    */
   function showSearchData(PluginPrintercountersSearch $search){
      global $CFG_GLPI;

      // Format data
      $input = $this->formatSearchData($search->input);
      
      // Line break
      switch($search->output_type){
         case search::HTML_OUTPUT:
            $lineBreak = "<br/>";
            break;
         default:
            $lineBreak = "\n";
            break;
      }
   
      // Fill counters if recordmodel has changed
      $recordmodels_id = array();
      foreach ($input['records'] as $records_id => $history) {
         $recordmodels_id[$history['recordmodels_id']] = $history['recordmodels_id'];
      }
      $countertype_recordmodels = new PluginPrintercountersCountertype_Recordmodel();
      $input['records'] = $countertype_recordmodels->fillCountersGap($input['records'], $recordmodels_id);

      // Display data
      $row_num  = 1;
      foreach ($input['records'] as $records_id => $history) {
         $row_num++;
         $col_num = 1;
         echo Search::showNewLine($search->output_type);
         $onclick = '';
         if ($this->canUpdateRecords()) {
            $onclick = "style='cursor:pointer' onclick='printercountersActions(\"".$CFG_GLPI['root_doc']."\", \"showManualRecord\", \"\", \"\", 
               ".json_encode(array('items_id'        => $this->items_id, 
                                   'itemtype'        => $this->itemtype, 
                                   'addLowerRecord'  => $this->canAddLessRecords() ? 1 : 0,
                                   'records_id'      => $records_id, 
                                   'formName'        => 'search_form'.$this->rand, 
                                   'historyFormName' => 'history_showForm'.$this->rand, 
                                   'rand'            => $this->rand)).");'";
         }

         echo Search::showItem($search->output_type, $history['formated_date'], $col_num, $row_num, $onclick);
         echo Search::showItem($search->output_type, $history['recordmodels_name'], $col_num, $row_num, $onclick);
         echo Search::showItem($search->output_type, $history['entities_name'], $col_num, $row_num, $onclick);
         $counters = array();
         foreach ($history['counters'] as $val) {
            $counters[$val['counters_name']] = $val['counters_value'];
         }
         echo Search::showItem($search->output_type, implode($lineBreak, array_keys($counters)), $col_num, $row_num, $onclick);
         echo Search::showItem($search->output_type, implode($lineBreak, $counters), $col_num, $row_num, $onclick);
         echo Search::showItem($search->output_type, $history['record_type'], $col_num, $row_num, $onclick);
         echo Search::showItem($search->output_type, $history['result'], $col_num, $row_num, $onclick);
         echo Search::showItem($search->output_type, $history['location'], $col_num, $row_num, $onclick);
         echo Search::showItem($search->output_type, $history['budget'], $col_num, $row_num, $onclick);
         echo Search::showItem($search->output_type, PluginPrintercountersPagecost::getCost($history['record_cost']), $col_num, $row_num, $onclick);
         echo Search::showEndLine($search->output_type);
      }
      
      // Total
      $row_num++;
      $col_num = 1;
      echo Search::showNewLine($search->output_type);
      $searchopt = array();
      $searchopt = &Search::getOptions($this->getType());
      $count = 0;
      foreach ($searchopt as $val) {
         if (!isset($val['nodisplay']) || !$val['nodisplay']) {
            $count++;
         }
      }
      for ($i = 2; $i < $count; $i++) {
         echo Search::showItem($search->output_type, '', $col_num, $row_num, "class='tab_bg_1'");
      }
      echo Search::showItem($search->output_type, "<b>".__('Total')."</b>", $col_num, $row_num, "class='tab_bg_1'");
      echo Search::showItem($search->output_type, "<b>".Html::formatNumber($input['total_record_cost'])."</b>", $col_num, $row_num, "class='tab_bg_1'");
      echo Search::showEndLine($search->output_type);
   }
   
   /** 
    * Function format record history data
    * 
    * @param array $input
    */
   function formatSearchData($input){

      $searchopt = array();
      $searchopt = &Search::getOptions($this->getType());

      $output = array();
      $types  = array();
      
      foreach ($searchopt as $num => $val) {
         if (is_array($val) && (!isset($val['nosql']) || $val['nosql'] == false)) {
            if ($val['table'] == 'glpi_plugin_printercounters_records' && $val['field'] == 'date') {
               $types['date'] = $num;

            } elseif ($val['table'] == 'glpi_plugin_printercounters_records' && $val['field'] == 'id') {
               $types['records_id'] = $num; 

            } elseif ($val['table'] == 'glpi_plugin_printercounters_recordmodels' && $val['field'] == 'name') {
               $types['recordmodels_name'] = $num; 

            } elseif ($val['table'] == 'glpi_plugin_printercounters_records' && $val['field'] == 'last_recordmodels_id') {
               $types['recordmodels_id'] = $num; 

            } elseif ($val['table'] == 'glpi_entities' && $val['field'] == 'name') {
               $types['entities_name'] = $num;

            } elseif ($val['table'] == 'glpi_entities' && $val['field'] == 'id') {
               $types['entities_id'] = $num;

            }elseif ($val['table'] == 'glpi_plugin_printercounters_countertypes' && $val['field'] == 'name') {
               $types['counters_name'] = $num;

            } elseif ($val['table'] == 'glpi_plugin_printercounters_countertypes' && $val['field'] == 'id') {
               $types['countertypes_id'] = $num;

            } elseif ($val['table'] == 'glpi_locations' && $val['field'] == 'completename') {
               $types['location'] = $num;

            } elseif ($val['table'] == 'glpi_plugin_printercounters_counters' && $val['field'] == 'value') {
               $types['counters_value'] = $num;

            } elseif ($val['table'] == 'glpi_plugin_printercounters_records' && $val['field'] == 'record_type') {
               $types['record_type'] = $num;

            } elseif ($val['table'] == 'glpi_plugin_printercounters_records' && $val['field'] == 'result') {
               $types['result'] = $num;
               
            } elseif ($val['table'] == 'glpi_plugin_printercounters_pagecosts' && $val['field'] == 'cost') {
               $types['cost'] = $num;
               
            } elseif ($val['table'] == 'glpi_plugin_printercounters_billingmodels' && $val['field'] == 'application_date') {
               $types['application_date'] = $num;
               
            } elseif ($val['table'] == 'glpi_plugin_printercounters_budgets' && $val['field'] == 'name') {
               $types['budget'] = $num;
            }
         }
      }

      if (!empty($input)) {
         $give_item = array();
         foreach ($input as $i => $row) {
            $count = 0;
            foreach ($searchopt as $num => $val) {
               if (is_array($val) && (!isset($val['nosql']) || $val['nosql'] == false)) {
                  $give_item[$i][$num] = Search::giveItem($this->getType(), $num, $row, $count);
                  $count++;
               }
            }
         }

         $itemtype = strtolower($this->itemtype);
         foreach ($give_item as $row) {
            if (!empty($row[$types['records_id']])) {
               $output[$row[$types['records_id']]]['formated_date']     = $row[$types['date']];
               $output[$row[$types['records_id']]]['date']              = date('Y-m-d H:i:s', strtotime($row[$types['date']]));
               $output[$row[$types['records_id']]]['items_id']          = $this->items_id;
               $output[$row[$types['records_id']]]['itemtype']          = $itemtype;
               $output[$row[$types['records_id']]]['recordmodels_name'] = $row[$types['recordmodels_name']];
               $output[$row[$types['records_id']]]['recordmodels_id']   = $row[$types['recordmodels_id']];
               $output[$row[$types['records_id']]]['entities_name']     = $row[$types['entities_name']];
               $output[$row[$types['records_id']]]['entities_id']       = $row[$types['entities_id']];
               $output[$row[$types['records_id']]]['location']          = $row[$types['location']];
               $output[$row[$types['records_id']]]['record_type']       = $row[$types['record_type']];
               $output[$row[$types['records_id']]]['result']            = $row[$types['result']];
               $output[$row[$types['records_id']]]['budget']            = $row[$types['budget']];
               $output[$row[$types['records_id']]]['counters'][$row[$types['countertypes_id']]] = array('counters_name'  => $row[$types['counters_name']], 
                                                                                                        'counters_value' => $row[$types['counters_value']]);
            }
         }
      }
                     
      // Get record costs
      $item_billingmodel = new PluginPrintercountersItem_Billingmodel($this->itemtype, $this->items_id);
      $output = $item_billingmodel->computeRecordCost($output);
      
      return $output;
   }

  /** 
   * getItemRecordConfig 
   * 
   * @global type $DB
   * @param type $items_id
   * @param type $itemtype
   * @return type
   */
   function getItemRecordConfig($items_id, $itemtype){
      global $DB;
      
      $output = array();
      $itemjoin = getTableForItemType($itemtype);
         
      if(!empty($items_id) && !empty($itemtype)){
         $query = "SELECT `".$this->getTable()."`.`id` as plugin_items_recordmodels_id,
                          `".$this->getTable()."`.`plugin_printercounters_recordmodels_id` as plugin_recordmodels_id,
                          `".$this->getTable()."`.`items_id`,
                          `".$this->getTable()."`.`periodicity`,
                          (`".$this->getTable()."`.`max_timeout`)*1000000 as max_timeout,
                          `".$this->getTable()."`.`nb_retries`,
                          `".$this->getTable()."`.`enable_automatic_record`,
                          `".$this->getTable()."`.`error_counter`,
                          `".$itemjoin."`.`entities_id`,
                          `".$itemjoin."`.`locations_id`,
                          `".$itemjoin."`.`name`
             FROM ".$this->getTable()."
             LEFT JOIN `".$itemjoin."`
                ON (`".$this->getTable()."`.`items_id` = `".$itemjoin."`.`id`)
             WHERE `".$this->getTable()."`.`items_id` IN ('".implode("','", $items_id)."')
             AND LOWER(`".$this->getTable()."`.`itemtype`)=LOWER('".$itemtype."')";

         $result = $DB->query($query);
         if ($DB->numrows($result)) {
            while ($data = $DB->fetch_assoc($result)) {
               $output[$data['items_id']] = $data;
            }
         }
      }
      
      return $output;
   }
   
  /** 
   * function set mutex on item
   * 
   * @global type $DB
   * @param array $items_id
   * @param type $process_id
   */
   function setMutex(array $items_id, $process_id){
      global $DB;
   
      $DB->query("UPDATE ".$this->getTable()." 
                  SET `active_mutex`='".date('Y-m-d H:i:s', time())."', `process_id`='".$process_id."'
                  WHERE `".$this->getTable()."`.`items_id` IN ('".implode("','", $items_id)."')
                  AND LOWER(`".$this->getTable()."`.`itemtype`)='".$this->itemtype."'");
                
   }
   
  /** 
   * function unset mutex on item
   * 
   * @global type $DB
   * @param int $items_recordmodels_id
   */
   function unsetMutex($items_recordmodels_id){
      $this->update(array('id' => $items_recordmodels_id, 'active_mutex' => 'NULL', 'process_id' => 0));         
   }
   
  /** 
   * function get mutex of item
   * 
   * @global type $DB
   * @param int $items_recordmodels_id
   */
   function getMutex($items_recordmodels_id){
      $this->getFromDB($items_recordmodels_id);
      
      return $this->fields['active_mutex'];
   }
   
   /**
    * Add Logs
    *
    * @return nothing
    */
   function addLog() {

      if ($this->dohistory && !empty($this->oldvalues)) {
         $searchopt = Search::getOptions($this->fields['itemtype']);

         if (!is_array($searchopt)) {
            return false;
         }
         
         $item = getItemForItemtype($this->fields['itemtype']);

         foreach ($this->oldvalues as $key => $oldval) {
            $changes = array();

            // Parsing $SEARCHOPTION to find changed field
            foreach ($searchopt as $key2 => $val2) {
               if (!is_array($val2)) {
                  // skip sub-title
                  continue;
               }
               
               // Linkfield or standard field not massive action enable
               if (($val2['field'] == $key && $val2['table'] == $this->getTable()) 
                       || ($key == getForeignKeyFieldForItemType('PluginPrintercountersRecordmodel') 
                               && $val2['field'] == 'name' && $val2['table'] == getTableForItemType('PluginPrintercountersRecordmodel')
                       || ($key == getForeignKeyFieldForItemType('PluginPrintercountersSnmpauthentication') 
                               && $val2['field'] == 'name' && $val2['table'] == getTableForItemType('PluginPrintercountersSnmpauthentication')))) {
                  
                  $id_search_option = $key2; // Give ID of the $SEARCHOPTION
                  $changes = array($id_search_option, $item->getValueToDisplay($id_search_option, addslashes($oldval)), $item->getValueToDisplay($id_search_option, $this->fields[$key]));
                  break;
               }
            }
            
            Log::history($this->fields['items_id'], $this->fields['itemtype'], $changes);
         }
      }
   }
   
         
  /** 
   * Add left join to the search
   * 
   * @param type $type
   * @param type $ref_table
   * @param type $new_table
   * @param type $linkfield
   * @param type $already_link_tables
   * @return type
   */
   function addLeftJoin($type, $ref_table, $new_table, $linkfield, &$already_link_tables) {
      
      switch ($new_table) {
         case "glpi_plugin_printercounters_budgets" :
            $out = " LEFT JOIN `$new_table`
               ON (`glpi_plugin_printercounters_records`.`entities_id` = `$new_table`.`entities_id`
                   AND `glpi_plugin_printercounters_records`.`date` >= `$new_table`.`begin_date` 
                   AND `glpi_plugin_printercounters_records`.`date` <= `$new_table`.`end_date`)";
            return $out;
            break;
      }
   }
   
  /** 
   * Actions done after add
   */
   function post_addItem() {
      $config = PluginPrintercountersConfig::getInstance();
      if ($config['set_first_record']) {
         $record = new PluginPrintercountersRecord($this->fields['itemtype'], $this->fields['items_id']);
         $record->setFirstRecord($this->fields['id'], $this->fields['plugin_printercounters_recordmodels_id']);
      }

      parent::post_addItem();
   }
      
  /** 
   * Actions done before add
   * 
   * @param type $input
   * @return boolean
   */
   function prepareInputForAdd($input) {
                      
      // Default authentication
      $snmpAuthentication      = new PluginPrintercountersSnmpauthentication();
      $defaultAuthentification = $snmpAuthentication->getDefaultAuthentication();
      if (!empty($defaultAuthentification)) {
         $input['plugin_printercounters_snmpauthentications_id'] = $defaultAuthentification;
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
      if (!$this->checkMandatoryFields($input)) {
         return false;
      }

      if (isset($input['_clean_records'])
              && isset($this->fields['plugin_printercounters_recordmodels_id'])
              && isset($input['plugin_printercounters_recordmodels_id'])
              && isset($input['id'])
              && $this->input['plugin_printercounters_recordmodels_id']
              != $this->fields['plugin_printercounters_recordmodels_id']) {

         $temp = new PluginPrintercountersRecord();
         $temp->deleteByCriteria(array('last_recordmodels_id'                         => $this->fields['plugin_printercounters_recordmodels_id'],
                                       'plugin_printercounters_items_recordmodels_id' => $input['id']), 1);
      }

      return $input;
   }
   
  /** 
   * Check mandatory fields 
   * 
   * @param type $input
   * @return boolean
   */
   function checkMandatoryFields($input){
      $msg     = array();
      $checkKo = false;
      
      $item = getItemForItemtype($this->itemtype);
      
      $mandatory_fields = array('plugin_printercounters_snmpauthentications_id'  => PluginPrintercountersSnmpauthentication::getTypeName(),
                                'plugin_printercounters_recordmodels_id'         => PluginPrintercountersRecordmodel::getTypeName(),
                                'items_id'                                       => $item::getTypeName(),
                                'periodicity'                                    => __('Periodicity', 'printercounters'));

      foreach($input as $key => $value){
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
?>