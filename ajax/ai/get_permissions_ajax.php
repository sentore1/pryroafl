<?php
/**
 * Get AI Permissions - Returns current AI permissions for display in settings panel
 */
header('Content-Type: application/json');
require_once("../../loader.php");
require_once("ai_permissions_helper.php");

$user = new User;
if (!$user->cdp_is_Admin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $perms = new AIPermissions();
    $permissions = $perms->getAllPermissions();
    
    echo json_encode([
        'success' => true,
        'permissions' => $permissions
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading permissions: ' . $e->getMessage()
    ]);
}
?>
