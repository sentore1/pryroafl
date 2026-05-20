# CBM Input Field Feature

## Overview
This feature adds flexible input options for package measurements in your shipping system. You can now choose between:
- **Dimensions Input** (Length × Width × Height)
- **Direct CBM Input** (Cubic Meters)
- **Both Methods** (User's choice)

## What's New

### 1. New Setting: `show_cbm_input_field`
Controls whether users can enter CBM directly instead of dimensions.

### 2. Flexible Configuration
Three configuration modes available:

| Mode | Dimensions | CBM Input | Use Case |
|------|-----------|-----------|----------|
| **Dimensions Only** | ✓ | ✗ | Parcels, precise measurements |
| **CBM Only** | ✗ | ✓ | Freight, consolidation (e.g., "1 CBM") |
| **Both Methods** | ✓ | ✓ | Maximum flexibility |

### 3. Smart Validation
- Fields are conditionally required based on settings
- At least one input method must be enabled
- Automatic CBM calculation from dimensions when available

## Installation Steps

### Step 1: Run Database Migration
Execute the SQL file to add the new column:

```sql
-- Run this in your database
ALTER TABLE `cdb_settings` 
ADD COLUMN `show_cbm_input_field` TINYINT(1) DEFAULT 0 
AFTER `show_cbm_in_forms`;
```

Or use the migration test file:
```
http://yourdomain.com/test_cbm_input_field_migration.php
```

### Step 2: Verify Files Updated
The following files have been modified:

**Core Files:**
- `lib/Core.php` - Added `show_cbm_input_field` property
- `ajax/tools/config_cbm_ajax.php` - Added setting save logic

**Configuration:**
- `views/tools/config_cbm.php` - Added UI controls
- `sql/add_cbm_input_field_setting.sql` - Database migration

**Courier Forms:**
- `views/courier/courier_add.php` - Added hidden settings
- `dataJs/courier_add.js` - Updated form rendering and validation

### Step 3: Configure Your Preference
Go to: **Tools → CBM Configuration**

Choose your input method:
1. **Package Dimensions Input** - Show L × W × H fields
2. **CBM Direct Input** - Show CBM input field
3. **CBM Calculation Display** - Show total CBM

## How It Works

### Dimensions Only Mode
```
User enters: Length = 100cm, Width = 50cm, Height = 40cm
System calculates: CBM = (100 × 50 × 40) ÷ 1,000,000 = 0.2 m³
```

### CBM Only Mode
```
User enters: CBM = 1 m³
System uses: 1 m³ directly for calculations
```

### Both Methods Enabled
```
User can enter either:
- Dimensions → System calculates CBM
- CBM directly → System uses it
```

## Calculation Logic

### When Dimensions Are Provided:
```javascript
CBM = (Length × Width × Height) ÷ 1,000,000
Volumetric Weight = (Length × Width × Height) ÷ volumetric_percentage
```

### When CBM Is Provided Directly:
```javascript
CBM = User Input
Volumetric Weight = CBM × 1,000,000 ÷ volumetric_percentage
```

### Charge Calculation:
Based on `cbm_vs_weight_priority` setting:
- **weight**: Always use actual weight
- **cbm**: Always use volumetric weight
- **higher**: Use whichever is higher

## Configuration Examples

### Example 1: Parcel Service (Dimensions Only)
```
show_package_dimensions = 1
show_cbm_input_field = 0
show_cbm_in_forms = 1
```
**Result:** Users enter L×W×H, see calculated CBM

### Example 2: Freight Service (CBM Only)
```
show_package_dimensions = 0
show_cbm_input_field = 1
show_cbm_in_forms = 1
```
**Result:** Users enter CBM directly (e.g., "1 CBM", "0.5 CBM")

### Example 3: Mixed Service (Both)
```
show_package_dimensions = 1
show_cbm_input_field = 1
show_cbm_in_forms = 1
```
**Result:** Users can use either method

## Validation Rules

### When Dimensions Are Shown:
- Length, Width, Height are **required**
- Must be greater than 0
- CBM is calculated automatically

### When CBM Input Is Shown (and dimensions hidden):
- CBM value is **required**
- Must be greater than 0
- Dimensions are optional/hidden

### When Both Are Shown:
- User must provide at least one method
- If both provided, CBM input takes priority

## Database Schema

### New Column:
```sql
show_cbm_input_field TINYINT(1) DEFAULT 0
```

**Values:**
- `0` = Hidden (default)
- `1` = Shown

### Related Columns:
- `show_package_dimensions` - Show/hide L×W×H fields
- `show_cbm_in_forms` - Show/hide total CBM display
- `cbm_measurement_unit` - Unit for dimensions (cm, inch, m)
- `cbm_vs_weight_priority` - Charge calculation method

## JavaScript Changes

### Package Item Structure:
```javascript
{
  qty: 1,
  description: "",
  length: 0,
  width: 0,
  height: 0,
  weight: 0,
  declared_value: 0,
  fixed_value: 0,
  cbm_value: 0  // NEW
}
```

### Conditional Rendering:
Fields are shown/hidden based on settings loaded from hidden inputs:
```javascript
var show_dimensions = $("#show_package_dimensions").val() == "1";
var show_cbm_input = $("#show_cbm_input_field").val() == "1";
```

## Testing

### Test File:
`test_cbm_input_field_migration.php`

**Features:**
- Check if database column exists
- View current settings
- Test Core class loading
- Quick setup buttons
- Configuration recommendations

### Manual Testing:
1. Go to CBM Configuration
2. Enable/disable different combinations
3. Create a new shipment
4. Verify correct fields are shown
5. Test validation
6. Check calculations

## Troubleshooting

### Issue: Column doesn't exist
**Solution:** Run the SQL migration or use the test file

### Issue: Settings not saving
**Solution:** Check ajax/tools/config_cbm_ajax.php for errors

### Issue: Fields not showing/hiding
**Solution:** 
1. Clear browser cache
2. Check hidden inputs in courier_add.php
3. Verify JavaScript console for errors

### Issue: Validation errors
**Solution:** Ensure at least one input method is enabled

### Issue: CBM calculation wrong
**Solution:** Check `cbm_measurement_unit` setting matches your input

## Future Enhancements

Potential additions:
- [ ] Apply to consolidate forms
- [ ] Apply to customer package forms
- [ ] Apply to pickup forms
- [ ] Add CBM presets (standard pallet sizes)
- [ ] Bulk import with CBM values
- [ ] CBM-based pricing tiers

## Support

For issues or questions:
1. Check this README
2. Run test_cbm_input_field_migration.php
3. Check browser console for JavaScript errors
4. Verify database column exists
5. Contact support with error details

## Version History

**Version 1.0** (Current)
- Initial release
- Added show_cbm_input_field setting
- Updated courier_add form
- Conditional validation
- Smart calculation logic

---

**Last Updated:** 2024
**Compatible With:** DEPRIXA PRO Shipping System
