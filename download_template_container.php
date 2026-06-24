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
$filename = "container_bulk_upload_template_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Determine which measurement mode is active
$show_dimensions = isset($core->show_package_dimensions) ? (int)$core->show_package_dimensions : 1;
$show_cbm_input  = isset($core->show_cbm_input_field)    ? (int)$core->show_cbm_input_field    : 0;

// Build headers based on active mode
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
);

if ($show_dimensions) {
    $headers[] = 'length';
    $headers[] = 'width';
    $headers[] = 'height';
}
if ($show_cbm_input) {
    $headers[] = 'cbm';
}

$headers = array_merge($headers, array(
    'sender_country',
    'sender_city',
    'sender_address',
    'recipient_country',
    'recipient_city',
    'recipient_address',
));

fputcsv($output, $headers);

// Sample data rows
if ($show_dimensions && !$show_cbm_input) {
    $sample_data = array(
        array('client1@company.com', 'John', 'Smith', 'customer1@email.com', 'Jane', 'Doe', 'CDPE', '100001', 'Electronics - Laptop', '2.5', '15', '10', '5', 'USA', 'New York', '123 Main St', 'Canada', 'Toronto', '456 Oak Ave'),
        array('client2@business.com', 'Michael', 'Johnson', 'customer2@email.com', 'Sarah', 'Williams', 'CDPE', '100002', 'Clothing - 5 Shirts', '4.0', '20', '15', '8', 'USA', 'Los Angeles', '789 Pine Rd', 'Mexico', 'Mexico City', '321 Elm St'),
    );
} elseif (!$show_dimensions && $show_cbm_input) {
    $sample_data = array(
        array('client1@company.com', 'John', 'Smith', 'customer1@email.com', 'Jane', 'Doe', 'CDPE', '100001', 'Electronics - Laptop', '2.5', '0.0075', 'USA', 'New York', '123 Main St', 'Canada', 'Toronto', '456 Oak Ave'),
        array('client2@business.com', 'Michael', 'Johnson', 'customer2@email.com', 'Sarah', 'Williams', 'CDPE', '100002', 'Clothing - 5 Shirts', '4.0', '0.0240', 'USA', 'Los Angeles', '789 Pine Rd', 'Mexico', 'Mexico City', '321 Elm St'),
    );
} else {
    // Both enabled
    $sample_data = array(
        array('client1@company.com', 'John', 'Smith', 'customer1@email.com', 'Jane', 'Doe', 'CDPE', '100001', 'Electronics - Laptop', '2.5', '15', '10', '5', '0.0075', 'USA', 'New York', '123 Main St', 'Canada', 'Toronto', '456 Oak Ave'),
        array('client2@business.com', 'Michael', 'Johnson', 'customer2@email.com', 'Sarah', 'Williams', 'CDPE', '100002', 'Clothing - 5 Shirts', '4.0', '20', '15', '8', '0.0240', 'USA', 'Los Angeles', '789 Pine Rd', 'Mexico', 'Mexico City', '321 Elm St'),
    );
}

foreach ($sample_data as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>
