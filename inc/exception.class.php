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
 * Class PluginPrintercountersException
 *
 * This class adds custom exception management for the plugin
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersException extends Exception {

   protected $itemptype;
   protected $items_id;

   /**
    * Constructor
    *
    * @param string $message
    * @param int $code
    * @param Exception $previous
    * @param int $items_id
    * @param string $itemtype
    */
   public function __construct($message, $code, $previous, $items_id, $itemtype) {
      $this->setItemtype($itemtype);
      $this->setItems_id($items_id);

      parent::__construct($message, $code, $previous);
   }

   /**
    * Function sets items_id
    *
    * @param string
    */
   public function setItems_id($items_id) {
      $this->items_id = $items_id;
   }

   /**
    * Function sets itemtype
    *
    * @param string
    */
   public function setItemtype($itemtype) {
      $this->itemptype = $itemtype;
   }


   /**
    * Function sets plugin error message
    * @return string
    */
   function getPrintercountersMessage() {

      // Debug mode
      $user = new User();
      $user->getFromDB(Session::getLoginUserID());
      if (isset($user->fields['use_mode']) && $user->fields['use_mode'] == Session::DEBUG_MODE) {
         $trace = $this->getTraceAsString();
         $trace = preg_replace('/#(\d)/', '<br>#$1', $trace);
         return __('SNMP Error: ', 'printercounters').$this->getMessage().' '.$trace.' (itemtype : '.$this->itemptype.', items_id : '.$this->items_id.')<br>';

         // Normal mode
      } else {
         return __('SNMP Error: ', 'printercounters').$this->getMessage().' (itemtype : '.$this->itemptype.', items_id : '.$this->items_id.')';
      }
   }
}
