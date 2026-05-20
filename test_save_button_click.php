<?php
require_once("loader.php");
$user = new User;
if (!$user->cdp_is_Admin()) die("Access denied");
$core = new Core;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Save Button Click Test</title>
    <style>
        body { font-family: Arial; padding: 20px; max-width: 800px; margin: 0 auto; }
        .log { background: #f4f4f4; padding: 10px; margin: 10px 0; border-left: 4px solid #2196F3; }
        .error { border-left-color: #f44336; background: #ffebee; }
        .success { border-left-color: #4CAF50; background: #e8f5e9; }
        button { padding: 12px 24px; font-size: 16px; margin: 10px 5px; cursor: pointer; }
        .btn-test { background: #2196F3; color: white; border: none; }
        .btn-save { background: #4CAF50; color: white; border: none; }
        pre { background: white; padding: 10px; border: 1px solid #ddd; overflow: auto; }
    </style>
</head>
<body>
    <h1>🔍 Save Button Click Diagnostic</h1>
    
    <div class="log">
        <strong>Current Settings:</strong><br>
        show_package_dimensions: <?php echo $core->show_package_dimensions ?? 'NULL'; ?><br>
        show_cbm_input_field: <?php echo $core->show_cbm_input_field ?? 'NULL'; ?><br>
        show_cbm_in_forms: <?php echo $core->show_cbm_in_forms ?? 'NULL'; ?>
    </div>

    <h2>Test Form</h2>
    <form id="save_config_cbm">
        <label>
            <input type="checkbox" name="cbm_enabled" value="1" checked>
            Enable CBM
        </label><br>
        
        <label>
            <input type="checkbox" name="show_package_dimensions" value="1" <?php echo ($core->show_package_dimensions == 1) ? 'checked' : ''; ?>>
            Show Package Dimensions
        </label><br>
        
        <label>
            <input type="checkbox" name="show_cbm_input_field" value="1" <?php echo ($core->show_cbm_input_field == 1) ? 'checked' : ''; ?>>
            Show CBM Input Field
        </label><br>
        
        <label>
            <input type="checkbox" name="show_cbm_in_forms" value="1" <?php echo ($core->show_cbm_in_forms == 1) ? 'checked' : ''; ?>>
            Show CBM in Forms
        </label><br><br>
        
        <input type="number" name="cbm_rate" value="50" step="0.01"><br>
        <select name="cbm_priority">
            <option value="higher">Higher</option>
            <option value="weight">Weight</option>
            <option value="cbm">CBM</option>
        </select><br>
        <select name="cbm_measurement_unit">
            <option value="cm">CM</option>
            <option value="inch">Inch</option>
            <option value="m">M</option>
        </select><br><br>
        
        <button type="button" class="btn-test" onclick="testFormData()">1. Test Form Data</button>
        <button type="submit" class="btn-save">2. Submit Form</button>
    </form>

    <div id="log"></div>

    <script src="assets/template/assets/libs/jquery/dist/jquery.min.js"></script>
    <script>
    var logDiv = document.getElementById('log');
    
    function addLog(message, type) {
        type = type || 'log';
        var div = document.createElement('div');
        div.className = type;
        div.innerHTML = '<strong>' + new Date().toLocaleTimeString() + ':</strong> ' + message;
        logDiv.appendChild(div);
        console.log(message);
    }
    
    function testFormData() {
        addLog('Testing form data...', 'log');
        
        var formData = $('#save_config_cbm').serialize();
        addLog('Serialized data: ' + formData, 'log');
        
        var formArray = $('#save_config_cbm').serializeArray();
        addLog('<pre>' + JSON.stringify(formArray, null, 2) + '</pre>', 'log');
        
        // Check checkboxes specifically
        var checkboxes = {
            cbm_enabled: $('input[name="cbm_enabled"]').is(':checked'),
            show_package_dimensions: $('input[name="show_package_dimensions"]').is(':checked'),
            show_cbm_input_field: $('input[name="show_cbm_input_field"]').is(':checked'),
            show_cbm_in_forms: $('input[name="show_cbm_in_forms"]').is(':checked')
        };
        
        addLog('Checkbox states: <pre>' + JSON.stringify(checkboxes, null, 2) + '</pre>', 'log');
    }
    
    // Check if jQuery is loaded
    if (typeof jQuery === 'undefined') {
        addLog('ERROR: jQuery is NOT loaded!', 'error');
    } else {
        addLog('✓ jQuery is loaded (version ' + jQuery.fn.jquery + ')', 'success');
    }
    
    // Check if form exists
    if ($('#save_config_cbm').length === 0) {
        addLog('ERROR: Form #save_config_cbm not found!', 'error');
    } else {
        addLog('✓ Form #save_config_cbm found', 'success');
    }
    
    // Attach submit handler
    jQuery(document).ready(function($) {
        addLog('✓ Document ready fired', 'success');
        
        $("#save_config_cbm").on('submit', function(event) {
            event.preventDefault();
            addLog('Form submit event triggered!', 'success');
            
            var data = $(this).serialize();
            addLog('Sending data: ' + data, 'log');
            
            $.ajax({
                type: "POST",
                url: "ajax/tools/config_cbm_ajax_debug.php",
                data: data,
                dataType: 'json',
                beforeSend: function() {
                    addLog('AJAX request starting...', 'log');
                },
                success: function(response) {
                    addLog('AJAX Success!', 'success');
                    addLog('<pre>' + JSON.stringify(response, null, 2) + '</pre>', 'success');
                    
                    if (response.success) {
                        addLog('✓ Save successful! Page will reload in 2 seconds...', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    }
                },
                error: function(xhr, status, error) {
                    addLog('AJAX Error!', 'error');
                    addLog('Status: ' + status, 'error');
                    addLog('Error: ' + error, 'error');
                    addLog('Response: <pre>' + xhr.responseText + '</pre>', 'error');
                }
            });
        });
        
        addLog('✓ Submit handler attached', 'success');
    });
    </script>
</body>
</html>
