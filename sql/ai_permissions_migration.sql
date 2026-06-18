-- ================================================================
-- P-AI PERMISSIONS & AUTOPILOT SETTINGS
-- Run this to add AI permission controls to cdb_settings
-- ================================================================

-- Add AI permission columns
ALTER TABLE `cdb_settings`
    -- Autopilot Mode
    ADD COLUMN IF NOT EXISTS `ai_autopilot_enabled` TINYINT(1) DEFAULT 0 AFTER `openai_api_key`,
    ADD COLUMN IF NOT EXISTS `ai_autopilot_threshold` INT DEFAULT 5 AFTER `ai_autopilot_enabled`,
    
    -- Read Permissions
    ADD COLUMN IF NOT EXISTS `ai_can_read_customers` TINYINT(1) DEFAULT 1 AFTER `ai_autopilot_threshold`,
    ADD COLUMN IF NOT EXISTS `ai_can_read_packages` TINYINT(1) DEFAULT 1 AFTER `ai_can_read_customers`,
    ADD COLUMN IF NOT EXISTS `ai_can_read_financials` TINYINT(1) DEFAULT 1 AFTER `ai_can_read_packages`,
    ADD COLUMN IF NOT EXISTS `ai_can_read_drivers` TINYINT(1) DEFAULT 1 AFTER `ai_can_read_financials`,
    ADD COLUMN IF NOT EXISTS `ai_can_read_inventory` TINYINT(1) DEFAULT 0 AFTER `ai_can_read_drivers`,
    
    -- Action Permissions
    ADD COLUMN IF NOT EXISTS `ai_can_assign_drivers` TINYINT(1) DEFAULT 1 AFTER `ai_can_read_inventory`,
    ADD COLUMN IF NOT EXISTS `ai_can_confirm_payments` TINYINT(1) DEFAULT 1 AFTER `ai_can_assign_drivers`,
    ADD COLUMN IF NOT EXISTS `ai_can_update_status` TINYINT(1) DEFAULT 1 AFTER `ai_can_confirm_payments`,
    ADD COLUMN IF NOT EXISTS `ai_can_create_shipments` TINYINT(1) DEFAULT 0 AFTER `ai_can_update_status`,
    ADD COLUMN IF NOT EXISTS `ai_can_edit_shipments` TINYINT(1) DEFAULT 0 AFTER `ai_can_create_shipments`,
    ADD COLUMN IF NOT EXISTS `ai_can_cancel_shipments` TINYINT(1) DEFAULT 0 AFTER `ai_can_edit_shipments`,
    
    -- Communication Permissions
    ADD COLUMN IF NOT EXISTS `ai_can_send_sms` TINYINT(1) DEFAULT 0 AFTER `ai_can_cancel_shipments`,
    ADD COLUMN IF NOT EXISTS `ai_can_send_email` TINYINT(1) DEFAULT 0 AFTER `ai_can_send_sms`,
    ADD COLUMN IF NOT EXISTS `ai_can_send_whatsapp` TINYINT(1) DEFAULT 0 AFTER `ai_can_send_email`,
    
    -- Reporting Permissions
    ADD COLUMN IF NOT EXISTS `ai_can_generate_reports` TINYINT(1) DEFAULT 1 AFTER `ai_can_send_whatsapp`,
    ADD COLUMN IF NOT EXISTS `ai_can_export_data` TINYINT(1) DEFAULT 1 AFTER `ai_can_generate_reports`,
    
    -- Customer Management
    ADD COLUMN IF NOT EXISTS `ai_can_create_customers` TINYINT(1) DEFAULT 0 AFTER `ai_can_export_data`,
    ADD COLUMN IF NOT EXISTS `ai_can_edit_customers` TINYINT(1) DEFAULT 0 AFTER `ai_can_create_customers`,
    
    -- Financial Operations
    ADD COLUMN IF NOT EXISTS `ai_can_process_refunds` TINYINT(1) DEFAULT 0 AFTER `ai_can_edit_customers`,
    ADD COLUMN IF NOT EXISTS `ai_can_apply_discounts` TINYINT(1) DEFAULT 0 AFTER `ai_can_process_refunds`,
    
    -- Advanced Features
    ADD COLUMN IF NOT EXISTS `ai_can_predict_analytics` TINYINT(1) DEFAULT 1 AFTER `ai_can_apply_discounts`,
    ADD COLUMN IF NOT EXISTS `ai_can_optimize_routes` TINYINT(1) DEFAULT 0 AFTER `ai_can_predict_analytics`;

-- Set default values for existing installations
UPDATE `cdb_settings` 
SET 
    ai_autopilot_enabled = 0,
    ai_autopilot_threshold = 5,
    ai_can_read_customers = 1,
    ai_can_read_packages = 1,
    ai_can_read_financials = 1,
    ai_can_read_drivers = 1,
    ai_can_read_inventory = 0,
    ai_can_assign_drivers = 1,
    ai_can_confirm_payments = 1,
    ai_can_update_status = 1,
    ai_can_create_shipments = 0,
    ai_can_edit_shipments = 0,
    ai_can_cancel_shipments = 0,
    ai_can_send_sms = 0,
    ai_can_send_email = 0,
    ai_can_send_whatsapp = 0,
    ai_can_generate_reports = 1,
    ai_can_export_data = 1,
    ai_can_create_customers = 0,
    ai_can_edit_customers = 0,
    ai_can_process_refunds = 0,
    ai_can_apply_discounts = 0,
    ai_can_predict_analytics = 1,
    ai_can_optimize_routes = 0
WHERE 1=1;

-- Add index for faster permission checks
CREATE INDEX IF NOT EXISTS idx_ai_permissions ON cdb_settings(ai_autopilot_enabled, ai_can_assign_drivers, ai_can_confirm_payments);
