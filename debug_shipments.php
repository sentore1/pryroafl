<?php
require_once("loader.php");

$db = new Conexion;

// Check recent shipments
$db->cdp_query("SELECT order_id, order_prefix, order_no, sender_id, receiver_id, status_courier, order_incomplete, is_pickup, is_consolidate 
                FROM cdb_add_order 
                ORDER BY order_id DESC 
                LIMIT 10");
$db->cdp_execute();
$shipments = $db->cdp_registros();

echo "<h2>Recent Shipments in Database:</h2>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Tracking</th><th>Sender ID</th><th>Receiver ID</th><th>Status</th><th>Incomplete</th><th>Pickup</th><th>Consolidate</th></tr>";
foreach ($shipments as $ship) {
    echo "<tr>";
    echo "<td>{$ship->order_id}</td>";
    echo "<td>{$ship->order_prefix}{$ship->order_no}</td>";
    echo "<td>{$ship->sender_id}</td>";
    echo "<td>{$ship->receiver_id}</td>";
    echo "<td>{$ship->status_courier}</td>";
    echo "<td>{$ship->order_incomplete}</td>";
    echo "<td>{$ship->is_pickup}</td>";
    echo "<td>{$ship->is_consolidate}</td>";
    echo "</tr>";
}
echo "</table>";

// Check if status 1 exists in cdb_styles
$db->cdp_query("SELECT * FROM cdb_styles WHERE id = 1");
$db->cdp_execute();
$status = $db->cdp_registro();

echo "<h2>Status Style ID=1:</h2>";
if ($status) {
    echo "Exists: {$status->mod_style} (Color: {$status->color})";
} else {
    echo "NOT FOUND - This is the problem!";
}

// Check user level
$user = new User;
$userData = $user->cdp_getUserData();
echo "<h2>Current User:</h2>";
echo "User ID: {$userData->id}<br>";
echo "User Level: {$userData->userlevel}<br>";
?>
