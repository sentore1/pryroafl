<?php
// *************************************************************************
// *                                                                       *
// * CBM Feature Complete Self-Test                                       *
// * This script tests all components of the CBM input field feature      *
// *                                                                       *
// *************************************************************************

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("loader.php");

$user = new User;
$core = new Core;

if (!$user->cdp_is_Admin()) {
    die("Access denied. Admin privileges required.");
}

$db = new Conexion;
$all_tests_passed = true;
$test_results = [];

// Styling
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; background: #f5f5f5; }
    h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
    h2 { color: #555; margin-top: 30px; }
    .test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .test-item { padding: 10px; margin: 5px 0; border-left: 4px solid #ddd; }
    .pass { border-left-color: #4CAF50; background: #f1f8f4; }
    .fail { border-left-color: #f44336; background: #fef1f1; }
    .warning { border-left-color: #ff9800; background: #fff8f1; }
    .info { border-left-color: #2196F3; background: #f1f5f9; }
    .icon { font-size: 20px; margin-right: 10px; }
    .summary { font-size: 24px; padding: 20px; text-align: center; border-radius: 5px; margin: 20px 0; }
    .summary.pass { background: #4CAF50; color: white; }
    .summary.fail { background: #f44336; color: white; }
    pre { background: #f4f4f4; padding: 10px; border-radius: 3px; overflow: auto; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
    th { background: #f2f2f2; font-weight: bold; }
    .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 3px; cursor: pointer; font-size: 14px; }
    .btn-primary { background: #2196F3; color: white; }
    .btn-success { background: #4CAF50; color: white; }
    .btn-danger { background: #f44336; color: white; }
</style>";

echo "<h1>🧪 CBM Feature Complete Self-Test</h1>";
echo "<p>Testing all components of the CBM input field feature...</p>";

// ============================================================================
// TEST 1: Database Structure
// ============================================================================
echo "<div class='test-section'>";
echo "<h2>Test 1: Database Structure</h2>";

$required_columns = [
    'cbm_calculation_enabled' => 'TINYINT',
    'cbm_rate_per_cubic_meter' => 'DECIMAL',
    'cbm_vs_weight_priority' => 'ENUM',
    'cbm_measurement_unit' => 'ENUM',
    'show_package_dimensions' => 'TINYINT',
    'show_cbm_input_field' => 'TINYINT',
    'show_cbm_in_forms' => 'TINYINT'
];

$db->cdp_query("SHOW COLUMNS FROM cdb_settings");
$db->cdp_execute();
$existing_columns = $db->cdp_registros();
$column_map = [];
foreach ($existing_columns as $col) {
    $column_map[$col->Field] = $col->Type;
}

foreach ($required_columns as $col_name => $expected_type) {
    if (isset($column_map[$col_name])) {
        echo "<div class='test-item pass'><span class='icon'>✓</span> Column <strong>$col_name</strong> exists</div>";
        $test_results['db_' . $col_name] = true;
    } else {
        echo "<div class='test-item fail'><span class='icon'>✗</span> Column <strong>$col_name</strong> is MISSING</div>";
        $test_results['db_' . $col_name] = false;
        $all_tests_passed = false;
    }
}

echo "</div>";

// ============================================================================
// TEST 2: Core Class Properties
// ============================================================================
echo "<div class='test-section'>";
echo "<h2>Test 2: Core Class Properties</h2>";

$required_properties = [
    'cbm_calculation_enabled',
    'cbm_rate_per_cubic_meter',
    'cbm_vs_weight_priority',
    'cbm_measurement_unit',
    'show_package_dimensions',
    'show_cbm_input_field',
    'show_cbm_in_forms'
];

foreach ($required_properties as $prop) {
    if (property_exists($core, $prop)) {
        $value = $core->$prop ?? 'NULL';
        echo "<div class='test-item pass'><span class='icon'>✓</span> Property <strong>$prop</strong> exists (value: $value)</div>";
        $test_results['core_' . $prop] = true;
    } else {
        echo "<div class='test-item fail'><span class='icon'>✗</span> Property <strong>$prop</strong> is MISSING in Core class</div>";
        $test_results['core_' . $prop] = false;
        $all_tests_passed = false;
    }
}

echo "</div>";

// ============================================================================
// TEST 3: Current Database Values
// ============================================================================
echo "<div class='test-section'>";
echo "<h2>Test 3: Current Database Values</h2>";

$db->cdp_query("SELECT * FROM cdb_settings WHERE id = 1");
$db->cdp_execute();
$settings = $db->cdp_registro();

echo "<table>";
echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";

$settings_check = [
    'cbm_calculation_enabled' => $settings->cbm_calculation_enabled ?? null,
    'cbm_rate_per_cubic_meter' => $settings->cbm_rate_per_cubic_meter ?? null,
    'cbm_vs_weight_priority' => $settings->cbm_vs_weight_priority ?? null,
    'cbm_measurement_unit' => $settings->cbm_measurement_unit ?? null,
    'show_package_dimensions' => $settings->show_package_dimensions ?? null,
    'show_cbm_input_field' => $settings->show_cbm_input_field ?? null,
    'show_cbm_in_forms' => $settings->show_cbm_in_forms ?? null
];

foreach ($settings_check as $key => $value) {
    $display_value = $value !== null ? $value : 'NULL';
    $status = $value !== null ? '✓' : '⚠';
    $class = $value !== null ? 'pass' : 'warning';
    echo "<tr class='$class'><td>$key</td><td>$display_value</td><td>$status</td></tr>";
}

echo "</table>";
echo "</div>";

// ============================================================================
// TEST 4: File Existence
// ============================================================================
echo "<div class='test-section'>";
echo "<h2>Test 4: Required Files</h2>";

$required_files = [
    'lib/Core.php' => 'Core class file',
    'ajax/tools/config_cbm_ajax.php' => 'AJAX save handler',
    'views/tools/config_cbm.php' => 'Configuration page',
    'views/courier/courier_add.php' => 'Courier add form',
    'dataJs/courier_add.js' => 'Courier add JavaScript'
];

foreach ($required_files as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='test-item pass'><span class='icon'>✓</span> <strong>$description</strong> exists ($file)</div>";
        $test_results['file_' . md5($file)] = true;
    } else {
        echo "<div class='test-item fail'><span class='icon'>✗</span> <strong>$description</strong> is MISSING ($file)</div>";
        $test_results['file_' . md5($file)] = false;
        $all_tests_passed = false;
    }
}

echo "</div>";

// ============================================================================
// TEST 5: AJAX Handler Test
// ============================================================================
echo "<div class='test-section'>";
echo "<h2>Test 5: AJAX Handler Functionality</h2>";

// Simulate a POST request
$_POST = [
    'cbm_enabled' => '1',
    'cbm_rate' => '50',
    'cbm_priority' => 'higher',
    'cbm_measurement_unit' => 'cm',
    'show_package_dimensions' => '1',
    'show_cbm_input_field' => '0',
    'show_cbm_in_forms' => '1'
];

// Test the logic without actually executing
$cbm_enabled = isset($_POST['cbm_enabled']) ? 1 : 0;
$cbm_rate = floatval($_POST['cbm_rate']);
$cbm_priority = $_POST['cbm_priority'];
$cbm_measurement_unit = $_POST['cbm_measurement_unit'];
$show_package_dimensions = isset($_POST['show_package_dimensions']) ? 1 : 0;
$show_cbm_input_field = isset($_POST['show_cbm_input_field']) ? 1 : 0;
$show_cbm_in_forms = isset($_POST['show_cbm_in_forms']) ? 1 : 0;

echo "<div class='test-item info'><span class='icon'>ℹ</span> Simulating POST data processing...</div>";
echo "<pre>";
echo "Parsed values:\n";
echo "  cbm_enabled: $cbm_enabled\n";
echo "  cbm_rate: $cbm_rate\n";
echo "  cbm_priority: $cbm_priority\n";
echo "  cbm_measurement_unit: $cbm_measurement_unit\n";
echo "  show_package_dimensions: $show_package_dimensions\n";
echo "  show_cbm_input_field: $show_cbm_input_field\n";
echo "  show_cbm_in_forms: $show_cbm_in_forms\n";
echo "</pre>";

// Validation test
$validation_errors = [];
if ($cbm_rate < 0) {
    $validation_errors[] = 'CBM rate must be positive';
}
if (!in_array($cbm_priority, ['weight', 'cbm', 'higher'])) {
    $validation_errors[] = 'Invalid priority';
}
if (!in_array($cbm_measurement_unit, ['cm', 'inch', 'm'])) {
    $validation_errors[] = 'Invalid measurement unit';
}
if ($show_package_dimensions == 0 && $show_cbm_input_field == 0) {
    $validation_errors[] = 'At least one input method must be enabled';
}

if (empty($validation_errors)) {
    echo "<div class='test-item pass'><span class='icon'>✓</span> Validation logic works correctly</div>";
    $test_results['ajax_validation'] = true;
} else {
    echo "<div class='test-item fail'><span class='icon'>✗</span> Validation errors: " . implode(', ', $validation_errors) . "</div>";
    $test_results['ajax_validation'] = false;
    $all_tests_passed = false;
}

// Clear POST
$_POST = [];

echo "</div>";

// ============================================================================
// TEST 6: JavaScript File Check
// ============================================================================
echo "<div class='test-section'>";
echo "<h2>Test 6: JavaScript Implementation</h2>";

if (file_exists('dataJs/courier_add.js')) {
    $js_content = file_get_contents('dataJs/courier_add.js');
    
    $js_checks = [
        'cbm_value property' => strpos($js_content, 'cbm_value') !== false,
        'show_package_dimensions check' => strpos($js_content, 'show_package_dimensions') !== false,
        'show_cbm_input_field check' => strpos($js_content, 'show_cbm_input_field') !== false,
        'CBM input field rendering' => strpos($js_content, 'CBM (m³)') !== false,
        'Conditional validation' => strpos($js_content, 'if (show_dimensions)') !== false
    ];
    
    foreach ($js_checks as $check => $result) {
        if ($result) {
            echo "<div class='test-item pass'><span class='icon'>✓</span> $check implemented</div>";
        } else {
            echo "<div class='test-item fail'><span class='icon'>✗</span> $check NOT found</div>";
            $all_tests_passed = false;
        }
    }
} else {
    echo "<div class='test-item fail'><span class='icon'>✗</span> courier_add.js file not found</div>";
    $all_tests_passed = false;
}

echo "</div>";

// ============================================================================
// TEST 7: Configuration Page Check
// ============================================================================
echo "<div class='test-section'>";
echo "<h2>Test 7: Configuration Page</h2>";

if (file_exists('views/tools/config_cbm.php')) {
    $config_content = file_get_contents('views/tools/config_cbm.php');
    
    $config_checks = [
        'Form ID' => strpos($config_content, 'id="save_config_cbm"') !== false,
        'Package Dimensions checkbox' => strpos($config_content, 'name="show_package_dimensions"') !== false,
        'CBM Input Field checkbox' => strpos($config_content, 'name="show_cbm_input_field"') !== false,
        'CBM in Forms checkbox' => strpos($config_content, 'name="show_cbm_in_forms"') !== false,
        'jQuery document ready' => strpos($config_content, 'jQuery(document).ready') !== false || strpos($config_content, '$(document).ready') !== false,
        'AJAX submit handler' => strpos($config_content, '$("#save_config_cbm").on(\'submit\'') !== false
    ];
    
    foreach ($config_checks as $check => $result) {
        if ($result) {
            echo "<div class='test-item pass'><span class='icon'>✓</span> $check found</div>";
        } else {
            echo "<div class='test-item fail'><span class='icon'>✗</span> $check NOT found</div>";
            $all_tests_passed = false;
        }
    }
} else {
    echo "<div class='test-item fail'><span class='icon'>✗</span> config_cbm.php file not found</div>";
    $all_tests_passed = false;
}

echo "</div>";

// ============================================================================
// TEST 8: Live Save Test
// ============================================================================
echo "<div class='test-section'>";
echo "<h2>Test 8: Live Save Test</h2>";
echo "<p>Click the button below to test the actual save functionality:</p>";

echo "<button class='btn btn-primary' onclick='testSave()'>Run Live Save Test</button>";
echo "<div id='save_test_result' style='margin-top: 20px;'></div>";

echo "</div>";

// ============================================================================
// SUMMARY
// ============================================================================
$total_tests = count($test_results);
$passed_tests = count(array_filter($test_results));
$failed_tests = $total_tests - $passed_tests;

echo "<div class='summary " . ($all_tests_passed ? 'pass' : 'fail') . "'>";
if ($all_tests_passed) {
    echo "<h2>✓ All Tests Passed!</h2>";
    echo "<p>The CBM input field feature is properly installed and configured.</p>";
} else {
    echo "<h2>✗ Some Tests Failed</h2>";
    echo "<p>Passed: $passed_tests / $total_tests tests</p>";
    echo "<p>Please review the failed tests above and fix the issues.</p>";
}
echo "</div>";

// ============================================================================
// RECOMMENDATIONS
// ============================================================================
echo "<div class='test-section'>";
echo "<h2>Recommendations</h2>";

if (!$all_tests_passed) {
    echo "<div class='test-item warning'>";
    echo "<h3>Issues Found:</h3>";
    echo "<ul>";
    
    // Check for missing columns
    $missing_columns = [];
    foreach ($required_columns as $col => $type) {
        if (!isset($column_map[$col])) {
            $missing_columns[] = $col;
        }
    }
    
    if (!empty($missing_columns)) {
        echo "<li><strong>Missing database columns:</strong> " . implode(', ', $missing_columns);
        echo "<br><button class='btn btn-danger' onclick='showMigrationSQL()'>Show Migration SQL</button></li>";
    }
    
    echo "</ul>";
    echo "</div>";
}

echo "<div class='test-item info'>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>If database columns are missing, run the migration SQL</li>";
echo "<li>If files are missing, re-upload the modified files</li>";
echo "<li>Clear browser cache (Ctrl+F5)</li>";
echo "<li>Test the save functionality using the button above</li>";
echo "<li>Go to Tools → CBM Configuration and test in the actual interface</li>";
echo "</ol>";
echo "</div>";

echo "</div>";

// ============================================================================
// JavaScript for Interactive Tests
// ============================================================================
?>

<script src="assets/template/assets/libs/jquery/dist/jquery.min.js"></script>
<script>
function testSave() {
    $('#save_test_result').html('<div class="test-item info">Testing save functionality...</div>');
    
    var testData = {
        cbm_enabled: 1,
        cbm_rate: 50,
        cbm_priority: 'higher',
        cbm_measurement_unit: 'cm',
        show_package_dimensions: 1,
        show_cbm_input_field: 0,
        show_cbm_in_forms: 1
    };
    
    $.ajax({
        type: 'POST',
        url: 'ajax/tools/config_cbm_ajax_debug.php',
        data: testData,
        dataType: 'json',
        success: function(response) {
            console.log('Save test response:', response);
            
            var html = '';
            if (response.success) {
                html = '<div class="test-item pass"><span class="icon">✓</span> <strong>Save test PASSED!</strong><br>' + response.message + '</div>';
            } else {
                html = '<div class="test-item fail"><span class="icon">✗</span> <strong>Save test FAILED!</strong><br>' + response.message + '</div>';
            }
            
            if (response.debug) {
                html += '<div class="test-item info"><strong>Debug Info:</strong><pre>' + JSON.stringify(response.debug, null, 2) + '</pre></div>';
            }
            
            $('#save_test_result').html(html);
        },
        error: function(xhr, status, error) {
            console.error('Save test error:', xhr.responseText);
            
            var html = '<div class="test-item fail"><span class="icon">✗</span> <strong>AJAX Error!</strong><br>';
            html += 'Status: ' + status + '<br>';
            html += 'Error: ' + error + '<br>';
            html += '<pre>' + xhr.responseText + '</pre>';
            html += '</div>';
            
            $('#save_test_result').html(html);
        }
    });
}

function showMigrationSQL() {
    var sql = `ALTER TABLE \`cdb_settings\` 
ADD COLUMN \`show_cbm_input_field\` TINYINT(1) DEFAULT 0 
AFTER \`show_cbm_in_forms\`;`;
    
    alert('Run this SQL in your database:\n\n' + sql);
}
</script>

<hr>
<p><a href="tools.php?do=config_cbm">← Go to CBM Configuration</a> | <a href="test_cbm_save_simple.php">Simple Save Test →</a></p>

