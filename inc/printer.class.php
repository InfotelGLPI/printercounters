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
 * Class PluginPrintercountersPrinter
 *
 * This class brings fonctions and attributes for the SNMP interrogation of printers
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersPrinter extends PluginPrintercountersCommonSNMPObject {

   /**
    * Printer types
    */
   const PRINTER_TYPE_MONO = 'mono printer';
   const PRINTER_TYPE_COLOR = 'color printer';

   /**
    * Printer colors
    */
   const CARTRIDGE_COLOR_CYAN = 'cyan';
   const CARTRIDGE_COLOR_MAGENTA = 'magenta';
   const CARTRIDGE_COLOR_YELLOW = 'yellow';
   const CARTRIDGE_COLOR_BLACK = 'black';
   const OTHER_DATA = 'other';

   /**
    * Printer data types
    */
   const TONER_TYPE = 'toner';
   const DRUM_TYPE  = 'drum';
   const OTHER_TYPE = 'other';


   /**
    * Printer data sub-types
    */
   const PRINTER_NAME     = 1;
   const PRINTER_LOCATION = 2;
   const PRINTER_CONTACT  = 3;
   const PRINTER_UPTIME   = 4;

   /**
    * SNMP MARKER_SUPPLIES possible results
    */
   const MARKER_SUPPLIES_UNAVAILABLE = -1;
   const MARKER_SUPPLIES_UNKNOWN = -2;
   const MARKER_SUPPLIES_SOME_REMAINING = -3; // means that there is some remaining but unknown how much

   /**
    * SNMP printer common object ids
    */

   const SNMP_PRINTER_SYSUP = '.1.3.6.1.2.1.1.3.0';
   const SNMP_PRINTER_SYSNAME = '.1.3.6.1.2.1.1.5.0';
   const SNMP_PRINTER_SYSLOCATION = '.1.3.6.1.2.1.1.6.0';
   const SNMP_PRINTER_SYSCONTACT = '.1.3.6.1.2.1.1.4.0';
   const SNMP_PRINTER_FACTORY_ID = '.1.3.6.1.2.1.1.1.0';
   const SNMP_PRINTER_RUNNING_TIME = '.1.3.6.1.2.1.1.3.0';
   const SNMP_PRINTER_SERIAL_NUMBER = '.1.3.6.1.2.1.43.5.1.1.17.1';
   const SNMP_PRINTER_MAC_ADDRESS = '.1.3.6.1.2.1.2.2.1.6';
   const SNMP_PRINTER_SYSDESCR = '.1.3.6.1.2.1.25.3.2.1.3.1';
   const SNMP_PRINTER_VENDOR_NAME = '.1.3.6.1.2.1.43.9.2.1.8.1.1';
   const SNMP_NUMBER_OF_PRINTED_PAPERS = '.1.3.6.1.2.1.43.10.2.1.4.1.1';
   const SNMP_MARKER_COLORANT_VALUE = '.1.3.6.1.2.1.43.12.1.1.4.1';
   const SNMP_MARKER_SUPPLIES_MAX_CAPACITY_SLOTS = '.1.3.6.1.2.1.43.11.1.1.8.1';
   const SNMP_MARKER_SUPPLIES_MAX_CAPACITY_SLOT_1 = '.1.3.6.1.2.1.43.11.1.1.8.1.1';
   const SNMP_MARKER_SUPPLIES_MAX_CAPACITY_SLOT_2 = '.1.3.6.1.2.1.43.11.1.1.8.1.2';
   const SNMP_MARKER_SUPPLIES_MAX_CAPACITY_SLOT_3 = '.1.3.6.1.2.1.43.11.1.1.8.1.3';
   const SNMP_MARKER_SUPPLIES_MAX_CAPACITY_SLOT_4 = '.1.3.6.1.2.1.43.11.1.1.8.1.4';
   const SNMP_MARKER_SUPPLIES_MAX_CAPACITY_SLOT_5 = '.1.3.6.1.2.1.43.11.1.1.8.1.5';
   const SNMP_MARKER_SUPPLIES_ACTUAL_CAPACITY_SLOTS = '.1.3.6.1.2.1.43.11.1.1.9.1';
   const SNMP_MARKER_SUPPLIES_ACTUAL_CAPACITY_SLOT_1 = '.1.3.6.1.2.1.43.11.1.1.9.1.1';
   const SNMP_MARKER_SUPPLIES_ACTUAL_CAPACITY_SLOT_2 = '.1.3.6.1.2.1.43.11.1.1.9.1.2';
   const SNMP_MARKER_SUPPLIES_ACTUAL_CAPACITY_SLOT_3 = '.1.3.6.1.2.1.43.11.1.1.9.1.3';
   const SNMP_MARKER_SUPPLIES_ACTUAL_CAPACITY_SLOT_4 = '.1.3.6.1.2.1.43.11.1.1.9.1.4';
   const SNMP_MARKER_SUPPLIES_ACTUAL_CAPACITY_SLOT_5 = '.1.3.6.1.2.1.43.11.1.1.9.1.5';
   const SNMP_SUB_UNIT_TYPE_SLOTS = '.1.3.6.1.2.1.43.11.1.1.6.1';
   const SNMP_SUB_UNIT_TYPE_SLOT_1 = '.1.3.6.1.2.1.43.11.1.1.6.1.1';
   const SNMP_SUB_UNIT_TYPE_SLOT_2 = '.1.3.6.1.2.1.43.11.1.1.6.1.2';
   const SNMP_SUB_UNIT_TYPE_SLOT_3 = '.1.3.6.1.2.1.43.11.1.1.6.1.3';
   const SNMP_SUB_UNIT_TYPE_SLOT_4 = '.1.3.6.1.2.1.43.11.1.1.6.1.4';
   const SNMP_CARTRIDGE_COLOR_SLOT_1 = '.1.3.6.1.2.1.43.12.1.1.4.1.1';
   const SNMP_CARTRIDGE_COLOR_SLOT_2 = '.1.3.6.1.2.1.43.12.1.1.4.1.2';
   const SNMP_CARTRIDGE_COLOR_SLOT_3 = '.1.3.6.1.2.1.43.12.1.1.4.1.3';
   const SNMP_CARTRIDGE_COLOR_SLOT_4 = '.1.3.6.1.2.1.43.12.1.1.4.1.4';

   static $rightname = 'plugin_printercounters';

   static function getTypeName($nb = 0) {
      return __('Printer counters', 'printercounters');
   }

   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   /**
    * Function gets and return what type of printer we are working with,
    * or returns false if error occurred
    *
    * @return string Type of printer (PRINTER_TYPE_MONO|PRINTER_TYPE_COLOR)
    */
   public function getTypeOfPrinter() {
      $colorCartridgeSlot1 = $this->getSNMPString(self::SNMP_CARTRIDGE_COLOR_SLOT_1);
      if ($colorCartridgeSlot1 !== false) {

         if (strtolower($colorCartridgeSlot1) === self::CARTRIDGE_COLOR_CYAN) {

            /**
             * We found CYAN color cartridge in slot1 so it is color printer
             */
            return self::PRINTER_TYPE_COLOR;
         } else {

            /**
             * else it is mono printer
             */
            return self::PRINTER_TYPE_MONO;
         }
      }

      return false;
   }

   /**
    * Function returns true if it is color printer
    *
    * @return boolean
    */
   public function isColorPrinter() {
      $type = $this->getTypeOfPrinter();
      if ($type !== false) {
         return ($type === self::PRINTER_TYPE_COLOR) ? true : false;
      } else {
         return false;
      }
   }

   /**
    * Function returns true if it is color printer
    *
    * @return boolean
    */
   public function isMonoPrinter() {
      $type = $this->getTypeOfPrinter();
      if ($type !== false) {
         return ($type === self::PRINTER_TYPE_MONO) ? true : false;
      } else {
         return false;
      }
   }

   /**
    * Function gets factory id (name) of the printer,
    * or returns false if call failed
    *
    * @return string|boolean
    */
   public function getFactoryId() {
      return $this->getSNMPString(self::SNMP_PRINTER_FACTORY_ID);
   }

   /**
    * Function gets vendor name of printer
    *
    * @return string|boolean
    */
   public function getVendorName() {
      return $this->getSNMPString(self::SNMP_PRINTER_VENDOR_NAME);
   }

   /**
    * Function gets serial number of printer
    *
    * @return string|boolean
    */
   public function getSerialNumber() {
      return $this->getSNMPString(self::SNMP_PRINTER_SERIAL_NUMBER);
   }

   /**
    * Function gets a count of printed papers,
    * or returns false if call failed
    *
    * @return int|boolean
    */
   public function getNumberOfPrintedPapers() {
      snmp_set_quick_print(true);
      $numberOfPrintedPapers = $this->get(self::SNMP_NUMBER_OF_PRINTED_PAPERS);
      snmp_set_quick_print(false);

      return ($numberOfPrintedPapers !== false) ? (int) $numberOfPrintedPapers : false;
   }

   /**
    * Function gets description about black catridge of the printer,
    * or returns false if call failed
    *
    * @return string|boolean
    */
   public function getBlackCatridgeType() {
      if ($this->isColorPrinter()) {
         return $this->getSNMPString(self::SNMP_SUB_UNIT_TYPE_SLOT_4);
      } else if ($this->isMonoPrinter()) {
         return $this->getSNMPString(self::SNMP_SUB_UNIT_TYPE_SLOT_1);
      } else {
         return false;
      }
   }

   /**
    * Function gets description about cyan catridge of the printer,
    * or returns false if call failed
    *
    * @return string|boolean
    */
   public function getCyanCatridgeType() {
      if ($this->isColorPrinter()) {
         return $this->getSNMPString(self::SNMP_SUB_UNIT_TYPE_SLOT_1);
      } else {
         return false;
      }
   }

   /**
    * Function gets description about magenta catridge of the printer,
    * or returns false if call failed
    *
    * @return string|boolean
    */
   public function getMagentaCatridgeType() {
      if ($this->isColorPrinter()) {
         return $this->getSNMPString(self::SNMP_SUB_UNIT_TYPE_SLOT_2);
      } else {
         return false;
      }
   }

   /**
    * Function gets description about yellow catridge of the printer,
    * or returns false if call failed
    *
    * @return string|boolean
    */
   public function getYellowCatridgeType() {
      if ($this->isColorPrinter()) {
         return $this->getSNMPString(self::SNMP_SUB_UNIT_TYPE_SLOT_3);
      } else {
         return false;
      }
   }

   /**
    * Function gets sub-unit percentage level of the printer,
    * or
    * -1 : MARKER_SUPPLIES_UNAVAILABLE Level is unavailable
    * -2 : MARKER_SUPPLIES_UNKNOWN Level is unknown
    * -3 : MARKER_SUPPLIES_SOME_REMAINING Information about level is only that there is some remaining, but we don't know how much
    *
    * or returns false if call failed
    *
    * @param string $maxValueSNMPSlot SNMP object id
    * @param string $actualValueSNMPSlot SNMP object id
    * @return int|float|boolean
    */
   protected function getSubUnitPercentageLevel($maxValueSNMPSlot, $actualValueSNMPSlot) {
      $max = $this->get($maxValueSNMPSlot);
      $actual = $this->get($actualValueSNMPSlot);

      if ($max === false || $actual === false) {
         return false;
      }

      if ((int) $actual <= 0) {

         /**
          * Actual level of drum is unavailable, unknown or some unknown remaining
          */
         return (int) $actual;
      } else {

         /**
          * Counting result in percent format
          */
         return ($actual / ($max / 100));
      }
   }

   /**
    * Function gets actual level of black toner (in percents)
    * or returns false if call failed
    *
    * @see getSubUnitPercentageLevel
    * @return int|float|boolean
    */
   public function getBlackTonerLevel() {
      if ($this->isColorPrinter()) {
         return $this->getSubUnitPercentageLevel(self::SNMP_MARKER_SUPPLIES_MAX_CAPACITY_SLOT_4, self::SNMP_MARKER_SUPPLIES_ACTUAL_CAPACITY_SLOT_4);
      } else if ($this->isMonoPrinter()) {
         return $this->getSubUnitPercentageLevel(self::SNMP_MARKER_SUPPLIES_MAX_CAPACITY_SLOT_1, self::SNMP_MARKER_SUPPLIES_ACTUAL_CAPACITY_SLOT_1);
      } else {
         return false;
      }
   }

   /**
    * Function gets actual level of cyan toner (in percents)
    * or returns false if call failed
    *
    * @see getSubUnitPercentageLevel
    * @return int|float|boolean
    */
   public function getCyanTonerLevel() {
      if ($this->isColorPrinter()) {
         return $this->getSubUnitPercentageLevel(self::SNMP_MARKER_SUPPLIES_MAX_CAPACITY_SLOT_1, self::SNMP_MARKER_SUPPLIES_ACTUAL_CAPACITY_SLOT_1);
      } else {
         return false;
      }
   }

   /**
    * Function gets actual level of magenta toner (in percents)
    * or returns false if call failed
    *
    * @see getSubUnitPercentageLevel
    * @return int|float|boolean
    */
   public function getMagentaTonerLevel() {
      if ($this->isColorPrinter()) {
         return $this->getSubUnitPercentageLevel(self::SNMP_MARKER_SUPPLIES_MAX_CAPACITY_SLOT_2, self::SNMP_MARKER_SUPPLIES_ACTUAL_CAPACITY_SLOT_2);
      } else {
         return false;
      }
   }

   /**
    * Function gets actual level of yellow toner (in percents)
    * or returns false if call failed
    *
    * @see getSubUnitPercentageLevel
    * @return int|float|boolean
    */
   public function getYellowTonerLevel() {
      if ($this->isColorPrinter()) {
         return $this->getSubUnitPercentageLevel(self::SNMP_MARKER_SUPPLIES_MAX_CAPACITY_SLOT_3, self::SNMP_MARKER_SUPPLIES_ACTUAL_CAPACITY_SLOT_3);
      } else {
         return false;
      }
   }

   /**
    * Function gets drum level of the printer (in percents)
    * or returns false if call failed
    *
    * @see getSubUnitPercentageLevel
    * @return int|float|boolean
    */
   public function getDrumLevel() {
      if ($this->isColorPrinter()) {
         return $this->getSubUnitPercentageLevel(self::SNMP_MARKER_SUPPLIES_MAX_CAPACITY_SLOT_5, self::SNMP_MARKER_SUPPLIES_ACTUAL_CAPACITY_SLOT_5);
      } else if ($this->isMonoPrinter()) {
         return $this->getSubUnitPercentageLevel(self::SNMP_MARKER_SUPPLIES_MAX_CAPACITY_SLOT_2, self::SNMP_MARKER_SUPPLIES_ACTUAL_CAPACITY_SLOT_2);
      } else {
         return false;
      }
   }

   /**
    * Function walks through SNMP object ids of Sub-Units and returns results of them all in array
    * with calculated percentage level
    *
    * @return array
    */
   public function getAllSubUnitData() {

      $names = $this->walk(self::SNMP_SUB_UNIT_TYPE_SLOTS);
      $maxValues = $this->walk(self::SNMP_MARKER_SUPPLIES_MAX_CAPACITY_SLOTS);
      $actualValues = $this->walk(self::SNMP_MARKER_SUPPLIES_ACTUAL_CAPACITY_SLOTS);

      foreach ($names as $key => $name) {
         $resultData[] = ['name'            => $name,
                               'maxValue'        => $maxValues[$key] ,
                               'actualValue'     => $actualValues[$key],
                               'percentageLevel' => ((int) $actualValues[$key] >= 0) ? ($actualValues[$key] / ($maxValues[$key] / 100)) : null];
      }

      return $resultData;
   }



   /**
    * Function walks through SNMP object ids of Sub-Units and returns results of them all in array
    * with calculated percentage level
    *
    * @return array
    */
   public function getTonerData($resultData = []) {

      $types = $this->walk(self::SNMP_MARKER_COLORANT_VALUE);
      $names = $this->walk(self::SNMP_SUB_UNIT_TYPE_SLOTS);
      $maxValues = $this->walk(self::SNMP_MARKER_SUPPLIES_MAX_CAPACITY_SLOTS);
      $actualValues = $this->walk(self::SNMP_MARKER_SUPPLIES_ACTUAL_CAPACITY_SLOTS);

      foreach ($types as $key => $type) {
         if(ctype_xdigit(preg_replace('/\s+/', '', $type)) && ctype_xdigit(preg_replace('/\s+/', '', $names[$key]))) {
            $resultData[] = ['type' => self::TONER_TYPE,
                                  'sub_type' => trim(pack("H*", preg_replace('/\s+/', '', $type))),
                                  'name'     => trim(pack("H*", preg_replace('/\s+/', '', $names[$key]))),
                                  'value'    => ((int) $actualValues[$key] >= 0) ? ($actualValues[$key] / ($maxValues[$key] / 100)) : null];
         } elseif(ctype_xdigit(preg_replace('/\s+/', '', $type))) {
            $resultData[] = ['type' => self::TONER_TYPE,
                                  'sub_type' => trim(pack("H*", preg_replace('/\s+/', '', $type))),
                                  'name'     => $names[$key],
                                  'value'    => ((int) $actualValues[$key] >= 0) ? ($actualValues[$key] / ($maxValues[$key] / 100)) : null];
         } elseif(ctype_xdigit(preg_replace('/\s+/', '', $names[$key]))) {
            $resultData[] = ['type' => self::TONER_TYPE,
                                  'sub_type' => $type,
                                  'name'     => trim(pack("H*", preg_replace('/\s+/', '', $names[$key]))),
                                  'value'    => ((int) $actualValues[$key] >= 0) ? ($actualValues[$key] / ($maxValues[$key] / 100)) : null];
         } else {
            $resultData[] = ['type' => self::TONER_TYPE,
                                  'sub_type' => $type,
                                  'name'     => $names[$key],
                                  'value'    => ((int) $actualValues[$key] >= 0) ? ($actualValues[$key] / ($maxValues[$key] / 100)) : null];
         }
      }

      return $resultData;
   }

   /**
    * Function walks through SNMP object ids of Sub-Units and returns results of them all in array
    * with calculated percentage level
    *
    * @return array
    */
   public function getPrinterInfo($resultData = []) {

      $countertype_recordmodel = new PluginPrintercountersCountertype_Recordmodel();
      $oid_name = $countertype_recordmodel->getOIDRecordmodelCountersForItem($this->items_id, $this->itemtype, PluginPrintercountersCountertype_Recordmodel::NAME);

      if ($oid_name ==! false) {
         $name = $this->get($oid_name);
      } else {
         $name = $this->get(self::SNMP_PRINTER_SYSNAME);
      }

      $location     = $this->get(self::SNMP_PRINTER_SYSLOCATION);
      $contact      = $this->get(self::SNMP_PRINTER_SYSCONTACT);
      $uptime       = $this->get(self::SNMP_PRINTER_SYSUP);

      $resultData[] =  ['type'      => self::OTHER_TYPE,
                             'sub_type'  => self::PRINTER_NAME,
                             'name'      => __('Name'),
                             'value'     => $name];

      $resultData[] =  ['type'      => self::OTHER_TYPE,
                             'sub_type'  => self::PRINTER_LOCATION,
                             'name'      => __('Location'),
                             'value'     => $location];

      $resultData[] =  ['type'      => self::OTHER_TYPE,
                             'sub_type'  => self::PRINTER_CONTACT,
                             'name'      => __('Contact'),
                             'value'     => $contact];

      $resultData[] =  ['type'      => self::OTHER_TYPE,
                             'sub_type'  => self::PRINTER_UPTIME,
                             'name'      => __('Uptime', 'printercounters'),
                             'value'     => $uptime];

      return $resultData;
   }

   /**
    * Function gets OIDs of an item
    *
    */
   public function getOID() {
      global $DB;

      $itemjoin2 = "PluginPrintercountersItem_Recordmodel";
      $itemjoin3 = "PluginPrintercountersCountertype_Recordmodel";

      $output = [];
      $dbu    = new DbUtils();

      $query = "SELECT `".$dbu->getTableForItemType($itemjoin3)."`.`id` as countertypes_recordmodels_id,
                       `".$dbu->getTableForItemType($itemjoin3)."`.`oid` as oid
          FROM ".$dbu->getTableForItemType($itemjoin3)."
          LEFT JOIN `".$dbu->getTableForItemType($itemjoin2)."` 
             ON (`".$dbu->getTableForItemType($itemjoin2)."`.`plugin_printercounters_recordmodels_id` = `".$dbu->getTableForItemType($itemjoin3)."`.`plugin_printercounters_recordmodels_id`)
          WHERE `".$dbu->getTableForItemType($itemjoin2)."`.`items_id`=".$this->items_id." 
          AND LOWER(`".$dbu->getTableForItemType($itemjoin2)."`.`itemtype`)='".$this->itemtype."'
          AND `".$dbu->getTableForItemType($itemjoin3)."`.`oid_type`!='".PluginPrintercountersCountertype_Recordmodel::SERIAL."' 
          AND `".$dbu->getTableForItemType($itemjoin3)."`.`oid_type`!='".PluginPrintercountersCountertype_Recordmodel::NUMBER_OF_PRINTED_PAPERS."' 
          AND `".$dbu->getTableForItemType($itemjoin3)."`.`oid_type`!='".PluginPrintercountersCountertype_Recordmodel::MODEL."' 
          AND `".$dbu->getTableForItemType($itemjoin3)."`.`oid_type`!='".PluginPrintercountersCountertype_Recordmodel::NAME."' 
          AND `".$dbu->getTableForItemType($itemjoin3)."`.`oid_type`!='".PluginPrintercountersCountertype_Recordmodel::SYSDESCR."'";

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $output[$data['countertypes_recordmodels_id']] = $data['oid'];
         }
      }

      $this->oid = $output;
   }

   /**
    * Function inits search of counters for the printers
    *
    * @param array $recordmodel_config
    * @return type
    */
   public function initSearch($recordmodel_config) {

      // Conformity check
      $recordmodel = new PluginPrintercountersRecordmodel();
      list($record_result, $record_type) = $recordmodel->checkConformity($this, $recordmodel_config);

      // If no IP error : Search toner data
      $additional_datas = [];
      if ($record_result != PluginPrintercountersRecord::$IP_FAIL) {
         if ($recordmodel_config['enable_toner_level']) {
            $additional_datas = $this->getTonerData();
         }
         if ($recordmodel_config['enable_printer_info']) {
            $additional_datas = $this->getPrinterInfo($additional_datas);
         }
      }

      $counters = [];

      if (!empty($this->oid)) {
         $record = new PluginPrintercountersRecord();

         // If conformity is ok : Get counters
         if ($record_result == PluginPrintercountersRecord::$SUCCESS) {
            foreach ($this->oid as $countertypes_recordmodels_id => $oid) {
               $counters['counters'][$countertypes_recordmodels_id][0] = $this->get($oid);
            }

            // Check if counters lower than previous
            list($messages, $error) = $record->checkValues($this->items_id, $this->itemtype, $counters, 'last_record');
            if ($error) {
               $record_type = PluginPrintercountersRecord::$RECORD_ERROR_TYPE;
            }

            // Check if counters are correct values
            list($messages, $error) = $record->checkValues($this->items_id, $this->itemtype, $counters, 'numeric_or_empty_counters');
            if ($error) {
               $record_type   = PluginPrintercountersRecord::$HOST_ERROR_TYPE;
               $record_result = PluginPrintercountersRecord::$OID_FAIL;
            }
         }

         // Set counters to 0 if in error state
         if ($record_result != PluginPrintercountersRecord::$SUCCESS) {
            foreach ($this->oid as $countertypes_recordmodels_id => $oid) {
               $counters['counters'][$countertypes_recordmodels_id][0] = 0;
            }
         }
      }

      $counters['itemtype']              = $this->itemtype;
      $counters['items_id']              = $this->items_id;
      $counters['items_recordmodels_id'] = $this->item_recordmodel;
      $counters['recordmodels_id']       = $this->recordmodel;
      $counters['entities_id']           = $this->entities_id;
      $counters['locations_id']          = $this->locations_id;
      $counters['additional_datas']      = $additional_datas;

      return ['counters'         => $counters,
                   'record_result'    => $record_result,
                   'record_type'      => $record_type];
   }

   /**
    * Function set values on printer
    *
    * @param type $SNMPsetValues
    */

   public function setValues($SNMPsetValues) {

      $resultData = [];

      if (!empty($SNMPsetValues)) {
         foreach ($SNMPsetValues as $key => $val) {
            switch ($key) {
               case 'set_contact':
                  $this->set(self::SNMP_PRINTER_SYSCONTACT, 's', $SNMPsetValues['contact']);
                  $resultData['additional_datas'][] =  ['type'      => self::OTHER_TYPE,
                                                             'sub_type'  => self::PRINTER_CONTACT,
                                                             'name'      => __('Contact'),
                                                             'value'     => $SNMPsetValues['contact']];
                  break;

               case 'set_name':
                  $countertype_recordmodel = new PluginPrintercountersCountertype_Recordmodel();
                  $oid_name                = $countertype_recordmodel->getOIDRecordmodelCountersForItem($this->items_id, $this->itemtype, PluginPrintercountersCountertype_Recordmodel::NAME);

                  if ($oid_name === false) {
                     $oid_name = self::SNMP_PRINTER_SYSNAME;
                  }

                  $this->set($oid_name, 's', $val);
                  $resultData['additional_datas'][] =  ['type'      => self::OTHER_TYPE,
                                                             'sub_type'  => self::PRINTER_NAME,
                                                             'name'      => __('Name'),
                                                             'value'     => $val];
                  break;

               case 'set_location':
                  $this->set(self::SNMP_PRINTER_SYSLOCATION, 's', $val);
                  $resultData['additional_datas'][] =  ['type'      => self::OTHER_TYPE,
                                                             'sub_type'  => self::PRINTER_LOCATION,
                                                             'name'      => __('Location'),
                                                             'value'     => $val];
                  break;
            }
         }
      }

      return $resultData;
   }

}
