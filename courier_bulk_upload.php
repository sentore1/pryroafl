<?php
// *************************************************************************
// *                                                                       *
// * DEPRIXA PRO -  Integrated Web Shipping System                         *
// * Copyright (c) JAOMWEB. All Rights Reserved                            *
// *                                                                       *
// *************************************************************************

require_once("loader.php");

$user = new User;
$core = new Core;

if (!$user->cdp_loginCheck()) {
    header("location: login.php");
    exit;
}

$userData = $user->cdp_getUserData();
?>
<!DOCTYPE html>
<html dir="<?php echo $direction_layout; ?>" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/<?php echo $core->favicon ?>">
    <title>Bulk Upload Shipments | <?php echo $core->site_name ?></title>
    
    <link rel="stylesheet" href="assets/template/assets/libs/sweetalert2/sweetalert2.min.css">
    <?php include 'views/inc/head_scripts.php'; ?>
</head>

<body>
    <?php include 'views/inc/preloader.php'; ?>
    
    <div id="main-wrapper">
        <?php include 'views/inc/topbar.php'; ?>
        <?php include 'views/inc/left_sidebar.php'; ?>
        
        <div class="page-wrapper">
            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-12 align-self-center">
                        <h4 class="page-title"><i class="ti-upload" aria-hidden="true"></i> Bulk Upload Shipments</h4>
                        <br>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Upload Excel/CSV File</h4>
                                <p class="text-muted">Upload a file containing multiple shipments to create them all at once</p>
                                
                                <div class="border p-3 mb-3">
                                    <h5><i class="fas fa-info-circle"></i> File Format Instructions</h5>
                                    <p>Your Excel/CSV file should contain the following columns:</p>
                                    <ul>
                                        <li><strong>sender_email</strong> - Sender's email address (will be created if doesn't exist)</li>
                                        <li><strong>sender_fname</strong> - Sender's first name (used if creating new user)</li>
                                        <li><strong>sender_lname</strong> - Sender's last name (used if creating new user)</li>
                                        <li><strong>recipient_email</strong> - Recipient's email address (will be created if doesn't exist)</li>
                                        <li><strong>recipient_fname</strong> - Recipient's first name (used if creating new user)</li>
                                        <li><strong>recipient_lname</strong> - Recipient's last name (used if creating new user)</li>
                                        <li><strong>tracking_number</strong> - Shipment tracking number (without prefix)</li>
                                        <li><strong>tracking_prefix</strong> - Tracking prefix (e.g., CDPE)</li>
                                        <li><strong>item_description</strong> - Description of the package contents (e.g., Electronics, Clothing, Documents)</li>
                                        <li><strong>weight</strong> - Package weight</li>
                                        <li><strong>length</strong> - Package length</li>
                                        <li><strong>width</strong> - Package width</li>
                                        <li><strong>height</strong> - Package height</li>
                                        <li><strong>sender_country</strong> - Origin country</li>
                                        <li><strong>sender_city</strong> - Origin city</li>
                                        <li><strong>sender_address</strong> - Origin address</li>
                                        <li><strong>recipient_country</strong> - Destination country</li>
                                        <li><strong>recipient_city</strong> - Destination city</li>
                                        <li><strong>recipient_address</strong> - Destination address</li>
                                    </ul>
                                    <div class="alert alert-info mt-2">
                                        <strong>Note:</strong> The system will automatically create sender and recipient accounts if they don't exist.
                                    </div>
                                    <a href="download_template_shipments.php" class="btn btn-sm btn-success"><i class="fas fa-download"></i> Download Template</a>
                                </div>

                                <form id="bulk_upload_form" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Select File (Excel or CSV)</label>
                                                <input type="file" name="bulk_file" id="bulk_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-primary" id="upload_btn">
                                                <i class="fas fa-upload"></i> Upload and Process
                                            </button>
                                            <a href="courier_add.php" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left"></i> Back to Create Shipment
                                            </a>
                                        </div>
                                    </div>
                                </form>

                                <div id="upload_results" class="mt-4"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'views/inc/footer.php'; ?>
        </div>
    </div>

    <script src="assets/template/assets/libs/sweetalert2/sweetalert2.min.js"></script>
    
    <script>
    $(document).ready(function() {
        $('#bulk_upload_form').on('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            
            $('#upload_btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            
            $.ajax({
                url: 'ajax/courier/process_bulk_upload_shipments_ajax.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#upload_results').html(response);
                    $('#upload_btn').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload and Process');
                    $('#bulk_file').val('');
                },
                error: function() {
                    Swal.fire('Error', 'An error occurred while processing the file', 'error');
                    $('#upload_btn').prop('disabled', false).html('<i class="fas fa-upload"></i> Upload and Process');
                }
            });
        });
    });
    </script>
</body>
</html>
