-- ================================================================
-- AI PERMISSIONS PER USER
-- Adds AI permission columns to cdb_users so each employee/admin
-- can have their own AI capabilities independently from globals.
-- ================================================================

ALTER TABLE `cdb_users`
    ADD COLUMN IF NOT EXISTS `ai_access`              TINYINT(1) DEFAULT NULL AFTER `active`,
    ADD COLUMN IF NOT EXISTS `ai_can_assign_drivers`  TINYINT(1) DEFAULT NULL AFTER `ai_access`,
    ADD COLUMN IF NOT EXISTS `ai_can_confirm_payments`TINYINT(1) DEFAULT NULL AFTER `ai_can_assign_drivers`,
    ADD COLUMN IF NOT EXISTS `ai_can_update_status`   TINYINT(1) DEFAULT NULL AFTER `ai_can_confirm_payments`,
    ADD COLUMN IF NOT EXISTS `ai_can_create_shipments`TINYINT(1) DEFAULT NULL AFTER `ai_can_update_status`,
    ADD COLUMN IF NOT EXISTS `ai_can_edit_shipments`  TINYINT(1) DEFAULT NULL AFTER `ai_can_create_shipments`,
    ADD COLUMN IF NOT EXISTS `ai_can_cancel_shipments`TINYINT(1) DEFAULT NULL AFTER `ai_can_edit_shipments`,
    ADD COLUMN IF NOT EXISTS `ai_can_send_sms`        TINYINT(1) DEFAULT NULL AFTER `ai_can_cancel_shipments`,
    ADD COLUMN IF NOT EXISTS `ai_can_send_email`      TINYINT(1) DEFAULT NULL AFTER `ai_can_send_sms`,
    ADD COLUMN IF NOT EXISTS `ai_can_send_whatsapp`   TINYINT(1) DEFAULT NULL AFTER `ai_can_send_email`,
    ADD COLUMN IF NOT EXISTS `ai_can_generate_reports`TINYINT(1) DEFAULT NULL AFTER `ai_can_send_whatsapp`,
    ADD COLUMN IF NOT EXISTS `ai_can_export_data`     TINYINT(1) DEFAULT NULL AFTER `ai_can_generate_reports`,
    ADD COLUMN IF NOT EXISTS `ai_can_create_customers`TINYINT(1) DEFAULT NULL AFTER `ai_can_export_data`,
    ADD COLUMN IF NOT EXISTS `ai_can_edit_customers`  TINYINT(1) DEFAULT NULL AFTER `ai_can_create_customers`,
    ADD COLUMN IF NOT EXISTS `ai_can_process_refunds` TINYINT(1) DEFAULT NULL AFTER `ai_can_edit_customers`,
    ADD COLUMN IF NOT EXISTS `ai_can_apply_discounts` TINYINT(1) DEFAULT NULL AFTER `ai_can_process_refunds`;

-- NULL means "inherit from global settings" — set explicitly to 0 or 1 to override
