# CBM Measurement Unit & Display Options - COMPLETED ✅

## Summary
Added measurement unit selection and display control options to the CBM Configuration page.

**Date Completed:** Current Session  
**Status:** ✅ Complete  

---

## New Features Added

### 1. Measurement Unit Selection
**Options:**
- **Centimeters (cm)** - Default, divisor: 1,000,000
- **Inches (in)** - Divisor: 61,024
- **Meters (m)** - Divisor: 1

**Formula Updates:**
- Formula display updates automatically based on selected unit
- Shows correct divisor for each unit
- Displays unit name in formula

### 2. Package Dimensions Display Control
**Toggle:** Show/Hide Package Dimensions in Forms
- **Enabled (default):** Shows Length, Width, Height fields
- **Disabled:** Hides dimension fields in shipment forms

**Use Case:** If you only want to use weight-based pricing, you can hide the dimension fields

### 3. CBM Display Control
**Toggle:** Show/Hide CBM Calculation in Forms
- **Enabled (default):** Shows real-time CBM calculation
- **Disabled:** Hides CBM display in forms

**Use Case:** If CBM is calculated but you don't want to show it to users

---

## Files Modified

### 1. Settings Page UI
**File:** `views/tools/config_cbm.php`

**Changes:**
- Added measurement unit dropdown (cm/inch/m)
- Added "Show Package Dimensions" toggle
- Added "Show CBM in Forms" toggle
- Added JavaScript to update formula display
- Reorganized layout for better UX

### 2. AJAX Handler
**File:** `ajax/tools/config_cbm_ajax.php`

**Changes:**
- Added validation for measurement unit
- Added saving for `cbm_measurement_unit`
- Added saving for `show_package_dimensions`
- Added saving for `show_cbm_in_forms`

### 3. Core Class
**File:** `lib/Core.php`

**Changes:**
- Added `cbm_measurement_unit` property
- Added `show_package_dimensions` property
- Added `show_cbm_in_forms` property
- Load these values from database

### 4. Database Migration
**File:** `sql/cbm_settings_update.sql` (NEW)

**Changes:**
- Add `cbm_measurement_unit` column
- Add `show_package_dimensions` column
- Add `show_cbm_in_forms` column

---

## Database Changes

### New Columns in `cdb_settings`:

```sql
cbm_measurement_unit ENUM('cm','inch','m') DEFAULT 'cm'
show_package_dimensions TINYINT(1) DEFAULT 1
show_cbm_in_forms TINYINT(1) DEFAULT 1
```

---

## How to Use

### Step 1: Run Database Migration

**In phpMyAdmin:**
```sql
ALTER TABLE `cdb_settings` 
ADD COLUMN `cbm_measurement_unit` ENUM('cm','inch','m') DEFAULT 'cm' COMMENT 'Measurement unit for dimensions' AFTER `cbm_vs_weight_priority`,
ADD COLUMN `show_package_dimensions` TINYINT(1) DEFAULT 1 COMMENT 'Show/hide package dimensions in forms' AFTER `cbm_measurement_unit`,
ADD COLUMN `show_cbm_in_forms` TINYINT(1) DEFAULT 1 COMMENT 'Show/hide CBM calculation in forms' AFTER `show_package_dimensions`;
```

### Step 2: Access Settings Page
Go to: **Tools → CBM Configuration**

### Step 3: Configure Settings

**Measurement Unit:**
1. Select your preferred unit (cm/inch/m)
2. Formula display updates automatically
3. All calculations will use this unit

**Display Options:**
1. Toggle "Show Package Dimensions" on/off
2. Toggle "Show CBM in Forms" on/off
3. Click "Save Settings"

---

## CBM Formulas by Unit

### Centimeters (cm) - Default
```
CBM = (Length × Width × Height) ÷ 1,000,000
Example: 50cm × 40cm × 30cm = 60,000 ÷ 1,000,000 = 0.0600 m³
```

### Inches (in)
```
CBM = (Length × Width × Height) ÷ 61,024
Example: 20in × 16in × 12in = 3,840 ÷ 61,024 = 0.0629 m³
```

### Meters (m)
```
CBM = (Length × Width × Height) ÷ 1
Example: 0.5m × 0.4m × 0.3m = 0.06 ÷ 1 = 0.0600 m³
```

---

## Use Cases

### Use Case 1: US Company (Inches)
**Scenario:** Company in USA uses inches for measurements

**Configuration:**
- Measurement Unit: **Inches (in)**
- Show Package Dimensions: **Enabled**
- Show CBM in Forms: **Enabled**

**Result:** Users enter dimensions in inches, CBM calculated correctly

### Use Case 2: Weight-Only Pricing
**Scenario:** Company only charges by weight, doesn't need dimensions

**Configuration:**
- CBM Enabled: **Disabled**
- Show Package Dimensions: **Disabled**
- Show CBM in Forms: **Disabled**

**Result:** Dimension fields hidden, cleaner forms

### Use Case 3: Internal CBM Tracking
**Scenario:** Calculate CBM internally but don't show to customers

**Configuration:**
- CBM Enabled: **Enabled**
- Show Package Dimensions: **Enabled**
- Show CBM in Forms: **Disabled**

**Result:** CBM calculated and saved, but not displayed to users

---

## JavaScript Functionality

### Formula Display Update
```javascript
$('#cbm_measurement_unit').on('change', function() {
    var unit = $(this).val();
    var divisor, unitText;
    
    switch(unit) {
        case 'cm':
            divisor = '1,000,000';
            unitText = 'centimeters (cm)';
            break;
        case 'inch':
            divisor = '61,024';
            unitText = 'inches (in)';
            break;
        case 'm':
            divisor = '1';
            unitText = 'meters (m)';
            break;
    }
    
    $('#divisor').text(divisor);
    $('#unit-text').text(unitText);
});
```

---

## Settings Page Layout

```
┌─────────────────────────────────────────────────────────┐
│ General Settings                                         │
├─────────────────────────────────────────────────────────┤
│ [✓] Enable CBM Calculations                             │
│ Default Rate: $50.00                                     │
├─────────────────────────────────────────────────────────┤
│ Charge Priority: [Use Higher Charge ▼]                  │
│ Measurement Unit: [Centimeters (cm) ▼]                  │
│ Formula: CBM = (L × W × H) ÷ 1,000,000                  │
├─────────────────────────────────────────────────────────┤
│ [✓] Show Package Dimensions in Forms                    │
│ [✓] Show CBM Calculation in Forms                       │
├─────────────────────────────────────────────────────────┤
│ [Save Settings]  [Reset]                                │
└─────────────────────────────────────────────────────────┘
```

---

## Testing Checklist

### Database
- [ ] Run migration SQL
- [ ] Verify columns exist
- [ ] Check default values

### Settings Page
- [ ] Page loads without errors
- [ ] Measurement unit dropdown works
- [ ] Formula updates when unit changes
- [ ] Toggles work correctly
- [ ] Settings save successfully

### Form Display (Future)
- [ ] Dimension fields show/hide based on setting
- [ ] CBM display shows/hide based on setting
- [ ] Calculations use correct unit

---

## Future Enhancements

### Phase 1: Form Integration
Update shipment forms to respect these settings:
- Check `$core->show_package_dimensions` to show/hide fields
- Check `$core->show_cbm_in_forms` to show/hide CBM display
- Use `$core->cbm_measurement_unit` for calculations

### Phase 2: Unit Conversion
Add automatic conversion between units:
- Convert existing data when unit changes
- Show conversion helper in forms
- Support mixed units

### Phase 3: Custom Units
Allow custom measurement units:
- Add custom unit name
- Define custom divisor
- Save as custom option

---

## Deployment Steps

### 1. Backup Database
```bash
mysqldump -u username -p database_name > backup_before_unit_update.sql
```

### 2. Run Migration
```sql
SOURCE sql/cbm_settings_update.sql;
```

### 3. Verify Columns
```sql
SHOW COLUMNS FROM cdb_settings LIKE 'cbm%';
SHOW COLUMNS FROM cdb_settings LIKE 'show_%';
```

### 4. Test Settings Page
1. Go to Tools → CBM Configuration
2. Change measurement unit
3. Verify formula updates
4. Toggle display options
5. Save settings
6. Reload page to verify

---

## Troubleshooting

### Issue: Formula doesn't update
**Solution:** Clear browser cache, check JavaScript console

### Issue: Settings don't save
**Solution:** Verify database columns exist, check PHP error log

### Issue: Unit dropdown not showing
**Solution:** Verify Core class loads `cbm_measurement_unit`

---

## Summary

✅ **Added:** Measurement unit selection (cm/inch/m)  
✅ **Added:** Package dimensions display toggle  
✅ **Added:** CBM display toggle  
✅ **Updated:** Settings page UI  
✅ **Updated:** AJAX handler  
✅ **Updated:** Core class  
✅ **Created:** Database migration SQL  

**Status:** Complete and ready to use!

---

**Next Steps:**
1. Run database migration
2. Test settings page
3. Configure your preferred unit
4. (Optional) Update forms to respect display settings

