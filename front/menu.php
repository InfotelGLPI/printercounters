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

include ('../../../inc/includes.php');

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
   Html::header(PluginPrintercountersMenu::getTypeName(2), '', "plugins", "printercounters");
} else {
   Html::helpHeader(PluginPrintercountersMenu::getTypeName(2));
}

$menu = new PluginPrintercountersMenu();
$menu->showMenu();

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}
?>