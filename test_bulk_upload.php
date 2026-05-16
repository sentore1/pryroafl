<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("loader.php");

$db = new Conexion;
$user = new User;
$core = new Core;
$userData = $user->cdp_getUserData();

echo "<h1>Bulk Upload Test Script</h1>";
echo "<hr>";

// Test data
$test_data = array(
    array(
        'sender_email' => 'testclient@test.com',
        'recipient_email' => 'testcustomer@test.com',
        'tracking_prefix' => 'TEST',
        'tracking_number' => '999001',
        'weight' => '5.5',
        'length' => '10',
        'width' => '8',
        'height' => '6'
    )
);

echo "<h2>Step 1: Test Data</h2>";
echo "<pre>" . print_r($test_data[0], true) . "</pre>";

foreach ($test_data as $index => $row) {
    $rowNumber = $index + 2;
    
    echo "<h2>Step 2: Processing Row $rowNumber</h2>";
    
    try {
        // Trim all values
        $row = array_map('trim', $row);
        
        // Validate required fields
        if (empty($row['sender_email'])) {
            throw new Exception('Sender email is required');
        }
        if (empty($row['recipient_email'])) {
            throw new Exception('Recipient email is required');
        }
        if (empty($row['tracking_number'])) {
            throw new Exception('Tracking number is required');
        }
        
        echo "✓ Validation passed<br>";
        
        // Find or create sender
        $sender_email = trim($row['sender_email']);
        echo "<h3>Creating/Finding Sender: $sender_email</h3>";
        
        $db->cdp_query("SELECT id, fname, lname FROM cdb_users WHERE email = :email AND userlevel = 1");
        $db->bind(':email', $sender_email);
        $db->cdp_execute();
        $sender = $db->cdp_registro();
        
        if (!$sender) {
            echo "Sender not found, creating new...<br>";
            $sender_name_parts = explode('@', $sender_email);
            $sender_fname = ucfirst($sender_name_parts[0]);
            $sender_lname = 'Client';
            
            $db->cdp_query("INSERT INTO cdb_users (email, fname, lname, userlevel, created, active) 
                           VALUES (:email, :fname, :lname, 1, NOW(), 1)");
            $db->bind(':email', $sender_email);
            $db->bind(':fname', $sender_fname);
            $db->bind(':lname', $sender_lname);
            $db->cdp_execute();
            
            $sender_id = $db->dbh->lastInsertId();
            echo "✓ Created sender with ID: $sender_id<br>";
            
            $sender = (object)[
                'id' => $sender_id,
                'fname' => $sender_fname,
                'lname' => $sender_lname
            ];
        } else {
            echo "✓ Found existing sender with ID: {$sender->id}<br>";
        }
        
        // Find or create recipient
        $recipient_email = trim($row['recipient_email']);
        echo "<h3>Creating/Finding Recipient: $recipient_email</h3>";
        
        $db->cdp_query("SELECT id, fname, lname FROM cdb_users WHERE email = :email");
        $db->bind(':email', $recipient_email);
        $db->cdp_execute();
        $recipient = $db->cdp_registro();
        
        if (!$recipient) {
            echo "Recipient not found, creating new...<br>";
            $recipient_name_parts = explode('@', $recipient_email);
            $recipient_fname = ucfirst($recipient_name_parts[0]);
            $recipient_lname = 'Customer';
            
            $db->cdp_query("INSERT INTO cdb_users (email, fname, lname, userlevel, created, active) 
                           VALUES (:email, :fname, :lname, 9, NOW(), 1)");
            $db->bind(':email', $recipient_email);
            $db->bind(':fname', $recipient_fname);
            $db->bind(':lname', $recipient_lname);
            $db->cdp_execute();
            
            $recipient_id = $db->dbh->lastInsertId();
            echo "✓ Created recipient with ID: $recipient_id<br>";
            
            $recipient = (object)[
                'id' => $recipient_id,
                'fname' => $recipient_fname,
                'lname' => $recipient_lname
            ];
        } else {
            echo "✓ Found existing recipient with ID: {$recipient->id}<br>";
        }
        
        // Check for existing shipment
        $tracking_prefix = isset($row['tracking_prefix']) ? trim($row['tracking_prefix']) : '';
        $tracking_number = trim($row['tracking_number']);
        
        echo "<h3>Checking for existing shipment: {$tracking_prefix}{$tracking_number}</h3>";
        
        $db->cdp_query("SELECT order_id, order_prefix, order_no, is_consolidate FROM cdb_add_order 
                       WHERE order_no = :order_no AND order_prefix = :order_prefix");
        $db->bind(':order_no', $tracking_number);
        $db->bind(':order_prefix', $tracking_prefix);
        $db->cdp_execute();
        $shipment = $db->cdp_registro();
        
        if (!$shipment) {
            echo "Shipment not found, creating new...<br>";
            
            // Calculate dimensions
            $weight = isset($row['weight']) ? floatval($row['weight']) : 1;
            $length = isset($row['length']) ? floatval($row['length']) : 1;
            $width = isset($row['width']) ? floatval($row['width']) : 1;
            $height = isset($row['height']) ? floatval($row['height']) : 1;
            $volumetric_weight = ($length * $width * $height) / 5000;
            $total_weight = $weight + $volumetric_weight;
            
            echo "Weight: $weight, Length: $length, Width: $width, Height: $height<br>";
            echo "Volumetric Weight: $volumetric_weight, Total Weight: $total_weight<br>";
            
            // Create recipient record in cdb_recipients
            echo "<h4>Creating recipient record in cdb_recipients...</h4>";
            $db->cdp_query("INSERT INTO cdb_recipients (
                fname, lname, email, phone, country, city, address, 
                sender_id, created
            ) VALUES (
                :fname, :lname, :email, '', '', '', '',
                :sender_id, NOW()
            )");
            $db->bind(':fname', $recipient->fname);
            $db->bind(':lname', $recipient->lname);
            $db->bind(':email', $recipient_email);
            $db->bind(':sender_id', $sender->id);
            
            try {
                $db->cdp_execute();
                $recipient_record_id = $db->dbh->lastInsertId();
                echo "✓ Created recipient record with ID: $recipient_record_id<br>";
            } catch (Exception $e) {
                echo "✗ ERROR creating recipient record: " . $e->getMessage() . "<br>";
                throw $e;
            }
            
            // Create shipment order
            echo "<h4>Creating shipment order in cdb_add_order...</h4>";
            $sql = "INSERT INTO cdb_add_order (
                order_prefix, order_no, sender_id, receiver_id, 
                order_date, status_courier, is_consolidate, order_incomplete,
                order_weight, order_length, order_width, order_height,
                volumetric_weight, order_courier, order_service_options, 
                order_pay_mode, order_deli_time, order_package, 
                order_item_category, agency, origin_off, user_id,
                is_pickup, due_date, status_invoice, total_order,
                sub_total, tax_value, total_tax, tax_discount, total_tax_discount,
                declared_value, total_declared_value, total_fixed_value,
                total_insured_value, tax_insurance_value, total_tax_insurance,
                tax_custom_tariffis_value, total_tax_custom_tariffis,
                total_reexp, total_weight, value_weight, volumetric_percentage
            ) VALUES (
                :order_prefix, :order_no, :sender_id, :receiver_id,
                NOW(), 1, 0, 1,
                :weight, :length, :width, :height,
                :volumetric_weight, 1, 1, 
                1, 1, 1,
                1, 1, 1, :user_id,
                0, NOW(), 2, 0,
                0, 0, 0, 0, 0,
                0, 0, 0,
                0, 0, 0,
                0, 0,
                0, :total_weight, 0, 5000
            )";
            
            $db->cdp_query($sql);
            $db->bind(':order_prefix', $tracking_prefix);
            $db->bind(':order_no', $tracking_number);
            $db->bind(':sender_id', $sender->id);
            $db->bind(':receiver_id', $recipient_record_id);
            $db->bind(':weight', $weight);
            $db->bind(':length', $length);
            $db->bind(':width', $width);
            $db->bind(':height', $height);
            $db->bind(':volumetric_weight', $volumetric_weight);
            $db->bind(':user_id', $userData->id);
            $db->bind(':total_weight', $total_weight);
            
            try {
                $result = $db->cdp_execute();
                if ($result === false) {
                    $errorInfo = $db->dbh->errorInfo();
                    echo "✗ Execute returned false. PDO Error: " . print_r($errorInfo, true) . "<br>";
                    throw new Exception("Database execute failed: " . $errorInfo[2]);
                }
                $shipment_id = $db->dbh->lastInsertId();
                if ($shipment_id == 0) {
                    $errorInfo = $db->dbh->errorInfo();
                    echo "✗ lastInsertId returned 0. PDO Error: " . print_r($errorInfo, true) . "<br>";
                    throw new Exception("Failed to get insert ID. Error: " . $errorInfo[2]);
                }
                echo "✓ Created shipment with ID: $shipment_id<br>";
            } catch (Exception $e) {
                echo "✗ ERROR creating shipment: " . $e->getMessage() . "<br>";
                echo "SQL: " . $sql . "<br>";
                throw $e;
            }
            
            // Create address record
            echo "<h4>Creating address record in cdb_address_shipments...</h4>";
            $db->cdp_query("INSERT INTO cdb_address_shipments (
                order_id, order_track, 
                sender_country, sender_state, sender_city, sender_zip_code, sender_address,
                recipient_country, recipient_state, recipient_city, recipient_zip_code, recipient_address
            ) VALUES (
                :order_id, :order_track,
                'N/A', 'N/A', 'N/A', '', '',
                'N/A', 'N/A', 'N/A', '', ''
            )");
            $db->bind(':order_id', $shipment_id);
            $db->bind(':order_track', $tracking_prefix . $tracking_number);
            
            try {
                $db->cdp_execute();
                echo "✓ Created address record<br>";
            } catch (Exception $e) {
                echo "✗ ERROR creating address record: " . $e->getMessage() . "<br>";
                throw $e;
            }
            
            // Create package record
            echo "<h4>Creating package record in cdb_add_order_item...</h4>";
            $db->cdp_query("INSERT INTO cdb_add_order_item (
                order_id, order_item_description, order_item_quantity,
                order_item_weight, order_item_length, order_item_width, order_item_height,
                order_item_declared_value, order_item_fixed_value
            ) VALUES (
                :order_id, 'Package', 1,
                :weight, :length, :width, :height,
                0, 0
            )");
            $db->bind(':order_id', $shipment_id);
            $db->bind(':weight', $weight);
            $db->bind(':length', $length);
            $db->bind(':width', $width);
            $db->bind(':height', $height);
            
            try {
                $db->cdp_execute();
                echo "✓ Created package record<br>";
            } catch (Exception $e) {
                echo "✗ ERROR creating package record: " . $e->getMessage() . "<br>";
                throw $e;
            }
            
            echo "<h3 style='color: green;'>✓ SUCCESS! Shipment created with ID: $shipment_id</h3>";
            
        } else {
            echo "✓ Shipment already exists with ID: {$shipment->order_id}<br>";
        }
        
    } catch (Exception $e) {
        echo "<h3 style='color: red;'>✗ ERROR: " . $e->getMessage() . "</h3>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}

echo "<hr>";
echo "<h2>Step 3: Verify Database</h2>";

// Check if shipment was created
$db->cdp_query("SELECT order_id, order_prefix, order_no, sender_id, receiver_id, status_courier 
                FROM cdb_add_order 
                ORDER BY order_id DESC 
                LIMIT 5");
$db->cdp_execute();
$shipments = $db->cdp_registros();

echo "<h3>Recent Shipments:</h3>";
if (count($shipments) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Tracking</th><th>Sender ID</th><th>Receiver ID</th><th>Status</th></tr>";
    foreach ($shipments as $ship) {
        echo "<tr>";
        echo "<td>{$ship->order_id}</td>";
        echo "<td>{$ship->order_prefix}{$ship->order_no}</td>";
        echo "<td>{$ship->sender_id}</td>";
        echo "<td>{$ship->receiver_id}</td>";
        echo "<td>{$ship->status_courier}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No shipments found in database!</p>";
}

echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<ul>";
echo "<li><a href='courier_list.php'>Go to Courier List</a></li>";
echo "<li><a href='debug_shipments.php'>Go to Debug Page</a></li>";
echo "<li><a href='test_bulk_upload.php'>Run Test Again</a></li>";
echo "</ul>";
?>
