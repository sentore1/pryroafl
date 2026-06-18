<?php
// Simple connection test for P-AI system
// Access: http://localhost/pryroafl/ajax/ai/test_connection.php

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$tests = [
    'timestamp' => date('Y-m-d H:i:s'),
    'loader' => false,
    'database' => false,
    'user_class' => false,
    'permissions_class' => false,
    'api_key' => false,
    'errors' => []
];

// Test 1: Can we load the main loader file?
try {
    require_once("../../loader.php");
    $tests['loader'] = true;
} catch (Exception $e) {
    $tests['errors'][] = "Loader error: " . $e->getMessage();
}

// Test 2: Can we connect to database?
try {
    $db = new Conexion;
    $db->cdp_query("SELECT 1 as test");
    $db->cdp_execute();
    $result = $db->cdp_registro();
    if ($result && $result->test == 1) {
        $tests['database'] = true;
    }
} catch (Exception $e) {
    $tests['errors'][] = "Database error: " . $e->getMessage();
}

// Test 3: Can we create User object?
try {
    $user = new User;
    $tests['user_class'] = true;
    $userData = $user->cdp_getUserData();
    $tests['user_id'] = $userData ? $userData->id : null;
    $tests['user_level'] = $userData ? $userData->userlevel : null;
    $tests['is_admin'] = $user->cdp_is_Admin();
} catch (Exception $e) {
    $tests['errors'][] = "User class error: " . $e->getMessage();
}

// Test 4: Can we load permissions helper?
try {
    require_once("ai_permissions_helper.php");
    $perms = new AIPermissions();
    $tests['permissions_class'] = true;
    $tests['autopilot_enabled'] = $perms->isAutopilotEnabled();
} catch (Exception $e) {
    $tests['errors'][] = "Permissions class error: " . $e->getMessage();
}

// Test 5: Can we get API key from database?
try {
    $db->cdp_query("SELECT ai_provider, groq_api_key, openai_api_key FROM cdb_settings LIMIT 1");
    $db->cdp_execute();
    $row = $db->cdp_registro();
    if ($row) {
        $tests['api_key_configured'] = !empty($row->groq_api_key) || !empty($row->openai_api_key);
        $tests['provider'] = $row->ai_provider ?? 'groq';
        $tests['groq_key_length'] = !empty($row->groq_api_key) ? strlen($row->groq_api_key) : 0;
        $tests['openai_key_length'] = !empty($row->openai_api_key) ? strlen($row->openai_api_key) : 0;
        
        // Show first/last 4 chars of key for verification
        if (!empty($row->groq_api_key)) {
            $key = $row->groq_api_key;
            $tests['groq_key_preview'] = substr($key, 0, 8) . '...' . substr($key, -4);
        }
        if (!empty($row->openai_api_key)) {
            $key = $row->openai_api_key;
            $tests['openai_key_preview'] = substr($key, 0, 8) . '...' . substr($key, -4);
        }
    }
} catch (Exception $e) {
    $tests['errors'][] = "API key check error: " . $e->getMessage();
}

// Test 6: PHP configuration
$tests['php_version'] = phpversion();
$tests['curl_enabled'] = function_exists('curl_init');
$tests['json_enabled'] = function_exists('json_encode');
$tests['session_started'] = session_status() === PHP_SESSION_ACTIVE;

// Overall status
$tests['overall_status'] = (
    $tests['loader'] && 
    $tests['database'] && 
    $tests['user_class'] && 
    $tests['permissions_class'] &&
    $tests['curl_enabled']
) ? 'READY' : 'ERROR';

echo json_encode($tests, JSON_PRETTY_PRINT);
