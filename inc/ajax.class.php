<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 printercounters plugin for GLPI
 Copyright (C) 2014-2016 by the printercounters Development Team.

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
 * Class PluginPrintercountersAjax
 * 
 * Ajax functions
 * 
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersAjax extends CommonDBTM {
   
  /**
   * Js edition system
   * 
   * @global type $CFG_GLPI
   * @param type $toupdate
   * @param type $function_name
   * @param type $itemtype
   * @param type $items_id
   * @param type $parenttype
   * @param type $parents_id
   */
   static function getJSEdition($toupdate, $function_name, $itemtype, $items_id, $parenttype, $parents_id){
      global $CFG_GLPI;
      
      $parent = getItemForItemtype($parenttype);
      
      echo "\n<script type='text/javascript' >\n";
            echo "function $function_name() {\n";
            $params = array('type'                        => $itemtype,
                            'parenttype'                  => $parenttype,
                            $parent->getForeignKeyField() => $parents_id,
                            'id'                          => $items_id);
            Ajax::updateItemJsCode($toupdate,
                                   $CFG_GLPI["root_doc"]."/plugins/printercounters/ajax/viewsubitem.php", $params);
            echo "};";
            echo "</script>\n";
   }
   
}