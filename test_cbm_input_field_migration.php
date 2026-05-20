<?php
// *************************************************************************
// *                                                                       *
// * DEPRIXA PRO -  Integrated Web Shipping System                         *
// * Copyright (c) JAOMWEB. All Rights Reserved                            *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * CBM Input Field Migration Test & Setup                               *
// * This file helps you add the new CBM input field setting              *
// *                                                                       *
// *************************************************************************

require_once("loader.php");

$db = new Conexion();
$core = new Core();

echo "<h2>CBM Input Field Migration</h2>";
echo "<hr>";

// Check if column exists
echo "<h3>Step 1: Check Database Column</h3>";
$db->cdp_query("SHOW COLUMNS FROM cdb_settings LIKE 'show_cbm_input_field'");
$db->cdp_execute();
$column_exists = $db->cdp_rowCount() > 0;

if ($column_exists) {
    echo "<p style='color: green;'>✓ Column 'show_cbm_input_field' already exists!</p>";
} else {
    echo "<p style='color: orange;'>⚠ Column 'show_cbm_input_field' does not exist yet.</p>";
    echo "<p><strong>Run this SQL to add it:</strong></p>";
    echo "<pre style='background: #f4f4f4; padding: 15px; border: 1px solid #ddd;'>";
    echo "ALTER TABLE `cdb_settings` \n";
    echo "ADD COLUMN `show_cbm_input_field` TINYINT(1) DEFAULT 0 \n";
    echo "AFTER `show_cbm_in_forms`;";
    echo "</pre>";
    
    // Try to add it automatically
    echo "<p><strong>Attempting to add column automatically...</strong></p>";
    try {
        $db->cdp_query("ALTER TABLE `cdb_settings` ADD COLUMN `show_cbm_input_field` TINYINT(1) DEFAULT 0 AFTER `show_cbm_in_forms`");
        $db->cdp_execute();
        echo "<p style='color: green;'>✓ Column added successfully!</p>";
        $column_exists = true;
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Could not add column automatically: " . $e->getMessage() . "</p>";
        echo "<p>Please run the SQL manually in your database.</p>";
    }
}

echo "<hr>";

// Check current settings
echo "<h3>Step 2: Current Settings</h3>";
$db->cdp_query("SELECT * FROM cdb_settings WHERE id = 1");
$db->cdp_execute();
$settings = $db->cdp_registro();

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Setting</th><th>Value</th><th>Description</th></tr>";

echo "<tr>";
echo "<td><strong>show_package_dimensions</strong></td>";
echo "<td>" . ($settings->show_package_dimensions ?? 'NOT SET') . "</td>";
echo "<td>Show Length, Width, Height fields</td>";
echo "</tr>";

echo "<tr>";
echo "<td><strong>show_cbm_input_field</strong></td>";
echo "<td>" . ($settings->show_cbm_input_field ?? 'NOT SET') . "</td>";
echo "<td>Show CBM direct input field</td>";
echo "</tr>";

echo "<tr>";
echo "<td><strong>show_cbm_in_forms</strong></td>";
echo "<td>" . ($settings->show_cbm_in_forms ?? 'NOT SET') . "</td>";
echo "<td>Show total CBM calculation</td>";
echo "</tr>";

echo "</table>";

echo "<hr>";

// Test Core class
echo "<h3>Step 3: Test Core Class</h3>";
echo "<p>Testing if Core class loads the new setting...</p>";

if (property_exists($core, 'show_cbm_input_field')) {
    echo "<p style='color: green;'>✓ Core class has 'show_cbm_input_field' property</p>";
    echo "<p>Value: <strong>" . ($core->show_cbm_input_field ?? 'NULL') . "</strong></p>";
} else {
    echo "<p style='color: red;'>✗ Core class does not have 'show_cbm_input_field' property</p>";
    echo "<p>Make sure lib/Core.php has been updated.</p>";
}

echo "<hr>";

// Recommendations
echo "<h3>Step 4: Configuration Recommendations</h3>";
echo "<div style='background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3;'>";
echo "<h4>Input Method Options:</h4>";
echo "<ul>";
echo "<li><strong>Option 1 - Dimensions Only (Default):</strong><br>";
echo "show_package_dimensions = 1, show_cbm_input_field = 0<br>";
echo "<em>Users enter L × W × H, system calculates CBM automatically</em></li>";
echo "<br>";
echo "<li><strong>Option 2 - CBM Only:</strong><br>";
echo "show_package_dimensions = 0, show_cbm_input_field = 1<br>";
echo "<em>Users enter CBM directly (e.g., 1 m³, 0.4 m³)</em></li>";
echo "<br>";
echo "<li><strong>Option 3 - Both Methods:</strong><br>";
echo "show_package_dimensions = 1, show_cbm_input_field = 1<br>";
echo "<em>Users can choose which method to use</em></li>";
echo "</ul>";
echo "<p><strong>Note:</strong> At least one input method must be enabled!</p>";
echo "</div>";

echo "<hr>";

// Quick setup buttons
if ($column_exists) {
    echo "<h3>Step 5: Quick Setup</h3>";
    echo "<p>Click a button to configure your preferred input method:</p>";
    
    echo "<form method='post' style='display: inline-block; margin-right: 10px;'>";
    echo "<input type='hidden' name='setup_mode' value='dimensions_only'>";
    echo "<button type='submit' style='padding: 10px 20px; background: #4CAF50; color: white; border: none; cursor: pointer;'>Dimensions Only (Default)</button>";
    echo "</form>";
    
    echo "<form method='post' style='display: inline-block; margin-right: 10px;'>";
    echo "<input type='hidden' name='setup_mode' value='cbm_only'>";
    echo "<button type='submit' style='padding: 10px 20px; background: #2196F3; color: white; border: none; cursor: pointer;'>CBM Only</button>";
    echo "</form>";
    
    echo "<form method='post' style='display: inline-block;'>";
    echo "<input type='hidden' name='setup_mode' value='both'>";
    echo "<button type='submit' style='padding: 10px 20px; background: #FF9800; color: white; border: none; cursor: pointer;'>Both Methods</button>";
    echo "</form>";
    
    // Handle form submission
    if (isset($_POST['setup_mode'])) {
        $mode = $_POST['setup_mode'];
        
        switch ($mode) {
            case 'dimensions_only':
                $dims = 1;
                $cbm = 0;
                $mode_name = "Dimensions Only";
                break;
            case 'cbm_only':
                $dims = 0;
                $cbm = 1;
                $mode_name = "CBM Only";
                break;
            case 'both':
                $dims = 1;
                $cbm = 1;
                $mode_name = "Both Methods";
                break;
            default:
                $dims = 1;
                $cbm = 0;
                $mode_name = "Dimensions Only";
        }
        
        $db->cdp_query("UPDATE cdb_settings SET show_package_dimensions = :dims, show_cbm_input_field = :cbm WHERE id = 1");
        $db->bind(':dims', $dims);
        $db->bind(':cbm', $cbm);
        
        if ($db->cdp_execute()) {
            echo "<div style='background: #4CAF50; color: white; padding: 15px; margin-top: 20px;'>";
            echo "<strong>✓ Success!</strong> Configuration updated to: <strong>$mode_name</strong>";
            echo "<br><br>Refresh this page to see the changes.";
            echo "</div>";
        } else {
            echo "<div style='background: #f44336; color: white; padding: 15px; margin-top: 20px;'>";
            echo "<strong>✗ Error!</strong> Could not update settings.";
            echo "</div>";
        }
    }
}

echo "<hr>";
echo "<p><a href='tools.php?do=config_cbm'>Go to CBM Configuration Page</a></p>";
?>
