<?php
// =============================================================
// SAVE AI CONFIG — stores keys & permissions in cdb_settings (DB)
// =============================================================

require_once("../../loader.php");

header('Content-Type: application/json');

$user = new User;
if (!$user->cdp_is_Admin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get API keys and provider
$groq_key   = isset($_POST['groq_api_key'])   ? trim($_POST['groq_api_key'])   : '';
$openai_key = isset($_POST['openai_api_key']) ? trim($_POST['openai_api_key']) : '';
$provider   = isset($_POST['ai_provider'])    ? trim($_POST['ai_provider'])    : 'groq';

// Get autopilot settings
$autopilot_enabled   = isset($_POST['ai_autopilot_enabled'])   ? (int)$_POST['ai_autopilot_enabled']   : 0;
$autopilot_threshold = isset($_POST['ai_autopilot_threshold']) ? (int)$_POST['ai_autopilot_threshold'] : 5;

// Get read permissions
$read_customers  = isset($_POST['ai_can_read_customers'])  ? (int)$_POST['ai_can_read_customers']  : 0;
$read_packages   = isset($_POST['ai_can_read_packages'])   ? (int)$_POST['ai_can_read_packages']   : 0;
$read_financials = isset($_POST['ai_can_read_financials']) ? (int)$_POST['ai_can_read_financials'] : 0;
$read_drivers    = isset($_POST['ai_can_read_drivers'])    ? (int)$_POST['ai_can_read_drivers']    : 0;
$read_inventory  = isset($_POST['ai_can_read_inventory'])  ? (int)$_POST['ai_can_read_inventory']  : 0;

// Get action permissions
$assign_drivers    = isset($_POST['ai_can_assign_drivers'])    ? (int)$_POST['ai_can_assign_drivers']    : 0;
$confirm_payments  = isset($_POST['ai_can_confirm_payments'])  ? (int)$_POST['ai_can_confirm_payments']  : 0;
$update_status     = isset($_POST['ai_can_update_status'])     ? (int)$_POST['ai_can_update_status']     : 0;
$create_shipments  = isset($_POST['ai_can_create_shipments'])  ? (int)$_POST['ai_can_create_shipments']  : 0;
$edit_shipments    = isset($_POST['ai_can_edit_shipments'])    ? (int)$_POST['ai_can_edit_shipments']    : 0;
$cancel_shipments  = isset($_POST['ai_can_cancel_shipments'])  ? (int)$_POST['ai_can_cancel_shipments']  : 0;

// Get communication permissions
$send_sms      = isset($_POST['ai_can_send_sms'])      ? (int)$_POST['ai_can_send_sms']      : 0;
$send_email    = isset($_POST['ai_can_send_email'])    ? (int)$_POST['ai_can_send_email']    : 0;
$send_whatsapp = isset($_POST['ai_can_send_whatsapp']) ? (int)$_POST['ai_can_send_whatsapp'] : 0;

// Get reporting permissions
$generate_reports = isset($_POST['ai_can_generate_reports']) ? (int)$_POST['ai_can_generate_reports'] : 0;
$export_data      = isset($_POST['ai_can_export_data'])      ? (int)$_POST['ai_can_export_data']      : 0;

// Get customer management permissions
$create_customers = isset($_POST['ai_can_create_customers']) ? (int)$_POST['ai_can_create_customers'] : 0;
$edit_customers   = isset($_POST['ai_can_edit_customers'])   ? (int)$_POST['ai_can_edit_customers']   : 0;

// Get financial operations permissions
$process_refunds  = isset($_POST['ai_can_process_refunds'])  ? (int)$_POST['ai_can_process_refunds']  : 0;
$apply_discounts  = isset($_POST['ai_can_apply_discounts'])  ? (int)$_POST['ai_can_apply_discounts']  : 0;

// Get advanced features permissions
$predict_analytics = isset($_POST['ai_can_predict_analytics']) ? (int)$_POST['ai_can_predict_analytics'] : 0;
$optimize_routes   = isset($_POST['ai_can_optimize_routes'])   ? (int)$_POST['ai_can_optimize_routes']   : 0;

// Sanitize API keys - allow alphanumeric, dashes, underscores, dots
$groq_key   = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $groq_key);
$openai_key = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $openai_key);
$provider   = in_array($provider, ['groq', 'openai']) ? $provider : 'groq';

// Use raw PDO to handle column creation safely
require_once("../../config/config.php");

try {
    $pdo = new PDO(
        'mysql:host=' . CDP_DB_HOST . ';dbname=' . CDP_DB_NAME . ';charset=utf8',
        CDP_DB_USER,
        CDP_DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Add columns one by one — catches error if column already exists
    $columns = [
        "ALTER TABLE cdb_settings ADD COLUMN ai_provider VARCHAR(20) NOT NULL DEFAULT 'groq'",
        "ALTER TABLE cdb_settings ADD COLUMN groq_api_key VARCHAR(255) NOT NULL DEFAULT ''",
        "ALTER TABLE cdb_settings ADD COLUMN openai_api_key VARCHAR(255) NOT NULL DEFAULT ''",
        "ALTER TABLE cdb_settings ADD COLUMN ai_autopilot_enabled TINYINT(1) DEFAULT 0",
        "ALTER TABLE cdb_settings ADD COLUMN ai_autopilot_threshold INT DEFAULT 5",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_read_customers TINYINT(1) DEFAULT 1",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_read_packages TINYINT(1) DEFAULT 1",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_read_financials TINYINT(1) DEFAULT 1",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_read_drivers TINYINT(1) DEFAULT 1",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_read_inventory TINYINT(1) DEFAULT 0",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_assign_drivers TINYINT(1) DEFAULT 1",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_confirm_payments TINYINT(1) DEFAULT 1",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_update_status TINYINT(1) DEFAULT 1",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_create_shipments TINYINT(1) DEFAULT 0",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_edit_shipments TINYINT(1) DEFAULT 0",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_cancel_shipments TINYINT(1) DEFAULT 0",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_send_sms TINYINT(1) DEFAULT 0",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_send_email TINYINT(1) DEFAULT 0",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_send_whatsapp TINYINT(1) DEFAULT 0",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_generate_reports TINYINT(1) DEFAULT 1",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_export_data TINYINT(1) DEFAULT 1",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_create_customers TINYINT(1) DEFAULT 0",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_edit_customers TINYINT(1) DEFAULT 0",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_process_refunds TINYINT(1) DEFAULT 0",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_apply_discounts TINYINT(1) DEFAULT 0",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_predict_analytics TINYINT(1) DEFAULT 1",
        "ALTER TABLE cdb_settings ADD COLUMN ai_can_optimize_routes TINYINT(1) DEFAULT 0",
    ];
    
    foreach ($columns as $sql) {
        try { $pdo->exec($sql); } catch (PDOException $e) { /* column already exists — ignore */ }
    }

    // Now update all settings
    $stmt = $pdo->prepare("UPDATE cdb_settings SET
        ai_provider = :ai_provider,
        groq_api_key = :groq_api_key,
        openai_api_key = :openai_api_key,
        ai_autopilot_enabled = :autopilot_enabled,
        ai_autopilot_threshold = :autopilot_threshold,
        ai_can_read_customers = :read_customers,
        ai_can_read_packages = :read_packages,
        ai_can_read_financials = :read_financials,
        ai_can_read_drivers = :read_drivers,
        ai_can_read_inventory = :read_inventory,
        ai_can_assign_drivers = :assign_drivers,
        ai_can_confirm_payments = :confirm_payments,
        ai_can_update_status = :update_status,
        ai_can_create_shipments = :create_shipments,
        ai_can_edit_shipments = :edit_shipments,
        ai_can_cancel_shipments = :cancel_shipments,
        ai_can_send_sms = :send_sms,
        ai_can_send_email = :send_email,
        ai_can_send_whatsapp = :send_whatsapp,
        ai_can_generate_reports = :generate_reports,
        ai_can_export_data = :export_data,
        ai_can_create_customers = :create_customers,
        ai_can_edit_customers = :edit_customers,
        ai_can_process_refunds = :process_refunds,
        ai_can_apply_discounts = :apply_discounts,
        ai_can_predict_analytics = :predict_analytics,
        ai_can_optimize_routes = :optimize_routes
    ");
    
    $stmt->execute([
        ':ai_provider'          => $provider,
        ':groq_api_key'         => $groq_key,
        ':openai_api_key'       => $openai_key,
        ':autopilot_enabled'    => $autopilot_enabled,
        ':autopilot_threshold'  => $autopilot_threshold,
        ':read_customers'       => $read_customers,
        ':read_packages'        => $read_packages,
        ':read_financials'      => $read_financials,
        ':read_drivers'         => $read_drivers,
        ':read_inventory'       => $read_inventory,
        ':assign_drivers'       => $assign_drivers,
        ':confirm_payments'     => $confirm_payments,
        ':update_status'        => $update_status,
        ':create_shipments'     => $create_shipments,
        ':edit_shipments'       => $edit_shipments,
        ':cancel_shipments'     => $cancel_shipments,
        ':send_sms'             => $send_sms,
        ':send_email'           => $send_email,
        ':send_whatsapp'        => $send_whatsapp,
        ':generate_reports'     => $generate_reports,
        ':export_data'          => $export_data,
        ':create_customers'     => $create_customers,
        ':edit_customers'       => $edit_customers,
        ':process_refunds'      => $process_refunds,
        ':apply_discounts'      => $apply_discounts,
        ':predict_analytics'    => $predict_analytics,
        ':optimize_routes'      => $optimize_routes,
    ]);

    $autopilot_msg = $autopilot_enabled ? ' Autopilot mode is now ACTIVE.' : '';
    echo json_encode(['success' => true, 'message' => 'AI settings and permissions saved successfully.' . $autopilot_msg]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
}

