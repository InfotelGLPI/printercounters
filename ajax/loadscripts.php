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

include ('../../../inc/includes.php');

Html::header_nocache();
Session::checkLoginUser();
header("Content-Type: text/html; charset=UTF-8");

switch ($_POST['action']) {
   case "load" :
      foreach (PluginPrintercountersItem_Recordmodel::$types as $item) {
         if (isset($_SERVER['HTTP_REFERER'])
               && strpos($_SERVER['HTTP_REFERER'], strtolower($item)) !== false
               && Session::getCurrentInterface() == "central") {

            $params = ['root_doc'   => $CFG_GLPI['root_doc'],
                            'itemtype'   => $item,
                            'itemToShow' => 'Infocom',
                            'glpi_tab'   => 'Infocom$1',
                            'lang'       => ['global_tco' => __('Global TCO', 'printercounters')]];

            echo "<script type='text/javascript'>";
            echo "printercounters_addelements(".json_encode($params).");";
            echo "</script>";
         }
      }
      break;
}
