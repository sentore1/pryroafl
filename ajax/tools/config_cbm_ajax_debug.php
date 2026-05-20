<?php
// *************************************************************************
// *                                                                       *
// * CBM AJAX Debug - See exactly what's being received                   *
// *                                                                       *
// *************************************************************************

// Log everything to a file for debugging
$log_file = '../../cbm_debug_log.txt';
$log_data = date('Y-m-d H:i:s') . "\n";
$log_data .= "POST Data:\n";
$log_data .= print_r($_POST, true);
$log_data .= "\n---\n\n";
file_put_contents($log_file, $log_data, FILE_APPEND);

require_once("../../loader.php");
require_once("../../helpers/querys.php");

$user = new User;
$core = new Core;
$errors = array();

// Return detailed debug info
$debug_info = array(
    'received_post' => $_POST,
    'is_admin' => $user->cdp_is_Admin(),
    'timestamp' => date('Y-m-d H:i:s')
);

// Check if user is admin
if (!$user->cdp_is_Admin()) {
    $response = array(
        'success' => false,
        'message' => 'Access denied. Admin privileges required.',
        'debug' => $debug_info
    );
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Get form data
$cbm_enabled = isset($_POST['cbm_enabled']) ? 1 : 0;
$cbm_rate = isset($_POST['cbm_rate']) ? floatval($_POST['cbm_rate']) : 0;
$cbm_priority = isset($_POST['cbm_priority']) ? cdp_sanitize($_POST['cbm_priority']) : 'higher';
$cbm_measurement_unit = isset($_POST['cbm_measurement_unit']) ? cdp_sanitize($_POST['cbm_measurement_unit']) : 'cm';
$show_package_dimensions = isset($_POST['show_package_dimensions']) ? 1 : 0;
$show_cbm_input_field = isset($_POST['show_cbm_input_field']) ? 1 : 0;
$show_cbm_in_forms = isset($_POST['show_cbm_in_forms']) ? 1 : 0;

$debug_info['parsed_values'] = array(
    'cbm_enabled' => $cbm_enabled,
    'cbm_rate' => $cbm_rate,
    'cbm_priority' => $cbm_priority,
    'cbm_measurement_unit' => $cbm_measurement_unit,
    'show_package_dimensions' => $show_package_dimensions,
    'show_cbm_input_field' => $show_cbm_input_field,
    'show_cbm_in_forms' => $show_cbm_in_forms
);

// Validate inputs
if ($cbm_rate < 0) {
    $errors['cbm_rate'] = 'CBM rate must be a positive number';
}

if (!in_array($cbm_priority, ['weight', 'cbm', 'higher'])) {
    $errors['cbm_priority'] = 'Invalid charge priority selected';
}

if (!in_array($cbm_measurement_unit, ['cm', 'inch', 'm'])) {
    $errors['cbm_measurement_unit'] = 'Invalid measurement unit selected';
}

// Validate that at least one input method is enabled
if ($show_package_dimensions == 0 && $show_cbm_input_field == 0) {
    $errors['input_method'] = 'At least one input method (Dimensions or CBM) must be enabled';
}

$debug_info['validation_errors'] = $errors;

// If no errors, update settings
if (empty($errors)) {
    
    $db = new Conexion;
    
    // Check which columns exist
    $db->cdp_query("SHOW COLUMNS FROM cdb_settings LIKE 'cbm_measurement_unit'");
    $db->cdp_execute();
    $has_unit_column = $db->cdp_rowCount() > 0;
    
    $db->cdp_query("SHOW COLUMNS FROM cdb_settings LIKE 'show_package_dimensions'");
    $db->cdp_execute();
    $has_show_dims = $db->cdp_rowCount() > 0;
    
    $db->cdp_query("SHOW COLUMNS FROM cdb_settings LIKE 'show_cbm_in_forms'");
    $db->cdp_execute();
    $has_show_cbm = $db->cdp_rowCount() > 0;
    
    $db->cdp_query("SHOW COLUMNS FROM cdb_settings LIKE 'show_cbm_input_field'");
    $db->cdp_execute();
    $has_cbm_input = $db->cdp_rowCount() > 0;
    
    $debug_info['column_checks'] = array(
        'has_unit_column' => $has_unit_column,
        'has_show_dims' => $has_show_dims,
        'has_show_cbm' => $has_show_cbm,
        'has_cbm_input' => $has_cbm_input
    );
    
    // Build UPDATE query
    $update_fields = array(
        'cbm_calculation_enabled = :enabled',
        'cbm_rate_per_cubic_meter = :rate',
        'cbm_vs_weight_priority = :priority'
    );
    
    if ($has_unit_column) {
        $update_fields[] = 'cbm_measurement_unit = :unit';
    }
    if ($has_show_dims) {
        $update_fields[] = 'show_package_dimensions = :show_dims';
    }
    if ($has_show_cbm) {
        $update_fields[] = 'show_cbm_in_forms = :show_cbm';
    }
    if ($has_cbm_input) {
        $update_fields[] = 'show_cbm_input_field = :cbm_input';
    }
    
    $sql = "UPDATE cdb_settings SET " . implode(', ', $update_fields) . " WHERE id = 1";
    
    $debug_info['sql_query'] = $sql;
    $debug_info['sql_params'] = array(
        'enabled' => $cbm_enabled,
        'rate' => $cbm_rate,
        'priority' => $cbm_priority,
        'unit' => $cbm_measurement_unit,
        'show_dims' => $show_package_dimensions,
        'show_cbm' => $show_cbm_in_forms,
        'cbm_input' => $show_cbm_input_field
    );
    
    $db->cdp_query($sql);
    $db->bind(':enabled', $cbm_enabled);
    $db->bind(':rate', $cbm_rate);
    $db->bind(':priority', $cbm_priority);
    
    if ($has_unit_column) {
        $db->bind(':unit', $cbm_measurement_unit);
    }
    if ($has_show_dims) {
        $db->bind(':show_dims', $show_package_dimensions);
    }
    if ($has_show_cbm) {
        $db->bind(':show_cbm', $show_cbm_in_forms);
    }
    if ($has_cbm_input) {
        $db->bind(':cbm_input', $show_cbm_input_field);
    }
    
    $execute_result = $db->cdp_execute();
    $debug_info['execute_result'] = $execute_result;
    
    if ($execute_result) {
        
        // Verify the update
        $db->cdp_query("SELECT show_package_dimensions, show_cbm_input_field, show_cbm_in_forms FROM cdb_settings WHERE id = 1");
        $db->cdp_execute();
        $verify = $db->cdp_registro();
        
        $debug_info['after_update'] = array(
            'show_package_dimensions' => $verify->show_package_dimensions ?? 'NULL',
            'show_cbm_input_field' => $verify->show_cbm_input_field ?? 'NULL',
            'show_cbm_in_forms' => $verify->show_cbm_in_forms ?? 'NULL'
        );
        
        $message = 'CBM settings saved successfully!';
        
        if (!$has_unit_column || !$has_show_dims || !$has_show_cbm || !$has_cbm_input) {
            $message .= ' Note: Some columns are missing.';
        }
        
        $response = array(
            'success' => true,
            'message' => $message,
            'debug' => $debug_info
        );
        
    } else {
        $response = array(
            'success' => false,
            'message' => 'Database execute failed. Check debug info.',
            'debug' => $debug_info
        );
    }
    
} else {
    $response = array(
        'success' => false,
        'message' => 'Validation errors: ' . implode(', ', $errors),
        'debug' => $debug_info
    );
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>
