# AI Action Buttons Troubleshooting Guide

**Problem:** Action buttons show "Failed" when clicked

---

## 🔍 Diagnosis Steps

### Step 1: Check Error Message
**NEW:** Error messages now appear in the chat!

When you click an action button and it fails:
1. Look in the AI chat area
2. A red error message will appear showing the exact problem
3. Common error messages:
   - "Permission denied: AI cannot..."
   - "Missing order_id or status_id"
   - "Network error: Connection failed"

### Step 2: Check Browser Console
1. Press `F12` to open Developer Tools
2. Go to "Console" tab
3. Look for red errors
4. Check "Network" tab for failed requests

---

## ✅ Common Solutions

### Solution 1: Enable AI Permissions
**Most Common Cause**

1. Go to **Tools → Config** (or navigate to `tools.php`)
2. Look for **AI Settings** tab/section
3. Enable these permissions:
   - ✅ AI can update shipment status
   - ✅ AI can confirm payments
   - ✅ AI can assign drivers
4. Click **Save**
5. Try action button again

**Alternative Location:**
- Check file: `ajax/ai/ai_permissions_helper.php`
- Settings might be in database table: `cdb_settings`

### Solution 2: Check AI Permissions Helper
The file `ajax/ai/ai_permissions_helper.php` must exist.

**Check if it exists:**
```bash
ls ajax/ai/ai_permissions_helper.php
```

**If missing, create it:**
```php
<?php
class AIPermissions {
    private $db;
    
    public function __construct() {
        $this->db = new Conexion;
    }
    
    // Default: Allow all actions (change to false for security)
    public function canUpdateStatus() {
        return true;
    }
    
    public function canConfirmPayments() {
        return true;
    }
    
    public function canAssignDrivers() {
        return true;
    }
    
    public function canCancelShipments() {
        return false; // More restrictive
    }
    
    public function canSendSMS() {
        return false;
    }
    
    public function canSendEmail() {
        return false;
    }
    
    public function canSendWhatsApp() {
        return false;
    }
    
    public function canGenerateReports() {
        return false;
    }
    
    public function canExportData() {
        return false;
    }
    
    public function canCreateShipments() {
        return false;
    }
    
    public function canEditShipments() {
        return false;
    }
    
    public function canCreateCustomers() {
        return false;
    }
    
    public function canApplyDiscounts() {
        return false;
    }
    
    public function isAutopilotEnabled() {
        return false;
    }
    
    public function getAutopilotThreshold() {
        return 5;
    }
    
    public function getPermissionsSummary() {
        return "AI can update status, confirm payments, and assign drivers.";
    }
}
?>
```

### Solution 3: Check Database Connection
1. Make sure XAMPP MySQL is running
2. Check `loader.php` is loading correctly
3. Test database connection:
```php
<?php
require_once("loader.php");
$db = new Conexion;
echo "Database connected!";
?>
```

### Solution 4: Check User Permissions
1. Make sure you're logged in as **Admin** (userlevel 9 or 2)
2. Action handler checks: `if (!$user->cdp_is_Admin())`
3. Non-admin users will get "Unauthorized" error

### Solution 5: Verify Action Data
Check that the action button has required data:

**Minimum Required:**
```javascript
{
    "action": "update_status",
    "order_id": 123,        // Must be valid number
    "status_id": 4,         // Must be valid status
    "order_type": "courier" // Must be valid type
}
```

**Valid Status IDs:**
- 2 = Pending
- 3 = Processing
- 4 = In Transit
- 5 = Out for Delivery
- 8 = Delivered
- 21 = Cancelled

---

## 🐛 Debugging Mode

### Enable Detailed Logging

**In `ajax/ai/ai_action_ajax.php`, add at the top:**
```php
<?php
// DEBUG MODE - Remove in production
error_reporting(E_ALL);
ini_set('display_errors', 1);
file_put_contents('ai_action_log.txt', date('Y-m-d H:i:s') . ' - ' . print_r($_POST, true) . "\n", FILE_APPEND);
```

This will:
1. Show PHP errors
2. Log all requests to `ai_action_log.txt`
3. Help identify the exact problem

**Check the log file:**
```bash
tail -f ai_action_log.txt
```

---

## 🔧 Specific Error Solutions

### Error: "Permission denied"
```
Permission denied: AI cannot update shipment status
```

**Solution:**
1. Go to AI Settings
2. Enable the specific permission
3. Or modify `ai_permissions_helper.php` to return `true`

### Error: "Missing order_id"
```
Missing order_id or status_id
```

**Solution:**
- The action button data is incomplete
- Check AI response JSON
- Verify `ACTIONS_JSON` includes all required fields

### Error: "Unknown action"
```
Unknown action: update_status
```

**Solution:**
- Action not in switch statement
- Check `ai_action_ajax.php` has case for this action
- Verify action name spelling

### Error: "Unauthorized"
```
Unauthorized
```

**Solution:**
- Not logged in as admin
- Session expired
- Log out and log back in

### Error: "Network error"
```
Network error: Connection failed
```

**Solution:**
- AJAX request failed
- Check browser console for details
- Verify `ajax/ai/ai_action_ajax.php` path is correct
- Check Apache/XAMPP is running

---

## 📊 Test Action Manually

### Test with Postman or cURL

```bash
curl -X POST http://localhost/pryroafl/ajax/ai/ai_action_ajax.php \
-H "Content-Type: application/x-www-form-urlencoded" \
-d "action=update_status" \
-d 'payload={"order_id":2,"status_id":4,"order_type":"courier"}'
```

**Expected Response:**
```json
{"success":true,"message":"Status updated successfully."}
```

**Error Response:**
```json
{"success":false,"message":"Permission denied: AI cannot update shipment status. Enable this in AI Settings."}
```

---

## ✅ Verification Checklist

Before clicking action buttons, verify:

- [ ] Logged in as Admin (level 9 or 2)
- [ ] XAMPP MySQL is running
- [ ] AI Permissions enabled in settings
- [ ] `ai_permissions_helper.php` exists
- [ ] `ai_action_ajax.php` exists
- [ ] Order ID is valid (exists in database)
- [ ] Browser console shows no JavaScript errors
- [ ] Network tab shows 200 OK response

---

## 🚀 Quick Fix (Most Common)

**If you're getting "Failed" on all action buttons:**

1. **Check permissions file exists:**
   ```
   ajax/ai/ai_permissions_helper.php
   ```

2. **If missing, the system might be using default (all denied)**

3. **Quick fix - Create the file with this content:**
   ```php
   <?php
   class AIPermissions {
       public function canUpdateStatus() { return true; }
       public function canConfirmPayments() { return true; }
       public function canAssignDrivers() { return true; }
       public function canCancelShipments() { return false; }
       public function canSendSMS() { return false; }
       public function canSendEmail() { return false; }
       public function canSendWhatsApp() { return false; }
       public function canGenerateReports() { return false; }
       public function canExportData() { return false; }
       public function canCreateShipments() { return false; }
       public function canEditShipments() { return false; }
       public function canCreateCustomers() { return false; }
       public function canApplyDiscounts() { return false; }
       public function isAutopilotEnabled() { return false; }
       public function getAutopilotThreshold() { return 5; }
       public function getPermissionsSummary() {
           return "AI can: update status, confirm payments, assign drivers";
       }
   }
   ?>
   ```

4. **Refresh browser and try again**

---

## 📞 Still Not Working?

### Get More Info:
1. **Check error message in chat** (new feature)
2. **Check browser console** (F12 → Console)
3. **Check Apache error log**
   - Location: `C:\xampp\apache\logs\error.log`
4. **Check PHP error log**
   - Location: `C:\xampp\php\logs\php_error_log`

### Report Issue:
Include this information:
- Exact error message from chat
- Browser console errors
- Action button data (JSON)
- PHP version
- Browser version

---

**Updated:** June 18, 2026  
**Version:** 2.1  
**Status:** Enhanced with error messages in chat

