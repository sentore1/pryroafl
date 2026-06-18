<?php
// *************************************************************************
// *                                                                       *
// * WhatsApp Direct Link - Get Single Shipment Data                       *
// *                                                                       *
// *************************************************************************

require_once("../../loader.php");

$db = new Conexion;
$user = new User;
$core = new Core;

header('Content-Type: application/json');

// Check if user is logged in
if (!$user->cdp_loginCheck()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$recipient_type = isset($_POST['recipient_type']) ? $_POST['recipient_type'] : 'sender'; // 'sender' or 'receiver'

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

// Get shipment data
$db->cdp_query("SELECT * FROM cdb_add_order WHERE order_id = :order_id");
$db->bind(':order_id', $order_id);
$db->cdp_execute();
$shipment = $db->cdp_registro();

if (!$shipment) {
    echo json_encode(['success' => false, 'message' => 'Shipment not found']);
    exit;
}

// Get recipient data based on type
if ($recipient_type === 'receiver') {
    $db->cdp_query("SELECT * FROM cdb_recipients WHERE id = :recipient_id");
    $db->bind(':recipient_id', $shipment->receiver_id);
    $db->cdp_execute();
    $recipient = $db->cdp_registro();
} else {
    $db->cdp_query("SELECT * FROM cdb_users WHERE id = :sender_id");
    $db->bind(':sender_id', $shipment->sender_id);
    $db->cdp_execute();
    $recipient = $db->cdp_registro();
}

if (!$recipient) {
    echo json_encode(['success' => false, 'message' => 'Recipient not found']);
    exit;
}

// Check if recipient has phone number
if (empty($recipient->phone)) {
    echo json_encode(['success' => false, 'message' => 'No phone number available for this recipient']);
    exit;
}

// Get shipment status
$db->cdp_query("SELECT * FROM cdb_styles WHERE id = :status_id");
$db->bind(':status_id', $shipment->status_courier);
$db->cdp_execute();
$status = $db->cdp_registro();

// Get settings
$settings = cdp_getSettingsCourier();

// Build tracking number
$tracking_number = $shipment->order_prefix . $shipment->order_no;

// Build customer name
$customer_name = $recipient->fname . ' ' . $recipient->lname;

// Build message
$message = "Hello " . $customer_name . ",\n\n";
$message .= "Your shipment tracking number is: *" . $tracking_number . "*\n\n";
if ($status) {
    $message .= "Status: " . $status->mod_style . "\n\n";
}
$message .= "Track your shipment here:\n";
$message .= $settings->site_url . "/track.php?tracking_id=" . $tracking_number . "\n\n";
$message .= "Best regards,\n" . $settings->site_name;

// Return data
echo json_encode([
    'success' => true,
    'phone' => $recipient->phone,
    'message' => $message,
    'tracking_number' => $tracking_number,
    'customer_name' => $customer_name,
    'company_name' => $settings->site_name,
    'site_url' => $settings->site_url
]);
exit;
