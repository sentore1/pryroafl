<?php
// *************************************************************************
// *                                                                       *
// * CBM Settings Save Debug Tool                                         *
// * This helps debug what data is being sent when saving CBM settings    *
// *                                                                       *
// *************************************************************************

require_once("loader.php");

$user = new User;
$core = new Core;

// Check if user is admin
if (!$user->cdp_is_Admin()) {
    die("Access denied. Admin privileges required.");
}

echo "<style>
    .success { background: #4CAF50; color: white; padding: 15px; margin: 10px 0; }
    .error { background: #f44336; color: white; padding: 15px; margin: 10px 0; }
    .warning { background: #ff9800; color: white; padding: 15px; margin: 10px 0; }
    .info { background: #2196F3; color: white; padding: 15px; margin: 10px 0; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #f2f2f2; }
    .test-section { background: #f9f9f9; padding: 20px; margin: 20px 0; border: 1px solid #ddd; }
</style>";

echo "<h2>🔧 CBM Settings Save Debug Tool</h2>";
echo "<hr>";

// Step 1: Check database columns
echo "<h3>Step 1: Database Column Check</h3>";
$db = new Conexion;

$columns_to_check = [
    'cbm_calculation_enabled',
    'cbm_rate_per_cubic_meter',
    'cbm_vs_weight_priority',
    'cbm_measurement_unit',
    'show_package_dimensions',
    'show_cbm_input_field',
    'show_cbm_in_forms'
];

$missing_columns = [];
foreach ($columns_to_check as $column) {
    $db->cdp_query("SHOW COLUMNS FROM cdb_settings LIKE '$column'");
    $db->cdp_execute();
    if ($db->cdp_rowCount() == 0) {
        $missing_columns[] = $column;
    }
}

if (empty($missing_columns)) {
    echo "<div class='success'>✓ All required columns exist!</div>";
} else {
    echo "<div class='error'>✗ Missing columns: " . implode(', ', $missing_columns) . "</div>";
    echo "<div class='warning'>⚠ You need to run the database migration first!</div>";
    echo "<p><strong>Run this SQL:</strong></p>";
    echo "<pre style='background: #f4f4f4; padding: 15px; border: 1px solid #ddd;'>";
    foreach ($missing_columns as $col) {
        if ($col == 'show_cbm_input_field') {
            echo "ALTER TABLE `cdb_settings` ADD COLUMN `$col` TINYINT(1) DEFAULT 0 AFTER `show_cbm_in_forms`;\n";
        }
    }
    echo "</pre>";
}

echo "<hr>";

// Step 2: Show current settings
echo "<h3>Step 2: Current Settings in Database</h3>";
$db->cdp_query("SELECT * FROM cdb_settings WHERE id = 1");
$db->cdp_execute();
$settings = $db->cdp_registro();

echo "<table>";
echo "<tr><th>Setting</th><th>Current Value</th><th>Status</th></tr>";

$setting_checks = [
    ['cbm_calculation_enabled', 'CBM Enabled', $settings->cbm_calculation_enabled ?? 'NULL'],
    ['cbm_rate_per_cubic_meter', 'CBM Rate', $settings->cbm_rate_per_cubic_meter ?? 'NULL'],
    ['cbm_vs_weight_priority', 'Priority', $settings->cbm_vs_weight_priority ?? 'NULL'],
    ['cbm_measurement_unit', 'Unit', $settings->cbm_measurement_unit ?? 'NULL'],
    ['show_package_dimensions', 'Show Dimensions', $settings->show_package_dimensions ?? 'NULL'],
    ['show_cbm_input_field', 'Show CBM Input', $settings->show_cbm_input_field ?? 'NULL'],
    ['show_cbm_in_forms', 'Show CBM Total', $settings->show_cbm_in_forms ?? 'NULL'],
];

foreach ($setting_checks as $check) {
    $status = $check[2] !== 'NULL' ? '✓' : '✗';
    $color = $check[2] !== 'NULL' ? 'green' : 'red';
    echo "<tr>";
    echo "<td><strong>{$check[1]}</strong><br><small>{$check[0]}</small></td>";
    echo "<td>{$check[2]}</td>";
    echo "<td style='color: $color;'>$status</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";

// Step 3: Test Core class
echo "<h3>Step 3: Core Class Property Check</h3>";
$core_checks = [
    'cbm_calculation_enabled',
    'cbm_rate_per_cubic_meter',
    'cbm_vs_weight_priority',
    'cbm_measurement_unit',
    'show_package_dimensions',
    'show_cbm_input_field',
    'show_cbm_in_forms'
];

$core_issues = [];
foreach ($core_checks as $prop) {
    if (!property_exists($core, $prop)) {
        $core_issues[] = $prop;
    }
}

if (empty($core_issues)) {
    echo "<div class='success'>✓ Core class has all required properties!</div>";
} else {
    echo "<div class='error'>✗ Core class missing properties: " . implode(', ', $core_issues) . "</div>";
    echo "<div class='warning'>⚠ Check if lib/Core.php has been updated!</div>";
}

echo "<hr>";

// Step 4: Interactive test form
echo "<h3>Step 4: Test Save Functionality</h3>";
echo "<p>Use this form to test if saving works. Toggle the switches and click 'Test Save'.</p>";

?>

<form id="test_form" method="post">
    <div style="background: #f4f4f4; padding: 20px; margin: 20px 0;">
        <h4>Test Checkboxes:</h4>
        
        <div style="margin: 10px 0;">
            <label>
                <input type="checkbox" name="cbm_enabled" value="1" <?php echo ($settings->cbm_calculation_enabled == 1) ? 'checked' : ''; ?>>
                Enable CBM Calculations
            </label>
        </div>
        
        <div style="margin: 10px 0;">
            <label>
                <input type="checkbox" name="show_package_dimensions" value="1" <?php echo ($settings->show_package_dimensions == 1) ? 'checked' : ''; ?>>
                Show Package Dimensions
            </label>
        </div>
        
        <div style="margin: 10px 0;">
            <label>
                <input type="checkbox" name="show_cbm_input_field" value="1" <?php echo ($settings->show_cbm_input_field == 1) ? 'checked' : ''; ?>>
                Show CBM Input Field
            </label>
        </div>
        
        <div style="margin: 10px 0;">
            <label>
                <input type="checkbox" name="show_cbm_in_forms" value="1" <?php echo ($settings->show_cbm_in_forms == 1) ? 'checked' : ''; ?>>
                Show CBM in Forms
            </label>
        </div>
        
        <div style="margin: 10px 0;">
            <label>CBM Rate:</label>
            <input type="number" name="cbm_rate" value="<?php echo $settings->cbm_rate_per_cubic_meter ?? 0; ?>" step="0.01">
        </div>
        
        <div style="margin: 10px 0;">
            <label>Priority:</label>
            <select name="cbm_priority">
                <option value="weight" <?php echo ($settings->cbm_vs_weight_priority == 'weight') ? 'selected' : ''; ?>>Weight</option>
                <option value="cbm" <?php echo ($settings->cbm_vs_weight_priority == 'cbm') ? 'selected' : ''; ?>>CBM</option>
                <option value="higher" <?php echo ($settings->cbm_vs_weight_priority == 'higher') ? 'selected' : ''; ?>>Higher</option>
            </select>
        </div>
        
        <div style="margin: 10px 0;">
            <label>Measurement Unit:</label>
            <select name="cbm_measurement_unit">
                <option value="cm" <?php echo ($settings->cbm_measurement_unit == 'cm') ? 'selected' : ''; ?>>Centimeters</option>
                <option value="inch" <?php echo ($settings->cbm_measurement_unit == 'inch') ? 'selected' : ''; ?>>Inches</option>
                <option value="m" <?php echo ($settings->cbm_measurement_unit == 'm') ? 'selected' : ''; ?>>Meters</option>
            </select>
        </div>
        
        <button type="button" onclick="testSubmit()" style="padding: 10px 20px; background: #4CAF50; color: white; border: none; cursor: pointer;">
            Test Submit (Show Data)
        </button>
        
        <button type="submit" style="padding: 10px 20px; background: #2196F3; color: white; border: none; cursor: pointer; margin-left: 10px;">
            Actually Save
        </button>
    </div>
</form>

<div id="debug_output" style="background: #fff3cd; padding: 20px; margin: 20px 0; display: none;">
    <h4>Data Being Sent:</h4>
    <pre id="debug_data" style="background: white; padding: 10px; border: 1px solid #ddd;"></pre>
</div>

<div id="save_result" style="margin: 20px 0;"></div>

<script src="assets/template/assets/libs/jquery/dist/jquery.min.js"></script>
<script>
function testSubmit() {
    var formData = $('#test_form').serialize();
    $('#debug_output').show();
    $('#debug_data').text(formData);
    
    // Also show as object
    var formObject = {};
    $('#test_form').serializeArray().forEach(function(item) {
        formObject[item.name] = item.value;
    });
    
    $('#debug_data').text(
        'Serialized String:\n' + formData + '\n\n' +
        'As Object:\n' + JSON.stringify(formObject, null, 2) + '\n\n' +
        'Note: Unchecked checkboxes are NOT included!'
    );
}

$('#test_form').on('submit', function(e) {
    e.preventDefault();
    
    var formData = $(this).serialize();
    
    $('#save_result').html('<p style="color: blue;">Saving...</p>');
    
    $.ajax({
        type: 'POST',
        url: 'ajax/tools/config_cbm_ajax.php',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#save_result').html(
                    '<div style="background: #4CAF50; color: white; padding: 15px;">' +
                    '<strong>✓ Success!</strong> ' + response.message +
                    '</div>'
                );
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                $('#save_result').html(
                    '<div style="background: #f44336; color: white; padding: 15px;">' +
                    '<strong>✗ Error!</strong> ' + response.message +
                    '</div>'
                );
            }
        },
        error: function(xhr, status, error) {
            $('#save_result').html(
                '<div style="background: #f44336; color: white; padding: 15px;">' +
                '<strong>✗ AJAX Error!</strong><br>' +
                'Status: ' + status + '<br>' +
                'Error: ' + error + '<br>' +
                'Response: ' + xhr.responseText +
                '</div>'
            );
        }
    });
});
</script>

<?php
// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    echo "<hr>";
    echo "<h3>POST Data Received:</h3>";
    echo "<pre style='background: #f4f4f4; padding: 15px;'>";
    print_r($_POST);
    echo "</pre>";
}
?>

<hr>
<p><a href="tools.php?do=config_cbm">← Back to CBM Configuration</a></p>
