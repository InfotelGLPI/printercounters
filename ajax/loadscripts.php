<?php

/*
  -------------------------------------------------------------------------
  Vip plugin for GLPI
  Copyright (C) 2013 by the Vip Development Team.
  -------------------------------------------------------------------------

  LICENSE

  This file is part of Vip.

  Vip is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Vip is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Vip. If not, see <http://www.gnu.org/licenses/>.
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
               && $_SESSION['glpiactiveprofile']['interface'] == "central") {

            $params = array('root_doc'   => $CFG_GLPI['root_doc'],
                            'itemtype'   => $item,
                            'itemToShow' => 'Infocom',
                            'glpi_tab'   => 'Infocom$1',
                            'lang'       => array('global_tco' => __('Global TCO', 'printercounters')));

            echo "<script type='text/javascript'>";
            echo "printercounters_addelements(".json_encode($params).");";
            echo "</script>";
         }
      }
      break;
}
?>