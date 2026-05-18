# CBM Feature - Quick Reference Card

## 📐 CBM Formula
```
CBM = (Length × Width × Height) / 1,000,000
```
**Example:** 50cm × 40cm × 30cm = 0.0600 m³

---

## 🎯 Where CBM Appears

| Location | Display Format | Status |
|----------|---------------|--------|
| Consolidate Add | Blue info box | ✅ |
| Consolidate Edit | Blue info box | ✅ |
| Courier Add | Blue info box | ✅ |
| Courier Edit | Blue info box | ✅ |
| Customer Package Add | Blue info box | ✅ |
| Customer Package Edit | Blue info box | ✅ |
| Bulk Upload | Automatic | ✅ |
| PDF Invoices | Summary table | ✅ |

---

## 💾 Database Tables with CBM

1. `cdb_add_order` - `total_cbm`
2. `cdb_add_order_item` - `cbm`
3. `cdb_consolidate` - `total_cbm`
4. `cdb_customers_packages` - `total_cbm`
5. `cdb_customers_packages_detail` - `order_item_cbm`
6. `cdb_address_locker` - `cbm_capacity`, `current_cbm_used`
7. `cdb_settings` - `cbm_calculation_enabled`, `cbm_rate_per_cubic_meter`

---

## 🔧 PHP Functions

```php
// Calculate CBM
cdp_calculateCBM($length, $width, $height, 'cm')

// Calculate charge
cdp_calculateCBMCharge($cbm, $rate_per_cbm)

// Compare weight vs CBM
cdp_getChargeableWeight($weight, $vol_weight, $cbm, $weight_rate, $cbm_rate)

// Format for display
cdp_formatCBM($cbm) // Returns: "0.0600 m³"
```

---

## 🧪 Quick Tests

### Test CBM Calculation:
```sql
SELECT 
    order_id,
    order_item_length,
    order_item_width,
    order_item_height,
    cbm,
    (order_item_length * order_item_width * order_item_height) / 1000000 as calculated_cbm
FROM cdb_add_order_item
WHERE cbm > 0
LIMIT 5;
```

### Test Total CBM:
```sql
SELECT 
    order_id,
    total_cbm,
    (SELECT SUM(cbm) FROM cdb_add_order_item WHERE order_id = o.order_id) as sum_cbm
FROM cdb_add_order o
WHERE total_cbm > 0
LIMIT 5;
```

---

## 📋 Common Scenarios

### Scenario 1: Bulky Light Item
- **Item:** Pillows
- **Weight:** 2 kg
- **Dimensions:** 80×60×50 cm
- **CBM:** 0.2400 m³
- **Weight Charge:** $4
- **CBM Charge:** $12
- **Use:** CBM (higher)

### Scenario 2: Heavy Small Item
- **Item:** Books
- **Weight:** 20 kg
- **Dimensions:** 30×20×15 cm
- **CBM:** 0.0090 m³
- **Weight Charge:** $40
- **CBM Charge:** $0.45
- **Use:** Weight (higher)

---

## 🚨 Troubleshooting

| Issue | Solution |
|-------|----------|
| CBM shows 0.0000 | Enter dimensions (L×W×H) |
| Not calculating | Clear browser cache |
| Not saving | Check hidden input exists |
| Not on PDF | Verify database migration |
| Wrong calculation | Check unit (cm vs inch) |

---

## 📞 Quick Support

**Documentation:**
- `CBM_IMPLEMENTATION_GUIDE.md` - Full guide
- `README_CBM.md` - Quick start
- `INSTALL_CBM.md` - Installation

**SQL Files:**
- `sql/cbm_migration.sql` - Database setup
- `sql/cbm_test_queries.sql` - Test queries

---

## ✅ Deployment Checklist

- [ ] Run database migration
- [ ] Test consolidate add/edit
- [ ] Test courier add/edit
- [ ] Test customer packages
- [ ] Test bulk upload
- [ ] Generate PDF invoices
- [ ] Verify CBM on PDFs
- [ ] Train staff
- [ ] Monitor for issues

---

## 📊 Standard Container Capacities

| Container | CBM Capacity |
|-----------|--------------|
| 20ft | 33 m³ |
| 40ft | 67 m³ |
| 40ft HC | 76 m³ |
| 45ft HC | 86 m³ |

---

## 💡 Pro Tips

1. **Always enter dimensions** - CBM = 0 without them
2. **Use centimeters** - System default unit
3. **Check blue info box** - Real-time CBM display
4. **Bulk upload** - Automatic CBM calculation
5. **PDF invoices** - CBM always displayed
6. **Higher charge wins** - System uses weight or CBM (whichever is higher)

---

**Version:** 1.2  
**Status:** Production Ready ✅  
**Last Updated:** [Current Date]
