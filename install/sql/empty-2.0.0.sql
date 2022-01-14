
-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_configs'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_configs`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_configs` (
   `id` int unsigned NOT NULL auto_increment,
   `nb_errors_ticket` int unsigned NOT NULL default '10',
   `nb_errors_delay_ticket` int unsigned NOT NULL default '864000',
   `no_record_delay_ticket` int unsigned NOT NULL default '864000',
   `items_status` longtext COLLATE utf8mb4_unicode_ci,
   `tickets_category` int unsigned NOT NULL default '0',
   `tickets_content` text COLLATE utf8mb4_unicode_ci,
   `add_item_group` char(1) collate utf8mb4_unicode_ci default NULL,
   `add_item_user` char(1) collate utf8mb4_unicode_ci default NULL,
   `disable_autosearch` tinyint NOT NULL default '0',
   `set_first_record` tinyint NOT NULL DEFAULT '0',
   `enable_toner_alert` tinyint NOT NULL DEFAULT '0',
   `toner_alert_repeat` int unsigned NOT NULL DEFAULT '0',
   `toner_treshold` int unsigned NOT NULL DEFAULT '0',
   `max_error_counter` int unsigned NOT NULL DEFAULT '3',
   `enable_error_handler` int unsigned NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `glpi_plugin_printercounters_configs` VALUES ('1', '10', '864000', '864000', '', '0', '', '0', '0', '0', '0', '0', '0', '0', '3', '0');

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_countertypes'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_countertypes`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_countertypes` (
   `id` int unsigned NOT NULL auto_increment,
   `name` varchar(255) default NULL,
   `entities_id` int unsigned NOT NULL default '0',
   `is_recursive` int unsigned NOT NULL default '0',
   `comment` text COLLATE utf8mb4_unicode_ci,
   PRIMARY KEY (`id`),
   KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_countertypes_recordmodels'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_countertypes_recordmodels`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_countertypes_recordmodels` (
   `id` int unsigned NOT NULL auto_increment,
   `oid` varchar(255) default NULL,
   `oid_type` int unsigned NOT NULL default '0',
   `plugin_printercounters_recordmodels_id` int unsigned NOT NULL default '0',
   `plugin_printercounters_countertypes_id` int unsigned NOT NULL default '0',
   PRIMARY KEY (`id`),
   UNIQUE KEY `unicity` (`plugin_printercounters_recordmodels_id`, `plugin_printercounters_countertypes_id`),
   KEY `oid_type` (`oid_type`),
   KEY `plugin_printercounters_recordmodels_id` (`plugin_printercounters_recordmodels_id`),
   KEY `plugin_printercounters_countertypes_id` (`plugin_printercounters_countertypes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_recordmodels'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_recordmodels`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_recordmodels` (
   `id` int unsigned NOT NULL auto_increment,
   `name` varchar(255) default NULL,
   `entities_id` int unsigned NOT NULL default '0',
   `is_recursive` tinyint NOT NULL default '0',
   `mac_address_conformity` tinyint NOT NULL default '0',
   `sysdescr_conformity` tinyint NOT NULL default '0',
   `serial_conformity` tinyint NOT NULL default '0',
   `enable_toner_level` tinyint NOT NULL DEFAULT '0',
   `enable_printer_info` tinyint NOT NULL DEFAULT '0',
   `is_template` tinyint NOT NULL default '0',
   `template_name` varchar(255) default NULL,
   `comment` text COLLATE utf8mb4_unicode_ci,
   PRIMARY KEY (`id`),
   KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_sysdescrs'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_sysdescrs`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_sysdescrs` (
   `id` int unsigned NOT NULL auto_increment,
   `sysdescr` varchar(255) default NULL,
   `plugin_printercounters_recordmodels_id` int unsigned NOT NULL default '0',
   PRIMARY KEY (`id`),
   KEY `plugin_printercounters_recordmodels_id` (`plugin_printercounters_recordmodels_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_items_recordmodels'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_items_recordmodels`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_items_recordmodels` (
   `id` int unsigned NOT NULL auto_increment,
   `nb_retries` int unsigned NOT NULL default '0',
   `max_timeout` int unsigned NOT NULL default '5',
   `enable_automatic_record` tinyint NOT NULL default '0',
   `periodicity` int unsigned NOT NULL default '86400',
   `items_id` int unsigned NOT NULL default '0',
   `itemtype` varchar(255) default NULL,
   `active_mutex` timestamp NULL DEFAULT NULL,
   `process_id` int unsigned NOT NULL default '0',
   `global_tco` decimal(20,4) DEFAULT '0.0000',
   `plugin_printercounters_recordmodels_id` int unsigned NOT NULL default '0',
   `plugin_printercounters_snmpauthentications_id` int unsigned NOT NULL default '0',
   `status` int unsigned NOT NULL default '0',
   `error_counter` int unsigned NOT NULL default '0',
   PRIMARY KEY (`id`),
   UNIQUE KEY `unicity` (`plugin_printercounters_recordmodels_id`,`items_id`, `itemtype`),
   KEY `plugin_printercounters_recordmodels_id` (`plugin_printercounters_recordmodels_id`),
   KEY `plugin_printercounters_snmpauthentications_id` (`plugin_printercounters_snmpauthentications_id`),
   KEY `items_id` (`items_id`),
   KEY `itemtype` (`itemtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_counters'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_counters`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_counters` (
   `id` int unsigned NOT NULL auto_increment,
   `value` int unsigned NOT NULL default '0',
   `plugin_printercounters_countertypes_recordmodels_id` int unsigned NOT NULL default '0',
   `plugin_printercounters_records_id` int unsigned NOT NULL default '0',
   PRIMARY KEY (`id`),
   KEY `plugin_printercounters_countertypes_recordmodels_id` (`plugin_printercounters_countertypes_recordmodels_id`),
   KEY `plugin_printercounters_records_id` (`plugin_printercounters_records_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- -------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_records'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_records`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_records` (
   `id` int unsigned NOT NULL auto_increment,
   `date` timestamp NULL DEFAULT NULL,
   `entities_id` int unsigned NOT NULL default '0',
   `result` int unsigned NOT NULL default '0',
   `state` int unsigned NOT NULL default '0',
   `record_type` int unsigned NOT NULL default '0',
   `locations_id` int unsigned NOT NULL default '0',
   `last_recordmodels_id` int unsigned NOT NULL default '0',
   `plugin_printercounters_items_recordmodels_id` int unsigned NOT NULL default '0',
   PRIMARY KEY (`id`),
   KEY `plugin_printercounters_items_recordmodels_id` (`plugin_printercounters_items_recordmodels_id`),
   KEY `date` (`date`),
   KEY `result` (`result`),
   KEY `state` (`state`),
   KEY `record_type` (`record_type`),
   KEY `last_recordmodels_id` (`last_recordmodels_id`),
   KEY `locations_id` (`locations_id`),
   KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_snmpauthentications'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_snmpauthentications`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_snmpauthentications` (
   `id` int unsigned NOT NULL auto_increment,
   `name` varchar(255) default NULL,
   `entities_id` int unsigned NOT NULL default '0',
   `is_recursive` tinyint NOT NULL default '0',
   `is_default` tinyint NOT NULL default '0',
   `version` int unsigned NOT NULL default '0',
   `community` varchar(255) NULL default 'public',
   `community_write` varchar(255) NULL default 'private',
   `authentication_encrypt` int unsigned NOT NULL default '0',
   `data_encrypt` int unsigned NOT NULL default '0',
   `user` varchar(255) default NULL,
   `authentication_password` varchar(255) default NULL,
   `data_password` varchar(255) default NULL,
   `comment` text COLLATE utf8mb4_unicode_ci,
   PRIMARY KEY (`id`),
   KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_pagecosts'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_pagecosts`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_pagecosts` (
   `id` int unsigned NOT NULL auto_increment,
   `cost` decimal(6,5) NOT NULL default '0',
   `plugin_printercounters_billingmodels_id` int unsigned NOT NULL default '0',
   `plugin_printercounters_countertypes_id` int unsigned NOT NULL default '0',
   PRIMARY KEY (`id`),
   KEY `plugin_printercounters_billingmodels_id` (`plugin_printercounters_billingmodels_id`),
   KEY `plugin_printercounters_countertypes_id` (`plugin_printercounters_countertypes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_billingmodels'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_billingmodels`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_billingmodels` (
   `id` int unsigned NOT NULL auto_increment,
   `name` varchar(255) default NULL,
   `entities_id` int unsigned NOT NULL default '0',
   `is_recursive` tinyint NOT NULL default '0',
   `application_date` timestamp NULL DEFAULT NULL,
   `plugin_printercounters_recordmodels_id` int unsigned NOT NULL default '0',
   `contracts_id` int unsigned NOT NULL default '0',
   `budgets_id` int unsigned NOT NULL default '0',
   `suppliers_id` int unsigned NOT NULL default '0',
   `comment` text COLLATE utf8mb4_unicode_ci,
   PRIMARY KEY (`id`),
   KEY `plugin_printercounters_recordmodels_id` (`plugin_printercounters_recordmodels_id`),
   KEY `contracts_id` (`contracts_id`),
   KEY `budgets_id` (`budgets_id`),
   KEY `suppliers_id` (`suppliers_id`),
   KEY `entities_id` (`entities_id`),
   KEY `application_date` (`application_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_items_billingmodels'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_items_billingmodels`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_items_billingmodels` (
   `id` int unsigned NOT NULL auto_increment,
   `itemtype` varchar(255) default NULL,
   `items_id` int unsigned NOT NULL default '0',
   `plugin_printercounters_billingmodels_id` int unsigned NOT NULL default '0',
   PRIMARY KEY (`id`),
   KEY `plugin_printercounters_billingmodels_id` (`plugin_printercounters_billingmodels_id`),
   KEY `items_id` (`items_id`),
   KEY `itemtype` (`itemtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_budgets'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_budgets`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_budgets` (
   `id` int unsigned NOT NULL auto_increment,
   `name` varchar(255) default NULL,
   `entities_id` int unsigned default NULL,
   `is_recursive` tinyint NOT NULL default '0',
   `amount` int unsigned NOT NULL default '0',
   `begin_date` timestamp NULL DEFAULT NULL,
   `end_date` timestamp NULL DEFAULT NULL,
   `comment` text COLLATE utf8mb4_unicode_ci,
   PRIMARY KEY (`id`),
   KEY `entities_id` (`entities_id`),
   KEY `begin_date` (`begin_date`),
   KEY `end_date` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_items_tickets'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_items_tickets`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_items_tickets` (
   `id` int unsigned NOT NULL auto_increment,
   `itemtype` varchar(255) default NULL,
   `items_id` int unsigned NOT NULL default '0',
   `events_type` int unsigned NOT NULL default '0',
   `date_mod` timestamp NULL DEFAULT NULL,
   `tickets_id` int unsigned NOT NULL default '0',
   PRIMARY KEY (`id`),
   KEY `tickets_id` (`tickets_id`),
   KEY `items_id` (`items_id`),
   KEY `itemtype` (`itemtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_snmpsets'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_snmpsets`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_snmpsets` (
   `id` int unsigned NOT NULL auto_increment,
   `entities_id` int unsigned NOT NULL default '0',
   `is_recursive` tinyint NOT NULL default '0',
   `set_location` tinyint NOT NULL default '1',
   `set_contact` tinyint NOT NULL default '1',
   `set_name` tinyint NOT NULL default '1',
   `contact` text COLLATE utf8mb4_unicode_ci,
   PRIMARY KEY (`id`),
   UNIQUE KEY `unicity` (`entities_id`),
   KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_additionals_datas'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_printercounters_additionals_datas`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_additionals_datas` (
   `id` int unsigned NOT NULL auto_increment,
   `name` varchar(255) default NULL,
   `type` varchar(255) default NULL,
   `sub_type` varchar(255) default NULL,
   `value` varchar(255) default NULL,
   `plugin_printercounters_items_recordmodels_id` int unsigned NOT NULL default '0',
   PRIMARY KEY (`id`),
   UNIQUE KEY `unicity` (`plugin_printercounters_items_recordmodels_id`, `sub_type`),
   KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
