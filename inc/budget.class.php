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
 * Class PluginPrintercountersBudget
 *
 * This class allows to manage the budgets
 *
 * @package    Printercounters
 * @author     Ludovic Dupont
 */
class PluginPrintercountersBudget extends CommonDropdown {

   const GET_NO_AMOUNT     = 0;
   const GET_PARENT_AMOUNT = 1;
   const GET_SON_AMOUNT    = 2;
   const ADD_AMOUNT        = 3;

   var $rand = 0;

   static $rightname = 'plugin_printercounters';

   /**
    * Constructor
    *
    * @param type $itemtype
    * @param type $items_id
    */
   public function __construct() {

      $this->setRand();

      parent::__construct();
   }


   static function getTypeName($nb = 0) {
      return __("Budget");
   }

   /**
    * Function sets rand
    */
   public function setRand() {

      $this->rand = mt_rand();
   }

   /**
    * Search function : getDefaultSearch
    *
    * @return string
    */
   function getDefaultSearch() {

      $default_search = [];
      $options  = Search::getCleanedOptions($this->getType());
      foreach ($options as $num => $val) {
         if ($val['table'] == 'glpi_entities' && $val['field'] == 'level') {
             $default_search['sort'] = $num;
            break;
         }
      }
      $default_search['order']    = 'ASC';

      return $default_search;
   }

   /**
    * Search function : addRestriction
    *
    * @return string
    */
   function addRestriction() {

      $options  = Search::getCleanedOptions($this->getType());
      $restriction = '';
      foreach ($options as $num => $val) {
         if ($val['table'] == 'glpi_entities' && $val['field'] == 'id') {
            $restriction .= PluginPrintercountersSearch::addWhere('', 0, $this->getType(),
                                                                  $num, 'under',
                                                                  $_SESSION['glpiactive_entity']);
         }
      }

      return $restriction;
   }

   /**
    * Search function : show record history data
    *
    * @global type $CFG_GLPI
    * @param array $input
    * @param int $output_type
    */
   function showSearchData(PluginPrintercountersSearch $search) {

      // Format data
      list($input, $total) = $this->formatSearchData($search->input);

      // Display recursive data
      $row_num = 1;
      $this->displayRecursiveBudget($input, $search, count($search->input), $row_num, $this->getRootLevel($input));

      // Total
      $row_num++;
      $col_num = 1;
      echo Search::showNewLine($search->output_type);
      echo Search::showItem($search->output_type, '', $col_num, $row_num, "class='tab_bg_1'");
      if ($search->output_type == Search::HTML_OUTPUT) {
         echo Search::showItem($search->output_type, '', $col_num, $row_num, "class='tab_bg_1'");
      }
      echo Search::showItem($search->output_type, "<b>".__('Total')."</b>", $col_num, $row_num, "class='tab_bg_1'");
      echo Search::showItem($search->output_type, "<b>".Html::formatNumber($total['total_amount'],
                                                                           false, 2)."</b>",
                            $col_num, $row_num, "class='tab_bg_1'");
      echo Search::showItem($search->output_type, "<b>".Html::formatNumber($total['total_record_amount'],
                                                                           false, 2)."</b>",
                            $col_num, $row_num, "class='tab_bg_1'");
      echo Search::showItem($search->output_type, "<b>".Html::formatNumber($total['total_usage_rate'])."%</b>",
                            $col_num, $row_num, "class='tab_bg_1'");
      echo Search::showItem($search->output_type, "<b>".Html::formatNumber($total['total_color_rate'])."%</b>",
                            $col_num, $row_num, "class='tab_bg_1'");
      echo Search::showItem($search->output_type, "<b>".Html::formatNumber($total['total_page_number'],
                                                                           false, 0)."</b>",
                            $col_num, $row_num, "class='tab_bg_1'");
      echo Search::showItem($search->output_type, "<b>".Html::formatNumber($total['total_color_page_rate'])."%</b>",
                            $col_num, $row_num, "class='tab_bg_1'");
      echo Search::showItem($search->output_type, '', $col_num, $row_num, "class='tab_bg_1'");
      echo Search::showItem($search->output_type, '', $col_num, $row_num, "class='tab_bg_1'");
      echo Search::showEndLine($search->output_type);
   }

   /**
    * Display recursive budget
    *
    * @param type $input
    * @param type $search
    * @param type $nbLines
    * @param type $row_num
    * @param type $rootLevel
    */
   function displayRecursiveBudget($input, $search, $nbLines, &$row_num = 1, $rootLevel = 0) {

      // Display data
      foreach ($input as $history) {
         if (!isset($history['display']) || (isset($history['display']) && $history['display'])) {

            $tree = "";
            if ($history['budgets_level'] > $rootLevel) {
               $tree = "<div class='printercounters_tree' style='margin-left:".($history['budgets_level'] * (10))."px'></div>";
            }

            $row_num++;
            $col_num = 1;
            echo Search::showNewLine($search->output_type);
            if ($this->canCreate() && $search->output_type == search::HTML_OUTPUT) {
               echo "<td class='center' width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $history['budgets_id']);
               echo "</td>";
            }
            echo Search::showItem($search->output_type, $tree.$history['budget'], $col_num, $row_num);
            echo Search::showItem($search->output_type, $history['entities_name'], $col_num, $row_num);
            echo Search::showItem($search->output_type, Html::formatNumber($history['amount'], false, 2),
                                  $col_num, $row_num);
            echo Search::showItem($search->output_type, Html::formatNumber($history['record_amount'],
                                                                           false, 2),
                                  $col_num, $row_num);
            echo Search::showItem($search->output_type, Html::formatNumber($history['usage_rate'])."%",
                                  $col_num, $row_num);
            echo Search::showItem($search->output_type, Html::formatNumber($history['color_rate'])."%",
                                  $col_num, $row_num);
            echo Search::showItem($search->output_type, Html::formatNumber($history['total_page_number'], false, 0),
                                  $col_num, $row_num);
            echo Search::showItem($search->output_type, Html::formatNumber($history['color_page_rate'])."%",
                                  $col_num, $row_num);
            echo Search::showItem($search->output_type, Html::convDateTime($history['begin_date']),
                                  $col_num, $row_num);
            echo Search::showItem($search->output_type, Html::convDateTime($history['end_date']),
                                  $col_num, $row_num);
            echo Search::showEndLine($search->output_type);
         }

         // Get sons recursively
         if (isset($history['sons']) && !(isset($history['son_display']) && !$history['son_display'])) {
            $this->displayRecursiveBudget($history['sons'], $search, $nbLines, $row_num, $rootLevel);
         }
      }
   }

   /**
    * Get root level of budgets
    *
    * @param type $budgets
    * @return type
    */
   function getRootLevel($budgets) {

      if (isset($budgets[0]['sons'])) {
         $budgets = $budgets[0]['sons'];
      }
      $budgets = reset($budgets);

      return $budgets['budgets_level'];
   }

   /**
    * Function format record history data
    *
    * @param type $input
    * @param type $options
    * @return type
    */
   function formatSearchData($input, $options = []) {

      $params['use_repartition'] = true;

      if (!empty($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      $searchopt = &Search::getOptions($this->getType());

      $output = [];
      $total_all_pages = 0;
      $types  = [];

      foreach ($searchopt as $num => $val) {
         if (is_array($val) && (!isset($val['nosql']) || $val['nosql'] == false)) {
            if ($val['table'] == $this->getTable() && $val['field'] == 'name') {
               $types['budget'] = $num;

            } else if ($val['table'] == $this->getTable() && $val['field'] == 'amount') {
               $types['amount'] = $num;

            } else if ($val['table'] == $this->getTable() && $val['field'] == 'begin_date') {
               $types['begin_date'] = $num;

            } else if ($val['table'] == $this->getTable() && $val['field'] == 'end_date') {
               $types['end_date'] = $num;

            } else if ($val['table'] == 'glpi_entities' && $val['field'] == 'completename') {
               $types['entities_name'] = $num;

            } else if ($val['table'] == 'glpi_entities' && $val['field'] == 'id') {
               $types['entities_id'] = $num;

            } else if ($val['table'] == 'glpi_entities' && $val['field'] == 'level') {
               $types['entities_level'] = $num;

            } else if ($val['table'] == 'glpi_entities' && $val['field'] == 'entities_id') {
               $types['entities_parent'] = $num;

            } else if ($val['table'] == $this->getTable() && $val['field'] == 'id') {
               $types['budgets_id'] = $num;
            }
         }
      }

      if (!empty($input)) {
         $give_item = [];
         $line['raw'] = [];
         foreach ($input as $i => $row) {
            $count = 0;
            $line['raw'] = $row;
            PluginPrintercountersSearch::parseData($line,$this->getType());
            foreach ($searchopt as $num => $val) {
               if (is_array($val) && (!isset($val['nosql']) || $val['nosql'] == false)) {
                  $give_item[$i][$num] = Search::giveItem($this->getType(), $num, $line, $count);
                  $count++;
               }
            }
         }

         foreach ($give_item as $row) {
            if (!empty($row[$types['budgets_id']])) {
               $output[$row[$types['budgets_id']]]['record_amount']     = 0;
               $output[$row[$types['budgets_id']]]['total_page_number'] = 0;
               $output[$row[$types['budgets_id']]]['record_detail']     = [];
               $output[$row[$types['budgets_id']]]['budget']            = $row[$types['budget']];
               $output[$row[$types['budgets_id']]]['budgets_id']        = $row[$types['budgets_id']];
               $output[$row[$types['budgets_id']]]['amount']            = $row[$types['amount']];
               $output[$row[$types['budgets_id']]]['begin_date']        = $row[$types['begin_date']];
               $output[$row[$types['budgets_id']]]['end_date']          = $row[$types['end_date']];
               $output[$row[$types['budgets_id']]]['entities_name']     = $row[$types['entities_name']];
               $output[$row[$types['budgets_id']]]['entities_id']       = $row[$types['entities_id']];
               $output[$row[$types['budgets_id']]]['entities_level']    = $row[$types['entities_level']];
               $output[$row[$types['budgets_id']]]['entities_parent']   = $row[$types['entities_parent']];
            }
         }

         // Get record amount
         list($output, $total) = $this->getRecordsAmountForBudget($output,
                                                                  PluginPrintercountersCountertype_Recordmodel::COLOR,
                                                                  $params['use_repartition']);
         list($output, $total) = $this->computeUsageRate($output, $total);
         list($output, $total) = $this->computeColorRate($output, $total);

         // Set total
         $total['total_usage_rate']      = $total['total_usage_rate'] / count($input);
         $total['total_color_rate']      = $total['total_color_rate'] / count($input);
         $total['total_color_page_rate'] = $total['total_color_page_rate'] / count($input);
      }

      return [$output, $total];
   }


   /**
   * Get records for the budgets of an entity
   *
   * @param type $budgets
   * @param type $oid_type
   * @param type $use_repartition
   * @return type
   */
   function getRecordsAmountForBudget($budgets, $oid_type = null, $use_repartition = true) {

      // Prepare budget data
      $budgets = $this->prepareBudgetData($budgets);

      // Record amount repartition on parent budgets
      $budgets = $this->getRecordAmount($budgets, $oid_type, $use_repartition);

      // Compute total values
      $total['total_page_number']   = $budgets[0]['total_page_number'];
      $total['total_record_amount'] = $budgets[0]['record_amount'];
      $total['total_amount']        = $this->getTotalBudgetAmount($budgets);

      return [$budgets, $total];
   }


   /**
    * Prepare budget data
    *
    * @param type $budgets
    * @return type
    */
   function prepareBudgetData($budgets) {

      foreach ($budgets as &$budget) {
         $budget['begin_date'] = date('Y-m-d H:i:s', strtotime($budget['begin_date']));
         $budget['end_date'] = date('Y-m-d H:i:s', strtotime($budget['end_date']));
      }

      // Set default root budget
      $budgets[0] = ['record_amount'     => 0,
                          'total_page_number' => 0,
                          'budget'            => '',
                          'budgets_id'        => 0,
                          'amount'            => 0,
                          'begin_date'        => 'NULL',
                          'end_date'          => 'NULL',
                          'entities_name'     => '',
                          'entities_id'       => -1,
                          'entities_level'    => -1,
                          'entities_parent'   => 0,
                          'display'           => false];

      // Sort by entity levels ASC
      uasort($budgets, function($a, $b) {
         return ($a["entities_level"] - $b["entities_level"]) + (strcmp($a["entities_name"], $b["entities_name"]));
      });

      // Set recursive budget with entities tree
      list($budgets, $used) = $this->setRecursiveBudget($budgets);

      // Set budget levels
      $budgets = $this->setBudgetLevels($budgets);

      // Fill budget which has same entity
      $budgets = $this->copySameEntitiesBudgets($budgets, $used);

      return $budgets;
   }

   /**
    * Set sons for the budgets with same entities
    *
    * @param type $budgets
    * @param type $used
    * @param type $sameEntities
    * @return type
    */
   function getSameEntities($budgets, $used, $sameEntities = []) {

      foreach ($budgets as $val2) {
         foreach ($used as $val) {
            if ($val['entities_id'] == $val2['entities_id']
                    && $val['budgets_id'] != $val2['budgets_id']
                    && !isset($budgets[$val2['budgets_id']]['sons'])
                    && isset($budgets[$val['budgets_id']]['sons'])) {

               $sameEntities[$val['budgets_id']][] = $val2['budgets_id'];

            }
         }

         // Get sons recursively
         if (isset($val2['sons'])) {
            $sameEntities = $this->getSameEntities($val2['sons'], $used, $sameEntities);
         }
      }

      return $sameEntities;
   }

   /**
    * Copy budget sons if in same entity of another budget
    *
    * @param type $budgets
    * @param type $used
    * @return type
    */
   function copySameEntitiesBudgets($budgets, $used) {

      $sameEntities = $this->getSameEntities($budgets, $used);

      foreach ($sameEntities as $budgetsToCopy => $sameEntity) {
         $data = $this->getBudgetData($budgets, $budgetsToCopy, 'sons');

         $budgets = $this->setBudgetData($budgets, $budgetsToCopy, false, 'son_display');

         end($sameEntity);
         $lastBudgetToCopy = key($sameEntity);
         reset($sameEntity);

         foreach ($sameEntity as $key => $budgets_id) {
            $budgets = $this->setBudgetData($budgets, $budgets_id, $data, 'sons');

            $parent_budget = $sameEntity;
            $parent_budget[] = $budgetsToCopy;
            $budgets = $this->setBudgetData($budgets, $budgets_id, $parent_budget, 'parent_budget', true);

            if ($key != $lastBudgetToCopy) {
               $budgets = $this->setBudgetData($budgets, $budgets_id, false, 'son_display');
            }
         }

      }

      return $budgets;
   }

   /**
    * Set budget data
    *
    * @param type $budgets
    * @param type $records
    * @param type $use_repartition
    * @return type
    */
   function setBudgetData($budgets, $budgets_id, $data, $field, $copyToSons = false) {

      foreach ($budgets as &$budget) {
         if ($budget['budgets_id'] == $budgets_id) {
            if ($copyToSons) {
               if (isset($budget['sons'])) {
                  foreach ($budget['sons'] as $key => &$son) {
                     $son[$field] = $data;
                  }
               }

            } else {
               $budget[$field] = $data;
            }
         }

         // Get sons recursively
         if (isset($budget['sons'])) {
            $budget['sons'] = $this->setBudgetData($budget['sons'], $budgets_id, $data, $field, $copyToSons);
         }
      }

      return $budgets;
   }

   /**
    * Get budget data in recursive array
    *
    * @param type $budgets
    * @param type $budgets_id
    * @param type $field
    * @param type $result
    * @return type
    */
   function getBudgetData($budgets, $budgets_id, $field, $result = []) {

      foreach ($budgets as $budget) {
         if ($budget['budgets_id'] == $budgets_id) {
            $result = $budgets[$budgets_id][$field];
         }

         // Get sons recursively
         if (isset($budget['sons'])) {
            $result = $this->getBudgetData($budget['sons'], $budgets_id, $field, $result);
         }
      }

      return $result;
   }


   /**
    * Get record amount
    *
    * @param type $budgets
    * @param type $records
    * @param type $use_repartition
    * @return type
    */
   function getRecordAmount($budgets, $oid_type, $use_repartition = true) {

      foreach ($budgets as &$budget) {
         if ($oid_type != null) {
            $budget['record_amount_'.$oid_type] = 0;
            $budget['total_page_number_'.$oid_type] = 0;
         }

         // Get records
         $record = new PluginPrintercountersRecord();
         $records = [];
         if (!empty($budget['end_date']) && !empty($budget['begin_date']) && !is_null($budget['entities_id'])) {
            $records = $record->getRecords(0, 'Printer',
                    ['order' => "`date` DESC",
                          'condition' => " AND `glpi_printers`.`entities_id` = ".$budget['entities_id']." 
                                           AND `glpi_plugin_printercounters_records`.`date` >= ADDDATE('".$budget['begin_date']."', INTERVAL 1 DAY) 
                                           AND `glpi_plugin_printercounters_records`.`date` <= ADDDATE('".$budget['end_date']."', INTERVAL 1 DAY)"]);
         }

         $recordResults = [];
         $allItemsId = [];
         if (!empty($records)) {
            foreach ($records as $itemtype => $value) {
               foreach ($value as $items_id => $record) {
                  $allItemsId[] = $items_id;
                  // Record must be sorted by date DESC and items_id
                  uasort($record, function($a, $b) {
                     return (strtotime($b["date"]) - strtotime($a["date"]));
                  });
                  foreach ($record as $key => $val) {
                     $recordResults[$key] = $val;
                  }
               }
            }
         }

         // Get record costs
         if (!empty($recordResults)) {
            $item_billingmodel = new PluginPrintercountersItem_Billingmodel('Printer', $allItemsId);
            $recordResults = $item_billingmodel->computeRecordCost($recordResults, [$oid_type]);

            // All oid results
            $budget['record_amount'] = $recordResults['total_record_cost'];
            $budget['total_page_number'] = $recordResults['total_page_number'];

            // Specific oid results
            if ($oid_type != null) {
               $budget['record_amount_'.$oid_type] = $recordResults['total_oid_type'][$oid_type]['record_cost'];
               $budget['total_page_number_'.$oid_type] = $recordResults['total_oid_type'][$oid_type]['page_number'];
            }

            // Set records on budget according to the dates
            foreach ($recordResults['records'] as $records_id => $record) {
               // Set record details in budget
               $budget['record_detail'][$records_id] = ['entities_id'            => $record['entities_id'],
                                                             'page_number'            => $record['page_number'],
                                                             'items_id'               => $record['items_id'],
                                                             'result'                 => $record['result'],
                                                             'record_cost'            => $record['record_cost'],
                                                             'record_cost_'.$oid_type => $record['record_cost_'.$oid_type],
                                                             'page_number_'.$oid_type => $record['page_number_'.$oid_type],
                                                             'date'                   => $record['date']];
            }
         }

         // Get sons recursively
         if (isset($budget['sons'])) {
            $sons = $this->getRecordAmount($budget['sons'], $oid_type, $use_repartition);
            $budget['sons'] = $sons;

            // Parent repatition of record amounts
            if ($use_repartition) {
               list($amount, $used) = $this->setAmountRepartition($sons, $budget, 'record_cost');
               $budget['record_amount'] += $amount;

               list($amount, $used) = $this->setAmountRepartition($sons, $budget, 'page_number');
               $budget['total_page_number'] += $amount;

               if ($oid_type != null) {
                  list($amount, $used) = $this->setAmountRepartition($sons, $budget, 'record_cost_'.$oid_type);
                  $budget['record_amount_'.$oid_type] += $amount;

                  list($amount, $used) = $this->setAmountRepartition($sons, $budget, 'page_number_'.$oid_type);
                  $budget['total_page_number_'.$oid_type] += $amount;
               }
            }
         }

      }

      return $budgets;
   }

   /**
    * Compute sons record amount recursively
    *
    * @param type $sonsBudgets
    * @param type $parentBudget
    * @param type $records
    * @param type $field
    * @param type $used
    * @return type
    */
   function setAmountRepartition($sonsBudgets, $parentBudget, $field, $used = []) {

      // Display data
      $amount = 0;

      foreach ($sonsBudgets as &$son) {
         // If son is not already compute on parent budget
         if (!isset($used[$parentBudget['budgets_id']]) || !in_array($son['budgets_id'], $used[$parentBudget['budgets_id']])) {

               // Unlimited dates
            if (($parentBudget['end_date'] == 'NULL' && $parentBudget['begin_date'] == 'NULL')) {
               if (!empty($son['record_detail'])) {
                  foreach ($son['record_detail'] as $record) {
                     if ($record['date'] >= $son['begin_date']
                             && $record['date'] <= $son['end_date']) {

                        if (isset($record[$field])) {
                           $amount += $record[$field];
                        }
                     }
                  }
               }

               // Son period in parent period
            } else if ($this->isInPeriod($son, $parentBudget)) {
               // Parent dates between son dates : get sons records with parent date
               if ($parentBudget['begin_date'] >= $son['begin_date']
                       && $parentBudget['end_date'] <= $son['end_date']) {
                  if (!empty($son['record_detail'])) {
                     foreach ($son['record_detail'] as $record) {
                        if ($record['date'] >= $parentBudget['begin_date']
                                && $record['date'] <= $parentBudget['end_date']) {

                           if (isset($record[$field])) {
                              $amount += $record[$field];
                           }
                        }
                     }
                  }

                  // If intersection between son dates and parent dates
               } else if ($son['begin_date'] >= $parentBudget['begin_date']
                       && $son['end_date'] >= $parentBudget['end_date']) {
                  if (!empty($son['record_detail'])) {
                     foreach ($son['record_detail'] as $record) {
                        if ($record['date'] >= $son['begin_date']
                                && $record['date'] <= $parentBudget['end_date']) {

                           if (isset($record[$field])) {
                              $amount += $record[$field];
                           }
                        }
                     }
                  }

                  // If intersection between son dates and parent dates
               } else if ($son['begin_date'] <= $parentBudget['begin_date']
                       && $son['end_date'] <= $parentBudget['begin_date']) {
                  if (!empty($son['record_detail'])) {
                     foreach ($son['record_detail'] as $record) {
                        if ($record['date'] >= $parentBudget['begin_date']
                                && $record['date'] <= $son['end_date']) {

                           if (isset($record[$field])) {
                              $amount += $record[$field];
                           }
                        }
                     }
                  }

                  // Son dates between parent dates : get all son records
               } else if ($son['begin_date'] >= $parentBudget['begin_date']
                       && $son['end_date'] <= $parentBudget['end_date']) {
                  if (!empty($son['record_detail'])) {
                     foreach ($son['record_detail'] as $record) {
                        if ($record['date'] >= $son['begin_date']
                                && $record['date'] <= $son['end_date']) {

                           if (isset($record[$field])) {
                              $amount += $record[$field];
                           }
                        }
                     }
                  }
               }
            }

            $used[$parentBudget['budgets_id']][] = $son['budgets_id'];
         }

         // Get sons recursively
         if (isset($son['sons'])) {
            list($son_amount, $used) = $this->setAmountRepartition($son['sons'], $parentBudget, $field, $used);
            $amount += $son_amount;
         }
      }

      return [$amount, $used];
   }

   /**
    * Transform budget in a recursive array with entities tree
    *
    * @param array $budgets
    * @param int $budgets_id
    * @param array $newOrder
    * @param array $used
    * @return type
    */
   function setRecursiveBudget(&$budgets, $budgets_id = 0, $newOrder = [], $used = []) {

      // Select parent budget where sons can be found
      $selected_budget = [];
      if (empty($budgets_id)) {
         $keys = array_keys($budgets);
         $selected_budget = $budgets[$keys[0]];

      } else {
         foreach ($budgets as $budget) {
            if ($budget['budgets_id'] == $budgets_id) {
               $selected_budget = $budget;
               break;
            }
         }
      }

      // Get array of budget sons
      $dbu           = new DbUtils();
      $entities_sons = $dbu->getSonsOf('glpi_entities', $selected_budget['entities_id']);
      if ($selected_budget['entities_id'] == -1) {
         $entities_sons[0] = 0;
      }
      unset($entities_sons[$selected_budget['entities_id']]);
      $newOrder[$selected_budget['budgets_id']] = $selected_budget;
      $used[$selected_budget['budgets_id']] = $selected_budget;

      // Find sons
      foreach ($budgets as &$budget2) {
         // Son found !
         if (in_array($budget2['entities_id'], $entities_sons) && !in_array($budget2['budgets_id'], array_keys($used))) {
            // Add sons recursively
            $used[$budget2['budgets_id']] = $budget2;
            list($sons, $used) = $this->setRecursiveBudget($budgets, $budget2['budgets_id'], [], $used);
            $keys = array_keys($sons);
            foreach ($sons as &$son) {
               $son['parent_budget'] = [$selected_budget['budgets_id']];
            }
            $newOrder[$selected_budget['budgets_id']]['sons'][$keys[0]] = $sons[$keys[0]];
         }
      }

      return [$newOrder, $used];
   }

   /**
   * Define budget level
   *
   * @param type $budgets
   * @param type $lastlevel
   * @param type $level
   * @return type
   */
   function setBudgetLevels($budgets, $level = -1) {

      $level++;

      foreach ($budgets as &$budget) {
         $budget['budgets_level'] = $level;

         // Get sons recursively
         if (isset($budget['sons'])) {
            $sons = $this->setBudgetLevels($budget['sons'], $level);
            $budget['sons'] = $sons;
         }
      }

      return $budgets;
   }

   /**
    * Compute usage rate recursively
    *
    * @param type $budgets
    * @param type $total
    * @return type
    */
   function computeUsageRate($budgets, $total = []) {

      foreach ($budgets as &$budget) {
         if ($budget['budgets_id'] > 0) {
            $budget['usage_rate'] = 0;

            if ($budget['amount'] > 0) {
               $budget['usage_rate'] = (($budget['record_amount'] / $budget['amount']) * 100);
            }

            if (isset($total['total_usage_rate'])) {
               $total['total_usage_rate'] += $budget['usage_rate'];
            } else {
               $total['total_usage_rate'] = $budget['usage_rate'];
            }
         }

         // Get sons recursively
         if (isset($budget['sons'])) {
            list($sons, $total) = $this->computeUsageRate($budget['sons'], $total);
            $budgets[$budget['budgets_id']]['sons'] = $sons;
         }
      }

      return [$budgets, $total];
   }

   /**
    * Compute color rate recursively
    *
    * @param type $budgets
    * @param type $total
    * @return type
    */
   function computeColorRate($budgets, $total = []) {

      $oid = PluginPrintercountersCountertype_Recordmodel::COLOR;

      foreach ($budgets as &$budget) {
         if ($budget['budgets_id'] > 0) {
            $budget['color_rate']      = 0;
            $budget['color_page_rate'] = 0;

            if (isset($budget['record_amount_'.$oid]) && isset($budget['total_page_number_'.$oid])) {
               // Compute color rate
               if ($budget['record_amount'] > 0) {
                  $budget['color_rate'] = (($budget['record_amount_'.$oid] / $budget['record_amount']) * 100);
               }
               if ($budget['color_rate'] >= 100) {
                  $budget['color_rate'] = 100;
               }

               // Compute color page rate
               if ($budget['total_page_number'] > 0) {
                  $budget['color_page_rate'] = ($budget['total_page_number_'.$oid] / $budget['total_page_number']) * 100;
               }
               if ($budget['color_page_rate'] >= 100) {
                  $budget['color_page_rate'] = 100;
               }

               // Total color rate
               if (isset($total['total_color_rate'])) {
                  $total['total_color_rate'] += $budget['color_rate'];
               } else {
                  $total['total_color_rate'] = $budget['color_rate'];
               }

               // Total color page rate
               if (isset($total['total_color_page_rate'])) {
                  $total['total_color_page_rate'] += $budget['color_page_rate'];
               } else {
                  $total['total_color_page_rate'] = $budget['color_page_rate'];
               }
            }
         }

         // Get sons recursively
         if (isset($budget['sons'])) {
            list($sons, $total) = $this->computeColorRate($budget['sons'], $total);
            $budgets[$budget['budgets_id']]['sons'] = $sons;
         }
      }

      return [$budgets, $total];
   }

   /**
    * Compute sons amount recursively
    *
    * @param type $sonsBudgets
    * @param type $field
    * @param type $amount
    * @param type $rootLevel
    * @param type $son_display
    * @return type
    */
   function computeSonsAmount($sonsBudgets, $field, $amount = 0, $rootLevel = 0, $son_display = false) {

      if (!empty($sonsBudgets)) {
         foreach ($sonsBudgets as $budget) {
            if ($budget['budgets_level'] > $rootLevel && $budget['budgets_id'] != 0) {
               $amount += $budget[$field];
            }

            // Get sons recursively
            if (isset($budget['sons'])) {
               switch ($field) {
                  case 'amount';
                     if ((isset($budget['son_display']) && $son_display) || (!isset($budget['son_display']))) {
                        $amount = $this->computeSonsAmount($budget['sons'], $field, $amount, $rootLevel);
                     }
                     break;
                  default :
                     $amount = $this->computeSonsAmount($budget['sons'], $field, $amount, $rootLevel);
                     break;
               }
            }
         }
      }

      return $amount;
   }

   /**
   * Compute sons amount recursively
   *
   * @param type $parentBudgets
   * @param type $field
   * @return type
   */
   function computeParentsAmount($parentBudgets, $field) {

      $amount = 0;

      if (!empty($parentBudgets)) {
         foreach ($parentBudgets as $budget) {
            $amount += $budget[$field];
         }
      }

      return $amount;
   }

   /**
    * Merge budget data recursively
    *
    * @param type $usage_rate
    * @param type $color_rate
    * @return type
    */
   function mergeData($usage_rate, $color_rate) {

      // Display data
      foreach ($usage_rate as &$budget) {
         $budget['usage_rate']        = $budget['usage_rate'];
         $budget['total_page_number'] = $budget['total_page_number'];
         $budget['color_rate']        = $color_rate[$budget['budgets_id']]['color_rate'];
         $budget['color_page_rate']   = $color_rate[$budget['budgets_id']]['color_page_rate'];

         // Get sons recursively
         if (isset($budget['sons'])) {
            $usage_rate[$budget['budgets_id']]['sons'] = $this->mergeData($budget['sons'], $color_rate[$budget['budgets_id']]['sons']);
         }
      }

      return $usage_rate;
   }

   /**
   * getItems
   *
   * @global type $DB
   * @param type $condition
   * @return type
   */
   function getItems($condition = null) {
      global $DB;

      $output   = [];
      $dbu      = new DbUtils();
      $itemjoin = $dbu->getTableForItemType('Entity');

      $query = "SELECT `".$this->getTable()."`.`name`, 
                       `".$this->getTable()."`.`id` as budgets_id, 
                       `".$this->getTable()."`.`name` as budget,
                       `".$this->getTable()."`.`amount`,
                       `".$this->getTable()."`.`begin_date`,
                       `".$this->getTable()."`.`end_date`,
                       `".$this->getTable()."`.`amount`,
                       `".$this->getTable()."`.`entities_id`,
                       `".$itemjoin."`.`level` as entities_level,
                       `".$itemjoin."`.`entities_id` as entities_parent,
                       `".$itemjoin."`.`completename` as entities_name
                          
          FROM ".$this->getTable()."
          LEFT JOIN `".$itemjoin."` 
             ON (`".$this->getTable()."`.`entities_id` = `".$itemjoin."`.`id`)
          WHERE 1 $condition";

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $output[$data['budgets_id']] = $data;
         }
      }

      return $output;
   }

   /**
    * Provides search options configuration. Do not rely directly
    * on this, @see CommonDBTM::searchOptions instead.
    *
    * @since 9.3
    *
    * This should be overloaded in Class
    *
    * @return array a *not indexed* array of search options
    *
    * @see https://glpi-developer-documentation.rtfd.io/en/master/devapi/search.html
    **/
   public function rawSearchOptions() {

      $tab[] = [
         'id'                 => '20',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Budget'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '21',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'datatype'           => 'dropdown',
         'name'               => __('Entity'),
         'massiveaction'      => true,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '22',
         'table'              => $this->getTable(),
         'field'              => 'amount',
         'name'               => __('Amount', 'printercounters'),
         'massiveaction'      => false,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '23',
         'table'              => 'glpi_plugin_printercounters_records',
         'field'              => 'record_amount',
         'name'               => _n('Record amount', 'Records amount', 1, 'printercounters'),
         'massiveaction'      => false,
         'nosearch'           => true,
         'nosql'              => true,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '24',
         'table'              => $this->getTable(),
         'field'              => 'usage_rate',
         'name'               => __('Usage rate', 'printercounters'),
         'datatype'           => 'number',
         'massiveaction'      => false,
         'nosearch'           => true,
         'nosql'              => true,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '25',
         'table'              => $this->getTable(),
         'field'              => 'color_rate',
         'name'               => __('Color rate', 'printercounters'),
         'datatype'           => 'number',
         'massiveaction'      => false,
         'nosearch'           => true,
         'nosql'              => true,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '26',
         'table'              => $this->getTable(),
         'field'              => 'total_page_number',
         'name'               => __('Total page number', 'printercounters'),
         'datatype'           => 'number',
         'massiveaction'      => false,
         'nosearch'           => true,
         'nosql'              => true,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '27',
         'table'              => $this->getTable(),
         'field'              => 'color_page_rate',
         'name'               => __('Color page rate', 'printercounters'),
         'datatype'           => 'number',
         'massiveaction'      => false,
         'nosearch'           => true,
         'nosql'              => true,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '28',
         'table'              => $this->getTable(),
         'field'              => 'begin_date',
         'name'               => __('Begin date'),
         'datatype'           => 'datetime',
         'massiveaction'      => true,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '29',
         'table'              => $this->getTable(),
         'field'              => 'end_date',
         'name'               => __('End date'),
         'datatype'           => 'datetime',
         'massiveaction'      => true,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '30',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'datatype'           => 'number',
         'massiveaction'      => false,
         'nosearch'           => true,
         'nodisplay'          => true,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '31',
         'table'              => 'glpi_entities',
         'field'              => 'id',
         'name'               => __('ID'),
         'massiveaction'      => false,
         'nosearch'           => true,
         'nodisplay'          => true,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '32',
         'table'              => 'glpi_entities',
         'field'              => 'level',
         'name'               => __('Level'),
         'massiveaction'      => false,
         'nosearch'           => true,
         'nodisplay'          => true,
         'nosort'             => true
      ];

      $tab[] = [
         'id'                 => '33',
         'table'              => 'glpi_entities',
         'field'              => 'entities_id',
         'name'               => __('Parent'),
         'massiveaction'      => false,
         'nosearch'           => true,
         'nodisplay'          => true,
         'nosort'             => true
      ];

      return $tab;
   }

   /**
   * Get additional fields in form
   *
   * @return array
   */
   function getAdditionalFields() {

      $tab = [
                   ['name'  => 'amount',
                         'label' => __('Amount', 'printercounters'),
                         'type'  => 'text',
                         'list'  => true],
                   ['name'  => 'begin_date',
                         'label' => __('Begin date'),
                         'type'  => 'datetime',
                         'list'  => true],
                   ['name'  => 'end_date',
                         'label' => __('End date'),
                         'type'  => 'datetime',
                         'list'  => true],
                   ];

      return $tab;
   }

   /**
   * Form header
   */
   function displayHeader() {
      Html::header($this->getTypeName(), '', "tools", "pluginprintercountersmenu", "budget");
   }

   /**
    * Actions done at the end of the getEmpty function
    *
    * @return nothing
   **/
   function post_getEmpty() {
      // Set session saved if exists
      $this->setSessionValues();
   }

   /**
   * Set session values in object
   *
   * @return type
   */
   function setSessionValues() {
      if (isset($_SESSION['plugin_printercounters']['budget']) && !empty($_SESSION['plugin_printercounters']['budget'])) {
         foreach ($_SESSION['plugin_printercounters']['budget'] as $key => $val) {
            $this->fields[$key] = $val;
         }
      }
      unset($_SESSION['plugin_printercounters']['budget']);
   }

   /**
   * Actions done before add
   *
   * @param type $input
   * @return type
   */
   function prepareInputForAdd($input) {
      if (!$this->checkMandatoryFields($input) || !$this->checkBudget($input)) {
         $_SESSION['plugin_printercounters']['budget'] = $input;
         return false;
      }

      return $input;
   }

   /**
   * Actions done before update
   *
   * @param type $input
   * @return type
   */
   function prepareInputForUpdate($input) {
      if (!$this->checkMandatoryFields($input) || !$this->checkBudget($input)) {
         return false;
      }

      return $input;
   }

   /**
   * Check if budget dates are not already used
   *
   * @param type $input
   * @return boolean
   */
   function checkBudget($input) {

      $condition = "";
      if (isset($input['id'])) {
         $condition .= " AND `".$this->getTable()."`.`id`!=".$input['id'];
      }

      $budgets = $this->getItems($condition);

      $entity = new Entity();
      $entity->getFromDB($input['entities_id']);
      $input['entities_name'] = $entity->fields['completename'];
      $input['entities_level'] = $entity->fields['level'];
      $input['budgets_id'] = (isset($input['id'])) ? $input['id'] : -1;

      // Check if budget not already exists for this entity
      foreach ($budgets as $value) {
         if ($this->isInPeriod($input, $value)
                 && $value['entities_id'] == $input['entities_id']) {

            Session::addMessageAfterRedirect(sprintf(__('The budget period %s already exists', 'printercounters'), Html::convDate($input['begin_date']).' - '.Html::convDate($input['end_date'])), true, ERROR);
            return false;
         }
      }

      $budgets[] = $input;

      // Prepare budget data
      $budgets = $this->prepareBudgetData($budgets);

      // Check sons amount exceeding

      return $this->checkParentAmount($budgets, $input['budgets_id']);
   }

   /**
    * Check if sons amount not exceed parent budget amount
    *
    * @global type $CFG_GLPI
    * @param type $budgets
    * @param type $budgets_id
    * @param type $parent_budget
    * @param type $result
    * @param type $rootLevel
    * @return type
    */
   function checkParentAmount($budgets, $budgets_id = 0, $parent_budget = [], $result = true, $rootLevel = 0) {

      $amount = 0;
      foreach ($budgets as $budget) {
         if (!empty($parent_budget)) {
            $rootLevel = $this->getRootLevel([$parent_budget['budgets_id'] => $parent_budget]);
         }
         if ($budgets_id == $budget['budgets_id'] && $this->isInPeriod($budget, $parent_budget)) {
            $amount = $this->computeSonsAmount([$parent_budget['budgets_id'] => $parent_budget], 'amount', 0, $rootLevel, true);
            if ($amount > $parent_budget['amount'] && $parent_budget['amount'] > 0) {
               $link = "<a href='".Toolbox::getItemTypeFormURL('PluginPrintercountersBudget')."?id=".$parent_budget['budgets_id']."'>".$parent_budget['name']." (".$parent_budget['amount'].")</a>";
               Session::addMessageAfterRedirect(__("The sub-entities budget amount exceed the parent budget", 'printercounters')." : $link ", true, ERROR);
               $result = false;
            }
         }

         // Get sons recursively
         if (isset($budget['sons'])) {
            $result = $this->checkParentAmount($budget['sons'], $budgets_id, $budget, $result, $rootLevel);
         }
      }

      return $result;
   }

   /**
    * Check intersection between 2 dates
    *
    * @param type $dates1
    * @param type $dates2
    * @return boolean
    */
   function isInPeriod($dates1, $dates2) {

      if (isset($dates1['begin_date']) && isset($dates1['begin_date'])) {
         if (!(($dates1['begin_date'] < $dates2['begin_date'] && $dates1['end_date'] < $dates2['begin_date'])
                 || ($dates1['begin_date'] > $dates2['end_date'] && ($dates1['end_date'] > $dates2['end_date'])))) {

            return true;
         }
      }
   }

   /**
    * Get budget amounts recursively
    *
    * @param type $budgets
    * @return type
    */
   function getTotalBudgetAmount($budgets) {

      $budgets = $this->getNonRecursiveBudget($budgets);

      // Sort by entity levels ASC
      uasort($budgets, function($a, $b) {
         return ($a["budgets_level"] - $b["budgets_level"]);
      });

      $total = [];

      foreach ($budgets as $budget) {
         if (isset($total['amount'])) {
            switch ($this->canAddAmount($budget, $budgets, $total)) {
               case self::ADD_AMOUNT:
                  $total['amount'] += $budget['amount'];
                  $total['last_budget_added'] = $budget['budgets_id'];
                  break;

               case self::GET_SON_AMOUNT:
                  $total['amount'] = $budget['amount'];
                  $total['last_budget_added'] = $budget['budgets_id'];
                  break;
            }

         } else {
            $total['amount'] = $budget['amount'];
            $total['last_budget_added'] = $budget['budgets_id'];
         }
      }

      return $total['amount'];
   }

   /**
    * Check if a date can be added in budget sample
    *
    * @param type $budget
    * @param type $budgets
    * @param type $total
    * @return int
    */
   function canAddAmount($budget, $budgets, $total) {

      $parents = [];
      if (isset($budgets[$budget['budgets_id']]['parent_budget'])) {
         $parents = $this->getParentTree($budgets, $budget['budgets_id']);
      }

      if (empty($parents)) {
         $parents = [0];
      }

      $last   = $total['last_budget_added'];
      $amount = $total['amount'];

      if (!empty($budgets)) {
         // Is last added budget a parent ?
         if (in_array($last, $parents)
                 && $budget['amount'] >= $amount) {

            return self::GET_SON_AMOUNT;

            // Is last added budget in same level ?
         } else if ($budget['budgets_id'] != $budgets[$last]['budgets_id']
                 && $budget['budgets_level'] == $budgets[$last]['budgets_level']) {

            return self::ADD_AMOUNT;
         }
      }

      return 0;
   }

   /**
    * Set recursive budget to non recursive
    *
    * @param type $recursiveBudget
    * @param type $unrecursiveBudget
    * @return type
    */
   function getNonRecursiveBudget($recursiveBudget, $unrecursiveBudget = []) {

      if (!empty($recursiveBudget)) {
         foreach ($recursiveBudget as $value) {
            $saved = $value;
            unset($saved['sons']);
            unset($saved['record_detail']);
            $unrecursiveBudget[$saved['budgets_id']] = $saved;

            // search in sons
            if (isset($value['sons'])) {
               $unrecursiveBudget = $this->getNonRecursiveBudget($value['sons'], $unrecursiveBudget);
            }
         }
      }

      return $unrecursiveBudget;
   }

   /**
    * Get budget ancestors
    *
    * @param type $budgets
    * @param type $budgets_id
    * @param type $budgets_ancestors
    * @return type
    */
   function getParentTree($budgets, $budgets_id, $budgets_ancestors = []) {

      $parents = null;
      if (isset($budgets[$budgets_id]['parent_budget'])) {
         $parents = $budgets[$budgets_id]['parent_budget'];
      }

      if (!empty($budgets) && !empty($parents)) {
         foreach ($budgets as $value) {
            if ($value['budgets_id'] != $budgets_id && $value['budgets_id'] != 0) {
               foreach ($parents as $parent) {
                  if ($parent == $value['budgets_id']) {
                     $budgets_ancestors[] = $value['budgets_id'];

                     // Search in parents
                     if (isset($value['parent_budget'])) {
                        $budgets_ancestors = $this->getParentTree($budgets, $value['budgets_id'], $budgets_ancestors);
                     }
                  }
               }
            }
         }
      }

      return array_unique($budgets_ancestors);
   }

   /**
   * Check mandatory fields
   *
   * @param type $input
   * @return boolean
   */
   function checkMandatoryFields(&$input) {
      $msg     = [];
      $checkKo = false;

      $mandatory_fields = ['amount'     => __('Amount', 'printercounters'),
                                'begin_date' => __('Begin date'),
                                'end_date'   => __('End date')];

      foreach ($input as $key => $value) {
         if (array_key_exists($key, $mandatory_fields)) {
            switch ($key) {
               case 'begin_date' : case 'end_date' :
                     if (isset($input['begin_date'])
                          && isset($input['end_date'])
                          && strtotime($input['begin_date']) > strtotime($input['end_date'])) {
                        Session::addMessageAfterRedirect(__("Begin date cannot be higher than end date", 'printercounters'), true, ERROR);
                        return false;
                     }
                     if (empty($value) || $value == 'NULL') {
                        $msg[] = $mandatory_fields[$key];
                        $checkKo = true;
                        unset($input[$key]);
                     }
                  break;
               default :
                  if (empty($value)) {
                     $msg[] = $mandatory_fields[$key];
                     $checkKo = true;
                  }
                  break;
            }
         }
      }

      if ($checkKo) {
         Session::addMessageAfterRedirect(sprintf(__("Mandatory fields are not filled. Please correct: %s"), implode(', ', $msg)), true, ERROR);
         return false;
      }

      return true;
   }

   /**
    * Get the standard massive actions which are forbidden
    *
    * @return an array of massive actions
   **/
   function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'merge';

      return $forbidden;
   }

}
