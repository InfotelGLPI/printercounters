<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 printercounters plugin for GLPI
 Copyright (C) 2014-2016 by the printercounters Development Team.

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

// Class NotificationTarget
class PluginPrintercountersNotificationTargetAdditional_Data extends NotificationTarget {

   const AUTHOR                    = 30;
   const AUTHOR_GROUP              = 31;
   const DELIVERY_USER             = 32;
   const DELIVERY_GROUP            = 33;
   const SUPERVISOR_AUTHOR_GROUP   = 34;
   const SUPERVISOR_DELIVERY_GROUP = 35;
   const TONER_ALERT      = 'toner_alert';
   const TONER_ALERT_NAME = 'Toner level alert';

   static $itemtype = 'PluginPrintercountersAdditional_Data';

   /**
    * Get events
    */
   function getEvents() {

      return array(self::TONER_ALERT => __("Toner level alert", "printercounters"));
   }

   /**
    * Get datas for template
    * 
    * @global type $CFG_GLPI
    * @param type $event
    * @param type $options
    */
   function getDatasForTemplate($event, $options = array()) {
      global $CFG_GLPI;
  
      $events                                    = $this->getAllEvents();
      $this->datas['##printercounters.action##'] = $events[$event];

      foreach ($options['items'] as $id => $item) {
         $tmp                                                 = array();
         $tmp['##printercountersadditionaldatas.name##']     = $item['name'];
         $tmp['##printercountersadditionaldatas.type##']     = $item['type'];
         $tmp['##printercountersadditionaldatas.sub_type##'] = $item['sub_type'];
         switch ($event) {
            case self::TONER_ALERT:
               $tmp['##printercountersadditionaldatas.value##'] = $item['value']." %";
               break;
            default :
               $tmp['##printercountersadditionaldatas.value##'] = $item['value'];
               break;
         }
         $this->datas['printercountersadditionaldatas'][] = $tmp;
      }

      $this->getTags();
      foreach ($this->tag_descriptions[NotificationTarget::TAG_LANGUAGE] as $tag => $values) {
         if (!isset($this->datas[$tag])) {
            $this->datas[$tag] = $values['label'];
         }
      }
      
      switch ($event) {
         case self::TONER_ALERT:
            $this->datas['##lang.printercountersadditionaldatas.action##'] = __("Toner level alert", "printercounters");
            break;
         default :
            $this->datas['##lang.printercountersadditionaldatas.action##'] = "";
            break;
      }
      
      $item = getItemForItemtype($options['itemtype']);
      $item->getFromDB($options['items_id']);

      $this->datas['##printercountersadditionaldatas.itemlink##']      = $this->formatURL('', strtolower($item->getType())."_".$item->getField("id")."_PluginPrintercountersItem_Recordmodel$1");
      $this->datas['##printercountersadditionaldatas.itemname##']      = $item->getField('name');
      if ($_SESSION['glpiis_ids_visible']) {
          $this->datas['##printercountersadditionaldatas.itemname##'] .= " (".$item->getField('id').")";
      }
      
      
      $this->datas['##lang.printercountersadditionaldatas.itemlink##'] = __("Item link", "printercounters");
      $this->datas['##lang.printercountersadditionaldatas.itemname##'] = __("Item name", "printercounters");
      $this->datas['##lang.printercountersadditionaldatas.name##']     = __("Name");
      $this->datas['##lang.printercountersadditionaldatas.value##']    = __("Value");
      $this->datas['##lang.printercountersadditionaldatas.type##']     = _n("Type", "Types", 1);
      $this->datas['##lang.printercountersadditionaldatas.sub_type##'] = __("Sub-type", "printercounters");
   }

   /**
    * Get tags
    */
   function getTags() {

      $tags = array('printercountersadditionaldatas.name'          => __("Name"),
                    'printercountersadditionaldatas.value'         => __("Value"),
                    'printercountersadditionaldatas.type'          => _n("Type", "Types", 1),
                    'printercountersadditionaldatas.sub_type'      => __("Sub-type", "printercounters"),
                    'printercountersadditionaldatas.itemlink'      => __("Item link", "printercounters"),
                    'printercountersadditionaldatas.itemname'      => __("Item name", "printercounters"),
                    'lang.printercountersadditionaldatas.itemlink' => __("Label")." : ".__("Item link", "printercounters"),
                    'lang.printercountersadditionaldatas.itemname' => __("Label")." : ".__("Item name", "printercounters"),
                    'lang.printercountersadditionaldatas.name'     => __("Label")." : ".__("Name"),
                    'lang.printercountersadditionaldatas.value'    => __("Label")." : ".__("Value"),
                    'lang.printercountersadditionaldatas.type'     => __("Label")." : "._n("Type", "Types", 1),
                    'lang.printercountersadditionaldatas.sub_type' => __("Label")." : ".__("Sub-type", "printercounters"));

      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag'   => $tag,
                                   'label' => $label,
                                   'value' => true));
      }

      asort($this->tag_descriptions);
   }

   /**
    * Install notifications
    */
   static function install() {
      global $DB;

      $template     = new NotificationTemplate();
      $templates_id = false;

      $query_id = "SELECT `id`
                   FROM `glpi_notificationtemplates`
                   WHERE `itemtype`='".self::$itemtype."'
                   AND `name` = '".self::TONER_ALERT_NAME."'";

      $result = $DB->query($query_id) or die($DB->error());
      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $templates_id = $template->add(array('name'     => self::TONER_ALERT_NAME,
                                              'itemtype' => self::$itemtype,
                                              'date_mod' => $_SESSION['glpi_currenttime'],
                                              'comment'  => '',
                                              'css'      => ''));
      }

      if ($templates_id) {
         $translation = new NotificationTemplateTranslation();
         if (!countElementsInTable($translation->getTable(), "`notificationtemplates_id`='$templates_id'")) {
            $tmp                             = array();
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']                 = '';
            $tmp['subject']                  = '##lang.printercountersadditionaldatas.action## : ##lang.printercountersadditionaldatas.name##';
            $tmp['content_text']             = '##printercountersadditionaldatas.itemlink## : 
##.printercountersadditionaldatas.itemname##
##lang.printercountersadditionaldatas.action## 
##FOREACHprintercountersadditionaldatas##
##lang.printercountersadditionaldatas.name## : ##printercountersadditionaldatas.name##
##lang.printercountersadditionaldatas.type## : ##printercountersadditionaldatas.type##
##lang.printercountersadditionaldatas.sub_type## : ##printercountersadditionaldatas.sub_type##
##lang.printercountersadditionaldatas.value## : ##printercountersadditionaldatas.value##
##ENDFOREACHprintercountersadditionaldatas##';
            
            $tmp['content_html']             = "<p>##printercountersadditionaldatas.itemlink## :</p>
<p>##printercountersadditionaldatas.itemname##</p>
<p>##lang.printercountersadditionaldatas.action##</p>
<p>##FOREACHprintercountersadditionaldatas##</p>
<p>##lang.printercountersadditionaldatas.name## : ##printercountersadditionaldatas.name##</p>
<p>##lang.printercountersadditionaldatas.type## : ##printercountersadditionaldatas.type##</p>
<p>##lang.printercountersadditionaldatas.sub_type## : ##printercountersadditionaldatas.sub_type##</p>
<p>##lang.printercountersadditionaldatas.value## : ##printercountersadditionaldatas.value##</p>
<p>##ENDFOREACHprintercountersadditionaldatas##</p>";
            $translation->add($tmp);
         }

         $notifs       = array(self::TONER_ALERT_NAME => self::TONER_ALERT);
         $notification = new Notification();
         foreach ($notifs as $label => $name) {
            if (!countElementsInTable("glpi_notifications", "`itemtype`='".self::$itemtype."' AND `event`='$name'")) {
               $tmp = array('name'                     => $label,
                   'entities_id'              => 0,
                   'itemtype'                 => self::$itemtype,
                   'event'                    => $name,
                   'mode'                     => 'mail',
                   'comment'                  => '',
                   'is_recursive'             => 1,
                   'is_active'                => 1,
                   'date_mod'                 => $_SESSION['glpi_currenttime'],
                   'notificationtemplates_id' => $templates_id);
               $notification->add($tmp);
            }
         }
      }
   }

   /**
    * Uninstall notifications
    */
   static function uninstall() {
      global $DB;

      $notif = new Notification();

      foreach (array(self::TONER_ALERT) as $event) {
         $options = array('itemtype' => self::$itemtype,
                          'event'    => $event,
                          'FIELDS'   => 'id');
         foreach ($DB->request('glpi_notifications', $options) as $data) {
            $notif->delete($data);
         }
      }

      //templates
      $template    = new NotificationTemplate();
      $translation = new NotificationTemplateTranslation();
      $options     = array('itemtype' => self::$itemtype,
                           'FIELDS'   => 'id');
      foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
         $options_template = array('notificationtemplates_id' => $data['id'],
                                   'FIELDS'                   => 'id');
         foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template) as $data_template) {
            $translation->delete($data_template);
         }
         $template->delete($data);
      }
   }
   
      /**
    * @since version 0.84
    *
    * @param $usertype
    * @param $redirect
   **/
   function formatURL($type, $redirect) {
      global $CFG_GLPI;

      return urldecode($CFG_GLPI["url_base"]."/index.php?redirect=$redirect");
   }

//   /**
//    * Get additionnals targets for Tickets
//    */
//   function getAdditionalTargets($event = '') {
//
//      $this->addTarget(self::AUTHOR, __("Author"));
//      $this->addTarget(self::AUTHOR_GROUP, __("Author group", "printercounters"));
//      $this->addTarget(self::DELIVERY_USER, __("Recipient"));
//      $this->addTarget(self::DELIVERY_GROUP, __("Recipient group", "printercounters"));
//      $this->addTarget(self::SUPERVISOR_AUTHOR_GROUP, __("Manager")." ".__("Author group", "printercounters"));
//      $this->addTarget(self::SUPERVISOR_DELIVERY_GROUP, __("Manager")." ".__("Recipient group", "printercounters"));
//   }
//
//   /**
//    * Get specific targets
//    */
//   function getSpecificTargets($data, $options) {
//      switch ($data['items_id']) {
//         case self::AUTHOR:
//            $this->getUserByField("users_id");
//            break;
//         case self::DELIVERY_USER:
//            $this->getUserByField("users_id_delivery");
//            break;
//         case self::AUTHOR_GROUP:
//            $this->getAddressesByGroup(0, $this->obj->fields['groups_id']);
//            break;
//         case self::DELIVERY_GROUP:
//            $this->getAddressesByGroup(0, $this->obj->fields['groups_id_delivery']);
//            break;
//         case self::SUPERVISOR_AUTHOR_GROUP:
//            $this->getAddressesByGroup(1, $this->obj->fields['groups_id']);
//            break;
//         case self::SUPERVISOR_DELIVERY_GROUP:
//            $this->getAddressesByGroup(1, $this->obj->fields['groups_id_delivery']);
//            break;
//      }
//   }

}

?>