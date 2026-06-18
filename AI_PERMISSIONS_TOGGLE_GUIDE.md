# AI Permissions - Interactive Toggle Guide

**NEW FEATURE:** Enable/disable AI permissions directly from the AI panel!

---

## 🎯 What's New

You can now **toggle AI permissions ON/OFF** directly from the AI panel settings without going to the main Settings page!

### Before (v2.0):
1. Open AI panel
2. See permissions (read-only)
3. Close AI panel
4. Go to Tools → AI Settings
5. Change permissions
6. Save
7. Go back to AI panel

### After (v2.1):
1. Open AI panel
2. Click ⚙️ Settings
3. **Toggle switch ON/OFF** ✅
4. Done! (Auto-saved)

---

## 🎮 How to Use

### Step-by-Step:

1. **Open AI Panel**
   - Click the "AI" button in top navigation

2. **Open Settings**
   - Click the **⚙️ (Settings)** icon in panel header

3. **Scroll to AI Permissions**
   - See "AI Permissions (What AI Can Do)" section

4. **Toggle Switches**
   - **Autopilot Mode**: Large toggle at top
   - **Individual Permissions**: Small toggles next to each permission

5. **Changes Save Automatically**
   - No "Save" button needed
   - Green success message appears in chat
   - Permissions reload automatically

---

## 🎨 Visual Guide

### Toggle Switch Appearance:

**OFF (Disabled):**
```
Permission Name                    ⚪─────
                                   Gray switch (left)
```

**ON (Enabled):**
```
Permission Name                    ─────🟢
                                   Green switch (right)
```

### Complete Panel Layout:

```
┌──────────────────────────────────────────────┐
│ ⚙️ Interface Settings                       │
├──────────────────────────────────────────────┤
│ [Provider] [Length] [Auto-Refresh] [Sound]  │
├──────────────────────────────────────────────┤
│ 🔒 AI Permissions (What AI Can Do)          │
├──────────────────────────────────────────────┤
│                                              │
│ ⚡ Autopilot Mode              [🟢 ON]      │
│    Threshold: [5] items                      │
│                                              │
│ ⚡ Core Actions                  3/6 enabled│
│ ┌────────────────────────────────────────┐  │
│ │ Assign Drivers            [🟢 ON]     │  │
│ │ Confirm Payments          [🟢 ON]     │  │
│ │ Update Status             [🟢 ON]     │  │
│ │ Create Shipments          [⚪ OFF]    │  │
│ │ Edit Shipments            [⚪ OFF]    │  │
│ │ Cancel Shipments          [⚪ OFF]    │  │
│ └────────────────────────────────────────┘  │
│                                              │
│ 📧 Communication                 0/3 enabled│
│ ┌────────────────────────────────────────┐  │
│ │ Send SMS                  [⚪ OFF]    │  │
│ │ Send Email                [⚪ OFF]    │  │
│ │ Send WhatsApp             [⚪ OFF]    │  │
│ └────────────────────────────────────────┘  │
│                                              │
│ 💡 Tip: Toggle switches to enable/disable   │
│    permissions instantly. Changes saved     │
│    automatically.                            │
│                                              │
│                   [Save UI Settings]         │
└──────────────────────────────────────────────┘
```

---

## ⚡ Interactive Features

### 1. **Autopilot Toggle**
**Location:** Top of permissions section

**What it does:**
- Enables/disables autopilot mode
- Shows/hides threshold input when enabled

**Threshold Input:**
- Only visible when Autopilot is ON
- Number input field
- Range: 1-50 items
- Changes save on blur (when you click away)

**Example:**
```
⚡ Autopilot Mode                    [🟢 ON]
   Threshold: [5 ▼] items
            ↑
    Click to change value
```

### 2. **Category Toggles**
**6 Categories:**
- Core Actions
- Communication
- Customer Management
- Financial
- Reporting
- Advanced

**Each permission has:**
- Permission name (left)
- Toggle switch (right)
- Colored background when enabled

**Visual Feedback:**
```
BEFORE TOGGLE:
Assign Drivers              [⚪ OFF]
(Gray background)

AFTER TOGGLE:
Assign Drivers              [🟢 ON]
(Blue background with border)
```

### 3. **Success Notifications**
**Where:** AI chat area

**What you see:**
```
✓ Assign Drivers enabled successfully
```

**Style:**
- Green badge
- Appears in chat
- Auto-scrolls into view
- Stays until chat is cleared

### 4. **Auto-Reload**
After toggling:
1. Request sent to server
2. Success notification appears
3. Permissions panel reloads (0.5s delay)
4. Toggle reflects new state

---

## 🔧 Technical Details

### How It Works:

1. **User clicks toggle**
   ```javascript
   onchange="cdp_togglePermission('actions_assign_drivers', true)"
   ```

2. **AJAX request sent**
   ```
   POST ajax/ai/update_permission_ajax.php
   Data: permission=actions_assign_drivers&enabled=1
   ```

3. **Database updated**
   ```sql
   UPDATE cdb_settings 
   SET ai_can_assign_drivers = 1 
   LIMIT 1
   ```

4. **Response received**
   ```json
   {"success":true,"message":"Assign Drivers enabled successfully"}
   ```

5. **UI updates**
   - Success message in chat
   - Permissions reload
   - Toggle stays in new position

### Database Columns:

All permissions stored in `cdb_settings` table:

| Permission | Column Name | Type | Default |
|------------|-------------|------|---------|
| Autopilot Enabled | `ai_autopilot_enabled` | TINYINT(1) | 0 |
| Autopilot Threshold | `ai_autopilot_threshold` | INT(11) | 5 |
| Assign Drivers | `ai_can_assign_drivers` | TINYINT(1) | 1 |
| Confirm Payments | `ai_can_confirm_payments` | TINYINT(1) | 1 |
| Update Status | `ai_can_update_status` | TINYINT(1) | 1 |
| Send SMS | `ai_can_send_sms` | TINYINT(1) | 0 |
| Send Email | `ai_can_send_email` | TINYINT(1) | 0 |
| (etc.) | (etc.) | (etc.) | (etc.) |

### Auto-Create Columns:
If a column doesn't exist, the endpoint automatically:
1. Creates the column with ALTER TABLE
2. Sets the new value
3. Returns success

**This means:** No manual database setup required!

---

## 💡 Usage Examples

### Example 1: Enable AI to Send SMS

**Before:**
- AI says: "I don't have permission to send SMS"
- You're frustrated

**Now:**
1. Open AI panel
2. Click ⚙️ Settings
3. Find "Communication" → "Send SMS"
4. Toggle **OFF → ON** 🟢
5. See: "✓ Send SMS enabled successfully"
6. Ask AI to send SMS again
7. It works! 🎉

**Time saved:** 30 seconds (vs going to Settings page)

### Example 2: Enable Multiple Permissions

**Goal:** Enable all communication features

**Steps:**
1. Open Settings panel
2. Find "Communication" category
3. Toggle ON:
   - Send SMS 🟢
   - Send Email 🟢
   - Send WhatsApp 🟢
4. See 3 success messages
5. Category shows "3/3 enabled"

**Time:** 10 seconds

### Example 3: Disable Risky Permission

**Scenario:** AI is canceling shipments by mistake

**Quick Fix:**
1. Open Settings
2. Find "Core Actions" → "Cancel Shipments"
3. Toggle **ON → OFF** ⚪
4. See: "✓ Cancel Shipments disabled successfully"
5. AI can no longer cancel shipments

**Crisis averted!** ✅

### Example 4: Adjust Autopilot Threshold

**Current:** Autopilot triggers at 5 items  
**Want:** More conservative (10 items)

**Steps:**
1. Open Settings
2. Autopilot section
3. Change threshold: `[5]` → `[10]`
4. Click outside input field
5. See: "✓ Autopilot threshold updated to 10 items"

---

## 🎯 Quick Actions

### Enable All Core Actions:
1. Settings → Core Actions
2. Toggle all 6 switches ON
3. Wait for confirmations
4. Done!

### Disable All Communication:
1. Settings → Communication
2. Toggle all 3 switches OFF
3. AI cannot send any messages

### Enable Safe Defaults:
**Recommended for most users:**
- ✅ Assign Drivers
- ✅ Confirm Payments
- ✅ Update Status
- ✅ Generate Reports
- ✅ Export Data
- ⚪ Everything else OFF

---

## 🔒 Security Notes

### Safe to Enable:
- Read permissions (no changes made)
- Generate reports
- Export data
- Predict analytics
- Assign drivers (reversible)
- Update status (reversible)

### Use with Caution:
- Confirm payments (check accuracy first)
- Send SMS/Email (costs money)
- Create/Edit shipments
- Cancel shipments

### Usually Keep Disabled:
- Process refunds (financial risk)
- Apply discounts (financial risk)
- Create customers (data integrity)
- Bulk operations

---

## 🐛 Troubleshooting

### Problem: Toggle doesn't switch

**Possible Causes:**
- Not logged in as admin
- Database connection issue
- Column doesn't exist

**Solution:**
1. Check browser console (F12 → Console)
2. Look for error messages
3. Try logging out and back in
4. Check XAMPP MySQL is running

### Problem: Success message but permission still disabled

**Cause:** Cache issue or database not updating

**Solution:**
1. Close settings panel
2. Reopen settings panel
3. Verify toggle position
4. If still wrong, go to Tools → AI Settings
5. Check if setting is saved there

### Problem: "Error updating permission"

**Solution:**
1. Check console for details
2. Verify `update_permission_ajax.php` exists
3. Check PHP error logs
4. Try a different permission

---

## 📱 Mobile Experience

**Desktop:**
- All toggles visible
- Full labels
- 2-column grid

**Mobile:**
- Stacked 1-column layout
- Larger touch targets
- Scrollable
- Same functionality

---

## ✅ Benefits

### 1. **Speed**
- **Before:** 7 clicks + page load
- **After:** 1 click
- **Saved:** ~30 seconds per permission

### 2. **Convenience**
- No page navigation
- No form submission
- No page reload
- Instant feedback

### 3. **Context**
- Stay in AI panel
- See AI response immediately
- Test permission instantly

### 4. **Visual**
- Clear ON/OFF state
- Color-coded categories
- Success confirmations
- Real-time updates

---

## 🎓 Best Practices

### DO:
- ✅ Toggle one permission at a time
- ✅ Wait for success message
- ✅ Test AI after enabling new permission
- ✅ Disable unused permissions

### DON'T:
- ❌ Toggle rapidly (wait for save)
- ❌ Close panel immediately after toggle
- ❌ Enable all without understanding
- ❌ Ignore error messages

---

## 📊 Summary

**What Changed:**
- ✅ Permissions now have toggle switches
- ✅ Changes save automatically
- ✅ Success notifications in chat
- ✅ Auto-reload after change
- ✅ Autopilot threshold editable
- ✅ No need to visit Settings page

**User Experience:**
- **Faster:** 30 seconds → 2 seconds
- **Easier:** 7 clicks → 1 click
- **Better:** Stay in context, instant feedback

**Technical:**
- AJAX endpoint: `update_permission_ajax.php`
- Auto-creates missing database columns
- Transaction-safe updates
- Error handling and recovery

---

**Version:** 2.1  
**Feature:** Interactive Permission Toggles  
**Status:** ✅ Complete & Production Ready  
**Created:** June 18, 2026

