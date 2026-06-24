<?php
// *************************************************************************
// *                                                                       *
// * DEPRIXA PRO -  Integrated Web Shipping System                         *
// *                                                                       *
// * Dynamic CSV template download for package rows (in-form import).      *
// * Columns adapt to the active dimension / CBM setting.                  *
// *                                                                       *
// *************************************************************************

require_once("loader.php");

$user = new User();
$core = new Core();

if (!$user->cdp_loginCheck()) {
    header("location: login.php");
    exit;
}

$filename = "packages_import_template_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

$show_dimensions = isset($core->show_package_dimensions) ? (int)$core->show_package_dimensions : 1;
$show_cbm_input  = isset($core->show_cbm_input_field)    ? (int)$core->show_cbm_input_field    : 0;

// Build headers
$headers = array('qty', 'description', 'weight');

if ($show_dimensions) {
    $headers[] = 'length';
    $headers[] = 'width';
    $headers[] = 'height';
}
if ($show_cbm_input) {
    $headers[] = 'cbm';
}

$headers[] = 'fixed_charge';
$headers[] = 'declared_value';

fputcsv($output, $headers);

// One sample row
$sample = array('1', 'Package Description', '0');
if ($show_dimensions) {
    $sample[] = '0'; // length
    $sample[] = '0'; // width
    $sample[] = '0'; // height
}
if ($show_cbm_input) {
    $sample[] = '0'; // cbm
}
$sample[] = '0'; // fixed_charge
$sample[] = '0'; // declared_value

fputcsv($output, $sample);

fclose($output);
exit;
?>
