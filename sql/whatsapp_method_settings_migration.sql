-- *************************************************************************
-- *                                                                       *
-- * WhatsApp Method Settings - Database Migration                         *
-- * Adds settings for choosing between API and Direct Link methods        *
-- *                                                                       *
-- *************************************************************************

-- Add new columns to settings table for WhatsApp method control
ALTER TABLE `cdb_settings`
    ADD COLUMN `whatsapp_method` VARCHAR(20) NOT NULL DEFAULT 'api' 
        COMMENT 'api, direct_link, or both' 
        AFTER `active_whatsapp`,
    ADD COLUMN `whatsapp_default_action` VARCHAR(20) NOT NULL DEFAULT 'api' 
        COMMENT 'Default method when both are enabled' 
        AFTER `whatsapp_method`,
    ADD COLUMN `enable_direct_link_buttons` TINYINT(1) NOT NULL DEFAULT 1 
        COMMENT 'Show direct link buttons in UI' 
        AFTER `whatsapp_default_action`,
    ADD COLUMN `enable_api_buttons` TINYINT(1) NOT NULL DEFAULT 1 
        COMMENT 'Show API notification buttons in UI' 
        AFTER `enable_direct_link_buttons`;

-- Create index for faster lookups
ALTER TABLE `cdb_settings` 
    ADD INDEX `idx_whatsapp_settings` (`active_whatsapp`, `whatsapp_method`);

-- Optional: Set default values for existing installations
UPDATE `cdb_settings` 
SET 
    `whatsapp_method` = 'api',
    `whatsapp_default_action` = 'api',
    `enable_direct_link_buttons` = 1,
    `enable_api_buttons` = 1
WHERE `id` = 1;
