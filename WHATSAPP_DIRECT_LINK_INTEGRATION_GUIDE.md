# WhatsApp Direct Link Integration Guide

## Overview
This implementation allows you to send WhatsApp messages using `https://wa.me/` links that open directly in WhatsApp (web or mobile app). This works **without requiring API keys** and happens instantly in the user's browser.

## Features
✅ Single shipment WhatsApp messaging
✅ Bulk shipment WhatsApp messaging (with staggered delays)
✅ No API credentials needed (uses wa.me links)
✅ Works on desktop and mobile
✅ Automatic message generation with tracking info
✅ Action logging to database
✅ Pure JavaScript + PHP (no Python needed)

## How It Works

### Technology Stack
- **Frontend**: JavaScript/jQuery (opens WhatsApp tabs)
- **Backend**: PHP (fetches shipment data)
- **Database**: MySQL (logs actions)

### Flow Diagram
```
User clicks button → AJAX request → PHP fetches data → JavaScript opens wa.me link → WhatsApp opens
```

## Installation Steps

### Step 1: Run Database Migration
```sql
-- Run this SQL in your database
source c:\xampp\htdocs\pryroafl\sql\whatsapp_direct_link_migration.sql
```

Or manually execute the SQL file via phpMyAdmin.

### Step 2: Include JavaScript File
Add to your page header or footer (where jQuery is loaded):

```html
<script src="dataJs/whatsapp_direct_link.js"></script>
```

### Step 3: Add WhatsApp Buttons to Your Pages

#### A. Single Shipment Button (in dropdown menu)
In `ajax/courier/courier_list_ajax.php`, add this inside the dropdown menu:

```html
<!-- Add this in the dropdown menu after email option -->
<a class="dropdown-item btn-whatsapp-single" 
   href="#" 
   data-order-id="<?php echo $row->order_id; ?>" 
   data-recipient-type="sender">
    <i class="fab fa-whatsapp" style="color:#25D366"></i>
    &nbsp;Send WhatsApp to Sender
</a>

<a class="dropdown-item btn-whatsapp-single" 
   href="#" 
   data-order-id="<?php echo $row->order_id; ?>" 
   data-recipient-type="receiver">
    <i class="fab fa-whatsapp" style="color:#25D366"></i>
    &nbsp;Send WhatsApp to Receiver
</a>
```

#### B. Bulk WhatsApp Button
Add this button near other bulk action buttons (e.g., near "Delete Selected"):

```html
<button type="button" id="btn-whatsapp-bulk" class="btn btn-success">
    <i class="fab fa-whatsapp"></i> Send WhatsApp (Bulk)
</button>
```

### Step 4: Modify Your Table Rows (Optional but Recommended)
For better bulk functionality, add data attributes to your `<tr>` tags:

```php
<tr class="card-hovera" 
    data-sender-phone="<?php echo $sender_data->phone; ?>"
    data-receiver-phone="<?php echo $receiver_data->phone; ?>">
```

## Usage Examples

### Example 1: Single WhatsApp from Courier List
```javascript
// Button is already wired up via class .btn-whatsapp-single
// Just add the button to your page (see Step 3A)
```

### Example 2: Bulk WhatsApp
```javascript
// Button is already wired up via ID #btn-whatsapp-bulk
// Just add the button to your page (see Step 3B)
```

### Example 3: Custom Integration
```javascript
// Send WhatsApp to specific phone with custom message
WhatsAppDirectLink.openWhatsApp(
    '1234567890', 
    'Hello! Your package is ready.',
    true  // Open in new tab
);
```

### Example 4: Programmatic Bulk Send
```javascript
var shipments = [
    {
        phone: '1234567890',
        trackingNumber: 'TRK001',
        customerName: 'John Doe',
        companyName: 'My Company',
        siteUrl: 'https://mysite.com'
    },
    {
        phone: '0987654321',
        trackingNumber: 'TRK002',
        customerName: 'Jane Smith',
        companyName: 'My Company',
        siteUrl: 'https://mysite.com'
    }
];

WhatsAppDirectLink.sendToBulk(shipments, 2000); // 2 second delay between sends
```

## Message Template
The default message format is:

```
Hello [Customer Name],

Your shipment tracking number is: *[Tracking Number]*

Status: [Status]

Track your shipment here:
[Site URL]/track.php?tracking_id=[Tracking Number]

Best regards,
[Company Name]
```

### Customizing Messages
To customize the message, modify the `generateMessage()` function in `dataJs/whatsapp_direct_link.js`.

## API Endpoints

### 1. Get Single Shipment Data
**Endpoint**: `ajax/whatsapp/get_whatsapp_link_data.php`

**Method**: POST

**Parameters**:
- `order_id` (int): Order ID
- `recipient_type` (string): 'sender' or 'receiver'

**Response**:
```json
{
    "success": true,
    "phone": "1234567890",
    "message": "Hello John Doe...",
    "tracking_number": "TRK001",
    "customer_name": "John Doe",
    "company_name": "My Company",
    "site_url": "https://mysite.com"
}
```

### 2. Get Bulk Shipments Data
**Endpoint**: `ajax/whatsapp/get_bulk_whatsapp_data.php`

**Method**: POST

**Parameters**:
- `order_numbers` (JSON array): Array of order numbers

**Response**:
```json
{
    "success": true,
    "shipments": [
        {
            "orderNo": "001",
            "phone": "1234567890",
            "trackingNumber": "TRK001",
            "customerName": "John Doe",
            "companyName": "My Company",
            "siteUrl": "https://mysite.com",
            "customMessage": "Hello John Doe...",
            "status": "In Transit"
        }
    ],
    "count": 1
}
```

### 3. Log WhatsApp Action
**Endpoint**: `ajax/whatsapp/log_whatsapp_action.php`

**Method**: POST

**Parameters**:
- `order_id` (int): Order ID
- `recipient_type` (string): 'sender' or 'receiver'
- `action_type` (string): 'direct_link', 'api', 'bulk'

## Browser Compatibility
- ✅ Chrome/Edge: Opens WhatsApp Web or desktop app
- ✅ Firefox: Opens WhatsApp Web or desktop app
- ✅ Safari (iOS): Opens WhatsApp mobile app
- ✅ Chrome (Android): Opens WhatsApp mobile app

## Limitations & Best Practices

### Limitations
1. **Popup Blockers**: Users must allow popups for bulk operations
2. **Rate Limiting**: Some browsers may limit rapid tab opening (use delays)
3. **Phone Numbers**: Must include country code (system auto-formats)
4. **No Auto-Send**: User must manually send each message (WhatsApp requirement)

### Best Practices
1. **Bulk Operations**: Use 1.5-2 second delays between messages
2. **Phone Validation**: Ensure phone numbers in database include country codes
3. **User Confirmation**: Always confirm before bulk operations
4. **Limit Batch Size**: Maximum 50 shipments per bulk operation
5. **Clear Instructions**: Tell users to allow popups

## Advantages Over API-Based Solutions

| Feature | wa.me Links | API (Twilio/Meta) |
|---------|-------------|-------------------|
| Setup Complexity | ✅ Simple | ❌ Complex |
| Cost | ✅ Free | ❌ Paid |
| API Keys | ✅ Not needed | ❌ Required |
| Instant Setup | ✅ Yes | ❌ No |
| Background Send | ❌ No | ✅ Yes |
| Auto Send | ❌ No | ✅ Yes |
| User Control | ✅ High | ❌ Low |

## Troubleshooting

### Issue: Phone numbers not found
**Solution**: Ensure phone numbers are stored in database with country codes

### Issue: Popup blocked
**Solution**: Instruct users to allow popups in browser settings

### Issue: Bulk operation stops
**Solution**: Increase delay between messages (default 1500ms)

### Issue: WhatsApp doesn't open
**Solution**: 
- Check phone number format
- Ensure WhatsApp is installed
- Try opening wa.me link manually in browser

## Example Integration in Courier List

```php
<!-- In ajax/courier/courier_list_ajax.php -->
<!-- Add after the email option in dropdown -->

<?php if (!empty($sender_data->phone)) { ?>
    <a class="dropdown-item btn-whatsapp-single" 
       href="#" 
       data-order-id="<?php echo $row->order_id; ?>" 
       data-recipient-type="sender">
        <i class="fab fa-whatsapp" style="color:#25D366"></i>
        &nbsp;WhatsApp Sender
    </a>
<?php } ?>

<?php if (!empty($receiver_data->phone)) { ?>
    <a class="dropdown-item btn-whatsapp-single" 
       href="#" 
       data-order-id="<?php echo $row->order_id; ?>" 
       data-recipient-type="receiver">
        <i class="fab fa-whatsapp" style="color:#25D366"></i>
        &nbsp;WhatsApp Receiver
    </a>
<?php } ?>
```

## Logging & Analytics

All WhatsApp actions are logged to `cdb_whatsapp_logs` table:

```sql
-- View recent WhatsApp actions
SELECT 
    l.*,
    o.order_prefix,
    o.order_no,
    u.fname,
    u.lname
FROM cdb_whatsapp_logs l
JOIN cdb_add_order o ON l.order_id = o.order_id
JOIN cdb_users u ON l.user_id = u.id
ORDER BY l.created_at DESC
LIMIT 50;
```

## Support & Customization

For customization needs:
1. Modify message template in `dataJs/whatsapp_direct_link.js`
2. Add custom data attributes to table rows
3. Extend PHP endpoints for additional data
4. Add custom buttons to any page

## Conclusion

This solution provides a **simple, free, and effective** way to send WhatsApp messages without API complexity. It works best for:
- Manual notifications
- Small to medium batch operations
- User-initiated communications
- Systems without WhatsApp API budget

For fully automated, background messaging, continue using your existing API integrations (Twilio/Meta/UltraMsg).
