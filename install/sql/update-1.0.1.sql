ALTER TABLE `glpi_plugin_printercounters_billingmodels` DROP `plugin_printercounters_budgets_id`;
ALTER TABLE `glpi_plugin_printercounters_billingmodels` ADD  `budgets_id` int(11) NOT NULL DEFAULT '0';
