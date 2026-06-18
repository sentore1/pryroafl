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



if (!$user->cdp_is_Admin())
	cdp_redirect_to("login.php");

$userData = $user->cdp_getUserData();
$office = $core->cdp_getOffices();

?>
<!DOCTYPE html>
<html dir="<?php echo $direction_layout; ?>" lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!-- Tell the browser to be responsive to screen width -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<!-- Favicon icon -->
	<link rel="icon" type="image/png" sizes="16x16" href="assets/<?php echo $core->favicon ?>">
	<title><?php echo $lang['tools-config61'] ?> | <?php echo $core->site_name ?></title>
	<!-- This Page CSS -->
	<!-- Custom CSS -->
	<link rel="stylesheet" href="assets/template/assets/libs/intlTelInput/intlTelInput.css">
	<?php include 'views/inc/head_scripts.php'; ?>
	<link href="assets/template/dist/css/custom_swicth.css" rel="stylesheet">

</head>

<body>
	<!-- ============================================================== -->
	<!-- Preloader - style you can find in spinners.css -->
	<!-- ============================================================== -->


	<?php include 'views/inc/preloader.php'; ?>
	<!-- ============================================================== -->
	<!-- Main wrapper - style you can find in pages.scss -->
	<!-- ============================================================== -->
	<div id="main-wrapper">
		<!-- ============================================================== -->
		<!-- Topbar header - style you can find in pages.scss -->
		<!-- ============================================================== -->

		<!-- ============================================================== -->
		<!-- Preloader - style you can find in spinners.css -->
		<!-- ============================================================== -->

		<?php include 'views/inc/topbar.php'; ?>

		<!-- End Topbar header -->


		<!-- Left Sidebar - style you can find in sidebar.scss  -->

		<?php include 'views/inc/left_sidebar.php'; ?>


		<!-- End Left Sidebar - style you can find in sidebar.scss  -->

		<!-- Page wrapper  -->
		<!-- ============================================================== -->
		<div class="page-wrapper">

			<!-- Button group part -->
			<div class="bg-light">
				<div class="row justify-content-center">
					<div class="col-md-12">
						<div class="row">
							<div class="col-12">
								<!-- <div id="loader" style="display:none"></div> -->
								<div id="resultados_ajax"></div>

							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- Action part -->


			<div class="container-fluid">

				<div class="row">
					<!-- Column -->

					<div class="col-lg-12 col-xl-12 col-md-12">

						<div class="card">
							<div class="card-body">
								<div class="d-md-flex align-items-center">
                                    <div>
                                        <h3 class="card-title"><span><?php echo $lang['user_manage37'] ?></span></h3>
                                    </div>
                                </div>
                                <div><hr><br></div>

								<form class="form-horizontal form-material" id="save_user" name="save_user" method="post">
									<section>

										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label for="firstName1"><?php echo $lang['user_manage54'] ?></label>
													<select class="custom-select col-12" id="branch_office" name="branch_office" placeholder="<?php echo $lang['tools-office1'] ?>">
														<?php foreach ($office as $row) : ?>
															<option value="<?php echo $row->name_off; ?>"><?php echo $row->name_off; ?></option>
														<?php endforeach; ?>
													</select>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="firstName1"><?php echo $lang['user_manage3'] ?></label>
													<input type="text" class="form-control" name="username" id="username" placeholder="<?php echo $lang['user_manage3'] ?>">
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="lastName1"><?php echo $lang['user_manage32'] ?></label>
													<input type="text" class="form-control" name="password" id="password" placeholder="<?php echo $lang['user_manage32'] ?>">
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="emailAddress1"><?php echo $lang['user_manage6'] ?></label>
													<input type="text" class="form-control" name="fname" id="fname" placeholder="<?php echo $lang['user_manage6'] ?>">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="phoneNumber1"><?php echo $lang['user_manage7'] ?></label>
													<input type="text" class="form-control" id="lname" name="lname" placeholder="<?php echo $lang['user_manage7'] ?>">
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="emailAddress1"><?php echo $lang['user_manage5'] ?></label>
													<input type="text" class="form-control" id="email" name="email" placeholder="<?php echo $lang['user_manage5'] ?>">
												</div>
											</div>

											<div class="col-md-6">
												<div class="form-group">
													<label for="phoneNumber1"><?php echo $lang['user_manage9'] ?></label>
													<input type="tel" class="form-control" name="phone_custom" id="phone_custom" placeholder="<?php echo $lang['user_manage9'] ?>">

													<span id="valid-msg" class="hide"></span>
													<div id="error-msg" class="hide text-danger"></div>
												</div>
											</div>
										</div>

										<div class="row">

											<div class="col-md-6">
												<div class="form-group">
													<label for="phoneNumber1"><?php echo $lang['user_manage11'] ?></label>
													<select class="custom-select form-control" name="gender" id="gender" placeholder="<?php echo $lang['user_manage11'] ?>">
														<option value=""><?php echo $lang['leftorder177'] ?></option>
														<option value="Male"><?php echo $lang['leftorder179'] ?></option>
														<option value="Female"><?php echo $lang['leftorder178'] ?></option>
														<option value="Other"><?php echo $lang['leftorder180'] ?></option>
													</select>
												</div>
											</div>
											<div class="col-md-6">
											    <div class="form-group">
											        <label for="emailAddress1"><?php echo $lang['user_manage15'] ?></label>
											        <select class="custom-select form-control" id="userlevel" name="userlevel" onchange="toggleDriverFields()" >
											            <option value="" selected disabled><?php echo $lang['left393']; ?></option>
											            <?php echo $user->cdp_getUserLevels($lang, false); ?>
											        </select>
											    </div>
											</div>

										</div>

                                        <div class="row" id="driverFields" style="display: none;">
										    <div class="col-md-6">
										        <div class="form-group">
										            <label for="emailAddress1"><?php echo $lang['edit-clien59'] ?></label>
										            <input type="text" class="form-control" id="enrollment" name="enrollment"  placeholder="<?php echo $lang['edit-clien59'] ?>">
										        </div>
										    </div>
										    <div class="col-md-6">
										        <div class="form-group">
										            <label for="phoneNumber1"><?php echo $lang['edit-clien60'] ?></label>
										            <input type="text" class="form-control" id="vehiclecode" name="vehiclecode" placeholder="<?php echo $lang['edit-clien60'] ?>" >
										        </div>
										    </div>
										</div>

                                        

										<input type="hidden" name="phone" id="phone" />


										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="phoneNumber1"><?php echo $lang['user_manage20'] ?></label>
													<div class="btn-group" data-toggle="buttons">
														<label class="btn">
															<div class="custom-control custom-radio">
																<input type="radio" id="customRadio2" class="custom-control-input" name="active" value="1" checked="checked">
																<label class="custom-control-label" for="customRadio2"> <?php echo $lang['user_manage16'] ?></label>
															</div>
														</label>
														<label class="btn">
															<div class="custom-control custom-radio">
																<input type="radio" id="customRadio1" class="custom-control-input" name="active" value="0">
																<label class="custom-control-label" for="customRadio1"> <?php echo $lang['user_manage17'] ?></label>
															</div>
														</label>

													</div>
												</div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
													<label for="phoneNumber1"><?php echo $lang['user_manage23'] ?></label>
													<div class="btn-group" data-toggle="buttons">
														<label class="btn">
															<div class="custom-control custom-radio">
																<input type="radio" id="customRadio4" name="newsletter" value="1" checked="checked" class="custom-control-input">
																<label class="custom-control-label" for="customRadio4"> <?php echo $lang['user_manage21'] ?></label>
															</div>
														</label>
														<label class="btn">
															<div class="custom-control custom-radio">
																<input type="radio" id="customRadio5" name="newsletter" value="0" class="custom-control-input">
																<label class="custom-control-label" for="customRadio5"> <?php echo $lang['user_manage22'] ?></label>
															</div>
														</label>
													</div>
												</div>
											</div>
										</div>

										<div class="row">

											<div class="col-md-6">
												<div class="form-group">
													<label class="custom-control custom-checkbox">
														<input type="checkbox" class="custom-control-input" name="notify" id="notify" value="1">
														<i></i><?php echo $lang['edit-clien34'] ?>
														<span class="custom-control-indicator"></span><br><br>
														<label><span><i class="ti-email" style="color:#6610f2"></i>&nbsp; <?php echo $lang['edit-clien35'] ?></span></label>
													</label>
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<label for="emailAddress1"><?php echo $lang['user_manage36'] ?></label>
													<textarea class="form-control" id="notes" name="notes" rows="6" name="notes" placeholder="<?php echo $lang['user_manage36'] ?>"></textarea>
												</div>
											</div>
										</div>

									</section>

								<!-- ── AI Permissions (shown for admin/employee roles) ── -->
								<div id="ai-perms-section" style="display:none;">
									<hr/>
									<h4 style="font-size:14px; font-weight:700; color:#495057; margin-bottom:4px;">
										<i class="ti-lock" style="color:#0d6efd;"></i> Pryro AI Permissions
									</h4>
									<p style="font-size:12px; color:#6c757d; margin-bottom:14px;">
										Set this user's AI capabilities. Leave as <strong>Inherit</strong> to follow global AI Settings.
									</p>
									<div style="margin-bottom:12px;">
										<div style="background:#f0f4ff; border:1px solid #c5d6ff; border-radius:8px; padding:10px 14px; display:flex; align-items:center; justify-content:space-between;">
											<div>
												<strong style="font-size:12px; color:#0d6efd;"><i class="ti-shield"></i> AI Panel Access</strong>
												<div style="font-size:11px; color:#6c757d;">Can this user open and use the AI assistant?</div>
											</div>
											<select name="ai_access" class="form-control form-control-sm" style="width:130px;">
												<option value="">Inherit</option>
												<option value="1">✅ Enabled</option>
												<option value="0">❌ Disabled</option>
											</select>
										</div>
									</div>
									<?php
									$ai_groups_add = [
										'Actions' => [
											['field'=>'ai_can_assign_drivers',  'label'=>'Assign Drivers',   'icon'=>'ti-truck'],
											['field'=>'ai_can_confirm_payments','label'=>'Confirm Payments', 'icon'=>'ti-money'],
											['field'=>'ai_can_update_status',   'label'=>'Update Status',    'icon'=>'ti-reload'],
											['field'=>'ai_can_create_shipments','label'=>'Create Shipments', 'icon'=>'ti-package'],
											['field'=>'ai_can_edit_shipments',  'label'=>'Edit Shipments',   'icon'=>'ti-pencil'],
											['field'=>'ai_can_cancel_shipments','label'=>'Cancel Shipments', 'icon'=>'ti-close'],
										],
										'Communication' => [
											['field'=>'ai_can_send_sms',     'label'=>'Send SMS',       'icon'=>'ti-mobile'],
											['field'=>'ai_can_send_email',   'label'=>'Send Email',     'icon'=>'ti-email'],
											['field'=>'ai_can_send_whatsapp','label'=>'Send WhatsApp',  'icon'=>'ti-comment'],
										],
										'Reporting & Finance' => [
											['field'=>'ai_can_generate_reports','label'=>'Generate Reports', 'icon'=>'ti-bar-chart'],
											['field'=>'ai_can_export_data',     'label'=>'Export Data',      'icon'=>'ti-download'],
											['field'=>'ai_can_create_customers','label'=>'Create Customers', 'icon'=>'ti-user-add'],
											['field'=>'ai_can_edit_customers',  'label'=>'Edit Customers',   'icon'=>'ti-pencil-alt'],
											['field'=>'ai_can_process_refunds', 'label'=>'Process Refunds',  'icon'=>'ti-money'],
											['field'=>'ai_can_apply_discounts', 'label'=>'Apply Discounts',  'icon'=>'ti-tag'],
										],
									];
									foreach ($ai_groups_add as $gname => $gitems):
									?>
									<div style="margin-bottom:8px;">
										<div style="font-size:10px; font-weight:700; color:#6c757d; text-transform:uppercase; letter-spacing:1px; margin-bottom:5px;"><?php echo $gname; ?></div>
										<div style="display:grid; grid-template-columns:1fr 1fr; gap:5px;">
											<?php foreach ($gitems as $p): ?>
											<div style="background:#fff; border:1px solid #e9ecef; border-radius:6px; padding:7px 10px; display:flex; align-items:center; justify-content:space-between;">
												<div style="display:flex; align-items:center; gap:7px;">
													<i class="<?php echo $p['icon']; ?>" style="color:#0d6efd; font-size:12px;"></i>
													<span style="font-size:11px; color:#495057;"><?php echo $p['label']; ?></span>
												</div>
												<select name="<?php echo $p['field']; ?>" class="form-control form-control-sm" style="width:110px; font-size:11px;">
													<option value="">Inherit</option>
													<option value="1">✅ Allow</option>
													<option value="0">❌ Deny</option>
												</select>
											</div>
											<?php endforeach; ?>
										</div>
									</div>
									<?php endforeach; ?>
									<hr/>
								</div>

								<script>
								(function(){
									var sel = document.getElementById('userlevel');
									function toggle(){ var v=sel?sel.value:''; document.getElementById('ai-perms-section').style.display=(v=='9'||v=='2')?'':'none'; }
									if(sel) sel.addEventListener('change', toggle);
									document.addEventListener('DOMContentLoaded', toggle);
								})();
								</script>

								<div class="form-group">
										<div class="col-sm-12">
											<button class="btn btn-outline-primary btn-confirmation" id="save_data" name="save_data" type="submit"><?php echo $lang['user_manage37'] ?><span><i class="icon-ok"></i></span></button>
											<a href="users_list.php" class="btn btn-outline-secondary btn-confirmation"><span><i class="ti-share-alt"></i></span> <?php echo $lang['user_manage30'] ?></a>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
					<!-- Column --> 
				</div>
			</div>

			<?php include 'views/inc/footer.php'; ?>

		</div>
		<!-- ============================================================== -->
		<!-- End Page wrapper  -->
		<!-- ============================================================== -->
	</div>
	<!-- ============================================================== -->
	<!-- End Wrapper -->
	<!-- ============================================================== -->
	<?php include('helpers/languages/translate_to_js.php'); ?>

    
    <script src="assets/template/assets/libs/select2/dist/js/select2.full.min.js"></script>
    <script src="assets/template/assets/libs/select2/dist/js/select2.min.js"></script>
    <script src="assets/template/assets/libs/intlTelInput/intlTelInput.js"></script>

	<script src="dataJs/users_add.js"></script>


</body>

</html>