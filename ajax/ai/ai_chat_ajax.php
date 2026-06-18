<?php
// CRITICAL: No output before this point! Not even whitespace.
// Disable display_errors and log to file instead to prevent breaking JSON
error_reporting(E_ALL);
ini_set('display_errors', 0); // MUST be 0 for AJAX endpoints
ini_set('log_errors', 1);
ini_set('error_log', '../../error_log.txt'); // Log to file instead

// Clean any accidental output buffer
if (ob_get_level()) ob_end_clean();
ob_start();

header('Content-Type: application/json');
require_once("../../loader.php");
require_once("ai_permissions_helper.php");
require_once("local_ai_engine.php");

// Wrap everything in try-catch for better error handling
try {

$user = new User;
if (!$user->cdp_is_Admin()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Simple rate limiting (prevent spam)
session_start();
$now = time();
$min_delay = 2; // seconds between requests

if (isset($_SESSION['last_ai_request'])) {
    $time_since_last = $now - $_SESSION['last_ai_request'];
    if ($time_since_last < $min_delay) {
        echo json_encode([
            'reply' => '⏱️ Please wait ' . ($min_delay - $time_since_last) . ' second(s) before sending another message. This helps prevent rate limiting.',
            'actions' => []
        ]);
        exit;
    }
}
$_SESSION['last_ai_request'] = $now;

$message      = isset($_POST['message'])  ? trim($_POST['message'])  : '';
$history_json = isset($_POST['history'])  ? $_POST['history']        : '[]';
$history      = json_decode($history_json, true) ?: [];

$db = new Conexion;
$perms = new AIPermissions();
$context = [];

// --- Stuck shipments with order_id for actions ---
$db->cdp_query("
    SELECT o.order_id, o.order_prefix, o.order_no, o.order_date,
           TIMESTAMPDIFF(HOUR, MAX(t.t_date), NOW()) as hours_stuck,
           u.fname, u.lname,
           d.fname as driver_fname, d.lname as driver_lname,
           o.total_order, o.status_invoice
    FROM cdb_add_order o
    LEFT JOIN cdb_courier_track t ON t.order_id = o.order_id
    LEFT JOIN cdb_users u ON u.id = o.sender_id
    LEFT JOIN cdb_users d ON d.id = o.driver_id
    WHERE o.status_courier NOT IN (8,21) AND o.is_pickup=0
    GROUP BY o.order_id
    HAVING hours_stuck > 24 OR MAX(t.t_date) IS NULL
    ORDER BY hours_stuck DESC LIMIT 10
");
$db->cdp_execute();
$stuck = $db->cdp_registros();
$context['stuck_shipments'] = [];
if ($stuck) foreach ($stuck as $r) {
    $context['stuck_shipments'][] = [
        'order_id'     => (int)$r->order_id,
        'tracking'     => $r->order_prefix . $r->order_no,
        'hours_stuck'  => (int)$r->hours_stuck,
        'customer'     => trim($r->fname . ' ' . $r->lname),
        'driver'       => $r->driver_fname ? trim($r->driver_fname . ' ' . $r->driver_lname) : 'Unassigned',
        'value'        => (float)$r->total_order,
        'payment_status' => (int)$r->status_invoice,
    ];
}

// --- Driver workload ---
$db->cdp_query("
    SELECT d.fname, d.lname, d.id, COUNT(o.order_id) as active_shipments
    FROM cdb_users d
    LEFT JOIN cdb_add_order o ON o.driver_id = d.id AND o.status_courier NOT IN (8,21)
    WHERE d.userlevel = 3
    GROUP BY d.id ORDER BY active_shipments ASC LIMIT 10
");
$db->cdp_execute();
$drivers = $db->cdp_registros();
$context['drivers'] = [];
if ($drivers) foreach ($drivers as $r) {
    $context['drivers'][] = [
        'driver_id'        => (int)$r->id,
        'name'             => trim($r->fname . ' ' . $r->lname),
        'active_shipments' => (int)$r->active_shipments,
    ];
}

// --- Pending payments (not overdue but unpaid) with order_id ---
$db->cdp_query("
    SELECT o.order_id, o.order_prefix, o.order_no, o.total_order, u.fname, u.lname, o.status_courier
    FROM cdb_add_order o
    LEFT JOIN cdb_users u ON u.id = o.sender_id
    WHERE o.status_invoice = 2 AND o.status_courier NOT IN (21)
    ORDER BY o.order_date DESC LIMIT 10
");
$db->cdp_execute();
$pending_pay = $db->cdp_registros();
$context['pending_payments'] = [];
if ($pending_pay) foreach ($pending_pay as $r) {
    $context['pending_payments'][] = [
        'order_id' => (int)$r->order_id,
        'tracking' => $r->order_prefix . $r->order_no,
        'customer' => trim($r->fname . ' ' . $r->lname),
        'amount'   => (float)$r->total_order,
    ];
}

// --- Overdue invoices with order_id ---
$db->cdp_query("
    SELECT o.order_id, o.order_prefix, o.order_no, o.due_date, o.total_order,
           DATEDIFF(NOW(), o.due_date) as days_overdue, u.fname, u.lname
    FROM cdb_add_order o
    LEFT JOIN cdb_users u ON u.id = o.sender_id
    WHERE o.due_date < NOW() AND o.status_invoice = 2 AND o.status_courier != 21
    ORDER BY days_overdue DESC LIMIT 10
");
$db->cdp_execute();
$overdue = $db->cdp_registros();
$context['overdue_invoices'] = [];
if ($overdue) foreach ($overdue as $r) {
    $context['overdue_invoices'][] = [
        'order_id'     => (int)$r->order_id,
        'tracking'     => $r->order_prefix . $r->order_no,
        'customer'     => trim($r->fname . ' ' . $r->lname),
        'amount'       => (float)$r->total_order,
        'days_overdue' => (int)$r->days_overdue,
    ];
}

// --- Revenue ---
$db->cdp_query("SELECT IFNULL(SUM(total_order),0) as total FROM cdb_add_order WHERE MONTH(order_date)=MONTH(NOW()) AND YEAR(order_date)=YEAR(NOW()) AND status_courier!=21");
$db->cdp_execute(); $r = $db->cdp_registro();
$context['revenue_this_month'] = $r ? (float)$r->total : 0;

$db->cdp_query("SELECT IFNULL(SUM(total_order),0) as total FROM cdb_add_order WHERE MONTH(order_date)=MONTH(NOW()-INTERVAL 1 MONTH) AND YEAR(order_date)=YEAR(NOW()-INTERVAL 1 MONTH) AND status_courier!=21");
$db->cdp_execute(); $r = $db->cdp_registro();
$context['revenue_last_month'] = $r ? (float)$r->total : 0;

// --- Top customers ---
$db->cdp_query("
    SELECT u.fname, u.lname, COUNT(o.order_id) as shipments, IFNULL(SUM(o.total_order),0) as total
    FROM cdb_add_order o LEFT JOIN cdb_users u ON u.id = o.sender_id
    WHERE MONTH(o.order_date)=MONTH(NOW()) AND YEAR(o.order_date)=YEAR(NOW()) AND o.status_courier!=21
    GROUP BY o.sender_id ORDER BY total DESC LIMIT 5
");
$db->cdp_execute();
$top = $db->cdp_registros();
$context['top_customers'] = [];
if ($top) foreach ($top as $r) {
    $context['top_customers'][] = ['name' => trim($r->fname . ' ' . $r->lname), 'shipments' => (int)$r->shipments, 'revenue' => (float)$r->total];
}

// --- Last 24h ---
$db->cdp_query("SELECT COUNT(*) as total FROM cdb_add_order WHERE order_datetime >= NOW() - INTERVAL 24 HOUR AND is_pickup=0");
$db->cdp_execute(); $r = $db->cdp_registro(); $context['new_shipments_24h'] = $r ? (int)$r->total : 0;

$db->cdp_query("SELECT COUNT(*) as total FROM cdb_payment_gateways WHERE payment_date >= NOW() - INTERVAL 24 HOUR AND payment_status=3");
$db->cdp_execute(); $r = $db->cdp_registro(); $context['payments_received_24h'] = $r ? (int)$r->total : 0;

$db->cdp_query("SELECT COUNT(*) as total FROM cdb_add_order WHERE order_datetime >= NOW() - INTERVAL 24 HOUR AND status_courier=21");
$db->cdp_execute(); $r = $db->cdp_registro(); $context['cancellations_24h'] = $r ? (int)$r->total : 0;

$db->cdp_query("SELECT COUNT(*) as total FROM cdb_add_order WHERE (driver_id IS NULL OR driver_id=0 OR driver_id='') AND status_courier NOT IN (8,21) AND is_pickup=0");
$db->cdp_execute(); $r = $db->cdp_registro(); $context['unassigned_shipments'] = $r ? (int)$r->total : 0;

$db->cdp_query("SELECT COUNT(*) as total FROM cdb_prealert WHERE status=0 OR status IS NULL");
$db->cdp_execute(); $r = $db->cdp_registro(); $context['pending_prealerts'] = $r ? (int)$r->total : 0;

$db->cdp_query("SELECT COUNT(*) as total FROM cdb_users WHERE userlevel=1 AND created >= NOW() - INTERVAL 7 DAY");
$db->cdp_execute(); $r = $db->cdp_registro(); $context['new_customers_week'] = $r ? (int)$r->total : 0;

// --- Full customer list ---
$db->cdp_query("
    SELECT u.id, u.fname, u.lname, u.email, u.phone,
           COUNT(o.order_id) as total_shipments,
           IFNULL(SUM(o.total_order),0) as total_spent,
           MAX(o.order_date) as last_order
    FROM cdb_users u
    LEFT JOIN cdb_add_order o ON o.sender_id = u.id AND o.status_courier != 21
    WHERE u.userlevel = 1 AND u.active = 1
    GROUP BY u.id
    ORDER BY u.fname ASC
    LIMIT 100
");
$db->cdp_execute();
$all_customers = $db->cdp_registros();
$context['all_customers'] = [];
$context['total_customers'] = 0;
if ($all_customers) foreach ($all_customers as $r) {
    $context['all_customers'][] = [
        'id'              => (int)$r->id,
        'name'            => trim($r->fname . ' ' . $r->lname),
        'email'           => $r->email,
        'phone'           => $r->phone,
        'total_shipments' => (int)$r->total_shipments,
        'total_spent'     => (float)$r->total_spent,
        'last_order'      => $r->last_order,
    ];
    $context['total_customers']++;
}

// --- Currency ---
$db->cdp_query("SELECT currency, for_symbol, for_currency FROM cdb_settings LIMIT 1");
$db->cdp_execute();
$settings_row = $db->cdp_registro();
$currency = 'FRw';
if ($settings_row) {
    $currency = !empty($settings_row->for_symbol) ? $settings_row->for_symbol
              : (!empty($settings_row->for_currency) ? $settings_row->for_currency : $settings_row->currency);
}

$context_json = json_encode($context, JSON_PRETTY_PRINT);
$permissions_summary = $perms->getPermissionsSummary();
$autopilot_mode = $perms->isAutopilotEnabled();
$autopilot_threshold = $perms->getAutopilotThreshold();

// ------------------------------------------------------------------
// SYSTEM PROMPT
// ------------------------------------------------------------------
$autopilot_instructions = $autopilot_mode ? "
AUTOPILOT MODE IS ENABLED:
You can automatically take LOW-RISK actions when you detect issues that exceed the threshold of {$autopilot_threshold} items:
- Automatically assign drivers to unassigned shipments if there are {$autopilot_threshold}+ unassigned
- Automatically mark stuck shipments as 'In Transit' if there are {$autopilot_threshold}+ stuck
- When in autopilot, include AUTO_ACTION tag in your response
HIGH-RISK actions (cancellations, refunds, bulk payment confirmations) ALWAYS require manual confirmation even in autopilot mode.
" : "AUTOPILOT MODE IS DISABLED: All actions require manual confirmation via action buttons.";

$system_prompt = <<<PROMPT
You are Pryro AI, an intelligent operations assistant for a shipping and logistics company called Pryro.
You have access to live system data and help the admin manage shipments, drivers, payments, and customers.
Be concise, direct, and actionable. Use bullet points when listing items.
Always refer to specific tracking numbers, customer names, and amounts from the data when relevant.
Always use "{$currency}" as the currency symbol. Never use dollar sign or any other currency.

When asked about customers/clients, use the "all_customers" array in the context data — it contains the full list with name, email, phone, total shipments, and total spent. The "total_customers" field shows the total count.

{$autopilot_instructions}

PERMISSIONS:
{$permissions_summary}

IMPORTANT - ACTION BUTTONS:
Only append ACTIONS_JSON if the data contains real items that need action. NEVER include an action if the data shows 0 items or empty arrays for that category.
Always check your permissions before suggesting actions. If you don't have permission for an action, explain to the user that they need to enable it in AI Settings.

Specific rules:
- Add "confirm_payment" button ONLY if pending_payments OR overdue_invoices array has items with actual order_id values AND you have ai_can_confirm_payments permission
- Add "confirm_all_wire_payments" button ONLY if overdue_invoices array has 2 or more items AND you have ai_can_confirm_payments permission
- Add "update_status" button ONLY if stuck_shipments array has items with actual order_id values AND you have ai_can_update_status permission
- Add "send_sms" button ONLY if you have ai_can_send_sms permission
- Add "send_email" button ONLY if you have ai_can_send_email permission
- Add "send_whatsapp" button ONLY if you have ai_can_send_whatsapp permission
- Add "cancel_shipment" button ONLY if you have ai_can_cancel_shipments permission AND the user explicitly asks to cancel
- Add "create_driver" button when user asks to create a driver
- Add "create_customer" button when user asks to create a customer
- Add "update_customer" button when user asks to update customer info
- Add "add_charge" button when user asks to add a charge
- Add "refund_payment" button when user asks to process a refund
- Add "add_prealert" button when user asks to add a pre-alert
- Add "schedule_pickup" button when user asks to schedule a pickup
- Add "send_bulk_sms" button when user asks to send SMS to multiple customers
- Add "generate_report" button when user asks to generate a report
- Add "export_data" button when user asks to export data
- Add "mark_delivered" button when user asks to mark a shipment as delivered (needs order_id, person_receives, driver_id)
- Add "add_tracking_note" button when user asks to add a tracking update (needs order_id, status_id, comment)
- Add "delete_shipment" button when user explicitly asks to delete a shipment
- Add "bulk_update_status" button when user asks to update multiple shipments at once (needs order_ids comma-separated, status_id)
- Add "bulk_assign_driver" button when user asks to assign one driver to multiple shipments (needs order_ids comma-separated, driver_id)
- Add "mark_package_delivered" button when user asks to mark a package as delivered (needs package_id, person_receives, driver_id)
- Add "update_consolidate_driver" button when user asks to assign driver to a consolidate order
- Add "confirm_consolidate_payment" button when user asks to confirm payment for a consolidate order
- Add "accept_pickup" button when user asks to accept a pickup request
- Add "cancel_pickup" button when user asks to cancel a pickup
- Add "delete_customer" button when user explicitly asks to delete a customer (requires confirm=DELETE)
- Add "reset_customer_password" button when user asks to reset a customer password
- Add "edit_driver" button when user asks to update driver info
- Add "delete_driver" button when user explicitly asks to delete a driver (requires confirm=DELETE)
- Add "report_payments_received" button when user asks for a payments received report
- Add "report_driver_performance" button when user asks for driver performance/stats
- Add "report_customer_balance" button when user asks for customer balance/debt report
- Add "notify_sms_shipment" button when user asks to send SMS notification for a specific shipment
- Add "record_payment" button when user asks to record/log a payment for an order
- Add "delete_charge" button when user asks to delete a charge record
- Add "create_recipient" button when user asks to create a new recipient/consignee
- Add "delete_recipient" button when user explicitly asks to delete a recipient
- Add "create_employee" button when user asks to create a new employee/staff account
- Add "delete_employee" button when user explicitly asks to delete/deactivate an employee
- Add "reset_employee_password" button when user asks to reset an employee password
- Add "send_whatsapp_bulk" button when user asks to send WhatsApp to multiple people
- Add "delete_prealert" button when user asks to delete a pre-alert
- Add "update_consolidate_status" button when user asks to update status of a consolidate order
- Add "report_general" button when user asks for a general/overview shipments report
- Add "report_pickup_summary" button when user asks for pickup operations report
- Add "report_packages_registered" button when user asks for customer packages report
- If everything is fine and no action is needed, do NOT include ACTIONS_JSON at all

When the user asks to create a customer, ALWAYS include a "create_customer" action with ALL data the user provided. Use these exact field names:
  fname (first name), lname (last name), email, phone
  Example: {"action":"create_customer","label":"Create Customer - NAME","fname":"John","lname":"Doe","email":"john@example.com","phone":"+250788000000"}
  If a field wasn't mentioned, leave it as empty string "".

When the user asks to create a driver, ALWAYS include a "create_driver" action with: fname, lname, email, phone, vehicle
When the user asks to cancel a shipment, include "cancel_shipment" with: order_id, reason
When the user asks to update a customer, include "update_customer" with: customer_id, phone/email/address (whichever was mentioned)
When the user asks to add a charge, include "add_charge" with: customer_id, amount, description, due_date
When the user asks to refund, include "refund_payment" with: order_id, amount, reason
When the user asks to add a pre-alert, include "add_prealert" with: tracking, customer_id, description, weight
When the user asks to schedule a pickup, include "schedule_pickup" with: customer_id, address, date, notes
When the user asks to send bulk SMS, include "send_bulk_sms" with: filter (all/customer_id), message
When the user asks to generate a report, include "generate_report" with: report_type, start_date, end_date
When the user asks to export data, include "export_data" with: data_type, format
When the user asks to record a payment, include "record_payment" with: order_id, amount, payment_type (method ID), notes
When the user asks to create a recipient, include "create_recipient" with: fname, lname, phone, email, address
When the user asks to create an employee/staff, include "create_employee" with: fname, lname, email, phone, username
When the user asks to delete a pre-alert, include "delete_prealert" with: prealert_id
When the user asks to update consolidate status, include "update_consolidate_status" with: consolidate_id, status_id, comment
When the user asks for a general report, include "report_general" with: start_date, end_date, status_id
When the user asks for a pickup report, include "report_pickup_summary" with: start_date, end_date, driver_id
When the user asks for a packages report, include "report_packages_registered" with: start_date, end_date, customer_id

When actions exist, append at the very end on one line:
ACTIONS_JSON:[{"action":"confirm_payment","label":"Confirm Payment - TRACKING","order_id":REAL_ID,"order_type":"courier","description":"Confirm payment for TRACKING"},{"action":"update_status","label":"Mark In Transit - TRACKING","order_id":REAL_ID,"status_id":4,"order_type":"courier","description":"Update TRACKING to In Transit"}]

Status IDs: 2=Pending, 3=Processing, 4=In Transit, 5=Out for Delivery, 8=Delivered, 21=Cancelled

Here is the current live system data:
{$context_json}
PROMPT;

// ------------------------------------------------------------------
// BUILD MESSAGES
// ------------------------------------------------------------------
$messages = [['role' => 'system', 'content' => $system_prompt]];
foreach ($history as $h) {
    if (isset($h['role']) && isset($h['content'])) {
        $messages[] = ['role' => $h['role'], 'content' => $h['content']];
    }
}
if (!empty($message)) {
    $messages[] = ['role' => 'user', 'content' => $message];
}

// ------------------------------------------------------------------
// LOAD API KEY
// ------------------------------------------------------------------
$api_key = ''; $provider = 'groq';
try {
    try { $db->cdp_query("ALTER TABLE cdb_settings ADD COLUMN ai_provider VARCHAR(20) NOT NULL DEFAULT 'groq'"); $db->cdp_execute(); } catch(Exception $e){}
    try { $db->cdp_query("ALTER TABLE cdb_settings ADD COLUMN groq_api_key VARCHAR(255) NOT NULL DEFAULT ''"); $db->cdp_execute(); } catch(Exception $e){}
    try { $db->cdp_query("ALTER TABLE cdb_settings ADD COLUMN openai_api_key VARCHAR(255) NOT NULL DEFAULT ''"); $db->cdp_execute(); } catch(Exception $e){}
    $db->cdp_query("SELECT ai_provider, groq_api_key, openai_api_key FROM cdb_settings LIMIT 1");
    $db->cdp_execute();
    $row = $db->cdp_registro();
    if ($row) {
        $provider = !empty($row->ai_provider)  ? $row->ai_provider  : 'groq';
        $api_key  = !empty($row->groq_api_key) ? trim($row->groq_api_key) : '';
        if ($provider === 'openai' && !empty($row->openai_api_key)) $api_key = trim($row->openai_api_key);
    }
} catch (Exception $e) {}

if (empty($api_key)) {
    // No API key — use local fallback engine
    $full_reply = cdp_local_ai_engine($message, $context, $currency, $perms);
    $actions    = cdp_local_ai_actions($message, $context, $perms);
    $output = ob_get_clean();
    echo json_encode(['reply' => $full_reply, 'actions' => $actions]);
    exit;
}

// ------------------------------------------------------------------
// CALL LLM
// ------------------------------------------------------------------
$endpoint = ($provider === 'openai') ? 'https://api.openai.com/v1/chat/completions' : 'https://api.groq.com/openai/v1/chat/completions';
$model    = ($provider === 'openai') ? 'gpt-4o' : 'llama-3.3-70b-versatile';

$payload = json_encode(['model' => $model, 'messages' => $messages, 'max_tokens' => 1200, 'temperature' => 0.4]);

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $api_key]);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    // API failed (rate limit, invalid key, service down) — use local fallback engine silently
    $full_reply = cdp_local_ai_engine($message, $context, $currency, $perms);
    $actions    = cdp_local_ai_actions($message, $context, $perms);
    $output = ob_get_clean();
    echo json_encode(['reply' => $full_reply, 'actions' => $actions]);
    exit;
}

$result     = json_decode($response, true);
$full_reply = isset($result['choices'][0]['message']['content']) ? $result['choices'][0]['message']['content'] : 'No response.';

// ------------------------------------------------------------------
// PARSE ACTIONS_JSON OUT OF REPLY
// ------------------------------------------------------------------
$actions = [];
// Find ACTIONS_JSON: and extract everything after it
if (preg_match('/ACTIONS_JSON:(.+)$/s', $full_reply, $matches)) {
    $actions_raw = trim($matches[1]);
    $actions     = json_decode($actions_raw, true) ?: [];
    // Remove ACTIONS_JSON block from the visible reply - everything from ACTIONS_JSON onward
    $full_reply  = trim(preg_replace('/ACTIONS_JSON:.+$/s', '', $full_reply));
}

// Clean output buffer and send JSON
$output = ob_get_clean();
if (!empty($output)) {
    // Log any unexpected output
    error_log("P-AI Warning: Unexpected output before JSON: " . substr($output, 0, 200));
}

echo json_encode(['reply' => $full_reply, 'actions' => $actions]);
exit;

} catch (Exception $e) {
    // Catch any PHP errors and return them as JSON
    ob_end_clean(); // Clear any buffered output
    error_log("P-AI Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    echo json_encode([
        'error' => 'Server error: ' . $e->getMessage(),
        'reply' => 'An error occurred while processing your request. Check PHP error logs.',
        'actions' => [],
        'debug' => [
            'message' => $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ]);
    exit;
}
