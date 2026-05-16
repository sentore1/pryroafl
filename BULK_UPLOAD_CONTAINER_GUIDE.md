# Bulk Upload Container Feature

## Overview
This feature allows you to upload multiple clients with their goods at the same time using an Excel or CSV file, making it easier to create containers with many shipments.

## How to Access

### Option 1: From Sidebar Menu
1. Navigate to **Container** → **Shipments** → **Bulk Upload** in the left sidebar

### Option 2: From Container Add Page
1. Go to **Container** → **Shipments** → **Packages to Container**
2. Click the **"Bulk Upload"** button at the top right

## How to Use

### Step 1: Download Template
1. On the Bulk Upload page, click **"Download Template"** button
2. This will download a CSV file with sample data and the correct format

### Step 2: Prepare Your Data
Fill in the template with your shipment data. Required columns:

| Column Name | Description | Example |
|------------|-------------|---------|
| sender_email | Email address of the sender (must exist in system) | sender@example.com |
| sender_fname | Sender's first name (used if creating new user) | John |
| sender_lname | Sender's last name (used if creating new user) | Smith |
| recipient_email | Email address of the recipient (must exist in system) | recipient@example.com |
| recipient_fname | Recipient's first name (used if creating new user) | Jane |
| recipient_lname | Recipient's last name (used if creating new user) | Doe |
| tracking_prefix | Tracking number prefix | CDPE |
| tracking_number | Tracking number (without prefix) | 123456 |
| item_description | Description of package contents | Electronics - Laptop |
| weight | Package weight | 5.5 |
| length | Package length | 10 |
| width | Package width | 8 |
| height | Package height | 6 |
| sender_country | Origin country | USA |
| sender_city | Origin city | New York |
| sender_address | Origin address | 123 Main St |
| recipient_country | Destination country | Canada |
| recipient_city | Destination city | Toronto |
| recipient_address | Destination address | 456 Oak Ave |

### Step 3: Upload File
1. Click **"Choose File"** and select your prepared Excel (.xlsx, .xls) or CSV file
2. Click **"Upload and Process"**
3. The system will validate all records and show you the results

### Step 4: Review Results
The system will display:
- **Success records** (in green) - Valid shipments that can be added to container
- **Error records** (in red) - Invalid records with error messages
- Summary showing total success and error counts

### Step 5: Create Container
If you have valid shipments, click **"Create Container with Valid Shipments"** to proceed to the container creation page.

## Validation Rules

The system validates:
1. **Sender exists** - Sender email must match an existing customer in the system
2. **Recipient exists** - Recipient email must match an existing user in the system
3. **Shipment exists** - Tracking number must match an existing shipment
4. **Not already consolidated** - Shipment must not already be part of another container
5. **Valid status** - Shipment must not be delivered, cancelled, or in certain restricted statuses

## Common Errors and Solutions

| Error | Solution |
|-------|----------|
| "Sender not found" | Make sure the sender email exists in your customer database |
| "Recipient not found" | Make sure the recipient email exists in your user database |
| "Shipment not found" | Verify the tracking prefix and number are correct |
| "Shipment already consolidated" | This shipment is already in another container |
| "Missing required fields" | Make sure all required columns have values |

## Tips for Success

1. **Use the template** - Always start with the downloaded template to ensure correct format
2. **Check emails** - Verify all sender and recipient emails exist in your system before uploading
3. **Verify tracking numbers** - Double-check tracking prefixes and numbers
4. **Test with small batch** - Try uploading 5-10 records first to verify your data format
5. **Keep backup** - Save a copy of your original file before uploading

## File Format Support

- **CSV** (.csv) - Comma-separated values
- **Excel** (.xlsx, .xls) - Microsoft Excel format

## Technical Notes

- Maximum file size: Depends on your server configuration
- Encoding: UTF-8 recommended for special characters
- Date format: System will use current date/time for container creation
- The bulk upload validates data but doesn't create the container automatically - you still need to complete the container creation process

## Support

If you encounter issues:
1. Check the error messages in the results table
2. Verify your data matches the template format
3. Ensure all referenced users and shipments exist in the system
4. Contact your system administrator for database-related issues
