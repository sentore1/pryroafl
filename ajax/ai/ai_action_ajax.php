<?php
// =============================================================
// AI ACTION EXECUTOR — performs actions on behalf of Pryro AI
// =============================================================
// Buffer ALL output — prevents any PHP warnings/errors or DB
// error messages from corrupting the JSON response.
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once("../../loader.php");
require_once("ai_permissions_helper.php");

// Flush any output that loader.php may have produced
ob_clean();

// Safety net: if anything outputs HTML before our JSON, catch it
register_shutdown_function(function() {
    $output = ob_get_clean();
    // If output doesn't start with { it's not our JSON — something went wrong
    $trimmed = ltrim($output);
    if (empty($trimmed)) return;
    if ($trimmed[0] !== '{' && $trimmed[0] !== '[') {
        // Something printed HTML/text — wrap as error and return clean JSON
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Server output error. Check PHP error logs.']);
    } else {
        echo $output;
    }
});

$user = new User;
if (!$user->cdp_is_Admin()) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action  = isset($_POST['action'])  ? trim($_POST['action'])  : '';
$payload = isset($_POST['payload']) ? $_POST['payload']       : '{}';
$data    = json_decode($payload, true) ?: [];

$db = new Conexion;
$perms = new AIPermissions();

// Clean buffer one more time before switch — catches any DB connection output
ob_clean();

try {
switch ($action) {

    // ----------------------------------------------------------
    // ASSIGN DRIVER to a shipment
    // ----------------------------------------------------------
    case 'assign_driver':
        if (!$perms->canAssignDrivers()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot assign drivers. Enable this in AI Settings.']);
            exit;
        }
        
        $order_id  = isset($data['order_id'])  ? (int)$data['order_id']  : 0;
        $driver_id = isset($data['driver_id']) ? (int)$data['driver_id'] : 0;
        if (!$order_id || !$driver_id) {
            echo json_encode(['success' => false, 'message' => 'Missing order_id or driver_id']);
            exit;
        }
        $db->cdp_query("UPDATE cdb_add_order SET driver_id=:driver_id WHERE order_id=:order_id");
        $db->bind(':driver_id', $driver_id);
        $db->bind(':order_id',  $order_id);
        $db->cdp_execute();
        echo json_encode(['success' => true, 'message' => 'Driver assigned successfully.']);
        break;

    // ----------------------------------------------------------
    // CONFIRM PAYMENT on a shipment
    // ----------------------------------------------------------
    case 'confirm_payment':
        if (!$perms->canConfirmPayments()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot confirm payments. Enable this in AI Settings.']);
            exit;
        }
        
        $order_id   = isset($data['order_id'])   ? (int)$data['order_id']   : 0;
        $order_type = isset($data['order_type']) ? $data['order_type']       : 'courier'; // courier | consolidate | package
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Missing order_id']);
            exit;
        }
        if ($order_type === 'consolidate') {
            $db->cdp_query("UPDATE cdb_consolidate SET status_invoice=3 WHERE c_id=:id");
        } elseif ($order_type === 'package') {
            $db->cdp_query("UPDATE cdb_customers_packages SET status_invoice=3 WHERE order_id=:id");
        } else {
            $db->cdp_query("UPDATE cdb_add_order SET status_invoice=3 WHERE order_id=:id");
        }
        $db->bind(':id', $order_id);
        $db->cdp_execute();

        // Also update payment gateway record if exists
        $db->cdp_query("UPDATE cdb_payment_gateways SET payment_status=3 WHERE order_id=:id AND payment_status=2");
        $db->bind(':id', $order_id);
        $db->cdp_execute();

        echo json_encode(['success' => true, 'message' => 'Payment confirmed successfully.']);
        break;

    // ----------------------------------------------------------
    // UPDATE STATUS on a shipment
    // ----------------------------------------------------------
    case 'update_status':
        if (!$perms->canUpdateStatus()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot update shipment status. Enable this in AI Settings.']);
            exit;
        }
        
        $order_id   = isset($data['order_id'])     ? (int)$data['order_id']     : 0;
        $status_id  = isset($data['status_id'])    ? (int)$data['status_id']    : 0;
        $order_type = isset($data['order_type'])   ? $data['order_type']        : 'courier';
        $comment    = isset($data['comment'])      ? trim($data['comment'])      : 'Status updated by Pryro AI';
        
        // Check if trying to cancel
        if ($status_id == 21 && !$perms->canCancelShipments()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot cancel shipments. Enable this in AI Settings.']);
            exit;
        }
        
        if (!$order_id || !$status_id) {
            echo json_encode(['success' => false, 'message' => 'Missing order_id or status_id']);
            exit;
        }

        if ($order_type === 'consolidate') {
            $db->cdp_query("UPDATE cdb_consolidate SET status_courier=:status WHERE c_id=:id");
        } elseif ($order_type === 'package') {
            $db->cdp_query("UPDATE cdb_customers_packages SET status_courier=:status WHERE order_id=:id");
        } else {
            $db->cdp_query("UPDATE cdb_add_order SET status_courier=:status WHERE order_id=:id");
        }
        $db->bind(':status', $status_id);
        $db->bind(':id',     $order_id);
        $db->cdp_execute();

        // Add tracking record
        $db->cdp_query("SELECT order_prefix, order_no FROM cdb_add_order WHERE order_id=:id");
        $db->bind(':id', $order_id);
        $db->cdp_execute();
        $ord = $db->cdp_registro();
        if ($ord) {
            $track = $ord->order_prefix . $ord->order_no;
            $userData = $user->cdp_getUserData();
            $db->cdp_query("INSERT INTO cdb_courier_track (order_id, order_track, comments, t_date, status_courier, user_id) VALUES (:oid, :track, :comment, NOW(), :status, :uid)");
            $db->bind(':oid',     $order_id);
            $db->bind(':track',   $track);
            $db->bind(':comment', $comment);
            $db->bind(':status',  $status_id);
            $db->bind(':uid',     $userData->id);
            $db->cdp_execute();
        }

        echo json_encode(['success' => true, 'message' => 'Status updated successfully.']);
        break;

    // ----------------------------------------------------------
    // BULK CONFIRM all pending wire payments
    // ----------------------------------------------------------
    case 'confirm_all_wire_payments':
        if (!$perms->canConfirmPayments()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot confirm payments. Enable this in AI Settings.']);
            exit;
        }
        
        $db->cdp_query("UPDATE cdb_add_order SET status_invoice=3 WHERE due_date < NOW() AND status_invoice=2 AND status_courier!=21");
        $db->cdp_execute();
        $affected = $db->cdp_rowCount();
        echo json_encode(['success' => true, 'message' => $affected . ' overdue invoice(s) marked as paid.']);
        break;

    // ----------------------------------------------------------
    // SEND SMS NOTIFICATION
    // ----------------------------------------------------------
    case 'send_sms':
        if (!$perms->canSendSMS()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot send SMS. Enable this in AI Settings.']);
            exit;
        }
        
        $order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
        $message  = isset($data['message'])  ? trim($data['message'])  : '';
        $phone    = isset($data['phone'])    ? trim($data['phone'])    : '';
        
        if (!$message) {
            echo json_encode(['success' => false, 'message' => 'Missing message']);
            exit;
        }
        
        // Get customer phone from order if not provided
        if (!$phone && $order_id) {
            $db->cdp_query("SELECT u.phone FROM cdb_add_order o LEFT JOIN cdb_users u ON u.id = o.sender_id WHERE o.order_id = :id");
            $db->bind(':id', $order_id);
            $db->cdp_execute();
            $order = $db->cdp_registro();
            $phone = $order ? $order->phone : '';
        }
        
        if (!$phone) {
            echo json_encode(['success' => false, 'message' => 'Phone number not found']);
            exit;
        }
        
        // TODO: Integrate with your existing SMS service (Twilio, etc.)
        // Example: $sms_service->send($phone, $message);
        
        echo json_encode(['success' => true, 'message' => 'SMS sent to ' . $phone . ' (Demo mode - integrate with your SMS provider)']);
        break;

    // ----------------------------------------------------------
    // SEND EMAIL NOTIFICATION
    // ----------------------------------------------------------
    case 'send_email':
        if (!$perms->canSendEmail()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot send emails. Enable this in AI Settings.']);
            exit;
        }
        
        $order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
        $subject  = isset($data['subject'])  ? trim($data['subject'])  : '';
        $message  = isset($data['message'])  ? trim($data['message'])  : '';
        $email    = isset($data['email'])    ? trim($data['email'])    : '';
        
        if (!$subject || !$message) {
            echo json_encode(['success' => false, 'message' => 'Missing subject or message']);
            exit;
        }
        
        // Get customer email from order if not provided
        if (!$email && $order_id) {
            $db->cdp_query("SELECT u.email FROM cdb_add_order o LEFT JOIN cdb_users u ON u.id = o.sender_id WHERE o.order_id = :id");
            $db->bind(':id', $order_id);
            $db->cdp_execute();
            $order = $db->cdp_registro();
            $email = $order ? $order->email : '';
        }
        
        if (!$email) {
            echo json_encode(['success' => false, 'message' => 'Email address not found']);
            exit;
        }
        
        // TODO: Use your existing email system
        // Example: mail($email, $subject, $message);
        
        echo json_encode(['success' => true, 'message' => 'Email sent to ' . $email . ' (Demo mode - integrate with your email system)']);
        break;

    // ----------------------------------------------------------
    // SEND WHATSAPP MESSAGE
    // ----------------------------------------------------------
    case 'send_whatsapp':
        if (!$perms->canSendWhatsApp()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot send WhatsApp messages. Enable this in AI Settings.']);
            exit;
        }
        
        $order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
        $message  = isset($data['message'])  ? trim($data['message'])  : '';
        $phone    = isset($data['phone'])    ? trim($data['phone'])    : '';
        
        if (!$message) {
            echo json_encode(['success' => false, 'message' => 'Missing message']);
            exit;
        }
        
        // Get customer phone from order if not provided
        if (!$phone && $order_id) {
            $db->cdp_query("SELECT u.phone FROM cdb_add_order o LEFT JOIN cdb_users u ON u.id = o.sender_id WHERE o.order_id = :id");
            $db->bind(':id', $order_id);
            $db->cdp_execute();
            $order = $db->cdp_registro();
            $phone = $order ? $order->phone : '';
        }
        
        if (!$phone) {
            echo json_encode(['success' => false, 'message' => 'Phone number not found']);
            exit;
        }
        
        // TODO: Integrate with your WhatsApp Business API
        
        echo json_encode(['success' => true, 'message' => 'WhatsApp message sent to ' . $phone . ' (Demo mode - integrate with WhatsApp API)']);
        break;

    // ----------------------------------------------------------
    // GENERATE REPORT
    // ----------------------------------------------------------
    case 'generate_report':
        if (!$perms->canGenerateReports()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot generate reports. Enable this in AI Settings.']);
            exit;
        }
        
        $report_type = isset($data['report_type']) ? $data['report_type'] : 'revenue';
        $start_date  = isset($data['start_date'])  ? $data['start_date']  : date('Y-m-01');
        $end_date    = isset($data['end_date'])    ? $data['end_date']    : date('Y-m-t');
        
        // TODO: Generate actual report (PDF/Excel)
        
        echo json_encode(['success' => true, 'message' => ucfirst($report_type) . ' report generated for ' . $start_date . ' to ' . $end_date . ' (Demo mode - implement report generation)']);
        break;

    // ----------------------------------------------------------
    // EXPORT DATA
    // ----------------------------------------------------------
    case 'export_data':
        if (!$perms->canExportData()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot export data. Enable this in AI Settings.']);
            exit;
        }
        
        $data_type = isset($data['data_type']) ? $data['data_type'] : 'shipments';
        $format    = isset($data['format'])    ? $data['format']    : 'csv';
        
        // TODO: Generate CSV/Excel export
        
        echo json_encode(['success' => true, 'message' => ucfirst($data_type) . ' data exported as ' . strtoupper($format) . ' (Demo mode - implement data export)']);
        break;

    // ----------------------------------------------------------
    // CREATE SHIPMENT
    // ----------------------------------------------------------
    case 'create_shipment':
        if (!$perms->canCreateShipments()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot create shipments. Enable this in AI Settings.']);
            exit;
        }
        
        $sender_id      = isset($data['sender_id'])      ? (int)$data['sender_id']      : 0;
        $recipient_name = isset($data['recipient_name']) ? trim($data['recipient_name']) : '';
        $recipient_addr = isset($data['recipient_addr']) ? trim($data['recipient_addr']) : '';
        
        if (!$sender_id || !$recipient_name) {
            echo json_encode(['success' => false, 'message' => 'Missing sender_id or recipient information']);
            exit;
        }
        
        // TODO: Implement full shipment creation logic
        // This would include: generating tracking number, calculating price, creating order record, etc.
        
        echo json_encode(['success' => true, 'message' => 'Shipment created successfully (Demo mode - implement full shipment creation)']);
        break;

    // ----------------------------------------------------------
    // EDIT SHIPMENT
    // ----------------------------------------------------------
    case 'edit_shipment':
        if (!$perms->canEditShipments()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot edit shipments. Enable this in AI Settings.']);
            exit;
        }
        
        $order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
        
        if (!$order_id) {
            echo json_encode(['success' => false, 'message' => 'Missing order_id']);
            exit;
        }
        
        // TODO: Implement shipment editing logic
        
        echo json_encode(['success' => true, 'message' => 'Shipment updated successfully (Demo mode - implement shipment editing)']);
        break;

    // ----------------------------------------------------------
    // CREATE CUSTOMER
    // ----------------------------------------------------------
    case 'create_customer':
        if (!$perms->canCreateCustomers()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot create customers. Enable this in AI Settings.']);
            exit;
        }
        
        $fname = isset($data['fname']) ? trim($data['fname']) : '';
        $lname = isset($data['lname']) ? trim($data['lname']) : '';
        $email = isset($data['email']) ? trim($data['email']) : '';
        $phone = isset($data['phone']) ? trim($data['phone']) : '';
        
        if (!$fname || !$email) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing required fields (name, email)']);
            exit;
        }
        
        // Check if email already exists
        $db->cdp_query("SELECT id FROM cdb_users WHERE email = :email LIMIT 1");
        $db->bind(':email', $email);
        $db->cdp_execute();
        if ($db->cdp_registro()) {
            echo json_encode(['success' => false, 'message' => 'A customer with this email already exists.']);
            exit;
        }
        
        // Generate unique username from email
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0])) . rand(100, 999);
        
        // Generate random password
        $raw_password = bin2hex(random_bytes(6));
        $hashed       = password_hash($raw_password, PASSWORD_DEFAULT);
        
        $db->cdp_query("INSERT INTO cdb_users (username, password, userlevel, email, fname, lname, phone, created, active)
                        VALUES (:username, :password, 1, :email, :fname, :lname, :phone, NOW(), 1)");
        $db->bind(':username', $username);
        $db->bind(':password', $hashed);
        $db->bind(':email',    $email);
        $db->bind(':fname',    $fname);
        $db->bind(':lname',    $lname);
        $db->bind(':phone',    $phone);
        $db->cdp_execute();
        
        $new_id = $db->dbh->lastInsertId();
        if ($new_id) {
            $full_name = trim($fname . ' ' . $lname);
            echo json_encode(['success' => true, 'message' => 'Customer "' . $full_name . '" created successfully (ID: ' . $new_id . ')']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create customer. Please try again.']);
        }
        break;

    // ----------------------------------------------------------
    // APPLY DISCOUNT
    // ----------------------------------------------------------
    case 'apply_discount':
        if (!$perms->canApplyDiscounts()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot apply discounts. Enable this in AI Settings.']);
            exit;
        }
        
        $order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
        $discount = isset($data['discount']) ? (float)$data['discount'] : 0;
        
        if (!$order_id || !$discount) {
            echo json_encode(['success' => false, 'message' => 'Missing order_id or discount amount']);
            exit;
        }
        
        // TODO: Implement discount application logic
        
        echo json_encode(['success' => true, 'message' => 'Discount applied successfully (Demo mode - implement discount logic)']);
        break;

    // ----------------------------------------------------------
    // CANCEL SHIPMENT
    // ----------------------------------------------------------
    case 'cancel_shipment':
        if (!$perms->canCancelShipments()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot cancel shipments. Enable this in AI Settings.']);
            exit;
        }
        $order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
        $reason   = isset($data['reason'])   ? trim($data['reason'])   : 'Cancelled by Pryro AI';
        if (!$order_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing order_id']);
            exit;
        }
        $db->cdp_query("UPDATE cdb_add_order SET status_courier=21 WHERE order_id=:id");
        $db->bind(':id', $order_id);
        $db->cdp_execute();
        // Add tracking entry
        $userData = $user->cdp_getUserData();
        $db->cdp_query("SELECT order_prefix, order_no FROM cdb_add_order WHERE order_id=:id");
        $db->bind(':id', $order_id); $db->cdp_execute(); $ord = $db->cdp_registro();
        if ($ord) {
            $db->cdp_query("INSERT INTO cdb_courier_track (order_id, order_track, comments, t_date, status_courier, user_id) VALUES (:oid,:track,:comment,NOW(),21,:uid)");
            $db->bind(':oid',   $order_id);
            $db->bind(':track', $ord->order_prefix . $ord->order_no);
            $db->bind(':comment', $reason);
            $db->bind(':uid',   $userData->id);
            $db->cdp_execute();
        }
        echo json_encode(['success' => true, 'message' => 'Shipment #' . $order_id . ' cancelled. Reason: ' . $reason]);
        break;

    // ----------------------------------------------------------
    // ADD PRE-ALERT
    // ----------------------------------------------------------
    case 'add_prealert':
        $tracking    = isset($data['tracking'])    ? trim($data['tracking'])    : '';
        $customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : 0;
        $description = isset($data['description']) ? trim($data['description']) : '';
        $weight      = isset($data['weight'])      ? (float)$data['weight']     : 0;
        if (!$tracking || !$customer_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing tracking number or customer ID']);
            exit;
        }
        $db->cdp_query("INSERT INTO cdb_prealert (user_id, tracking, description, weight, status, created) VALUES (:uid,:tracking,:desc,:weight,0,NOW())");
        $db->bind(':uid',      $customer_id);
        $db->bind(':tracking', $tracking);
        $db->bind(':desc',     $description);
        $db->bind(':weight',   $weight);
        $db->cdp_execute();
        $new_id = $db->dbh->lastInsertId();
        echo json_encode(['success' => (bool)$new_id, 'message' => $new_id ? 'Pre-alert added for tracking ' . $tracking : 'Failed to add pre-alert']);
        break;

    // ----------------------------------------------------------
    // SCHEDULE PICKUP
    // ----------------------------------------------------------
    case 'schedule_pickup':
        $customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : 0;
        $address     = isset($data['address'])     ? trim($data['address'])     : '';
        $date        = isset($data['date'])        ? trim($data['date'])        : '';
        $notes       = isset($data['notes'])       ? trim($data['notes'])       : '';
        if (!$customer_id || !$address || !$date) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing customer, address or date']);
            exit;
        }
        // Create as a pickup order
        $db->cdp_query("SELECT settings_prefix FROM cdb_settings LIMIT 1");
        $db->cdp_execute(); $s = $db->cdp_registro();
        $prefix = $s ? $s->settings_prefix : 'PRY';
        $db->cdp_query("SELECT MAX(CAST(order_no AS UNSIGNED)) as max_no FROM cdb_add_order");
        $db->cdp_execute(); $mx = $db->cdp_registro();
        $next_no = ($mx && $mx->max_no) ? ((int)$mx->max_no + 1) : 1;
        $db->cdp_query("INSERT INTO cdb_add_order (order_prefix,order_no,sender_id,recipient_address,order_date,order_datetime,status_courier,status_invoice,is_pickup,notes,created_by) VALUES (:prefix,:no,:uid,:addr,NOW(),NOW(),2,2,1,:notes,:admin)");
        $db->bind(':prefix', $prefix);
        $db->bind(':no',     str_pad($next_no, 6, '0', STR_PAD_LEFT));
        $db->bind(':uid',    $customer_id);
        $db->bind(':addr',   $address);
        $db->bind(':notes',  $notes . ' | Pickup date: ' . $date);
        $userData = $user->cdp_getUserData();
        $db->bind(':admin',  $userData->id);
        $db->cdp_execute();
        $new_id = $db->dbh->lastInsertId();
        echo json_encode(['success' => (bool)$new_id, 'message' => $new_id ? 'Pickup scheduled for ' . $date . ' at ' . $address : 'Failed to schedule pickup']);
        break;

    // ----------------------------------------------------------
    // UPDATE CUSTOMER
    // ----------------------------------------------------------
    case 'update_customer':
        $customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : 0;
        $phone       = isset($data['phone'])   ? trim($data['phone'])   : '';
        $email       = isset($data['email'])   ? trim($data['email'])   : '';
        $address     = isset($data['address']) ? trim($data['address']) : '';
        if (!$customer_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing customer ID']);
            exit;
        }
        $updates = []; $binds = [':id' => $customer_id];
        if ($phone)   { $updates[] = 'phone=:phone';     $binds[':phone']   = $phone; }
        if ($email)   { $updates[] = 'email=:email';     $binds[':email']   = $email; }
        if ($address) { $updates[] = 'address=:address'; $binds[':address'] = $address; }
        if (empty($updates)) {
            echo json_encode(['success' => false, 'message' => 'No fields to update — provide phone, email or address']);
            exit;
        }
        $db->cdp_query("UPDATE cdb_users SET " . implode(',', $updates) . " WHERE id=:id");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        $db->cdp_execute();
        echo json_encode(['success' => true, 'message' => 'Customer #' . $customer_id . ' updated successfully.']);
        break;

    // ----------------------------------------------------------
    // REFUND PAYMENT
    // ----------------------------------------------------------
    case 'refund_payment':
        $order_id = isset($data['order_id']) ? (int)$data['order_id']   : 0;
        $amount   = isset($data['amount'])   ? (float)$data['amount']   : 0;
        $reason   = isset($data['reason'])   ? trim($data['reason'])    : '';
        if (!$order_id || !$amount || !$reason) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing order ID, amount or reason']);
            exit;
        }
        // Mark as refunded (status_invoice = 4) and log in tracking
        $db->cdp_query("UPDATE cdb_add_order SET status_invoice=4 WHERE order_id=:id");
        $db->bind(':id', $order_id); $db->cdp_execute();
        $userData = $user->cdp_getUserData();
        $db->cdp_query("SELECT order_prefix, order_no FROM cdb_add_order WHERE order_id=:id");
        $db->bind(':id', $order_id); $db->cdp_execute(); $ord = $db->cdp_registro();
        if ($ord) {
            $db->cdp_query("INSERT INTO cdb_courier_track (order_id,order_track,comments,t_date,status_courier,user_id) VALUES (:oid,:track,:comment,NOW(),0,:uid)");
            $db->bind(':oid',     $order_id);
            $db->bind(':track',   $ord->order_prefix . $ord->order_no);
            $db->bind(':comment', 'REFUND: ' . $amount . ' — ' . $reason);
            $db->bind(':uid',     $userData->id);
            $db->cdp_execute();
        }
        echo json_encode(['success' => true, 'message' => 'Refund of ' . $amount . ' recorded for order #' . $order_id]);
        break;

    // ----------------------------------------------------------
    // ADD CHARGE (Accounts Receivable)
    // ----------------------------------------------------------
    case 'add_charge':
        $customer_id = isset($data['customer_id']) ? (int)$data['customer_id']   : 0;
        $amount      = isset($data['amount'])      ? (float)$data['amount']      : 0;
        $description = isset($data['description']) ? trim($data['description'])  : '';
        $due_date    = isset($data['due_date'])    ? trim($data['due_date'])      : date('Y-m-d', strtotime('+30 days'));
        if (!$customer_id || !$amount || !$description) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing customer ID, amount or description']);
            exit;
        }
        $db->cdp_query("INSERT INTO cdb_accounts_receivable (user_id, amount, description, due_date, status, created) VALUES (:uid,:amount,:desc,:due,1,NOW())");
        $db->bind(':uid',    $customer_id);
        $db->bind(':amount', $amount);
        $db->bind(':desc',   $description);
        $db->bind(':due',    $due_date);
        $db->cdp_execute();
        $new_id = $db->dbh->lastInsertId();
        echo json_encode(['success' => (bool)$new_id, 'message' => $new_id ? 'Charge of ' . $amount . ' added for customer #' . $customer_id : 'Failed to add charge. Check if accounts_receivable table exists.']);
        break;

    // ----------------------------------------------------------
    // SEND BULK SMS
    // ----------------------------------------------------------
    case 'send_bulk_sms':
        if (!$perms->canSendSMS()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot send SMS. Enable this in AI Settings.']);
            exit;
        }
        $filter  = isset($data['filter'])  ? trim($data['filter'])  : 'all';
        $message = isset($data['message']) ? trim($data['message']) : '';
        if (!$message) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing message']);
            exit;
        }
        // Count recipients
        if ($filter === 'all') {
            $db->cdp_query("SELECT COUNT(*) as total FROM cdb_users WHERE userlevel=1 AND active=1 AND phone != '' AND phone IS NOT NULL");
        } elseif (is_numeric($filter)) {
            $db->cdp_query("SELECT COUNT(*) as total FROM cdb_users WHERE id=:id AND phone != ''");
            $db->bind(':id', (int)$filter);
        } else {
            $db->cdp_query("SELECT COUNT(*) as total FROM cdb_users WHERE userlevel=1 AND active=1 AND phone != '' AND phone IS NOT NULL");
        }
        $db->cdp_execute(); $cnt = $db->cdp_registro();
        $count = $cnt ? (int)$cnt->total : 0;
        // TODO: Integrate with SMS provider to actually send
        echo json_encode(['success' => true, 'message' => 'Bulk SMS queued for ' . $count . ' recipient(s). Message: "' . substr($message, 0, 50) . (strlen($message) > 50 ? '...' : '') . '" (Demo mode — integrate SMS provider)']);
        break;

    // ----------------------------------------------------------
    // CREATE DRIVER
    // ----------------------------------------------------------
    case 'create_driver':
        $fname   = isset($data['fname'])   ? trim($data['fname'])   : '';
        $lname   = isset($data['lname'])   ? trim($data['lname'])   : '';
        $email   = isset($data['email'])   ? trim($data['email'])   : '';
        $phone   = isset($data['phone'])   ? trim($data['phone'])   : '';
        $vehicle = isset($data['vehicle']) ? trim($data['vehicle']) : '';
        if (!$fname || !$email) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing first name or email']);
            exit;
        }
        // Check duplicate email
        $db->cdp_query("SELECT id FROM cdb_users WHERE email=:email LIMIT 1");
        $db->bind(':email', $email); $db->cdp_execute();
        if ($db->cdp_registro()) {
            echo json_encode(['success' => false, 'message' => 'A user with this email already exists.']);
            exit;
        }
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0])) . 'drv' . rand(10, 99);
        $hashed   = password_hash(bin2hex(random_bytes(6)), PASSWORD_DEFAULT);
        $db->cdp_query("INSERT INTO cdb_users (username,password,userlevel,email,fname,lname,phone,vehicle,created,active) VALUES (:usr,:pass,3,:email,:fname,:lname,:phone,:vehicle,NOW(),1)");
        $db->bind(':usr',     $username);
        $db->bind(':pass',    $hashed);
        $db->bind(':email',   $email);
        $db->bind(':fname',   $fname);
        $db->bind(':lname',   $lname);
        $db->bind(':phone',   $phone);
        $db->bind(':vehicle', $vehicle);
        $db->cdp_execute();
        $new_id = $db->dbh->lastInsertId();
        echo json_encode(['success' => (bool)$new_id, 'message' => $new_id ? 'Driver "' . trim($fname . ' ' . $lname) . '" created (ID: ' . $new_id . ')' : 'Failed to create driver.']);
        break;

    // ----------------------------------------------------------
    // MARK DELIVERED (courier shipment)
    // ----------------------------------------------------------
    case 'mark_delivered':
        if (!$perms->canUpdateStatus()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: AI cannot update shipment status.']);
            exit;
        }
        $order_id        = isset($data['order_id'])        ? (int)$data['order_id']        : 0;
        $person_receives = isset($data['person_receives']) ? trim($data['person_receives']) : '';
        $driver_id       = isset($data['driver_id'])       ? (int)$data['driver_id']       : 0;
        $comment         = isset($data['comment'])         ? trim($data['comment'])         : 'Delivered by Pryro AI';
        if (!$order_id || !$person_receives || !$driver_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing required fields']);
            exit;
        }
        $db->cdp_query("UPDATE cdb_add_order SET status_courier=8, person_receives=:pr WHERE order_id=:id");
        $db->bind(':pr', $person_receives); $db->bind(':id', $order_id); $db->cdp_execute();
        $db->cdp_query("SELECT order_prefix, order_no FROM cdb_add_order WHERE order_id=:id");
        $db->bind(':id', $order_id); $db->cdp_execute(); $ord = $db->cdp_registro();
        if ($ord) {
            $userData = $user->cdp_getUserData();
            $db->cdp_query("INSERT INTO cdb_courier_track (order_id,order_track,comments,t_date,status_courier,user_id) VALUES (:oid,:track,:comment,NOW(),8,:uid)");
            $db->bind(':oid', $order_id); $db->bind(':track', $ord->order_prefix.$ord->order_no);
            $db->bind(':comment', $comment); $db->bind(':uid', $userData->id); $db->cdp_execute();
        }
        echo json_encode(['success' => true, 'message' => 'Shipment #'.$order_id.' marked as Delivered. Received by: '.$person_receives]);
        break;

    // ----------------------------------------------------------
    // ADD TRACKING NOTE
    // ----------------------------------------------------------
    case 'add_tracking_note':
        $order_id  = isset($data['order_id'])  ? (int)$data['order_id']  : 0;
        $status_id = isset($data['status_id']) ? (int)$data['status_id'] : 0;
        $comment   = isset($data['comment'])   ? trim($data['comment'])   : '';
        if (!$order_id || !$status_id || !$comment) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing order_id, status_id or comment']);
            exit;
        }
        $db->cdp_query("UPDATE cdb_add_order SET status_courier=:status WHERE order_id=:id");
        $db->bind(':status', $status_id); $db->bind(':id', $order_id); $db->cdp_execute();
        $db->cdp_query("SELECT order_prefix, order_no FROM cdb_add_order WHERE order_id=:id");
        $db->bind(':id', $order_id); $db->cdp_execute(); $ord = $db->cdp_registro();
        if ($ord) {
            $userData = $user->cdp_getUserData();
            $db->cdp_query("INSERT INTO cdb_courier_track (order_id,order_track,comments,t_date,status_courier,user_id) VALUES (:oid,:track,:comment,NOW(),:status,:uid)");
            $db->bind(':oid', $order_id); $db->bind(':track', $ord->order_prefix.$ord->order_no);
            $db->bind(':comment', $comment); $db->bind(':status', $status_id); $db->bind(':uid', $userData->id);
            $db->cdp_execute();
        }
        echo json_encode(['success' => true, 'message' => 'Tracking note added to shipment #'.$order_id]);
        break;

    // ----------------------------------------------------------
    // DELETE SHIPMENT
    // ----------------------------------------------------------
    case 'delete_shipment':
        $order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
        if (!$order_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing order_id']);
            exit;
        }
        $db->cdp_query("DELETE FROM cdb_add_order WHERE order_id=:id");
        $db->bind(':id', $order_id); $db->cdp_execute();
        $affected = $db->cdp_rowCount();
        echo json_encode(['success' => (bool)$affected, 'message' => $affected ? 'Shipment #'.$order_id.' deleted.' : 'Shipment not found.']);
        break;

    // ----------------------------------------------------------
    // BULK UPDATE STATUS
    // ----------------------------------------------------------
    case 'bulk_update_status':
        if (!$perms->canUpdateStatus()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied.']); exit;
        }
        $order_ids_raw = isset($data['order_ids']) ? $data['order_ids'] : '';
        $status_id     = isset($data['status_id']) ? (int)$data['status_id'] : 0;
        if (!$order_ids_raw || !$status_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing order_ids or status_id']); exit;
        }
        $ids = array_filter(array_map('intval', explode(',', $order_ids_raw)));
        $count = 0;
        $userData = $user->cdp_getUserData();
        foreach ($ids as $oid) {
            $db->cdp_query("UPDATE cdb_add_order SET status_courier=:s WHERE order_id=:id");
            $db->bind(':s', $status_id); $db->bind(':id', $oid); $db->cdp_execute();
            $db->cdp_query("SELECT order_prefix,order_no FROM cdb_add_order WHERE order_id=:id");
            $db->bind(':id', $oid); $db->cdp_execute(); $ord = $db->cdp_registro();
            if ($ord) {
                $db->cdp_query("INSERT INTO cdb_courier_track (order_id,order_track,comments,t_date,status_courier,user_id) VALUES (:oid,:track,'Bulk updated by Pryro AI',NOW(),:s,:uid)");
                $db->bind(':oid', $oid); $db->bind(':track', $ord->order_prefix.$ord->order_no);
                $db->bind(':s', $status_id); $db->bind(':uid', $userData->id); $db->cdp_execute();
            }
            $count++;
        }
        echo json_encode(['success' => true, 'message' => $count.' shipment(s) updated to status '.$status_id]);
        break;

    // ----------------------------------------------------------
    // BULK ASSIGN DRIVER
    // ----------------------------------------------------------
    case 'bulk_assign_driver':
        if (!$perms->canAssignDrivers()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied.']); exit;
        }
        $order_ids_raw = isset($data['order_ids']) ? $data['order_ids'] : '';
        $driver_id     = isset($data['driver_id']) ? (int)$data['driver_id'] : 0;
        if (!$order_ids_raw || !$driver_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing order_ids or driver_id']); exit;
        }
        $ids = array_filter(array_map('intval', explode(',', $order_ids_raw)));
        $count = 0;
        foreach ($ids as $oid) {
            $db->cdp_query("UPDATE cdb_add_order SET driver_id=:d WHERE order_id=:id");
            $db->bind(':d', $driver_id); $db->bind(':id', $oid); $db->cdp_execute(); $count++;
        }
        echo json_encode(['success' => true, 'message' => 'Driver #'.$driver_id.' assigned to '.$count.' shipment(s)']);
        break;

    // ----------------------------------------------------------
    // MARK PACKAGE DELIVERED (customers_packages)
    // ----------------------------------------------------------
    case 'mark_package_delivered':
        $package_id      = isset($data['package_id'])      ? (int)$data['package_id']      : 0;
        $person_receives = isset($data['person_receives']) ? trim($data['person_receives']) : '';
        $driver_id       = isset($data['driver_id'])       ? (int)$data['driver_id']       : 0;
        if (!$package_id || !$person_receives || !$driver_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing package_id, person_receives or driver_id']); exit;
        }
        $db->cdp_query("UPDATE cdb_customers_packages SET status_courier=8, person_receives=:pr WHERE order_id=:id");
        $db->bind(':pr', $person_receives); $db->bind(':id', $package_id); $db->cdp_execute();
        $db->cdp_query("SELECT order_prefix, order_no FROM cdb_customers_packages WHERE order_id=:id");
        $db->bind(':id', $package_id); $db->cdp_execute(); $pkg = $db->cdp_registro();
        if ($pkg) {
            $userData = $user->cdp_getUserData();
            $db->cdp_query("INSERT INTO cdb_courier_track (order_id,order_track,comments,t_date,status_courier,user_id) VALUES (:oid,:track,'Package delivered by Pryro AI',NOW(),8,:uid)");
            $db->bind(':oid', $package_id); $db->bind(':track', $pkg->order_prefix.$pkg->order_no);
            $db->bind(':uid', $userData->id); $db->cdp_execute();
        }
        echo json_encode(['success' => true, 'message' => 'Package #'.$package_id.' marked as Delivered. Received by: '.$person_receives]);
        break;

    // ----------------------------------------------------------
    // UPDATE CONSOLIDATE DRIVER
    // ----------------------------------------------------------
    case 'update_consolidate_driver':
        if (!$perms->canAssignDrivers()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied.']); exit;
        }
        $consolidate_id = isset($data['consolidate_id']) ? (int)$data['consolidate_id'] : 0;
        $driver_id      = isset($data['driver_id'])      ? (int)$data['driver_id']      : 0;
        if (!$consolidate_id || !$driver_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing consolidate_id or driver_id']); exit;
        }
        $db->cdp_query("UPDATE cdb_consolidate SET driver_id=:d WHERE c_id=:id");
        $db->bind(':d', $driver_id); $db->bind(':id', $consolidate_id); $db->cdp_execute();
        echo json_encode(['success' => true, 'message' => 'Driver #'.$driver_id.' assigned to consolidate order #'.$consolidate_id]);
        break;

    // ----------------------------------------------------------
    // CONFIRM CONSOLIDATE PAYMENT
    // ----------------------------------------------------------
    case 'confirm_consolidate_payment':
        if (!$perms->canConfirmPayments()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied.']); exit;
        }
        $consolidate_id = isset($data['consolidate_id']) ? (int)$data['consolidate_id'] : 0;
        if (!$consolidate_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing consolidate_id']); exit;
        }
        $db->cdp_query("UPDATE cdb_consolidate SET status_invoice=3 WHERE c_id=:id");
        $db->bind(':id', $consolidate_id); $db->cdp_execute();
        echo json_encode(['success' => true, 'message' => 'Payment confirmed for consolidate order #'.$consolidate_id]);
        break;

    // ----------------------------------------------------------
    // ACCEPT PICKUP
    // ----------------------------------------------------------
    case 'accept_pickup':
        $pickup_id = isset($data['pickup_id']) ? (int)$data['pickup_id'] : 0;
        $driver_id = isset($data['driver_id']) ? (int)$data['driver_id'] : 0;
        if (!$pickup_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing pickup_id']); exit;
        }
        $db->cdp_query("UPDATE cdb_add_order SET status_courier=3" . ($driver_id ? ", driver_id=:d" : "") . " WHERE order_id=:id AND is_pickup=1");
        if ($driver_id) $db->bind(':d', $driver_id);
        $db->bind(':id', $pickup_id); $db->cdp_execute();
        echo json_encode(['success' => true, 'message' => 'Pickup #'.$pickup_id.' accepted'.($driver_id ? ' and assigned to driver #'.$driver_id : '')]);
        break;

    // ----------------------------------------------------------
    // CANCEL PICKUP
    // ----------------------------------------------------------
    case 'cancel_pickup':
        $pickup_id = isset($data['pickup_id']) ? (int)$data['pickup_id'] : 0;
        $reason    = isset($data['reason'])    ? trim($data['reason'])    : 'Cancelled by Pryro AI';
        if (!$pickup_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing pickup_id']); exit;
        }
        $db->cdp_query("UPDATE cdb_add_order SET status_courier=21 WHERE order_id=:id AND is_pickup=1");
        $db->bind(':id', $pickup_id); $db->cdp_execute();
        echo json_encode(['success' => true, 'message' => 'Pickup #'.$pickup_id.' cancelled. Reason: '.$reason]);
        break;

    // ----------------------------------------------------------
    // DELETE CUSTOMER
    // ----------------------------------------------------------
    case 'delete_customer':
        $customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : 0;
        $confirm     = isset($data['confirm'])     ? trim($data['confirm'])     : '';
        if (!$customer_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing customer_id']); exit;
        }
        if (strtoupper($confirm) !== 'DELETE') {
            echo json_encode(['success' => false, 'message' => 'Type DELETE in the confirm field to proceed.']); exit;
        }
        // Check referential integrity
        $db->cdp_query("SELECT COUNT(*) as n FROM cdb_add_order WHERE sender_id=:id"); $db->bind(':id', $customer_id); $db->cdp_execute(); $r = $db->cdp_registro();
        if ($r && $r->n > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete: customer has '.$r->n.' shipment(s) on record.']); exit;
        }
        $db->cdp_query("UPDATE cdb_users SET active=0 WHERE id=:id AND userlevel=1");
        $db->bind(':id', $customer_id); $db->cdp_execute();
        echo json_encode(['success' => true, 'message' => 'Customer #'.$customer_id.' deactivated successfully.']);
        break;

    // ----------------------------------------------------------
    // RESET CUSTOMER PASSWORD
    // ----------------------------------------------------------
    case 'reset_customer_password':
        $customer_id  = isset($data['customer_id'])  ? (int)$data['customer_id']   : 0;
        $new_password = isset($data['new_password']) ? trim($data['new_password'])  : '';
        if (!$customer_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing customer_id']); exit;
        }
        $raw  = $new_password ?: bin2hex(random_bytes(5));
        $hash = password_hash($raw, PASSWORD_DEFAULT);
        $db->cdp_query("UPDATE cdb_users SET password=:p WHERE id=:id");
        $db->bind(':p', $hash); $db->bind(':id', $customer_id); $db->cdp_execute();
        $msg = $new_password ? 'Password reset for customer #'.$customer_id : 'Password auto-generated for customer #'.$customer_id.'. New password: '.$raw.' (share securely)';
        echo json_encode(['success' => true, 'message' => $msg]);
        break;

    // ----------------------------------------------------------
    // EDIT DRIVER
    // ----------------------------------------------------------
    case 'edit_driver':
        $driver_id = isset($data['driver_id']) ? (int)$data['driver_id'] : 0;
        if (!$driver_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing driver_id']); exit;
        }
        $updates = []; $binds = [':id' => $driver_id];
        if (!empty($data['fname']))   { $updates[] = 'fname=:fname';       $binds[':fname']   = trim($data['fname']); }
        if (!empty($data['lname']))   { $updates[] = 'lname=:lname';       $binds[':lname']   = trim($data['lname']); }
        if (!empty($data['phone']))   { $updates[] = 'phone=:phone';       $binds[':phone']   = trim($data['phone']); }
        if (!empty($data['vehicle'])) { $updates[] = 'vehiclecode=:vc';    $binds[':vc']      = trim($data['vehicle']); }
        if (empty($updates)) {
            echo json_encode(['success' => false, 'message' => 'No fields to update']); exit;
        }
        $db->cdp_query("UPDATE cdb_users SET ".implode(',', $updates)." WHERE id=:id AND userlevel=3");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        $db->cdp_execute();
        echo json_encode(['success' => true, 'message' => 'Driver #'.$driver_id.' updated successfully.']);
        break;

    // ----------------------------------------------------------
    // DELETE DRIVER
    // ----------------------------------------------------------
    case 'delete_driver':
        $driver_id = isset($data['driver_id']) ? (int)$data['driver_id'] : 0;
        $confirm   = isset($data['confirm'])   ? trim($data['confirm'])   : '';
        if (!$driver_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing driver_id']); exit;
        }
        if (strtoupper($confirm) !== 'DELETE') {
            echo json_encode(['success' => false, 'message' => 'Type DELETE in the confirm field to proceed.']); exit;
        }
        $db->cdp_query("SELECT COUNT(*) as n FROM cdb_add_order WHERE driver_id=:id AND status_courier NOT IN (8,21)"); $db->bind(':id', $driver_id); $db->cdp_execute(); $r = $db->cdp_registro();
        if ($r && $r->n > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete: driver has '.$r->n.' active shipment(s). Reassign them first.']); exit;
        }
        $db->cdp_query("UPDATE cdb_users SET active=0 WHERE id=:id AND userlevel=3");
        $db->bind(':id', $driver_id); $db->cdp_execute();
        echo json_encode(['success' => true, 'message' => 'Driver #'.$driver_id.' deactivated successfully.']);
        break;

    // ----------------------------------------------------------
    // REPORT: PAYMENTS RECEIVED
    // ----------------------------------------------------------
    case 'report_payments_received':
        $start      = isset($data['start_date'])  ? $data['start_date']       : date('Y-m-01');
        $end        = isset($data['end_date'])    ? $data['end_date']         : date('Y-m-t');
        $cust_id    = isset($data['customer_id']) ? (int)$data['customer_id'] : 0;
        $where      = "a.charge_date BETWEEN :start AND :end";
        $db->cdp_query("SELECT COUNT(*) as n, IFNULL(SUM(a.total),0) as total FROM cdb_charges_order a INNER JOIN cdb_add_order b ON a.order_id=b.order_id WHERE $where" . ($cust_id ? " AND b.sender_id=:cid" : ""));
        $db->bind(':start', $start); $db->bind(':end', $end);
        if ($cust_id) $db->bind(':cid', $cust_id);
        $db->cdp_execute(); $row = $db->cdp_registro();
        $count = $row ? (int)$row->n : 0; $total = $row ? (float)$row->total : 0;
        echo json_encode(['success' => true, 'message' => "Payments report ($start to $end): $count payment(s) totalling $total" . ($cust_id ? " for customer #$cust_id" : " across all customers")]);
        break;

    // ----------------------------------------------------------
    // REPORT: DRIVER PERFORMANCE
    // ----------------------------------------------------------
    case 'report_driver_performance':
        $start     = isset($data['start_date']) ? $data['start_date']       : date('Y-m-01');
        $end       = isset($data['end_date'])   ? $data['end_date']         : date('Y-m-t');
        $driver_id = isset($data['driver_id'])  ? (int)$data['driver_id']   : 0;
        $db->cdp_query("SELECT u.fname, u.lname, COUNT(o.order_id) as shipments, SUM(CASE WHEN o.status_courier=8 THEN 1 ELSE 0 END) as delivered, IFNULL(SUM(o.total_order),0) as revenue FROM cdb_add_order o LEFT JOIN cdb_users u ON u.id=o.driver_id WHERE o.order_date BETWEEN :start AND :end" . ($driver_id ? " AND o.driver_id=:did" : "") . " GROUP BY o.driver_id ORDER BY shipments DESC LIMIT 10");
        $db->bind(':start', $start); $db->bind(':end', $end);
        if ($driver_id) $db->bind(':did', $driver_id);
        $db->cdp_execute(); $rows = $db->cdp_registros();
        $summary = [];
        if ($rows) foreach ($rows as $r) $summary[] = trim($r->fname.' '.$r->lname).': '.$r->shipments.' shipments, '.$r->delivered.' delivered';
        $msg = count($summary) ? implode(' | ', $summary) : 'No data found for this period.';
        echo json_encode(['success' => true, 'message' => "Driver report ($start–$end): $msg"]);
        break;

    // ----------------------------------------------------------
    // REPORT: CUSTOMER BALANCE
    // ----------------------------------------------------------
    case 'report_customer_balance':
        $start   = isset($data['start_date'])  ? $data['start_date']       : date('Y-m-01');
        $end     = isset($data['end_date'])    ? $data['end_date']         : date('Y-m-t');
        $cust_id = isset($data['customer_id']) ? (int)$data['customer_id'] : 0;
        $db->cdp_query("SELECT u.fname, u.lname, IFNULL(SUM(o.total_order),0) as billed, IFNULL((SELECT SUM(c.total) FROM cdb_charges_order c WHERE c.order_id=o.order_id),0) as paid FROM cdb_add_order o LEFT JOIN cdb_users u ON u.id=o.sender_id WHERE o.order_date BETWEEN :start AND :end AND o.order_payment_method!=1" . ($cust_id ? " AND o.sender_id=:cid" : "") . " GROUP BY o.sender_id ORDER BY billed DESC LIMIT 20");
        $db->bind(':start', $start); $db->bind(':end', $end);
        if ($cust_id) $db->bind(':cid', $cust_id);
        $db->cdp_execute(); $rows = $db->cdp_registros();
        $summary = []; $total_balance = 0;
        if ($rows) foreach ($rows as $r) { $balance = $r->billed - $r->paid; $total_balance += $balance; if ($balance > 0) $summary[] = trim($r->fname.' '.$r->lname).': '.number_format($balance,2); }
        $msg = count($summary) ? 'Outstanding balances — '.implode(' | ', array_slice($summary,0,10)).(count($summary)>10?' + more':'').'. Total: '.number_format($total_balance,2) : 'No outstanding balances found.';
        echo json_encode(['success' => true, 'message' => $msg]);
        break;

    // ----------------------------------------------------------
    // NOTIFY SMS SHIPMENT
    // ----------------------------------------------------------
    case 'notify_sms_shipment':
        if (!$perms->canSendSMS()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: SMS not enabled.']); exit;
        }
        $order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
        $message  = isset($data['message'])  ? trim($data['message'])  : '';
        if (!$order_id || !$message) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing order_id or message']); exit;
        }
        $db->cdp_query("SELECT u.phone, u.fname, u.lname FROM cdb_add_order o LEFT JOIN cdb_users u ON u.id=o.sender_id WHERE o.order_id=:id");
        $db->bind(':id', $order_id); $db->cdp_execute(); $cust = $db->cdp_registro();
        if (!$cust || !$cust->phone) {
            echo json_encode(['success' => false, 'message' => 'Customer phone not found for order #'.$order_id]); exit;
        }
        // TODO: plug into existing sendNotificationSMS() — requires loader includes
        echo json_encode(['success' => true, 'message' => 'SMS queued for '.trim($cust->fname.' '.$cust->lname).' ('.$cust->phone.'): "'.$message.'" (integrate with SMS provider to send)']);
        break;

    // ----------------------------------------------------------
    // RECORD PAYMENT (Accounts Receivable)
    // ----------------------------------------------------------
    case 'record_payment':
        $order_id     = isset($data['order_id'])     ? (int)$data['order_id']     : 0;
        $amount       = isset($data['amount'])       ? (float)$data['amount']     : 0;
        $payment_type = isset($data['payment_type']) ? (int)$data['payment_type'] : 0;
        $notes        = isset($data['notes'])        ? trim($data['notes'])        : '';
        if (!$order_id || !$amount || !$payment_type) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing order_id, amount or payment_type']); exit;
        }
        $db->cdp_query("INSERT INTO cdb_charges_order (order_id, total, payment_type, notes, charge_date, user_id) VALUES (:oid,:total,:type,:notes,NOW(),:uid)");
        $db->bind(':oid',   $order_id);
        $db->bind(':total', $amount);
        $db->bind(':type',  $payment_type);
        $db->bind(':notes', $notes);
        $userData = $user->cdp_getUserData();
        $db->bind(':uid', $userData->id);
        $db->cdp_execute();
        $new_id = $db->dbh->lastInsertId();
        if ($new_id) {
            // Update invoice status to paid
            $db->cdp_query("UPDATE cdb_add_order SET status_invoice=1 WHERE order_id=:id");
            $db->bind(':id', $order_id); $db->cdp_execute();
        }
        echo json_encode(['success' => (bool)$new_id, 'message' => $new_id ? 'Payment of '.$amount.' recorded for order #'.$order_id : 'Failed to record payment.']);
        break;

    // ----------------------------------------------------------
    // DELETE CHARGE (Accounts Receivable)
    // ----------------------------------------------------------
    case 'delete_charge':
        $charge_id = isset($data['charge_id']) ? (int)$data['charge_id'] : 0;
        if (!$charge_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing charge_id']); exit;
        }
        $db->cdp_query("DELETE FROM cdb_charges_order WHERE id_charge=:id");
        $db->bind(':id', $charge_id); $db->cdp_execute();
        $affected = $db->cdp_rowCount();
        echo json_encode(['success' => (bool)$affected, 'message' => $affected ? 'Charge #'.$charge_id.' deleted.' : 'Charge not found.']);
        break;

    // ----------------------------------------------------------
    // CREATE RECIPIENT
    // ----------------------------------------------------------
    case 'create_recipient':
        $fname   = isset($data['fname'])   ? trim($data['fname'])   : '';
        $lname   = isset($data['lname'])   ? trim($data['lname'])   : '';
        $phone   = isset($data['phone'])   ? trim($data['phone'])   : '';
        $email   = isset($data['email'])   ? trim($data['email'])   : '';
        $address = isset($data['address']) ? trim($data['address']) : '';
        if (!$fname || !$lname || !$phone) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing fname, lname or phone']); exit;
        }
        $userData = $user->cdp_getUserData();
        $db->cdp_query("INSERT INTO cdb_recipients (fname, lname, phone, email, sender_id, created) VALUES (:fname,:lname,:phone,:email,:sid,NOW())");
        $db->bind(':fname', $fname); $db->bind(':lname', $lname);
        $db->bind(':phone', $phone); $db->bind(':email', $email);
        $db->bind(':sid',   $userData->id);
        $db->cdp_execute();
        $new_id = $db->dbh->lastInsertId();
        if ($new_id && $address) {
            $db->cdp_query("INSERT INTO cdb_recipients_addresses (recipient_id, address, created) VALUES (:rid,:addr,NOW())");
            $db->bind(':rid', $new_id); $db->bind(':addr', $address); $db->cdp_execute();
        }
        echo json_encode(['success' => (bool)$new_id, 'message' => $new_id ? 'Recipient "'.trim($fname.' '.$lname).'" created (ID: '.$new_id.')' : 'Failed to create recipient.']);
        break;

    // ----------------------------------------------------------
    // DELETE RECIPIENT
    // ----------------------------------------------------------
    case 'delete_recipient':
        $recipient_id = isset($data['recipient_id']) ? (int)$data['recipient_id'] : 0;
        $confirm      = isset($data['confirm'])      ? trim($data['confirm'])      : '';
        if (!$recipient_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing recipient_id']); exit;
        }
        if (strtoupper($confirm) !== 'DELETE') {
            echo json_encode(['success' => false, 'message' => 'Type DELETE in the confirm field.']); exit;
        }
        $db->cdp_query("DELETE FROM cdb_recipients WHERE id=:id"); $db->bind(':id', $recipient_id); $db->cdp_execute();
        $affected = $db->cdp_rowCount();
        echo json_encode(['success' => (bool)$affected, 'message' => $affected ? 'Recipient #'.$recipient_id.' deleted.' : 'Recipient not found.']);
        break;

    // ----------------------------------------------------------
    // CREATE EMPLOYEE (userlevel=2)
    // ----------------------------------------------------------
    case 'create_employee':
        $fname    = isset($data['fname'])    ? trim($data['fname'])    : '';
        $lname    = isset($data['lname'])    ? trim($data['lname'])    : '';
        $email    = isset($data['email'])    ? trim($data['email'])    : '';
        $phone    = isset($data['phone'])    ? trim($data['phone'])    : '';
        $username = isset($data['username']) ? trim($data['username']) : '';
        if (!$fname || !$email || !$username) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing fname, email or username']); exit;
        }
        $db->cdp_query("SELECT id FROM cdb_users WHERE email=:e OR username=:u LIMIT 1");
        $db->bind(':e', $email); $db->bind(':u', $username); $db->cdp_execute();
        if ($db->cdp_registro()) {
            echo json_encode(['success' => false, 'message' => 'Email or username already in use.']); exit;
        }
        $raw  = bin2hex(random_bytes(5));
        $hash = password_hash($raw, PASSWORD_DEFAULT);
        $db->cdp_query("INSERT INTO cdb_users (username,password,userlevel,email,fname,lname,phone,created,active) VALUES (:usr,:pass,2,:email,:fname,:lname,:phone,NOW(),1)");
        $db->bind(':usr',   $username); $db->bind(':pass',  $hash);
        $db->bind(':email', $email);    $db->bind(':fname', $fname);
        $db->bind(':lname', $lname);    $db->bind(':phone', $phone);
        $db->cdp_execute();
        $new_id = $db->dbh->lastInsertId();
        echo json_encode(['success' => (bool)$new_id, 'message' => $new_id ? 'Employee "'.trim($fname.' '.$lname).'" created (ID: '.$new_id.'). Temp password: '.$raw : 'Failed to create employee.']);
        break;

    // ----------------------------------------------------------
    // DELETE EMPLOYEE
    // ----------------------------------------------------------
    case 'delete_employee':
        $employee_id = isset($data['employee_id']) ? (int)$data['employee_id'] : 0;
        $confirm     = isset($data['confirm'])     ? trim($data['confirm'])     : '';
        if (!$employee_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing employee_id']); exit;
        }
        if (strtoupper($confirm) !== 'DELETE') {
            echo json_encode(['success' => false, 'message' => 'Type DELETE in the confirm field.']); exit;
        }
        $db->cdp_query("UPDATE cdb_users SET active=0 WHERE id=:id AND userlevel=2");
        $db->bind(':id', $employee_id); $db->cdp_execute();
        echo json_encode(['success' => true, 'message' => 'Employee #'.$employee_id.' deactivated.']);
        break;

    // ----------------------------------------------------------
    // RESET EMPLOYEE PASSWORD
    // ----------------------------------------------------------
    case 'reset_employee_password':
        $employee_id  = isset($data['employee_id'])  ? (int)$data['employee_id']  : 0;
        $new_password = isset($data['new_password']) ? trim($data['new_password']) : '';
        if (!$employee_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing employee_id']); exit;
        }
        $raw  = $new_password ?: bin2hex(random_bytes(5));
        $hash = password_hash($raw, PASSWORD_DEFAULT);
        $db->cdp_query("UPDATE cdb_users SET password=:p WHERE id=:id AND userlevel=2");
        $db->bind(':p', $hash); $db->bind(':id', $employee_id); $db->cdp_execute();
        $msg = $new_password ? 'Password updated for employee #'.$employee_id : 'Auto-generated password for employee #'.$employee_id.': '.$raw.' (share securely)';
        echo json_encode(['success' => true, 'message' => $msg]);
        break;

    // ----------------------------------------------------------
    // SEND BULK WHATSAPP
    // ----------------------------------------------------------
    case 'send_whatsapp_bulk':
        if (!$perms->canSendWhatsApp()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied: WhatsApp not enabled.']); exit;
        }
        $filter  = isset($data['filter'])  ? trim($data['filter'])  : 'all';
        $message = isset($data['message']) ? trim($data['message']) : '';
        if (!$message) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing message']); exit;
        }
        if ($filter === 'all') {
            $db->cdp_query("SELECT COUNT(*) as n FROM cdb_users WHERE userlevel=1 AND active=1 AND phone!='' AND phone IS NOT NULL");
        } else {
            $db->cdp_query("SELECT COUNT(*) as n FROM cdb_users WHERE id=:id"); $db->bind(':id', (int)$filter);
        }
        $db->cdp_execute(); $cnt = $db->cdp_registro();
        $count = $cnt ? (int)$cnt->n : 0;
        // TODO: integrate with WhatsApp API provider
        echo json_encode(['success' => true, 'message' => 'Bulk WhatsApp queued for '.$count.' recipient(s). Message: "'.substr($message,0,60).(strlen($message)>60?'...':'"').' (integrate WhatsApp API to send)']);
        break;

    // ----------------------------------------------------------
    // DELETE PRE-ALERT
    // ----------------------------------------------------------
    case 'delete_prealert':
        $prealert_id = isset($data['prealert_id']) ? (int)$data['prealert_id'] : 0;
        if (!$prealert_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing prealert_id']); exit;
        }
        $db->cdp_query("DELETE FROM cdb_prealert WHERE id=:id"); $db->bind(':id', $prealert_id); $db->cdp_execute();
        $affected = $db->cdp_rowCount();
        echo json_encode(['success' => (bool)$affected, 'message' => $affected ? 'Pre-alert #'.$prealert_id.' deleted.' : 'Pre-alert not found.']);
        break;

    // ----------------------------------------------------------
    // UPDATE CONSOLIDATE STATUS
    // ----------------------------------------------------------
    case 'update_consolidate_status':
        if (!$perms->canUpdateStatus()) {
            echo json_encode(['success' => false, 'message' => 'Permission denied.']); exit;
        }
        $consolidate_id = isset($data['consolidate_id']) ? (int)$data['consolidate_id'] : 0;
        $status_id      = isset($data['status_id'])      ? (int)$data['status_id']      : 0;
        $comment        = isset($data['comment'])        ? trim($data['comment'])        : 'Status updated by Pryro AI';
        if (!$consolidate_id || !$status_id) {
            echo json_encode(['success' => false, 'needs_input' => true, 'message' => 'Missing consolidate_id or status_id']); exit;
        }
        $db->cdp_query("UPDATE cdb_consolidate SET status_courier=:s WHERE c_id=:id");
        $db->bind(':s', $status_id); $db->bind(':id', $consolidate_id); $db->cdp_execute();
        echo json_encode(['success' => true, 'message' => 'Consolidate #'.$consolidate_id.' status updated to '.$status_id.'. '.$comment]);
        break;

    // ----------------------------------------------------------
    // REPORT: GENERAL SHIPMENTS
    // ----------------------------------------------------------
    case 'report_general':
        $start     = isset($data['start_date']) ? $data['start_date']       : date('Y-m-01');
        $end       = isset($data['end_date'])   ? $data['end_date']         : date('Y-m-t');
        $status_id = isset($data['status_id'])  ? (int)$data['status_id']   : 0;
        $where = "o.order_date BETWEEN :start AND :end AND o.status_courier!=21";
        if ($status_id) $where .= " AND o.status_courier=:s";
        $db->cdp_query("SELECT COUNT(*) as total, IFNULL(SUM(o.total_order),0) as revenue, SUM(CASE WHEN o.status_courier=8 THEN 1 ELSE 0 END) as delivered FROM cdb_add_order o WHERE $where");
        $db->bind(':start', $start); $db->bind(':end', $end);
        if ($status_id) $db->bind(':s', $status_id);
        $db->cdp_execute(); $row = $db->cdp_registro();
        $total = $row ? (int)$row->total : 0; $revenue = $row ? (float)$row->revenue : 0; $delivered = $row ? (int)$row->delivered : 0;
        echo json_encode(['success' => true, 'message' => "General report ($start to $end): $total shipments, $delivered delivered, revenue: $revenue"]);
        break;

    // ----------------------------------------------------------
    // REPORT: PICKUP SUMMARY
    // ----------------------------------------------------------
    case 'report_pickup_summary':
        $start     = isset($data['start_date']) ? $data['start_date']     : date('Y-m-01');
        $end       = isset($data['end_date'])   ? $data['end_date']       : date('Y-m-t');
        $driver_id = isset($data['driver_id'])  ? (int)$data['driver_id'] : 0;
        $db->cdp_query("SELECT COUNT(*) as total, SUM(CASE WHEN o.status_courier=8 THEN 1 ELSE 0 END) as done, SUM(CASE WHEN o.status_courier=21 THEN 1 ELSE 0 END) as cancelled FROM cdb_add_order o WHERE o.is_pickup=1 AND o.order_date BETWEEN :start AND :end" . ($driver_id ? " AND o.driver_id=:did" : ""));
        $db->bind(':start', $start); $db->bind(':end', $end);
        if ($driver_id) $db->bind(':did', $driver_id);
        $db->cdp_execute(); $row = $db->cdp_registro();
        $total = $row?(int)$row->total:0; $done = $row?(int)$row->done:0; $cancelled = $row?(int)$row->cancelled:0;
        echo json_encode(['success' => true, 'message' => "Pickup report ($start to $end): $total pickups — $done completed, $cancelled cancelled".($driver_id?" for driver #$driver_id":'')]);
        break;

    // ----------------------------------------------------------
    // REPORT: CUSTOMER PACKAGES REGISTERED
    // ----------------------------------------------------------
    case 'report_packages_registered':
        $start   = isset($data['start_date'])  ? $data['start_date']       : date('Y-m-01');
        $end     = isset($data['end_date'])    ? $data['end_date']         : date('Y-m-t');
        $cust_id = isset($data['customer_id']) ? (int)$data['customer_id'] : 0;
        $db->cdp_query("SELECT COUNT(*) as total, IFNULL(SUM(p.total_order),0) as revenue, SUM(CASE WHEN p.status_courier=8 THEN 1 ELSE 0 END) as delivered FROM cdb_customers_packages p WHERE p.order_date BETWEEN :start AND :end" . ($cust_id ? " AND p.sender_id=:cid" : ""));
        $db->bind(':start', $start); $db->bind(':end', $end);
        if ($cust_id) $db->bind(':cid', $cust_id);
        $db->cdp_execute(); $row = $db->cdp_registro();
        $total = $row?(int)$row->total:0; $revenue = $row?(float)$row->revenue:0; $delivered = $row?(int)$row->delivered:0;
        echo json_encode(['success' => true, 'message' => "Packages report ($start to $end): $total packages, $delivered delivered, revenue: $revenue".($cust_id?" for customer #$cust_id":'')]);
        break;

    default:
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Unknown action: ' . htmlspecialchars($action)]);
}
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}