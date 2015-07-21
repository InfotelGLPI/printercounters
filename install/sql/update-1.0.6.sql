-- Default authentication
ALTER TABLE `glpi_plugin_printercounters_snmpauthentications` ADD `is_default` tinyint(1) NOT NULL DEFAULT '0';
