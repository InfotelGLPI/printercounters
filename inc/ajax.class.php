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
   
   /**
    * Create modal window
    * After display it using $name.show()
    * May be constraint to a predefined html item setting renderTo options
    *
    * @since version 0.84
    *
    * @param $name            name of the js object
    * @param $url             URL to display in modal
    * @param $options array   of possible options:
    *          - width (default 800)
    *          - height (default 400)
    *          - modal : is a modal window ? (default true)
    *          - container : specify a html element to render (default empty to html.body)
    *          - title : window title (default empty)
   **/
   static function createModalWindow($name, $url, $options=array() ) {

      $param = array('width'       => 800,
                     'height'      => 400,
                     'modal'       => true,
                     'container'   => '',
                     'title'       => '',
                     'extraparams' => array());

      if (count($options)) {
         foreach ($options as $key => $val) {
            if (isset($param[$key])) {
               $param[$key] = $val;
            }
         }
      }

      echo "<script type='text/javascript'>";
      echo "var $name=new Ext.Window({
         layout:'fit',
         width:".$param['width'].",
         height:".$param['height'].",
         closeAction:'hide',
         modal: ".($param['modal']?'true':'false').",
         ".(!empty($param['container'])?"renderTo: '".$param['container']."',":'')."
         autoScroll: true,
         title: \"".addslashes($param['title'])."\"";
      if (is_array($param['extraparams']) && count($param['extraparams'])) {
         echo "autoLoad: {url: '$url',
                    scripts: true,
                    nocache: true";
         echo ", params: '".Toolbox::append_params($param['extraparams'])."'";
         echo "}";
      }
     echo " }); ";
      echo "</script>";
   }
   
}