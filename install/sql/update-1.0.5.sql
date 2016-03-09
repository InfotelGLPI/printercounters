-- Toner level
ALTER TABLE `glpi_plugin_printercounters_recordmodels` ADD `enable_toner_level` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_printercounters_recordmodels` ADD `enable_printer_info` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_printercounters_configs` ADD `enable_toner_alert` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_printercounters_configs` ADD `toner_alert_repeat` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_printercounters_configs` ADD `toner_treshold` int(11) NOT NULL DEFAULT '0';

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_additionals_datas'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_additionals_datas` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) default NULL,
   `type` varchar(255) default NULL,
   `sub_type` varchar(255) default NULL,
   `value` varchar(255) default NULL,
   `plugin_printercounters_items_recordmodels_id` int(11) NOT NULL default '0',
   PRIMARY KEY (`id`),
   UNIQUE KEY `unicity` (`plugin_printercounters_items_recordmodels_id`, `sub_type`),
   KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
