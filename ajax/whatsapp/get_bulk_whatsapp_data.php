<?php
// *************************************************************************
// *                                                                       *
// * WhatsApp Direct Link - Get Bulk Shipments Data                        *
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

$order_numbers = isset($_POST['order_numbers']) ? json_decode($_POST['order_numbers'], true) : [];

if (empty($order_numbers) || !is_array($order_numbers)) {
    echo json_encode(['success' => false, 'message' => 'No order numbers provided']);
    exit;
}

// Limit to prevent abuse
if (count($order_numbers) > 50) {
    echo json_encode(['success' => false, 'message' => 'Maximum 50 shipments at a time']);
    exit;
}

// Get settings
$settings = cdp_getSettingsCourier();

$shipments_data = [];

foreach ($order_numbers as $order_no) {
    // Get shipment by order_no
    $db->cdp_query("SELECT * FROM cdb_add_order WHERE order_no = :order_no");
    $db->bind(':order_no', $order_no);
    $db->cdp_execute();
    $shipment = $db->cdp_registro();
    
    if (!$shipment) {
        continue;
    }
    
    // Get sender data (primary recipient for notifications)
    $db->cdp_query("SELECT * FROM cdb_users WHERE id = :sender_id");
    $db->bind(':sender_id', $shipment->sender_id);
    $db->cdp_execute();
    $sender = $db->cdp_registro();
    
    if (!$sender || empty($sender->phone)) {
        continue;
    }
    
    // Get status
    $db->cdp_query("SELECT * FROM cdb_styles WHERE id = :status_id");
    $db->bind(':status_id', $shipment->status_courier);
    $db->cdp_execute();
    $status = $db->cdp_registro();
    
    // Build tracking number
    $tracking_number = $shipment->order_prefix . $shipment->order_no;
    
    // Build customer name
    $customer_name = $sender->fname . ' ' . $sender->lname;
    
    // Build message
    $message = "Hello " . $customer_name . ",\n\n";
    $message .= "Your shipment tracking number is: *" . $tracking_number . "*\n\n";
    if ($status) {
        $message .= "Status: " . $status->mod_style . "\n\n";
    }
    $message .= "Track your shipment here:\n";
    $message .= $settings->site_url . "/track.php?tracking_id=" . $tracking_number . "\n\n";
    $message .= "Best regards,\n" . $settings->site_name;
    
    // Add to array
    $shipments_data[] = [
        'orderNo' => $order_no,
        'phone' => $sender->phone,
        'trackingNumber' => $tracking_number,
        'customerName' => $customer_name,
        'companyName' => $settings->site_name,
        'siteUrl' => $settings->site_url,
        'customMessage' => $message,
        'status' => $status ? $status->mod_style : ''
    ];
}

if (empty($shipments_data)) {
    echo json_encode(['success' => false, 'message' => 'No valid shipments found with phone numbers']);
    exit;
}

echo json_encode([
    'success' => true,
    'shipments' => $shipments_data,
    'count' => count($shipments_data)
]);
exit;
