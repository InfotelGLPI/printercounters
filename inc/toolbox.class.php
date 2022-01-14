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
 * Class PluginPrintercountersToolbox
 *
 * This class adds usefull functions
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersToolbox {

   /**
    * Check if mac address is valid and return it in the format 00:00:00:00:00:00:00:00
    *
    * @param type $mac
    * @return boolean
    */
   static function getValidMacAddress($mac) {

      if (preg_match("/^([0-9a-f]{1,2}[\.:\-\s]){5}([0-9a-f]{1,2})$/i", $mac)) {
         $mac = str_replace(['.', '-', ' '], ':', $mac);

         $hexa = explode(':', $mac);
         foreach ($hexa as &$value) {
            $value = strtolower(str_pad($value, 2, '0', STR_PAD_LEFT));
         }

         return implode(':', $hexa);
      }

      return false;
   }

   /**
    * Get number of same item name
    *
    * @global type $DB
    * @param type $name
    * @param type $table
    * @return int
    */
   static function getCopyNumber($name, $table) {
      global $DB;

      $query = "SELECT count(*) as count      
          FROM ".$table."
          WHERE `name` LIKE '%".$name."%';";

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            return $data['count'];
         }
      }

      return 0;
   }

   static function replaceAccents($string) {
      $unwanted_array = [    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' ];
      return strtr($string, $unwanted_array);
   }
}
