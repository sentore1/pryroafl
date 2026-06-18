	<header class="topbar">
		<nav class="navbar top-navbar navbar-expand-md navbar-dark">
			<div class="navbar-header">
				<!-- This is for the sidebar toggle which is visible on mobile only -->
				<a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
				<!-- ============================================================== -->
				<!-- Logo -->
				<!-- ============================================================== -->
				<a class="navbar-brand" href="index.php">
					<!-- Logo text -->
					<span class="logo-text">
						<!-- dark Logo text -->
						<?php echo ($core->logo) ? '<img src="assets/' . $core->logo . '" alt="' . $core->site_name . '" width="' . $core->thumb_w . '" height="' . $core->thumb_h . '"/>' : $core->site_name; ?>
					</span>
				</a> 

				<!-- ============================================================== -->
				<!-- End Logo -->
				<!-- ============================================================== -->
				<!-- ============================================================== -->
				<!-- Toggle which is visible on mobile only -->
				<!-- ============================================================== -->
				<a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
			</div>
			<!-- ============================================================== -->
			<!-- End Logo -->
			<!-- ============================================================== -->
			<div class="navbar-collapse collapse" id="navbarSupportedContent">
				<!-- ============================================================== -->
				<!-- toggle and nav items -->
				<!-- ============================================================== -->
				<ul class="navbar-nav float-left mr-auto">
					<li class="nav-item d-none d-md-block"><a class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)" data-sidebartype="mini-sidebar"><i class="mdi mdi-menu font-24"></i></a></li>

				</ul>
				<!-- ============================================================== -->
				<!-- Right side toggle and nav items -->
				<!-- ============================================================== -->
				<ul class="navbar-nav float-right">
					<!-- P-AI Button -->
					<?php if ($userData->userlevel == 9 || $userData->userlevel == 2): ?>
					<li class="nav-item mr-2" style="display:flex; align-items:center;">
						<button onclick="cdp_openPAI()" id="btn-pryro-ai" style="background:#0d6efd; color:#fff; border:none; font-size:8px; font-weight:700; padding:3px 8px; border-radius:20px; letter-spacing:0.5px; cursor:pointer; transition: all 0.2s ease; white-space:nowrap; overflow:hidden; max-width:24px;"
						onmouseenter="this.style.maxWidth='70px'; this.innerHTML='PRYRO AI';"
						onmouseleave="this.style.maxWidth='24px'; this.innerHTML='AI';">AI</button>
					</li>
					<?php endif; ?>
					<!-- ============================================================== -->
					<!-- create new -->
					<!-- ============================================================== -->
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown2" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-weight:600; font-size:13px; letter-spacing:0.5px;">
							<i class="mdi mdi-translate mr-1" style="font-size:18px;"></i>
							<?php
							$langLabels = ['en'=>'EN','es'=>'ES','fr'=>'FR','ar'=>'AR','he'=>'HE'];
							echo isset($langLabels[$core->language]) ? $langLabels[$core->language] : strtoupper($core->language);
							?>
						</a>
						<div class="dropdown-menu dropdown-menu-right animated fadeIn" style="min-width:120px;">
							<a class="dropdown-item lang-switch <?php echo $core->language=='en'?'active':''; ?>" href="#" data-lang="en">English</a>
							<a class="dropdown-item lang-switch <?php echo $core->language=='es'?'active':''; ?>" href="#" data-lang="es">Español</a>
							<a class="dropdown-item lang-switch <?php echo $core->language=='fr'?'active':''; ?>" href="#" data-lang="fr">Français</a>
							<a class="dropdown-item lang-switch <?php echo $core->language=='ar'?'active':''; ?>" href="#" data-lang="ar">العربية</a>
							<a class="dropdown-item lang-switch <?php echo $core->language=='he'?'active':''; ?>" href="#" data-lang="he">עברית</a>
						</div>
					</li>
					<!-- ============================================================== -->
					<!-- Comment -->
					<!-- ============================================================== -->

					<li class="nav-item dropdown" id="notif-dropdown">
						<a id="clickme" class="nav-link dropdown-toggle waves-effect waves-dark" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="mdi mdi-bell-outline" style="font-size:22px;"></i>
							<span class="badge badge-notify badge-sm up badge-light pull-top-xs" id="countNotifications">0</span>
						</a>

						<div class="dropdown-menu dropdown-menu-right mailbox">
							<div id="ajax_response"></div>
						</div>

					</li>

					<script>
					document.addEventListener('DOMContentLoaded', function () {
						var notifLi = document.getElementById('notif-dropdown');
						var menu = notifLi.querySelector('.dropdown-menu');
						notifLi.addEventListener('mouseenter', function () {
							menu.classList.add('show');
							notifLi.classList.add('show');
						});
						notifLi.addEventListener('mouseleave', function () {
							menu.classList.remove('show');
							notifLi.classList.remove('show');
						});
					});
					</script>


					<!-- ============================================================== -->
					<!-- User profile and search -->
					<!-- ============================================================== -->
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="assets/<?php echo ($userData->avatar) ? $userData->avatar : "uploads/blank.png"; ?>" class="rounded-circle" width="34" />&nbsp; <i class="fa fa-caret-down"></i></a>
						<div class="dropdown-menu dropdown-menu-right user-dd animated fadeIn">
							<span class="with-arrow"><span class="bg-primary"></span></span>
							<div class="d-flex no-block align-items-center p-15 bg-primary text-white m-b-10">
								<div class="">
									<img src="assets/<?php echo ($userData->avatar) ? $userData->avatar : "uploads/blank.png"; ?>" class="rounded-circle" width="80" />
								</div>
								<div class="m-l-10">
									<h4 class="m-b-0"><?php echo $userData->username; ?></h4>
									<p class=" m-b-0"><?php echo $userData->email; ?></p>
								</div>
							</div>

							<?php
							if ($userData->userlevel == 9 || $userData->userlevel == 2) {
							?>
								<a class="dropdown-item" href="users_edit.php?user=<?php echo $userData->id; ?>">
									<i class="ti-user m-r-5 m-l-5"></i> <?php echo $lang['miprofile'] ?></a>
							<?php
							} else	if ($userData->userlevel == 1) {

							?>
								<a class="dropdown-item" href="customers_profile_edit.php?user=<?php echo $userData->id; ?>">
									<i class="ti-user m-r-5 m-l-5"></i> <?php echo $lang['miprofile'] ?></a>
							<?php

							} else	if ($userData->userlevel == 3) {

							?>
								<a class="dropdown-item" href="drivers_edit.php?user=<?php echo $userData->id; ?>">
									<i class="ti-user m-r-5 m-l-5"></i> <?php echo $lang['miprofile'] ?></a>
							<?php
							}
							?>


							<div class="dropdown-divider"></div>
							<?php
							if ($userData->userlevel == 9) {
							?>
								<a class="dropdown-item" href="users_list.php">
									<i class="ti-settings m-r-5 m-l-5"></i> <?php echo $lang['accountset'] ?></a>
								<div class="dropdown-divider"></div>
							<?php
							}
							?>

							<a class="dropdown-item" href="logout.php"><i class="fa fa-power-off m-r-5 m-l-5"></i>
								<?php echo $lang['logoouts'] ?></a>
						</div>
					</li>
					<!-- ============================================================== -->
					<!-- User profile and search -->
					<!-- ============================================================== -->
				</ul>
			</div>
		</nav>
	</header>

	<audio id="chatAudio">
		<source src="assets/notify.mp3" type="audio/mpeg">
	</audio>

	<script>
	document.addEventListener('DOMContentLoaded', function() {
		document.querySelectorAll('.lang-switch').forEach(function(el) {
			el.addEventListener('click', function(e) {
				e.preventDefault();
				var lang = this.getAttribute('data-lang');
				var xhr = new XMLHttpRequest();
				xhr.open('POST', 'ajax/tools/switch_language_ajax.php', true);
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				xhr.onload = function() {
					try {
						var res = JSON.parse(xhr.responseText);
						if (res.status === 'success') {
							location.reload();
						} else {
							alert('Error: ' + res.message);
						}
					} catch(err) {
						alert('Parse error: ' + xhr.responseText);
					}
				};
				xhr.onerror = function() { alert('Network error'); };
				xhr.send('language=' + encodeURIComponent(lang));
			});
		});
	});
	</script>


	<!-- <script src="dataJs/load_notifications_all.js"> </script> -->

<!-- P-AI Modal -->
<div class="modal fade" id="modal-pai" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" id="pai-modal-dialog" role="document" style="transition: all 0.2s ease;">
        <div class="modal-content" style="border-radius:8px; overflow:hidden; display:flex; flex-direction:column; height:100%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <!-- Header -->
            <div class="modal-header" style="background:linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color:#fff; padding:14px 20px; flex-shrink:0; border:none;">
                <div class="d-flex align-items-center flex-grow-1">
                    <div style="background:rgba(255,255,255,0.2); width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; margin-right:12px;">AI</div>
                    <div>
                        <h5 class="mb-0" style="color:#fff; font-size:16px; font-weight:600;">Pryro AI Assistant</h5>
                        <small style="color:rgba(255,255,255,0.8); font-size:11px;">Operations & Analytics Dashboard</small>
                    </div>
                </div>
                <div class="d-flex align-items-center" style="gap:8px;">
                    <!-- Settings button -->
                    <button type="button" id="btn-pai-settings" onclick="cdp_togglePAISettings()" style="background:rgba(255,255,255,0.15); border:none; color:#fff; border-radius:4px; padding:5px 10px; cursor:pointer; transition: all 0.2s;" title="Settings">
                        <i class="ti-settings"></i>
                    </button>
                    <!-- Clear chat button -->
                    <button type="button" id="btn-pai-clear" onclick="cdp_clearPAIChat()" style="background:rgba(255,255,255,0.15); border:none; color:#fff; border-radius:4px; padding:5px 10px; cursor:pointer; transition: all 0.2s;" title="Clear Chat">
                        <i class="ti-trash"></i>
                    </button>
                    <!-- Expand/fullscreen toggle -->
                    <button type="button" id="btn-pai-expand" onclick="cdp_togglePAIFullscreen()" style="background:rgba(255,255,255,0.15); border:none; color:#fff; border-radius:4px; padding:5px 10px; cursor:pointer; transition: all 0.2s;" title="Expand">
                        <i class="ti-fullscreen"></i>
                    </button>
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1; margin:0; padding:0 0 0 8px; font-size:28px;">
                        <span>&times;</span>
                    </button>
                </div>
            </div>

            <!-- Settings Panel (hidden by default) -->
            <div id="pai-settings-panel" style="display:none; background:#f8f9fa; padding:16px; border-bottom:2px solid #e9ecef; flex-shrink:0; max-height:70vh; overflow-y:auto;">
                <!-- UI Settings Section -->
                <div style="margin-bottom:20px;">
                    <h6 style="font-size:13px; font-weight:700; color:#495057; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
                        <i class="ti-settings"></i> Interface Settings
                    </h6>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#495057; margin-bottom:6px; display:block;">AI Provider</label>
                            <select id="pai-setting-provider" class="form-control form-control-sm" style="font-size:12px;">
                                <option value="groq">Groq (Fast & Free)</option>
                                <option value="openai">OpenAI (GPT-4o)</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#495057; margin-bottom:6px; display:block;">Response Length</label>
                            <select id="pai-setting-length" class="form-control form-control-sm" style="font-size:12px;">
                                <option value="brief">Brief (Quick)</option>
                                <option value="normal" selected>Normal</option>
                                <option value="detailed">Detailed</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#495057; margin-bottom:6px; display:block;">Auto-Refresh</label>
                            <select id="pai-setting-autorefresh" class="form-control form-control-sm" style="font-size:12px;">
                                <option value="0">Disabled</option>
                                <option value="30">Every 30 seconds</option>
                                <option value="60">Every minute</option>
                                <option value="300">Every 5 minutes</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#495057; margin-bottom:6px; display:block;">Sound</label>
                            <select id="pai-setting-sound" class="form-control form-control-sm" style="font-size:12px;">
                                <option value="1" selected>Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- AI Permissions Section -->
                <div style="margin-bottom:16px;">
                    <h6 style="font-size:13px; font-weight:700; color:#495057; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
                        <i class="ti-lock"></i> AI Permissions <span style="font-size:10px; color:#6c757d; font-weight:400;">(What AI Can Do)</span>
                    </h6>
                    
                    <div id="pai-permissions-loading" style="text-align:center; padding:20px; color:#6c757d;">
                        <i class="fa fa-spinner fa-spin"></i> Loading permissions...
                    </div>
                    
                    <div id="pai-permissions-list" style="display:none;">
                        <!-- Permissions will be loaded here via AJAX -->
                    </div>
                    
                    <div style="margin-top:10px; padding:10px; background:#d1ecf1; border-left:3px solid #17a2b8; border-radius:4px;">
                        <small style="font-size:11px; color:#0c5460;">
                            <i class="ti-info-alt"></i> <strong>Tip:</strong> Toggle switches to enable/disable permissions instantly. 
                            Changes are saved automatically to the database.
                        </small>
                    </div>
                </div>

                <!-- Save Button -->
                <div style="padding-top:12px; border-top:1px solid #dee2e6;">
                    <div class="d-flex justify-content-between align-items-center">
                        <small style="color:#6c757d; font-size:11px;"><i class="ti-info-alt"></i> UI settings saved in browser</small>
                        <button onclick="cdp_savePAISettings()" class="btn btn-sm btn-primary" style="font-size:11px; padding:6px 16px;">
                            <i class="ti-check"></i> Save UI Settings
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Bar -->
            <div style="background:#fff; padding:10px 16px; border-bottom:1px solid #e9ecef; flex-shrink:0;">
                <div class="d-flex" style="gap:8px; flex-wrap:wrap;">
                    <button onclick="cdp_quickAction('briefing')" class="btn btn-sm" style="background:#e7f3ff; color:#0d6efd; border:none; font-size:11px; padding:4px 10px; border-radius:4px;">
                        <i class="ti-dashboard"></i> System Status
                    </button>
                    <button onclick="cdp_quickAction('stuck')" class="btn btn-sm" style="background:#fff3cd; color:#856404; border:none; font-size:11px; padding:4px 10px; border-radius:4px;">
                        <i class="ti-alert"></i> Stuck Shipments
                    </button>
                    <button onclick="cdp_quickAction('payments')" class="btn btn-sm" style="background:#d4edda; color:#155724; border:none; font-size:11px; padding:4px 10px; border-radius:4px;">
                        <i class="ti-money"></i> Payments
                    </button>
                    <button onclick="cdp_quickAction('drivers')" class="btn btn-sm" style="background:#d1ecf1; color:#0c5460; border:none; font-size:11px; padding:4px 10px; border-radius:4px;">
                        <i class="ti-truck"></i> Drivers
                    </button>
                    <button onclick="cdp_quickAction('revenue')" class="btn btn-sm" style="background:#f8d7da; color:#721c24; border:none; font-size:11px; padding:4px 10px; border-radius:4px;">
                        <i class="ti-stats-up"></i> Revenue
                    </button>
                </div>
            </div>

            <!-- Chat messages -->
            <div id="pai-chat-messages" style="flex:1; overflow-y:auto; padding:16px; background:#f8f9fa; min-height:320px; max-height:420px;">
                <div class="text-center text-muted py-5">
                    <div class="mb-3">
                        <i class="fa fa-spinner fa-spin fa-3x" style="color:#0d6efd; opacity:0.5;"></i>
                    </div>
                    <p class="mb-2" style="font-size:14px; font-weight:600; color:#495057;">Initializing Pryro AI...</p>
                    <p class="mb-0" style="font-size:12px; color:#6c757d;">Analyzing your logistics operations</p>
                </div>
            </div>

            <!-- Typing Indicator -->
            <div id="pai-typing-status" style="display:none; padding:6px 16px; background:#f8f9fa; font-size:11px; color:#6c757d; border-top:1px solid #e9ecef;">
                <i class="fa fa-circle" style="font-size:6px; animation: pulse 1.5s infinite;"></i> AI is thinking...
            </div>

            <!-- Input -->
            <div style="padding:14px 16px; border-top:2px solid #e9ecef; background:#fff; flex-shrink:0;">
                <form onsubmit="cdp_sendPAIMessage(); return false;" style="margin:0;">
                    <div class="input-group">
                        <input type="text" 
                               id="pai-chat-input" 
                               class="form-control" 
                               placeholder="Ask me anything about shipments, drivers, payments, customers..." 
                               style="border-radius:22px 0 0 22px; font-size:13px; border:2px solid #e9ecef; padding:10px 16px;"
                               autocomplete="off"
                               maxlength="500">
                        <div class="input-group-append">
                            <button type="button"
                                    id="pai-send-btn" 
                                    onclick="cdp_sendPAIMessage()" 
                                    class="btn btn-primary" 
                                    style="border-radius:0 22px 22px 0; padding:10px 20px; font-weight:600;">
                                <i class="ti-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </form>
                <div class="d-flex justify-content-between align-items-center" style="margin-top:6px; padding:0 4px;">
                    <small style="color:#aaa; font-size:10px;"><i class="ti-info-alt"></i> Press Enter to send or use quick actions above</small>
                    <small style="color:#aaa; font-size:10px;" id="pai-char-count">0/500</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Action Input Modal — shown when required fields are missing from an AI action -->
<div class="modal fade" id="pai-input-modal" tabindex="-1" role="dialog" aria-labelledby="pai-input-modal-title" style="z-index:99999;">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:460px;">
        <div class="modal-content" style="border-radius:10px; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,0.25);">
            <!-- Header -->
            <div id="pai-input-modal-header" style="background:#0d6efd; padding:14px 18px; display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:30px; height:30px; background:rgba(255,255,255,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                        <i id="pai-input-modal-icon" class="ti-pencil" style="color:#fff; font-size:14px;"></i>
                    </div>
                    <div>
                        <div id="pai-input-modal-title" style="color:#fff; font-size:13px; font-weight:700;">Complete Action</div>
                        <div style="color:rgba(255,255,255,0.8); font-size:10px;">Fill in the missing fields to proceed</div>
                    </div>
                </div>
                <button type="button" data-dismiss="modal" style="background:none; border:none; color:#fff; opacity:0.7; font-size:18px; cursor:pointer; padding:0;">&times;</button>
            </div>
            <!-- Body -->
            <div style="padding:18px; background:#fff;">
                <div id="pai-input-modal-fields" style="display:flex; flex-direction:column; gap:12px;">
                    <!-- Fields rendered dynamically by JS -->
                </div>
            </div>
            <!-- Footer -->
            <div style="padding:12px 18px; background:#f8f9fa; border-top:1px solid #e9ecef; display:flex; gap:8px; justify-content:flex-end;">
                <button type="button" data-dismiss="modal" style="background:#6c757d; color:#fff; border:none; padding:7px 16px; border-radius:6px; font-size:12px; cursor:pointer;">
                    Cancel
                </button>
                <button type="button" id="pai-input-modal-submit" onclick="cdp_submitActionModal()" style="background:#0d6efd; color:#fff; border:none; padding:7px 18px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                    <i class="ti-check"></i> Execute Action
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library for Visual Data -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20%       { transform: translateX(-6px); }
    40%       { transform: translateX(6px); }
    60%       { transform: translateX(-4px); }
    80%       { transform: translateX(4px); }
}
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Stat Cards Styling */
.pai-stat-card {
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    animation: slideIn 0.4s ease;
}

.pai-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    border-color: #0d6efd;
}

.pai-stat-card-icon {
    font-size: 32px;
    margin-bottom: 8px;
}

.pai-stat-card-value {
    font-size: 28px;
    font-weight: 700;
    color: #0d6efd;
    margin: 8px 0;
}

.pai-stat-card-label {
    font-size: 12px;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pai-stat-card-btn {
    margin-top: 10px;
    padding: 6px 14px;
    font-size: 11px;
    border-radius: 6px;
    border: none;
    background: #0d6efd;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
}

.pai-stat-card-btn:hover {
    background: #0a58ca;
    transform: scale(1.05);
}

/* Chart Container */
.pai-chart-container {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin: 16px 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    animation: slideIn 0.5s ease;
}

.pai-chart-title {
    font-size: 14px;
    font-weight: 700;
    color: #495057;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Data Table */
.pai-data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    margin: 12px 0;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.pai-data-table th {
    background: #0d6efd;
    color: white;
    padding: 10px 12px;
    text-align: left;
    font-weight: 600;
}

.pai-data-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #e9ecef;
}

.pai-data-table tr:hover {
    background: #f8f9fa;
}

/* Progress Bar */
.pai-progress-bar {
    background: #e9ecef;
    border-radius: 10px;
    height: 20px;
    overflow: hidden;
    margin: 8px 0;
}

.pai-progress-fill {
    background: linear-gradient(90deg, #0d6efd 0%, #0a58ca 100%);
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 11px;
    font-weight: 600;
    transition: width 1s ease;
}

/* Alert Card */
.pai-alert-card {
    background: linear-gradient(135deg, #fff3cd 0%, #fff8e1 100%);
    border-left: 4px solid #ffc107;
    border-radius: 8px;
    padding: 14px;
    margin: 12px 0;
    animation: slideIn 0.4s ease;
}

.pai-alert-card.danger {
    background: linear-gradient(135deg, #f8d7da 0%, #ffe5e7 100%);
    border-left-color: #dc3545;
}

.pai-alert-card.success {
    background: linear-gradient(135deg, #d4edda 0%, #e7f7ea 100%);
    border-left-color: #28a745;
}

.pai-alert-card.info {
    background: linear-gradient(135deg, #d1ecf1 0%, #e7f6f9 100%);
    border-left-color: #17a2b8;
}

/* Toggle Switch */
.switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}

.switch.switch-sm {
    width: 36px;
    height: 20px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.switch .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.3s;
    border-radius: 24px;
}

.switch .slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

.switch.switch-sm .slider:before {
    height: 14px;
    width: 14px;
    left: 3px;
    bottom: 3px;
}

.switch input:checked + .slider {
    background-color: #28a745;
}

.switch input:focus + .slider {
    box-shadow: 0 0 1px #28a745;
}

.switch input:checked + .slider:before {
    transform: translateX(20px);
}

.switch.switch-sm input:checked + .slider:before {
    transform: translateX(16px);
}

#pai-chat-input:focus {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15) !important;
}

#pai-send-btn:hover {
    background: #0a58ca !important;
    transform: scale(1.05);
}

#btn-pai-settings:hover, #btn-pai-clear:hover, #btn-pai-expand:hover {
    background: rgba(255,255,255,0.3) !important;
}

/* Scrollbar styling */
#pai-chat-messages::-webkit-scrollbar {
    width: 6px;
}

#pai-chat-messages::-webkit-scrollbar-track {
    background: #e9ecef;
}

#pai-chat-messages::-webkit-scrollbar-thumb {
    background: #0d6efd;
    border-radius: 3px;
}

#pai-chat-messages::-webkit-scrollbar-thumb:hover {
    background: #0a58ca;
}
</style>

<script>
var paiHistory   = [];
var paiFullscreen = false;
var paiSettings = {
    provider: 'groq',
    length: 'normal',
    autorefresh: 0,
    sound: 1
};
var paiAutoRefreshInterval = null;

function cdp_openPAI() {
    paiHistory = [];
    // Load settings from localStorage
    cdp_loadPAISettings();
    $('#modal-pai').modal('show');
    
    // Focus input field after modal is fully shown
    setTimeout(function() {
        $('#pai-chat-input').focus();
    }, 500);
    
    // Send initial briefing request
    cdp_sendPAIMessage('Give me a full briefing of the current system status. Include stuck shipments with details, driver workload, overdue payments with customer names and amounts, revenue comparison vs last month, top customers, and what happened in the last 24 hours.');
}

function cdp_togglePAIFullscreen() {
    var $dialog = $('#pai-modal-dialog');
    var $msgs   = $('#pai-chat-messages');
    var $icon   = $('#btn-pai-expand i');
    paiFullscreen = !paiFullscreen;
    if (paiFullscreen) {
        $dialog.css({
            'position':'fixed', 'top':'0', 'left':'0', 'right':'0', 'bottom':'0',
            'max-width':'100vw', 'width':'100vw', 'height':'100vh',
            'margin':'0', 'padding':'0', 'z-index':'9999'
        });
        $('.modal-content', $dialog).css('height','100vh');
        $msgs.css('max-height', 'calc(100vh - 160px)');
        $icon.removeClass('ti-fullscreen').addClass('ti-zoom-out');
    } else {
        $dialog.css({
            'position':'', 'top':'', 'left':'', 'right':'', 'bottom':'',
            'max-width':'800px', 'width':'', 'height':'',
            'margin':'', 'padding':'', 'z-index':''
        });
        $('.modal-content', $dialog).css('height','');
        $msgs.css('max-height', '420px');
        $icon.removeClass('ti-zoom-out').addClass('ti-fullscreen');
    }
}

function cdp_sendPAIMessage(autoMsg) {
    var msg = autoMsg || $('#pai-chat-input').val().trim();
    if (!msg) return;

    var $msgs = $('#pai-chat-messages');
    var $input = $('#pai-chat-input');
    var $btn   = $('#pai-send-btn');

    // Clear input and character counter
    if (!autoMsg) {
        $input.val('');
        $('#pai-char-count').text('0/500').css('color', '#aaa');
    }

    // Clear initial spinner if first message
    if (paiHistory.length === 0 && autoMsg) {
        $msgs.html('');
    }

    // Show user message (skip for auto briefing)
    if (!autoMsg) {
        var timestamp = new Date().toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'});
        $msgs.append(
            '<div style="display:flex; justify-content:flex-end; margin-bottom:10px;">'
            + '<div style="max-width:75%;">'
            + '<div style="background:#0d6efd; color:#fff; padding:10px 14px; border-radius:16px 16px 4px 16px; font-size:13px; line-height:1.5; box-shadow:0 2px 8px rgba(13,110,253,0.2);">'
            + $('<div>').text(msg).html()
            + '</div>'
            + '<div style="text-align:right; font-size:10px; color:#aaa; margin-top:3px; padding-right:4px;">' + timestamp + '</div>'
            + '</div></div>'
        );
    }

    // Show typing indicator
    var typingId = 'typing-' + Date.now();
    $msgs.append(
        '<div id="' + typingId + '" style="display:flex; align-items:flex-start; margin-bottom:10px;">'
        + '<div style="background:linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color:#fff; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; margin-right:10px; flex-shrink:0; box-shadow:0 2px 6px rgba(13,110,253,0.3);">AI</div>'
        + '<div style="background:#fff; border:1px solid #e9ecef; padding:10px 14px; border-radius:4px 16px 16px 16px; font-size:13px; color:#888; box-shadow:0 2px 6px rgba(0,0,0,0.05);">'
        + '<i class="fa fa-circle" style="font-size:6px; animation: pulse 1.5s infinite;"></i> <i class="fa fa-circle" style="font-size:6px; animation: pulse 1.5s infinite 0.3s;"></i> <i class="fa fa-circle" style="font-size:6px; animation: pulse 1.5s infinite 0.6s;"></i> Analyzing...'
        + '</div></div>'
    );
    $msgs.scrollTop($msgs[0].scrollHeight);
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
        url: 'ajax/ai/ai_chat_ajax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            message: msg,
            history: JSON.stringify(paiHistory)
        },
        success: function(data) {
            $('#' + typingId).remove();
            var reply   = data.reply   || 'No response.';
            var actions = data.actions || [];

            // Play notification sound
            cdp_playNotificationSound();

            // Format reply text
            var html = cdp_formatPAIReply(reply);

            // Build action buttons if any
            var actionsHtml = '';
            if (actions.length > 0) {
                actionsHtml += '<div style="margin-top:12px; padding-top:12px; border-top:1px solid #e9ecef; display:flex; flex-wrap:wrap; gap:8px;">';
                actionsHtml += '<div style="font-size:10px; font-weight:600; color:#6c757d; width:100%; margin-bottom:4px;">QUICK ACTIONS:</div>';
                actions.forEach(function(act, idx) {
                    var btnId = 'pai-act-' + Date.now() + '-' + idx;
                    var color = '#0d6efd';
                    if (act.action === 'confirm_payment' || act.action === 'confirm_all_wire_payments') color = '#28a745';
                    if (act.action === 'update_status') color = '#fd7e14';
                    if (act.action === 'assign_driver') color = '#17a2b8';
                    actionsHtml += '<button id="' + btnId + '" '
                        + 'onclick="cdp_executeAction(' + JSON.stringify(act).replace(/"/g, '&quot;') + ', \'' + btnId + '\')" '
                        + 'style="background:' + color + '; color:#fff; border:none; padding:6px 14px; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer; transition: all 0.2s; box-shadow:0 2px 4px rgba(0,0,0,0.1);" '
                        + 'onmouseover="this.style.transform=\'translateY(-2px)\'; this.style.boxShadow=\'0 4px 8px rgba(0,0,0,0.15)\';" '
                        + 'onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 2px 4px rgba(0,0,0,0.1)\';" '
                        + 'title="' + (act.description || '') + '">'
                        + '<i class="ti-check-box"></i> ' + act.label
                        + '</button>';
                });
                actionsHtml += '</div>';
            }

            var timestamp = new Date().toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'});
            $msgs.append(
                '<div style="display:flex; align-items:flex-start; margin-bottom:14px;">'
                + '<div style="background:linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color:#fff; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; margin-right:10px; flex-shrink:0; box-shadow:0 2px 6px rgba(13,110,253,0.3);">AI</div>'
                + '<div style="max-width:85%;">'
                + '<div style="background:#fff; border:1px solid #e9ecef; padding:12px 16px; border-radius:4px 16px 16px 16px; font-size:13px; line-height:1.7; box-shadow:0 2px 6px rgba(0,0,0,0.05);">'
                + html + actionsHtml
                + '</div>'
                + '<div style="font-size:10px; color:#aaa; margin-top:3px; padding-left:4px;">' + timestamp + '</div>'
                + '</div></div>'
            );
            $msgs.scrollTop($msgs[0].scrollHeight);

            // Update history
            paiHistory.push({ role: 'user',      content: msg });
            paiHistory.push({ role: 'assistant', content: reply });
            if (paiHistory.length > 20) paiHistory = paiHistory.slice(-20);
        },
        error: function(xhr, status, error) {
            $('#' + typingId).remove();
            
            // Log detailed error info to console for debugging
            console.error('AI Chat Error:', {
                status: status,
                error: error,
                responseText: xhr.responseText,
                statusCode: xhr.status,
                readyState: xhr.readyState
            });
            
            // Try to parse error response
            var errorMsg = 'Could not reach AI service.';
            var detailedError = '';
            
            try {
                if (xhr.responseText) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        errorMsg = response.error;
                    } else if (response.reply) {
                        errorMsg = response.reply;
                    }
                }
            } catch(e) {
                // If response is not JSON, show raw text
                if (xhr.responseText && xhr.responseText.length < 500) {
                    detailedError = '<br><small>Server response: ' + $('<div>').text(xhr.responseText).html() + '</small>';
                }
            }
            
            // Check for specific network errors
            if (status === 'timeout') {
                errorMsg = 'Request timed out. The AI service took too long to respond.';
            } else if (status === 'parsererror') {
                errorMsg = 'Invalid response from server. Check PHP error logs.';
                detailedError = '<br><small>The server returned invalid JSON. Open browser console (F12) for details.</small>';
            } else if (xhr.status === 0) {
                errorMsg = 'Network error. Check if XAMPP/Apache is running and the file path is correct.';
                detailedError = '<br><small>Status code: 0 means the request didn\'t reach the server.</small>';
            } else if (xhr.status === 404) {
                errorMsg = 'AI endpoint not found (404). Check that ajax/ai/ai_chat_ajax.php exists.';
            } else if (xhr.status === 500) {
                errorMsg = 'Server error (500). Check PHP error logs in XAMPP.';
                detailedError = '<br><small>Open XAMPP control panel → Apache → Logs → Error Log</small>';
            }
            
            $msgs.append(
                '<div class="alert alert-danger m-2" style="font-size:12px; border-radius:8px; border-left:4px solid #dc3545;">'
                + '<i class="ti-alert"></i> <strong>Connection Error:</strong> ' + errorMsg + detailedError
                + '<br><br>Check your API key in <a href="tools.php?list=config_ai" style="font-weight:600;">AI Settings</a>.'
                + '<br><small style="opacity:0.7;">Technical: ' + status + ' (HTTP ' + xhr.status + ')</small>'
                + '</div>'
            );
            $msgs.scrollTop($msgs[0].scrollHeight);
        },
        complete: function() {
            $btn.prop('disabled', false).html('<i class="ti-arrow-right"></i>');
            $input.focus();
        }
    });
}

// -------------------------------------------------------
// ACTION FIELD SCHEMAS — defines what each action needs
// -------------------------------------------------------
var cdp_actionSchemas = {
    create_customer: {
        title: 'Create Customer',
        icon: 'ti-user',
        color: '#0d6efd',
        fields: [
            { key: 'fname', label: 'First Name',  type: 'text',  placeholder: 'e.g. John',              required: true  },
            { key: 'lname', label: 'Last Name',   type: 'text',  placeholder: 'e.g. Doe',               required: false },
            { key: 'email', label: 'Email',        type: 'email', placeholder: 'e.g. john@example.com',  required: true  },
            { key: 'phone', label: 'Phone',        type: 'text',  placeholder: 'e.g. +250788000000',     required: false }
        ]
    },
    create_shipment: {
        title: 'Create Shipment',
        icon: 'ti-package',
        color: '#17a2b8',
        fields: [
            { key: 'sender_id',      label: 'Sender ID',         type: 'number', placeholder: 'Customer ID',           required: true  },
            { key: 'recipient_name', label: 'Recipient Name',    type: 'text',   placeholder: 'e.g. Jane Doe',         required: true  },
            { key: 'recipient_addr', label: 'Recipient Address', type: 'text',   placeholder: 'e.g. Kigali, Rwanda',   required: false }
        ]
    },
    edit_shipment: {
        title: 'Edit Shipment',
        icon: 'ti-pencil',
        color: '#fd7e14',
        fields: [
            { key: 'order_id', label: 'Order ID', type: 'number', placeholder: 'Shipment ID to edit', required: true }
        ]
    },
    assign_driver: {
        title: 'Assign Driver',
        icon: 'ti-truck',
        color: '#17a2b8',
        fields: [
            { key: 'order_id',  label: 'Order ID',  type: 'number', placeholder: 'Shipment ID', required: true },
            { key: 'driver_id', label: 'Driver ID', type: 'number', placeholder: 'Driver ID',   required: true }
        ]
    },
    send_sms: {
        title: 'Send SMS',
        icon: 'ti-mobile',
        color: '#28a745',
        fields: [
            { key: 'phone',   label: 'Phone Number', type: 'text',     placeholder: 'e.g. +250788000000', required: true  },
            { key: 'message', label: 'Message',       type: 'textarea', placeholder: 'SMS message text',   required: true  }
        ]
    },
    send_email: {
        title: 'Send Email',
        icon: 'ti-email',
        color: '#6f42c1',
        fields: [
            { key: 'email',   label: 'Email Address', type: 'email',    placeholder: 'recipient@example.com', required: true  },
            { key: 'subject', label: 'Subject',        type: 'text',     placeholder: 'Email subject',         required: true  },
            { key: 'message', label: 'Message',        type: 'textarea', placeholder: 'Email body',            required: true  }
        ]
    },
    send_whatsapp: {
        title: 'Send WhatsApp',
        icon: 'ti-comment',
        color: '#25D366',
        fields: [
            { key: 'phone',   label: 'Phone Number', type: 'text',     placeholder: 'e.g. +250788000000', required: true  },
            { key: 'message', label: 'Message',       type: 'textarea', placeholder: 'WhatsApp message',   required: true  }
        ]
    },
    apply_discount: {
        title: 'Apply Discount',
        icon: 'ti-tag',
        color: '#e83e8c',
        fields: [
            { key: 'order_id', label: 'Order ID',        type: 'number', placeholder: 'Shipment ID',  required: true },
            { key: 'discount', label: 'Discount Amount', type: 'number', placeholder: 'e.g. 10.00',   required: true }
        ]
    },
    cancel_shipment: {
        title: 'Cancel Shipment',
        icon: 'ti-close',
        color: '#dc3545',
        fields: [
            { key: 'order_id', label: 'Order ID',          type: 'number', placeholder: 'Shipment ID to cancel',         required: true  },
            { key: 'reason',   label: 'Cancellation Reason', type: 'textarea', placeholder: 'Why is this being cancelled?', required: true  }
        ]
    },
    add_prealert: {
        title: 'Add Pre-Alert',
        icon: 'ti-bell',
        color: '#fd7e14',
        fields: [
            { key: 'tracking',    label: 'Tracking Number', type: 'text',   placeholder: 'e.g. PRY00123',          required: true  },
            { key: 'customer_id', label: 'Customer ID',     type: 'number', placeholder: 'Customer ID',            required: true  },
            { key: 'description', label: 'Description',     type: 'text',   placeholder: 'e.g. Electronics, 2kg',  required: false },
            { key: 'weight',      label: 'Weight (kg)',      type: 'number', placeholder: 'e.g. 2.5',               required: false }
        ]
    },
    update_customer: {
        title: 'Update Customer',
        icon: 'ti-pencil-alt',
        color: '#6f42c1',
        fields: [
            { key: 'customer_id', label: 'Customer ID', type: 'number', placeholder: 'Customer ID to update', required: true  },
            { key: 'phone',       label: 'New Phone',   type: 'text',   placeholder: 'e.g. +250788000000',    required: false },
            { key: 'email',       label: 'New Email',   type: 'email',  placeholder: 'new@email.com',         required: false },
            { key: 'address',     label: 'New Address', type: 'text',   placeholder: 'e.g. Kigali, Rwanda',   required: false }
        ]
    },
    refund_payment: {
        title: 'Refund Payment',
        icon: 'ti-money',
        color: '#dc3545',
        fields: [
            { key: 'order_id', label: 'Order ID',      type: 'number',   placeholder: 'Shipment ID',         required: true  },
            { key: 'amount',   label: 'Refund Amount', type: 'number',   placeholder: 'e.g. 15000',          required: true  },
            { key: 'reason',   label: 'Reason',        type: 'textarea', placeholder: 'Reason for refund',   required: true  }
        ]
    },
    add_charge: {
        title: 'Add Charge',
        icon: 'ti-receipt',
        color: '#fd7e14',
        fields: [
            { key: 'customer_id',  label: 'Customer ID',  type: 'number',   placeholder: 'Customer ID',          required: true  },
            { key: 'amount',       label: 'Amount',        type: 'number',   placeholder: 'e.g. 5000',            required: true  },
            { key: 'description',  label: 'Description',   type: 'text',     placeholder: 'e.g. Storage fee',     required: true  },
            { key: 'due_date',     label: 'Due Date',      type: 'date',     placeholder: '',                     required: false }
        ]
    },
    send_bulk_sms: {
        title: 'Send Bulk SMS',
        icon: 'ti-comments',
        color: '#28a745',
        fields: [
            { key: 'filter',  label: 'Send To',  type: 'text',     placeholder: 'all / customer_id / driver_id', required: true  },
            { key: 'message', label: 'Message',  type: 'textarea', placeholder: 'Message to send to all',        required: true  }
        ]
    },
    create_driver: {
        title: 'Create Driver',
        icon: 'ti-user',
        color: '#17a2b8',
        fields: [
            { key: 'fname',   label: 'First Name', type: 'text',  placeholder: 'e.g. Jean',            required: true  },
            { key: 'lname',   label: 'Last Name',  type: 'text',  placeholder: 'e.g. Pierre',          required: false },
            { key: 'email',   label: 'Email',       type: 'email', placeholder: 'driver@example.com',  required: true  },
            { key: 'phone',   label: 'Phone',       type: 'text',  placeholder: 'e.g. +250788000000',  required: false },
            { key: 'vehicle', label: 'Vehicle',     type: 'text',  placeholder: 'e.g. Toyota Hiace',   required: false }
        ]
    },
    generate_report: {
        title: 'Generate Report',
        icon: 'ti-file',
        color: '#343a40',
        fields: [
            { key: 'report_type', label: 'Report Type', type: 'text', placeholder: 'revenue / shipments / customers / drivers', required: true  },
            { key: 'start_date',  label: 'Start Date',  type: 'date', placeholder: '',                                          required: true  },
            { key: 'end_date',    label: 'End Date',    type: 'date', placeholder: '',                                          required: true  }
        ]
    },
    export_data: {
        title: 'Export Data',
        icon: 'ti-download',
        color: '#343a40',
        fields: [
            { key: 'data_type', label: 'Data Type', type: 'text', placeholder: 'shipments / customers / payments / drivers', required: true  },
            { key: 'format',    label: 'Format',    type: 'text', placeholder: 'csv / excel',                                required: true  }
        ]
    },
    // ── Shipment Operations ──────────────────────────────────────────
    mark_delivered: {
        title: 'Mark Delivered',
        icon: 'ti-check-box',
        color: '#28a745',
        fields: [
            { key: 'order_id',        label: 'Order ID',         type: 'number', placeholder: 'Shipment ID',             required: true  },
            { key: 'person_receives', label: 'Received By',      type: 'text',   placeholder: 'Name of person who received', required: true  },
            { key: 'driver_id',       label: 'Driver ID',        type: 'number', placeholder: 'Driver ID',               required: true  },
            { key: 'comment',         label: 'Note',             type: 'text',   placeholder: 'Optional delivery note',  required: false }
        ]
    },
    add_tracking_note: {
        title: 'Add Tracking Note',
        icon: 'ti-location-pin',
        color: '#17a2b8',
        fields: [
            { key: 'order_id',   label: 'Order ID',   type: 'number',   placeholder: 'Shipment ID',                    required: true  },
            { key: 'status_id',  label: 'Status ID',  type: 'number',   placeholder: '2=Processing, 4=In Transit…',   required: true  },
            { key: 'comment',    label: 'Comment',    type: 'textarea', placeholder: 'Tracking note / location',       required: true  }
        ]
    },
    delete_shipment: {
        title: 'Delete Shipment',
        icon: 'ti-trash',
        color: '#dc3545',
        fields: [
            { key: 'order_id', label: 'Order ID', type: 'number', placeholder: 'Shipment ID to delete', required: true }
        ]
    },
    bulk_update_status: {
        title: 'Bulk Update Status',
        icon: 'ti-layers',
        color: '#fd7e14',
        fields: [
            { key: 'order_ids', label: 'Order IDs (comma-separated)', type: 'text',   placeholder: 'e.g. 101,102,103',          required: true },
            { key: 'status_id', label: 'New Status ID',               type: 'number', placeholder: '4=In Transit, 8=Delivered', required: true }
        ]
    },
    bulk_assign_driver: {
        title: 'Bulk Assign Driver',
        icon: 'ti-truck',
        color: '#343a40',
        fields: [
            { key: 'order_ids', label: 'Order IDs (comma-separated)', type: 'text',   placeholder: 'e.g. 101,102,103', required: true },
            { key: 'driver_id', label: 'Driver ID',                   type: 'number', placeholder: 'Driver ID',         required: true }
        ]
    },
    // ── Package Module ───────────────────────────────────────────────
    mark_package_delivered: {
        title: 'Mark Package Delivered',
        icon: 'ti-package',
        color: '#28a745',
        fields: [
            { key: 'package_id',      label: 'Package ID',   type: 'number', placeholder: 'Customer package ID',        required: true  },
            { key: 'person_receives', label: 'Received By',  type: 'text',   placeholder: 'Person who received',        required: true  },
            { key: 'driver_id',       label: 'Driver ID',    type: 'number', placeholder: 'Driver ID',                  required: true  }
        ]
    },
    // ── Consolidate Module ───────────────────────────────────────────
    update_consolidate_driver: {
        title: 'Update Consolidate Driver',
        icon: 'ti-truck',
        color: '#6f42c1',
        fields: [
            { key: 'consolidate_id', label: 'Consolidate ID', type: 'number', placeholder: 'Consolidate shipment ID', required: true },
            { key: 'driver_id',      label: 'Driver ID',      type: 'number', placeholder: 'Driver ID',               required: true }
        ]
    },
    confirm_consolidate_payment: {
        title: 'Confirm Consolidate Payment',
        icon: 'ti-money',
        color: '#28a745',
        fields: [
            { key: 'consolidate_id', label: 'Consolidate ID', type: 'number', placeholder: 'Consolidate order ID', required: true },
            { key: 'customer_id',    label: 'Customer ID',    type: 'number', placeholder: 'Customer ID',          required: false }
        ]
    },
    // ── Pickup Module ────────────────────────────────────────────────
    accept_pickup: {
        title: 'Accept Pickup',
        icon: 'ti-check',
        color: '#28a745',
        fields: [
            { key: 'pickup_id', label: 'Pickup Order ID', type: 'number', placeholder: 'Pickup order ID to accept', required: true },
            { key: 'driver_id', label: 'Driver ID',       type: 'number', placeholder: 'Assign driver',            required: false }
        ]
    },
    cancel_pickup: {
        title: 'Cancel Pickup',
        icon: 'ti-close',
        color: '#dc3545',
        fields: [
            { key: 'pickup_id', label: 'Pickup Order ID', type: 'number',   placeholder: 'Pickup order ID',    required: true  },
            { key: 'reason',    label: 'Reason',          type: 'textarea', placeholder: 'Reason for cancel',  required: false }
        ]
    },
    // ── Customer Management ──────────────────────────────────────────
    delete_customer: {
        title: 'Delete Customer',
        icon: 'ti-trash',
        color: '#dc3545',
        fields: [
            { key: 'customer_id', label: 'Customer ID', type: 'number', placeholder: 'Customer ID to delete', required: true },
            { key: 'confirm',     label: 'Type DELETE to confirm', type: 'text', placeholder: 'DELETE', required: true }
        ]
    },
    reset_customer_password: {
        title: 'Reset Customer Password',
        icon: 'ti-lock',
        color: '#fd7e14',
        fields: [
            { key: 'customer_id',   label: 'Customer ID',   type: 'number', placeholder: 'Customer ID',        required: true  },
            { key: 'new_password',  label: 'New Password',  type: 'text',   placeholder: 'Leave blank = auto-generate', required: false }
        ]
    },
    // ── Driver Management ────────────────────────────────────────────
    edit_driver: {
        title: 'Edit Driver',
        icon: 'ti-pencil',
        color: '#17a2b8',
        fields: [
            { key: 'driver_id', label: 'Driver ID', type: 'number', placeholder: 'Driver ID to update',  required: true  },
            { key: 'fname',     label: 'First Name',type: 'text',   placeholder: 'New first name',        required: false },
            { key: 'lname',     label: 'Last Name', type: 'text',   placeholder: 'New last name',         required: false },
            { key: 'phone',     label: 'Phone',     type: 'text',   placeholder: 'New phone number',      required: false },
            { key: 'vehicle',   label: 'Vehicle',   type: 'text',   placeholder: 'e.g. Toyota Hiace',     required: false }
        ]
    },
    delete_driver: {
        title: 'Delete Driver',
        icon: 'ti-trash',
        color: '#dc3545',
        fields: [
            { key: 'driver_id', label: 'Driver ID', type: 'number', placeholder: 'Driver ID to delete', required: true },
            { key: 'confirm',   label: 'Type DELETE to confirm', type: 'text', placeholder: 'DELETE',   required: true }
        ]
    },
    // ── Reports ──────────────────────────────────────────────────────
    report_payments_received: {
        title: 'Payments Received Report',
        icon: 'ti-stats-up',
        color: '#28a745',
        fields: [
            { key: 'start_date',   label: 'Start Date',   type: 'date',   placeholder: '',                   required: true  },
            { key: 'end_date',     label: 'End Date',     type: 'date',   placeholder: '',                   required: true  },
            { key: 'customer_id',  label: 'Customer ID',  type: 'number', placeholder: '0 = all customers', required: false }
        ]
    },
    report_driver_performance: {
        title: 'Driver Performance Report',
        icon: 'ti-bar-chart',
        color: '#17a2b8',
        fields: [
            { key: 'start_date', label: 'Start Date', type: 'date',   placeholder: '',            required: true  },
            { key: 'end_date',   label: 'End Date',   type: 'date',   placeholder: '',            required: true  },
            { key: 'driver_id',  label: 'Driver ID',  type: 'number', placeholder: '0 = all',    required: false }
        ]
    },
    report_customer_balance: {
        title: 'Customer Balance Report',
        icon: 'ti-pie-chart',
        color: '#6f42c1',
        fields: [
            { key: 'start_date',  label: 'Start Date',  type: 'date',   placeholder: '',                   required: true  },
            { key: 'end_date',    label: 'End Date',    type: 'date',   placeholder: '',                   required: true  },
            { key: 'customer_id', label: 'Customer ID', type: 'number', placeholder: '0 = all customers', required: false }
        ]
    },
    // ── SMS Notification ─────────────────────────────────────────────
    notify_sms_shipment: {
        title: 'Notify Customer via SMS',
        icon: 'ti-mobile',
        color: '#20c997',
        fields: [
            { key: 'order_id', label: 'Order ID',     type: 'number',   placeholder: 'Shipment ID',            required: true  },
            { key: 'message',  label: 'SMS Message',  type: 'textarea', placeholder: 'Custom message to send', required: true  }
        ]
    },
    // ── Accounts Receivable ───────────────────────────────────────────
    record_payment: {
        title: 'Record Payment',
        icon: 'ti-credit-card',
        color: '#28a745',
        fields: [
            { key: 'order_id',      label: 'Order ID',       type: 'number',   placeholder: 'Order/Shipment ID',       required: true  },
            { key: 'amount',        label: 'Amount Paid',    type: 'number',   placeholder: 'e.g. 15000',              required: true  },
            { key: 'payment_type',  label: 'Payment Type',   type: 'number',   placeholder: 'Payment method ID',       required: true  },
            { key: 'notes',         label: 'Notes',          type: 'text',     placeholder: 'Optional note',           required: false }
        ]
    },
    delete_charge: {
        title: 'Delete Charge',
        icon: 'ti-trash',
        color: '#dc3545',
        fields: [
            { key: 'charge_id', label: 'Charge ID', type: 'number', placeholder: 'Charge record ID to delete', required: true }
        ]
    },
    // ── Recipients ───────────────────────────────────────────────────
    create_recipient: {
        title: 'Create Recipient',
        icon: 'ti-user',
        color: '#17a2b8',
        fields: [
            { key: 'fname',   label: 'First Name', type: 'text',  placeholder: 'e.g. Alice',             required: true  },
            { key: 'lname',   label: 'Last Name',  type: 'text',  placeholder: 'e.g. Smith',             required: true  },
            { key: 'phone',   label: 'Phone',       type: 'text',  placeholder: 'e.g. +250788000000',    required: true  },
            { key: 'email',   label: 'Email',       type: 'email', placeholder: 'alice@example.com',     required: false },
            { key: 'address', label: 'Address',     type: 'text',  placeholder: 'e.g. Kigali, Rwanda',  required: false }
        ]
    },
    delete_recipient: {
        title: 'Delete Recipient',
        icon: 'ti-trash',
        color: '#dc3545',
        fields: [
            { key: 'recipient_id', label: 'Recipient ID', type: 'number', placeholder: 'Recipient ID to delete', required: true },
            { key: 'confirm',      label: 'Type DELETE to confirm', type: 'text', placeholder: 'DELETE',         required: true }
        ]
    },
    // ── Employees / Staff ─────────────────────────────────────────────
    create_employee: {
        title: 'Create Employee',
        icon: 'ti-id-badge',
        color: '#6f42c1',
        fields: [
            { key: 'fname',    label: 'First Name',   type: 'text',   placeholder: 'e.g. Paul',              required: true  },
            { key: 'lname',    label: 'Last Name',    type: 'text',   placeholder: 'e.g. Mugisha',           required: false },
            { key: 'email',    label: 'Email',         type: 'email',  placeholder: 'employee@example.com',  required: true  },
            { key: 'phone',    label: 'Phone',         type: 'text',   placeholder: 'e.g. +250788000000',    required: false },
            { key: 'username', label: 'Username',      type: 'text',   placeholder: 'e.g. paul.mugisha',     required: true  }
        ]
    },
    delete_employee: {
        title: 'Delete Employee',
        icon: 'ti-trash',
        color: '#dc3545',
        fields: [
            { key: 'employee_id', label: 'Employee ID', type: 'number', placeholder: 'Employee user ID',          required: true },
            { key: 'confirm',     label: 'Type DELETE to confirm', type: 'text', placeholder: 'DELETE',           required: true }
        ]
    },
    reset_employee_password: {
        title: 'Reset Employee Password',
        icon: 'ti-lock',
        color: '#fd7e14',
        fields: [
            { key: 'employee_id',  label: 'Employee ID',  type: 'number', placeholder: 'Employee user ID',           required: true  },
            { key: 'new_password', label: 'New Password', type: 'text',   placeholder: 'Leave blank = auto-generate', required: false }
        ]
    },
    // ── WhatsApp Bulk ─────────────────────────────────────────────────
    send_whatsapp_bulk: {
        title: 'Send Bulk WhatsApp',
        icon: 'ti-comments',
        color: '#25D366',
        fields: [
            { key: 'filter',  label: 'Send To',  type: 'text',     placeholder: 'all / customer_id / order_id', required: true  },
            { key: 'message', label: 'Message',   type: 'textarea', placeholder: 'WhatsApp message text',        required: true  }
        ]
    },
    // ── Pre-alert ─────────────────────────────────────────────────────
    delete_prealert: {
        title: 'Delete Pre-Alert',
        icon: 'ti-trash',
        color: '#dc3545',
        fields: [
            { key: 'prealert_id', label: 'Pre-Alert ID', type: 'number', placeholder: 'Pre-alert ID to delete', required: true }
        ]
    },
    // ── Consolidate Status ────────────────────────────────────────────
    update_consolidate_status: {
        title: 'Update Consolidate Status',
        icon: 'ti-layers',
        color: '#fd7e14',
        fields: [
            { key: 'consolidate_id', label: 'Consolidate ID', type: 'number', placeholder: 'Consolidate order ID',             required: true  },
            { key: 'status_id',      label: 'Status ID',      type: 'number', placeholder: '2=Processing, 4=In Transit, 8=Delivered', required: true  },
            { key: 'comment',        label: 'Comment',        type: 'text',   placeholder: 'Optional tracking note',           required: false }
        ]
    },
    // ── Reports ───────────────────────────────────────────────────────
    report_general: {
        title: 'General Shipments Report',
        icon: 'ti-bar-chart',
        color: '#343a40',
        fields: [
            { key: 'start_date', label: 'Start Date', type: 'date',   placeholder: '', required: true  },
            { key: 'end_date',   label: 'End Date',   type: 'date',   placeholder: '', required: true  },
            { key: 'status_id',  label: 'Status ID',  type: 'number', placeholder: '0 = all statuses', required: false }
        ]
    },
    report_pickup_summary: {
        title: 'Pickup Operations Report',
        icon: 'ti-map',
        color: '#17a2b8',
        fields: [
            { key: 'start_date', label: 'Start Date', type: 'date',   placeholder: '', required: true  },
            { key: 'end_date',   label: 'End Date',   type: 'date',   placeholder: '', required: true  },
            { key: 'driver_id',  label: 'Driver ID',  type: 'number', placeholder: '0 = all drivers', required: false }
        ]
    },
    report_packages_registered: {
        title: 'Customer Packages Report',
        icon: 'ti-package',
        color: '#6f42c1',
        fields: [
            { key: 'start_date',  label: 'Start Date',  type: 'date',   placeholder: '', required: true  },
            { key: 'end_date',    label: 'End Date',    type: 'date',   placeholder: '', required: true  },
            { key: 'customer_id', label: 'Customer ID', type: 'number', placeholder: '0 = all customers', required: false }
        ]
    }
};

// Current pending action waiting for modal input
var cdp_pendingAction   = null;
var cdp_pendingBtnId    = null;
var cdp_pendingOrigText = null;
var cdp_pendingOrigBg   = null;

// Show the AI input modal for missing fields
function cdp_showActionInputModal(act, btnId, originalText, originalBg) {
    var schema = cdp_actionSchemas[act.action];
    if (!schema) return; // No schema — nothing to show

    cdp_pendingAction   = act;
    cdp_pendingBtnId    = btnId;
    cdp_pendingOrigText = originalText;
    cdp_pendingOrigBg   = originalBg;

    // Normalize common alternate key names the AI might use
    var normalized = $.extend({}, act);
    if (!normalized.fname && normalized.name) {
        var parts = normalized.name.split(' ');
        normalized.fname = parts[0] || '';
        normalized.lname = normalized.lname || parts.slice(1).join(' ') || '';
    }
    if (!normalized.fname && normalized.customer_name) {
        var parts = normalized.customer_name.split(' ');
        normalized.fname = parts[0] || '';
        normalized.lname = normalized.lname || parts.slice(1).join(' ') || '';
    }
    if (!normalized.email && normalized.customer_email) normalized.email = normalized.customer_email;
    if (!normalized.phone && normalized.customer_phone) normalized.phone = normalized.customer_phone;
    if (!normalized.phone && normalized.phone_number)   normalized.phone = normalized.phone_number;

    cdp_pendingAction = normalized;

    // Set header
    $('#pai-input-modal-title').text(schema.title);
    $('#pai-input-modal-icon').attr('class', schema.icon);
    $('#pai-input-modal-header').css('background', schema.color);
    $('#pai-input-modal-submit').css('background', schema.color);

    // Build fields
    var fieldsHtml = '';
    schema.fields.forEach(function(f) {
        var val = act[f.key] || '';
        var badge = f.required
            ? '<span style="color:#dc3545; font-size:10px; margin-left:4px;">*required</span>'
            : '<span style="color:#6c757d; font-size:10px; margin-left:4px;">optional</span>';

        fieldsHtml += '<div>';
        fieldsHtml += '<label style="font-size:11px; font-weight:600; color:#495057; margin-bottom:4px; display:flex; align-items:center;">'
                    + f.label + badge + '</label>';

        var borderColor = '#ced4da';
        var style = 'width:100%; padding:7px 10px; border:1px solid ' + borderColor + '; border-radius:6px; font-size:12px; outline:none; transition:border-color 0.2s;';

        if (f.type === 'textarea') {
            fieldsHtml += '<textarea id="pai-field-' + f.key + '" rows="3" placeholder="' + f.placeholder + '" style="' + style + ' resize:vertical;" oninput="this.style.borderColor=this.value.trim()?\'#28a745\':\'#ced4da\'">' + val + '</textarea>';
        } else {
            fieldsHtml += '<input type="' + f.type + '" id="pai-field-' + f.key + '" value="' + val + '" placeholder="' + f.placeholder + '" style="' + style + '" oninput="this.style.borderColor=this.value.trim()?\'#28a745\':\'#ced4da\'">';
        }
        fieldsHtml += '<small id="pai-field-err-' + f.key + '" style="color:#dc3545; font-size:10px; display:none;"><i class="ti-alert"></i> This field is required</small>';
        fieldsHtml += '</div>';
    });

    $('#pai-input-modal-fields').html(fieldsHtml);
    $('#pai-input-modal').modal('show');
}

// Submit handler for the input modal
function cdp_submitActionModal() {
    if (!cdp_pendingAction) return;

    var schema = cdp_actionSchemas[cdp_pendingAction.action];
    if (!schema) return;

    // Collect values and validate
    var updatedAct = $.extend({}, cdp_pendingAction);
    var valid = true;

    schema.fields.forEach(function(f) {
        var val = $('#pai-field-' + f.key).val().trim();
        updatedAct[f.key] = val;
        if (f.required && !val) {
            $('#pai-field-' + f.key).css('border-color', '#dc3545');
            $('#pai-field-err-' + f.key).show();
            valid = false;
        } else {
            $('#pai-field-' + f.key).css('border-color', val ? '#28a745' : '#ced4da');
            $('#pai-field-err-' + f.key).hide();
        }
    });

    if (!valid) {
        // Shake the modal
        var $modal = $('#pai-input-modal .modal-content');
        $modal.css('animation', 'none');
        setTimeout(function() { $modal.css('animation', 'shake 0.4s ease'); }, 10);
        return;
    }

    $('#pai-input-modal').modal('hide');

    // Fire the action with completed data
    var btnId    = cdp_pendingBtnId;
    var origText = cdp_pendingOrigText;
    var origBg   = cdp_pendingOrigBg;

    cdp_pendingAction = null;
    cdp_sendActionToServer(updatedAct, btnId, origText, origBg);
}

// Core AJAX sender (shared by normal execute and modal submit)
function cdp_sendActionToServer(act, btnId, originalText, originalBg) {
    var $btn = $('#' + btnId);
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

    $.ajax({
        url: 'ajax/ai/ai_action_ajax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            action:  act.action,
            payload: JSON.stringify(act)
        },
        success: function(res) {
            if (res.success) {
                $btn.css('background', '#28a745').html('<i class="ti-check"></i> Done');
                cdp_playNotificationSound();

                var $msgs = $('#pai-chat-messages');
                $msgs.append(
                    '<div style="display:flex; justify-content:center; margin-bottom:12px; animation: slideIn 0.3s ease;">'
                    + '<div style="background:#d4edda; color:#155724; padding:8px 16px; border-radius:20px; font-size:12px; font-weight:600; box-shadow:0 2px 6px rgba(40,167,69,0.2); border:1px solid #c3e6cb;">'
                    + '<i class="ti-check"></i> ' + res.message
                    + '</div></div>'
                );
                $msgs.scrollTop($msgs[0].scrollHeight);

                setTimeout(function(){ $btn.prop('disabled', true).css('opacity', '0.6'); }, 1500);

            } else if (res.needs_input) {
                // Server says fields are missing — open modal as fallback
                $btn.prop('disabled', false).html(originalText).css('background', originalBg);
                cdp_showActionInputModal(act, btnId, originalText, originalBg);

            } else {
                var errorMsg = res.message || 'Action failed';
                var $msgs = $('#pai-chat-messages');
                $msgs.append(
                    '<div style="display:flex; justify-content:center; margin-bottom:12px;">'
                    + '<div style="background:#f8d7da; color:#721c24; padding:8px 16px; border-radius:20px; font-size:12px; font-weight:600; box-shadow:0 2px 6px rgba(220,53,69,0.2); border:1px solid #f5c6cb;">'
                    + '<i class="ti-close"></i> ' + errorMsg
                    + '</div></div>'
                );
                $msgs.scrollTop($msgs[0].scrollHeight);

                $btn.prop('disabled', false).css('background', '#dc3545').html('<i class="ti-close"></i> Failed');
                setTimeout(function(){ $btn.css('background', originalBg).prop('disabled', false).html(originalText); }, 3000);
            }
        },
        error: function(xhr, status, error) {
            var $msgs = $('#pai-chat-messages');
            $msgs.append(
                '<div style="display:flex; justify-content:center; margin-bottom:12px;">'
                + '<div style="background:#f8d7da; color:#721c24; padding:8px 16px; border-radius:20px; font-size:12px; font-weight:600;">'
                + '<i class="ti-alert"></i> Network error: ' + (error || 'Connection failed')
                + '</div></div>'
            );
            $msgs.scrollTop($msgs[0].scrollHeight);
            $btn.prop('disabled', false).css('background', '#dc3545').html('<i class="ti-close"></i> Error');
            setTimeout(function(){ $btn.css('background', originalBg).prop('disabled', false).html(originalText); }, 3000);
        }
    });
}

// Execute an action button click
function cdp_executeAction(act, btnId) {
    var $btn = $('#' + btnId);
    var originalText = $btn.html();
    var originalBg = $btn.css('background-color');

    // Normalize alternate field names before checking
    var normalized = $.extend({}, act);
    if (!normalized.fname && normalized.name) {
        var parts = normalized.name.split(' ');
        normalized.fname = parts[0] || '';
        normalized.lname = normalized.lname || parts.slice(1).join(' ') || '';
    }
    if (!normalized.fname && normalized.customer_name) {
        var parts = normalized.customer_name.split(' ');
        normalized.fname = parts[0] || '';
        normalized.lname = normalized.lname || parts.slice(1).join(' ') || '';
    }
    if (!normalized.email && normalized.customer_email) normalized.email = normalized.customer_email;
    if (!normalized.phone && normalized.customer_phone) normalized.phone = normalized.customer_phone;
    if (!normalized.phone && normalized.phone_number)   normalized.phone = normalized.phone_number;

    // Check if this action has a schema and any required fields are missing
    var schema = cdp_actionSchemas[normalized.action];
    if (schema) {
        var missingRequired = schema.fields.some(function(f) {
            return f.required && (!normalized[f.key] || String(normalized[f.key]).trim() === '' || normalized[f.key] === 0);
        });
        if (missingRequired) {
            // Show modal instead of firing and failing
            cdp_showActionInputModal(normalized, btnId, originalText, originalBg);
            return;
        }
    }

    // All fields present — fire directly
    cdp_sendActionToServer(normalized, btnId, originalText, originalBg);
}

function cdp_formatPAIReply(text) {
    // Check for special visual markers
    var chartId = 'chart-' + Date.now();
    
    // Parse VISUAL_CARDS if present
    if (text.includes('VISUAL_CARDS:')) {
        var match = text.match(/VISUAL_CARDS:(\{.*?\})/s);
        if (match) {
            try {
                var cards = JSON.parse(match[1]);
                var cardsHtml = cdp_createStatCards(cards.stats);
                text = text.replace(/VISUAL_CARDS:\{.*?\}/s, cardsHtml);
            } catch(e) {
                console.error('Failed to parse VISUAL_CARDS:', e);
            }
        }
    }
    
    // Parse LINE_CHART if present
    if (text.includes('LINE_CHART:')) {
        var match = text.match(/LINE_CHART:(\{.*?\})/s);
        if (match) {
            try {
                var data = JSON.parse(match[1]);
                var chartHtml = cdp_createLineChart(data, chartId + '-line');
                text = text.replace(/LINE_CHART:\{.*?\}/s, chartHtml);
            } catch(e) {
                console.error('Failed to parse LINE_CHART:', e);
            }
        }
    }
    
    // Parse BAR_CHART if present
    if (text.includes('BAR_CHART:')) {
        var match = text.match(/BAR_CHART:(\{.*?\})/s);
        if (match) {
            try {
                var data = JSON.parse(match[1]);
                var chartHtml = cdp_createBarChart(data, chartId + '-bar');
                text = text.replace(/BAR_CHART:\{.*?\}/s, chartHtml);
            } catch(e) {
                console.error('Failed to parse BAR_CHART:', e);
            }
        }
    }
    
    // Parse PIE_CHART if present
    if (text.includes('PIE_CHART:')) {
        var match = text.match(/PIE_CHART:(\{.*?\})/s);
        if (match) {
            try {
                var data = JSON.parse(match[1]);
                var chartHtml = cdp_createPieChart(data, chartId + '-pie', false);
                text = text.replace(/PIE_CHART:\{.*?\}/s, chartHtml);
            } catch(e) {
                console.error('Failed to parse PIE_CHART:', e);
            }
        }
    }
    
    // Parse DATA_TABLE if present
    if (text.includes('DATA_TABLE:')) {
        var match = text.match(/DATA_TABLE:(\{.*?\})/s);
        if (match) {
            try {
                var data = JSON.parse(match[1]);
                var tableHtml = cdp_createDataTable(data);
                text = text.replace(/DATA_TABLE:\{.*?\}/s, tableHtml);
            } catch(e) {
                console.error('Failed to parse DATA_TABLE:', e);
            }
        }
    }
    
    // Parse SUGGESTIONS: chips — renders as clickable quick-send buttons
    if (text.includes('SUGGESTIONS:')) {
        var sugMatch = text.match(/SUGGESTIONS:\[(.+?)\]/s);
        if (sugMatch) {
            try {
                var suggestions = JSON.parse('[' + sugMatch[1] + ']');
                var chipsHtml = '<div style="display:flex; flex-wrap:wrap; gap:6px; margin:10px 0 4px 0;">';
                suggestions.forEach(function(sug) {
                    chipsHtml += '<button onclick="cdp_sendPAIMessage(\'' + sug.replace(/'/g, "\\'") + '\')" '
                        + 'style="background:#f0f4ff; color:#0d6efd; border:1px solid #c5d6ff; padding:5px 12px; '
                        + 'border-radius:20px; font-size:11px; font-weight:600; cursor:pointer; transition:all 0.2s;" '
                        + 'onmouseover="this.style.background=\'#0d6efd\';this.style.color=\'#fff\'" '
                        + 'onmouseout="this.style.background=\'#f0f4ff\';this.style.color=\'#0d6efd\'">'
                        + sug + '</button>';
                });
                chipsHtml += '</div>';
                text = text.replace(/SUGGESTIONS:\[.+?\]/s, chipsHtml);
            } catch(e) {
                text = text.replace(/SUGGESTIONS:\[.+?\]/s, '');
            }
        }
    }

    // Format basic text (bold, italic, bullets)
    text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    text = text.replace(/\*(.+?)\*/g, '<em>$1</em>');
    
    var lines = text.split('\n');
    var html  = '';
    lines.forEach(function(line) {
        line = line.trim();
        if (!line) { html += '<div style="height:6px;"></div>'; return; }
        if (line.match(/^[-•*]\s+/)) {
            line = line.replace(/^[-•*]\s+/, '');
            html += '<div style="padding-left:12px; margin-bottom:4px; display:flex; gap:6px;"><span style="color:#0d6efd; flex-shrink:0;">•</span><span>' + line + '</span></div>';
        } else if (line.includes('<div') || line.includes('<table') || line.includes('<canvas')) {
            // Already HTML, don't wrap
            html += line;
        } else {
            html += '<div style="margin-bottom:4px;">' + line + '</div>';
        }
    });
    return html;
}

// Enter key to send - FIXED VERSION with better event handling
$('#modal-pai').on('keydown', '#pai-chat-input', function(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        e.preventDefault();
        e.stopPropagation();
        cdp_sendPAIMessage();
        return false;
    }
});

// Also handle it at document level as backup
$(document).on('keydown', '#pai-chat-input', function(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        e.preventDefault();
        e.stopPropagation();
        cdp_sendPAIMessage();
        return false;
    }
});

// Character counter
$(document).on('input', '#pai-chat-input', function() {
    var len = $(this).val().length;
    $('#pai-char-count').text(len + '/500');
    if (len > 500) {
        $(this).val($(this).val().substring(0, 500));
        $('#pai-char-count').css('color', '#dc3545');
    } else {
        $('#pai-char-count').css('color', '#aaa');
    }
});

// Settings functions
function cdp_togglePAISettings() {
    $('#pai-settings-panel').slideToggle(200);
    // Load permissions when opening
    if ($('#pai-settings-panel').is(':visible')) {
        cdp_loadAIPermissions();
    }
}

function cdp_loadAIPermissions() {
    $('#pai-permissions-loading').show();
    $('#pai-permissions-list').hide();
    
    $.ajax({
        url: 'ajax/ai/get_permissions_ajax.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                cdp_renderPermissions(data.permissions);
            } else {
                $('#pai-permissions-loading').html('<div style="color:#dc3545; font-size:12px;"><i class="ti-alert"></i> Failed to load permissions</div>');
            }
        },
        error: function() {
            $('#pai-permissions-loading').html('<div style="color:#dc3545; font-size:12px;"><i class="ti-alert"></i> Error loading permissions</div>');
        }
    });
}

function cdp_renderPermissions(perms) {
    var html = '';
    
    // Autopilot
    html += '<div style="margin-bottom:12px; padding:10px; background:' + (perms.autopilot.enabled ? '#d4edda' : '#f8f9fa') + '; border-radius:6px; border-left:3px solid ' + (perms.autopilot.enabled ? '#28a745' : '#6c757d') + ';">';
    html += '<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">';
    html += '<div style="display:flex; align-items:center; gap:8px;">';
    html += '<i class="' + (perms.autopilot.enabled ? 'ti-bolt' : 'ti-hand-stop') + '" style="font-size:16px; color:' + (perms.autopilot.enabled ? '#28a745' : '#6c757d') + ';"></i>';
    html += '<strong style="font-size:12px; color:#495057;">Autopilot Mode</strong>';
    html += '</div>';
    html += '<label class="switch" style="margin:0;"><input type="checkbox" id="perm-autopilot-enabled" ' + (perms.autopilot.enabled ? 'checked' : '') + ' onchange="cdp_togglePermission(\'autopilot_enabled\', this.checked)"><span class="slider"></span></label>';
    html += '</div>';
    if (perms.autopilot.enabled) {
        html += '<div style="display:flex; align-items:center; gap:8px; margin-top:8px;">';
        html += '<small style="font-size:10px; color:#155724; flex:1;">Threshold:</small>';
        html += '<input type="number" id="perm-autopilot-threshold" value="' + perms.autopilot.threshold + '" min="1" max="50" style="width:60px; font-size:11px; padding:3px 6px; border:1px solid #28a745; border-radius:4px;" onchange="cdp_updateAutopilotThreshold(this.value)">';
        html += '<small style="font-size:10px; color:#155724;">items</small>';
        html += '</div>';
    }
    html += '</div>';
    
    // Permission Categories
    var categories = [
        {title: 'Core Actions', icon: 'ti-bolt', key: 'actions', data: perms.actions, color: '#0d6efd'},
        {title: 'Communication', icon: 'ti-email', key: 'communication', data: perms.communication, color: '#17a2b8'},
        {title: 'Customer Management', icon: 'ti-user', key: 'customer_management', data: perms.customer_management, color: '#6f42c1'},
        {title: 'Financial', icon: 'ti-money', key: 'financial', data: perms.financial, color: '#28a745'},
        {title: 'Reporting', icon: 'ti-file', key: 'reporting', data: perms.reporting, color: '#fd7e14'},
        {title: 'Advanced', icon: 'ti-rocket', key: 'advanced', data: perms.advanced, color: '#e83e8c'}
    ];
    
    categories.forEach(function(cat) {
        var enabled = Object.values(cat.data).filter(function(v) { return v === true; }).length;
        var total = Object.keys(cat.data).length;
        
        html += '<div style="margin-bottom:8px; padding:8px; background:#fff; border-radius:6px; border:1px solid #e9ecef;">';
        html += '<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">';
        html += '<div style="display:flex; align-items:center; gap:6px;">';
        html += '<i class="' + cat.icon + '" style="color:' + cat.color + '; font-size:14px;"></i>';
        html += '<strong style="font-size:11px; color:#495057;">' + cat.title + '</strong>';
        html += '</div>';
        html += '<span style="font-size:10px; color:' + (enabled > 0 ? '#28a745' : '#dc3545') + '; font-weight:600;">' + enabled + '/' + total + ' enabled</span>';
        html += '</div>';
        
        html += '<div style="display:grid; grid-template-columns: 1fr 1fr; gap:5px;">';
        for (var key in cat.data) {
            var label = key.replace(/_/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
            var isEnabled = cat.data[key];
            var permKey = cat.key + '_' + key;
            
            html += '<div style="display:flex; align-items:center; justify-content:space-between; padding:4px 7px; background:' + (isEnabled ? '#e7f3ff' : '#f8f9fa') + '; border-radius:4px; border:1px solid ' + (isEnabled ? '#0d6efd' : '#e9ecef') + '; min-width:0;">';
            html += '<span style="font-size:10px; color:#495057; flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-right:4px;">' + label + '</span>';
            html += '<label class="switch switch-sm" style="margin:0; flex-shrink:0;"><input type="checkbox" ' + (isEnabled ? 'checked' : '') + ' onchange="cdp_togglePermission(\'' + permKey + '\', this.checked)"><span class="slider"></span></label>';
            html += '</div>';
        }
        html += '</div>';
        html += '</div>';
    });
    
    $('#pai-permissions-loading').hide();
    $('#pai-permissions-list').html(html).show();
}

// Toggle a single permission
function cdp_togglePermission(permKey, enabled) {
    var $btn = $('<span>').text('Saving...');
    
    $.ajax({
        url: 'ajax/ai/update_permission_ajax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            permission: permKey,
            enabled: enabled ? 1 : 0
        },
        success: function(res) {
            if (res.success) {
                // Show success notification
                var $msgs = $('#pai-chat-messages');
                $msgs.append(
                    '<div style="display:flex; justify-content:center; margin-bottom:12px; animation: slideIn 0.3s ease;">'
                    + '<div style="background:#d4edda; color:#155724; padding:6px 14px; border-radius:20px; font-size:11px; font-weight:600; box-shadow:0 2px 6px rgba(40,167,69,0.2);">'
                    + '<i class="ti-check"></i> ' + res.message
                    + '</div></div>'
                );
                $msgs.scrollTop($msgs[0].scrollHeight);
                
                // Reload permissions to reflect changes
                setTimeout(function() {
                    cdp_loadAIPermissions();
                }, 500);
            } else {
                alert('Failed to update permission: ' + res.message);
                // Reload to revert checkbox
                cdp_loadAIPermissions();
            }
        },
        error: function() {
            alert('Error updating permission. Please try again.');
            cdp_loadAIPermissions();
        }
    });
}

// Update autopilot threshold
function cdp_updateAutopilotThreshold(value) {
    $.ajax({
        url: 'ajax/ai/update_permission_ajax.php',
        type: 'POST',
        dataType: 'json',
        data: {
            permission: 'autopilot_threshold',
            value: parseInt(value)
        },
        success: function(res) {
            if (res.success) {
                var $msgs = $('#pai-chat-messages');
                $msgs.append(
                    '<div style="display:flex; justify-content:center; margin-bottom:12px;">'
                    + '<div style="background:#d4edda; color:#155724; padding:6px 14px; border-radius:20px; font-size:11px; font-weight:600;">'
                    + '<i class="ti-check"></i> Autopilot threshold updated to ' + value + ' items'
                    + '</div></div>'
                );
                $msgs.scrollTop($msgs[0].scrollHeight);
            }
        }
    });
}

function cdp_loadPAISettings() {
    try {
        var saved = localStorage.getItem('paiSettings');
        if (saved) {
            paiSettings = JSON.parse(saved);
            $('#pai-setting-provider').val(paiSettings.provider || 'groq');
            $('#pai-setting-length').val(paiSettings.length || 'normal');
            $('#pai-setting-autorefresh').val(paiSettings.autorefresh || 0);
            $('#pai-setting-sound').val(paiSettings.sound || 1);
        }
    } catch(e) {
        console.log('Failed to load settings:', e);
    }
}

function cdp_savePAISettings() {
    paiSettings.provider = $('#pai-setting-provider').val();
    paiSettings.length = $('#pai-setting-length').val();
    paiSettings.autorefresh = parseInt($('#pai-setting-autorefresh').val());
    paiSettings.sound = parseInt($('#pai-setting-sound').val());
    
    try {
        localStorage.setItem('paiSettings', JSON.stringify(paiSettings));
        
        // Show success message
        var $msgs = $('#pai-chat-messages');
        $msgs.append(
            '<div style="display:flex; justify-content:center; margin-bottom:8px;">'
            + '<div style="background:#d4edda; color:#155724; padding:5px 14px; border-radius:20px; font-size:12px;">'
            + '✓ Settings saved successfully'
            + '</div></div>'
        );
        $msgs.scrollTop($msgs[0].scrollHeight);
        
        // Setup auto-refresh
        cdp_setupAutoRefresh();
        
        $('#pai-settings-panel').slideUp(200);
    } catch(e) {
        alert('Failed to save settings: ' + e.message);
    }
}

function cdp_setupAutoRefresh() {
    // Clear existing interval
    if (paiAutoRefreshInterval) {
        clearInterval(paiAutoRefreshInterval);
        paiAutoRefreshInterval = null;
    }
    
    // Setup new interval if enabled
    if (paiSettings.autorefresh > 0) {
        paiAutoRefreshInterval = setInterval(function() {
            cdp_quickAction('briefing');
        }, paiSettings.autorefresh * 1000);
    }
}

function cdp_clearPAIChat() {
    if (confirm('Clear chat history? This cannot be undone.')) {
        paiHistory = [];
        $('#pai-chat-messages').html(
            '<div class="text-center text-muted py-5">'
            + '<p style="font-size:14px; color:#6c757d;"><i class="ti-trash"></i> Chat cleared</p>'
            + '<p style="font-size:12px; color:#aaa;">Start a new conversation or use quick actions above</p>'
            + '</div>'
        );
    }
}

function cdp_quickAction(action) {
    var messages = {
        'briefing': 'Give me a full briefing of the current system status with stuck shipments, driver workload, overdue payments, revenue comparison vs last month, and recent activity.',
        'stuck': 'Show me all stuck shipments with tracking numbers, customer names, how long they\'ve been stuck, and suggest actions to resolve them.',
        'payments': 'Show me pending and overdue payments with customer names, amounts, and days overdue. Include wire transfer confirmations waiting for approval.',
        'drivers': 'Show me driver workload distribution. Who is overloaded? Who is available? Suggest optimal assignments for unassigned shipments.',
        'revenue': 'Analyze revenue trends. Compare this month vs last month, show top customers, identify growth patterns, and predict next month\'s revenue.'
    };
    
    if (messages[action]) {
        // Clear the "Chat cleared" message if present
        var $msgs = $('#pai-chat-messages');
        if ($msgs.find('.ti-trash').length > 0) {
            $msgs.html('');
        }
        cdp_sendPAIMessage(messages[action]);
    }
}

function cdp_playNotificationSound() {
    if (paiSettings.sound === 1) {
        try {
            $('#chatAudio')[0].play();
        } catch(e) {
            console.log('Sound notification failed:', e);
        }
    }
}

// Reset on modal close
$('#modal-pai').on('hidden.bs.modal', function() {
    paiHistory = [];
    paiFullscreen = false;
    $('#pai-settings-panel').hide();
    $('#pai-modal-dialog').css({ 'position':'', 'top':'', 'left':'', 'right':'', 'bottom':'', 'max-width':'', 'width':'', 'height':'', 'margin':'', 'padding':'', 'z-index':'' });
    $('.modal-content', '#pai-modal-dialog').css('height','');
    $('#pai-chat-messages').css('max-height', '420px').html(
        '<div class="text-center text-muted py-5">'
        + '<div class="mb-3"><i class="fa fa-spinner fa-spin fa-3x" style="color:#0d6efd; opacity:0.5;"></i></div>'
        + '<p class="mb-2" style="font-size:14px; font-weight:600; color:#495057;">Initializing Pryro AI...</p>'
        + '<p class="mb-0" style="font-size:12px; color:#6c757d;">Analyzing your logistics operations</p>'
        + '</div>'
    );
    $('#btn-pai-expand i').removeClass('ti-zoom-out').addClass('ti-fullscreen');
    $('#pai-char-count').text('0/500');
    
    // Clear auto-refresh
    if (paiAutoRefreshInterval) {
        clearInterval(paiAutoRefreshInterval);
        paiAutoRefreshInterval = null;
    }
});

// Focus input when modal is shown
$('#modal-pai').on('shown.bs.modal', function() {
    $('#pai-chat-input').focus();
});

// ============================================================================
// VISUAL COMPONENTS - Charts, Cards, Tables
// ============================================================================

// Generate Stat Cards
function cdp_createStatCards(stats) {
    var html = '<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:12px; margin:12px 0;">';
    
    stats.forEach(function(stat) {
        var color = stat.color || '#0d6efd';
        var bgColor = stat.bgColor || '#e7f3ff';
        
        html += '<div class="pai-stat-card" style="border-color:' + color + ';">';
        html += '<div class="pai-stat-card-icon">' + stat.icon + '</div>';
        html += '<div class="pai-stat-card-value" style="color:' + color + ';">' + stat.value + '</div>';
        html += '<div class="pai-stat-card-label">' + stat.label + '</div>';
        
        if (stat.action) {
            html += '<button class="pai-stat-card-btn" style="background:' + color + ';" onclick="' + stat.action + '">';
            html += stat.actionLabel || 'View';
            html += '</button>';
        }
        
        html += '</div>';
    });
    
    html += '</div>';
    return html;
}

// Generate Line Chart
function cdp_createLineChart(data, containerId) {
    var canvas = '<div class="pai-chart-container">';
    canvas += '<div class="pai-chart-title"><i class="ti-stats-up"></i> ' + data.title + '</div>';
    canvas += '<canvas id="' + containerId + '" style="max-height:250px;"></canvas>';
    canvas += '</div>';
    
    setTimeout(function() {
        var ctx = document.getElementById(containerId);
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: data.label,
                        data: data.values,
                        borderColor: data.color || '#0d6efd',
                        backgroundColor: data.bgColor || 'rgba(13, 110, 253, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: true }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    }, 100);
    
    return canvas;
}

// Generate Bar Chart
function cdp_createBarChart(data, containerId) {
    var canvas = '<div class="pai-chart-container">';
    canvas += '<div class="pai-chart-title"><i class="ti-bar-chart"></i> ' + data.title + '</div>';
    canvas += '<canvas id="' + containerId + '" style="max-height:250px;"></canvas>';
    canvas += '</div>';
    
    setTimeout(function() {
        var ctx = document.getElementById(containerId);
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: data.label,
                        data: data.values,
                        backgroundColor: data.colors || ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545'],
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    }, 100);
    
    return canvas;
}

// Generate Pie/Donut Chart
function cdp_createPieChart(data, containerId, donut) {
    var canvas = '<div class="pai-chart-container">';
    canvas += '<div class="pai-chart-title"><i class="ti-pie-chart"></i> ' + data.title + '</div>';
    canvas += '<canvas id="' + containerId + '" style="max-height:250px;"></canvas>';
    canvas += '</div>';
    
    setTimeout(function() {
        var ctx = document.getElementById(containerId);
        if (ctx) {
            new Chart(ctx, {
                type: donut ? 'doughnut' : 'pie',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: data.colors || ['#28a745', '#0d6efd', '#ffc107', '#dc3545', '#6c757d'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }, 100);
    
    return canvas;
}

// Generate Data Table
function cdp_createDataTable(data) {
    var html = '<div style="overflow-x:auto; margin:12px 0;">';
    html += '<table class="pai-data-table">';
    
    // Header
    html += '<thead><tr>';
    data.columns.forEach(function(col) {
        html += '<th>' + col + '</th>';
    });
    html += '</tr></thead>';
    
    // Rows
    html += '<tbody>';
    data.rows.forEach(function(row) {
        html += '<tr>';
        row.forEach(function(cell) {
            html += '<td>' + cell + '</td>';
        });
        html += '</tr>';
    });
    html += '</tbody>';
    
    html += '</table></div>';
    return html;
}

// Generate Progress Bar
function cdp_createProgressBar(label, percentage, color) {
    var html = '<div style="margin:8px 0;">';
    html += '<div style="font-size:11px; font-weight:600; color:#495057; margin-bottom:4px;">' + label + '</div>';
    html += '<div class="pai-progress-bar">';
    html += '<div class="pai-progress-fill" style="width:' + percentage + '%; background:' + (color || '#0d6efd') + ';">';
    html += percentage + '%';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    return html;
}

// Generate Alert Card
function cdp_createAlertCard(text, type, icon) {
    type = type || 'warning'; // warning, danger, success, info
    icon = icon || 'ti-alert';
    
    var html = '<div class="pai-alert-card ' + type + '">';
    html += '<div style="display:flex; align-items:start; gap:10px;">';
    html += '<i class="' + icon + '" style="font-size:20px; flex-shrink:0;"></i>';
    html += '<div style="flex:1; font-size:13px; line-height:1.6;">' + text + '</div>';
    html += '</div>';
    html += '</div>';
    return html;
}
</script>