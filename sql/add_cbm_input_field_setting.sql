-- Add new setting for CBM input field visibility
-- This allows users to choose between entering dimensions or CBM directly

ALTER TABLE `cdb_settings` 
ADD COLUMN IF NOT EXISTS `show_cbm_input_field` TINYINT(1) DEFAULT 0 
AFTER `show_cbm_in_forms`;

-- Update default value: show dimensions by default, hide CBM input by default
UPDATE `cdb_settings` 
SET `show_package_dimensions` = 1,
    `show_cbm_input_field` = 0
WHERE `id` = 1;
