# CBM Input Field Feature - Visual Guide

## Configuration Page

### Location
**Tools → CBM Configuration**

### New Layout (3 Input Options)

```
┌─────────────────────────────────────────────────────────────────┐
│  Package Dimensions Input                                       │
│  ☑ Show Length, Width, Height Fields                           │
│  Enable to show L × W × H input fields in shipment forms       │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  CBM Direct Input                                               │
│  ☐ Show CBM Input Field                                        │
│  Enable to allow direct CBM entry (e.g., 1 m³, 0.4 m³)        │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  CBM Calculation Display                                        │
│  ☑ Show Total CBM Calculation                                  │
│  Enable to show real-time total CBM in shipment forms          │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  ℹ Input Options:                                               │
│  • Dimensions Only: Users enter L × W × H, system calculates   │
│    CBM automatically                                            │
│  • CBM Only: Users enter CBM directly (e.g., 1 m³ for         │
│    standard pallet)                                             │
│  • Both Enabled: Users can choose which method to use          │
│  • Note: At least one input method must be enabled             │
└─────────────────────────────────────────────────────────────────┘
```

## Shipment Form Views

### Mode 1: Dimensions Only (Default)

```
Package Information
┌──────┬─────────────┬────────┬────────┬───────┬────────┬─────────┐
│ Qty  │ Description │ Weight │ Length │ Width │ Height │ Vol.Wt  │
├──────┼─────────────┼────────┼────────┼───────┼────────┼─────────┤
│  1   │ Box         │  10kg  │  50cm  │ 40cm  │  30cm  │  6.00   │
└──────┴─────────────┴────────┴────────┴───────┴────────┴─────────┘

Total CBM: 0.0600 m³
```

**What User Sees:**
- ✅ Length field (required)
- ✅ Width field (required)
- ✅ Height field (required)
- ✅ Volumetric Weight (calculated)
- ❌ CBM input field (hidden)
- ✅ Total CBM display

**Calculation:**
```
CBM = (50 × 40 × 30) ÷ 1,000,000 = 0.06 m³
```

---

### Mode 2: CBM Only

```
Package Information
┌──────┬─────────────┬────────┬──────────┬─────────┬──────────┐
│ Qty  │ Description │ Weight │ CBM (m³) │ Fixed   │ Declared │
├──────┼─────────────┼────────┼──────────┼─────────┼──────────┤
│  1   │ Pallet      │ 100kg  │   1.0    │  $0.00  │  $100.00 │
└──────┴─────────────┴────────┴──────────┴─────────┴──────────┘

Total CBM: 1.0000 m³
```

**What User Sees:**
- ❌ Length field (hidden)
- ❌ Width field (hidden)
- ❌ Height field (hidden)
- ❌ Volumetric Weight (hidden)
- ✅ CBM input field (required) - Green border
- ✅ Total CBM display

**User Input:**
```
Simply enter: 1 (for 1 cubic meter)
Or: 0.5 (for half cubic meter)
Or: 2.5 (for 2.5 cubic meters)
```

---

### Mode 3: Both Methods Enabled

```
Package Information
┌──────┬─────────────┬────────┬────────┬───────┬────────┬─────────┬──────────┐
│ Qty  │ Description │ Weight │ Length │ Width │ Height │ Vol.Wt  │ CBM (m³) │
├──────┼─────────────┼────────┼────────┼───────┼────────┼─────────┼──────────┤
│  1   │ Box         │  10kg  │  50cm  │ 40cm  │  30cm  │  6.00   │   0.06   │
└──────┴─────────────┴────────┴────────┴───────┴────────┴─────────┴──────────┘

Total CBM: 0.0600 m³
```

**What User Sees:**
- ✅ Length field (optional)
- ✅ Width field (optional)
- ✅ Height field (optional)
- ✅ Volumetric Weight (calculated if dimensions provided)
- ✅ CBM input field (optional) - Green border
- ✅ Total CBM display

**User Can:**
1. Enter dimensions only → CBM calculated
2. Enter CBM only → Used directly
3. Enter both → CBM input takes priority

---

## Field Styling

### Dimension Fields (Standard)
```
┌─────────────────┐
│ Length          │
│ ┌─────────────┐ │
│ │    50       │ │  ← Standard border
│ └─────────────┘ │
└─────────────────┘
```

### CBM Input Field (Highlighted)
```
┌─────────────────┐
│ CBM (m³)        │
│ ┌─────────────┐ │
│ │    1.0      │ │  ← Green border (#36bea6)
│ └─────────────┘ │
└─────────────────┘
```

### Weight Field (Required - Red Border)
```
┌─────────────────┐
│ Weight          │
│ ┌─────────────┐ │
│ │    10       │ │  ← Red border (required)
│ └─────────────┘ │
└─────────────────┘
```

---

## Validation Messages

### When Dimensions Are Required
```
┌────────────────────────────────────┐
│  ⚠ Error!                          │
│                                    │
│  Please enter Length value         │
│                                    │
│  [ OK ]                            │
└────────────────────────────────────┘
```

### When CBM Is Required
```
┌────────────────────────────────────┐
│  ⚠ Error!                          │
│                                    │
│  Please enter CBM value            │
│                                    │
│  [ OK ]                            │
└────────────────────────────────────┘
```

### When No Input Method Enabled (Config)
```
┌────────────────────────────────────┐
│  ⚠ Validation Error                │
│                                    │
│  At least one input method         │
│  (Dimensions or CBM) must be       │
│  enabled                           │
│                                    │
│  [ OK ]                            │
└────────────────────────────────────┘
```

---

## Total CBM Display

### Always Visible (when show_cbm_in_forms = 1)
```
┌─────────────────────────────────────┐
│  Total CBM (m³)                     │
│  ┌───────────────────────────────┐  │
│  │  📦 0.0600 m³                 │  │
│  └───────────────────────────────┘  │
│  Cubic Meter Volume                 │
└─────────────────────────────────────┘
```

### Multiple Packages Example
```
Package 1: 0.06 m³
Package 2: 0.04 m³
Package 3: 0.10 m³
─────────────────
Total:     0.20 m³
```

---

## Migration Test Page

### URL
`http://yourdomain.com/test_cbm_input_field_migration.php`

### Display
```
╔═══════════════════════════════════════════════════════════╗
║  CBM Input Field Migration                                ║
╠═══════════════════════════════════════════════════════════╣
║                                                           ║
║  Step 1: Check Database Column                           ║
║  ✓ Column 'show_cbm_input_field' already exists!        ║
║                                                           ║
║  ─────────────────────────────────────────────────────   ║
║                                                           ║
║  Step 2: Current Settings                                ║
║  ┌─────────────────────────────────────────────────┐    ║
║  │ Setting                  │ Value │ Description  │    ║
║  ├─────────────────────────────────────────────────┤    ║
║  │ show_package_dimensions  │   1   │ Show L,W,H   │    ║
║  │ show_cbm_input_field     │   0   │ Show CBM     │    ║
║  │ show_cbm_in_forms        │   1   │ Show Total   │    ║
║  └─────────────────────────────────────────────────┘    ║
║                                                           ║
║  ─────────────────────────────────────────────────────   ║
║                                                           ║
║  Step 3: Test Core Class                                 ║
║  ✓ Core class has 'show_cbm_input_field' property       ║
║  Value: 0                                                 ║
║                                                           ║
║  ─────────────────────────────────────────────────────   ║
║                                                           ║
║  Step 4: Configuration Recommendations                   ║
║  ℹ Input Method Options:                                 ║
║  • Option 1 - Dimensions Only (Default)                  ║
║  • Option 2 - CBM Only                                   ║
║  • Option 3 - Both Methods                               ║
║                                                           ║
║  ─────────────────────────────────────────────────────   ║
║                                                           ║
║  Step 5: Quick Setup                                     ║
║  [ Dimensions Only (Default) ] [ CBM Only ] [ Both ]     ║
║                                                           ║
╚═══════════════════════════════════════════════════════════╝
```

---

## User Workflow Examples

### Example 1: Parcel Service User
**Configuration:** Dimensions Only

1. User creates new shipment
2. Sees: Qty, Description, Weight, Length, Width, Height
3. Enters: 50cm × 40cm × 30cm
4. System calculates: 0.06 m³
5. Sees total: "Total CBM: 0.0600 m³"

---

### Example 2: Freight Service User
**Configuration:** CBM Only

1. User creates new shipment
2. Sees: Qty, Description, Weight, CBM (m³)
3. Enters: 1 (for 1 cubic meter)
4. System uses: 1 m³ directly
5. Sees total: "Total CBM: 1.0000 m³"

---

### Example 3: Mixed Service User
**Configuration:** Both Methods

**Scenario A - Small Parcel:**
1. Enters dimensions: 30cm × 20cm × 15cm
2. Leaves CBM empty
3. System calculates: 0.009 m³

**Scenario B - Standard Pallet:**
1. Leaves dimensions empty
2. Enters CBM: 1
3. System uses: 1 m³

**Scenario C - Custom:**
1. Enters both
2. System prioritizes CBM input

---

## Mobile View Considerations

### Dimensions Only (Stacked)
```
┌─────────────────┐
│ Quantity        │
│ [    1    ]     │
└─────────────────┘
┌─────────────────┐
│ Description     │
│ [ Box       ]   │
└─────────────────┘
┌─────────────────┐
│ Weight          │
│ [   10kg   ]    │
└─────────────────┘
┌─────────────────┐
│ Length          │
│ [   50cm   ]    │
└─────────────────┘
┌─────────────────┐
│ Width           │
│ [   40cm   ]    │
└─────────────────┘
┌─────────────────┐
│ Height          │
│ [   30cm   ]    │
└─────────────────┘
```

### CBM Only (Stacked)
```
┌─────────────────┐
│ Quantity        │
│ [    1    ]     │
└─────────────────┘
┌─────────────────┐
│ Description     │
│ [ Pallet    ]   │
└─────────────────┘
┌─────────────────┐
│ Weight          │
│ [  100kg   ]    │
└─────────────────┘
┌─────────────────┐
│ CBM (m³)        │
│ [   1.0    ]    │ ← Green border
└─────────────────┘
```

---

## Color Legend

- 🔴 **Red Border** = Required field (Weight)
- 🟢 **Green Border** = CBM input field (when visible)
- ⚪ **Standard Border** = Optional/calculated fields
- 📦 **Icon** = CBM display indicator

---

**Note:** All screenshots are conceptual representations. Actual appearance may vary based on your theme and customizations.
