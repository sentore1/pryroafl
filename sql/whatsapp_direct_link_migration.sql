-- *************************************************************************
-- *                                                                       *
-- * WhatsApp Direct Link - Database Migration                             *
-- * Creates table for logging WhatsApp direct link actions                *
-- *                                                                       *
-- *************************************************************************

-- Create WhatsApp logs table
CREATE TABLE IF NOT EXISTS `cdb_whatsapp_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `recipient_type` varchar(20) NOT NULL COMMENT 'sender or receiver',
  `action_type` varchar(50) NOT NULL COMMENT 'direct_link, api, bulk',
  `user_id` int(11) NOT NULL COMMENT 'ID of user who triggered action',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: Add index for better query performance
ALTER TABLE `cdb_whatsapp_logs` 
  ADD INDEX `idx_order_action` (`order_id`, `action_type`);

-- Optional: Add foreign key constraints (uncomment if you want strict referential integrity)
-- ALTER TABLE `cdb_whatsapp_logs`
--   ADD CONSTRAINT `fk_whatsapp_log_order` FOREIGN KEY (`order_id`) REFERENCES `cdb_add_order` (`order_id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `fk_whatsapp_log_user` FOREIGN KEY (`user_id`) REFERENCES `cdb_users` (`id`) ON DELETE CASCADE;
