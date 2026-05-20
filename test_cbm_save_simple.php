<?php
require_once("loader.php");

$user = new User;
if (!$user->cdp_is_Admin()) {
    die("Access denied");
}

$core = new Core;
?>
<!DOCTYPE html>
<html>
<head>
    <title>CBM Save Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .success { background: #4CAF50; color: white; padding: 15px; margin: 10px 0; }
        .error { background: #f44336; color: white; padding: 15px; margin: 10px 0; }
        .info { background: #2196F3; color: white; padding: 15px; margin: 10px 0; }
        .form-group { margin: 15px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; }
        label { display: block; margin: 10px 0; font-weight: bold; }
        input[type="checkbox"] { margin-right: 10px; }
        button { padding: 12px 24px; font-size: 16px; cursor: pointer; margin: 5px; }
        .btn-save { background: #4CAF50; color: white; border: none; }
        .btn-debug { background: #2196F3; color: white; border: none; }
        pre { background: #f4f4f4; padding: 15px; border: 1px solid #ddd; overflow: auto; }
    </style>
</head>
<body>
    <h1>🔧 CBM Settings Save Test</h1>
    
    <div class="info">
        <strong>Current Values:</strong><br>
        show_package_dimensions: <?php echo $core->show_package_dimensions ?? 'NULL'; ?><br>
        show_cbm_input_field: <?php echo $core->show_cbm_input_field ?? 'NULL'; ?><br>
        show_cbm_in_forms: <?php echo $core->show_cbm_in_forms ?? 'NULL'; ?>
    </div>

    <form id="testForm">
        <div class="form-group">
            <h3>Toggle These Settings:</h3>
            
            <label>
                <input type="checkbox" name="cbm_enabled" value="1" <?php echo ($core->cbm_calculation_enabled == 1) ? 'checked' : ''; ?>>
                Enable CBM Calculations
            </label>
            
            <label>
                <input type="checkbox" name="show_package_dimensions" value="1" <?php echo ($core->show_package_dimensions == 1) ? 'checked' : ''; ?>>
                Show Package Dimensions (L × W × H)
            </label>
            
            <label>
                <input type="checkbox" name="show_cbm_input_field" value="1" <?php echo ($core->show_cbm_input_field == 1) ? 'checked' : ''; ?>>
                Show CBM Input Field
            </label>
            
            <label>
                <input type="checkbox" name="show_cbm_in_forms" value="1" <?php echo ($core->show_cbm_in_forms == 1) ? 'checked' : ''; ?>>
                Show CBM in Forms
            </label>
        </div>
        
        <div class="form-group">
            <label>CBM Rate:</label>
            <input type="number" name="cbm_rate" value="<?php echo $core->cbm_rate_per_cubic_meter ?? 0; ?>" step="0.01">
            
            <label>Priority:</label>
            <select name="cbm_priority">
                <option value="weight" <?php echo ($core->cbm_vs_weight_priority == 'weight') ? 'selected' : ''; ?>>Weight</option>
                <option value="cbm" <?php echo ($core->cbm_vs_weight_priority == 'cbm') ? 'selected' : ''; ?>>CBM</option>
                <option value="higher" <?php echo ($core->cbm_vs_weight_priority == 'higher') ? 'selected' : ''; ?>>Higher</option>
            </select>
            
            <label>Measurement Unit:</label>
            <select name="cbm_measurement_unit">
                <option value="cm" <?php echo ($core->cbm_measurement_unit == 'cm') ? 'selected' : ''; ?>>Centimeters</option>
                <option value="inch" <?php echo ($core->cbm_measurement_unit == 'inch') ? 'selected' : ''; ?>>Inches</option>
                <option value="m" <?php echo ($core->cbm_measurement_unit == 'm') ? 'selected' : ''; ?>>Meters</option>
            </select>
        </div>
        
        <button type="button" class="btn-debug" onclick="showData()">Show Data (Debug)</button>
        <button type="button" class="btn-save" onclick="saveSettings()">Save Settings</button>
    </form>
    
    <div id="result"></div>

    <script src="assets/template/assets/libs/jquery/dist/jquery.min.js"></script>
    <script>
    function showData() {
        var formData = $('#testForm').serialize();
        var formArray = $('#testForm').serializeArray();
        
        var html = '<div class="info"><h3>Data Being Sent:</h3>';
        html += '<strong>Serialized:</strong><br>' + formData + '<br><br>';
        html += '<strong>As Array:</strong><pre>' + JSON.stringify(formArray, null, 2) + '</pre>';
        html += '<p><strong>Note:</strong> Unchecked checkboxes are NOT included in the data!</p>';
        html += '</div>';
        
        $('#result').html(html);
    }
    
    function saveSettings() {
        var formData = $('#testForm').serialize();
        
        $('#result').html('<div class="info">Saving...</div>');
        
        $.ajax({
            type: 'POST',
            url: 'ajax/tools/config_cbm_ajax_debug.php',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response);
                
                var html = '';
                if (response.success) {
                    html = '<div class="success"><h3>✓ Success!</h3>' + response.message + '</div>';
                } else {
                    html = '<div class="error"><h3>✗ Error!</h3>' + response.message + '</div>';
                }
                
                // Show debug info
                if (response.debug) {
                    html += '<div class="info"><h3>Debug Information:</h3>';
                    html += '<pre>' + JSON.stringify(response.debug, null, 2) + '</pre>';
                    html += '</div>';
                }
                
                $('#result').html(html);
                
                if (response.success) {
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                
                var html = '<div class="error">';
                html += '<h3>✗ AJAX Error!</h3>';
                html += '<strong>Status:</strong> ' + status + '<br>';
                html += '<strong>Error:</strong> ' + error + '<br>';
                html += '<strong>Response:</strong><pre>' + xhr.responseText + '</pre>';
                html += '</div>';
                
                $('#result').html(html);
            }
        });
    }
    </script>
</body>
</html>
