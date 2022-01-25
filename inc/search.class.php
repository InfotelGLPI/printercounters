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
 * Class PluginPrintercountersSearch
 *
 * This class adds an alternative search in Ajax for the plugin
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersSearch extends CommonDBTM {

   var $output_type    = Search::HTML_OUTPUT;
   var $number         = 0;
   var $default_search = [];
   var $current_search = [];
   var $dataSearch     = [];
   var $input          = [];

   /**
   * Function get values
   *
   * @param object $item
   * @param array $params
   */
   function manageHistoryGetValues($item, $params = []) {

      // Set search values
      $p = $this->setSearchValues($item, $params);

      // Total Number of events
      $dataSearch = $p;
      unset($dataSearch['limit']);
      unset($dataSearch['start']);
      $this->dataSearch = $this->getHistoryFromDB($item, $dataSearch);
      if (is_callable([$item, 'countLines'])) {
         $this->number = $item->countLines($this);
      } else {
         $this->number = count($this->dataSearch);
      }

      // Get data
      $this->input = $this->getHistoryFromDB($item, $p);
   }

   /**
   * Function set search values
   *
   * @param type $params
   */
   function setSearchValues($item, $params) {

      // Default values of parameters
      $this->default_search = $this->getDefaultSearch($item);

      $p['sort']  = $this->default_search['sort'];
      $p['order'] = $this->default_search['order'];
      $p['start'] = $this->default_search['start'];
      $p['limit'] = $this->default_search['limit'];

      foreach ($this->default_search as $key => $val) {
         if (is_array($val)) {
            foreach ($val as $key2 => $val2) {
               $p[$key][$key2] = $val2;
            }
         } else {
            $p[$key] = $val;
            if ($key == 'limit') {
               $_SESSION['glpilist_limit'] = $val;
            }
         }
      }

      foreach ($params as $key => $val) {
         $p[$key] = $val;
      }

      // Type of display
      if (isset($p["display_type"])) {
         $this->output_type = $p["display_type"];
         if ($this->output_type < 0) {
            $this->output_type = - $this->output_type;
         }
      }

      // Set current search parameters
      $this->current_search = $p;

      return $p;
   }

   /**
   * Function show search fields
   *
   * @param object $item
   */
   function showHistoryGenericSearch($item) {
      global $CFG_GLPI;

      if (empty($this->default_search)) {
         $this->default_search = $this->getDefaultSearch($item);
      }

      if (empty($this->current_search)) {
         $this->current_search = $this->getDefaultSearch($item);
      }

      $itemtype = $item->getType();
      $ID = $item->getID();

      // Display search
      if ($this->output_type == search::HTML_OUTPUT) {
         echo "<form id='search_form".$item->rand."'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'>";
         echo "<td>";
         echo "<div style='float:left;padding-top:8px;padding-bottom:8px;'>";
         // First line display add / delete images for normal and meta search items
         echo "<input type='hidden' disabled id='add_search_count".$item->rand."' name='add_search_count' value='".(count($this->default_search['criteria'])-1)."'>";
         echo "<a href=\"javascript:printercountersSearch.addSearchField('".PLUGIN_PRINTERCOUNTERS_WEBDIR."', 'search_line".$item->rand."', 'add_search_count".$item->rand."', 'search_form".$item->rand."');\">";
         echo "<i class='fa-1x fas fa-plus-square' title=\"".
         __('Add a search criterion')."\"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;";

         echo "<a href=\"javascript:printercountersSearch.deleteSearchField('search_line".$item->rand."', 'add_search_count".$item->rand."');\" >";
         echo "<i class='fa-1x fas fa-minus-square' title=\"".
              __('Delete a search criterion')."\"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;";
         echo "</div>";

         // Display link item
         foreach ($this->default_search['criteria'] as $key => $val) {
            $this->addSearchField($key, $item, $val);
         }

         echo "</td>";

         // Submit
         echo "<td class='center'>";
         echo "<input type='button' onClick = \"printercountersSearch.initSearch('".PLUGIN_PRINTERCOUNTERS_WEBDIR."', 'search_form".$item->rand."', 'history_showForm".$item->rand."');\" value='".__('Search')."' class='submit btn btn-primary'>";
         echo Html::hidden('itemtype', ['value' => $itemtype]);
         echo Html::hidden('id', ['value' => $ID]);
         echo Html::hidden('item', ['value' => base64_encode(serialize($item))]);
         echo "<a href='javascript:void(0)' onClick = \"printercountersSearch.resetSearchField('".PLUGIN_PRINTERCOUNTERS_WEBDIR."', 'history_showSearch".$item->rand."', 'search_form".$item->rand."', 'history_showForm".$item->rand."');\">";
         echo "&nbsp;&nbsp;";
         echo "<i class='fa-1x fas fa-times-circle' title=\"".__s('Blank')."\"></i>";
         echo "</a>";
         echo "</td>";
         echo "</tr></table>\n";

         Html::closeForm();
      }
   }

   /**
   * Function add search fields
   *
   * @global type $CFG_GLPI
   * @param int $i
   * @param string $itemtype
   */
   function addSearchField($i, $item, $default_search_params = []) {
      global $CFG_GLPI;

      $itemtype = $item->getType();

      if (empty($this->default_search)) {
         $default_search_params = $this->getDefaultSearch($item);
         $default_search_params = $default_search_params['criteria'][0];
      }

      $default_search['link']        = '';
      $default_search['field']       = '';
      $default_search['value']       = '';
      $default_search['searchtype']  = '';

      foreach ($default_search_params as $key => $val) {
         $default_search[$key] = $val;
      }

      echo "<div id='search_line".$item->rand.$i."'>";
      echo "<table>";
      echo "<tr><td style='padding:0px 0px;'>";

      // Display link item
      if ($i > 0) {
         $operators = ['AND', 'OR', 'AND NOT', 'OR NOT'];
         $elements = [];
         foreach ($operators as $val) {
            $elements[$val] = $val;
         }
         Dropdown::showFromArray("criteria[$i][link]", $elements,
                                 ['value' => $default_search['link'], 'width' => 80]);
         echo "&nbsp";
      }

      // display select box to define search item
      $elements = [];
      $options = Search::getOptions($itemtype);
      reset($options);

      $str_limit = 28;
      foreach ($options as $key => $val) {
         if ((!isset($val['nosearch']) || $val['nosearch'] == false)
             && (!isset($val['nodisplay']) || $val['nodisplay'] == false)) {
            $elements[$key] = Toolbox::substr($val["name"], 0, $str_limit);
         }
      }
      $rand = Dropdown::showFromArray("criteria[$i][field]", $elements,
                                      ['value' => $default_search['field'], 'width' => 150]);
      echo "</td>";

      echo "<td style='padding:0px 0px'>";
      echo "<div id='SearchSpan".$item->getType()."$i'>\n";
      if (isset($_POST['itemtype'])) {
         $itemtype = $_POST['itemtype'];
      }
      $_POST['itemtype']   = $item->getType();
      $_POST['num']        = $i;
      $_POST['field']      = $default_search['field'];
      $_POST['value']      = $default_search['value'];
      $_POST['searchtype'] = $default_search['searchtype'];
      include (PLUGIN_PRINTERCOUNTERS_DIR."/ajax/searchoption.php");
      $_POST['itemtype'] = !empty($itemtype) ? $itemtype : '';
      echo "</div>\n";

      $params = ['field'       => '__VALUE__',
                      'itemtype'    => $item->getType(),
                      'num'         => $i,
                      'value'       => '',
                      'searchtype'  => ''];

      Ajax::updateItemOnSelectEvent("dropdown_criteria_".$i."__field_".$rand, "SearchSpan".$item->getType()."$i",
                                    PLUGIN_PRINTERCOUNTERS_WEBDIR."/ajax/searchoption.php", $params);
      echo "</td></tr>";
      echo "</table></div>";
   }

   /**
   * Function set default search
   *
   * @param object $item
   */
   function getDefaultSearch($item) {

      $default_search                = [];
      $options                       = Search::getOptions($item->getType());
      $fields_num                    = array_keys($options);
      $default_search['criteria'][0] = ['field' => $fields_num[0], 'searchtype' => 'contains',
                                        'value' => '', 'link' => ''];
      $default_search['order']       = 'ASC';
      $default_search['sort']        = 1;
      $default_search['start']       = 0;
      $default_search['limit']       = $_SESSION['glpilist_limit'];

      if (is_callable([$item, 'getDefaultSearch'])) {
         $custom_search = $item->getDefaultSearch($this);
         foreach ($custom_search as $key => $val) {
            if (is_array($val)) {
               foreach ($val as $key2 => $val2) {
                  $default_search[$key][$key2] = $val2;
               }
            } else {
               $default_search[$key] = $val;
            }
         }
      }

      return $default_search;
   }

   /**
   * Function show each elments of search
   *
   * @param type $item
   * @param type $options
   */
   function showSearch($item, $options = []) {

      $params['massiveaction'] = false;
      $params['display']       = true;
      $params['fixedDisplay']  = true;

      if (!empty($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      if (!isset($item->rand)) {
         $item->rand = mt_rand();
      }

      $item->massiveaction = $params['massiveaction'];
      $item->fixedDisplay  = $params['fixedDisplay'];

      // Init request and set data
      $this->manageHistoryGetValues($item, $params);

      if ($params['display']) {
         // Display title
         $this->showTitle($item);

         // Show search bar
         echo "<div id='history_showSearch".$item->rand."'>";
         $this->showHistoryGenericSearch($item);
         echo "</div>";

         // Show list of items
         $canedit = ($item->canCreate() && $params['massiveaction']
                     && !(empty($this->input)
                          && empty($this->dataSearch)));
         if ($canedit) {
            Html::openMassiveActionsForm('mass'.__CLASS__.$item->rand);
            $massiveactionparams = ['item' => __CLASS__,
                                    'container' => 'mass'.__CLASS__.$item->rand,
                                    'fixed' => $params['fixedDisplay']];
            Html::showMassiveActions($massiveactionparams);
         }

         echo "<div id='history_showForm".$item->rand."'>";
         $this->showHistory($item);
         echo "</div>";

         if ($canedit) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }

         self::initPrintercounterJS($this->current_search);
      }
   }


   /**
    * Display the search title
   *
   * @param type $item
   */
   function showTitle($item) {

      echo '<table class="tab_cadre_fixe">';
      echo '<tr><th>';
      if (is_callable([$item, 'getSearchTitle'])) {
         echo $item->getSearchTitle($this);
      } else {
         echo $item::getTypeName();
      }
      echo '</th></tr>';
      echo '</table>';
   }

   /**
    * Print the history form
    *
    * @param type $item
    * @return boolean
    */
   function showHistory($item) {
      global $CFG_GLPI;

      // validation des droits
      if (!$item->canView()) {
         return false;
      }

      $row_num = 1;
      $col_num = 1;
      $output  = '';

      $custom_display = false;
      if (is_callable([$item, 'showSearchData'])) {
         $custom_display = true;
      }

      $canedit = ($item->canCreate() && $item->massiveaction);

      if (empty($this->input) && empty($this->dataSearch)) {
         echo Search::showHeader($this->output_type, 0, 1, true);
         echo Search::showNewLine($this->output_type);
         echo Search::showItem($this->output_type, __('No historical'), $col_num, $row_num,
                               'class="center"');
         echo Search::showEndLine($this->output_type);
         echo Search::showFooter($this->output_type, $item::getTypeName());

      } else {
         // Show pager
         if ($this->output_type == search::HTML_OUTPUT) {
            $this->printAjaxPager($item);
         }

         // Show headers
         echo Search::showHeader($this->output_type, count($this->input), 8, $item->fixedDisplay);
         echo Search::showNewLine($this->output_type);
         if ($canedit && $this->output_type == Search::HTML_OUTPUT) {
            echo Search::showHeaderItem($this->output_type,
                                        Html::getCheckAllAsCheckbox('mass'.__CLASS__.$item->rand),
                                        $col_num);
         }
         $searchopt            = [];
         $itemtype             = $item->getType();
         $searchopt[$itemtype] = &Search::getOptions($itemtype);
         ksort($searchopt[$itemtype]);
         foreach ($searchopt[$itemtype] as $num => $val) {
            if (isset($val['nodisplay']) && $val['nodisplay']) {
               continue;
            }
            $linkto = '';
            if (!isset($val['nosort']) || !$val['nosort']) {
               $linkto = "javascript:printercountersSearch.initSearch('".PLUGIN_PRINTERCOUNTERS_WEBDIR."', "
                                              . "'search_form".$item->rand."', "
                                              . "'history_showForm".$item->rand."', "
                                              .str_replace('"', "'",
                                                           json_encode(['start' => $this->current_search['start'],
                                                                        'limit' => $this->current_search['limit'],
                                                                        'order' => (($this->current_search['order'] == "ASC") ? "DESC" : "ASC"),
                                                                        'sort'  => $num])) . ");";
            }
            echo Search::showHeaderItem($this->output_type, $val["name"], $col_num, $linkto, ($this->current_search['sort'] == $num), $this->current_search['order']);
         }
         echo Search::showEndLine($this->output_type);

         // Show custom data
         if ($custom_display) {
            $item->showSearchData($this);

            // Default data display
         } else {
            $line['raw'] = [];
            foreach ($this->input as $history) {
               $row_num++;
               $col_num = 1;

               $line['raw'] = $history;
               PluginPrintercountersSearch::parseData($line,$item->getType());

               // Show massive action checkbox
               $count   = 0;
               echo Search::showNewLine($this->output_type);
               if ($canedit && $this->output_type == search::HTML_OUTPUT) {
                  foreach ($searchopt[$item->getType()] as $num => $val) {
                     if ($val['table'] == $item->getTable() && $val['field'] == 'id') {
                        echo "<td class='center' width='10'>";
                        Html::showMassiveActionCheckBox($item->getType(),
                                                        Search::giveItem($item->getType(),
                                                                         $num, $line, $count));
                        echo "</td>";
                     }
                     $count++;
                  }
               }
               // Show columns
               $count   = 0;
               foreach ($searchopt[$item->getType()] as $num => $val) {
                  if ((isset($val['nodisplay']) && $val['nodisplay'])
                      || (isset($val['nosql']) && $val['nosql'])) {
                     continue;
                  }
                  echo Search::showItem($this->output_type, Search::giveItem($item->getType(),
                                                                             $num, $line, $count),
                                        $col_num, $row_num);
                  $count++;
               }
               echo Search::showEndLine($this->output_type);
            }
         }

         echo Search::showFooter($this->output_type, self::getTypeName());
      }

      echo $output;
   }

   /**
   * Function construct query and get values
   *
   * @global type $DB
   * @global type $CFG_GLPI
   * @param type $item
   * @param array $params
   * @return type
   */
   function getHistoryFromDB($item, array $params) {
      global $DB, $CFG_GLPI;

      $itemtype = $item->getType();

      // Default values of parameters
      $p                = [];
      $p['sort']        = '';
      $p['order']       = '';

      foreach ($params as $key => $val) {
         $p[$key] = $val;
      }

      $searchopt = [];
      $searchopt[$item->getType()] = &Search::getOptions($item->getType());

       // Get the items to display
      $toview = [];
      foreach ($searchopt[$item->getType()] as $key => $val) {
         if ((!isset($val['nosql']) || $val['nosql'] == false)) {
            $toview[] = $key;
         }
      }
      sort($toview);

      $dbu = new DbUtils();

      $blacklist_tables = [];
      if (isset($CFG_GLPI['union_search_type'][$itemtype])) {
         $itemtable = $CFG_GLPI['union_search_type'][$itemtype];
         $blacklist_tables[] = $dbu->getTableForItemType($itemtype);
      } else {
         $itemtable = $dbu->getTableForItemType($itemtype);
      }

      // hack for AllAssets
      if (isset($CFG_GLPI['union_search_type'][$itemtype])) {
         $entity_restrict = true;
      } else {
         $entity_restrict = $item->isEntityAssign();
      }

      // Construct the request

      // 1 - SELECT
      // request currentuser for SQL supervision, not displayed
      $query = "SELECT ";
      // Add select for all toview item
      foreach ($toview as $key => $val) {
         $query .= self::addSelect($itemtype, $val, $val, 0);
      }

      if (!empty($itemtable)) {
         $query .= "`$itemtable`.`id` AS id ";
      }

      // 2 - FROM AND LEFT JOIN
      // Set reference table
      $query .= " FROM `$itemtable`";

      // Init already linked tables array in order not to link a table several times
      $already_link_tables = [];
      // Put reference table
      array_push($already_link_tables, $item->getTable());

      // Add default join
      $COMMONLEFTJOIN = Search::addDefaultJoin($item->getType(), $item->getTable(), $already_link_tables);
      $query .= $COMMONLEFTJOIN;

      // Search all case
      foreach ($searchopt[$item->getType()] as $key => $val) {
         // Do not search on Group Name
         if (is_array($val) && (!isset($val['nosql']) || $val['nosql'] == false)) {
            if (isset($searchopt[$item->getType()][$key]["table"])
                && !in_array($searchopt[$item->getType()][$key]["table"], $blacklist_tables)) {
               $query .= self::addLeftJoin($item->getType(), $item->getTable(), $already_link_tables,
                                           $searchopt[$item->getType()][$key]["table"],
                                           $searchopt[$item->getType()][$key]["linkfield"], 0, 0,
                                           $searchopt[$item->getType()][$key]["joinparams"]);
            }
         }
      }

      // 3 - WHERE
      $criteria = [];
      foreach ($p['criteria'] as $key => $search_item) {
         if (!empty($search_item['value'])) {
            $LINK = " ";
            $NOT = 0;
            $tmplink = "";

            if (isset($search_item['link'])) {
               if (strstr($search_item['link'], "NOT")) {
                  $tmplink = " ".str_replace(" NOT", "", $search_item['link']);
                  $NOT = 1;
               } else {
                  $tmplink = " ".$search_item['link'];
               }
            } else {
               $tmplink = " AND ";
            }
            // Manage Link if not first item
            if (!empty($criteria)) {
               $LINK = $tmplink;
            }

            $criteria[$key] = self::addWhere($LINK, $NOT, $item->getType(), $search_item['field'], $search_item['searchtype'], $search_item['value']);
         }
      }

      $query .= " WHERE 1";
      if (!empty($criteria)) {
         $query .= " AND ( ";
         foreach ($criteria as $value) {
            $query .= $value;
         }
         $query .= " )";
      }

      // Add item restrictions if needed
      if (is_callable([$item, 'addRestriction'])) {
         $query .= " AND ".$item->addRestriction($this);
      }

      // 4- GROUP BY
      if (is_callable([$item, 'addGroupBy'])) {
         $query .= " GROUP BY ".$item->addGroupBy($this);
      } else {
         //// 7 - Manage GROUP BY
         $GROUPBY = "";
         if (empty($GROUPBY)) {
            foreach ($toview as $key => $val) {
               if (!empty($GROUPBY)) {
                  break;
               }
               if (isset($searchopt[$itemtype][$val]["forcegroupby"])) {
                  $GROUPBY = " GROUP BY `$itemtable`.`id`";
               }
            }
         }
         $query .= $GROUPBY;
      }

      // 5 - ORDER
      if (isset($p['sort']) && isset($p['order'])) {
         foreach ($toview as $key => $val) {
            if ($p['sort'] == $val) {
               $query .= self::addOrderBy($itemtype, $p['sort'], $p['order'], $val);
            }
         }
      }

      // 6 - LIMIT
      if (isset($p['start']) && isset($p['limit'])) {
         if (is_callable([$item, 'addLimit'])) {
            $query .= " LIMIT ".$item->addLimit($this);
         } else {
            $query .= " LIMIT ".intval($p['start']).",".intval($p['limit']);
         }
      }

      // Return results
      $result = $DB->query($query);
      $output = [];
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $output[] = $data;
         }
      }

      return $output;
   }

      /**
    * Generic Function to add where to a request
    *
    * @param $link         link string
    * @param $nott         is it a negative search ?
    * @param $itemtype     item type
    * @param $ID           ID of the item to search
    * @param $searchtype   searchtype used (equals or contains)
    * @param $val          item num in the request
    * @param $meta         is a meta search (meta=2 in search.class.php) (default 0)
    *
    * @return select string
   **/
   static function addWhere($link, $nott, $itemtype, $ID, $searchtype, $val, $meta = 0) {

      $searchopt = &Search::getOptions($itemtype);
      $table     = $searchopt[$ID]["table"];
      $field     = $searchopt[$ID]["field"];
      $dbu       = new DbUtils();

      $inittable = $table;
      $addtable  = '';
      if (($table != 'asset_types')
          && ($table != $dbu->getTableForItemType($itemtype))
          && ($searchopt[$ID]["linkfield"] != $dbu->getForeignKeyFieldForTable($table))) {
         //         $addtable = "_".$searchopt[$ID]["linkfield"];
         $table   .= $addtable;
      }

      if (isset($searchopt[$ID]['joinparams'])) {
         $complexjoin = Search::computeComplexJoinID($searchopt[$ID]['joinparams']);

         if (!empty($complexjoin)) {
            //            $table .= "_".$complexjoin;
         }
      }

      if ($meta
          && ($dbu->getTableForItemType($itemtype) != $table)) {
         $table .= "_".$itemtype;
      }

      // Hack to allow search by ID on every sub-table
      if (preg_match('/^\$\$\$\$([0-9]+)$/', $val, $regs)) {
         return $link." (`$table`.`id` ".($nott?"<>":"=").$regs[1]." ".
                         (($regs[1] == 0)?" OR `$table`.`id` IS NULL":'').") ";
      }

      // Preparse value
      if (isset($searchopt[$ID]["datatype"])) {
         switch ($searchopt[$ID]["datatype"]) {
            case "datetime" :
            case "date" :
            case "date_delay" :
               $force_day = true;
               if ($searchopt[$ID]["datatype"] == 'datetime') {
                  $force_day = false;
               }
               if (strstr($val, 'BEGIN') || strstr($val, 'LAST')) {
                  $force_day = true;
               }

               $val = Html::computeGenericDateTimeSearch($val, $force_day);

               break;
         }
      }
      switch ($searchtype) {
         case "contains" :
            $SEARCH = Search::makeTextSearch($val, $nott);
            break;

         case "equals" :
            if ($nott) {
               $SEARCH = " <> '$val'";
            } else {
               $SEARCH = " = '$val'";
            }
            break;

         case "notequals" :
            if ($nott) {
               $SEARCH = " = '$val'";
            } else {
               $SEARCH = " <> '$val'";
            }
            break;

         case "under" :
            if ($nott) {
               $SEARCH = " NOT IN ('".implode("','", $dbu->getSonsOf($inittable, $val))."')";
            } else {
               $SEARCH = " IN ('".implode("','", $dbu->getSonsOf($inittable, $val))."')";
            }
            break;

         case "notunder" :
            if ($nott) {
               $SEARCH = " IN ('".implode("','", $dbu->getSonsOf($inittable, $val))."')";
            } else {
               $SEARCH = " NOT IN ('".implode("','", $dbu->getSonsOf($inittable, $val))."')";
            }
            break;

      }

      // Plugin can override core definition for its type
      if ($plug = isPluginItemType($itemtype)) {
         $function = 'plugin_'.$plug['plugin'].'_addWhere';
         if (function_exists($function)) {
            $out = $function($link, $nott, $itemtype, $ID, $val, $searchtype);
            if (!empty($out)) {
               return $out;
            }
         }
      }

      switch ($inittable.".".$field) {
         // //          case "glpi_users_validation.name" :

         case "glpi_users.name" :
            if ($itemtype == 'User') { // glpi_users case / not link table
               if (in_array($searchtype, ['equals', 'notequals'])) {
                  return " $link `$table`.`id`".$SEARCH;
               }
               return Search::makeTextCriteria("`$table`.`$field`", $val, $nott, $link);
            }
            if ($_SESSION["glpinames_format"] == User::FIRSTNAME_BEFORE) {
               $name1 = 'firstname';
               $name2 = 'realname';
            } else {
               $name1 = 'realname';
               $name2 = 'firstname';
            }

            if (in_array($searchtype, ['equals', 'notequals'])) {
               return " $link (`$table`.`id`".$SEARCH.
                               (($val == 0)?" OR `$table`.`id` IS NULL":'').') ';
            }
            $toadd   = '';

            $tmplink = 'OR';
            if ($nott) {
               $tmplink = 'AND';
            }

            if (($itemtype == 'Ticket') || ($itemtype == 'Problem')) {
               if (isset($searchopt[$ID]["joinparams"]["beforejoin"]["table"])
                   && isset($searchopt[$ID]["joinparams"]["beforejoin"]["joinparams"])
                   && (($searchopt[$ID]["joinparams"]["beforejoin"]["table"]
                         == 'glpi_tickets_users')
                       || ($searchopt[$ID]["joinparams"]["beforejoin"]["table"]
                             == 'glpi_problems_users')
                       || ($searchopt[$ID]["joinparams"]["beforejoin"]["table"]
                             == 'glpi_changes_users'))) {

                  $bj        = $searchopt[$ID]["joinparams"]["beforejoin"];
                  $linktable = $bj['table'].'_'.Search::computeComplexJoinID($bj['joinparams']);
                  //$toadd     = "`$linktable`.`alternative_email` $SEARCH $tmplink ";
                  $toadd     = Search::makeTextCriteria("`$linktable`.`alternative_email`", $val,
                                                      $nott, $tmplink);
               }
            }
            $toadd2 = '';
            if ($nott
                && ($val != 'NULL') && ($val != 'null')) {
               $toadd2 = " OR `$table`.`$field` IS NULL";
            }
            return $link." (((`$table`.`$name1` $SEARCH
                            $tmplink `$table`.`$name2` $SEARCH
                            $tmplink `$table`.`$field` $SEARCH
                            $tmplink CONCAT(`$table`.`$name1`, ' ', `$table`.`$name2`) $SEARCH )
                            $toadd2) $toadd)";

         case "glpi_groups.completename" :
            if ($val == 'mygroups') {
               switch ($searchtype) {
                  case 'equals' :
                     return " $link (`$table`.`id` IN ('".implode("','",
                                                                  $_SESSION['glpigroups'])."')) ";

                  case 'notequals' :
                     return " $link (`$table`.`id` NOT IN ('".implode("','",
                                                                      $_SESSION['glpigroups'])."')) ";

                  case 'under' :
                     $groups = $_SESSION['glpigroups'];
                     foreach ($_SESSION['glpigroups'] as $g) {
                        $groups += $dbu->getSonsOf($inittable, $g);
                     }
                     $groups = array_unique($groups);
                     return " $link (`$table`.`id` IN ('".implode("','", $groups)."')) ";

                  case 'notunder' :
                     $groups = $_SESSION['glpigroups'];
                     foreach ($_SESSION['glpigroups'] as $g) {
                        $groups += $dbu->getSonsOf($inittable, $g);
                     }
                     $groups = array_unique($groups);
                     return " $link (`$table`.`id` NOT IN ('".implode("','", $groups)."')) ";
               }
            }
            break;

         case "glpi_auth_tables.name" :
            $user_searchopt = Search::getOptions('User');
            $tmplink        = 'OR';
            if ($nott) {
               $tmplink = 'AND';
            }
            return $link." (`glpi_authmails".$addtable."_".
                              Search::computeComplexJoinID($user_searchopt[31]['joinparams'])."`.`name`
                           $SEARCH
                           $tmplink `glpi_authldaps".$addtable."_".
                              Search::computeComplexJoinID($user_searchopt[30]['joinparams'])."`.`name`
                           $SEARCH ) ";

         case "glpi_ipaddresses.name" :
            $search  = ["/\&lt;/","/\&gt;/"];
            $replace = ["<",">"];
            $val     = preg_replace($search, $replace, $val);
            if (preg_match("/^\s*([<>])([=]*)[[:space:]]*([0-9\.]+)/", $val, $regs)) {
               if ($nott) {
                  if ($regs[1] == '<') {
                     $regs[1] = '>';
                  } else {
                     $regs[1] = '<';
                  }
               }
               $regs[1] .= $regs[2];
               return $link." (INET_ATON(`$table`.`$field`) ".$regs[1]." INET_ATON('".$regs[3]."')) ";
            }
            break;

         case "glpi_tickets.status" :
         case "glpi_problems.status" :
         case "glpi_changes.status" :
            if ($val == 'all') {
               return "";
            }
            $tocheck = [];
            if ($item = $dbu->getItemForItemtype($itemtype)) {
               switch ($val) {
                  case 'process' :
                     $tocheck = $item->getProcessStatusArray();
                     break;

                  case 'notclosed' :
                     $tocheck = $item->getAllStatusArray();
                     foreach ($item->getClosedStatusArray() as $status) {
                        if (isset($tocheck[$status])) {
                           unset($tocheck[$status]);
                        }
                     }
                     $tocheck = array_keys($tocheck);
                     break;

                  case 'old' :
                     $tocheck = array_merge($item->getSolvedStatusArray(),
                                            $item->getClosedStatusArray());
                     break;

                  case 'notold' :
                     $tocheck = $item->getAllStatusArray();
                     foreach ($item->getSolvedStatusArray() as $status) {
                        if (isset($tocheck[$status])) {
                           unset($tocheck[$status]);
                        }
                     }
                     foreach ($item->getClosedStatusArray() as $status) {
                        if (isset($tocheck[$status])) {
                           unset($tocheck[$status]);
                        }
                     }
                     $tocheck = array_keys($tocheck);
                     break;
               }
            }

            if (count($tocheck) == 0) {
               $statuses = $item->getAllStatusArray();
               if (isset($statuses[$val])) {
                  $tocheck = [$val];
               }
            }

            if (count($tocheck)) {
               if ($nott) {
                  return $link." `$table`.`$field` NOT IN ('".implode("','", $tocheck)."')";
               }
               return $link." `$table`.`$field` IN ('".implode("','", $tocheck)."')";
            }
            break;

         case "glpi_tickets_tickets.tickets_id_1" :
            $tmplink = 'OR';
            $compare = '=';
            if ($nott) {
               $tmplink = 'AND';
               $compare = '<>';
            }
            $toadd2 = '';
            if ($nott
                && ($val != 'NULL') && ($val != 'null')) {
               $toadd2 = " OR `$table`.`$field` IS NULL";
            }

            return $link." (((`$table`.`tickets_id_1` $compare '$val'
                              $tmplink `$table`.`tickets_id_2` $compare '$val')
                             AND `glpi_tickets`.`id` <> '$val')
                            $toadd2)";

         case "glpi_tickets.priority" :
         case "glpi_tickets.impact" :
         case "glpi_tickets.urgency" :
         case "glpi_problems.priority" :
         case "glpi_problems.impact" :
         case "glpi_problems.urgency" :
         case "glpi_changes.priority" :
         case "glpi_changes.impact" :
         case "glpi_changes.urgency" :
         case "glpi_projects.priority" :
            if (is_numeric($val)) {
               if ($val > 0) {
                  return $link." `$table`.`$field` = '$val'";
               }
               if ($val < 0) {
                  return $link." `$table`.`$field` >= '".abs($val)."'";
               }
               // Show all
               return $link." `$table`.`$field` >= '0' ";
            }
            return "";

         case "glpi_tickets.global_validation" :
         case "glpi_ticketvalidations.status" :
            if ($val == 'all') {
               return "";
            }
            $tocheck = [];
            switch ($val) {
               case 'can' :
                  $tocheck = CommonITILValidation::getCanValidationStatusArray();
                  break;

               case 'all' :
                  $tocheck = CommonITILValidation::getAllValidationStatusArray();
                  break;
            }
            if (count($tocheck) == 0) {
               $tocheck = [$val];
            }
            if (count($tocheck)) {
               if ($nott) {
                  return $link." `$table`.`$field` NOT IN ('".implode("','", $tocheck)."')";
               }
               return $link." `$table`.`$field` IN ('".implode("','", $tocheck)."')";
            }
            break;

      }

      // Default cases

      // Link with plugin tables
      if (preg_match("/^glpi_plugin_([a-z0-9]+)/", $inittable, $matches)) {
         if (count($matches) == 2) {
            $plug     = $matches[1];
            $function = 'plugin_'.$plug.'_addWhere';
            if (function_exists($function)) {
               $out = $function($link, $nott, $itemtype, $ID, $val, $searchtype);
               if (!empty($out)) {
                  return $out;
               }
            }
         }
      }

      $tocompute      = "`$table`.`$field`";
      $tocomputetrans = "`".$table."_trans`.`value`";
      if (isset($searchopt[$ID]["computation"])) {
         $tocompute = $searchopt[$ID]["computation"];
         $tocompute = str_replace("TABLE", "`$table`", $tocompute);
      }

      // Preformat items
      if (isset($searchopt[$ID]["datatype"])) {
         switch ($searchopt[$ID]["datatype"]) {
            case "itemtypename" :
               if (in_array($searchtype, ['equals', 'notequals'])) {
                  return " $link (`$table`.`$field`".$SEARCH.') ';
               }
               break;

            case "itemlink" :
               if (in_array($searchtype, ['equals', 'notequals'])) {
                  return " $link (`$table`.`id`".$SEARCH.') ';
               }
               break;

            case "datetime" :
            case "date" :
            case "date_delay" :
               if ($searchopt[$ID]["datatype"] == 'datetime') {
                  // Specific search for datetime
                  if (in_array($searchtype, ['equals', 'notequals'])) {
                     $val = preg_replace("/:00$/", '', $val);
                     $val = '^'.$val;
                     if ($searchtype == 'notequals') {
                        $nott = !$nott;
                     }
                     return Search::makeTextCriteria("`$table`.`$field`", $val, $nott, $link);
                  }
               }
               if ($searchtype == 'lessthan') {
                  $val = '<'.$val;
               }
               if ($searchtype == 'morethan') {
                  $val = '>'.$val;
               }
               if ($searchtype) {
                  $date_computation = $tocompute;
               }
               $search_unit = ' MONTH ';
               if (isset($searchopt[$ID]['searchunit'])) {
                  $search_unit = $searchopt[$ID]['searchunit'];
               }
               if ($searchopt[$ID]["datatype"]=="date_delay") {
                  $delay_unit = ' MONTH ';
                  if (isset($searchopt[$ID]['delayunit'])) {
                     $delay_unit = $searchopt[$ID]['delayunit'];
                  }
                  $add_minus = '';
                  if (isset($searchopt[$ID]["datafields"][3])) {
                     $add_minus = "-`$table`.`".$searchopt[$ID]["datafields"][3]."`";
                  }
                  $date_computation = "ADDDATE(`$table`.".$searchopt[$ID]["datafields"][1].",
                                               INTERVAL (`$table`.".$searchopt[$ID]["datafields"][2]."
                                                         $add_minus)
                                               $delay_unit)";
               }
               if (in_array($searchtype, ['equals', 'notequals'])) {
                  return " $link ($date_computation ".$SEARCH.') ';
               }
               $search  = ["/\&lt;/","/\&gt;/"];
               $replace = ["<",">"];
               $val     = preg_replace($search, $replace, $val);
               if (preg_match("/^\s*([<>=]+)(.*)/", $val, $regs)) {
                  if (is_numeric($regs[2])) {
                     return $link." $date_computation ".$regs[1]."
                            ADDDATE(NOW(), INTERVAL ".$regs[2]." $search_unit) ";
                  }
                  // ELSE Reformat date if needed
                  $regs[2] = preg_replace('@(\d{1,2})(-|/)(\d{1,2})(-|/)(\d{4})@', '\5-\3-\1',
                                          $regs[2]);
                  if (preg_match('/[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}/', $regs[2])) {
                     return $link." $date_computation ".$regs[1]." '".$regs[2]."'";
                  }
                  return "";
               }
               // ELSE standard search
               // Date format modification if needed
               $val = preg_replace('@(\d{1,2})(-|/)(\d{1,2})(-|/)(\d{4})@', '\5-\3-\1', $val);
               return Search::makeTextCriteria($date_computation, $val, $nott, $link);

            case "right" :
               if ($searchtype == 'notequals') {
                  $nott = !$nott;
               }
               return $link. ($nott?' NOT':'')." ($tocompute & '$val') ";

            case "bool" :
               if (!is_numeric($val)) {
                  if (strcasecmp($val, __('No')) == 0) {
                     $val = 0;
                  } else if (strcasecmp($val, __('Yes')) == 0) {
                     $val = 1;
                  }
               }
               if ($searchtype == 'notequals') {
                  $nott = !$nott;
               }
               // No break here : use number comparaison case

            case "count" :
            case "number" :
            case "decimal" :
            case "timestamp" :
               $search  = ["/\&lt;/", "/\&gt;/"];
               $replace = ["<", ">"];
               $val     = preg_replace($search, $replace, $val);

               if (preg_match("/([<>])([=]*)[[:space:]]*([0-9]+)/", $val, $regs)) {
                  if ($nott) {
                     if ($regs[1] == '<') {
                        $regs[1] = '>';
                     } else {
                        $regs[1] = '<';
                     }
                  }
                  $regs[1] .= $regs[2];
                  return $link." ($tocompute ".$regs[1]." ".$regs[3].") ";
               }
               if (is_numeric($val)) {
                  if (isset($searchopt[$ID]["width"])) {
                     $ADD = "";
                     if ($nott
                         && ($val != 'NULL') && ($val != 'null')) {
                        $ADD = " OR $tocompute IS NULL";
                     }
                     if ($nott) {
                        return $link." ($tocompute < ".(intval($val) - $searchopt[$ID]["width"])."
                                        OR $tocompute > ".(intval($val) + $searchopt[$ID]["width"])."
                                        $ADD) ";
                     }
                     return $link." (($tocompute >= ".(intval($val) - $searchopt[$ID]["width"])."
                                      AND $tocompute <= ".(intval($val) + $searchopt[$ID]["width"]).")
                                     $ADD) ";
                  }
                  if (!$nott) {
                     return " $link ($tocompute = ".(intval($val)).") ";
                  }
                  return " $link ($tocompute <> ".(intval($val)).") ";
               }
               break;
         }
      }

      // Default case
      if (in_array($searchtype, ['equals', 'notequals','under', 'notunder'])) {

         if ((!isset($searchopt[$ID]['searchequalsonfield'])
              || !$searchopt[$ID]['searchequalsonfield'])
            && ($table != $dbu->getTableForItemType($itemtype)
                || ($itemtype == 'AllAssets'))) {
            $out = " $link (`$table`.`id`".$SEARCH;
         } else {
            $out = " $link (`$table`.`$field`".$SEARCH;
         }
         if ($searchtype == 'notequals') {
            $nott = !$nott;
         }
         // Add NULL if $val = 0 and not negative search
         // Or negative search on real value
         if ((!$nott && ($val == 0))
             || ($nott && ($val != 0))) {
            $out .= " OR `$table`.`id` IS NULL";
         }
         $out .= ')';
         return $out;
      }
      $transitemtype = $dbu->getItemTypeForTable($inittable);
      if (Session::haveTranslations($transitemtype, $field)) {
         return " $link (".Search::makeTextCriteria($tocompute, $val, $nott, '')."
                          OR ".Search::makeTextCriteria($tocomputetrans, $val, $nott, '').")";
      }

      return Search::makeTextCriteria($tocompute, $val, $nott, $link);
   }

   /**
    * Generic Function to add ORDER BY to a request
    *
    * @param $itemtype  ID of the device type
    * @param $ID        field to add
    * @param $order     order define
    * @param $key       item number (default 0)
    *
    * @return select string
    *
   **/
   static function addOrderBy($itemtype, $ID, $order, $key = 0) {
      global $CFG_GLPI;

      // Security test for order
      if ($order != "ASC") {
         $order = "DESC";
      }
      $searchopt = &Search::getOptions($itemtype);

      $table     = $searchopt[$ID]["table"];
      $field     = $searchopt[$ID]["field"];

      $addtable = '';

      //      if (($table != getTableForItemType($itemtype))
      //          && ($searchopt[$ID]["linkfield"] != getForeignKeyFieldForTable($table))) {
      //         $addtable .= "_".$searchopt[$ID]["linkfield"];
      //      }
      //
      //      if (isset($searchopt[$ID]['joinparams'])) {
      //         $complexjoin = Search::computeComplexJoinID($searchopt[$ID]['joinparams']);
      //
      //         if (!empty($complexjoin)) {
      //            $addtable .= "_".$complexjoin;
      //         }
      //      }

      if (isset($CFG_GLPI["union_search_type"][$itemtype])) {
         return " ORDER BY ITEM_$ID $order ";
      }

      // Plugin can override core definition for its type
      if ($plug = isPluginItemType($itemtype)) {
         $function = 'plugin_'.$plug['plugin'].'_addOrderBy';
         if (function_exists($function)) {
            $out = $function($itemtype, $ID, $order, $key);
            if (!empty($out)) {
               return $out;
            }
         }
      }

      switch ($table.".".$field) {
         case "glpi_auth_tables.name" :
            $user_searchopt = Search::getOptions('User');
            return " ORDER BY `glpi_users`.`authtype` $order,
                              `glpi_authldaps".$addtable."_".
                                 Search::computeComplexJoinID($user_searchopt[30]['joinparams'])."`.
                                 `name` $order,
                              `glpi_authmails".$addtable."_".
                                 Search::computeComplexJoinID($user_searchopt[31]['joinparams'])."`.
                                 `name` $order ";

         case "glpi_users.name" :
            if ($itemtype!='User') {
               return " ORDER BY ".$table.$addtable.".`realname` $order,
                                 ".$table.$addtable.".`firstname` $order,
                                 ".$table.$addtable.".`name` $order";
            }
            return " ORDER BY `".$table."`.`name` $order";

         case "glpi_networkequipments.ip" :
         case "glpi_ipaddresses.name" :
            return " ORDER BY INET_ATON($table$addtable.$field) $order ";
      }

      //// Default cases

      // Link with plugin tables
      if (preg_match("/^glpi_plugin_([a-z0-9]+)/", $table, $matches)) {
         if (count($matches) == 2) {
            $plug     = $matches[1];
            $function = 'plugin_'.$plug.'_addOrderBy';
            if (function_exists($function)) {
               $out = $function($itemtype, $ID, $order, $key);
               if (!empty($out)) {
                  return $out;
               }
            }
         }
      }

      // Preformat items
      if (isset($searchopt[$ID]["datatype"])) {
         switch ($searchopt[$ID]["datatype"]) {
            case "date_delay" :
               $interval = "MONTH";
               if (isset($searchopt[$ID]['delayunit'])) {
                  $interval = $searchopt[$ID]['delayunit'];
               }

               $add_minus = '';
               if (isset($searchopt[$ID]["datafields"][3])) {
                  $add_minus = "- `$table$addtable`.`".$searchopt[$ID]["datafields"][3]."`";
               }
               return " ORDER BY ADDDATE(`$table$addtable`.`".$searchopt[$ID]["datafields"][1]."`,
                                         INTERVAL (`$table$addtable`.`".
                                                   $searchopt[$ID]["datafields"][2]."` $add_minus)
                                         $interval) $order ";
         }
      }

      //return " ORDER BY $table.$field $order ";
      return " ORDER BY ITEM_$ID $order ";

   }

   /**
   * Function set export to pdf, csv ...
   *
   * @param object $item
   * @param array $input
   */
   function setExport($item) {
      global $CFG_GLPI;

      echo "<form method='POST' name='search_export$item->rand' target='_blank' action='".$this->getFormURL()."' 
               onsubmit=\"printecounters_reloadCsrf('".PLUGIN_PRINTERCOUNTERS_WEBDIR."','search_export$item->rand');\">\n";

      if (isset($this->current_search['searchopt'])) {
         unset($this->current_search['searchopt']);
      }
      echo Html::hidden('item', ['value' => base64_encode(serialize($item))]);

      foreach ($this->current_search as $key => $val) {
         if ($key != "_glpi_csrf_token") {
            if (is_array($val)) {
               foreach ($val as $k => $v) {
                  if (is_array($v)) {
                     foreach ($v as $k2 => $v2) {
                        echo "<input type='hidden' name='".$key."[$k][$k2]' value='$v2' >";
                     }
                  } else {
                     echo "<input type='hidden' name='".$key."[$k]' value='$v' >";
                  }
               }
            } else {
               echo "<input type='hidden' name='$key' value='$val' >";
            }
         }
      }
      Dropdown::showOutputFormat();
      Html::closeForm();
   }

   /**
    * Print Ajax pager for list in tab panel
    *
    * @global type $CFG_GLPI
    * @param type $item
    * @param type $title
    */
   function printAjaxPager($item, $title = '') {
      global $CFG_GLPI;

      $_SESSION['glpilist_limit'] = $this->current_search['limit'];

      $list_limit = $_SESSION['glpilist_limit'];
      // Forward is the next step forward
      $forward = $this->current_search['start'] + $list_limit;

      // This is the end, my friend
      $end = $this->number - $list_limit;

      // Human readable count starts here
      $current_start = $this->current_search['start'] + 1;

      // And the human is viewing from start to end
      $current_end = $current_start + $list_limit - 1;
      if ($current_end > $this->number) {
         $current_end = $this->number;
      }
      // Empty case
      if ($current_end == 0) {
         $current_start = 0;
      }
      // Backward browsing
      if ($current_start - $list_limit <= 0) {
         $back = 0;
      } else {
         $back = $this->current_search['start'] - $list_limit;
      }

      // Print it
      echo "<table class='".($item->fixedDisplay ? "tab_cadre_pager" : "tab_cadrehov")."'>";
      if ($title) {
         echo "<tr><th colspan='6'>$title</th></tr>";
      }
      echo "<tr>\n";

      // Back and fast backward button
      if (!$this->current_search['start'] == 0) {
         echo "<th class='left'><a href='javascript:printercountersSearch.initSearch(\"".PLUGIN_PRINTERCOUNTERS_WEBDIR."\", \"search_form".$item->rand."\", \"history_showForm".$item->rand."\", ".json_encode(['start' => 0]).");'>
               <i class='fa-2x fas fa-angle-double-left' title=\"".
              __('Start')."\"></i></a></th>";
         echo "<th class='left'><a href='javascript:printercountersSearch.initSearch(\"".PLUGIN_PRINTERCOUNTERS_WEBDIR."\", \"search_form".$item->rand."\", \"history_showForm".$item->rand."\", ".json_encode(['start' => $back]).");'>
               <i class='fa-2x fas fa-angle-left' title=\"".
              __s('Previous')."\"></i></th>";
      }

      echo "<td width='50%' class='tab_bg_2 center'>";
      $this->printPagerForm($item);
      echo "</td>";

      // Doc export
      echo "<td class='tab_bg_2 center' width='30%'>";
      $this->setExport($item, $this->current_search);
      echo "</td>";

      // Print the "where am I?"
      echo "<td width='50%' class='tab_bg_2 b center'>";
      echo sprintf(__('From %1$d to %2$d on %3$d'), $current_start, $current_end, $this->number);
      echo "</td>\n";

      // Forward and fast forward button
      if ($forward < $this->number) {
         echo "<th class='right'><a href='javascript:printercountersSearch.initSearch(\"".PLUGIN_PRINTERCOUNTERS_WEBDIR."\", \"search_form".$item->rand."\", \"history_showForm".$item->rand."\", ".json_encode(['start' => $forward]).");'>
               <i class='fa-2x fas fa-angle-right' title=\"".
              __s('Next')."\"></i></a></th>";
         echo "<th class='right'><a href='javascript:printercountersSearch.initSearch(\"".PLUGIN_PRINTERCOUNTERS_WEBDIR."\", \"search_form".$item->rand."\", \"history_showForm".$item->rand."\", ".json_encode(['start' => $end]).");'>
               <i class='fa-2x fas fa-double-angle-right' title=\"".
              __s('End')."\"></i></a></th>";
      }

      // End pager
      echo "</tr></table>";
   }

   /**
    * Display the list_limit combo choice
    *
    * @param $action page would be posted when change the value (URL + param)
    * ajax Pager will be displayed if empty
    *
    * @return nothing (print a combo)
    * */
   function printPagerForm($item) {
      global $CFG_GLPI;

      echo "<form method='POST' action =''>\n";
      echo "<span>".__('Display (number of items)')."&nbsp;</span>";
      Dropdown::showListLimit("printercountersSearch.initSearch(\"".PLUGIN_PRINTERCOUNTERS_WEBDIR."\", \"search_form".$item->rand."\", \"history_showForm".$item->rand."\", ".json_encode(['limit' => '__VALUE__']).")");
      Html::closeForm();
   }

   /**
   * Init printercoutners JS
   *
   */
   static function initPrintercounterJS($params) {

      Html::requireJs('printercounters');

      echo '<script type="text/javascript">';
      echo 'var printercountersSearch = $(document).printercountersSearch('.json_encode($params).');';
      echo '</script>';
   }

   /**
    * Generic Function to add left join to a request
    *
    * @param $itemtype                    item type
    * @param $ref_table                   reference table
    * @param $already_link_tables   array of tables already joined
    * @param $new_table                   new table to join
    * @param $linkfield                   linkfield for LeftJoin
    * @param $meta                        is it a meta item ? (default 0)
    * @param $meta_type                   meta type table (default 0)
    * @param $joinparams            array join parameters (condition / joinbefore...)
    *
    * @return Left join string
   **/
   static function addLeftJoin($itemtype, $ref_table, array &$already_link_tables, $new_table,
                                $linkfield, $meta = 0, $meta_type = 0, $joinparams = []) {

      $dbu = new DbUtils();

      // Rename table for meta left join
      $AS = "";
      $nt = $new_table;
      $cleannt    = $nt;

      // Multiple link possibilies case
      //       if ($new_table=="glpi_users"
      //           || $new_table=="glpi_groups"
      //           || $new_table=="glpi_users_validation") {

      //      if (!empty($linkfield) && ($linkfield != getForeignKeyFieldForTable($new_table))) {
      //         $nt .= "_".$linkfield;
      //         $AS  = " AS ".$nt;
      //      }

      $complexjoin = search::computeComplexJoinID($joinparams);

      if (!empty($complexjoin)) {
         //         $nt .= "_".$complexjoin;
         $AS  = " AS ".$nt;
      }

      //       }

      $addmetanum = "";
      $rt         = $ref_table;
      $cleanrt    = $rt;
      if ($meta) {
         $addmetanum = "_".$meta_type;
         $AS         = " AS $nt$addmetanum";
         $nt         = $nt.$addmetanum;
      }

      // Auto link
      if (($ref_table == $new_table)
          && empty($complexjoin)) {
         return "";
      }

      // Do not take into account standard linkfield
      $tocheck = $nt.".".$linkfield;
      if ($linkfield == $dbu->getForeignKeyFieldForTable($new_table)) {
         $tocheck = $nt;
      }
      //       echo '->'.$tocheck.'<br>';

      if (in_array($tocheck, $already_link_tables)) {
         return "";
      }
      array_push($already_link_tables, $tocheck);

      //        echo "DONE<br>";
      $specific_leftjoin = '';

      // Plugin can override core definition for its type
      if ($plug = isPluginItemType($itemtype)) {
         $function = 'plugin_'.$plug['plugin'].'_addLeftJoin';
         if (function_exists($function)) {
            $specific_leftjoin = $function($itemtype, $ref_table, $new_table, $linkfield,
                                           $already_link_tables);
         }
      }

      // Link with plugin tables : need to know left join structure
      if (empty($specific_leftjoin)
          && preg_match("/^glpi_plugin_([a-z0-9]+)/", $new_table, $matches)) {
         if (count($matches) == 2) {
            $function = 'plugin_'.$matches[1].'_addLeftJoin';
            if (function_exists($function)) {
               $specific_leftjoin = $function($itemtype, $ref_table, $new_table, $linkfield,
                                              $already_link_tables);
            }
         }
      }
      if (!empty($linkfield)) {
         $before = '';
         //          Html::printCleanArray($joinparams);
         if (isset($joinparams['beforejoin']) && is_array($joinparams['beforejoin'])) {

            if (isset($joinparams['beforejoin']['table'])) {
               $joinparams['beforejoin'] = [$joinparams['beforejoin']];
            }

            foreach ($joinparams['beforejoin'] as $tab) {
               if (isset($tab['table'])) {
                  $intertable = $tab['table'];
                  if (isset($tab['linkfield'])) {
                     $interlinkfield = $tab['linkfield'];
                  } else {
                     $interlinkfield = $dbu->getForeignKeyFieldForTable($intertable);
                  }

                  $interjoinparams = [];
                  if (isset($tab['joinparams'])) {
                     $interjoinparams = $tab['joinparams'];
                  }
                  //                   echo "BEFORE ";
                  $before .= self::addLeftJoin($itemtype, $rt, $already_link_tables, $intertable,
                                               $interlinkfield, $meta, $meta_type, $interjoinparams);
                  //                   echo "END BEFORE ".'<br>';
               }

               // No direct link with the previous joins
               if (!isset($tab['joinparams']['nolink']) || !$tab['joinparams']['nolink']) {
                  $cleanrt     = $intertable;
                  $complexjoin = search::computeComplexJoinID($interjoinparams);
                  if (!empty($complexjoin)) {
                     //                     $intertable .= "_".$complexjoin;
                  }
                  $rt = $intertable.$addmetanum;
               }
            }
         }

         $addcondition = '';
         if (isset($joinparams['condition'])) {
            $from         = ["`REFTABLE`", "REFTABLE", "`NEWTABLE`", "NEWTABLE"];
            $to           = ["`$rt`", "`$rt`", "`$nt`", "`$nt`"];
            $addcondition = str_replace($from, $to, $joinparams['condition']);
            $addcondition = $addcondition." ";
         }

         if (!isset($joinparams['jointype'])) {
            $joinparams['jointype'] = 'standard';
         }

         if (empty($specific_leftjoin)) {
            switch ($new_table) {
               // No link
               case "glpi_auth_tables" :
                     $user_searchopt     = search::getOptions('User');

                     $specific_leftjoin  = self::addLeftJoin($itemtype, $rt, $already_link_tables,
                                                             "glpi_authldaps", 'auths_id', 0, 0,
                                                             $user_searchopt[30]['joinparams']);
                     $specific_leftjoin .= self::addLeftJoin($itemtype, $rt, $already_link_tables,
                                                             "glpi_authmails", 'auths_id', 0, 0,
                                                             $user_searchopt[31]['joinparams']);
                     break;
            }
         }

         if (empty($specific_leftjoin)) {
            switch ($joinparams['jointype']) {
               case 'child' :
                  $linkfield = $dbu->getForeignKeyFieldForTable($cleanrt);
                  if (isset($joinparams['linkfield'])) {
                     $linkfield = $joinparams['linkfield'];
                  }

                  // Child join
                  $specific_leftjoin = " LEFT JOIN `$new_table` $AS
                                             ON (`$rt`.`id` = `$nt`.`$linkfield`
                                                 $addcondition)";
                  break;

               case 'item_item' :
                  // Item_Item join
                  $specific_leftjoin = " LEFT JOIN `$new_table` $AS
                                          ON ((`$rt`.`id`
                                                   = `$nt`.`".$dbu->getForeignKeyFieldForTable($cleanrt)."_1`
                                               OR `$rt`.`id`
                                                   = `$nt`.`".$dbu->getForeignKeyFieldForTable($cleanrt)."_2`)
                                              $addcondition)";
                  break;

               case 'item_item_revert' :
                  // Item_Item join reverting previous item_item
                  $specific_leftjoin = " LEFT JOIN `$new_table` $AS
                                          ON ((`$nt`.`id`
                                                   = `$rt`.`".$dbu->getForeignKeyFieldForTable($cleannt)."_1`
                                               OR `$nt`.`id`
                                                   = `$rt`.`".$dbu->getForeignKeyFieldForTable($cleannt)."_2`)
                                              $addcondition)";
                  break;

               case "itemtype_item" :
                  $used_itemtype = $itemtype;
                  if (isset($joinparams['specific_itemtype'])
                      && !empty($joinparams['specific_itemtype'])) {
                     $used_itemtype = $joinparams['specific_itemtype'];
                  }
                  // Itemtype join
                  $specific_leftjoin = " LEFT JOIN `$new_table` $AS
                                          ON (`$rt`.`id` = `$nt`.`items_id`
                                              AND `$nt`.`itemtype` = '$used_itemtype'
                                              $addcondition) ";
                  break;

               default :
                  // Standard join
                  $specific_leftjoin = "LEFT JOIN `$new_table` $AS
                                          ON (`$rt`.`$linkfield` = `$nt`.`id`
                                              $addcondition)";
                  break;
            }
         }
         //          echo $before.$specific_leftjoin.'<br>';
         return $before.$specific_leftjoin;
      }
      //     return '';
   }

    /**
    * Generic Function to add select to a request
    *
    * @param $itemtype     item type
    * @param $ID           ID of the item to add
    * @param $num          item num in the reque (default 0)
    * @param $meta         boolean is a meta
    * @param $meta_type    meta type table ID (default 0)
    *
    * @return select string
   **/
   static function addSelect($itemtype, $ID, $num, $meta = 0, $meta_type = 0) {
      global $CFG_GLPI;

      $dbu = new DbUtils();

      $searchopt = &Search::getOptions($itemtype);

      if (isset($searchopt[$ID]["table"]) && isset($searchopt[$ID]["field"])) {
         $table       = $searchopt[$ID]["table"];
         $field       = $searchopt[$ID]["field"];
         $addtable    = "";
         $NAME        = "ITEM";
         $complexjoin = '';

         if (isset($searchopt[$ID]['joinparams'])) {
            $complexjoin = Search::computeComplexJoinID($searchopt[$ID]['joinparams']);
         }

         if (((($table != $dbu->getTableForItemType($itemtype))
               && (!isset($CFG_GLPI["union_search_type"][$itemtype])
                   || ($CFG_GLPI["union_search_type"][$itemtype] != $table)))
              || !empty($complexjoin))
             && ($searchopt[$ID]["linkfield"] != $dbu->getForeignKeyFieldForTable($table))) {
            //         $addtable .= "_".$searchopt[$ID]["linkfield"];
         }

         if (!empty($complexjoin)) {
            //         $addtable .= "_".$complexjoin;
         }

         if ($meta) {
            //          $NAME = "META";
            if ($dbu->getTableForItemType($meta_type) != $table) {
               $addtable .= "_" . $meta_type;
            }
         }

         // Plugin can override core definition for its type
         if ($plug = isPluginItemType($itemtype)) {
            $function = 'plugin_' . $plug['plugin'] . '_addSelect';
            if (function_exists($function)) {
               $out = $function($itemtype, $ID, $num);
               if (!empty($out)) {
                  return $out;
               }
            }
         }

         $tocompute   = "`$table$addtable`.`$field`";
         $tocomputeid = "`$table$addtable`.`id`";

         $tocomputetrans = "IFNULL(`$table" . $addtable . "_trans`.`value`,'" . Search::NULLVALUE . "') ";

         $ADDITONALFIELDS = '';
         if (isset($searchopt[$ID]["additionalfields"])
             && count($searchopt[$ID]["additionalfields"])) {
            foreach ($searchopt[$ID]["additionalfields"] as $key) {
               if ($meta
                   || (isset($searchopt[$ID]["forcegroupby"]) && $searchopt[$ID]["forcegroupby"])) {
                  $ADDITONALFIELDS .= " GROUP_CONCAT(DISTINCT CONCAT(IFNULL(`$table$addtable`.`$key`,
                                                                         '" . Search::NULLVALUE . "'),
                                                   '$$', $tocomputeid) ORDER BY $tocomputeid SEPARATOR '$$$$')
                                    AS `" . $NAME . "_" . $num . "_$key`, ";
               } else {
                  $ADDITONALFIELDS .= "`$table$addtable`.`$key` AS `" . $NAME . "_" . $num . "_$key`, ";
               }
            }
         }

         // Virtual display no select : only get additional fields
         if (strpos($field, '_virtual') === 0) {
            return $ADDITONALFIELDS;
         }

         switch ($table . "." . $field) {

            case "glpi_users.name" :
               if ($itemtype != 'User') {
                  if ((isset($searchopt[$ID]["forcegroupby"]) && $searchopt[$ID]["forcegroupby"])) {
                     $addaltemail = "";
                     if ((($itemtype == 'Ticket') || ($itemtype == 'Problem'))
                         && isset($searchopt[$ID]['joinparams']['beforejoin']['table'])
                         && (($searchopt[$ID]['joinparams']['beforejoin']['table']
                              == 'glpi_tickets_users')
                             || ($searchopt[$ID]['joinparams']['beforejoin']['table']
                                 == 'glpi_problems_users')
                             || ($searchopt[$ID]['joinparams']['beforejoin']['table']
                                 == 'glpi_changes_users'))) { // For tickets_users

                        $ticket_user_table
                           = $searchopt[$ID]['joinparams']['beforejoin']['table'] .
                             "_" . Search::computeComplexJoinID($searchopt[$ID]['joinparams']['beforejoin']
                                                                ['joinparams']);
                        $addaltemail
                           = "GROUP_CONCAT(DISTINCT CONCAT(`$ticket_user_table`.`users_id`, ' ',
                                                        `$ticket_user_table`.`alternative_email`)
                                                        SEPARATOR '$$$$') AS `" . $NAME . "_" . $num . "_2`, ";
                     }
                     return " GROUP_CONCAT(DISTINCT `$table$addtable`.`id` SEPARATOR '$$$$')
                                       AS `" . $NAME . "_" . $num . "`,
                           $addaltemail
                           $ADDITONALFIELDS";

                  }
                  return " `$table$addtable`.`$field` AS `" . $NAME . "_$num`,
                        `$table$addtable`.`realname` AS `" . $NAME . "_" . $num . "_realname`,
                        `$table$addtable`.`id`  AS `" . $NAME . "_" . $num . "_id`,
                        `$table$addtable`.`firstname` AS `" . $NAME . "_" . $num . "_firstname`,
                        $ADDITONALFIELDS";
               }
               break;

            case "glpi_softwarelicenses.number" :
               return " FLOOR(SUM(`$table$addtable`.`$field`)
                           * COUNT(DISTINCT `$table$addtable`.`id`)
                           / COUNT(`$table$addtable`.`id`)) AS `" . $NAME . "_" . $num . "`,
                     MIN(`$table$addtable`.`$field`) AS `" . $NAME . "_" . $num . "_min`,
                      $ADDITONALFIELDS";

            case "glpi_profiles.name" :
               if (($itemtype == 'User')
                   && ($ID == 20)) {
                  return " GROUP_CONCAT(`$table$addtable`.`$field` SEPARATOR '$$$$') AS `" . $NAME . "_$num`,
                        GROUP_CONCAT(`glpi_profiles_users`.`entities_id` SEPARATOR '$$$$')
                                    AS `" . $NAME . "_" . $num . "_entities_id`,
                        GROUP_CONCAT(`glpi_profiles_users`.`is_recursive` SEPARATOR '$$$$')
                                    AS `" . $NAME . "_" . $num . "_is_recursive`,
                        GROUP_CONCAT(`glpi_profiles_users`.`is_dynamic` SEPARATOR '$$$$')
                                    AS `" . $NAME . "_" . $num . "_is_dynamic`,
                        $ADDITONALFIELDS";
               }
               break;

            case "glpi_entities.completename" :
               if (($itemtype == 'User')
                   && ($ID == 80)) {
                  return " GROUP_CONCAT(`$table$addtable`.`completename` SEPARATOR '$$$$')
                                    AS `" . $NAME . "_$num`,
                        GROUP_CONCAT(`glpi_profiles_users`.`profiles_id` SEPARATOR '$$$$')
                                    AS `" . $NAME . "_" . $num . "_profiles_id`,
                        GROUP_CONCAT(`glpi_profiles_users`.`is_recursive` SEPARATOR '$$$$')
                                    AS `" . $NAME . "_" . $num . "_is_recursive`,
                        GROUP_CONCAT(`glpi_profiles_users`.`is_dynamic` SEPARATOR '$$$$')
                                    AS `" . $NAME . "_" . $num . "_is_dynamic`,
                        $ADDITONALFIELDS";
               }
               break;

            case "glpi_auth_tables.name":
               $user_searchopt = Search::getOptions('User');
               return " `glpi_users`.`authtype` AS `" . $NAME . "_" . $num . "`,
                     `glpi_users`.`auths_id` AS `" . $NAME . "_" . $num . "_auths_id`,
                     `glpi_authldaps" . $addtable . "_" .
                      Search::computeComplexJoinID($user_searchopt[30]['joinparams']) . "`.`$field`
                              AS `" . $NAME . "_" . $num . "_ldapname`,
                     `glpi_authmails" . $addtable . "_" .
                      Search::computeComplexJoinID($user_searchopt[31]['joinparams']) . "`.`$field`
                              AS `" . $NAME . "_" . $num . "_mailname`,
                     $ADDITONALFIELDS";

            case "glpi_softwarelicenses.name" :
            case "glpi_softwareversions.name" :
               if ($meta) {
                  return " GROUP_CONCAT(DISTINCT CONCAT(`glpi_softwares`.`name`, ' - ',
                                                     `$table$addtable`.`$field`, '$$',
                                                     `$table$addtable`.`id`) SEPARATOR '$$$$')
                                    AS `" . $NAME . "_" . $num . "`,
                        $ADDITONALFIELDS";
               }
               break;

            case "glpi_softwarelicenses.serial" :
            case "glpi_softwarelicenses.otherserial" :
            case "glpi_softwarelicenses.comment" :
            case "glpi_softwareversions.comment" :
               if ($meta) {
                  return " GROUP_CONCAT(DISTINCT CONCAT(`glpi_softwares`.`name`, ' - ',
                                                     `$table$addtable`.`$field`,'$$',
                                                     `$table$addtable`.`id`) SEPARATOR '$$$$')
                                    AS `" . $NAME . "_" . $num . "`,
                        $ADDITONALFIELDS";
               }
               return " GROUP_CONCAT(DISTINCT CONCAT(`$table$addtable`.`name`, ' - ',
                                                  `$table$addtable`.`$field`, '$$',
                                                  `$table$addtable`.`id`) SEPARATOR '$$$$')
                                 AS `" . $NAME . "_" . $num . "`,
                     $ADDITONALFIELDS";

            case "glpi_states.name" :
               if ($meta && ($meta_type == 'Software')) {
                  return " GROUP_CONCAT(DISTINCT CONCAT(`glpi_softwares`.`name`, ' - ',
                                                     `glpi_softwareversions$addtable`.`name`, ' - ',
                                                     `$table$addtable`.`$field`, '$$',
                                                     `$table$addtable`.`id`) SEPARATOR '$$$$')
                                     AS `" . $NAME . "_" . $num . "`,
                        $ADDITONALFIELDS";
               } else if ($itemtype == 'Software') {
                  return " GROUP_CONCAT(DISTINCT CONCAT(`glpi_softwareversions`.`name`, ' - ',
                                                     `$table$addtable`.`$field`,'$$',
                                                     `$table$addtable`.`id`) SEPARATOR '$$$$')
                                    AS `" . $NAME . "_" . $num . "`,
                        $ADDITONALFIELDS";
               }
               break;
         }

         //// Default cases
         // Link with plugin tables
         if (preg_match("/^glpi_plugin_([a-z0-9]+)/", $table, $matches)) {
            if (count($matches) == 2) {
               $plug     = $matches[1];
               $function = 'plugin_' . $plug . '_addSelect';
               if (function_exists($function)) {
                  $out = $function($itemtype, $ID, $num);
                  if (!empty($out)) {
                     return $out;
                  }
               }
            }
         }

         if (isset($searchopt[$ID]["computation"])) {
            $tocompute = $searchopt[$ID]["computation"];
            $tocompute = str_replace("TABLE", "`$table$addtable`", $tocompute);
         }
         // Preformat items
         if (isset($searchopt[$ID]["datatype"])) {
            switch ($searchopt[$ID]["datatype"]) {
               case "count" :
                  return " COUNT(DISTINCT `$table$addtable`.`$field`) AS `" . $NAME . "_" . $num . "`,
                     $ADDITONALFIELDS";

               case "date_delay" :
                  $interval = "MONTH";
                  if (isset($searchopt[$ID]['delayunit'])) {
                     $interval = $searchopt[$ID]['delayunit'];
                  }

                  $add_minus = '';
                  if (isset($searchopt[$ID]["datafields"][3])) {
                     $add_minus = "-`$table$addtable`.`" . $searchopt[$ID]["datafields"][3] . "`";
                  }
                  if ($meta
                      || (isset($searchopt[$ID]["forcegroupby"]) && $searchopt[$ID]["forcegroupby"])) {
                     return " GROUP_CONCAT(DISTINCT ADDDATE(`$table$addtable`.`" .
                            $searchopt[$ID]["datafields"][1] . "`,
                                                         INTERVAL (`$table$addtable`.`" .
                            $searchopt[$ID]["datafields"][2] .
                            "` $add_minus) $interval)
                                         SEPARATOR '$$$$') AS `" . $NAME . "_$num`,
                           $ADDITONALFIELDS";
                  }
                  return "ADDDATE(`$table$addtable`.`" . $searchopt[$ID]["datafields"][1] . "`,
                               INTERVAL (`$table$addtable`.`" . $searchopt[$ID]["datafields"][2] .
                         "` $add_minus) $interval) AS `" . $NAME . "_$num`,
                       $ADDITONALFIELDS";

               case "itemlink" :
                  if ($meta
                      || (isset($searchopt[$ID]["forcegroupby"]) && $searchopt[$ID]["forcegroupby"])) {
                     return " GROUP_CONCAT(DISTINCT CONCAT($tocompute, '$$' ,
                                                        `$table$addtable`.`id`) SEPARATOR '$$$$')
                                       AS `" . $NAME . "_$num`,
                           $ADDITONALFIELDS";
                  }
                  return " $tocompute AS `" . $NAME . "_$num`,
                        `$table$addtable`.`id` AS `" . $NAME . "_" . $num . "_id`,
                        $ADDITONALFIELDS";
            }
         }
         // Default case
         if ($meta
             || (isset($searchopt[$ID]["forcegroupby"]) && $searchopt[$ID]["forcegroupby"]
                 && !isset($searchopt[$ID]["computation"]))) { // Not specific computation
            $TRANS = '';
            if (Session::haveTranslations($dbu->getItemTypeForTable($table), $field)) {
               $TRANS = "GROUP_CONCAT(DISTINCT CONCAT(IFNULL($tocomputetrans, '" . Search::NULLVALUE . "'),
                                                   '$$',$tocomputeid) ORDER BY $tocomputeid SEPARATOR '$$$$')
                                  AS `" . $NAME . "_" . $num . "_trans`, ";

            }
            return " GROUP_CONCAT(DISTINCT CONCAT(IFNULL($tocompute, '" . Search::NULLVALUE . "'),
                                               '$$',$tocomputeid) ORDER BY $tocomputeid SEPARATOR '$$$$')
                              AS `" . $NAME . "_$num`,
                  $TRANS
                  $ADDITONALFIELDS";
         }
         $TRANS = '';
         if (Session::haveTranslations($dbu->getItemTypeForTable($table), $field)) {
            $TRANS = $tocomputetrans . " AS `" . $NAME . "_" . $num . "_trans`, ";

         }
         return "$tocompute AS `" . $NAME . "_$num`, $TRANS $ADDITONALFIELDS";
      }
   }


   /**
    * Function compare search data
    *
    * @param type $itemtype
    * @param type $search_parameters
    * @param type $data_array : array([search item num], [value])
    * @return type
    */
   function compareData($itemtype, $search_parameters, $data_array) {

      $OK = true;

      if (!empty($search_parameters)) {
         $search     = [];
         $searchlink = [];
         $options    = Search::getOptions($itemtype);

         foreach ($search_parameters as $key => $value) {
            foreach ($data_array as $num => $data) {
               if ($num == $value['field']) {
                  foreach ($options as $options_num => $val) {
                     if ($options_num == $num) {
                        $type = $val['datatype'];
                        break;
                     }
                  }

                  // Specific search
                  switch ($type) {
                     case 'datetime':
                        $force_day = false;
                        if (strstr($value['value'], 'BEGIN') || strstr($value['value'], 'LAST')) {
                           $force_day = true;
                        }
                        $value['value'] = Html::computeGenericDateTimeSearch($value['value'], $force_day);

                        if ($value['searchtype'] == 'contains') {
                           $data = Html::convDateTime(date('Y-m-d H:i:s', $data));
                        } else {
                           $data = strtotime(date('Y-m-d H:i', $data));
                           $value['value'] = strtotime(date('Y-m-d H:i', strtotime($value['value'])));
                        }
                        break;
                  }

                  // Compare date
                  $search[$key] = 1;

                  if ($value['searchtype'] == 'equals' && $value['value'] != $data) {
                     $search[$key] = 0;

                  } else if ($value['searchtype'] == 'notequals' && $value['value'] == $data) {
                     $search[$key] = 0;

                  } else if ($value['searchtype'] == 'lessthan' && $data >= $value['value']) {
                     $search[$key] = 0;

                  } else if ($value['searchtype'] == 'morethan' && $data <= $value['value']) {
                     $search[$key] = 0;

                  } else if ($value['searchtype'] == 'contains' && !preg_match('/'.$value['value'].'/', Html::convDateTime(date('Y-m-d H:i:s', $data)))) {
                     $search[$key] = 0;
                  }

                  if ($value['NOT']) {
                     $search[$key] = !$search[$key];
                  }

                  $searchlink[$key] = trim($value['LINK']);
               }
            }
         }

         // Compare each search parameter line
         foreach ($search as $key => $planned) {
            switch ($searchlink[$key]) {
               case 'AND':
                  if (isset($search[$key - 1])) {
                     $search[$key] = ($search[$key] && $search[$key - 1]);
                     $OK = $search[$key];
                  } else {
                     $OK = $search[$key];
                  }
                  break;

               case 'OR':
                  if (isset($search[$key - 1])) {
                     $search[$key] = ($search[$key] || $search[$key - 1]);
                     $OK = $search[$key];
                  } else {
                     $OK = $search[$key];
                  }
                  break;

               default :
                  $OK = $search[$key];
                  break;
            }
         }
      }

      return $OK;
   }

   static function parseData(&$newrow, $itemtype) {
      // Parse datas
      if (!empty($newrow['raw'])) {
         foreach ($newrow['raw'] as $key => $val) {
            // For compatibility keep data at the top for the moment
            //                $newrow[$key] = $val;

            $keysplit = explode('_', $key);

            if (isset($keysplit[1]) && $keysplit[0] == 'ITEM') {
               $j         = $itemtype."_".$keysplit[1];
               $fieldname = 'name';
               if (isset($keysplit[2])) {
                  $fieldname = $keysplit[2];
                  $fkey      = 3;
                  while (isset($keysplit[$fkey])) {
                     $fieldname.= '_'.$keysplit[$fkey];
                     $fkey++;
                  }
               }

               // No Group_concat case
               if (strpos($val, "$$$$") === false) {
                  $newrow[$j]['count'] = 1;

                  if (strpos($val, "$$") === false) {
                     if ($val !== 0 && $val == Search::NULLVALUE) {
                        $newrow[$j][0][$fieldname] = null;
                     } else {
                        $newrow[$j][0][$fieldname] = $val;
                     }
                  } else {
                     $split2                    = Search::explodeWithID("$$", $val);
                     $newrow[$j][0][$fieldname] = $split2[0];
                     $newrow[$j][0]['id']       = $split2[1];
                  }
               } else {
                  if (!isset($newrow[$j])) {
                     $newrow[$j] = [];
                  }
                  $split               = explode("$$$$", $val);
                  $newrow[$j]['count'] = count($split);
                  foreach ($split as $key2 => $val2) {
                     if (strpos($val2, "$$") === false) {
                        $newrow[$j][$key2][$fieldname] = $val2;
                     } else {
                        $split2                  = Search::explodeWithID("$$", $val2);
                        $newrow[$j][$key2]['id'] = $split2[1];
                        if ($split2[0] == Search::NULLVALUE) {
                           $newrow[$j][$key2][$fieldname] = null;
                        } else {
                           $newrow[$j][$key2][$fieldname] = $split2[0];
                        }
                     }
                  }
               }
            }
         }
         if (isset($newrow['raw']['id'])) {
            $newrow['id'] = $newrow['raw']['id'];
         }
      }
   }

}
