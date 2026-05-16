<?php
require_once("loader.php");
$db = new Conexion;

echo "<h1>Column Existence Test</h1>";

$columns_to_check = array(
    'order_weight',
    'order_length', 
    'order_width',
    'order_height',
    'volumetric_weight',
    'total_weight',
    'value_weight'
);

$db->cdp_query("DESCRIBE cdb_add_order");
$db->cdp_execute();
$table_columns = $db->cdp_registros();

$existing_columns = array();
foreach ($table_columns as $col) {
    $existing_columns[] = $col->Field;
}

echo "<h2>Checking Required Columns:</h2>";
echo "<table border='1'><tr><th>Column Name</th><th>Exists?</th></tr>";

foreach ($columns_to_check as $col) {
    $exists = in_array($col, $existing_columns);
    $status = $exists ? '✓ YES' : '✗ NO - MISSING!';
    $color = $exists ? 'green' : 'red';
    echo "<tr><td>$col</td><td style='color:$color; font-weight:bold;'>$status</td></tr>";
}

echo "</table>";

echo "<h2>All Columns in cdb_add_order:</h2>";
echo "<ul>";
foreach ($existing_columns as $col) {
    echo "<li>$col</li>";
}
echo "</ul>";
?>
