<?php
// Test password verification
$password = "password";
$hash = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy';

echo "Testing password: " . $password . "\n";
echo "Hash: " . $hash . "\n";
echo "Verification result: " . (password_verify($password, $hash) ? "SUCCESS" : "FAILED") . "\n\n";

// Generate new hash
$new_hash = password_hash($password, PASSWORD_BCRYPT);
echo "New generated hash: " . $new_hash . "\n";
?>
