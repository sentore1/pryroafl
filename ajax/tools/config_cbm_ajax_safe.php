<?php
// Safe version that checks if columns exist before updating
require_once("../../loader.php");
require_once("../../helpers/querys.php");

$user = new User;
$core = new Core;
$errors = array();

// Check if user is admin
if ($user->cdp_is_Admin()) {

    // Get form data
    $cbm_enabled = isset($_POST['cbm_enabled']) ? 1 : 0;
    $cbm_rate = floatval($_POST['cbm_rate']);
    $cbm_priority = cdp_sanitize($_POST['cbm_priority']);
    $cbm_measurement_unit = isset($_POST['cbm_measurement_unit']) ? cdp_sanitize($_POST['cbm_measurement_unit']) : 'cm';
    $show_package_dimensions = isset($_POST['show_package_dimensions']) ? 1 : 0;
    $show_cbm_in_forms = isset($_POST['show_cbm_in_forms']) ? 1 : 0;

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
        
        // Build UPDATE query based on available columns
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
        
        $sql = "UPDATE cdb_settings SET " . implode(', ', $update_fields) . " WHERE id = 1";
        
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
        
        if ($db->cdp_execute()) {
            
            $message = 'CBM settings saved successfully!';
            
            if (!$has_unit_column || !$has_show_dims || !$has_show_cbm) {
                $message .= ' Note: Some new features require database migration.';
            }
            
            $response = array(
                'success' => true,
                'message' => $message,
                'columns_missing' => !($has_unit_column && $has_show_dims && $has_show_cbm)
            );
            
        } else {
            $response = array(
                'success' => false,
                'message' => 'Error saving settings. Please try again.'
            );
        }
        
    } else {
        $response = array(
            'success' => false,
            'message' => 'Validation errors: ' . implode(', ', $errors)
        );
    }

} else {
    $response = array(
        'success' => false,
        'message' => 'Access denied. Admin privileges required.'
    );
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
