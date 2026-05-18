-- *************************************************************************
-- * CBM Settings Update - Add Measurement Unit and Display Options       *
-- *************************************************************************

-- Add measurement unit setting
ALTER TABLE `cdb_settings` 
ADD COLUMN `cbm_measurement_unit` ENUM('cm','inch','m') DEFAULT 'cm' COMMENT 'Measurement unit for dimensions' AFTER `cbm_vs_weight_priority`;

-- Add display options
ALTER TABLE `cdb_settings` 
ADD COLUMN `show_package_dimensions` TINYINT(1) DEFAULT 1 COMMENT 'Show/hide package dimensions in forms' AFTER `cbm_measurement_unit`,
ADD COLUMN `show_cbm_in_forms` TINYINT(1) DEFAULT 1 COMMENT 'Show/hide CBM calculation in forms' AFTER `show_package_dimensions`;

-- Verify columns were added
SHOW COLUMNS FROM cdb_settings LIKE 'cbm%';
SHOW COLUMNS FROM cdb_settings LIKE 'show_%';
