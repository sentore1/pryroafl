<?php
// Test CBM settings save functionality
require_once("loader.php");

echo "<h2>CBM Settings Save Test</h2>";

// Check if columns exist
$db = new Conexion();
$db->cdp_query("SHOW COLUMNS FROM cdb_settings LIKE 'cbm%'");
$db->cdp_execute();
$cbm_columns = $db->cdp_registros();

$db->cdp_query("SHOW COLUMNS FROM cdb_settings LIKE 'show_%'");
$db->cdp_execute();
$show_columns = $db->cdp_registros();

echo "<h3>CBM Columns in Database:</h3>";
echo "<pre>";
if ($cbm_columns) {
    foreach ($cbm_columns as $col) {
        echo "✓ " . $col->Field . " (" . $col->Type . ")\n";
    }
} else {
    echo "❌ No CBM columns found!\n";
}
echo "</pre>";

echo "<h3>Display Columns in Database:</h3>";
echo "<pre>";
if ($show_columns) {
    foreach ($show_columns as $col) {
        echo "✓ " . $col->Field . " (" . $col->Type . ")\n";
    }
} else {
    echo "❌ No display columns found!\n";
}
echo "</pre>";

// Test if we can update
echo "<h3>Testing Update Query:</h3>";
echo "<pre>";

try {
    $db->cdp_query("
        UPDATE cdb_settings 
        SET 
            cbm_calculation_enabled = 1,
            cbm_rate_per_cubic_meter = 50.00,
            cbm_vs_weight_priority = 'higher'
        WHERE id = 1
    ");
    
    if ($db->cdp_execute()) {
        echo "✓ Basic update works!\n";
    } else {
        echo "❌ Basic update failed!\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test with new columns
try {
    $db->cdp_query("
        UPDATE cdb_settings 
        SET 
            cbm_measurement_unit = 'cm',
            show_package_dimensions = 1,
            show_cbm_in_forms = 1
        WHERE id = 1
    ");
    
    if ($db->cdp_execute()) {
        echo "✓ New columns update works!\n";
    } else {
        echo "❌ New columns update failed!\n";
    }
} catch (Exception $e) {
    echo "❌ Error with new columns: " . $e->getMessage() . "\n";
}

echo "</pre>";

// Check current values
$db->cdp_query("SELECT * FROM cdb_settings WHERE id = 1");
$db->cdp_execute();
$settings = $db->cdp_registro();

echo "<h3>Current Settings Values:</h3>";
echo "<pre>";
echo "cbm_calculation_enabled: " . ($settings->cbm_calculation_enabled ?? 'NOT SET') . "\n";
echo "cbm_rate_per_cubic_meter: " . ($settings->cbm_rate_per_cubic_meter ?? 'NOT SET') . "\n";
echo "cbm_vs_weight_priority: " . ($settings->cbm_vs_weight_priority ?? 'NOT SET') . "\n";
echo "cbm_measurement_unit: " . ($settings->cbm_measurement_unit ?? 'NOT SET') . "\n";
echo "show_package_dimensions: " . ($settings->show_package_dimensions ?? 'NOT SET') . "\n";
echo "show_cbm_in_forms: " . ($settings->show_cbm_in_forms ?? 'NOT SET') . "\n";
echo "</pre>";

echo "<h3>Action Required:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";
if (!$show_columns || count($show_columns) == 0) {
    echo "<strong>⚠️ You need to run the database migration!</strong><br><br>";
    echo "Run this SQL in phpMyAdmin:<br><br>";
    echo "<code style='background: #f8f9fa; padding: 10px; display: block;'>";
    echo "ALTER TABLE `cdb_settings` <br>";
    echo "ADD COLUMN `cbm_measurement_unit` ENUM('cm','inch','m') DEFAULT 'cm' AFTER `cbm_vs_weight_priority`,<br>";
    echo "ADD COLUMN `show_package_dimensions` TINYINT(1) DEFAULT 1 AFTER `cbm_measurement_unit`,<br>";
    echo "ADD COLUMN `show_cbm_in_forms` TINYINT(1) DEFAULT 1 AFTER `show_package_dimensions`;";
    echo "</code>";
} else {
    echo "<strong>✓ All columns exist! Settings should save correctly.</strong>";
}
echo "</div>";
?>
