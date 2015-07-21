-- Snmp authentications
ALTER TABLE `glpi_plugin_printercounters_snmpauthentications` ADD INDEX `entities_id` (`entities_id`);
ALTER TABLE `glpi_plugin_printercounters_snmpauthentications` ADD `community_write` varchar(255) NULL default 'private';

-- Profiles
ALTER TABLE `glpi_plugin_printercounters_profiles` ADD `snmpset` tinyint(1) NOT NULL default '0';
ALTER TABLE `glpi_plugin_printercounters_profiles`
  MODIFY COLUMN `add_lower_records` tinyint(1) NOT NULL default '0',
  MODIFY COLUMN `update_records` tinyint(1) NOT NULL default '0';

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

