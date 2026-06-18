<?php
/**
 * Update AI Permission - Toggle a single AI permission on/off
 */
header('Content-Type: application/json');
require_once("../../loader.php");

$user = new User;
if (!$user->cdp_is_Admin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$permission = isset($_POST['permission']) ? trim($_POST['permission']) : '';
$enabled    = isset($_POST['enabled'])    ? (int)$_POST['enabled']    : 0;
$value      = isset($_POST['value'])      ? (int)$_POST['value']      : 0;

if (empty($permission)) {
    echo json_encode(['success' => false, 'message' => 'Missing permission parameter']);
    exit;
}

$db = new Conexion;

// Map permission keys to database columns
$permission_map = [
    // Autopilot
    'autopilot_enabled'              => 'ai_autopilot_enabled',
    'autopilot_threshold'            => 'ai_autopilot_threshold',
    
    // Core Actions
    'actions_assign_drivers'         => 'ai_can_assign_drivers',
    'actions_confirm_payments'       => 'ai_can_confirm_payments',
    'actions_update_status'          => 'ai_can_update_status',
    'actions_create_shipments'       => 'ai_can_create_shipments',
    'actions_edit_shipments'         => 'ai_can_edit_shipments',
    'actions_cancel_shipments'       => 'ai_can_cancel_shipments',
    
    // Communication
    'communication_send_sms'         => 'ai_can_send_sms',
    'communication_send_email'       => 'ai_can_send_email',
    'communication_send_whatsapp'    => 'ai_can_send_whatsapp',
    
    // Customer Management
    'customer_management_create_customers' => 'ai_can_create_customers',
    'customer_management_edit_customers'   => 'ai_can_edit_customers',
    
    // Financial
    'financial_process_refunds'      => 'ai_can_process_refunds',
    'financial_apply_discounts'      => 'ai_can_apply_discounts',
    
    // Reporting
    'reporting_generate_reports'     => 'ai_can_generate_reports',
    'reporting_export_data'          => 'ai_can_export_data',
    
    // Advanced
    'advanced_predict_analytics'     => 'ai_can_predict_analytics',
    'advanced_optimize_routes'       => 'ai_can_optimize_routes',
    
    // Read Permissions
    'read_customers'                 => 'ai_can_read_customers',
    'read_packages'                  => 'ai_can_read_packages',
    'read_financials'                => 'ai_can_read_financials',
    'read_drivers'                   => 'ai_can_read_drivers',
    'read_inventory'                 => 'ai_can_read_inventory',
];

// Check if permission exists
if (!isset($permission_map[$permission])) {
    echo json_encode(['success' => false, 'message' => 'Unknown permission: ' . $permission]);
    exit;
}

$db_column = $permission_map[$permission];

// Ensure the column exists
try {
    // For threshold, use the value parameter
    if ($permission === 'autopilot_threshold') {
        $db->cdp_query("UPDATE cdb_settings SET {$db_column} = :value LIMIT 1");
        $db->bind(':value', $value);
    } else {
        $db->cdp_query("UPDATE cdb_settings SET {$db_column} = :enabled LIMIT 1");
        $db->bind(':enabled', $enabled);
    }
    
    $db->cdp_execute();
    
    // Check if update was successful
    if ($db->cdp_rowCount() >= 0) {
        // Get human-readable permission name
        $perm_name = str_replace('_', ' ', $permission);
        $perm_name = ucwords($perm_name);
        
        if ($permission === 'autopilot_threshold') {
            $message = 'Autopilot threshold updated to ' . $value . ' items';
        } else {
            $status = $enabled ? 'enabled' : 'disabled';
            $message = $perm_name . ' ' . $status . ' successfully';
        }
        
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No changes made (setting may already be at this value)'
        ]);
    }
} catch (Exception $e) {
    // Check if column doesn't exist and try to create it
    try {
        $column_type = ($permission === 'autopilot_threshold') ? 'INT(11) DEFAULT 5' : 'TINYINT(1) DEFAULT 0';
        $db->cdp_query("ALTER TABLE cdb_settings ADD COLUMN {$db_column} {$column_type}");
        $db->cdp_execute();
        
        // Retry the update
        if ($permission === 'autopilot_threshold') {
            $db->cdp_query("UPDATE cdb_settings SET {$db_column} = :value LIMIT 1");
            $db->bind(':value', $value);
        } else {
            $db->cdp_query("UPDATE cdb_settings SET {$db_column} = :enabled LIMIT 1");
            $db->bind(':enabled', $enabled);
        }
        $db->cdp_execute();
        
        $perm_name = str_replace('_', ' ', $permission);
        $perm_name = ucwords($perm_name);
        
        if ($permission === 'autopilot_threshold') {
            $message = 'Autopilot threshold set to ' . $value . ' items';
        } else {
            $status = $enabled ? 'enabled' : 'disabled';
            $message = $perm_name . ' ' . $status . ' (column created)';
        }
        
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } catch (Exception $e2) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e2->getMessage()
        ]);
    }
}
?>
