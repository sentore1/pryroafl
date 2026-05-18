-- *************************************************************************
-- * CBM Testing and Verification Queries                                 *
-- *************************************************************************

-- 1. Check if CBM columns were added successfully
DESCRIBE cdb_add_order_item;
DESCRIBE cdb_add_order;
DESCRIBE cdb_consolidate;
DESCRIBE cdb_customers_packages;
DESCRIBE cdb_address_locker;
DESCRIBE cdb_settings;

-- 2. View CBM pricing tiers
SELECT * FROM cdb_cbm_pricing_tiers WHERE active = 1;

-- 3. Check orders with CBM calculated
SELECT 
    o.order_id,
    o.order_prefix,
    o.order_no,
    o.total_weight,
    o.total_cbm,
    o.cbm_rate,
    o.charge_method
FROM cdb_add_order o
WHERE o.total_cbm > 0
ORDER BY o.order_id DESC
LIMIT 10;

-- 4. Check order items with CBM
SELECT 
    i.order_item_id,
    i.order_id,
    i.order_item_description,
    i.order_item_weight,
    i.order_item_length,
    i.order_item_width,
    i.order_item_height,
    i.cbm,
    i.cbm_charge
FROM cdb_add_order_item i
WHERE i.cbm > 0
ORDER BY i.order_item_id DESC
LIMIT 10;

-- 5. Check containers with CBM utilization
SELECT 
    c.consolidate_id,
    c.c_prefix,
    c.c_no,
    c.total_weight,
    c.total_cbm,
    c.max_cbm_capacity,
    c.cbm_utilization_percent,
    CONCAT(ROUND(c.cbm_utilization_percent, 2), '%') as utilization_display
FROM cdb_consolidate c
WHERE c.total_cbm > 0
ORDER BY c.consolidate_id DESC
LIMIT 10;

-- 6. Calculate CBM for existing orders (if not already done)
UPDATE cdb_add_order_item 
SET cbm = ROUND((order_item_length * order_item_width * order_item_height) / 1000000, 4)
WHERE order_item_length > 0 
AND order_item_width > 0 
AND order_item_height > 0
AND cbm = 0;

-- 7. Update total CBM for orders
UPDATE cdb_add_order o
SET total_cbm = (
    SELECT COALESCE(SUM(i.cbm), 0)
    FROM cdb_add_order_item i
    WHERE i.order_id = o.order_id
)
WHERE o.total_cbm = 0;

-- 8. Find orders where CBM charge would be higher than weight charge
-- (Assuming $50 per CBM and $2 per kg)
SELECT 
    o.order_id,
    o.order_prefix,
    o.order_no,
    o.total_weight,
    o.total_cbm,
    ROUND(o.total_weight * 2, 2) as weight_charge,
    ROUND(o.total_cbm * 50, 2) as cbm_charge,
    CASE 
        WHEN (o.total_cbm * 50) > (o.total_weight * 2) THEN 'CBM Higher'
        ELSE 'Weight Higher'
    END as recommended_method
FROM cdb_add_order o
WHERE o.total_cbm > 0
ORDER BY o.order_id DESC
LIMIT 20;

-- 9. Container capacity analysis
SELECT 
    c.consolidate_id,
    c.c_prefix,
    c.c_no,
    c.total_cbm as used_cbm,
    c.max_cbm_capacity,
    ROUND(c.max_cbm_capacity - c.total_cbm, 4) as remaining_cbm,
    c.cbm_utilization_percent,
    CASE 
        WHEN c.cbm_utilization_percent >= 90 THEN 'Nearly Full'
        WHEN c.cbm_utilization_percent >= 70 THEN 'Good Utilization'
        WHEN c.cbm_utilization_percent >= 50 THEN 'Half Full'
        ELSE 'Low Utilization'
    END as status
FROM cdb_consolidate c
WHERE c.max_cbm_capacity > 0
ORDER BY c.cbm_utilization_percent DESC;

-- 10. Locker CBM usage
SELECT 
    l.id,
    l.name,
    l.cbm_capacity,
    l.current_cbm_used,
    ROUND((l.current_cbm_used / l.cbm_capacity) * 100, 2) as usage_percent,
    ROUND(l.cbm_capacity - l.current_cbm_used, 4) as available_cbm
FROM cdb_address_locker l
WHERE l.cbm_capacity > 0
ORDER BY usage_percent DESC;

-- 11. Top 10 largest packages by CBM
SELECT 
    i.order_item_id,
    i.order_id,
    o.order_prefix,
    o.order_no,
    i.order_item_description,
    i.order_item_length,
    i.order_item_width,
    i.order_item_height,
    i.cbm,
    CONCAT(i.order_item_length, ' × ', i.order_item_width, ' × ', i.order_item_height, ' cm') as dimensions
FROM cdb_add_order_item i
JOIN cdb_add_order o ON i.order_id = o.order_id
WHERE i.cbm > 0
ORDER BY i.cbm DESC
LIMIT 10;

-- 12. CBM statistics summary
SELECT 
    COUNT(*) as total_orders,
    SUM(total_cbm) as total_cbm_all_orders,
    AVG(total_cbm) as avg_cbm_per_order,
    MIN(total_cbm) as min_cbm,
    MAX(total_cbm) as max_cbm
FROM cdb_add_order
WHERE total_cbm > 0;

-- 13. Monthly CBM volume
SELECT 
    DATE_FORMAT(order_date, '%Y-%m') as month,
    COUNT(*) as order_count,
    SUM(total_cbm) as total_cbm,
    AVG(total_cbm) as avg_cbm
FROM cdb_add_order
WHERE total_cbm > 0
GROUP BY DATE_FORMAT(order_date, '%Y-%m')
ORDER BY month DESC
LIMIT 12;

-- 14. Check CBM settings
SELECT 
    cbm_calculation_enabled,
    cbm_rate_per_cubic_meter,
    cbm_vs_weight_priority
FROM cdb_settings
LIMIT 1;

-- 15. Update CBM settings (example)
-- UPDATE cdb_settings 
-- SET 
--     cbm_calculation_enabled = 1,
--     cbm_rate_per_cubic_meter = 50.00,
--     cbm_vs_weight_priority = 'higher'
-- WHERE id = 1;

-- 16. Find packages that would benefit from CBM pricing
-- (Light but bulky items where CBM charge would be lower)
SELECT 
    o.order_id,
    o.order_prefix,
    o.order_no,
    i.order_item_description,
    i.order_item_weight,
    i.cbm,
    ROUND(i.order_item_weight * 2, 2) as weight_charge,
    ROUND(i.cbm * 50, 2) as cbm_charge,
    ROUND((i.order_item_weight * 2) - (i.cbm * 50), 2) as savings_with_cbm
FROM cdb_add_order_item i
JOIN cdb_add_order o ON i.order_id = o.order_id
WHERE i.cbm > 0
AND (i.cbm * 50) < (i.order_item_weight * 2)
ORDER BY savings_with_cbm DESC
LIMIT 20;

-- 17. Verify data integrity
SELECT 
    'Orders with items' as check_type,
    COUNT(DISTINCT o.order_id) as count
FROM cdb_add_order o
JOIN cdb_add_order_item i ON o.order_id = i.order_id
UNION ALL
SELECT 
    'Orders with CBM calculated' as check_type,
    COUNT(*) as count
FROM cdb_add_order
WHERE total_cbm > 0
UNION ALL
SELECT 
    'Items with CBM calculated' as check_type,
    COUNT(*) as count
FROM cdb_add_order_item
WHERE cbm > 0;

-- 18. Reset CBM values (use with caution!)
-- UPDATE cdb_add_order_item SET cbm = 0, cbm_charge = 0;
-- UPDATE cdb_add_order SET total_cbm = 0, cbm_rate = 0, total_cbm_charge = 0;
-- UPDATE cdb_consolidate SET total_cbm = 0, cbm_utilization_percent = 0;

-- 19. Recalculate all CBM values from dimensions
-- UPDATE cdb_add_order_item 
-- SET cbm = ROUND((order_item_length * order_item_width * order_item_height) / 1000000, 4)
-- WHERE order_item_length > 0 AND order_item_width > 0 AND order_item_height > 0;

-- 20. Export CBM data for analysis
SELECT 
    o.order_id,
    o.order_prefix,
    o.order_no,
    o.order_date,
    u.fname as sender_fname,
    u.lname as sender_lname,
    i.order_item_description,
    i.order_item_weight,
    i.order_item_length,
    i.order_item_width,
    i.order_item_height,
    i.cbm,
    o.total_cbm,
    o.total_weight
FROM cdb_add_order o
JOIN cdb_users u ON o.sender_id = u.id
JOIN cdb_add_order_item i ON o.order_id = i.order_id
WHERE i.cbm > 0
ORDER BY o.order_date DESC;
