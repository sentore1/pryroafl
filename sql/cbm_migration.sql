-- *************************************************************************
-- *                                                                       *
-- * DEPRIXA PRO - CBM (Cubic Meter) Calculation Feature                  *
-- * Migration Script                                                      *
-- *                                                                       *
-- *************************************************************************
-- * This script adds CBM calculation functionality to the system         *
-- * Run this script to add CBM fields to existing tables                 *
-- *************************************************************************

-- 1. Add CBM fields to order items (individual packages)
ALTER TABLE `cdb_add_order_item` 
ADD COLUMN `cbm` DECIMAL(10,4) DEFAULT 0 COMMENT 'Cubic Meter (L×W×H/1000000)' AFTER `order_item_height`,
ADD COLUMN `cbm_charge` DECIMAL(10,2) DEFAULT 0 COMMENT 'Charge based on CBM' AFTER `cbm`;

-- 2. Add CBM fields to main orders (shipments)
ALTER TABLE `cdb_add_order` 
ADD COLUMN `total_cbm` DECIMAL(10,4) DEFAULT 0 COMMENT 'Total CBM for all items' AFTER `total_weight`,
ADD COLUMN `cbm_rate` DECIMAL(10,2) DEFAULT 0 COMMENT 'Rate per cubic meter' AFTER `total_cbm`,
ADD COLUMN `total_cbm_charge` DECIMAL(10,2) DEFAULT 0 COMMENT 'Total CBM charge' AFTER `cbm_rate`,
ADD COLUMN `charge_method` ENUM('weight','cbm','manual') DEFAULT 'weight' COMMENT 'Which method was used for charging' AFTER `total_cbm_charge`;

-- 3. Add CBM fields to consolidate table (containers)
ALTER TABLE `cdb_consolidate` 
ADD COLUMN `total_cbm` DECIMAL(10,4) DEFAULT 0 COMMENT 'Total CBM in container' AFTER `total_weight`,
ADD COLUMN `max_cbm_capacity` DECIMAL(10,4) DEFAULT 0 COMMENT 'Maximum CBM capacity of container' AFTER `total_cbm`,
ADD COLUMN `cbm_utilization_percent` DECIMAL(5,2) DEFAULT 0 COMMENT 'Percentage of CBM used' AFTER `max_cbm_capacity`;

-- 4. Add CBM fields to customer packages
ALTER TABLE `cdb_customers_packages` 
ADD COLUMN `total_cbm` DECIMAL(10,4) DEFAULT 0 COMMENT 'Total CBM for package' AFTER `total_weight`,
ADD COLUMN `cbm_rate` DECIMAL(10,2) DEFAULT 0 COMMENT 'Rate per cubic meter' AFTER `total_cbm`;

-- 5. Add CBM to customer package details
ALTER TABLE `cdb_customers_packages_detail` 
ADD COLUMN `order_item_cbm` DECIMAL(10,4) DEFAULT 0 COMMENT 'CBM for this item' AFTER `order_item_height`;

-- 6. Add CBM to locker addresses
ALTER TABLE `cdb_address_locker` 
ADD COLUMN `cbm_capacity` DECIMAL(10,4) DEFAULT 0 COMMENT 'Total CBM capacity of locker' AFTER `cpostal`,
ADD COLUMN `current_cbm_used` DECIMAL(10,4) DEFAULT 0 COMMENT 'Current CBM used in locker' AFTER `cbm_capacity`;

-- 7. Add CBM configuration to settings table
ALTER TABLE `cdb_settings` 
ADD COLUMN `cbm_calculation_enabled` TINYINT(1) DEFAULT 0 COMMENT 'Enable/disable CBM calculations' AFTER `c_nit`,
ADD COLUMN `cbm_rate_per_cubic_meter` DECIMAL(10,2) DEFAULT 0 COMMENT 'Default rate per CBM' AFTER `cbm_calculation_enabled`,
ADD COLUMN `cbm_vs_weight_priority` ENUM('weight','cbm','higher') DEFAULT 'higher' COMMENT 'Which charge to use' AFTER `cbm_rate_per_cubic_meter`;

-- 8. Create CBM pricing tiers table (optional - for advanced pricing)
CREATE TABLE IF NOT EXISTS `cdb_cbm_pricing_tiers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tier_name` VARCHAR(100) NOT NULL COMMENT 'Name of pricing tier',
  `min_cbm` DECIMAL(10,4) DEFAULT 0 COMMENT 'Minimum CBM for this tier',
  `max_cbm` DECIMAL(10,4) DEFAULT 0 COMMENT 'Maximum CBM (0 = unlimited)',
  `rate_per_cbm` DECIMAL(10,2) DEFAULT 0 COMMENT 'Rate per cubic meter',
  `fixed_charge` DECIMAL(10,2) DEFAULT 0 COMMENT 'Fixed charge for this tier',
  `active` TINYINT(1) DEFAULT 1 COMMENT 'Is this tier active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='CBM pricing tiers for advanced pricing';

-- Insert default pricing tiers (examples)
INSERT INTO `cdb_cbm_pricing_tiers` (`tier_name`, `min_cbm`, `max_cbm`, `rate_per_cbm`, `fixed_charge`, `active`) VALUES
('Small Package', 0.0000, 0.5000, 50.00, 0.00, 1),
('Medium Package', 0.5001, 2.0000, 45.00, 0.00, 1),
('Large Package', 2.0001, 5.0000, 40.00, 0.00, 1),
('Bulk Shipment', 5.0001, 0.0000, 35.00, 0.00, 1);

-- Update existing records to calculate CBM from existing dimensions
-- This will populate CBM for existing data
UPDATE `cdb_add_order_item` 
SET `cbm` = ROUND((`order_item_length` * `order_item_width` * `order_item_height`) / 1000000, 4)
WHERE `order_item_length` > 0 AND `order_item_width` > 0 AND `order_item_height` > 0;

-- Update customer package details
UPDATE `cdb_customers_packages_detail` 
SET `order_item_cbm` = ROUND((`order_item_length` * `order_item_width` * `order_item_height`) / 1000000, 4)
WHERE `order_item_length` > 0 AND `order_item_width` > 0 AND `order_item_height` > 0;

-- Calculate total CBM for existing orders
UPDATE `cdb_add_order` o
SET `total_cbm` = (
    SELECT COALESCE(SUM(i.cbm), 0)
    FROM `cdb_add_order_item` i
    WHERE i.order_id = o.order_id
);

-- Calculate total CBM for existing customer packages
UPDATE `cdb_customers_packages` p
SET `total_cbm` = (
    SELECT COALESCE(SUM(d.order_item_cbm), 0)
    FROM `cdb_customers_packages_detail` d
    WHERE d.order_id = p.order_id
);

-- Set default container capacities (20ft = 33 CBM, 40ft = 67 CBM)
-- You can adjust these based on your actual container types
UPDATE `cdb_consolidate` 
SET `max_cbm_capacity` = 33.00 
WHERE `max_cbm_capacity` = 0;

-- Migration completed successfully
SELECT 'CBM Migration Completed Successfully!' as Status;
