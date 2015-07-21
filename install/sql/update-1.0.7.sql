-- Add item_recordmodel fields for items in error
ALTER TABLE `glpi_plugin_printercounters_items_recordmodels` ADD `status` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_printercounters_items_recordmodels` ADD `error_counter` int(11) NOT NULL DEFAULT '0';

-- Maximum number of interrogation for records in error
ALTER TABLE `glpi_plugin_printercounters_configs` ADD `max_error_counter` int(11) NOT NULL DEFAULT '3';
ALTER TABLE `glpi_plugin_printercounters_configs` ADD `enable_error_handler` int(11) NOT NULL DEFAULT '0';