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
   static function getJSEdition($toupdate, $function_name, $itemtype, $items_id, $parenttype, $parents_id) {
      global $CFG_GLPI;

      $dbu    = new DbUtils();
      $parent = $dbu->getItemForItemtype($parenttype);

      echo "\n<script type='text/javascript' >\n";
            echo "function $function_name() {\n";
            $params = ['type'                        => $itemtype,
                            'parenttype'                  => $parenttype,
                            $parent->getForeignKeyField() => $parents_id,
                            'id'                          => $items_id];
            Ajax::updateItemJsCode($toupdate,
                                   PLUGIN_PRINTERCOUNTERS_WEBDIR."/ajax/viewsubitem.php", $params);
            echo "};";
            echo "</script>\n";
   }


   /**
    * Create fixed modal window
    * After display it using $name.dialog("open");
    *
    * @since version 0.84
    *
    * @param string $name    name of the js object
    * @param array  $options Possible options:
    *          - width       (default 800)
    *          - height      (default 400)
    *          - modal       is a modal window? (default true)
    *          - container   specify a html element to render (default empty to html.body)
    *          - title       window title (default empty)
    *          - display     display or get string? (default true)
    *
    * @return void|string (see $options['display'])
    */
//   static function createFixedModalWindow($name, $options = []) {
//
//      $param = ['width'     => 800,
//                'height'    => 400,
//                'modal'     => true,
//                'container' => '',
//                'title'     => '',
//                'display'   => true];
//
//      if (count($options)) {
//         foreach ($options as $key => $val) {
//            if (isset($param[$key])) {
//               $param[$key] = $val;
//            }
//         }
//      }
//
//      $out  =  "<script type='text/javascript'>\n";
//      $out .= "var $name=";
//      if (!empty($param['container'])) {
//         $out .= Html::jsGetElementbyID(Html::cleanId($param['container']));
//      } else {
//         $out .= "$('<div></div>')";
//      }
//      $out .= ".dialog({\n
//         width:".$param['width'].",\n
//         autoOpen: false,\n
//         height:".$param['height'].",\n
//         modal: ".($param['modal']?'true':'false').",\n
//         title: \"".addslashes($param['title'])."\"\n
//         });\n";
//      $out .= "$('#$name').html(data.message);
//$('#dialog-confirm').dialog({
//   resizable: false,
//   height: 180,
//   width: 350,
//   modal: true,
//   buttons: {
//         OK: function () {
//            $(this).dialog('close');
//         }
//   }
//  });";
//  $out .= "</script>";
//
//      if ($param['display']) {
//         echo $out;
//      } else {
//         return $out;
//      }
//
//   }
}
