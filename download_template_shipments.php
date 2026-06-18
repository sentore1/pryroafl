<?php
// *************************************************************************
// *                                                                       *
// * DEPRIXA PRO -  Integrated Web Shipping System                         *
// * Copyright (c) JAOMWEB. All Rights Reserved                            *
// *                                                                       *
// *************************************************************************

require_once("loader.php");

$user = new User();
$core = new Core();

if (!$user->cdp_loginCheck()) {
    header("location: login.php");
    exit;
}

// Create CSV template
$filename = "shipments_bulk_upload_template_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Check if CBM mode is enabled
$cbm_enabled = isset($core->cbm_calculation_enabled) && $core->cbm_calculation_enabled == 1;

// Headers - conditional based on CBM mode
if ($cbm_enabled) {
    // CBM Mode: Include cbm column instead of dimensions
    $headers = array(
        'sender_email',
        'sender_fname',
        'sender_lname',
        'recipient_email',
        'recipient_fname',
        'recipient_lname',
        'tracking_prefix',
        'tracking_number',
        'item_description',
        'weight',
        'cbm',
        'sender_country',
        'sender_city',
        'sender_address',
        'recipient_country',
        'recipient_city',
        'recipient_address'
    );
} else {
    // Dimension Mode: Include length, width, height
    $headers = array(
        'sender_email',
        'sender_fname',
        'sender_lname',
        'recipient_email',
        'recipient_fname',
        'recipient_lname',
        'tracking_prefix',
        'tracking_number',
        'item_description',
        'weight',
        'length',
        'width',
        'height',
        'sender_country',
        'sender_city',
        'sender_address',
        'recipient_country',
        'recipient_city',
        'recipient_address'
    );
}

fputcsv($output, $headers);

// Sample data rows - conditional based on CBM mode
if ($cbm_enabled) {
    // CBM Mode samples
    $sample_data = array(
        array('client1@company.com', 'John', 'Smith', 'customer1@email.com', 'Jane', 'Doe', 'CDPE', '200001', 'Electronics - Laptop', '2.5', '0.0075', 'USA', 'New York', '123 Main St', 'Canada', 'Toronto', '456 Oak Ave'),
        array('client2@business.com', 'Michael', 'Johnson', 'customer2@email.com', 'Sarah', 'Williams', 'CDPE', '200002', 'Clothing - 5 Shirts', '4.0', '0.0240', 'USA', 'Los Angeles', '789 Pine Rd', 'Mexico', 'Mexico City', '321 Elm St'),
        array('client3@shop.com', 'David', 'Brown', 'customer3@email.com', 'Emily', 'Davis', 'CDPE', '200003', 'Documents', '6.5', '0.0540', 'Canada', 'Vancouver', '555 Maple Dr', 'USA', 'Seattle', '777 Cedar Ln')
    );
} else {
    // Dimension Mode samples
    $sample_data = array(
        array('client1@company.com', 'John', 'Smith', 'customer1@email.com', 'Jane', 'Doe', 'CDPE', '200001', 'Electronics - Laptop', '2.5', '15', '10', '5', 'USA', 'New York', '123 Main St', 'Canada', 'Toronto', '456 Oak Ave'),
        array('client2@business.com', 'Michael', 'Johnson', 'customer2@email.com', 'Sarah', 'Williams', 'CDPE', '200002', 'Clothing - 5 Shirts', '4.0', '20', '15', '8', 'USA', 'Los Angeles', '789 Pine Rd', 'Mexico', 'Mexico City', '321 Elm St'),
        array('client3@shop.com', 'David', 'Brown', 'customer3@email.com', 'Emily', 'Davis', 'CDPE', '200003', 'Documents', '6.5', '25', '18', '12', 'Canada', 'Vancouver', '555 Maple Dr', 'USA', 'Seattle', '777 Cedar Ln')
    );
}

foreach ($sample_data as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>
