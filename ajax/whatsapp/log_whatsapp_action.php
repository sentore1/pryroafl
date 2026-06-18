<?php
// *************************************************************************
// *                                                                       *
// * WhatsApp Direct Link - Log Action to Database                         *
// *                                                                       *
// *************************************************************************

require_once("../../loader.php");

$db = new Conexion;
$user = new User;

header('Content-Type: application/json');

// Check if user is logged in
if (!$user->cdp_loginCheck()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$recipient_type = isset($_POST['recipient_type']) ? $_POST['recipient_type'] : 'sender';
$action_type = isset($_POST['action_type']) ? $_POST['action_type'] : 'direct_link';

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

// Log to database (you may need to create this table)
$sql = "INSERT INTO cdb_whatsapp_logs 
        (order_id, recipient_type, action_type, user_id, created_at) 
        VALUES 
        (:order_id, :recipient_type, :action_type, :user_id, NOW())";

try {
    $db->cdp_query($sql);
    $db->bind(':order_id', $order_id);
    $db->bind(':recipient_type', $recipient_type);
    $db->bind(':action_type', $action_type);
    $db->bind(':user_id', $_SESSION['userid']);
    $db->cdp_execute();
    
    echo json_encode(['success' => true, 'message' => 'Action logged']);
} catch (Exception $e) {
    // Table might not exist yet, create it
    createWhatsAppLogTable($db);
    
    // Try again
    $db->cdp_query($sql);
    $db->bind(':order_id', $order_id);
    $db->bind(':recipient_type', $recipient_type);
    $db->bind(':action_type', $action_type);
    $db->bind(':user_id', $_SESSION['userid']);
    $db->cdp_execute();
    
    echo json_encode(['success' => true, 'message' => 'Action logged (table created)']);
}

exit;

/**
 * Create WhatsApp logs table if it doesn't exist
 */
function createWhatsAppLogTable($db) {
    $sql = "CREATE TABLE IF NOT EXISTS cdb_whatsapp_logs (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        order_id INT(11) NOT NULL,
        recipient_type VARCHAR(20) NOT NULL,
        action_type VARCHAR(50) NOT NULL,
        user_id INT(11) NOT NULL,
        created_at DATETIME NOT NULL,
        INDEX idx_order_id (order_id),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $db->cdp_query($sql);
    $db->cdp_execute();
}
