<div class="right-part mail-list bg-white">
    <div class="p-15 b-b">
        <div class="d-flex align-items-center">
            <span style="background:#7460ee; color:#fff; font-size:10px; font-weight:700; padding:2px 7px; border-radius:3px; margin-right:8px; letter-spacing:0.5px;">P-AI</span>
            <span>AI Assistant Configuration</span>
        </div>
    </div>

<?php
// Load current AI config from database
$ai_db = new Conexion;
$ai_db->cdp_query("SELECT * FROM cdb_settings LIMIT 1");
$ai_db->cdp_execute();
$ai_row = $ai_db->cdp_registro();

$current_groq     = ($ai_row && !empty($ai_row->groq_api_key))   ? $ai_row->groq_api_key   : '';
$current_openai   = ($ai_row && !empty($ai_row->openai_api_key)) ? $ai_row->openai_api_key : '';
$current_provider = ($ai_row && !empty($ai_row->ai_provider))    ? $ai_row->ai_provider    : 'groq';
$is_active        = !empty($current_groq) || !empty($current_openai);

// Helper function to get permission value
function ai_perm($row, $field, $default = 0) {
    return (isset($row->$field) && $row->$field !== null) ? (int)$row->$field : $default;
}
?>

    <div class="bg-light p-15">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div id="resultados_ajax"></div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card-body">
                <form class="form-horizontal form-material" id="save_ai_config" name="save_ai_config" method="post">

                    <h4 class="card-title"><b>AI Provider Settings</b></h4>
                    <p class="text-muted" style="font-size:13px;">Connect an AI model to power the P-AI Daily Briefing on your dashboard. Get a free Groq key at <a href="https://console.groq.com" target="_blank">console.groq.com</a> or an OpenAI key at <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com</a>.</p>
                    <hr />

                    <section>
                        <!-- Provider selection -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="mdi mdi-robot"></i> AI Provider</label>
                                    <select class="form-control" name="ai_provider" id="ai_provider">
                                        <option value="groq"  <?php echo ($current_provider == 'groq')  ? 'selected' : ''; ?>>Groq (Free &amp; Fast — Recommended)</option>
                                        <option value="openai" <?php echo ($current_provider == 'openai') ? 'selected' : ''; ?>>OpenAI (GPT-4o)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Groq -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label><i class="mdi mdi-key"></i> Groq API Key</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="groq_api_key" id="groq_api_key"
                                            placeholder="gsk_xxxxxxxxxxxxxxxxxxxxxxxx"
                                            value="<?php echo htmlspecialchars($current_groq); ?>">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary toggle-eye" data-target="groq_api_key">
                                                <i class="mdi mdi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted">Get your free key at <a href="https://console.groq.com" target="_blank">console.groq.com</a></small>
                                </div>
                            </div>
                        </div>

                        <!-- OpenAI -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label><i class="mdi mdi-key-variant"></i> OpenAI API Key <small class="text-muted">(optional)</small></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="openai_api_key" id="openai_api_key"
                                            placeholder="sk-xxxxxxxxxxxxxxxxxxxxxxxx"
                                            value="<?php echo htmlspecialchars($current_openai); ?>">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary toggle-eye" data-target="openai_api_key">
                                                <i class="mdi mdi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted">Get your key at <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com</a></small>
                                </div>
                            </div>
                        </div>

                        <hr />

                        <!-- Status indicator -->
                        <div class="row">
                            <div class="col-md-12">
                                <?php if ($is_active): ?>
                                    <div class="alert alert-success" style="font-size:13px;">
                                        <i class="mdi mdi-check-circle"></i> P-AI is <strong>active</strong>. The Daily Briefing is running on your dashboard.
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning" style="font-size:13px;">
                                        <i class="mdi mdi-alert"></i> No API key configured. Add a Groq or OpenAI key above to activate P-AI.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>

                    <!-- AUTOPILOT MODE SECTION -->
                    <section class="mt-4">
                        <h4 class="card-title"><b><i class="mdi mdi-airplane"></i> Autopilot Mode</b></h4>
                        <p class="text-muted" style="font-size:13px;">When enabled, P-AI can automatically take actions without asking for confirmation when it detects issues that meet the threshold criteria.</p>
                        <hr />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="ai_autopilot_enabled" name="ai_autopilot_enabled" value="1" <?php echo ai_perm($ai_row, 'ai_autopilot_enabled') ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="ai_autopilot_enabled">
                                            <strong>Enable Autopilot Mode</strong>
                                            <span class="badge badge-warning ml-2">EXPERIMENTAL</span>
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-2">AI will automatically assign drivers, confirm obvious payments, and update stuck shipments</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Autopilot Threshold (minimum items before auto-action)</label>
                                    <select class="form-control" name="ai_autopilot_threshold" id="ai_autopilot_threshold">
                                        <?php 
                                        $threshold = ai_perm($ai_row, 'ai_autopilot_threshold', 5);
                                        for ($i = 1; $i <= 20; $i++) {
                                            $selected = ($threshold == $i) ? 'selected' : '';
                                            echo "<option value='$i' $selected>$i items</option>";
                                        }
                                        ?>
                                    </select>
                                    <small class="text-muted">AI will only auto-act if there are at least this many items</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info" style="font-size:12px;">
                            <i class="mdi mdi-information"></i> <strong>Autopilot Safety:</strong> AI will only auto-act on low-risk operations (assigning drivers to unassigned shipments, marking stuck shipments as "in transit"). High-risk actions (cancellations, refunds) always require manual confirmation.
                        </div>
                    </section>

                    <!-- READ PERMISSIONS SECTION -->
                    <section class="mt-4">
                        <h4 class="card-title"><b><i class="mdi mdi-book-open-variant"></i> Read Permissions</b></h4>
                        <p class="text-muted" style="font-size:13px;">Control what data P-AI can see and analyze.</p>
                        <hr />

                        <div class="row">
                            <div class="col-md-4">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_read_customers" name="ai_can_read_customers" value="1" <?php echo ai_perm($ai_row, 'ai_can_read_customers', 1) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_read_customers">Customer Data</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_read_packages" name="ai_can_read_packages" value="1" <?php echo ai_perm($ai_row, 'ai_can_read_packages', 1) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_read_packages">Package Details</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_read_financials" name="ai_can_read_financials" value="1" <?php echo ai_perm($ai_row, 'ai_can_read_financials', 1) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_read_financials">Financial Data</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_read_drivers" name="ai_can_read_drivers" value="1" <?php echo ai_perm($ai_row, 'ai_can_read_drivers', 1) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_read_drivers">Driver Information</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_read_inventory" name="ai_can_read_inventory" value="1" <?php echo ai_perm($ai_row, 'ai_can_read_inventory', 0) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_read_inventory">Inventory Data</label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- ACTION PERMISSIONS SECTION -->
                    <section class="mt-4">
                        <h4 class="card-title"><b><i class="mdi mdi-lightning-bolt"></i> Action Permissions</b></h4>
                        <p class="text-muted" style="font-size:13px;">Control what actions P-AI can perform on your data.</p>
                        <hr />

                        <div class="row">
                            <div class="col-md-4">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_assign_drivers" name="ai_can_assign_drivers" value="1" <?php echo ai_perm($ai_row, 'ai_can_assign_drivers', 1) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_assign_drivers">Assign Drivers</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_confirm_payments" name="ai_can_confirm_payments" value="1" <?php echo ai_perm($ai_row, 'ai_can_confirm_payments', 1) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_confirm_payments">Confirm Payments</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_update_status" name="ai_can_update_status" value="1" <?php echo ai_perm($ai_row, 'ai_can_update_status', 1) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_update_status">Update Status</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_create_shipments" name="ai_can_create_shipments" value="1" <?php echo ai_perm($ai_row, 'ai_can_create_shipments', 0) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_create_shipments">Create Shipments <span class="badge badge-primary badge-sm">NEW</span></label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_edit_shipments" name="ai_can_edit_shipments" value="1" <?php echo ai_perm($ai_row, 'ai_can_edit_shipments', 0) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_edit_shipments">Edit Shipments <span class="badge badge-primary badge-sm">NEW</span></label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_cancel_shipments" name="ai_can_cancel_shipments" value="1" <?php echo ai_perm($ai_row, 'ai_can_cancel_shipments', 0) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_cancel_shipments">Cancel Shipments</label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- COMMUNICATION PERMISSIONS SECTION -->
                    <section class="mt-4">
                        <h4 class="card-title"><b><i class="mdi mdi-message-text"></i> Communication Permissions</b></h4>
                        <p class="text-muted" style="font-size:13px;">Allow P-AI to send notifications to customers and staff.</p>
                        <hr />

                        <div class="row">
                            <div class="col-md-4">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_send_sms" name="ai_can_send_sms" value="1" <?php echo ai_perm($ai_row, 'ai_can_send_sms', 0) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_send_sms">Send SMS <span class="badge badge-primary badge-sm">NEW</span></label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_send_email" name="ai_can_send_email" value="1" <?php echo ai_perm($ai_row, 'ai_can_send_email', 0) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_send_email">Send Email <span class="badge badge-primary badge-sm">NEW</span></label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_send_whatsapp" name="ai_can_send_whatsapp" value="1" <?php echo ai_perm($ai_row, 'ai_can_send_whatsapp', 0) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_send_whatsapp">Send WhatsApp <span class="badge badge-primary badge-sm">NEW</span></label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- REPORTING PERMISSIONS SECTION -->
                    <section class="mt-4">
                        <h4 class="card-title"><b><i class="mdi mdi-file-chart"></i> Reporting & Export Permissions</b></h4>
                        <p class="text-muted" style="font-size:13px;">Allow P-AI to generate and export reports.</p>
                        <hr />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_generate_reports" name="ai_can_generate_reports" value="1" <?php echo ai_perm($ai_row, 'ai_can_generate_reports', 1) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_generate_reports">Generate Reports <span class="badge badge-primary badge-sm">NEW</span></label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_export_data" name="ai_can_export_data" value="1" <?php echo ai_perm($ai_row, 'ai_can_export_data', 1) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_export_data">Export Data (CSV/Excel) <span class="badge badge-primary badge-sm">NEW</span></label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- CUSTOMER MANAGEMENT SECTION -->
                    <section class="mt-4">
                        <h4 class="card-title"><b><i class="mdi mdi-account-multiple"></i> Customer Management</b></h4>
                        <p class="text-muted" style="font-size:13px;">Allow P-AI to manage customer records.</p>
                        <hr />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_create_customers" name="ai_can_create_customers" value="1" <?php echo ai_perm($ai_row, 'ai_can_create_customers', 0) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_create_customers">Create Customers <span class="badge badge-primary badge-sm">NEW</span></label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_edit_customers" name="ai_can_edit_customers" value="1" <?php echo ai_perm($ai_row, 'ai_can_edit_customers', 0) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_edit_customers">Edit Customers <span class="badge badge-primary badge-sm">NEW</span></label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- FINANCIAL OPERATIONS SECTION -->
                    <section class="mt-4">
                        <h4 class="card-title"><b><i class="mdi mdi-cash-multiple"></i> Financial Operations</b></h4>
                        <p class="text-muted" style="font-size:13px;">High-risk financial actions (use with caution).</p>
                        <hr />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_process_refunds" name="ai_can_process_refunds" value="1" <?php echo ai_perm($ai_row, 'ai_can_process_refunds', 0) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_process_refunds">Process Refunds <span class="badge badge-danger badge-sm">HIGH RISK</span></label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_apply_discounts" name="ai_can_apply_discounts" value="1" <?php echo ai_perm($ai_row, 'ai_can_apply_discounts', 0) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_apply_discounts">Apply Discounts <span class="badge badge-warning badge-sm">MEDIUM RISK</span></label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- ADVANCED FEATURES SECTION -->
                    <section class="mt-4">
                        <h4 class="card-title"><b><i class="mdi mdi-brain"></i> Advanced Intelligence Features</b></h4>
                        <p class="text-muted" style="font-size:13px;">Enable advanced AI capabilities.</p>
                        <hr />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_predict_analytics" name="ai_can_predict_analytics" value="1" <?php echo ai_perm($ai_row, 'ai_can_predict_analytics', 1) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_predict_analytics">Predictive Analytics <span class="badge badge-primary badge-sm">NEW</span></label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="ai_can_optimize_routes" name="ai_can_optimize_routes" value="1" <?php echo ai_perm($ai_row, 'ai_can_optimize_routes', 0) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="ai_can_optimize_routes">Route Optimization <span class="badge badge-primary badge-sm">NEW</span></label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="form-group mt-4">
                        <div class="col-sm-12">
                            <button type="button" class="btn btn-primary btn-lg" id="btn_save_ai">
                                <i class="mdi mdi-content-save"></i> Save All AI Settings
                            </button>
                            <button type="button" class="btn btn-secondary ml-2" onclick="location.reload();">
                                <i class="mdi mdi-refresh"></i> Reset
                            </button>
                            <button type="button" class="btn btn-info ml-2" id="btn_test_connection">
                                <i class="mdi mdi-stethoscope"></i> Test Connection
                            </button>
                        </div>
                    </div>
                    
                    <!-- Test Results Panel -->
                    <div id="test_results" class="mt-3" style="display:none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Test Connection Function
document.getElementById('btn_test_connection').addEventListener('click', function() {
    var btn = this;
    var resultsDiv = document.getElementById('test_results');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Testing...';
    resultsDiv.style.display = 'block';
    resultsDiv.innerHTML = '<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Running connection tests...</div>';
    
    fetch('ajax/ai/test_connection.php')
        .then(response => response.json())
        .then(data => {
            var html = '<div class="card">';
            html += '<div class="card-header bg-primary text-white"><strong>Connection Test Results</strong> - ' + data.timestamp + '</div>';
            html += '<div class="card-body">';
            
            // Overall Status
            if (data.overall_status === 'READY') {
                html += '<div class="alert alert-success"><i class="mdi mdi-check-circle"></i> <strong>All Systems Ready!</strong> P-AI is properly configured.</div>';
            } else {
                html += '<div class="alert alert-danger"><i class="mdi mdi-alert"></i> <strong>Configuration Error!</strong> See details below.</div>';
            }
            
            // Individual Tests
            html += '<table class="table table-bordered table-sm">';
            html += '<thead><tr><th>Test</th><th>Status</th><th>Details</th></tr></thead><tbody>';
            
            html += '<tr><td>Loader File</td><td>' + (data.loader ? '<span class="badge badge-success">✓ OK</span>' : '<span class="badge badge-danger">✗ FAIL</span>') + '</td><td>loader.php</td></tr>';
            
            html += '<tr><td>Database</td><td>' + (data.database ? '<span class="badge badge-success">✓ OK</span>' : '<span class="badge badge-danger">✗ FAIL</span>') + '</td><td>MySQL connection</td></tr>';
            
            html += '<tr><td>User Class</td><td>' + (data.user_class ? '<span class="badge badge-success">✓ OK</span>' : '<span class="badge badge-danger">✗ FAIL</span>') + '</td><td>' + (data.user_id ? 'User ID: ' + data.user_id + ' (Level: ' + data.user_level + ')' : 'Not logged in') + '</td></tr>';
            
            html += '<tr><td>Permissions Class</td><td>' + (data.permissions_class ? '<span class="badge badge-success">✓ OK</span>' : '<span class="badge badge-danger">✗ FAIL</span>') + '</td><td>' + (data.autopilot_enabled ? 'Autopilot: ON' : 'Autopilot: OFF') + '</td></tr>';
            
            html += '<tr><td>API Key (Groq)</td><td>' + (data.groq_key_length > 0 ? '<span class="badge badge-success">✓ Configured</span>' : '<span class="badge badge-warning">○ Not Set</span>') + '</td><td>' + (data.groq_key_preview || 'No key') + ' (' + (data.groq_key_length || 0) + ' chars)</td></tr>';
            
            html += '<tr><td>API Key (OpenAI)</td><td>' + (data.openai_key_length > 0 ? '<span class="badge badge-success">✓ Configured</span>' : '<span class="badge badge-warning">○ Not Set</span>') + '</td><td>' + (data.openai_key_preview || 'No key') + ' (' + (data.openai_key_length || 0) + ' chars)</td></tr>';
            
            html += '<tr><td>cURL Support</td><td>' + (data.curl_enabled ? '<span class="badge badge-success">✓ Enabled</span>' : '<span class="badge badge-danger">✗ Missing</span>') + '</td><td>Required for API calls</td></tr>';
            
            html += '<tr><td>PHP Version</td><td><span class="badge badge-info">' + data.php_version + '</span></td><td>Current version</td></tr>';
            
            html += '</tbody></table>';
            
            // Errors
            if (data.errors && data.errors.length > 0) {
                html += '<div class="alert alert-danger mt-3"><strong>Errors:</strong><ul class="mb-0">';
                data.errors.forEach(function(err) {
                    html += '<li>' + err + '</li>';
                });
                html += '</ul></div>';
            }
            
            // Recommendations
            html += '<div class="alert alert-info mt-3"><strong>Next Steps:</strong><ul class="mb-0">';
            if (!data.curl_enabled) {
                html += '<li>Enable cURL extension in PHP</li>';
            }
            if (data.groq_key_length === 0 && data.openai_key_length === 0) {
                html += '<li>Add at least one API key (Groq or OpenAI)</li>';
            }
            if (!data.is_admin) {
                html += '<li>Log in with admin account to access P-AI</li>';
            }
            if (data.overall_status === 'READY') {
                html += '<li style="color:green;">✓ Everything looks good! Try the AI panel now.</li>';
            }
            html += '</ul></div>';
            
            html += '</div></div>';
            resultsDiv.innerHTML = html;
        })
        .catch(error => {
            resultsDiv.innerHTML = '<div class="alert alert-danger"><strong>Test Failed:</strong> ' + error.message + '<br><small>Make sure ajax/ai/test_connection.php exists and is accessible.</small></div>';
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="mdi mdi-stethoscope"></i> Test Connection';
        });
});

// Eye toggle for API key fields
document.querySelectorAll('.toggle-eye').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var input = document.getElementById(this.getAttribute('data-target'));
        var icon  = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('mdi-eye', 'mdi-eye-off');
        } else {
            input.type = 'password';
            icon.classList.replace('mdi-eye-off', 'mdi-eye');
        }
    });
});

document.getElementById('btn_save_ai').addEventListener('click', function () {
    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

    var formData = new FormData();
    
    // API Keys & Provider
    formData.append('groq_api_key',   document.getElementById('groq_api_key').value);
    formData.append('openai_api_key', document.getElementById('openai_api_key').value);
    formData.append('ai_provider',    document.getElementById('ai_provider').value);
    
    // Autopilot Settings
    formData.append('ai_autopilot_enabled', document.getElementById('ai_autopilot_enabled').checked ? '1' : '0');
    formData.append('ai_autopilot_threshold', document.getElementById('ai_autopilot_threshold').value);
    
    // Read Permissions
    formData.append('ai_can_read_customers', document.getElementById('ai_can_read_customers').checked ? '1' : '0');
    formData.append('ai_can_read_packages', document.getElementById('ai_can_read_packages').checked ? '1' : '0');
    formData.append('ai_can_read_financials', document.getElementById('ai_can_read_financials').checked ? '1' : '0');
    formData.append('ai_can_read_drivers', document.getElementById('ai_can_read_drivers').checked ? '1' : '0');
    formData.append('ai_can_read_inventory', document.getElementById('ai_can_read_inventory').checked ? '1' : '0');
    
    // Action Permissions
    formData.append('ai_can_assign_drivers', document.getElementById('ai_can_assign_drivers').checked ? '1' : '0');
    formData.append('ai_can_confirm_payments', document.getElementById('ai_can_confirm_payments').checked ? '1' : '0');
    formData.append('ai_can_update_status', document.getElementById('ai_can_update_status').checked ? '1' : '0');
    formData.append('ai_can_create_shipments', document.getElementById('ai_can_create_shipments').checked ? '1' : '0');
    formData.append('ai_can_edit_shipments', document.getElementById('ai_can_edit_shipments').checked ? '1' : '0');
    formData.append('ai_can_cancel_shipments', document.getElementById('ai_can_cancel_shipments').checked ? '1' : '0');
    
    // Communication Permissions
    formData.append('ai_can_send_sms', document.getElementById('ai_can_send_sms').checked ? '1' : '0');
    formData.append('ai_can_send_email', document.getElementById('ai_can_send_email').checked ? '1' : '0');
    formData.append('ai_can_send_whatsapp', document.getElementById('ai_can_send_whatsapp').checked ? '1' : '0');
    
    // Reporting Permissions
    formData.append('ai_can_generate_reports', document.getElementById('ai_can_generate_reports').checked ? '1' : '0');
    formData.append('ai_can_export_data', document.getElementById('ai_can_export_data').checked ? '1' : '0');
    
    // Customer Management
    formData.append('ai_can_create_customers', document.getElementById('ai_can_create_customers').checked ? '1' : '0');
    formData.append('ai_can_edit_customers', document.getElementById('ai_can_edit_customers').checked ? '1' : '0');
    
    // Financial Operations
    formData.append('ai_can_process_refunds', document.getElementById('ai_can_process_refunds').checked ? '1' : '0');
    formData.append('ai_can_apply_discounts', document.getElementById('ai_can_apply_discounts').checked ? '1' : '0');
    
    // Advanced Features
    formData.append('ai_can_predict_analytics', document.getElementById('ai_can_predict_analytics').checked ? '1' : '0');
    formData.append('ai_can_optimize_routes', document.getElementById('ai_can_optimize_routes').checked ? '1' : '0');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'ajax/ai/save_ai_config_ajax.php', true);
    xhr.onload = function () {
        var box = document.getElementById('resultados_ajax');
        try {
            var res = JSON.parse(xhr.responseText);
            if (res.success) {
                box.innerHTML = '<div class="alert alert-success"><i class="mdi mdi-check-circle"></i> ' + res.message + '</div>';
            } else {
                box.innerHTML = '<div class="alert alert-danger"><i class="mdi mdi-alert"></i> ' + res.message + '</div>';
            }
        } catch (e) {
            box.innerHTML = '<div class="alert alert-danger">Unexpected response: ' + xhr.responseText + '</div>';
        }
        box.scrollIntoView({ behavior: 'smooth' });
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-content-save"></i> Save All AI Settings';
    };
    xhr.onerror = function () {
        document.getElementById('resultados_ajax').innerHTML = '<div class="alert alert-danger">Request failed. Check your server.</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="mdi mdi-content-save"></i> Save All AI Settings';
    };
    xhr.send(formData);
});
</script>
