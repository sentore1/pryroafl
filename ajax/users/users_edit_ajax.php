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



require_once("../../loader.php");
require_once("../../helpers/querys.php");


$user = new User;
$core = new Core;
$errors = array();


if (empty($_POST['branch_office']))

    $errors['branch_office'] = $lang['validate_field_ajax121'];

if (empty($_POST['fname']))

    $errors['fname'] = $lang['validate_field_ajax122'];
if (empty($_POST['lname']))

    $errors['lname'] = $lang['validate_field_ajax123'];

if (empty($_POST['email']))

    $errors['email'] = $lang['validate_field_ajax125'];

if ($user->cdp_emailExists($_POST['email'], $_POST['id']))

    $errors[] = $lang['validate_field_ajax126'];

if (!$user->cdp_isValidEmail($_POST['email']))

    $errors[] = $lang['validate_field_ajax127'];

if (empty($_POST['phone']))

    $errors['phone'] = $lang['validate_field_ajax128'];


if (CDP_APP_MODE_DEMO === true) {
?>

    <div class="alert alert-warning" id="success-alert">
        <p><span class="icon-minus-sign"></span><i class="close icon-remove-circle"></i>
            <span>Error! </span> There was an error processing the request
        <ul class="error">

            <li>
                <i class="icon-double-angle-right"></i>
                This is a demo version, this action is not allowed, <a class="btn waves-effect waves-light btn-xs btn-success" href="https://codecanyon.net/item/courier-deprixa-pro-integrated-web-system-v32/15216982" target="_blank">Buy DEPRIXA PRO</a> the full version and enjoy all the functions...

            </li>


        </ul>
        </p>
    </div>
    <?php
} else {

    if (empty($errors)) {


        header('Content-type: application/json; charset=UTF-8');
    
        $response = array();


        if (isset($_POST['document_type'])) {

            $document_type = $_POST['document_type'];
        } else {

            $document_type = '';
        }

        if (isset($_POST['document_number'])) {

            $document_number = $_POST['document_number'];
        } else {

            $document_number = '';
        }

        $data = array(

            'email' => cdp_sanitize($_POST['email']),
            'lname' => cdp_sanitize($_POST['lname']),
            'fname' => cdp_sanitize($_POST['fname']),
            'newsletter' => intval($_POST['newsletter']),
            'notes' => cdp_sanitize($_POST['notes']),
            'phone' => cdp_sanitize($_POST['phone']),
            'gender' => cdp_sanitize($_POST['gender']),
            'document_number' => cdp_sanitize($document_number),
            'document_type' => cdp_sanitize($document_type),
            'active' => cdp_sanitize($_POST['active']),
            'id' => cdp_sanitize($_POST['id'])
        );

        // ── AI Permissions — save per-user overrides ──────────────────
        $ai_fields = [
            'ai_access', 'ai_can_assign_drivers', 'ai_can_confirm_payments',
            'ai_can_update_status', 'ai_can_create_shipments', 'ai_can_edit_shipments',
            'ai_can_cancel_shipments', 'ai_can_send_sms', 'ai_can_send_email',
            'ai_can_send_whatsapp', 'ai_can_generate_reports', 'ai_can_export_data',
            'ai_can_create_customers', 'ai_can_edit_customers',
            'ai_can_process_refunds', 'ai_can_apply_discounts',
        ];
        $ai_updates = [];
        $ai_binds   = [':id' => (int)$_POST['id']];
        foreach ($ai_fields as $f) {
            if (isset($_POST[$f]) && $_POST[$f] !== '') {
                // Explicit value (0 or 1)
                $ai_updates[] = "`$f`=:$f";
                $ai_binds[":$f"] = (int)$_POST[$f];
            } elseif (array_key_exists($f, $_POST)) {
                // Empty string submitted = reset to NULL (inherit global)
                $ai_updates[] = "`$f`=NULL";
            }
        }
        if (!empty($ai_updates)) {
            $db = new Conexion;
            $db->cdp_query("UPDATE cdb_users SET " . implode(',', $ai_updates) . " WHERE id=:id");
            foreach ($ai_binds as $k => $v) {
                if ($v !== 'NULL') $db->bind($k, $v);
            }
            $db->cdp_execute();
        }

        $userDataEdit = cdp_getUserEdit4bozo($_POST['id']);

        if ($_POST['password'] != "") {

            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        } else {

            $data['password'] = $userDataEdit['data']->password;
        }


        if (isset($_POST['userlevel'])) {

            $data['userlevel'] = $_POST['userlevel'];
        } else {

            $data['userlevel'] = $userDataEdit['data']->userlevel;
        }


        if (!empty($_POST['branch_office'])) {

            $data['branch_office'] = $_POST['branch_office'];
        } else {

            $data['branch_office'] = $userDataEdit['data']->name_off;
        }


        $insert = cdp_updateUserrx0xr($data);


        if ($insert) {
            $response['status'] = 'success';
            $response['message'] = $lang['message_ajax_success_updated'];
        } else {
            $response['status'] = 'error';
            $response['message'] = $lang['message_ajax_error1'];
        }


        echo json_encode($response);
    }


    if (!empty($errors)) {
    ?>
        <div class="alert alert-danger" id="success-alert">
            <p><span class="icon-minus-sign"></span><i class="close icon-remove-circle"></i>
                <?php echo $lang['message_ajax_error2']; ?>
            <ul class="error">
                <?php
                foreach ($errors as $error) { ?>
                    <li>
                        <i class="icon-double-angle-right"></i>
                        <?php
                        echo $error;

                        ?>

                    </li>
                <?php

                }
                ?>
            </ul>
            </p>
        </div>

    <?php
    }

    if (isset($messages)) {

    ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <p><span class="icon-info-sign"></span>
                <?php
                foreach ($messages as $message) {
                    echo $message;
                }
                ?>
            </p>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

<?php
    }
}
?>