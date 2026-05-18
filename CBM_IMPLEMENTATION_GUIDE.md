# CBM (Cubic Meter) Implementation Guide

## Overview
This guide documents the CBM (Cubic Meter) calculation feature added to the Deprixa Pro shipping system. CBM is used to calculate shipping charges based on volume rather than weight, which is essential for lightweight but bulky items.

---

## What is CBM?

**CBM (Cubic Meter)** is a volume measurement calculated as:
```
CBM = Length × Width × Height / 1,000,000 (when dimensions are in centimeters)
```

For example:
- Package: 50cm × 40cm × 30cm
- CBM = (50 × 40 × 30) / 1,000,000 = 0.06 m³

---

## Implementation Summary

### Phase 1: Database Changes ✅

**Migration File:** `sql/cbm_migration.sql`

**Tables Modified:**
1. `cdb_add_order_item` - Added `cbm` and `cbm_charge` columns
2. `cdb_add_order` - Added `total_cbm`, `cbm_rate`, `total_cbm_charge`, `charge_method`
3. `cdb_consolidate` - Added `total_cbm`, `max_cbm_capacity`, `cbm_utilization_percent`
4. `cdb_customers_packages` - Added `total_cbm`, `cbm_rate`
5. `cdb_customers_packages_detail` - Added `order_item_cbm`
6. `cdb_address_locker` - Added `cbm_capacity`, `current_cbm_used`
7. `cdb_settings` - Added CBM configuration fields

**New Table Created:**
- `cdb_cbm_pricing_tiers` - For tiered CBM pricing

**To Run Migration:**
```sql
-- Execute in phpMyAdmin or MySQL client
source sql/cbm_migration.sql;
```

---

### Phase 2: PHP Helper Functions ✅

**File:** `helpers/functions.php`

**New Functions Added:**

1. **`cdp_calculateCBM($length, $width, $height, $unit = 'cm')`**
   - Calculates CBM from dimensions
   - Supports cm, inch, and meter units
   - Returns: float (rounded to 4 decimals)

2. **`cdp_calculateCBMCharge($cbm, $rate_per_cbm, $min_charge = 0)`**
   - Calculates charge based on CBM
   - Returns: float

3. **`cdp_getChargeableWeight($actual_weight, $volumetric_weight, $cbm, $weight_rate, $cbm_rate, $priority = 'higher')`**
   - Determines whether to use weight or CBM for charging
   - Priority options: 'weight', 'cbm', 'higher'
   - Returns: array with charge details

4. **`cdp_getCBMPricingTier($cbm)`**
   - Gets pricing tier based on CBM value
   - Returns: array or null

5. **`cdp_calculateCBMUtilization($used_cbm, $max_cbm)`**
   - Calculates percentage of CBM used in containers
   - Returns: float (0-100)

6. **`cdp_formatCBM($cbm, $decimals = 4)`**
   - Formats CBM for display
   - Returns: string with m³ unit

7. **`cdp_getStandardContainerCBM()`**
   - Returns standard container capacities
   - 20ft = 33 CBM, 40ft = 67 CBM, etc.

8. **`cdp_updateLockerCBMUsage($locker_id)`**
   - Updates locker CBM usage
   - Returns: bool

---

### Phase 3: Bulk Upload Integration ✅

**File:** `ajax/consolidate/process_bulk_upload_ajax.php`

**Changes:**
- Added CBM calculation when creating shipments from Excel/CSV
- CBM is automatically calculated from length, width, height
- CBM value is stored in `cdb_add_order_item` table
- Total CBM is updated in `cdb_add_order` table

**Usage:**
When uploading bulk shipments, the system now:
1. Calculates CBM for each package
2. Stores CBM in database
3. Updates order totals with CBM

---

### Phase 4: JavaScript Calculations ✅

**File:** `dataJs/consolidate_add.js`

**Changes:**
- Added `sumador_cbm` variable to accumulate CBM
- CBM calculation: `(length × width × height) / 1000000`
- Display total CBM in UI
- Store CBM value in hidden input field

**Similar Updates Needed For:**
- `dataJs/consolidate_edit.js`
- `dataJs/consolidate_package_add.js`
- `dataJs/consolidate_package_edit.js`
- `dataJs/courier_add.js`
- `dataJs/courier_edit.js`
- `dataJs/customers_packages_add.js`
- `dataJs/customers_packages_edit.js`

---

## How to Use CBM Feature

### 1. Run Database Migration

```bash
# Login to MySQL
mysql -u your_username -p your_database

# Run migration
source sql/cbm_migration.sql;
```

### 2. Enable CBM in Settings

Navigate to: **Settings > System Configuration**

Set:
- **CBM Calculation Enabled:** Yes
- **CBM Rate per Cubic Meter:** (e.g., 50.00)
- **Charge Priority:** Higher (uses whichever is higher: weight or CBM)

### 3. Using CBM in Shipments

**Automatic Calculation:**
- When you enter Length, Width, Height for a package
- CBM is automatically calculated
- System compares weight-based charge vs CBM-based charge
- Uses the higher value (configurable)

**Manual Override:**
- You can manually set CBM rate per shipment
- Override charge method if needed

### 4. Container Management

**CBM Capacity:**
- Set maximum CBM capacity for containers
- Standard: 20ft = 33 CBM, 40ft = 67 CBM
- System tracks utilization percentage

**Example:**
```
Container: 40ft (67 CBM capacity)
Current Usage: 45.5 CBM
Utilization: 67.9%
Remaining: 21.5 CBM
```

### 5. Locker Management

**CBM Tracking:**
- Set CBM capacity for each locker
- System tracks current usage
- Alerts when near capacity

---

## CBM Pricing Examples

### Example 1: Small Package
```
Dimensions: 30cm × 20cm × 15cm
CBM = (30 × 20 × 15) / 1,000,000 = 0.009 m³
Rate: $50 per m³
Charge: 0.009 × $50 = $0.45
```

### Example 2: Large Package
```
Dimensions: 100cm × 80cm × 60cm
CBM = (100 × 80 × 60) / 1,000,000 = 0.48 m³
Rate: $50 per m³
Charge: 0.48 × $50 = $24.00
```

### Example 3: Weight vs CBM Comparison
```
Package: 5kg, 80cm × 60cm × 50cm

Weight Charge:
- Actual Weight: 5kg
- Volumetric Weight: (80×60×50)/5000 = 48kg
- Chargeable Weight: 48kg (higher)
- Rate: $2 per kg
- Charge: 48 × $2 = $96

CBM Charge:
- CBM: (80×60×50)/1,000,000 = 0.24 m³
- Rate: $50 per m³
- Charge: 0.24 × $50 = $12

Result: Use Weight Charge ($96) as it's higher
```

---

## Standard Container CBM Capacities

| Container Type | CBM Capacity | Dimensions (L×W×H) |
|---------------|--------------|-------------------|
| 20ft Standard | 33 m³ | 5.9m × 2.35m × 2.39m |
| 40ft Standard | 67 m³ | 12.03m × 2.35m × 2.39m |
| 40ft High Cube | 76 m³ | 12.03m × 2.35m × 2.69m |
| 45ft High Cube | 86 m³ | 13.55m × 2.35m × 2.69m |

---

## Next Steps (To Complete Implementation)

### 1. Update Remaining JavaScript Files
Apply similar CBM calculations to:
- ✅ `dataJs/consolidate_add.js` (DONE)
- ⏳ `dataJs/consolidate_edit.js`
- ⏳ `dataJs/consolidate_package_add.js`
- ⏳ `dataJs/consolidate_package_edit.js`
- ⏳ `dataJs/courier_add.js`
- ⏳ `dataJs/courier_edit.js`
- ⏳ `dataJs/customers_packages_add.js`
- ⏳ `dataJs/customers_packages_edit.js`

### 2. Update View Files (PHP)
Add CBM display fields to:
- `views/consolidate/consolidate_add.php`
- `views/consolidate/consolidate_edit.php`
- `views/courier/courier_add.php`
- `views/customers_packages/customers_packages_add.php`
- And their corresponding edit/view pages

### 3. Update AJAX Handlers
Modify AJAX files to save CBM values:
- `ajax/consolidate/consolidate_add_ajax.php`
- `ajax/courier/add_courier_ajax.php`
- `ajax/customers_packages/add_customers_packages_ajax.php`

### 4. Update PDF/Print Templates
Add CBM to invoices and labels:
- `pdf/documentos/html/consolidate_print.php`
- `pdf/documentos/html/shipment_print.php`
- `pdf/documentos/html/package_print.php`

### 5. Create Settings Page
- `views/config/config_cbm.php` - CBM configuration interface
- Add to settings menu

### 6. Update Reports
Add CBM columns to:
- Consolidate reports
- Shipment reports
- Package reports
- Container utilization reports

---

## Testing Checklist

- [ ] Run database migration successfully
- [ ] Create new shipment with CBM calculation
- [ ] Bulk upload with CBM
- [ ] Edit existing shipment (CBM preserved)
- [ ] Create container with CBM capacity tracking
- [ ] View CBM in reports
- [ ] Print invoice with CBM
- [ ] Test locker CBM tracking
- [ ] Test CBM pricing tiers
- [ ] Test charge priority (weight vs CBM)

---

## Troubleshooting

### CBM Shows 0.0000
**Cause:** Dimensions not entered or all zero
**Solution:** Ensure Length, Width, Height are all > 0

### CBM Not Calculating
**Cause:** JavaScript not loaded or function missing
**Solution:** Check browser console for errors, ensure functions.php is included

### Migration Fails
**Cause:** Table already has columns or syntax error
**Solution:** Check if columns exist, drop them first if needed

### Wrong CBM Value
**Cause:** Unit mismatch (cm vs inch vs m)
**Solution:** Verify unit parameter in `cdp_calculateCBM()` function

---

## Support

For questions or issues:
1. Check this documentation
2. Review code comments in modified files
3. Check database migration log
4. Test with sample data

---

## Version History

**Version 1.0** (Current)
- Initial CBM implementation
- Database schema updates
- PHP helper functions
- Bulk upload integration
- JavaScript calculations (partial)

**Planned Updates:**
- Complete JavaScript integration
- UI updates for all forms
- Settings configuration page
- Report enhancements
- PDF template updates

---

## Files Modified/Created

### Created:
- `sql/cbm_migration.sql`
- `CBM_IMPLEMENTATION_GUIDE.md` (this file)

### Modified:
- `helpers/functions.php` (added CBM functions)
- `ajax/consolidate/process_bulk_upload_ajax.php` (added CBM calculation)
- `dataJs/consolidate_add.js` (added CBM calculation)

### To Be Modified:
- Multiple JavaScript files (see Next Steps)
- Multiple view files (see Next Steps)
- Multiple AJAX handlers (see Next Steps)
- PDF templates (see Next Steps)

---

**End of Guide**
