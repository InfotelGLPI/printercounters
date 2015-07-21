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

define('GLPI_ROOT', '../../..');

include (GLPI_ROOT."/inc/includes.php");

Session::checkLoginUser();
//Html::header_nocache();

switch($_POST['action']){
   case 'getTco':
      header('Content-Type: application/json; charset=UTF-8"');
      $result = 0;
      if (isset($_POST['items_id']) && isset($_POST['itemtype'])) {
         $item_recordmodel = new PluginPrintercountersItem_Recordmodel();
         if ($item_recordmodel->getFromDBByQuery(" WHERE LOWER(`itemtype`)=LOWER('".$_POST['itemtype']."') AND `items_id`=".$_POST['items_id'])) {
            $result = $item_recordmodel->fields['global_tco'];
         }
      }
      echo json_encode(array('global_tco' => Html::formatNumber($result)));
      break;
}

?>