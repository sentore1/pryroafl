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
// * If you Purchased from Codecanyon, Please read the full License from   *
// * here- http://codecanyon.net/licenses/standard                         *
// *                                                                       *
// *************************************************************************

// Buffer ALL output so any stray whitespace/HTML from includes cannot corrupt the JSON response
ob_start();

require_once("../../../loader.php");
require_once("../../../helpers/querys.php");

$errors = array();

if (empty($_POST['country_destiny']))
    $errors['country_destiny'] =  $lang['validate_field_ajax1'];

if (empty($_POST['state_destinystates']))
    $errors['state_destinystates'] = $lang['validate_field_ajax3'];


if (empty($_POST['city_destinycities']))
    $errors['city_destinycities'] = $lang['validate_field_ajax2'];

if (empty($_POST['country_origin']))
    $errors['country_origin'] = $lang['validate_field_ajax4'];

if (empty($_POST['initial_range']))
    $errors['initial_range'] = $lang['validate_field_ajax5'];

if (empty($_POST['final_range']))
    $errors['final_range'] = $lang['validate_field_ajax6'];

if (empty($_POST['tariff_price']))
    $errors['tariff_price'] = $lang['validate_field_ajax7'];

    
$response = array();

// If server-side validation caught missing fields, return them as JSON errors
if (!empty($errors)) {
    $response['status'] = 'error';
    $response['message'] = $lang['message_ajax_error2'];
    ob_end_clean(); // discard any stray output from includes
    header('Content-type: application/json; charset=UTF-8');
    echo json_encode($response);
    exit;
}

if (isset($_POST['initial_range'], $_POST['final_range']) && ($_POST['final_range'] < $_POST['initial_range'])) {
    $response['status'] = 'error';
    $response['message'] = $lang['validate_field_ajax8'];
} elseif (isset($_POST['country_origin'], $_POST['country_destiny'], $_POST['initial_range'], $_POST['final_range'])) {
    if (cdp_verifyRangeTariffsExist($_POST['country_origin'], $_POST['country_destiny'], $_POST['initial_range'], $_POST['final_range'], $_POST['id'])) {
        $response['status'] = 'error';
        $response['message'] = $lang['validate_field_ajax9'];
    }
}

if (!isset($response['status'])) {
    $data = array(
        'tariff_price' => cdp_sanitize($_POST['tariff_price']),
        'initial_range' => cdp_sanitize($_POST['initial_range']),
        'final_range' => cdp_sanitize($_POST['final_range']),
        'country_origin' => cdp_sanitize($_POST['country_origin']),
        'country_destiny' => cdp_sanitize($_POST['country_destiny']),
        'state_destinystates' => cdp_sanitize($_POST['state_destinystates']),
        'city_destinycities' => cdp_sanitize($_POST['city_destinycities'])
    );

    $insert = cdp_insertTariffs($data);

    if ($insert) {
        $response['status'] = 'success';
        $response['message'] = $lang['message_ajax_success_add'];
    } else {
        $response['status'] = 'error';
        $response['message'] = $lang['message_ajax_error1'];
    }
}

ob_end_clean(); // discard any stray output from includes
header('Content-type: application/json; charset=UTF-8');
echo json_encode($response);
exit;