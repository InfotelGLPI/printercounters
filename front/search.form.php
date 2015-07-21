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

if (isset($_POST['item'])) {
   if ($_POST['display_type'] == Search::PDF_OUTPUT_PORTRAIT || $_POST['display_type'] == Search::PDF_OUTPUT_LANDSCAPE) {
      include (GLPI_ROOT."/lib/ezpdf/class.ezpdf.php");
   }
   
   $item = unserialize(Toolbox::decodeArrayFromInput($_POST['item']));

   $search = new PluginPrintercountersSearch();
   $search->manageHistoryGetValues($item, $_POST);
   $search->showHistory($item);
}
?>