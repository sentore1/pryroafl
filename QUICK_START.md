# Quick Start Guide - CBM Input Field Feature

## 🚀 Get Started in 5 Minutes

### Step 1: Run Migration (1 minute)
Visit: `http://yourdomain.com/test_cbm_input_field_migration.php`

Click any setup button:
- **[Dimensions Only]** - For parcel services (default)
- **[CBM Only]** - For freight services
- **[Both Methods]** - For mixed services

✅ Done! The database is updated automatically.

---

### Step 2: Test It (2 minutes)
1. Go to: **Courier → Add New Shipment**
2. Add a package
3. You'll see your chosen input method:
   - **Dimensions Only**: Length, Width, Height fields
   - **CBM Only**: CBM (m³) field with green border
   - **Both**: All fields visible

---

### Step 3: Create Test Shipment (2 minutes)

#### If Using Dimensions:
```
Length: 50
Width:  40
Height: 30
```
Result: System calculates 0.06 m³

#### If Using CBM:
```
CBM: 1
```
Result: System uses 1 m³ directly

---

## 🎯 Which Mode Should I Choose?

### Choose "Dimensions Only" if:
- ✅ You ship parcels/packages
- ✅ You need precise measurements
- ✅ You charge by weight or volumetric weight
- ✅ Your staff measures each package

**Example:** Express courier, e-commerce fulfillment

---

### Choose "CBM Only" if:
- ✅ You ship freight/cargo
- ✅ You quote in cubic meters
- ✅ You use standard pallet sizes
- ✅ You consolidate shipments

**Example:** Freight forwarder, consolidation service

---

### Choose "Both Methods" if:
- ✅ You handle both parcels and freight
- ✅ You want maximum flexibility
- ✅ Different staff prefer different methods
- ✅ You're not sure yet

**Example:** Mixed logistics service

---

## 📊 Common Scenarios

### Scenario 1: Small Parcel Service
**Setup:**
```
Dimensions Only + Show Total CBM
```

**User Experience:**
```
User enters: 30cm × 20cm × 15cm
System shows: 0.009 m³
```

---

### Scenario 2: Freight Consolidation
**Setup:**
```
CBM Only + Show Total CBM
```

**User Experience:**
```
User enters: 1 CBM
System shows: 1.0000 m³
Total for 5 packages: 5.0000 m³
```

---

### Scenario 3: Mixed Service
**Setup:**
```
Both Methods + Show Total CBM
```

**User Experience:**
```
Small package: Enter dimensions
Large freight: Enter CBM
System handles both correctly
```

---

## 🔧 Change Settings Anytime

### Via Configuration Page:
1. Go to: **Tools → CBM Configuration**
2. Toggle the switches:
   - Package Dimensions Input
   - CBM Direct Input
   - CBM Calculation Display
3. Click **Save Settings**

### Via Migration Page:
1. Visit: `test_cbm_input_field_migration.php`
2. Click a different setup button
3. Refresh and test

---

## ✅ Verification Checklist

After setup, verify:

- [ ] Can access CBM Configuration page
- [ ] Settings save successfully
- [ ] Correct fields show in shipment form
- [ ] Validation works (try submitting empty)
- [ ] Calculations are correct
- [ ] Total CBM displays properly

---

## 🐛 Troubleshooting

### Problem: "Column doesn't exist" error
**Solution:** Run the migration page, it will create it automatically

### Problem: Fields not showing/hiding
**Solution:** Clear browser cache (Ctrl+F5)

### Problem: Can't save settings
**Solution:** Check that at least one input method is enabled

### Problem: Calculations seem wrong
**Solution:** Check your measurement unit setting (cm/inch/m)

---

## 📱 Need Help?

1. **Read the docs:**
   - `CBM_INPUT_FEATURE_README.md` - Complete documentation
   - `VISUAL_GUIDE.md` - See what it looks like
   - `IMPLEMENTATION_SUMMARY.md` - Technical details

2. **Run diagnostics:**
   - Visit `test_cbm_input_field_migration.php`
   - Check all 5 steps are green ✓

3. **Check browser console:**
   - Press F12
   - Look for JavaScript errors
   - Report any errors found

---

## 💡 Pro Tips

### Tip 1: Start Simple
Begin with "Dimensions Only" (default), then switch to CBM if needed.

### Tip 2: Train Your Team
Show staff which fields to use based on your chosen mode.

### Tip 3: Use Presets
For freight, common CBM values:
- Small pallet: 0.5 m³
- Standard pallet: 1 m³
- Large pallet: 2 m³

### Tip 4: Monitor Total CBM
Use the total CBM display to:
- Verify container capacity
- Calculate freight charges
- Plan consolidation

### Tip 5: Test Before Going Live
Create test shipments with both methods to ensure calculations are correct.

---

## 🎓 Understanding CBM

### What is CBM?
CBM = Cubic Meter = m³
It's the volume of your shipment.

### How is it calculated?
```
CBM = (Length × Width × Height) ÷ 1,000,000

Example:
50cm × 40cm × 30cm = 60,000 cm³
60,000 ÷ 1,000,000 = 0.06 m³
```

### Why does it matter?
- Freight charges often based on CBM
- Container capacity measured in CBM
- Helps compare weight vs volume

### Standard Container Capacities:
- 20ft container: ~33 m³
- 40ft container: ~67 m³
- 40ft HC: ~76 m³

---

## 📈 Next Steps

### After Basic Setup:
1. ✅ Configure your preferred input method
2. ✅ Train your team
3. ✅ Create test shipments
4. ✅ Verify calculations

### Future Enhancements:
- Apply to consolidate forms
- Apply to customer package forms
- Add CBM presets
- Bulk import with CBM

---

## 🎉 You're Ready!

Your system now supports flexible CBM input. Choose the method that works best for your business and start creating shipments!

**Questions?** Check the full documentation in `CBM_INPUT_FEATURE_README.md`

---

**Last Updated:** 2024
**Version:** 1.0
**Status:** Ready to Use ✅
