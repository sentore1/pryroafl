# WhatsApp Settings Control System - Complete Guide

## 🎯 Overview

This system allows **administrators to control** which WhatsApp methods are available in the system:
- **API Only** (Twilio/Meta/UltraMsg) - Automatic background sending
- **Direct Link Only** (wa.me) - FREE manual sending
- **Both** - Users can choose which method to use

## ✅ Problem Solved

**Your Question:** "Can we control this in settings to avoid conflict? Can users switch between methods?"

**Answer:** YES! ✅ The system now has:
1. ✅ Admin settings to choose methods
2. ✅ No conflicts between API and Direct Link
3. ✅ User can switch between methods
4. ✅ Full control over which buttons appear
5. ✅ Default action configuration

---

## 🗂️ Files Created/Modified

### New Files:
1. `sql/whatsapp_method_settings_migration.sql` - Database migration
2. `helpers/whatsapp_helper.php` - Helper functions
3. `WHATSAPP_SETTINGS_CONTROL_GUIDE.md` - This guide

### Modified Files:
1. `views/tools/config_whatsapp.php` - Added settings UI
2. `dataJs/whatssap_config.js` - Added method control logic
3. `ajax/tools/api_whatsapp_config_ajax.php` - Save new settings

---

## 📊 Configuration Options

### 1. WhatsApp Method (Main Setting)

| Option | Description | Use Case |
|--------|-------------|----------|
| **API Only** | Only use Twilio/Meta/UltraMsg | Fully automated system |
| **Direct Link Only** | Only use wa.me links | Free, manual system |
| **Both** ⭐ | Use both methods | **RECOMMENDED** - Best flexibility |

### 2. Default Action (When "Both" is selected)

| Option | Description | When Used |
|--------|-------------|-----------|
| **Use API** | Checkboxes trigger API | User checks "notify WhatsApp" on forms |
| **Use Direct Link** | No auto-send, manual only | User must click WhatsApp button |
| **None** | No automatic send | All notifications are manual |

### 3. UI Button Controls

| Setting | Controls | Effect |
|---------|----------|--------|
| **Show Direct Link Buttons** | wa.me buttons | Shows/hides WhatsApp icons in dropdowns |
| **Show API Notification Checkboxes** | Notification checkboxes | Shows/hides checkboxes in forms |

---

## 🚀 Installation Steps

### Step 1: Run Database Migration

```sql
-- Open phpMyAdmin and run:
source c:\xampp\htdocs\pryroafl\sql\whatsapp_method_settings_migration.sql
```

Or manually:
```sql
ALTER TABLE `cdb_settings`
    ADD COLUMN `whatsapp_method` VARCHAR(20) NOT NULL DEFAULT 'api',
    ADD COLUMN `whatsapp_default_action` VARCHAR(20) NOT NULL DEFAULT 'api',
    ADD COLUMN `enable_direct_link_buttons` TINYINT(1) NOT NULL DEFAULT 1,
    ADD COLUMN `enable_api_buttons` TINYINT(1) NOT NULL DEFAULT 1;
```

### Step 2: Include Helper Functions

In files where you need to check WhatsApp settings, add:

```php
<?php
require_once("helpers/whatsapp_helper.php");

// Then use:
if (showDirectLinkButtons()) {
    // Show WhatsApp button
}

if (showAPINotificationCheckboxes()) {
    // Show notification checkboxes
}
?>
```

### Step 3: Configuration

1. Go to: **Tools → Configure WhatsApp**
2. Choose your **WhatsApp Method**
3. Configure **Default Action** (if using "Both")
4. Set **UI Button Controls**
5. Click **Save**

---

## 🎨 Usage Scenarios

### Scenario 1: API Only (Automated System)

**Settings:**
- Method: `API Only`
- Provider: `Twilio` (or Meta/UltraMsg)
- API Credentials: Configured

**Result:**
- ✅ Notification checkboxes show in forms
- ✅ Auto-send via API when checked
- ❌ No Direct Link buttons
- ✅ Fully automated

**Best For:** Enterprise, high-volume, automated workflows

---

### Scenario 2: Direct Link Only (Free System)

**Settings:**
- Method: `Direct Link Only`
- No API credentials needed

**Result:**
- ❌ No notification checkboxes
- ✅ WhatsApp buttons in dropdown menus
- ✅ Bulk WhatsApp button
- ✅ 100% FREE
- ⚠️ Manual send only

**Best For:** Small business, budget-conscious, manual operations

---

### Scenario 3: Both Methods ⭐ (RECOMMENDED)

**Settings:**
- Method: `Both`
- Provider: `Twilio` (configured)
- Default Action: `Use API`

**Result:**
- ✅ Notification checkboxes (trigger API)
- ✅ WhatsApp buttons (manual wa.me)
- ✅ Flexibility for users
- ✅ Best of both worlds

**Best For:** Most businesses - automation + manual control

---

## 🔧 Integration in Your Pages

### In Courier List (courier_list_ajax.php)

```php
<?php
require_once("../../helpers/whatsapp_helper.php");

$whatsappInfo = getWhatsAppMethodInfo();
?>

<!-- In the dropdown menu -->
<?php if ($whatsappInfo['show_direct_link_buttons']) { ?>
    <div class="dropdown-divider"></div>
    
    <!-- WhatsApp Sender -->
    <?php echo getWhatsAppButton($row->order_id, 'sender', $sender_data->phone); ?>
    
    <!-- WhatsApp Receiver -->
    <?php echo getWhatsAppButton($row->order_id, 'receiver', $receiver_data->phone); ?>
<?php } ?>

<!-- Bulk WhatsApp Button -->
<?php if ($whatsappInfo['show_direct_link_buttons']) { ?>
    <div class="row mb-3">
        <div class="col-md-12">
            <?php echo getBulkWhatsAppButton(); ?>
        </div>
    </div>
<?php } ?>

<!-- Include JavaScript if Direct Link enabled -->
<?php echo includeWhatsAppDirectLinkJS(); ?>
```

### In Courier Add/Edit Forms

```php
<?php
require_once("helpers/whatsapp_helper.php");
?>

<!-- Show checkboxes only if API notifications enabled -->
<?php if (showAPINotificationCheckboxes()) { ?>
    <div class="form-group">
        <label class="custom-control custom-checkbox">
            <input type="checkbox" name="notify_whatsapp_sender" value="1">
            Notify Sender via WhatsApp
        </label>
    </div>
    
    <div class="form-group">
        <label class="custom-control custom-checkbox">
            <input type="checkbox" name="notify_whatsapp_receiver" value="1">
            Notify Receiver via WhatsApp
        </label>
    </div>
<?php } ?>
```

### In Courier Add AJAX (add_courier_ajax.php)

```php
<?php
require_once("../../helpers/whatsapp_helper.php");

// Check if should send via API
$notify_whatsapp_sender = isset($_POST['notify_whatsapp_sender']) ? true : false;

if (shouldProcessAPINotification($notify_whatsapp_sender)) {
    // Send via API
    sendNotificationWhatsAppWithPDF($sender_data, $shipment_id, 3);
}
// If using Direct Link method, user will click button manually
?>
```

---

## 📖 Helper Functions Reference

### Check Methods Enabled

```php
// Check if API method is available
if (isWhatsAppAPIEnabled()) {
    // API is enabled
}

// Check if Direct Link is available
if (isWhatsAppDirectLinkEnabled()) {
    // Direct Link is enabled
}
```

### Check UI Controls

```php
// Should show Direct Link buttons?
if (showDirectLinkButtons()) {
    // Show wa.me buttons
}

// Should show API checkboxes?
if (showAPINotificationCheckboxes()) {
    // Show notification checkboxes
}
```

### Get Configuration

```php
// Get all WhatsApp settings
$info = getWhatsAppMethodInfo();
/*
Returns:
[
    'active' => true/false,
    'method' => 'api'|'direct_link'|'both',
    'default_action' => 'api'|'direct_link'|'none',
    'show_direct_link_buttons' => true/false,
    'show_api_checkboxes' => true/false,
    'api_enabled' => true/false,
    'direct_link_enabled' => true/false
]
*/
```

### Process Notifications

```php
// Check if checkbox should trigger API send
$checkboxChecked = isset($_POST['notify_whatsapp']);

if (shouldProcessAPINotification($checkboxChecked)) {
    // Send via API
    sendNotificationWhatsAppWithPDF($data, $id, $template);
}
```

---

## 🔄 How Conflicts Are Avoided

### Problem: Both methods sending at once
**Solution:** Settings control which method is active

### Problem: Confusing UI with too many buttons
**Solution:** UI controls show/hide buttons based on settings

### Problem: User doesn't know which to use
**Solution:** Admin sets default action

### Problem: Costs when user wants free option
**Solution:** Can disable API and use only Direct Link

---

## 💡 Decision Flow Chart

```
Admin goes to WhatsApp Settings
    ↓
Chooses Method:
    ├─→ API Only
    │   ├─→ Show notification checkboxes
    │   ├─→ Hide Direct Link buttons
    │   └─→ Auto-send via API
    │
    ├─→ Direct Link Only
    │   ├─→ Hide notification checkboxes
    │   ├─→ Show Direct Link buttons
    │   └─→ Manual send only
    │
    └─→ Both (Recommended)
        ├─→ Show both checkboxes and buttons
        ├─→ Set Default Action:
        │   ├─→ API: Checkboxes trigger API
        │   ├─→ Direct Link: Manual only
        │   └─→ None: All manual
        └─→ Full flexibility
```

---

## 🎯 Configuration Examples

### Example 1: Enterprise (Fully Automated)

```
Method: API Only
Provider: Twilio
Default Action: (N/A)
Show Direct Link Buttons: No
Show API Checkboxes: Yes

Result: Full automation, no manual buttons
Cost: ~$5/1000 messages
```

### Example 2: Small Business (Free & Manual)

```
Method: Direct Link Only
Provider: (None needed)
Default Action: (N/A)
Show Direct Link Buttons: Yes
Show API Checkboxes: No

Result: All manual, 100% free
Cost: $0
```

### Example 3: Hybrid (Best of Both) ⭐

```
Method: Both
Provider: Meta Cloud API
Default Action: Use API
Show Direct Link Buttons: Yes
Show API Checkboxes: Yes

Result: 
- Auto notifications via API
- Manual buttons for ad-hoc messages
- Full flexibility
Cost: API costs only for automated messages
```

---

## 🔐 Security & Permissions

All settings are **admin-only**. Regular users see:
- Buttons based on admin settings
- Cannot change method
- Cannot see configuration

---

## 📈 Migration Path

### Currently Using API Only?

1. Run SQL migration
2. Settings auto-set to "API Only"
3. Everything works as before
4. Optionally add Direct Link later

### Want to Add Direct Link?

1. Run SQL migration
2. Change method to "Both"
3. Set default action
4. Add buttons to your pages
5. Done!

### Want to Switch to Direct Link Only?

1. Run SQL migration
2. Change method to "Direct Link Only"
3. No API costs anymore
4. Users use manual buttons

---

## 🎓 Advanced Customization

### Custom Default for Different User Levels

```php
function getWhatsAppDefaultActionForUser($userLevel) {
    if ($userLevel == 9) { // Admin
        return 'both'; // Admins see both
    } elseif ($userLevel == 1) { // Client
        return 'direct_link'; // Clients use free method
    } else {
        return getWhatsAppDefaultAction();
    }
}
```

### Per-Shipment Method Override

```php
// In courier add form
<select name="whatsapp_send_method">
    <?php if (isWhatsAppAPIEnabled()) { ?>
        <option value="api">Send via API (Auto)</option>
    <?php } ?>
    <?php if (isWhatsAppDirectLinkEnabled()) { ?>
        <option value="direct_link">Send via Direct Link (Manual)</option>
    <?php } ?>
    <option value="none">Don't Send</option>
</select>
```

---

## 🐛 Troubleshooting

### Issue: Settings not saving
**Fix:** Run SQL migration first

### Issue: Buttons not appearing
**Fix:** Check `enable_direct_link_buttons` is enabled

### Issue: API not sending
**Fix:** Check method is "API Only" or "Both" with correct default action

### Issue: Both methods sending
**Fix:** Check default action setting, should not happen with proper config

---

## ✅ Testing Checklist

- [ ] Run SQL migration
- [ ] Access WhatsApp settings page
- [ ] Try "API Only" - verify checkboxes show, buttons hide
- [ ] Try "Direct Link Only" - verify buttons show, checkboxes hide
- [ ] Try "Both" - verify both show
- [ ] Test default action with "Both"
- [ ] Test single WhatsApp button
- [ ] Test bulk WhatsApp button
- [ ] Verify API still works
- [ ] Verify Direct Link works

---

## 📞 Support Scenarios

**Q: Can I use both at the same time?**
**A:** Yes! Set method to "Both"

**Q: Will this conflict with my API?**
**A:** No, settings control which is active

**Q: Can users choose?**
**A:** Yes, if you set method to "Both", both options are available

**Q: How do I switch from API to Direct Link?**
**A:** Just change method in settings, no code changes needed

**Q: Is Direct Link really free?**
**A:** Yes! 100% free, uses wa.me links

---

## 🎉 Summary

**You asked:** "Can we control this in settings to avoid conflicts?"

**Answer:** YES! ✅

The system now has:
1. ✅ Full settings control
2. ✅ No conflicts between methods
3. ✅ User can switch between methods
4. ✅ Admin controls everything
5. ✅ Works with existing API setup
6. ✅ Can use both simultaneously

**Perfect for any use case!** 🎯
