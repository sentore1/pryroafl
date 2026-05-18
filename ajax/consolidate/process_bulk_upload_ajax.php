<?php
// *************************************************************************
// *                                                                       *
// * DEPRIXA PRO -  Integrated Web Shipping System                         *
// * Copyright (c) JAOMWEB. All Rights Reserved                            *
// *                                                                       *
// *************************************************************************

require_once("../../loader.php");
require_once("../../helpers/querys.php");

$db = new Conexion;
$user = new User;
$core = new Core;
$userData = $user->cdp_getUserData();

if (!isset($_FILES['bulk_file'])) {
    echo '<div class="alert alert-danger">No file uploaded</div>';
    exit;
}

$file = $_FILES['bulk_file'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileError = $file['error'];

if ($fileError !== 0) {
    echo '<div class="alert alert-danger">Error uploading file</div>';
    exit;
}

$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Process CSV
if ($fileExt === 'csv') {
    $data = array();
    if (($handle = fopen($fileTmpName, 'r')) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ',');
        
        // Clean headers from BOM and whitespace
        $headers = array_map(function($header) {
            // Remove BOM
            $header = str_replace("\xEF\xBB\xBF", '', $header);
            // Trim whitespace
            return trim($header);
        }, $headers);
        
        while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
            if (count($headers) === count($row)) {
                $data[] = array_combine($headers, $row);
            }
        }
        fclose($handle);
    }
}
// Process Excel
elseif ($fileExt === 'xlsx' || $fileExt === 'xls') {
    require_once('../../helpers/phpexcel/vendor/autoload.php');
    
    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileTmpName);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        $headers = array_shift($rows);
        
        // Clean headers from BOM and whitespace
        $headers = array_map(function($header) {
            // Remove BOM
            $header = str_replace("\xEF\xBB\xBF", '', $header);
            // Trim whitespace
            return trim($header);
        }, $headers);
        
        $data = array();
        
        foreach ($rows as $row) {
            if (!empty(array_filter($row))) {
                if (count($headers) === count($row)) {
                    $data[] = array_combine($headers, $row);
                }
            }
        }
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">Error reading Excel file: ' . $e->getMessage() . '</div>';
        exit;
    }
} else {
    echo '<div class="alert alert-danger">Invalid file format. Please upload CSV or Excel file.</div>';
    exit;
}

if (empty($data)) {
    echo '<div class="alert alert-danger">No data found in file</div>';
    exit;
}

// Debug: Show what headers were detected (remove this after testing)
// echo '<div class="alert alert-info">Headers detected: ' . implode(', ', array_keys($data[0])) . '</div>';

// Process the data
$successCount = 0;
$errorCount = 0;
$errors = array();

echo '<div class="alert alert-info">Processing ' . count($data) . ' records...</div>';
echo '<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Row</th><th>Tracking</th><th>Sender</th><th>Recipient</th><th>Status</th></tr></thead><tbody>';

foreach ($data as $index => $row) {
    $rowNumber = $index + 2; // +2 because index starts at 0 and we have header row
    
    try {
        // Debug: trim all values
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
        
        // Find or create sender by email
        $db->cdp_query("SELECT id, fname, lname FROM cdb_users WHERE email = :email AND userlevel = 1");
        $db->bind(':email', trim($row['sender_email']));
        $db->cdp_execute();
        $sender = $db->cdp_registro();
        
        if (!$sender) {
            // Create new sender (client) using provided names or fallback to email-based names
            $sender_email = trim($row['sender_email']);
            
            // Use provided names if available, otherwise generate from email
            if (!empty($row['sender_fname']) && !empty($row['sender_lname'])) {
                $sender_fname = trim($row['sender_fname']);
                $sender_lname = trim($row['sender_lname']);
            } else {
                $sender_name_parts = explode('@', $sender_email);
                $sender_fname = ucfirst($sender_name_parts[0]);
                $sender_lname = 'Client';
            }
            
            $db->cdp_query("INSERT INTO cdb_users (email, fname, lname, userlevel, created, active) 
                           VALUES (:email, :fname, :lname, 1, NOW(), 1)");
            $db->bind(':email', $sender_email);
            $db->bind(':fname', $sender_fname);
            $db->bind(':lname', $sender_lname);
            $db->cdp_execute();
            
            $sender_id = $db->dbh->lastInsertId();
            
            // Create sender object
            $sender = (object)[
                'id' => $sender_id,
                'fname' => $sender_fname,
                'lname' => $sender_lname
            ];
        }
        
        // Find or create recipient by email
        $recipient_email = trim($row['recipient_email']);
        
        $db->cdp_query("SELECT id, fname, lname FROM cdb_users WHERE email = :email");
        $db->bind(':email', $recipient_email);
        $db->cdp_execute();
        $recipient = $db->cdp_registro();
        
        if (!$recipient) {
            // Create new recipient (customer) using provided names or fallback to email-based names
            $recipient_email = trim($row['recipient_email']);
            
            // Use provided names if available, otherwise generate from email
            if (!empty($row['recipient_fname']) && !empty($row['recipient_lname'])) {
                $recipient_fname = trim($row['recipient_fname']);
                $recipient_lname = trim($row['recipient_lname']);
            } else {
                $recipient_name_parts = explode('@', $recipient_email);
                $recipient_fname = ucfirst($recipient_name_parts[0]);
                $recipient_lname = 'Customer';
            }
            
            $db->cdp_query("INSERT INTO cdb_users (email, fname, lname, userlevel, created, active) 
                           VALUES (:email, :fname, :lname, 9, NOW(), 1)");
            $db->bind(':email', $recipient_email);
            $db->bind(':fname', $recipient_fname);
            $db->bind(':lname', $recipient_lname);
            $db->cdp_execute();
            
            $recipient_id = $db->dbh->lastInsertId();
            
            // Create recipient object
            $recipient = (object)[
                'id' => $recipient_id,
                'fname' => $recipient_fname,
                'lname' => $recipient_lname
            ];
        }
        
        // Find or create shipment by tracking number
        $tracking_prefix = isset($row['tracking_prefix']) ? trim($row['tracking_prefix']) : '';
        $tracking_number = trim($row['tracking_number']);
        
        $db->cdp_query("SELECT order_id, order_prefix, order_no, is_consolidate FROM cdb_add_order 
                       WHERE order_no = :order_no AND order_prefix = :order_prefix");
        $db->bind(':order_no', $tracking_number);
        $db->bind(':order_prefix', $tracking_prefix);
        $db->cdp_execute();
        $shipment = $db->cdp_registro();
        
        if (!$shipment) {
            // Calculate dimensions before creating records
            $weight = isset($row['weight']) ? floatval($row['weight']) : 1;
            $length = isset($row['length']) ? floatval($row['length']) : 1;
            $width = isset($row['width']) ? floatval($row['width']) : 1;
            $height = isset($row['height']) ? floatval($row['height']) : 1;
            $item_description = isset($row['item_description']) && !empty($row['item_description']) ? trim($row['item_description']) : 'Package';
            $volumetric_weight = ($length * $width * $height) / 5000;
            $total_weight = $weight + $volumetric_weight;
            
            // Calculate CBM (Cubic Meter)
            $cbm = cdp_calculateCBM($length, $width, $height, 'cm');
            
            // Create recipient record in cdb_recipients table
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
            $db->cdp_execute();
            
            $recipient_record_id = $db->dbh->lastInsertId();
            
            // Create shipment order with all required fields (without individual weight/dimension columns)
            $db->cdp_query("INSERT INTO cdb_add_order (
                order_prefix, order_no, sender_id, receiver_id, 
                order_date, status_courier, is_consolidate, order_incomplete,
                order_courier, order_service_options, 
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
                1, 1, 
                1, 1, 1,
                1, 1, 1, :user_id,
                0, NOW(), 2, 0,
                0, 0, 0, 0, 0,
                0, 0, 0,
                0, 0, 0,
                0, 0,
                0, :total_weight, 0, 5000
            )");
            
            $db->bind(':order_prefix', $tracking_prefix);
            $db->bind(':order_no', $tracking_number);
            $db->bind(':sender_id', $sender->id);
            $db->bind(':receiver_id', $recipient_record_id);
            $db->bind(':user_id', $userData->id);
            $db->bind(':total_weight', $total_weight);
            $db->cdp_execute();
            
            $shipment_id = $db->dbh->lastInsertId();
            
            // Get origin and destination data from the row
            $sender_country = isset($row['sender_country']) && !empty($row['sender_country']) ? trim($row['sender_country']) : 'N/A';
            $sender_city = isset($row['sender_city']) && !empty($row['sender_city']) ? trim($row['sender_city']) : 'N/A';
            $sender_address = isset($row['sender_address']) && !empty($row['sender_address']) ? trim($row['sender_address']) : '';
            $recipient_country = isset($row['recipient_country']) && !empty($row['recipient_country']) ? trim($row['recipient_country']) : 'N/A';
            $recipient_city = isset($row['recipient_city']) && !empty($row['recipient_city']) ? trim($row['recipient_city']) : 'N/A';
            $recipient_address = isset($row['recipient_address']) && !empty($row['recipient_address']) ? trim($row['recipient_address']) : '';
            
            // Create address shipment record with proper structure
            $db->cdp_query("INSERT INTO cdb_address_shipments (
                order_id, order_track, 
                sender_country, sender_state, sender_city, sender_zip_code, sender_address,
                recipient_country, recipient_state, recipient_city, recipient_zip_code, recipient_address
            ) VALUES (
                :order_id, :order_track,
                :sender_country, 'N/A', :sender_city, '', :sender_address,
                :recipient_country, 'N/A', :recipient_city, '', :recipient_address
            )");
            $db->bind(':order_id', $shipment_id);
            $db->bind(':order_track', $tracking_prefix . $tracking_number);
            $db->bind(':sender_country', $sender_country);
            $db->bind(':sender_city', $sender_city);
            $db->bind(':sender_address', $sender_address);
            $db->bind(':recipient_country', $recipient_country);
            $db->bind(':recipient_city', $recipient_city);
            $db->bind(':recipient_address', $recipient_address);
            $db->cdp_execute();
            
            // Create package record with CBM
            $db->cdp_query("INSERT INTO cdb_add_order_item (
                order_id, order_item_description, order_item_quantity,
                order_item_weight, order_item_length, order_item_width, order_item_height,
                order_item_declared_value, order_item_fixed_value, cbm
            ) VALUES (
                :order_id, :item_description, 1,
                :weight, :length, :width, :height,
                0, 0, :cbm
            )");
            $db->bind(':order_id', $shipment_id);
            $db->bind(':item_description', $item_description);
            $db->bind(':weight', $weight);
            $db->bind(':length', $length);
            $db->bind(':width', $width);
            $db->bind(':height', $height);
            $db->bind(':cbm', $cbm);
            $db->cdp_execute();
            
            // Update order with total CBM
            $db->cdp_query("UPDATE cdb_add_order SET total_cbm = :cbm WHERE order_id = :order_id");
            $db->bind(':cbm', $cbm);
            $db->bind(':order_id', $shipment_id);
            $db->cdp_execute();
            
            // Create shipment object
            $shipment = (object)[
                'order_id' => $shipment_id,
                'order_prefix' => $tracking_prefix,
                'order_no' => $tracking_number,
                'is_consolidate' => 0
            ];
        } else {
            // Check if already consolidated
            if ($shipment->is_consolidate == 1) {
                throw new Exception('Shipment already consolidated: ' . $tracking_prefix . $tracking_number);
            }
        }
        
        $successCount++;
        echo '<tr class="table-success">';
        echo '<td>' . $rowNumber . '</td>';
        echo '<td>' . $tracking_prefix . $tracking_number . '</td>';
        echo '<td>' . htmlspecialchars($sender->fname . ' ' . $sender->lname) . '</td>';
        echo '<td>' . htmlspecialchars($recipient->fname . ' ' . $recipient->lname) . '</td>';
        echo '<td><span class="badge badge-success">Valid</span></td>';
        echo '</tr>';
        
    } catch (Exception $e) {
        $errorCount++;
        $errors[] = 'Row ' . $rowNumber . ': ' . $e->getMessage();
        
        // Get the actual values for debugging
        $sender_display = isset($row['sender_email']) && !empty($row['sender_email']) ? htmlspecialchars($row['sender_email']) : 'N/A (empty or missing)';
        $recipient_display = isset($row['recipient_email']) && !empty($row['recipient_email']) ? htmlspecialchars($row['recipient_email']) : 'N/A (empty or missing)';
        $tracking_display = (isset($row['tracking_prefix']) ? $row['tracking_prefix'] : '') . (isset($row['tracking_number']) ? $row['tracking_number'] : 'N/A');
        
        echo '<tr class="table-danger">';
        echo '<td>' . $rowNumber . '</td>';
        echo '<td>' . $tracking_display . '</td>';
        echo '<td>' . $sender_display . '</td>';
        echo '<td>' . $recipient_display . '</td>';
        echo '<td><span class="badge badge-danger">' . htmlspecialchars($e->getMessage()) . '</span></td>';
        echo '</tr>';
    }
}

echo '</tbody></table></div>';

echo '<div class="alert alert-' . ($errorCount > 0 ? 'warning' : 'success') . '">';
echo '<h5>Processing Complete</h5>';
echo '<p><strong>Success:</strong> ' . $successCount . ' records</p>';
echo '<p><strong>Errors:</strong> ' . $errorCount . ' records</p>';

if ($successCount > 0) {
    echo '<hr>';
    echo '<p>Valid shipments have been identified. You can now proceed to create the container.</p>';
    echo '<a href="consolidate_add.php" class="btn btn-success"><i class="fas fa-plus"></i> Create Container with Valid Shipments</a>';
}

echo '</div>';

if (!empty($errors)) {
    echo '<div class="alert alert-danger">';
    echo '<h5>Error Details:</h5>';
    echo '<ul>';
    foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul>';
    echo '</div>';
}
?>
