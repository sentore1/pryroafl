# AI Permissions Panel - User Guide

**New Feature:** Quick view of AI permissions directly in the AI panel settings!

---

## 🎯 What's New

When you click the **⚙️ Settings** button in the AI panel, you now see:

1. **Interface Settings** (AI provider, response length, auto-refresh, sound)
2. **AI Permissions** ← NEW! Shows what the AI can and cannot do
3. **Save button** for UI settings

---

## 📊 Permissions Panel Layout

### What You'll See:

```
┌─────────────────────────────────────────────────┐
│ ⚙️ Interface Settings                          │
├─────────────────────────────────────────────────┤
│ [AI Provider: Groq ▼]  [Response: Normal ▼]   │
│ [Auto-Refresh: Off ▼]  [Sound: Enabled ▼]     │
├─────────────────────────────────────────────────┤
│ 🔒 AI Permissions (What AI Can Do)             │
├─────────────────────────────────────────────────┤
│                                                 │
│ ⚡ Autopilot Mode: [ENABLED/DISABLED]          │
│    Threshold: 5 items                          │
│                                                 │
│ ⚡ Core Actions                    3/6 enabled │
│  ✅ Assign Drivers    ✅ Confirm Payments      │
│  ✅ Update Status     ❌ Create Shipments      │
│  ❌ Edit Shipments    ❌ Cancel Shipments      │
│                                                 │
│ 📧 Communication                   0/3 enabled │
│  ❌ Send SMS          ❌ Send Email            │
│  ❌ Send WhatsApp                              │
│                                                 │
│ 👤 Customer Management             0/2 enabled │
│  ❌ Create Customers  ❌ Edit Customers        │
│                                                 │
│ 💰 Financial                       0/2 enabled │
│  ❌ Process Refunds   ❌ Apply Discounts       │
│                                                 │
│ 📄 Reporting                       2/2 enabled │
│  ✅ Generate Reports  ✅ Export Data           │
│                                                 │
│ 🚀 Advanced                        1/2 enabled │
│  ✅ Predict Analytics ❌ Optimize Routes       │
│                                                 │
│ ⚠️ Note: To change permissions, go to          │
│    Tools → AI Settings                         │
│                                                 │
└─────────────────────────────────────────────────┘
```

---

## 🎨 Visual Indicators

### Autopilot Status
- **Green** = Enabled (AI can take automatic actions)
- **Gray** = Disabled (All actions require confirmation)

### Permission Status
- **✅ Green checkmark** = Permission enabled
- **❌ Red X** = Permission disabled

### Category Summary
- **3/6 enabled** = 3 out of 6 permissions in this category are enabled
- **Green number** = At least one permission enabled
- **Red number** = No permissions enabled

### Category Colors
- **Blue** (⚡) = Core Actions
- **Teal** (📧) = Communication
- **Purple** (👤) = Customer Management
- **Green** (💰) = Financial
- **Orange** (📄) = Reporting
- **Pink** (🚀) = Advanced Features

---

## 📋 Permission Categories Explained

### ⚡ Core Actions
**Basic operations AI can perform:**
- **Assign Drivers** - Assign shipments to drivers
- **Confirm Payments** - Mark invoices as paid
- **Update Status** - Change shipment status (In Transit, Delivered, etc.)
- **Create Shipments** - Create new shipment orders
- **Edit Shipments** - Modify existing shipments
- **Cancel Shipments** - Cancel orders

### 📧 Communication
**Messaging capabilities:**
- **Send SMS** - Text notifications to customers
- **Send Email** - Email notifications
- **Send WhatsApp** - WhatsApp Business messages

### 👤 Customer Management
**Customer operations:**
- **Create Customers** - Add new customer accounts
- **Edit Customers** - Modify customer information

### 💰 Financial
**Money-related operations:**
- **Process Refunds** - Issue refunds to customers
- **Apply Discounts** - Apply discounts to orders

### 📄 Reporting
**Data export and reporting:**
- **Generate Reports** - Create PDF/Excel reports
- **Export Data** - Export data to CSV/Excel

### 🚀 Advanced Features
**AI-powered features:**
- **Predict Analytics** - Forecasting and trend analysis
- **Optimize Routes** - Route optimization for drivers

---

## 🔧 How to Use

### View Current Permissions
1. Open AI panel
2. Click **⚙️ Settings** button
3. Scroll down to "AI Permissions" section
4. View all enabled/disabled permissions

### Change Permissions
**You CANNOT change permissions from this panel.**

This panel is **read-only** - it only shows current settings.

**To change permissions:**
1. Go to **Tools → AI Settings** (or `tools.php?list=config_ai`)
2. Enable/disable checkboxes
3. Click **Save**
4. Return to AI panel
5. Open Settings to verify changes

### Why Read-Only?
- **Security**: Prevents accidental permission changes
- **Centralized**: All permission management in one place (AI Settings page)
- **Transparency**: Users can quickly see what AI can do without risk of changing it

---

## 💡 Common Scenarios

### Scenario 1: AI Action Button Fails
**User:** "Why did 'Mark In Transit' button fail?"

**Solution:**
1. Open AI panel
2. Click ⚙️ Settings
3. Check "Core Actions" → "Update Status"
4. If ❌ (disabled):
   - Go to Tools → AI Settings
   - Enable "AI can update shipment status"
   - Save and retry

### Scenario 2: AI Says "I don't have permission"
**AI Response:** "I don't have permission to send SMS. You need to enable this in AI Settings."

**Solution:**
1. Open AI panel settings
2. Check "Communication" → "Send SMS" is ❌
3. Go to Tools → AI Settings
4. Enable "AI can send SMS"
5. Save and retry

### Scenario 3: Want to Enable More Features
**User:** "I want AI to create shipments for me"

**Current Status:**
- Open settings → See "Create Shipments" is ❌

**Solution:**
1. Go to Tools → AI Settings
2. Enable "AI can create shipments"
3. Save
4. Return to AI panel
5. Refresh settings panel to verify ✅

---

## 🎯 Quick Reference

### Default Permissions (Typical Setup)

**Usually Enabled (✅):**
- Assign Drivers
- Confirm Payments
- Update Status
- Generate Reports
- Export Data
- Predict Analytics

**Usually Disabled (❌):**
- Create Shipments
- Edit Shipments
- Cancel Shipments
- Send SMS/Email/WhatsApp
- Process Refunds
- Apply Discounts
- Create/Edit Customers
- Optimize Routes

**Why?**
- **Enabled** = Low risk, reversible actions
- **Disabled** = High risk, financial impact, or external integrations

---

## 🔒 Security Notes

### Permission Levels

**Low Risk (Safe to Enable):**
- View data (read permissions)
- Generate reports
- Predictive analytics

**Medium Risk (Use with Caution):**
- Assign drivers
- Update shipment status
- Confirm payments (with monitoring)

**High Risk (Require Approval):**
- Create/cancel shipments
- Process refunds
- Apply discounts
- Financial operations

**Critical (Usually Disabled):**
- Communication (SMS/Email costs money)
- Customer account creation
- Bulk operations

---

## 🎨 Visual Design

### Color Coding System

**Permission Status:**
```
✅ Enabled   = Green background (#e7f3ff) + green checkmark
❌ Disabled  = Gray background (#f8f9fa) + red X
```

**Autopilot:**
```
Enabled  = Green (#d4edda) + ⚡ bolt icon
Disabled = Gray (#f8f9fa) + 🛑 stop icon
```

**Category Headers:**
```
Each category has a unique color for quick identification
```

---

## 📱 Mobile View

**Desktop:**
- 2 columns of permission checkboxes
- Full category names
- All sections visible

**Mobile:**
- 1 column (stacked)
- Abbreviated labels
- Scrollable panel

---

## 🔄 Auto-Refresh

The permissions panel:
- **Loads when you open settings** ⚙️
- **Does NOT auto-refresh** (manual refresh only)
- **Shows loading spinner** while fetching data
- **Displays errors** if loading fails

**To refresh permissions:**
1. Close settings panel
2. Reopen settings panel
3. Permissions re-load automatically

---

## 🐛 Troubleshooting

### Problem: "Loading permissions..." never completes

**Possible Causes:**
- Server error
- Database connection issue
- `get_permissions_ajax.php` file missing

**Solution:**
1. Check browser console (F12 → Console)
2. Look for red errors
3. Verify `ajax/ai/get_permissions_ajax.php` exists
4. Check Apache/XAMPP is running
5. Test URL directly: `ajax/ai/get_permissions_ajax.php`

### Problem: Shows all permissions as disabled

**Possible Causes:**
- Default settings (fresh install)
- Database settings not configured

**Solution:**
1. Go to Tools → AI Settings
2. Enable desired permissions
3. Save
4. Reopen AI panel settings to verify

### Problem: "Error loading permissions" message

**Solution:**
1. Check if logged in as admin
2. Verify `ai_permissions_helper.php` exists
3. Check database connection
4. Review server error logs

---

## ✅ Summary

**What This Feature Does:**
- ✅ Shows all AI permissions in one place
- ✅ Visual indicators (✅/❌) for quick scanning
- ✅ Organized by category
- ✅ Shows autopilot status
- ✅ Read-only view (safe to check anytime)

**What This Feature Does NOT Do:**
- ❌ Does not change permissions (use AI Settings for that)
- ❌ Does not auto-refresh (manual refresh by closing/opening)
- ❌ Does not show permission history

**Benefits:**
1. **Transparency** - See what AI can do without leaving chat
2. **Quick Troubleshooting** - Diagnose why actions fail
3. **Security** - Know exactly what AI is allowed to do
4. **No Guessing** - Clear visual indicators

---

**Created:** June 18, 2026  
**Version:** 2.1  
**Feature:** AI Permissions Panel in Settings

