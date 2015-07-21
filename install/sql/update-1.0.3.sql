-- Coutertypes recordmodel
ALTER TABLE `glpi_plugin_printercounters_countertypes_recordmodels` ADD INDEX `oid_type` (`oid_type`);
ALTER TABLE `glpi_plugin_printercounters_countertypes_recordmodels` ADD INDEX `plugin_printercounters_recordmodels_id` (`plugin_printercounters_recordmodels_id`);

-- Record
ALTER TABLE `glpi_plugin_printercounters_records` ADD INDEX `date` (`date`);
ALTER TABLE `glpi_plugin_printercounters_records` ADD INDEX `result` (`result`);
ALTER TABLE `glpi_plugin_printercounters_records` ADD INDEX `state` (`state`);
ALTER TABLE `glpi_plugin_printercounters_records` ADD INDEX `record_type` (`record_type`);
ALTER TABLE `glpi_plugin_printercounters_records` ADD INDEX `last_recordmodels_id` (`last_recordmodels_id`);
ALTER TABLE `glpi_plugin_printercounters_records` ADD INDEX `locations_id` (`locations_id`);
ALTER TABLE `glpi_plugin_printercounters_records` ADD INDEX `entities_id` (`entities_id`);

-- Billingmodel
ALTER TABLE `glpi_plugin_printercounters_billingmodels` ADD INDEX `budgets_id` (`budgets_id`);
ALTER TABLE `glpi_plugin_printercounters_billingmodels` ADD INDEX `entities_id` (`entities_id`);
