<?php
// *************************************************************************
// *                                                                       *
// * DEPRIXA PRO -  Integrated Web Shipping System                         *
// * Copyright (c) JAOMWEB. All Rights Reserved                            *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * Email: support@jaom.info                                              *
// * Website: http://www.jaom.info                                         *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * This software is furnished under a license and may be used and copied *
// * only  in  accordance  with  the  terms  of such  license and with the *
// * inclusion of the above copyright notice.                              *
// *                                                                       *
// *************************************************************************

require_once("../../loader.php");
require_once("../../helpers/querys.php");

$user = new User;
$core = new Core;
$db = new Conexion;

// Check if user is admin
if (!$user->cdp_is_Admin()) {
    $response = array(
        'success' => false,
        'message' => 'Access denied. Admin privileges required.'
    );
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$action = isset($_POST['action']) ? cdp_sanitize($_POST['action']) : '';

switch ($action) {
    
    // Get tier details
    case 'get':
        $tier_id = intval($_POST['id']);
        
        $db->cdp_query("SELECT * FROM cdb_cbm_pricing_tiers WHERE id = :id");
        $db->bind(':id', $tier_id);
        $tier = $db->cdp_registro();
        
        if ($tier) {
            $response = array(
                'success' => true,
                'data' => $tier
            );
        } else {
            $response = array(
                'success' => false,
                'message' => 'Pricing tier not found'
            );
        }
        break;
    
    // Add new tier
    case 'add':
        $tier_name = cdp_sanitize($_POST['tier_name']);
        $min_cbm = floatval($_POST['tier_min_cbm']);
        $max_cbm = floatval($_POST['tier_max_cbm']);
        $rate = floatval($_POST['tier_rate']);
        $fixed = floatval($_POST['tier_fixed']);
        $active = intval($_POST['tier_active']);
        
        // Validate
        if (empty($tier_name)) {
            $response = array('success' => false, 'message' => 'Tier name is required');
            break;
        }
        
        if ($min_cbm < 0 || $rate < 0 || $fixed < 0) {
            $response = array('success' => false, 'message' => 'Values must be positive numbers');
            break;
        }
        
        if ($max_cbm > 0 && $max_cbm <= $min_cbm) {
            $response = array('success' => false, 'message' => 'Maximum CBM must be greater than minimum CBM');
            break;
        }
        
        // Check for overlapping tiers
        $db->cdp_query("
            SELECT COUNT(*) as count FROM cdb_cbm_pricing_tiers 
            WHERE active = 1 
            AND (
                (min_cbm <= :min_cbm AND (max_cbm >= :min_cbm OR max_cbm = 0))
                OR
                (min_cbm <= :max_cbm AND (max_cbm >= :max_cbm OR max_cbm = 0))
                OR
                (min_cbm >= :min_cbm2 AND max_cbm <= :max_cbm2 AND max_cbm > 0)
            )
        ");
        $db->bind(':min_cbm', $min_cbm);
        $db->bind(':min_cbm2', $min_cbm);
        $db->bind(':max_cbm', $max_cbm > 0 ? $max_cbm : 999999);
        $db->bind(':max_cbm2', $max_cbm > 0 ? $max_cbm : 999999);
        $overlap = $db->cdp_registro();
        
        if ($overlap->count > 0) {
            $response = array('success' => false, 'message' => 'This tier overlaps with an existing active tier');
            break;
        }
        
        // Insert new tier
        $db->cdp_query("
            INSERT INTO cdb_cbm_pricing_tiers 
            (tier_name, min_cbm, max_cbm, rate_per_cbm, fixed_charge, active, created_at) 
            VALUES 
            (:name, :min, :max, :rate, :fixed, :active, NOW())
        ");
        
        $db->bind(':name', $tier_name);
        $db->bind(':min', $min_cbm);
        $db->bind(':max', $max_cbm);
        $db->bind(':rate', $rate);
        $db->bind(':fixed', $fixed);
        $db->bind(':active', $active);
        
        if ($db->cdp_execute()) {
            $response = array(
                'success' => true,
                'message' => 'Pricing tier added successfully!'
            );
        } else {
            $response = array(
                'success' => false,
                'message' => 'Error adding pricing tier'
            );
        }
        break;
    
    // Update tier
    case 'update':
        $tier_id = intval($_POST['tier_id']);
        $tier_name = cdp_sanitize($_POST['tier_name']);
        $min_cbm = floatval($_POST['tier_min_cbm']);
        $max_cbm = floatval($_POST['tier_max_cbm']);
        $rate = floatval($_POST['tier_rate']);
        $fixed = floatval($_POST['tier_fixed']);
        $active = intval($_POST['tier_active']);
        
        // Validate
        if (empty($tier_name)) {
            $response = array('success' => false, 'message' => 'Tier name is required');
            break;
        }
        
        if ($min_cbm < 0 || $rate < 0 || $fixed < 0) {
            $response = array('success' => false, 'message' => 'Values must be positive numbers');
            break;
        }
        
        if ($max_cbm > 0 && $max_cbm <= $min_cbm) {
            $response = array('success' => false, 'message' => 'Maximum CBM must be greater than minimum CBM');
            break;
        }
        
        // Check for overlapping tiers (excluding current tier)
        $db->cdp_query("
            SELECT COUNT(*) as count FROM cdb_cbm_pricing_tiers 
            WHERE active = 1 
            AND id != :id
            AND (
                (min_cbm <= :min_cbm AND (max_cbm >= :min_cbm OR max_cbm = 0))
                OR
                (min_cbm <= :max_cbm AND (max_cbm >= :max_cbm OR max_cbm = 0))
                OR
                (min_cbm >= :min_cbm2 AND max_cbm <= :max_cbm2 AND max_cbm > 0)
            )
        ");
        $db->bind(':id', $tier_id);
        $db->bind(':min_cbm', $min_cbm);
        $db->bind(':min_cbm2', $min_cbm);
        $db->bind(':max_cbm', $max_cbm > 0 ? $max_cbm : 999999);
        $db->bind(':max_cbm2', $max_cbm > 0 ? $max_cbm : 999999);
        $overlap = $db->cdp_registro();
        
        if ($overlap->count > 0) {
            $response = array('success' => false, 'message' => 'This tier overlaps with an existing active tier');
            break;
        }
        
        // Update tier
        $db->cdp_query("
            UPDATE cdb_cbm_pricing_tiers 
            SET 
                tier_name = :name,
                min_cbm = :min,
                max_cbm = :max,
                rate_per_cbm = :rate,
                fixed_charge = :fixed,
                active = :active,
                updated_at = NOW()
            WHERE id = :id
        ");
        
        $db->bind(':id', $tier_id);
        $db->bind(':name', $tier_name);
        $db->bind(':min', $min_cbm);
        $db->bind(':max', $max_cbm);
        $db->bind(':rate', $rate);
        $db->bind(':fixed', $fixed);
        $db->bind(':active', $active);
        
        if ($db->cdp_execute()) {
            $response = array(
                'success' => true,
                'message' => 'Pricing tier updated successfully!'
            );
        } else {
            $response = array(
                'success' => false,
                'message' => 'Error updating pricing tier'
            );
        }
        break;
    
    // Toggle tier status
    case 'toggle':
        $tier_id = intval($_POST['id']);
        $new_status = intval($_POST['status']);
        
        $db->cdp_query("
            UPDATE cdb_cbm_pricing_tiers 
            SET active = :status, updated_at = NOW()
            WHERE id = :id
        ");
        
        $db->bind(':id', $tier_id);
        $db->bind(':status', $new_status);
        
        if ($db->cdp_execute()) {
            $status_text = $new_status == 1 ? 'activated' : 'deactivated';
            $response = array(
                'success' => true,
                'message' => 'Pricing tier ' . $status_text . ' successfully!'
            );
        } else {
            $response = array(
                'success' => false,
                'message' => 'Error updating tier status'
            );
        }
        break;
    
    // Delete tier
    case 'delete':
        $tier_id = intval($_POST['id']);
        
        $db->cdp_query("DELETE FROM cdb_cbm_pricing_tiers WHERE id = :id");
        $db->bind(':id', $tier_id);
        
        if ($db->cdp_execute()) {
            $response = array(
                'success' => true,
                'message' => 'Pricing tier deleted successfully!'
            );
        } else {
            $response = array(
                'success' => false,
                'message' => 'Error deleting pricing tier'
            );
        }
        break;
    
    default:
        $response = array(
            'success' => false,
            'message' => 'Invalid action'
        );
        break;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
