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
 * Class PluginPrintercountersConfig
 *
 * This class allows to manage the config
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersConfig extends CommonDBTM {

   private static $instance;

   const SNMPSET    = 1;
   const TICKETS    = 2;
   const RECORDS    = 3;
   const ERRORITEMS = 4;

   static $rightname = 'plugin_printercounters';

   static function getTypeName($nb = 0) {
      return __('Plugin management', 'printercounters');
   }

   /**
   * Define tabs
   *
   * @param type $options
   * @return array
   */
   function defineTabs($options = []) {

      $ong = [];
      $this->addStandardTab(__CLASS__, $ong, $options);

      return $ong;
   }

   /**
    * Get tab name for item
    *
    * @param CommonGLPI $item
    * @param type $tabnum
    * @param type $withtemplate
    * @return boolean
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType() == __CLASS__) {
         $tabs[self::SNMPSET]    = PluginPrintercountersSnmpset::getTypeName();
         $tabs[self::TICKETS]    = PluginPrintercountersItem_Ticket::getTypeName();
         $tabs[self::RECORDS]    = PluginPrintercountersRecord::getTypeName(2);
         $tabs[self::ERRORITEMS] = PluginPrintercountersErrorItem::getTypeName(2);

         return $tabs;
      }
      return '';
   }

   /**
    * Display content for item
    *
    * @param CommonGLPI $item
    * @param type $tabnum
    * @param type $withtemplate
    * @return boolean
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      $config = PluginPrintercountersConfig::getInstance();

      if ($item->getType() == __CLASS__) {
         switch ($tabnum) {
            case self::SNMPSET : // Snmpset
               $snmpset = new PluginPrintercountersSnmpset();
               $snmpset->showSnmpSet($config);
               break;

            case self::TICKETS : // Ticket
               $ticket = new PluginPrintercountersItem_Ticket();
               $ticket->showTickets($config);
               break;

            case self::RECORDS :
               // Record
               $record = new PluginPrintercountersRecord();
               $record->showRecordConfig($config);
                // Process
               $process = new PluginPrintercountersProcess();
               $process->showProcesses($config);
               break;

            case self::ERRORITEMS :
               // Error item
               $erroritem = new PluginPrintercountersErrorItem();
               $erroritem->showErrorItemConfig($config);
               break;
         }
      }
      return true;
   }



   /**
    * Get config instance in database
    *
    * @param type $options
    * @return type
    */
   public static function getInstance() {
      if (!isset(self::$instance)) {
         $temp = new PluginPrintercountersConfig();

         $data = $temp->getConfigFromDB();
         $input = [];
         if ($data) {
            $input = ['configs_id'               => $data['id'],
                           'nb_errors_ticket'         => $data['nb_errors_ticket'],
                           'nb_errors_delay_ticket'   => $data['nb_errors_delay_ticket'],
                           'no_record_delay_ticket'   => $data['no_record_delay_ticket'],
                           'items_status'             => json_decode($data['items_status'], true),
                           'tickets_category'         => $data["tickets_category"],
                           'tickets_content'          => $data["tickets_content"],
                           'add_item_user'            => $data["add_item_user"],
                           'add_item_group'           => $data["add_item_group"],
                           'disable_autosearch'       => $data['disable_autosearch'],
                           'set_first_record'         => $data['set_first_record'],
                           'enable_toner_alert'       => $data['enable_toner_alert'],
                           'toner_alert_repeat'       => $data['toner_alert_repeat'],
                           'toner_treshold'           => $data['toner_treshold'],
                           'max_error_counter'        => $data['max_error_counter'],
                           'enable_error_handler'     => $data['enable_error_handler']
                         ];
         }

         self::$instance = $input;
      }

      return self::$instance;
   }

   /**
    * getConfigFromDB : get all configs in the database
    *
    * @param type $options
    * @return type
    */
   function getConfigFromDB($options = []) {

      $table = $this->getTable();
      $where = [];
      if (isset($options['where'])) {
         $where = $options['where'];
      }
      $dbu        = new DbUtils();
      $dataConfig = $dbu->getAllDataFromTable($table, $where);
      if (count($dataConfig) > 0) {
         return array_shift($dataConfig);
      }

      $this->getEmpty();
      return $this->fields;
   }

   /**
    * is the current object a new  one
    *
    * @since version 0.83
    *
    * @return boolean
   **/
   function isNewItem() {

      return false;
   }
}

