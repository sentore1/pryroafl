<!-- ============================================================== -->
<!-- CBM Configuration Page -->
<!-- ============================================================== -->
<div class="right-part mail-list bg-white">

	<div class="bg-light">
		<div class="row justify-content-center">
			<div class="col-md-12">
				<div class="row">
					<div class="col-12">
						<div id="loader" style="display:none"></div>
						<div id="resultados_ajax"></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-12">
			<div class="card-body">

				<div class="d-md-flex align-items-center">
					<div>
						<h3 class="card-title"><i class="fas fa-cube"></i> <span>CBM Configuration</span></h3>
						<p class="text-muted">Configure Cubic Meter (CBM) calculation settings and pricing tiers</p>
					</div>
				</div>
				<div><hr><br></div>

				<form class="form-horizontal form-material" id="save_config_cbm" name="save_config_cbm" method="post">

					<!-- General Settings Section -->
					<section>
						<h4 class="card-title"><i class="ti-settings"></i> General Settings</h4>
						<hr>

						<div class="row">
							<!-- Enable CBM Calculations -->
							<div class="col-md-6">
								<div class="form-group">
									<label for="cbm_enabled">
										<i class="fas fa-toggle-on"></i> Enable CBM Calculations
									</label>
									<div class="custom-control custom-switch">
										<input type="checkbox" class="custom-control-input" id="cbm_enabled" name="cbm_enabled" value="1" <?php echo (isset($core->cbm_calculation_enabled) && $core->cbm_calculation_enabled == 1) ? 'checked' : ''; ?>>
										<label class="custom-control-label" for="cbm_enabled">
											<?php echo (isset($core->cbm_calculation_enabled) && $core->cbm_calculation_enabled == 1) ? 'Enabled' : 'Disabled'; ?>
										</label>
									</div>
									<small class="form-text text-muted">Enable or disable CBM calculations system-wide</small>
								</div>
							</div>

							<!-- Default CBM Rate -->
							<div class="col-md-6">
								<div class="form-group">
									<label for="cbm_rate">
										<i class="fas fa-dollar-sign"></i> Default CBM Rate (per m³)
									</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><?php echo $core->currency; ?></span>
										</div>
										<input type="number" step="0.01" min="0" class="form-control" id="cbm_rate" name="cbm_rate" value="<?php echo isset($core->cbm_rate_per_cubic_meter) ? $core->cbm_rate_per_cubic_meter : '0.00'; ?>" required>
									</div>
									<small class="form-text text-muted">Default rate charged per cubic meter</small>
								</div>
							</div>
						</div>

						<div class="row">
							<!-- Charge Priority -->
							<div class="col-md-4">
								<div class="form-group">
									<label for="cbm_priority">
										<i class="fas fa-balance-scale"></i> Charge Priority
									</label>
									<select class="form-control custom-select" id="cbm_priority" name="cbm_priority" required>
										<option value="weight" <?php echo (isset($core->cbm_vs_weight_priority) && $core->cbm_vs_weight_priority == 'weight') ? 'selected' : ''; ?>>Always use Weight</option>
										<option value="cbm" <?php echo (isset($core->cbm_vs_weight_priority) && $core->cbm_vs_weight_priority == 'cbm') ? 'selected' : ''; ?>>Always use CBM</option>
										<option value="higher" <?php echo (!isset($core->cbm_vs_weight_priority) || $core->cbm_vs_weight_priority == 'higher') ? 'selected' : ''; ?>>Use Higher Charge</option>
									</select>
									<small class="form-text text-muted">Determines whether to charge based on weight, CBM, or whichever is higher</small>
								</div>
							</div>

							<!-- Measurement Unit -->
							<div class="col-md-4">
								<div class="form-group">
									<label for="cbm_measurement_unit">
										<i class="fas fa-ruler"></i> Measurement Unit
									</label>
									<select class="form-control custom-select" id="cbm_measurement_unit" name="cbm_measurement_unit" required>
										<option value="cm" <?php echo (!isset($core->cbm_measurement_unit) || $core->cbm_measurement_unit == 'cm') ? 'selected' : ''; ?>>Centimeters (cm)</option>
										<option value="inch" <?php echo (isset($core->cbm_measurement_unit) && $core->cbm_measurement_unit == 'inch') ? 'selected' : ''; ?>>Inches (in)</option>
										<option value="m" <?php echo (isset($core->cbm_measurement_unit) && $core->cbm_measurement_unit == 'm') ? 'selected' : ''; ?>>Meters (m)</option>
									</select>
									<small class="form-text text-muted">Unit for length, width, and height measurements</small>
								</div>
							</div>

							<!-- CBM Formula Display -->
							<div class="col-md-4">
								<div class="form-group">
									<label><i class="fas fa-calculator"></i> CBM Formula</label>
									<div class="alert alert-info">
										<strong>CBM = (L × W × H) ÷ <span id="divisor">1,000,000</span></strong>
										<br><small>For <span id="unit-text">centimeters</span></small>
									</div>
								</div>
							</div>
						</div>

						<div class="row">
							<!-- Show Package Dimensions -->
							<div class="col-md-4">
								<div class="form-group">
									<label for="show_package_dimensions">
										<i class="fas fa-ruler-combined"></i> Package Dimensions Input
									</label>
									<div class="custom-control custom-switch">
										<input type="checkbox" class="custom-control-input" id="show_package_dimensions" name="show_package_dimensions" value="1" <?php echo (!isset($core->show_package_dimensions) || $core->show_package_dimensions == 1) ? 'checked' : ''; ?>>
										<label class="custom-control-label" for="show_package_dimensions">
											Show Length, Width, Height Fields
										</label>
									</div>
									<small class="form-text text-muted">Enable to show L × W × H input fields in shipment forms</small>
								</div>
							</div>

							<!-- Show CBM Input Field -->
							<div class="col-md-4">
								<div class="form-group">
									<label for="show_cbm_input_field">
										<i class="fas fa-cube"></i> CBM Direct Input
									</label>
									<div class="custom-control custom-switch">
										<input type="checkbox" class="custom-control-input" id="show_cbm_input_field" name="show_cbm_input_field" value="1" <?php echo (isset($core->show_cbm_input_field) && $core->show_cbm_input_field == 1) ? 'checked' : ''; ?>>
										<label class="custom-control-label" for="show_cbm_input_field">
											Show CBM Input Field
										</label>
									</div>
									<small class="form-text text-muted">Enable to allow direct CBM entry (e.g., 1 m³, 0.4 m³)</small>
								</div>
							</div>

							<!-- Show CBM in Forms -->
							<div class="col-md-4">
								<div class="form-group">
									<label for="show_cbm_in_forms">
										<i class="fas fa-calculator"></i> CBM Calculation Display
									</label>
									<div class="custom-control custom-switch">
										<input type="checkbox" class="custom-control-input" id="show_cbm_in_forms" name="show_cbm_in_forms" value="1" <?php echo (!isset($core->show_cbm_in_forms) || $core->show_cbm_in_forms == 1) ? 'checked' : ''; ?>>
										<label class="custom-control-label" for="show_cbm_in_forms">
											Show Total CBM Calculation
										</label>
									</div>
									<small class="form-text text-muted">Enable to show real-time total CBM in shipment forms</small>
								</div>
							</div>
						</div>

						<div class="alert alert-info">
							<i class="fas fa-info-circle"></i> <strong>Input Options:</strong>
							<ul class="mb-0 mt-2">
								<li><strong>Dimensions Only:</strong> Users enter L × W × H, system calculates CBM automatically</li>
								<li><strong>CBM Only:</strong> Users enter CBM directly (e.g., 1 m³ for standard pallet)</li>
								<li><strong>Both Enabled:</strong> Users can choose which method to use</li>
								<li><strong>Note:</strong> At least one input method must be enabled</li>
							</ul>
						</div>

					</section>

					<!-- Container Capacities Section -->
					<section class="mt-4">
						<h4 class="card-title"><i class="fas fa-shipping-fast"></i> Standard Container Capacities</h4>
						<hr>

						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<label>20ft Container</label>
									<div class="input-group">
										<input type="text" class="form-control" value="33.00" readonly>
										<div class="input-group-append">
											<span class="input-group-text">m³</span>
										</div>
									</div>
									<small class="text-muted">Standard capacity</small>
								</div>
							</div>

							<div class="col-md-3">
								<div class="form-group">
									<label>40ft Container</label>
									<div class="input-group">
										<input type="text" class="form-control" value="67.00" readonly>
										<div class="input-group-append">
											<span class="input-group-text">m³</span>
										</div>
									</div>
									<small class="text-muted">Standard capacity</small>
								</div>
							</div>

							<div class="col-md-3">
								<div class="form-group">
									<label>40ft HC</label>
									<div class="input-group">
										<input type="text" class="form-control" value="76.00" readonly>
										<div class="input-group-append">
											<span class="input-group-text">m³</span>
										</div>
									</div>
									<small class="text-muted">High Cube</small>
								</div>
							</div>

							<div class="col-md-3">
								<div class="form-group">
									<label>45ft HC</label>
									<div class="input-group">
										<input type="text" class="form-control" value="86.00" readonly>
										<div class="input-group-append">
											<span class="input-group-text">m³</span>
										</div>
									</div>
									<small class="text-muted">High Cube</small>
								</div>
							</div>
						</div>

						<div class="alert alert-warning">
							<i class="fas fa-info-circle"></i> <strong>Note:</strong> These are standard container capacities for reference. Actual usable capacity may vary based on cargo type and packing efficiency.
						</div>

					</section>

					<!-- CBM Pricing Tiers Section -->
					<section class="mt-4">
						<h4 class="card-title"><i class="fas fa-layer-group"></i> CBM Pricing Tiers</h4>
						<hr>

						<div class="row mb-3">
							<div class="col-md-12">
								<button type="button" class="btn btn-primary" onclick="openAddTierModal()">
									<i class="ti-plus"></i> Add New Pricing Tier
								</button>
							</div>
						</div>

						<div class="table-responsive">
							<table class="table table-striped table-hover">
								<thead>
									<tr>
										<th>Tier Name</th>
										<th>Min CBM</th>
										<th>Max CBM</th>
										<th>Rate per m³</th>
										<th>Fixed Charge</th>
										<th>Status</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody id="pricing_tiers_list">
									<?php
									$db->cdp_query("SELECT * FROM cdb_cbm_pricing_tiers ORDER BY min_cbm ASC");
									$pricing_tiers = $db->cdp_registros();
									
									if ($pricing_tiers) {
										foreach ($pricing_tiers as $tier) {
									?>
									<tr>
										<td><strong><?php echo $tier->tier_name; ?></strong></td>
										<td><?php echo number_format($tier->min_cbm, 4); ?> m³</td>
										<td><?php echo ($tier->max_cbm > 0) ? number_format($tier->max_cbm, 4) . ' m³' : '<span class="badge badge-info">Unlimited</span>'; ?></td>
										<td><?php echo $core->currency; ?> <?php echo number_format($tier->rate_per_cbm, 2); ?></td>
										<td><?php echo $core->currency; ?> <?php echo number_format($tier->fixed_charge, 2); ?></td>
										<td>
											<?php if ($tier->active == 1) { ?>
												<span class="badge badge-success">Active</span>
											<?php } else { ?>
												<span class="badge badge-danger">Inactive</span>
											<?php } ?>
										</td>
										<td>
											<div class="btn-group">
												<button type="button" class="btn btn-sm btn-info" onclick="editTier(<?php echo $tier->id; ?>)" title="Edit">
													<i class="ti-pencil"></i>
												</button>
												<button type="button" class="btn btn-sm btn-<?php echo ($tier->active == 1) ? 'warning' : 'success'; ?>" onclick="toggleTierStatus(<?php echo $tier->id; ?>, <?php echo $tier->active; ?>)" title="<?php echo ($tier->active == 1) ? 'Deactivate' : 'Activate'; ?>">
													<i class="ti-<?php echo ($tier->active == 1) ? 'close' : 'check'; ?>"></i>
												</button>
												<button type="button" class="btn btn-sm btn-danger" onclick="deleteTier(<?php echo $tier->id; ?>)" title="Delete">
													<i class="ti-trash"></i>
												</button>
											</div>
										</td>
									</tr>
									<?php 
										}
									} else {
									?>
									<tr>
										<td colspan="7" class="text-center">
											<i class="fas fa-info-circle"></i> No pricing tiers configured yet. Click "Add New Pricing Tier" to create one.
										</td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>

						<div class="alert alert-info mt-3">
							<i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Pricing tiers allow you to set different rates based on shipment volume. For example, charge more per m³ for small shipments and less for large shipments.
						</div>

					</section>

					<!-- Save Button -->
					<div class="form-group mt-4">
						<div class="col-sm-12">
							<button type="submit" class="btn btn-success">
								<i class="fas fa-save"></i> Save Settings
							</button>
							<button type="button" class="btn btn-secondary" onclick="location.reload()">
								<i class="fas fa-undo"></i> Reset
							</button>
						</div>
					</div>

				</form>

			</div>
		</div>
	</div>

</div>

<!-- Modal for Add/Edit Pricing Tier -->
<div class="modal fade" id="tierModal" tabindex="-1" role="dialog" aria-labelledby="tierModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="tierModalLabel">
					<i class="fas fa-layer-group"></i> <span id="tierModalTitle">Add Pricing Tier</span>
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form id="tierForm">
				<div class="modal-body">
					<input type="hidden" id="tier_id" name="tier_id" value="">
					
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="tier_name">Tier Name <span class="text-danger">*</span></label>
								<input type="text" class="form-control" id="tier_name" name="tier_name" placeholder="e.g., Small Shipment" required>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="tier_active">Status</label>
								<select class="form-control" id="tier_active" name="tier_active">
									<option value="1">Active</option>
									<option value="0">Inactive</option>
								</select>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="tier_min_cbm">Minimum CBM <span class="text-danger">*</span></label>
								<div class="input-group">
									<input type="number" step="0.0001" min="0" class="form-control" id="tier_min_cbm" name="tier_min_cbm" placeholder="0.0000" required>
									<div class="input-group-append">
										<span class="input-group-text">m³</span>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="tier_max_cbm">Maximum CBM</label>
								<div class="input-group">
									<input type="number" step="0.0001" min="0" class="form-control" id="tier_max_cbm" name="tier_max_cbm" placeholder="0.0000 (0 = Unlimited)">
									<div class="input-group-append">
										<span class="input-group-text">m³</span>
									</div>
								</div>
								<small class="text-muted">Leave as 0 for unlimited</small>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="tier_rate">Rate per m³ <span class="text-danger">*</span></label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text"><?php echo $core->currency; ?></span>
									</div>
									<input type="number" step="0.01" min="0" class="form-control" id="tier_rate" name="tier_rate" placeholder="0.00" required>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="tier_fixed">Fixed Charge</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text"><?php echo $core->currency; ?></span>
									</div>
									<input type="number" step="0.01" min="0" class="form-control" id="tier_fixed" name="tier_fixed" placeholder="0.00" value="0">
								</div>
								<small class="text-muted">Optional fixed charge added to CBM charge</small>
							</div>
						</div>
					</div>

					<div class="alert alert-info">
						<strong>Example:</strong> For shipments between 0.5 m³ and 2.0 m³, charge $50 per m³ plus a $10 fixed fee.
					</div>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">
						<i class="fas fa-times"></i> Cancel
					</button>
					<button type="submit" class="btn btn-primary">
						<i class="fas fa-save"></i> Save Tier
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

