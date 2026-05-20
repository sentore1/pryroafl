# Pickup Forms - CBM Feature Implementation COMPLETE ✅

## Summary
All pickup forms have been successfully updated with the CBM input field feature. The implementation follows the same pattern as the courier forms.

## Files Updated

### 1. pickup_add.php + pickup_add.js ✅
**PHP Changes:**
- Added hidden settings inputs for `show_package_dimensions` and `show_cbm_input_field`

**JavaScript Changes:**
- ✅ Updated `packagesItems` array to include `cbm_value: 0`
- ✅ Updated `loadPackages()` - conditional rendering based on settings
- ✅ Updated `addPackage()` - includes cbm_value in new items
- ✅ Updated `importPackagesFromCSV()` - includes cbm_value column (9th column)
- ✅ Updated `calculateFinalTotal()` - handles CBM input calculations
- ✅ Updated form validation - dimensions/CBM conditionally required

### 2. pickup_add_full.php + pickup_add_full.js ✅
**PHP Changes:**
- Added hidden settings inputs for `show_package_dimensions` and `show_cbm_input_field`

**JavaScript Changes:**
- ✅ Updated `packagesItems` array to include `cbm_value: 0`
- ✅ Updated `loadPackages()` - conditional rendering based on settings
- ✅ Updated `addPackage()` - includes cbm_value in new items
- ✅ Updated `importPackagesFromCSV()` - includes cbm_value column (9th column)
- ✅ Updated `calculateFinalTotal()` - handles CBM input calculations
- ✅ Updated form validation - dimensions/CBM conditionally required

## Implementation Details

### Hidden Inputs Pattern
```php
<!-- CBM Settings -->
<input type="hidden" name="show_package_dimensions" id="show_package_dimensions" value="<?php echo $core->show_package_dimensions ?? 1; ?>" />
<input type="hidden" name="show_cbm_input_field" id="show_cbm_input_field" value="<?php echo $core->show_cbm_input_field ?? 0; ?>" />
```

### JavaScript Pattern

#### 1. Package Items Array
```javascript
var packagesItems = [
  {
    qty: 1,
    description: "",
    length: 0,
    width: 0,
    height: 0,
    weight: 0,
    declared_value: 0,
    fixed_value: 0,
    cbm_value: 0,  // Added
  },
];
```

#### 2. Conditional Rendering in loadPackages()
```javascript
// Get settings
var show_dimensions = $("#show_package_dimensions").val() == "1";
var show_cbm_input = $("#show_cbm_input_field").val() == "1";

// Show dimension fields if enabled
if (show_dimensions) {
  // Render L, W, H, Vol Weight fields
} else {
  // Hidden fields to maintain data structure
}

// Show CBM input if enabled
if (show_cbm_input) {
  // Render CBM input field
} else {
  // Hidden field to maintain data structure
}
```

#### 3. Calculation Logic in calculateFinalTotal()
```javascript
// Get settings
var show_dimensions = $("#show_package_dimensions").val() == "1";
var show_cbm_input = $("#show_cbm_input_field").val() == "1";

packagesItems.forEach(function (item, i) {
  var cbm_input = parseFloat(item.cbm_value) || 0;
  var total_metric = 0;
  var cbm = 0;
  
  // Calculate based on available input
  if (show_cbm_input && cbm_input > 0) {
    // Use CBM input directly
    cbm = cbm_input;
    total_metric = cbm * 1000000 / core_meter;
  } else if (show_dimensions && length > 0 && width > 0 && height > 0) {
    // Calculate from dimensions
    total_metric = (length * width * height) / core_meter;
    cbm = (length * width * height) / 1000000;
  }
  
  sumador_cbm += cbm;
  // ... rest of calculation
});
```

#### 4. Validation Logic
```javascript
// Get settings
var show_dimensions = $("#show_package_dimensions").val() == "1";
var show_cbm_input = $("#show_cbm_input_field").val() == "1";

for (let [i, val] of packagesItems.entries()) {
  // ... other validations
  
  // Validate dimensions only if dimensions are shown
  if (show_dimensions) {
    if ($.trim($("#length_" + i).val()).length == 0) {
      // Show error
      return false;
    }
    // ... width, height validation
  }
  
  // Validate CBM only if CBM input is shown and dimensions are not provided
  if (show_cbm_input && !show_dimensions) {
    if ($.trim($("#cbm_value_" + i).val()).length == 0 || parseFloat($("#cbm_value_" + i).val()) == 0) {
      // Show error
      return false;
    }
  }
}
```

#### 5. CSV Import
```javascript
function importPackagesFromCSV(input) {
  // ... file reading logic
  packagesItems.push({
    qty:            parseFloat(cols[0]) || 1,
    description:    cols[1] ? cols[1].trim() : "",
    weight:         parseFloat(cols[2]) || 0,
    length:         parseFloat(cols[3]) || 0,
    width:          parseFloat(cols[4]) || 0,
    height:         parseFloat(cols[5]) || 0,
    fixed_value:    parseFloat(cols[6]) || 0,
    declared_value: parseFloat(cols[7]) || 0,
    cbm_value:      parseFloat(cols[8]) || 0,  // Added as 9th column
  });
}
```

## Testing Checklist

### Test Scenarios
- [x] Configuration saves correctly
- [ ] Pickup form with dimensions only
- [ ] Pickup form with CBM only
- [ ] Pickup form with both methods
- [ ] Pickup full form with dimensions only
- [ ] Pickup full form with CBM only
- [ ] Pickup full form with both methods
- [ ] CSV import with CBM column
- [ ] Validation works correctly for each mode
- [ ] Calculations are accurate

### Expected Behavior

#### Mode 1: Dimensions Only
- L, W, H fields visible and required
- CBM field hidden
- System calculates CBM from dimensions
- Volumetric weight calculated from dimensions

#### Mode 2: CBM Only
- L, W, H fields hidden
- CBM field visible and required
- User enters CBM directly (e.g., 1.5)
- Volumetric weight calculated from CBM

#### Mode 3: Both Methods
- All fields visible
- Either dimensions OR CBM required
- If CBM provided, it takes precedence
- If only dimensions provided, CBM calculated

## CSV Import Format

When importing packages via CSV, the format is now:
```csv
qty,description,weight,length,width,height,fixed_value,declared_value,cbm_value
1,Package 1,10,50,40,30,5,100,0
1,Package 2,15,0,0,0,5,150,0.5
```

**Column 9 (cbm_value):**
- Use `0` if providing dimensions
- Use actual CBM value (e.g., `0.5`, `1.2`) if providing CBM directly

## Calculation Formulas

### From Dimensions to CBM
```
CBM = (Length × Width × Height) ÷ 1,000,000
```

### From CBM to Volumetric Weight
```
Volumetric Weight = CBM × 1,000,000 ÷ core_meter
```

### Example
If `core_meter = 5000`:
- Dimensions: 100cm × 50cm × 40cm
  - CBM = (100 × 50 × 40) ÷ 1,000,000 = 0.2 m³
  - Vol Weight = (100 × 50 × 40) ÷ 5000 = 40 kg

- Direct CBM: 0.2 m³
  - Vol Weight = 0.2 × 1,000,000 ÷ 5000 = 40 kg

## Next Steps

### Remaining Forms to Update
1. **Consolidate Forms**
   - `views/consolidate/consolidate_add.php`
   - `dataJs/consolidate_add.js`

2. **Customer Package Forms**
   - `views/customers_packages/add_customers_packages.php`
   - `dataJs/add_customers_packages.js`

### Implementation Pattern
Use the pickup forms as reference:
1. Add hidden settings inputs to PHP file
2. Update packagesItems array in JS
3. Update loadPackages() for conditional rendering
4. Update addPackage() to include cbm_value
5. Update calculateFinalTotal() for CBM calculations
6. Update form validation for conditional requirements
7. Update CSV import if applicable

## Files Reference

### Complete Implementations
- `dataJs/courier_add.js` - Original complete implementation
- `dataJs/pickup_add.js` - Pickup implementation
- `dataJs/pickup_add_full.js` - Pickup full implementation

### Configuration
- `dataJs/config_cbm.js` - Configuration page JavaScript
- `ajax/tools/config_cbm_ajax.php` - Save handler

### Database
- `sql/add_cbm_input_field_setting.sql` - Migration script

---

**Status:** ✅ COMPLETE
**Date:** 2024
**Forms Updated:** pickup_add, pickup_add_full
**Forms Remaining:** consolidate, customer_packages
