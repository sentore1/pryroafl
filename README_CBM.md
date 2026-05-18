# CBM (Cubic Meter) Feature - Complete Implementation Guide

## 🎯 Overview

This implementation adds **CBM (Cubic Meter)** calculation functionality to your Deprixa Pro shipping system. CBM is essential for calculating shipping charges based on volume rather than weight, particularly useful for bulky but lightweight items.

---

## 📦 What is CBM?

**CBM = Cubic Meter** - A volume measurement calculated as:

```
CBM = (Length × Width × Height) / 1,000,000
```
*(when dimensions are in centimeters)*

### Example:
- Package: 50cm × 40cm × 30cm
- CBM = (50 × 40 × 30) / 1,000,000 = **0.0600 m³**

---

## 🚀 Quick Start

### 1. Install Database Schema
```bash
mysql -u username -p database_name < sql/cbm_migration.sql
```

### 2. Verify Installation
```sql
SELECT * FROM cdb_cbm_pricing_tiers;
DESCRIBE cdb_add_order_item;
```

### 3. Test with Bulk Upload
1. Go to: **Consolidate > Bulk Upload Container**
2. Upload Excel with dimensions
3. CBM calculated automatically!

### 4. Check Results
```sql
SELECT order_id, total_cbm FROM cdb_add_order WHERE total_cbm > 0;
```

---

## 📚 Documentation Files

| File | Purpose | Status |
|------|---------|--------|
| `INSTALL_CBM.md` | Installation instructions | ✅ Complete |
| `CBM_IMPLEMENTATION_GUIDE.md` | Complete usage guide | ✅ Complete |
| `CBM_IMPLEMENTATION_STATUS.md` | Development status & roadmap | ✅ Complete |
| `CBM_UPDATE_PHASE2.md` | Phase 2 updates summary | ✅ Complete |
| `README_CBM.md` | This file - Quick reference | ✅ Complete |

---

## 📁 Implementation Files

### SQL Files
- `sql/cbm_migration.sql` - Database schema updates
- `sql/cbm_test_queries.sql` - 20 test/verification queries

### PHP Files
- `helpers/functions.php` - 8 new CBM functions added

### JavaScript Files (Updated)
- ✅ `dataJs/consolidate_add.js`
- ✅ `dataJs/consolidate_edit.js`
- ✅ `dataJs/courier_add.js`
- ✅ `dataJs/customers_packages_add.js`

### View Files (Updated)
- ✅ `views/consolidate/consolidate_add.php`

### AJAX Files (Updated)
- ✅ `ajax/consolidate/process_bulk_upload_ajax.php`

---

## ✅ What's Working Now

### Fully Functional:
1. **Database Structure** - All tables updated with CBM fields
2. **PHP Functions** - 8 helper functions for CBM calculations
3. **Bulk Upload** - Automatic CBM calculation from Excel/CSV
4. **Consolidate Add** - Full UI with CBM display and storage
5. **Consolidate Edit** - CBM preserved and editable
6. **JavaScript** - 4 modules with CBM calculations

### Partially Complete:
1. **Courier Module** - JavaScript ready, UI needs update
2. **Customer Packages** - JavaScript ready, UI needs update

### Not Started:
1. PDF/Print templates
2. Reports with CBM
3. Settings configuration page
4. Additional view files

---

## 🎓 Key Features

### 1. Automatic Calculation
- CBM calculated from Length × Width × Height
- Real-time updates as user types
- No manual calculation needed

### 2. Smart Charging
- Compare weight-based vs CBM-based charges
- Use whichever is higher (configurable)
- Pricing tiers support

### 3. Container Management
- Track CBM capacity (20ft = 33 CBM, 40ft = 67 CBM)
- Monitor utilization percentage
- Prevent overloading

### 4. Locker Tracking
- Set CBM capacity per locker
- Track current usage
- Alert when near capacity

### 5. Bulk Operations
- Excel/CSV upload with automatic CBM
- Batch processing
- No manual entry needed

---

## 📊 Current Status

### Overall Completion: ~45%

**✅ Phase 1 - Foundation (100%)**
- Database schema
- PHP helper functions
- Bulk upload integration
- Documentation

**✅ Phase 2 - Core Integration (60%)**
- JavaScript calculations (4/7 files)
- UI updates (1/10 files)
- Database integration

**⏳ Phase 3 - Remaining (0%)**
- Additional JavaScript files
- Additional view files
- PDF templates
- Reports
- Settings page

---

## 🔧 PHP Functions Available

```php
// Calculate CBM from dimensions
$cbm = cdp_calculateCBM($length, $width, $height, 'cm');

// Calculate CBM charge
$charge = cdp_calculateCBMCharge($cbm, $rate_per_cbm);

// Compare weight vs CBM
$result = cdp_getChargeableWeight($weight, $vol_weight, $cbm, $weight_rate, $cbm_rate);

// Get pricing tier
$tier = cdp_getCBMPricingTier($cbm);

// Calculate container utilization
$percent = cdp_calculateCBMUtilization($used_cbm, $max_cbm);

// Format for display
$formatted = cdp_formatCBM($cbm); // "0.0600 m³"

// Get standard container capacities
$capacities = cdp_getStandardContainerCBM();

// Update locker usage
cdp_updateLockerCBMUsage($locker_id);
```

---

## 🧪 Testing

### Quick Test:
```sql
-- 1. Check if migration ran
SELECT COUNT(*) FROM cdb_cbm_pricing_tiers;
-- Should return: 4

-- 2. Check existing orders with CBM
SELECT order_id, order_no, total_cbm 
FROM cdb_add_order 
WHERE total_cbm > 0 
LIMIT 5;

-- 3. Test calculation
SELECT 
    order_item_length,
    order_item_width,
    order_item_height,
    cbm,
    ROUND((order_item_length * order_item_width * order_item_height) / 1000000, 4) as calculated_cbm
FROM cdb_add_order_item
WHERE cbm > 0
LIMIT 5;
```

### UI Test:
1. Open: `consolidate_add.php`
2. Add shipment with dimensions: 50×40×30
3. Verify CBM displays: 0.0600 m³
4. Submit form
5. Check database for saved CBM

---

## 💡 Usage Examples

### Example 1: Small Package
```
Dimensions: 30cm × 20cm × 15cm
CBM = 0.009 m³
Rate: $50/m³
Charge: $0.45
```

### Example 2: Large Package
```
Dimensions: 100cm × 80cm × 60cm
CBM = 0.48 m³
Rate: $50/m³
Charge: $24.00
```

### Example 3: Weight vs CBM
```
Package: 5kg, 80cm × 60cm × 50cm

Weight Charge:
- Volumetric: 48kg
- Rate: $2/kg
- Charge: $96

CBM Charge:
- CBM: 0.24 m³
- Rate: $50/m³
- Charge: $12

Result: Use Weight ($96) - Higher
```

---

## 🎯 Next Steps

### For Developers:

**Immediate (2-3 hours):**
1. Update remaining JavaScript files
2. Update courier view files
3. Update customer packages views

**Short Term (2-3 hours):**
1. Update PDF templates
2. Add CBM to list views
3. End-to-end testing

**Long Term (6-8 hours):**
1. Create settings page
2. Add to reports
3. Container capacity UI
4. Analytics dashboard

### For Users:

**Now:**
1. Use bulk upload with dimensions
2. CBM calculated automatically
3. Monitor in database

**Soon:**
1. See CBM in all forms
2. Print invoices with CBM
3. View CBM reports

---

## 📖 Standard Container Capacities

| Container Type | CBM Capacity | Dimensions (L×W×H) |
|---------------|--------------|-------------------|
| 20ft Standard | 33 m³ | 5.9m × 2.35m × 2.39m |
| 40ft Standard | 67 m³ | 12.03m × 2.35m × 2.39m |
| 40ft High Cube | 76 m³ | 12.03m × 2.35m × 2.69m |
| 45ft High Cube | 86 m³ | 13.55m × 2.35m × 2.69m |

---

## 🔍 Troubleshooting

### CBM shows 0.0000
**Cause:** Dimensions not entered or all zero  
**Solution:** Enter Length, Width, Height > 0

### Migration fails
**Cause:** Columns already exist  
**Solution:** Check with `DESCRIBE cdb_add_order_item`

### JavaScript not calculating
**Cause:** File not uploaded or cached  
**Solution:** Clear browser cache (Ctrl+F5)

### CBM not saving
**Cause:** Hidden input missing  
**Solution:** Check for `#total_cbm_input` in form

---

## 📞 Support Resources

### Documentation:
- **Installation:** `INSTALL_CBM.md`
- **Complete Guide:** `CBM_IMPLEMENTATION_GUIDE.md`
- **Status & Roadmap:** `CBM_IMPLEMENTATION_STATUS.md`
- **Phase 2 Updates:** `CBM_UPDATE_PHASE2.md`

### SQL:
- **Migration:** `sql/cbm_migration.sql`
- **Test Queries:** `sql/cbm_test_queries.sql`

### Code:
- **PHP Functions:** `helpers/functions.php`
- **JavaScript Pattern:** `dataJs/consolidate_add.js`
- **View Pattern:** `views/consolidate/consolidate_add.php`

---

## 🏆 Achievements

- ✅ Solid database foundation
- ✅ Comprehensive PHP functions
- ✅ Bulk upload integration
- ✅ Real-time JavaScript calculations
- ✅ Working UI in consolidate module
- ✅ Extensive documentation
- ✅ Test queries for verification
- ✅ Backward compatible

---

## 📈 Roadmap

### Phase 1: Foundation ✅ (100%)
- Database schema
- PHP functions
- Documentation

### Phase 2: Core Integration ✅ (60%)
- JavaScript calculations
- UI updates
- Database integration

### Phase 3: Completion ⏳ (0%)
- Remaining JavaScript
- Remaining views
- PDF templates
- Reports

### Phase 4: Enhancement ⏳ (0%)
- Settings page
- Analytics
- Advanced features

---

## 🎉 Success Metrics

After full implementation, you'll have:

1. **Automatic CBM calculation** in all modules
2. **Smart charging** (weight vs CBM)
3. **Container capacity tracking**
4. **Locker space management**
5. **Bulk upload** with CBM
6. **Reports** with CBM data
7. **Invoices** showing CBM
8. **Settings** to configure rates

---

## 📝 Quick Reference

### Calculate CBM:
```
CBM = (L × W × H) / 1,000,000
```

### Display CBM:
```javascript
$("#total_cbm").html(cbm.toFixed(4));
```

### Save CBM:
```php
$db->bind(':total_cbm', floatval($_POST["total_cbm_input"]));
```

### Query CBM:
```sql
SELECT order_id, total_cbm FROM cdb_add_order WHERE total_cbm > 0;
```

---

## 🌟 Benefits

### For Business:
- More accurate pricing
- Better container utilization
- Reduced shipping costs
- Improved profitability

### For Operations:
- Automatic calculations
- No manual entry
- Real-time tracking
- Better planning

### For Customers:
- Fair pricing
- Transparent charges
- Accurate quotes
- Better service

---

## 📧 Feedback

Found an issue? Have a suggestion?
- Check documentation first
- Review test queries
- Verify installation
- Check browser console

---

**Version:** 1.0  
**Last Updated:** [Current Date]  
**Status:** Phase 2 Complete (45%)  
**Next Milestone:** Complete JavaScript integration

---

**Ready to use!** The foundation is solid and the core functionality is working. Continue with Phase 3 to complete the full integration across all modules.

