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

include ("../../../inc/includes.php");

Session::checkLoginUser();
header("Content-Type: text/html; charset=UTF-8");
//Html::header_nocache();

$search = new PluginPrintercountersSearch();

switch ($_POST['action']) {
   case 'addSearchField':
      if (!isset($_POST['item'])) {
         exit;
      }
      $search->addSearchField($_POST['search_count'], unserialize(base64_decode($_POST['item'])));
      break;

   case 'resetSearchField':
      if (!isset($_POST['item'])) {
         exit;
      }
      $item = unserialize(base64_decode($_POST['item']));
      $search->showHistoryGenericSearch($item);
      break;

   case 'initSearch':
      if (!isset($_POST['item'])) {
         exit;
      }
      $item = unserialize(base64_decode($_POST['item']));
      $search->manageHistoryGetValues($item, $_POST);
      $search->showHistory($item);
      break;

   case 'reloadCsrf':
      Session::cleanCSRFTokens();
      echo Session::getNewCSRFToken();
      break;
}

