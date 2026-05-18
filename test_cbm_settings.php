<?php
// Quick test to verify CBM settings are working
require_once("loader.php");

$core = new Core();

echo "<h2>CBM Settings Test</h2>";
echo "<pre>";
echo "CBM Enabled: " . (isset($core->cbm_calculation_enabled) ? $core->cbm_calculation_enabled : 'NOT SET') . "\n";
echo "CBM Rate: " . (isset($core->cbm_rate_per_cubic_meter) ? $core->cbm_rate_per_cubic_meter : 'NOT SET') . "\n";
echo "CBM Priority: " . (isset($core->cbm_vs_weight_priority) ? $core->cbm_vs_weight_priority : 'NOT SET') . "\n";
echo "</pre>";

// Check database columns
$db = new Conexion();
$db->cdp_query("SHOW COLUMNS FROM cdb_settings LIKE 'cbm%'");
$db->cdp_execute();
$columns = $db->cdp_registros();

echo "<h3>Database Columns:</h3>";
echo "<pre>";
if ($columns) {
    foreach ($columns as $col) {
        echo "Column: " . $col->Field . " | Type: " . $col->Type . " | Default: " . $col->Default . "\n";
    }
} else {
    echo "No CBM columns found!\n";
}
echo "</pre>";

// Check current values
$db->cdp_query("SELECT cbm_calculation_enabled, cbm_rate_per_cubic_meter, cbm_vs_weight_priority FROM cdb_settings WHERE id = 1");
$db->cdp_execute();
$settings = $db->cdp_registro();

echo "<h3>Current Database Values:</h3>";
echo "<pre>";
if ($settings) {
    echo "cbm_calculation_enabled: " . $settings->cbm_calculation_enabled . "\n";
    echo "cbm_rate_per_cubic_meter: " . $settings->cbm_rate_per_cubic_meter . "\n";
    echo "cbm_vs_weight_priority: " . $settings->cbm_vs_weight_priority . "\n";
} else {
    echo "No settings found!\n";
}
echo "</pre>";
?>
