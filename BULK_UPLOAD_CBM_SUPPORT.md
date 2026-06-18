# Bulk Upload CBM Support Implementation

## Overview
The bulk upload feature now supports **two modes** based on the system's CBM calculation setting:

### 1. **CBM Mode** (when `cbm_calculation_enabled = 1`)
- Accepts `cbm` column directly from CSV/Excel
- **No automatic calculation** from dimensions
- Stores CBM value as-is from the file
- Dimensions (length/width/height) are **not required** and set to 0

### 2. **Dimension Mode** (when `cbm_calculation_enabled = 0`)
- Accepts `length`, `width`, `height` columns
- Calculates volumetric weight: `(L × W × H) / 5000`
- CBM value is set to 0
- Traditional dimension-based processing

---

## Files Modified

### 1. `ajax/courier/process_bulk_upload_shipments_ajax.php`
**Changes:**
- Added CBM mode detection: `$core->cbm_calculation_enabled`
- Conditional processing logic:
  - **CBM Mode**: Reads `cbm` column, sets dimensions to 0
  - **Dimension Mode**: Reads dimensions, calculates volumetric weight
- Added `cbm` column to `cdb_add_order_item` INSERT
- Added `total_cbm` column to `cdb_add_order` INSERT
- Stores appropriate values based on active mode

### 2. `courier_bulk_upload.php`
**Changes:**
- Dynamic file format instructions based on CBM mode
- Shows `cbm` column when CBM mode is active
- Shows `length/width/height` columns when Dimension mode is active
- Added visual badges to indicate current mode:
  - 🟦 **Blue badge**: "CBM Mode" when CBM enabled
  - ⚠️ **Warning alert**: Shows which mode is active

### 3. `download_template_shipments.php`
**Changes:**
- Template headers now conditional based on CBM mode
- **CBM Mode Template**: Includes `cbm` column with sample values (0.0075, 0.0240, 0.0540)
- **Dimension Mode Template**: Includes `length`, `width`, `height` columns
- Sample data matches the active mode

---

## Column Structure

### CBM Mode Columns:
```
sender_email, sender_fname, sender_lname, recipient_email, recipient_fname, 
recipient_lname, tracking_prefix, tracking_number, item_description, 
weight, cbm, sender_country, sender_city, sender_address, 
recipient_country, recipient_city, recipient_address
```

### Dimension Mode Columns:
```
sender_email, sender_fname, sender_lname, recipient_email, recipient_fname, 
recipient_lname, tracking_prefix, tracking_number, item_description, 
weight, length, width, height, sender_country, sender_city, sender_address, 
recipient_country, recipient_city, recipient_address
```

---

## Database Storage

### CBM Mode:
```sql
-- cdb_add_order_item
order_item_weight = {weight from CSV}
order_item_length = 0
order_item_width = 0
order_item_height = 0
cbm = {cbm value from CSV}

-- cdb_add_order
total_weight = {weight only, no volumetric}
total_cbm = {cbm value from CSV}
```

### Dimension Mode:
```sql
-- cdb_add_order_item
order_item_weight = {weight from CSV}
order_item_length = {length from CSV}
order_item_width = {width from CSV}
order_item_height = {height from CSV}
cbm = 0

-- cdb_add_order
total_weight = {weight + volumetric_weight}
total_cbm = 0
```

---

## Usage Examples

### CBM Mode CSV Example:
```csv
sender_email,sender_fname,sender_lname,recipient_email,recipient_fname,recipient_lname,tracking_prefix,tracking_number,item_description,weight,cbm,sender_country,sender_city,sender_address,recipient_country,recipient_city,recipient_address
client1@company.com,John,Smith,customer1@email.com,Jane,Doe,CDPE,200001,Electronics - Laptop,2.5,0.0075,USA,New York,123 Main St,Canada,Toronto,456 Oak Ave
```

### Dimension Mode CSV Example:
```csv
sender_email,sender_fname,sender_lname,recipient_email,recipient_fname,recipient_lname,tracking_prefix,tracking_number,item_description,weight,length,width,height,sender_country,sender_city,sender_address,recipient_country,recipient_city,recipient_address
client1@company.com,John,Smith,customer1@email.com,Jane,Doe,CDPE,200001,Electronics - Laptop,2.5,15,10,5,USA,New York,123 Main St,Canada,Toronto,456 Oak Ave
```

---

## Testing Checklist

### Test CBM Mode:
- [ ] Enable CBM mode: `UPDATE cdb_settings SET cbm_calculation_enabled = 1 WHERE id = 1`
- [ ] Download template - verify it has `cbm` column (not dimensions)
- [ ] Upload CSV with CBM values
- [ ] Verify `cbm` column is populated in `cdb_add_order_item`
- [ ] Verify `total_cbm` is populated in `cdb_add_order`
- [ ] Verify dimensions are 0 in database
- [ ] Check bulk upload page shows CBM Mode badge

### Test Dimension Mode:
- [ ] Disable CBM mode: `UPDATE cdb_settings SET cbm_calculation_enabled = 0 WHERE id = 1`
- [ ] Download template - verify it has `length`, `width`, `height` columns
- [ ] Upload CSV with dimensions
- [ ] Verify dimensions are populated in `cdb_add_order_item`
- [ ] Verify volumetric weight is calculated
- [ ] Verify `cbm` is 0 in database
- [ ] Check bulk upload page shows Dimension Mode alert

### Test Mode Switching:
- [ ] Switch from Dimension to CBM mode
- [ ] Download template - verify columns change
- [ ] Upload appropriate CSV for each mode
- [ ] Verify data integrity after mode switch

---

## User Instructions

### For CBM Mode:
1. Navigate to **Settings > CBM Configuration**
2. Enable CBM calculations
3. Go to **Shipping > Bulk Upload Shipments**
4. Download the template (will include `cbm` column)
5. Fill in CBM values directly in the CSV
6. Upload and process

### For Dimension Mode:
1. Navigate to **Settings > CBM Configuration**
2. Disable CBM calculations (or keep default)
3. Go to **Shipping > Bulk Upload Shipments**
4. Download the template (will include `length`, `width`, `height` columns)
5. Fill in dimensions in the CSV
6. Upload and process (system calculates volumetric weight)

---

## Key Behaviors

✅ **No Automatic CBM Calculation in CBM Mode**
- When CBM mode is enabled, the system expects CBM values in the CSV
- **No calculation** from dimensions is performed
- Users must provide pre-calculated CBM values

✅ **Template Adapts Automatically**
- Template download always matches the current system mode
- Headers change automatically based on settings
- Sample data reflects the expected format

✅ **Visual Indicators**
- Upload page shows which mode is active
- Clear badges and alerts guide users
- Instructions change dynamically

✅ **Database Integrity**
- Unused fields are set to 0 (not NULL)
- Mode preference is read from `cdb_settings`
- All existing functionality preserved

---

## SQL Quick Reference

### Enable CBM Mode:
```sql
UPDATE cdb_settings 
SET cbm_calculation_enabled = 1 
WHERE id = 1;
```

### Disable CBM Mode (Dimension Mode):
```sql
UPDATE cdb_settings 
SET cbm_calculation_enabled = 0 
WHERE id = 1;
```

### Check Current Mode:
```sql
SELECT cbm_calculation_enabled 
FROM cdb_settings 
WHERE id = 1;
-- Returns: 1 = CBM Mode, 0 = Dimension Mode
```

---

## Status
✅ **Implementation Complete**
- CBM mode detection implemented
- Conditional CSV column structure
- Template generation updated
- UI instructions updated
- Database storage correct for both modes

**Version:** 1.0  
**Date:** June 18, 2026  
**Compatibility:** Requires CBM migration to be run first
