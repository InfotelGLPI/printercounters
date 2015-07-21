
-- --------------------------------------------------------
--  'glpi_plugin_printercounters_profiles'
-- --------------------------------------------------------

ALTER TABLE `glpi_plugin_printercounters_profiles` ADD `add_lower_records` char(1) collate utf8_unicode_ci default NULL;

-- --------------------------------------------------------
-- 'glpi_plugin_printercounters_configs'
-- --------------------------------------------------------

ALTER TABLE `glpi_plugin_printercounters_configs` ADD `tickets_content` text COLLATE utf8_unicode_ci default NULL;
ALTER TABLE `glpi_plugin_printercounters_configs` ADD `add_item_group` char(1) collate utf8_unicode_ci default NULL;
ALTER TABLE `glpi_plugin_printercounters_configs` ADD `add_item_user` char(1) collate utf8_unicode_ci default NULL;

-- -------------------------------------------------------
-- 'glpi_plugin_printercounters_records'
-- --------------------------------------------------------

ALTER TABLE `glpi_plugin_printercounters_records` ADD `locations_id` int(11) NOT NULL default '0';

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_printercounters_pagecosts'
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_printercounters_pagecosts` (
   `id` int(11) NOT NULL auto_increment,
   `cost` decimal(6,5) NOT NULL default '0',
   `plugin_printercounters_billingmodels_id` int(11) NOT NULL default '0',
   `plugin_printercounters_countertypes_id` int(11) NOT NULL default '0',
   PRIMARY KEY (`id`),
   UNIQUE KEY `unicity` (`plugin_printercounters_billingmodels_id`, `plugin_printercounters_countertypes_id`),
   FOREIGN KEY (`plugin_printercounters_billingmodels_id`) REFERENCES glpi_plugin_printercounters_billingmodels(id),
   FOREIGN KEY (`plugin_printercounters_countertypes_id`) REFERENCES glpi_plugin_printercounters_countertypes(id)
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
   `plugin_printercounters_budgets_id` int(11) NOT NULL default '0',
   `suppliers_id` int(11) NOT NULL default '0',
   `comment` text COLLATE utf8_unicode_ci,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`plugin_printercounters_recordmodels_id`) REFERENCES glpi_plugin_printercounters_recordmodels(id),
   FOREIGN KEY (`contracts_id`) REFERENCES contracts_id(id),
   FOREIGN KEY (`plugin_printercounters_budgets_id`) REFERENCES glpi_plugin_printercounters_budgets(id),
   FOREIGN KEY (`suppliers_id`) REFERENCES glpi_suppliers(id),
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
   FOREIGN KEY (`plugin_printercounters_billingmodels_id`) REFERENCES glpi_plugin_printercounters_billingmodels(id),
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
   FOREIGN KEY (`tickets_id`) REFERENCES glpi_tickets(id),
   KEY `items_id` (`items_id`),
   KEY `itemtype` (`itemtype`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
