<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2018 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

// Direct access to file
if (strpos($_SERVER['PHP_SELF'], "searchoption.php")) {
   include ('../../../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
} else if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

Session::checkLoginUser();

// Non define case
if (isset($_POST["itemtype"])
    && isset($_POST["field"])
    && isset($_POST["num"]) ) {

   if (!is_subclass_of($_POST['itemtype'], 'CommonDBTM')) {
      throw new \RuntimeException('Invalid itemtype provided!');
   }

   $_POST['num'] = intval($_POST['num']);

   if (isset($_POST['meta']) && $_POST['meta']) {
      $fieldname = 'metacriteria';
   } else {
      $fieldname = 'criteria';
      $_POST['meta'] = 0;
   }

   $actions = Search::getActionsFor($_POST["itemtype"], $_POST["field"]);

   // is it a valid action for type ?
   if (count($actions)
       && (empty($_POST['searchtype']) || !isset($actions[$_POST['searchtype']]))) {
      $tmp                 = $actions;
      unset($tmp['searchopt']);
      $_POST['searchtype'] = key($tmp);
      unset($tmp);
   }

   $randsearch   = -1;
   $dropdownname = "searchtype$fieldname".$_POST["itemtype"].$_POST["num"];
   $searchopt    = [];

   echo "<table><tr><td>";
   if (count($actions)>0) {

      // get already get search options
      if (isset($actions['searchopt'])) {
         $searchopt = $actions['searchopt'];
         // No name for clean array with quotes
         unset($searchopt['name']);
         unset($actions['searchopt']);
      }
      $randsearch = Dropdown::showFromArray($fieldname."[".$_POST["num"]."][searchtype]",
                                            $actions,
                                            ['value'  => $_POST["searchtype"]]);
      $fieldsearch_id = Html::cleanId("dropdown_".$fieldname."[".$_POST["num"]."][searchtype]$randsearch");
   }
   echo "</td><td>";
   echo "<span id='span$dropdownname'>\n";

   $_POST['value']      = stripslashes($_POST['value']);
   $_POST['searchopt']  = $searchopt;

   include(PLUGIN_PRINTERCOUNTERS_DIR."/ajax/searchoptionvalue.php");
   echo "</span>\n";
   echo "</td></tr></table>";

   $paramsaction = ['searchtype' => '__VALUE__',
                         'field'      => $_POST["field"],
                         'itemtype'   => $_POST["itemtype"],
                         'num'        => $_POST["num"],
                         'value'      => rawurlencode($_POST['value']),
                         'searchopt'  => $searchopt,
                         'meta'       => $_POST['meta']];

   Ajax::updateItemOnSelectEvent($fieldsearch_id,
                                 "span$dropdownname",
                                 PLUGIN_PRINTERCOUNTERS_WEBDIR."//ajax/searchoptionvalue.php",
                                 $paramsaction);
}
