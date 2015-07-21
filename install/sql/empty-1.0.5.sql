-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_profiles'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_profiles`;
CREATE TABLE `glpi_plugin_printercounters_profiles` (
   `id` int(11) NOT NULL auto_increment,
   `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   `printercounters` char(1) collate utf8_unicode_ci default NULL,
   `update_records` tinyint(1) NOT NULL default '0',
   `add_lower_records` tinyint(1) NOT NULL default '0',
   `snmpset` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   KEY `profiles_id` (`profiles_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_configs'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_configs` (
   `id` int(11) NOT NULL auto_increment,
   `nb_errors_ticket` int(11) NOT NULL default '10',
   `nb_errors_delay_ticket` int(11) NOT NULL default '864000',
   `no_record_delay_ticket` int(11) NOT NULL default '864000',
   `items_status` longtext COLLATE utf8_unicode_ci,
   `tickets_category` int(11) NOT NULL default '0',
   `tickets_content` text COLLATE utf8_unicode_ci,
   `add_item_group` char(1) collate utf8_unicode_ci default NULL,
   `add_item_user` char(1) collate utf8_unicode_ci default NULL,
   `disable_autosearch` tinyint(1) NOT NULL default '0',
   `set_first_record` tinyint(1) NOT NULL DEFAULT '0',
   `enable_toner_alert` tinyint(1) NOT NULL DEFAULT '0',
   `toner_alert_repeat` int(11) NOT NULL DEFAULT '0',
   `toner_treshold` int(11) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_printercounters_configs` VALUES ('1', '10', '864000', '864000', '', '0', '', '0', '0', '0', '0', '0', '0', '0');

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_countertypes'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_countertypes` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) default NULL,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` int(11) NOT NULL default '0',
   `comment` text COLLATE utf8_unicode_ci,
   PRIMARY KEY (`id`),
   KEY `entities_id` (`entities_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_countertypes_recordmodels'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_countertypes_recordmodels` (
   `id` int(11) NOT NULL auto_increment,
   `oid` varchar(255) default NULL,
   `oid_type` int(11) NOT NULL default '0',
   `plugin_printercounters_recordmodels_id` int(11) NOT NULL default '0',
   `plugin_printercounters_countertypes_id` int(11) NOT NULL default '0',
   PRIMARY KEY (`id`),
   UNIQUE KEY `unicity` (`plugin_printercounters_recordmodels_id`, `plugin_printercounters_countertypes_id`),
   KEY `oid_type` (`oid_type`),
   KEY `plugin_printercounters_recordmodels_id` (`plugin_printercounters_recordmodels_id`),
   KEY `plugin_printercounters_countertypes_id` (`plugin_printercounters_countertypes_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_recordmodels'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_recordmodels` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) default NULL,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL default '0',
   `mac_address_conformity` tinyint(1) NOT NULL default '0',
   `sysdescr_conformity` tinyint(1) NOT NULL default '0',
   `serial_conformity` tinyint(1) NOT NULL default '0',
   `enable_toner_level` tinyint(1) NOT NULL DEFAULT '0',
   `enable_printer_info` tinyint(1) NOT NULL DEFAULT '0',
   `is_template` tinyint(1) NOT NULL default '0',
   `template_name` varchar(255) default NULL,
   `comment` text COLLATE utf8_unicode_ci,
   PRIMARY KEY (`id`),
   KEY `entities_id` (`entities_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_sysdescrs'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_sysdescrs` (
   `id` int(11) NOT NULL auto_increment,
   `sysdescr` varchar(255) default NULL,
   `plugin_printercounters_recordmodels_id` int(11) NOT NULL default '0',
   PRIMARY KEY (`id`),
   KEY `plugin_printercounters_recordmodels_id` (`plugin_printercounters_recordmodels_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_items_recordmodels'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_items_recordmodels` (
   `id` int(11) NOT NULL auto_increment,
   `nb_retries` int(11) NOT NULL default '0',
   `max_timeout` int(11) NOT NULL default '1',
   `enable_automatic_record` tinyint(1) NOT NULL default '0',
   `periodicity` int(11) NOT NULL default '86400',
   `items_id` int(11) NOT NULL default '0',
   `itemtype` varchar(255) default NULL,
   `active_mutex` datetime default NULL,
   `process_id` int(11) NOT NULL default '0',
   `global_tco` decimal(20,4) DEFAULT '0.0000',
   `plugin_printercounters_recordmodels_id` int(11) NOT NULL default '0',
   `plugin_printercounters_snmpauthentications_id` int(11) NOT NULL default '0',
   PRIMARY KEY (`id`),
   UNIQUE KEY `unicity` (`plugin_printercounters_recordmodels_id`,`items_id`, `itemtype`),
   KEY `plugin_printercounters_recordmodels_id` (`plugin_printercounters_recordmodels_id`),
   KEY `plugin_printercounters_snmpauthentications_id` (`plugin_printercounters_snmpauthentications_id`),
   KEY `items_id` (`items_id`),
   KEY `itemtype` (`itemtype`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_counters'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_counters` (
   `id` int(11) NOT NULL auto_increment,
   `value` int(11) NOT NULL default '0',
   `plugin_printercounters_countertypes_recordmodels_id` int(11) NOT NULL default '0',
   `plugin_printercounters_records_id` int(11) NOT NULL default '0',
   PRIMARY KEY (`id`),
   KEY `plugin_printercounters_countertypes_recordmodels_id` (`plugin_printercounters_countertypes_recordmodels_id`),
   KEY `plugin_printercounters_records_id` (`plugin_printercounters_records_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- -------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_records'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_records` (
   `id` int(11) NOT NULL auto_increment,
   `date` datetime default NULL,
   `entities_id` int(11) NOT NULL default '0',
   `result` int(11) NOT NULL default '0',
   `state` int(11) NOT NULL default '0',
   `record_type` int(11) NOT NULL default '0',
   `locations_id` int(11) NOT NULL default '0',
   `last_recordmodels_id` int(11) NOT NULL default '0',
   `plugin_printercounters_items_recordmodels_id` int(11) NOT NULL default '0',
   PRIMARY KEY (`id`),
   KEY `plugin_printercounters_items_recordmodels_id` (`plugin_printercounters_items_recordmodels_id`),
   KEY `date` (`date`),
   KEY `result` (`result`),
   KEY `state` (`state`),
   KEY `record_type` (`record_type`),
   KEY `last_recordmodels_id` (`last_recordmodels_id`),
   KEY `locations_id` (`locations_id`),
   KEY `entities_id` (`entities_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_snmpauthentications'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_snmpauthentications` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) default NULL,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL default '0',
   `version` int(11) NOT NULL default '0',
   `community` varchar(255) NULL default 'public',
   `community_write` varchar(255) NULL default 'private',
   `authentication_encrypt` int(11) NOT NULL default '0',
   `data_encrypt` int(11) NOT NULL default '0',
   `user` varchar(255) default NULL,
   `authentication_password` varchar(255) default NULL,
   `data_password` varchar(255) default NULL,
   `comment` text COLLATE utf8_unicode_ci,
   PRIMARY KEY (`id`),
   KEY `entities_id` (`entities_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_pagecosts'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_pagecosts` (
   `id` int(11) NOT NULL auto_increment,
   `cost` decimal(6,5) NOT NULL default '0',
   `plugin_printercounters_billingmodels_id` int(11) NOT NULL default '0',
   `plugin_printercounters_countertypes_id` int(11) NOT NULL default '0',
   PRIMARY KEY (`id`),
   KEY `plugin_printercounters_billingmodels_id` (`plugin_printercounters_billingmodels_id`),
   KEY `plugin_printercounters_countertypes_id` (`plugin_printercounters_countertypes_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_billingmodels'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_billingmodels` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) default NULL,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL default '0',
   `application_date` datetime default NULL,
   `plugin_printercounters_recordmodels_id` int(11) NOT NULL default '0',
   `contracts_id` int(11) NOT NULL default '0',
   `budgets_id` int(11) NOT NULL default '0',
   `suppliers_id` int(11) NOT NULL default '0',
   `comment` text COLLATE utf8_unicode_ci,
   PRIMARY KEY (`id`),
   KEY `plugin_printercounters_recordmodels_id` (`plugin_printercounters_recordmodels_id`),
   KEY `contracts_id` (`contracts_id`),
   KEY `budgets_id` (`budgets_id`),
   KEY `suppliers_id` (`suppliers_id`),
   KEY `entities_id` (`entities_id`),
   KEY `application_date` (`application_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_items_billingmodels'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_items_billingmodels` (
   `id` int(11) NOT NULL auto_increment,
   `itemtype` varchar(255) default NULL,
   `items_id` int(11) NOT NULL default '0',
   `plugin_printercounters_billingmodels_id` int(11) NOT NULL default '0',
   PRIMARY KEY (`id`),
   KEY `plugin_printercounters_billingmodels_id` (`plugin_printercounters_billingmodels_id`),
   KEY `items_id` (`items_id`),
   KEY `itemtype` (`itemtype`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_budgets'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_budgets` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) default NULL,
   `entities_id` int(11) default NULL,
   `is_recursive` tinyint(1) NOT NULL default '0',
   `amount` int(11) NOT NULL default '0',
   `begin_date` datetime default NULL,
   `end_date` datetime default NULL,
   `comment` text COLLATE utf8_unicode_ci,
   PRIMARY KEY (`id`),
   KEY `entities_id` (`entities_id`),
   KEY `begin_date` (`begin_date`),
   KEY `end_date` (`end_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_items_tickets'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_items_tickets` (
   `id` int(11) NOT NULL auto_increment,
   `itemtype` varchar(255) default NULL,
   `items_id` int(11) NOT NULL default '0',
   `events_type` int(11) NOT NULL default '0',
   `date_mod` datetime default NULL,
   `tickets_id` int(11) NOT NULL default '0',
   PRIMARY KEY (`id`),
   KEY `tickets_id` (`tickets_id`),
   KEY `items_id` (`items_id`),
   KEY `itemtype` (`itemtype`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_snmpsets'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_snmpsets` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL default '0',
   `set_location` tinyint(1) NOT NULL default '1',
   `set_contact` tinyint(1) NOT NULL default '1',
   `set_name` tinyint(1) NOT NULL default '1',
   `contact` text COLLATE utf8_unicode_ci,
   PRIMARY KEY (`id`),
   UNIQUE KEY `unicity` (`entities_id`),
   KEY `entities_id` (`entities_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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