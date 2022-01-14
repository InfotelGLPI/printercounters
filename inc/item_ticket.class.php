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
 * Class PluginPrintercountersItem_Ticket
 *
 * This class allows to add and manage tickets on the items
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersItem_Ticket extends CommonDBTM {

   static $types = ['Printer'];
   static $rightname = 'plugin_printercounters';

   protected $itemtype;
   protected $items_id;

   // Event types
   static $NB_RECORD_ERROR = 1;
   static $NO_RECORD_DELAY = 2;


   /**
    * Constructor
    *
    * @param type $itemtype
    * @param type $items_id
    */
   public function __construct($itemtype = 'printer', $items_id = 0) {
      $this->setItemtype($itemtype);
      $this->setitems_id($items_id);

      parent::__construct();
   }

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   static function getTypeName($nb = 0) {
      return _n('Ticket creation', 'Tickets creation', $nb, 'printercounters');
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
   public function setitems_id($items_id) {

      $this->items_id = $items_id;
   }

      /**
    * Function Show the event dropdown
    *
    * @param type $name
    * @param array $options
    * @return type
    */
   static function dropdownEvent($name = 'event_type', array $options = []) {
      return Dropdown::showFromArray($name, array_merge([Dropdown::EMPTY_VALUE], self::getAllEventArray()), $options);
   }

   /**
    * Function get the event
    *
    * @param type $value
    * @return type
    */
   static function getEvent($value) {
      if (!empty($value)) {
         $data = self::getAllEventArray();
         return $data[$value];
      }
   }

   /**
    * Function Get the event list
    *
    * @return an array
    */
   static function getAllEventArray() {

      // To be overridden by class
      $tab = [self::$NB_RECORD_ERROR => __('Consecutive errors', 'printercounters'),
                   self::$NO_RECORD_DELAY => __('No records', 'printercounters')];

      return $tab;
   }

   /**
    * Function show for record model
    *
    * @param type $item
    * @return boolean
    */
   function showTickets($config) {

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
      $data = $this->getItems($start);

      echo "<form name='form' method='post' action='".
         Toolbox::getItemTypeFormURL('PluginPrintercountersConfig')."'>";

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr class='headerRow'><th colspan='4'>".self::getTypeName()."</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2'>";
      echo __('Number of consecutive errors', 'printercounters');
      echo "</th>";
      echo "<th colspan='2'>";
      echo __('No successful records', 'printercounters');
      echo "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Number of consecutive errors', 'printercounters');
      echo "</td>";
      echo "<td>";
      Dropdown::showNumber('nb_errors_ticket', ['value' => $config["nb_errors_ticket"],
                                                'min'   => 0,
                                                'max'   => 10]);
      echo "</td>";
      echo "<td>";
      echo __('No successful records', 'printercounters');
      echo "&nbsp;".__('since', 'printercounters');
      echo "</td>";
      echo "<td>";
      Dropdown::showTimeStamp("no_record_delay_ticket", ['min'             => DAY_TIMESTAMP,
                                                              'max'             => 190*DAY_TIMESTAMP,
                                                              'step'            => DAY_TIMESTAMP,
                                                              'value'           => $config["no_record_delay_ticket"],
                                                              'addfirstminutes' => false,
                                                              'inhours'         => false]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('since', 'printercounters')."</td>";
      echo "<td>";
      Dropdown::showTimeStamp("nb_errors_delay_ticket", ['min'             => DAY_TIMESTAMP,
                                                              'max'             => 15*DAY_TIMESTAMP,
                                                              'step'            => DAY_TIMESTAMP,
                                                              'value'           => $config["nb_errors_delay_ticket"],
                                                              'addfirstminutes' => false,
                                                              'inhours'         => false]);
      echo "</td>";
      echo "<td>";
      echo __('For the item status', 'printercounters');
      echo "</td>";
      echo "<td>";
      $state = [];
      $dbu   = new DbUtils();
      foreach ($dbu->getAllDataFromTable($dbu->getTableForItemType('State')) as $value) {
         $state[$value['id']] = $value['name'];
      }
      Dropdown::showFromArray('items_status', $state, ['multiple'        => true,
                                                            'values'          => !empty($config["items_status"])?$config["items_status"]:[],
                                                            'size'            => 10,
                                                            'mark_unmark_all' => true,
                                                            'width'           => 250]);
      echo "</td>";
      echo "</tr>";

      // Ticket fields
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4'>";
      echo __('Ticket fields', 'printercounters');
      echo "</th>";
      echo "</tr>";

      // Category
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Category assigned to the new ticket', 'printercounters');
      echo "</td>";
      echo "<td>";
      ITILCategory::dropdown(['name'  => 'tickets_category',
                                   'value' => $config["tickets_category"]]);
      echo "</td>";
      echo "<td colspan='2'></td>";
      echo "</tr>";

      // Add item group / user
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Add item group', 'printercounters');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo("add_item_group", $config["add_item_group"]);
      echo "</td>";
      echo "<td>";
      echo __('Add item user', 'printercounters');
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo("add_item_user", $config["add_item_user"]);
      echo "</td>";
      echo "</tr>";

      // Description
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Description of the new ticket', 'printercounters');
      echo "</td>";
      echo "<td colspan='3'>";
      Html::textarea(['name'            => 'tickets_content',
                      'value'       => stripslashes($config['tickets_content']),
                      'cols'       => 80,
                      'rows'       => 14,
                      'enable_richtext' => false]);
      echo "</td>";
      echo "</tr>";

      echo "<tr><td class='tab_bg_2 center' colspan='6'>";
      echo Html::submit(_sx('button', 'Update'), ['name' => 'update_config', 'class' => 'btn btn-primary']);
      echo "</td></tr>";
      echo "</table></div>";
      Html::closeForm();

      if (!empty($data)) {
         $this->listItems($data, $start, $rand);
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
   private function listItems($data, $start, $rand, $canedit = true) {
      $dbu  = new DbUtils();
      $item = $dbu->getItemForItemtype($this->itemtype);

      echo "<div class='left'>";

      if ($canedit) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass'.__CLASS__.$rand];
         Html::showMassiveActions($massiveactionparams);
      }

      Html::printAjaxPager(self::getTypeName(2), $start, $dbu->countElementsInTable($this->getTable()));
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th width='10'>";
      if ($canedit) {
         echo Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
      }
      echo "</th>";
      echo "<th>".$item::getTypeName()."</th>";
      echo "<th>".__('Ticket')."</th>";
      echo "<th>".__('Event type', 'printercounters')."</th>";
      echo "</tr>";

      foreach ($data as $field) {
         echo "<tr class='tab_bg_2'>";
         echo "<td width='10'>";
         if ($canedit) {
            Html::showMassiveActionCheckBox(__CLASS__, $field['id']);
         }
         echo "</td>";
         echo "<td><a href='".Toolbox::getItemTypeFormURL($field['itemtype'])."?id=".$field['items_id']."' target='_blank'>".$field['items_name']."</a></td>";
         echo "<td><a href='".Toolbox::getItemTypeFormURL('Ticket')."?id=".$field['tickets_id']."' target='_blank'>".$field['tickets_name']."</a></td>";
         echo "<td>".self::getEvent($field['events_type'])."</td>";
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
    * Function get items
    *
    * @global type $DB
    * @param type $start
    * @return type
    */
   function getItems($start = 0) {
      global $DB;

      $output = [];
      $dbu    = new DbUtils();

      $itemjoin = $dbu->getTableForItemType('Ticket');
      $itemjoin2 = $dbu->getTableForItemType($this->itemtype);

      $query = "SELECT `".$this->getTable()."`.`id`, 
                       `".$this->getTable()."`.`items_id`,
                       `".$this->getTable()."`.`itemtype`, 
                       `".$this->getTable()."`.`tickets_id`,
                       `".$this->getTable()."`.`events_type`,
                       `".$itemjoin."`.`name` as tickets_name,
                       `".$itemjoin2."`.`name` as items_name
          FROM ".$this->getTable()."
          LEFT JOIN `".$itemjoin."`
             ON(`".$this->getTable()."`.`tickets_id` = `".$itemjoin."`.`id`)
          LEFT JOIN `".$itemjoin2."`
             ON(`".$this->getTable()."`.`items_id` = `".$itemjoin2."`.`id` AND LOWER(`".$this->getTable()."`.`itemtype`) = '".strtolower($this->itemtype)."')
          WHERE (`".$itemjoin."`.`status` != '".Ticket::CLOSED."' 
            AND `".$itemjoin."`.`status` != '".Ticket::SOLVED."' )
          ORDER BY `".$itemjoin2."`.`name`
          LIMIT ".intval($start).",".intval($_SESSION['glpilist_limit']);

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $output[$data['id']] = $data;
         }
      }

      return $output;
   }


   //######################### CRON FUNCTIONS #####################################################

   static function cronInfo($name) {

      switch ($name) {
         case 'PrintercountersCreateTicket':
            return ['description' => __('Create a ticket if there are consecutive errors on records OR no records since a defined date', 'printercounters')];
      }
      return [];
   }

   /**
    * Cron action on tasks : create a ticket if consecutive errors on records OR no recrods since a defined date
    *
    * @param $task for log, if NULL display
    */
   static function cronPrintercountersCreateTicket($task = null) {

      $cron_status   = 1;
      $config        = new PluginPrintercountersConfig();
      $ticket        = new self();
      $message       = [];

      $config_data = $config->getInstance();
      $data = $ticket->getItems();
      $item_ticket_data = [];
      foreach ($data as $val) {
         $item_ticket_data[$val['events_type']][] = $val['items_id'];
      }

      // Check no automatic or manual recrods since a defined date
      $result = $ticket->checkNoRecordForDelay($config_data, $item_ticket_data, $task);
      if (!empty($result)) {
         $message[] = $result;
      }

      // Check consecutive errors on records
      $result = $ticket->checkConsecutiveRecordError($config_data, $item_ticket_data, $task);
      if (!empty($result)) {
         $message[] = $result;
      }

      // Display message
      self::displayCronMessage($message, $task);

      return $cron_status;
   }

   /**
    * Display cron messages
    *
    * @param type $message
    * @param type $task
    */
   static function displayCronMessage($message, $task = null) {
      $message = array_unique($message);
      if (!empty($message)) {
         foreach ($message as $value) {
            if ($task) {
               $task->log($value);
            } else {
               Session::addMessageAfterRedirect($value, true, ERROR);
            }
         }
      }
   }

   /**
    * Function check consecutive errors on records
    *
    * @global type $DB
    * @param array $config_data
    * @param array $item_ticket_data : already used items
    * @return type
    */
   function checkConsecutiveRecordError($config_data, $item_ticket_data) {
      global $DB;

      $items_ko   = [];
      $items_data = [];
      $items      = [];
      $message    = [];
      $dbu        = new DbUtils();

      if (!empty($config_data['nb_errors_delay_ticket'])) {
         $itemjoin1 = $dbu->getTableForItemType("PluginPrintercountersRecord");
         $itemjoin2 = $dbu->getTableForItemType("PluginPrintercountersItem_Recordmodels");
         $itemjoin3 = $dbu->getTableForItemType($this->itemtype);

         // Get all recordmodels items
         $query = "SELECT `".$itemjoin2."`.`items_id`,
                          `".$itemjoin2."`.`itemtype`,
                          `".$itemjoin3."`.`users_id`,
                          `".$itemjoin3."`.`groups_id_tech`,
                          `".$itemjoin3."`.`users_id_tech`,
                          `".$itemjoin3."`.`entities_id`,
                          `".$itemjoin1."`.`id` as records_id, 
                          `".$itemjoin1."`.`date` as min_records_date, 
                          `".$itemjoin1."`.`record_type`
                   FROM $itemjoin2
                   INNER JOIN $itemjoin3 
                      ON (`".$itemjoin2."`.`items_id` = `".$itemjoin3."`.`id` AND `".$itemjoin2."`.`itemtype` = '".$this->itemtype."')
                   INNER JOIN $itemjoin1 
                      ON (`".$itemjoin1."`.`plugin_printercounters_items_recordmodels_id` = `".$itemjoin2."`.`id`)
                   WHERE `".$itemjoin2."`.`enable_automatic_record` = '1'
                   GROUP BY `".$itemjoin2."`.`items_id`";

         $result = $DB->query($query);
         if ($DB->numrows($result)) {
            while ($data = $DB->fetchAssoc($result)) {
               if (!isset($item_ticket_data[PluginPrintercountersItem_Ticket::$NB_RECORD_ERROR]) || !in_array($data['items_id'], $item_ticket_data[PluginPrintercountersItem_Ticket::$NB_RECORD_ERROR])) {
                  $items[$data['itemtype']][] = $data['items_id'];
                  $items_data[$data['itemtype']][$data['items_id']] = $data;
               }
            }
         }

         // Search if there's no automatic and manual record since a defined date
         $query = "SELECT COUNT(`".$itemjoin1."`.`id`) as bad_record_count,
                          `".$itemjoin2."`.`items_id`,
                          `".$itemjoin2."`.`itemtype`
                   FROM $itemjoin2
                   LEFT JOIN $itemjoin1 
                      ON (`".$itemjoin1."`.`plugin_printercounters_items_recordmodels_id` = `".$itemjoin2."`.`id`)
                   WHERE `".$itemjoin1."`.`date` > FROM_UNIXTIME(UNIX_TIMESTAMP() - ".$config_data['nb_errors_delay_ticket'].", '%Y-%m-%d %h:%i')
                   AND `".$itemjoin1."`.`record_type` != '".PluginPrintercountersRecord::$MANUAL_TYPE."' 
                   AND `".$itemjoin1."`.`record_type` != '".PluginPrintercountersRecord::$AUTOMATIC_TYPE."' 
                   GROUP BY `".$itemjoin2."`.`items_id`;";

         $result = $DB->query($query);
         if ($DB->numrows($result)) {
            while ($data = $DB->fetchAssoc($result)) {
               if ($data['bad_record_count'] >= $config_data['nb_errors_ticket']) {
                  $items_ko[$data['itemtype']][] = $data['items_id'];
               }
            }
         }

         // If not ok create ticket
         if (!empty($items)) {
            foreach ($items as $itemtype => $item) {
               foreach ($item as $items_id) {
                  if (!empty($items_ko) && in_array($items_id, $items_ko[$itemtype])) {
                     $message[] = $this->createTicket($items_data[$itemtype][$items_id], $config_data, PluginPrintercountersItem_Ticket::$NB_RECORD_ERROR);
                  }
               }
            }
         }
      }

      return implode(", ", array_unique($message));
   }

   /**
    * Function check no automatic or manual records since a defined date
    *
    * @global type $DB
    * @param array $config_data
    * @param array $item_ticket_data : already used items
    * @return type
    */
   function checkNoRecordForDelay($config_data, $item_ticket_data) {
      global $DB;

      $items_ok   = [];
      $items_data = [];
      $items      = [];
      $message    = [];
      $dbu        = new DbUtils();

      if (!empty($config_data['no_record_delay_ticket'])) {

         $itemjoin1 = $dbu->getTableForItemType("PluginPrintercountersRecord");
         $itemjoin2 = $dbu->getTableForItemType("PluginPrintercountersItem_Recordmodels");
         $itemjoin3 = $dbu->getTableForItemType($this->itemtype);

         // Get all recordmodels items
         $query = "SELECT `".$itemjoin2."`.`items_id`,
                          `".$itemjoin2."`.`itemtype`,
                          `".$itemjoin3."`.`users_id`,
                          `".$itemjoin3."`.`groups_id_tech`,
                          `".$itemjoin3."`.`users_id_tech`,
                          `".$itemjoin3."`.`entities_id`,
                          `".$itemjoin1."`.`id` as records_id, 
                          `".$itemjoin1."`.`date` as min_records_date, 
                          `".$itemjoin1."`.`record_type`
                   FROM $itemjoin2
                   RIGHT JOIN $itemjoin3 
                      ON (`".$itemjoin2."`.`items_id` = `".$itemjoin3."`.`id` 
                         AND `".$itemjoin2."`.`itemtype` = '".$this->itemtype."'";

         if (isset($config_data['items_status']) && !empty($config_data['items_status'])) {
            $query .= "  AND `".$itemjoin3."`.`states_id` IN('".implode("','", $config_data['items_status'])."')";
         }

         $query .= "  )
                   LEFT JOIN $itemjoin1 
                      ON (`".$itemjoin1."`.`plugin_printercounters_items_recordmodels_id` = `".$itemjoin2."`.`id`)
                   WHERE `".$itemjoin2."`.`enable_automatic_record` = '1'
                   GROUP BY `glpi_plugin_printercounters_items_recordmodels`.`items_id` 
                   HAVING `glpi_plugin_printercounters_records`.`date` = min(`glpi_plugin_printercounters_records`.`date`)";

         $result = $DB->query($query);
         if ($DB->numrows($result)) {
            while ($data = $DB->fetchAssoc($result)) {
               if (!isset($item_ticket_data[self::$NO_RECORD_DELAY]) || !in_array($data['items_id'], $item_ticket_data[self::$NO_RECORD_DELAY])) {
                  $items[$data['itemtype']][] = $data['items_id'];
                  $items_data[$data['itemtype']][$data['items_id']] = $data;
               }
            }
         }

         // Search if there's no automatic and manual record since a defined date
         $query = "SELECT `".$itemjoin2."`.`items_id`,
                          `".$itemjoin2."`.`itemtype`
                   FROM $itemjoin2
                   LEFT JOIN $itemjoin1 
                      ON (`".$itemjoin1."`.`plugin_printercounters_items_recordmodels_id` = `".$itemjoin2."`.`id`)
                   WHERE `".$itemjoin1."`.`date` > FROM_UNIXTIME(UNIX_TIMESTAMP() - ".$config_data['no_record_delay_ticket'].", '%Y-%m-%d %h:%i')
                   AND (`".$itemjoin1."`.`record_type` = '".PluginPrintercountersRecord::$MANUAL_TYPE."' 
                      OR `".$itemjoin1."`.`record_type` = '".PluginPrintercountersRecord::$AUTOMATIC_TYPE."')
                   GROUP BY `".$itemjoin2."`.`items_id`;";

         $result = $DB->query($query);
         if ($DB->numrows($result)) {
            while ($data = $DB->fetchAssoc($result)) {
               $items_ok[$data['itemtype']][] = $data['items_id'];
            }
         }

         // If ok create ticket
         if (!empty($items)) {
            foreach ($items as $itemtype => $item) {
               foreach ($item as $items_id) {
                  if (empty($items_ok) || !in_array($items_id, $items_ok[$itemtype])) {
                     $message[] = $this->createTicket($items_data[$itemtype][$items_id], $config_data, self::$NO_RECORD_DELAY);
                  }
               }
            }
         }
      }

      return implode(", ", array_unique($message));
   }

   /**
    * Function create ticket
    *
    * @param type $input
    * @param type $config_data
    * @param type $events_type
    * @return type
    */
   private function createTicket($input, $config_data, $events_type) {

      $ticket = new Ticket();

      $item = new $input['itemtype']();
      $item->getFromDB($input['items_id']);

      if ($tickets_id = $ticket->add(Toolbox::addslashes_deep(['_users_id_assign'    => $config_data['add_item_user'] ? $input['users_id_tech'] : "",
                                           '_users_id_requester' => $config_data['add_item_user'] ? $input['users_id'] : "",
                                           '_groups_id_assign'   => $config_data['add_item_group'] ? $input['groups_id_tech'] : "",
                                           'entities_id'         => $input['entities_id'],
                                           'status'              => $ticket::INCOMING,
                                           'itilcategories_id'   => $config_data['tickets_category'],
                                           'items_id'            => [$input['itemtype'] => [$input['items_id']]],
                                           'name'                => _n($item->getType(), $item->getType().'s', 1)." - ".$item->fields['name'].' '.self::getEvent($events_type),
                                           'content'             => $config_data['tickets_content']]))) {

         $item_ticket = new PluginPrintercountersItem_Ticket();
         $item_ticket->add(['items_id'    => $input['items_id'],
                                 'itemtype'    => $input['itemtype'],
                                 'tickets_id'  => $tickets_id,
                                 'events_type' => $events_type]);

         return "<a href='".Toolbox::getItemTypeFormURL('PluginPrintercountersConfig')."?glpi_tab=PluginPrintercountersConfig$2&itemtype=PluginPrintercountersConfig'>".__('Tickets created for : ', 'printercounters').self::getEvent($events_type)."</a>";
      }
   }

   function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'delete';
      $forbidden[] = 'purge';
      $forbidden[] = 'restore';

      return $forbidden;
   }
}
