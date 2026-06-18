<?php
// =============================================================
// AI ACTION EXECUTOR — performs actions on behalf of Pryro AI
// =============================================================
header('Content-Type: application/json');
require_once("../../loader.php");
require_once("ai_permissions_helper.php");

$user = new User;
if (!$user->cdp_is_Admin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action  = isset($_POST['action'])  ? trim($_POST['action'])  : '';
$payload = isset($_POST['payload']) ? $_POST['payload']       : '{}';
$data    = json_decode($payload, true) ?: [];

$db = new Conexion;
$perms = new AIPermissions();

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
            echo json_encode(['success' => false, 'message' => 'Missing required fields (name, email)']);
            exit;
        }
        
        // TODO: Implement customer creation logic
        
        echo json_encode(['success' => true, 'message' => 'Customer created successfully (Demo mode - implement customer creation)']);
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

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action: ' . htmlspecialchars($action)]);
}
