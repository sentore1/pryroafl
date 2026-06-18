<?php
// *************************************************************************
// *                                                                       *
// * WhatsApp Direct Link - Get Bulk Shipments Data (Receivers)            *
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

$order_ids = isset($_POST['order_ids']) ? json_decode($_POST['order_ids'], true) : [];

if (empty($order_ids) || !is_array($order_ids)) {
    echo json_encode(['success' => false, 'message' => 'No order IDs provided']);
    exit;
}

// Limit to prevent abuse
if (count($order_ids) > 50) {
    echo json_encode(['success' => false, 'message' => 'Maximum 50 shipments at a time']);
    exit;
}

// Get settings
$settings = cdp_getSettingsCourier();

$shipments_data = [];

foreach ($order_ids as $order_id) {
    // Get shipment by order_id
    $db->cdp_query("SELECT * FROM cdb_add_order WHERE order_id = :order_id");
    $db->bind(':order_id', $order_id);
    $db->cdp_execute();
    $shipment = $db->cdp_registro();
    
    if (!$shipment) {
        continue;
    }
    
    // Get receiver data
    $db->cdp_query("SELECT * FROM cdb_recipients WHERE id = :receiver_id");
    $db->bind(':receiver_id', $shipment->receiver_id);
    $db->cdp_execute();
    $receiver = $db->cdp_registro();
    
    if (!$receiver || empty($receiver->phone)) {
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
    $customer_name = $receiver->fname . ' ' . $receiver->lname;
    
    // Build message
    $message = "Hello " . $customer_name . ",\n\n";
    $message .= "You have a shipment coming to you!\n\n";
    $message .= "Tracking Number: *" . $tracking_number . "*\n\n";
    if ($status) {
        $message .= "Status: " . $status->mod_style . "\n\n";
    }
    $message .= "Track your shipment here:\n";
    $message .= $settings->site_url . "/track.php?tracking_id=" . $tracking_number . "\n\n";
    $message .= "Best regards,\n" . $settings->site_name;
    
    // Add to array
    $shipments_data[] = [
        'orderNo' => $shipment->order_no,
        'phone' => $receiver->phone,
        'trackingNumber' => $tracking_number,
        'customerName' => $customer_name,
        'companyName' => $settings->site_name,
        'siteUrl' => $settings->site_url,
        'customMessage' => $message,
        'status' => $status ? $status->mod_style : ''
    ];
}

if (empty($shipments_data)) {
    echo json_encode(['success' => false, 'message' => 'No valid shipments found with receiver phone numbers']);
    exit;
}

echo json_encode([
    'success' => true,
    'shipments' => $shipments_data,
    'count' => count($shipments_data)
]);
exit;
