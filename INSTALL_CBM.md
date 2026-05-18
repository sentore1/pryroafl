# CBM Feature Installation Instructions

## Quick Installation Guide

Follow these steps to install the CBM (Cubic Meter) calculation feature in your Deprixa Pro system.

---

## Prerequisites

- ✅ Deprixa Pro installed and working
- ✅ Database access (phpMyAdmin or MySQL command line)
- ✅ FTP/File access to server
- ✅ Backup of database (recommended)

---

## Installation Steps

### Step 1: Backup Your Database ⚠️

**IMPORTANT:** Always backup before making database changes!

```bash
# Command line backup
mysqldump -u username -p database_name > backup_before_cbm.sql

# Or use phpMyAdmin:
# Export > Quick > Go
```

---

### Step 2: Upload Files

The following files have been created/modified:

**New Files:**
- `sql/cbm_migration.sql`
- `sql/cbm_test_queries.sql`
- `CBM_IMPLEMENTATION_GUIDE.md`
- `CBM_IMPLEMENTATION_STATUS.md`
- `INSTALL_CBM.md` (this file)

**Modified Files:**
- `helpers/functions.php` (CBM functions added at end)
- `ajax/consolidate/process_bulk_upload_ajax.php` (CBM calculation added)
- `dataJs/consolidate_add.js` (CBM calculation added)

**Action:** Ensure all files are uploaded to your server.

---

### Step 3: Run Database Migration

Choose one of these methods:

#### Method A: phpMyAdmin (Easiest)

1. Open phpMyAdmin
2. Select your Deprixa database
3. Click "SQL" tab
4. Open `sql/cbm_migration.sql` in a text editor
5. Copy ALL contents
6. Paste into SQL window
7. Click "Go"
8. Wait for "Success" message

#### Method B: Command Line

```bash
mysql -u your_username -p your_database_name < sql/cbm_migration.sql
```

Enter your password when prompted.

#### Method C: MySQL Workbench

1. Open MySQL Workbench
2. Connect to your database
3. File > Open SQL Script
4. Select `sql/cbm_migration.sql`
5. Execute (lightning bolt icon)

---

### Step 4: Verify Installation

Run these verification queries in phpMyAdmin or command line:

```sql
-- Check if columns were added
DESCRIBE cdb_add_order_item;

-- Should show: cbm, cbm_charge columns

-- Check pricing tiers
SELECT * FROM cdb_cbm_pricing_tiers;

-- Should show 4 pricing tiers

-- Check settings
SELECT cbm_calculation_enabled, cbm_rate_per_cubic_meter 
FROM cdb_settings LIMIT 1;

-- Should show new columns (may be 0/NULL initially)
```

**Expected Results:**
- ✅ `cdb_add_order_item` has `cbm` and `cbm_charge` columns
- ✅ `cdb_cbm_pricing_tiers` table exists with 4 rows
- ✅ `cdb_settings` has CBM configuration columns
- ✅ No error messages

---

### Step 5: Test CBM Calculation

#### Test 1: Check Existing Data

```sql
-- View orders with CBM calculated
SELECT 
    order_id, 
    order_no, 
    total_cbm 
FROM cdb_add_order 
WHERE total_cbm > 0 
LIMIT 5;
```

If you have existing orders with dimensions, they should now show CBM values.

#### Test 2: Bulk Upload Test

1. Go to: **Consolidate > Bulk Upload Container**
2. Download the template
3. Add test data:
   ```
   sender_email: test@example.com
   recipient_email: customer@example.com
   tracking_number: TEST001
   tracking_prefix: CDPE
   weight: 5
   length: 50
   width: 40
   height: 30
   ```
4. Upload the file
5. Check result - should process successfully

#### Test 3: Verify CBM in Database

```sql
-- Check the test shipment
SELECT 
    i.order_item_id,
    i.order_item_length,
    i.order_item_width,
    i.order_item_height,
    i.cbm,
    o.total_cbm
FROM cdb_add_order_item i
JOIN cdb_add_order o ON i.order_id = o.order_id
WHERE o.order_no = 'TEST001';
```

**Expected CBM:** (50 × 40 × 30) / 1,000,000 = 0.0600 m³

---

### Step 6: Configure Settings (Optional)

Currently, CBM settings must be configured directly in database:

```sql
-- Enable CBM and set default rate
UPDATE cdb_settings 
SET 
    cbm_calculation_enabled = 1,
    cbm_rate_per_cubic_meter = 50.00,
    cbm_vs_weight_priority = 'higher'
WHERE id = 1;
```

**Settings Explained:**
- `cbm_calculation_enabled`: 1 = enabled, 0 = disabled
- `cbm_rate_per_cubic_meter`: Default rate (e.g., $50 per m³)
- `cbm_vs_weight_priority`: 
  - `'higher'` = use whichever charge is higher
  - `'weight'` = always use weight-based charge
  - `'cbm'` = always use CBM-based charge

---

## Troubleshooting

### Problem: Migration Fails with "Column already exists"

**Solution:** Columns may already exist. Check with:
```sql
DESCRIBE cdb_add_order_item;
```

If columns exist, skip to Step 4 (Verify Installation).

---

### Problem: "Table 'cdb_cbm_pricing_tiers' already exists"

**Solution:** Table was created previously. You can:

**Option A:** Drop and recreate
```sql
DROP TABLE IF EXISTS cdb_cbm_pricing_tiers;
-- Then run migration again
```

**Option B:** Keep existing table
```sql
-- Just verify it has data
SELECT * FROM cdb_cbm_pricing_tiers;
```

---

### Problem: CBM Shows 0.0000 for All Orders

**Cause:** Orders don't have dimensions entered.

**Solution:** 
1. Check if dimensions exist:
   ```sql
   SELECT order_item_length, order_item_width, order_item_height 
   FROM cdb_add_order_item 
   LIMIT 5;
   ```

2. If dimensions are 0 or NULL, CBM will be 0
3. Enter dimensions when creating new shipments

---

### Problem: Bulk Upload Doesn't Calculate CBM

**Cause:** Modified file not uploaded or PHP function not found.

**Solution:**
1. Verify `helpers/functions.php` has `cdp_calculateCBM()` function
2. Verify `ajax/consolidate/process_bulk_upload_ajax.php` is updated
3. Check PHP error log for issues
4. Clear any PHP cache (if using OPcache)

---

### Problem: JavaScript Console Errors

**Cause:** JavaScript file not uploaded or cached.

**Solution:**
1. Verify `dataJs/consolidate_add.js` is updated
2. Clear browser cache (Ctrl+Shift+Delete)
3. Hard refresh page (Ctrl+F5)
4. Check browser console (F12) for specific errors

---

## Verification Checklist

Use this checklist to confirm installation:

- [ ] Database backup created
- [ ] All files uploaded to server
- [ ] Migration script executed successfully
- [ ] No SQL errors in phpMyAdmin
- [ ] `cdb_add_order_item` has `cbm` column
- [ ] `cdb_cbm_pricing_tiers` table exists
- [ ] Test bulk upload works
- [ ] CBM calculated for test shipment
- [ ] CBM value stored in database
- [ ] No PHP errors in error log
- [ ] No JavaScript errors in console

---

## What Works After Installation

✅ **Immediate Functionality:**
- Database structure ready for CBM
- PHP functions available for calculations
- Bulk upload calculates and stores CBM
- Existing data migrated with CBM values

⏳ **Requires Additional Setup:**
- UI display of CBM (forms need updating)
- Manual entry of CBM rates
- CBM in reports and invoices
- Settings configuration page

See `CBM_IMPLEMENTATION_STATUS.md` for complete status.

---

## Next Steps After Installation

### For Developers:

1. **Complete JavaScript Integration**
   - Update remaining `dataJs/*.js` files
   - Follow pattern in `consolidate_add.js`

2. **Update View Files**
   - Add CBM display fields
   - Add container capacity inputs
   - See `CBM_IMPLEMENTATION_GUIDE.md` for examples

3. **Update AJAX Handlers**
   - Save CBM values from forms
   - Update database queries

### For Users:

1. **Start Using Bulk Upload**
   - Include dimensions in your uploads
   - CBM will be calculated automatically

2. **Monitor CBM Data**
   - Use queries in `sql/cbm_test_queries.sql`
   - Check container utilization
   - Analyze weight vs CBM charges

3. **Configure Settings**
   - Set your CBM rates
   - Choose charge priority
   - Adjust pricing tiers

---

## Rollback Instructions

If you need to undo the installation:

### Step 1: Restore Database Backup

```bash
mysql -u username -p database_name < backup_before_cbm.sql
```

### Step 2: Remove Added Columns (Alternative)

If you don't want to restore full backup:

```sql
-- Remove CBM columns
ALTER TABLE cdb_add_order_item DROP COLUMN cbm, DROP COLUMN cbm_charge;
ALTER TABLE cdb_add_order DROP COLUMN total_cbm, DROP COLUMN cbm_rate, DROP COLUMN total_cbm_charge, DROP COLUMN charge_method;
ALTER TABLE cdb_consolidate DROP COLUMN total_cbm, DROP COLUMN max_cbm_capacity, DROP COLUMN cbm_utilization_percent;
ALTER TABLE cdb_customers_packages DROP COLUMN total_cbm, DROP COLUMN cbm_rate;
ALTER TABLE cdb_customers_packages_detail DROP COLUMN order_item_cbm;
ALTER TABLE cdb_address_locker DROP COLUMN cbm_capacity, DROP COLUMN current_cbm_used;
ALTER TABLE cdb_settings DROP COLUMN cbm_calculation_enabled, DROP COLUMN cbm_rate_per_cubic_meter, DROP COLUMN cbm_vs_weight_priority;

-- Remove pricing tiers table
DROP TABLE IF EXISTS cdb_cbm_pricing_tiers;
```

### Step 3: Restore Original Files

Replace modified files with originals from your backup.

---

## Support & Documentation

- **Full Guide:** `CBM_IMPLEMENTATION_GUIDE.md`
- **Status Report:** `CBM_IMPLEMENTATION_STATUS.md`
- **Test Queries:** `sql/cbm_test_queries.sql`
- **Migration Script:** `sql/cbm_migration.sql`

---

## FAQ

**Q: Will this affect my existing data?**
A: No, existing data is preserved. CBM is calculated from existing dimensions where available.

**Q: Do I need to re-enter all my shipments?**
A: No, the migration automatically calculates CBM for existing shipments that have dimensions.

**Q: Can I disable CBM if I don't want to use it?**
A: Yes, set `cbm_calculation_enabled = 0` in settings. The columns will remain but won't be used.

**Q: How do I set different CBM rates for different customers?**
A: Currently, you can use the pricing tiers table or set rates per shipment. Customer-specific rates require additional development.

**Q: Will this slow down my system?**
A: No, CBM calculation is very fast (simple multiplication/division). No performance impact.

**Q: Can I customize the CBM formula?**
A: Yes, edit the `cdp_calculateCBM()` function in `helpers/functions.php`.

---

## Installation Complete! 🎉

If all verification steps passed, your CBM feature is installed and ready to use!

**What to do next:**
1. Read `CBM_IMPLEMENTATION_GUIDE.md` for usage instructions
2. Check `CBM_IMPLEMENTATION_STATUS.md` for development roadmap
3. Start using bulk upload with dimensions
4. Monitor CBM data with test queries

**Need help?** Review the troubleshooting section or check the documentation files.

---

**Installation Date:** _______________
**Installed By:** _______________
**Database Backup Location:** _______________
**Notes:** _______________

---
