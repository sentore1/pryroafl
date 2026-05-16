<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("loader.php");

$db = new Conexion;
$user = new User;
$userData = $user->cdp_getUserData();

echo "<h1>Simple Insert Test</h1>";

// Test with minimal fields first
echo "<h2>Test 1: Minimal Insert</h2>";
$sql = "INSERT INTO cdb_add_order (
    order_prefix, order_no, sender_id, receiver_id, 
    order_date, status_courier
) VALUES (
    'TEST', '888001', 22, 1,
    NOW(), 1
)";

$db->cdp_query($sql);
$result = $db->cdp_execute();

if ($result) {
    $id = $db->dbh->lastInsertId();
    echo "✓ SUCCESS! Insert ID: $id<br>";
} else {
    $errorInfo = $db->dbh->errorInfo();
    echo "✗ FAILED. Error: " . print_r($errorInfo, true) . "<br>";
}

// Test with more fields
echo "<h2>Test 2: With Weight Fields</h2>";
$sql2 = "INSERT INTO cdb_add_order (
    order_prefix, order_no, sender_id, receiver_id, 
    order_date, status_courier,
    order_weight, order_length, order_width, order_height
) VALUES (
    'TEST', '888002', 22, 1,
    NOW(), 1,
    5.5, 10, 8, 6
)";

$db->cdp_query($sql2);
$result2 = $db->cdp_execute();

if ($result2) {
    $id2 = $db->dbh->lastInsertId();
    echo "✓ SUCCESS! Insert ID: $id2<br>";
} else {
    $errorInfo2 = $db->dbh->errorInfo();
    echo "✗ FAILED. Error: " . print_r($errorInfo2, true) . "<br>";
}

// Test with problematic fields
echo "<h2>Test 3: With All Our Fields</h2>";
$sql3 = "INSERT INTO cdb_add_order (
    order_prefix, order_no, sender_id, receiver_id, 
    order_date, status_courier, is_consolidate, order_incomplete,
    order_weight, order_length, order_width, order_height,
    volumetric_weight, order_courier, order_service_options, 
    order_pay_mode, order_deli_time, order_package, 
    order_item_category, agency, origin_off, user_id,
    is_pickup, due_date, status_invoice, total_order
) VALUES (
    'TEST', '888003', 22, 1,
    NOW(), 1, 0, 1,
    5.5, 10, 8, 6,
    0.096, 1, 1,
    1, 1, 1,
    1, 1, 1, 1,
    0, NOW(), 2, 0
)";

$db->cdp_query($sql3);
$result3 = $db->cdp_execute();

if ($result3) {
    $id3 = $db->dbh->lastInsertId();
    echo "✓ SUCCESS! Insert ID: $id3<br>";
} else {
    $errorInfo3 = $db->dbh->errorInfo();
    echo "✗ FAILED. Error: " . print_r($errorInfo3, true) . "<br>";
}

// Show table structure
echo "<h2>Table Structure Check</h2>";
$db->cdp_query("DESCRIBE cdb_add_order");
$db->cdp_execute();
$columns = $db->cdp_registros();

echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
foreach ($columns as $col) {
    echo "<tr>";
    echo "<td>{$col->Field}</td>";
    echo "<td>{$col->Type}</td>";
    echo "<td>{$col->Null}</td>";
    echo "<td>{$col->Key}</td>";
    echo "<td>{$col->Default}</td>";
    echo "</tr>";
}
echo "</table>";

// Check recent inserts
echo "<h2>Recent Shipments</h2>";
$db->cdp_query("SELECT order_id, order_prefix, order_no FROM cdb_add_order ORDER BY order_id DESC LIMIT 5");
$db->cdp_execute();
$recent = $db->cdp_registros();

if (count($recent) > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Prefix</th><th>Number</th></tr>";
    foreach ($recent as $r) {
        echo "<tr><td>{$r->order_id}</td><td>{$r->order_prefix}</td><td>{$r->order_no}</td></tr>";
    }
    echo "</table>";
} else {
    echo "No shipments found.";
}
?>
