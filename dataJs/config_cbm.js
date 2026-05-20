// *************************************************************************
// *                                                                       *
// * CBM Configuration JavaScript                                         *
// *                                                                       *
// *************************************************************************

$(document).ready(function() {
	
	// Save CBM Configuration
	$("#save_config_cbm").on('submit', function(event) {
		event.preventDefault();
		
		$('#loader').show();
		
		var data = $(this).serialize();
		
		$.ajax({
			type: "POST",
			url: "ajax/tools/config_cbm_ajax.php",
			data: data,
			dataType: 'json',
			success: function(response) {
				$('#loader').hide();
				
				if (response.success) {
					$('#resultados_ajax').html(
						'<div class="alert alert-success alert-dismissible fade show" role="alert">' +
						'<i class="fas fa-check-circle"></i> ' + response.message +
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
						'<span aria-hidden="true">&times;</span></button></div>'
					);
					
					setTimeout(function() {
						location.reload();
					}, 1500);
				} else {
					$('#resultados_ajax').html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
						'<i class="fas fa-exclamation-circle"></i> ' + response.message +
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
						'<span aria-hidden="true">&times;</span></button></div>'
					);
				}
			},
			error: function(xhr, status, error) {
				$('#loader').hide();
				console.error('AJAX Error:', xhr.responseText);
				$('#resultados_ajax').html(
					'<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
					'<i class="fas fa-exclamation-circle"></i> Error saving settings. Please try again.<br>' +
					'<small>Check browser console for details.</small>' +
					'<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
					'<span aria-hidden="true">&times;</span></button></div>'
				);
			}
		});
	});

	// Open Add Tier Modal
	window.openAddTierModal = function() {
		$('#tierModalTitle').text('Add Pricing Tier');
		$('#tierForm')[0].reset();
		$('#tier_id').val('');
		$('#tierModal').modal('show');
	};

	// Edit Tier
	window.editTier = function(id) {
		$.ajax({
			type: "POST",
			url: "ajax/tools/config_cbm_tier_ajax.php",
			data: { action: 'get', id: id },
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					$('#tierModalTitle').text('Edit Pricing Tier');
					$('#tier_id').val(response.data.id);
					$('#tier_name').val(response.data.tier_name);
					$('#tier_min_cbm').val(response.data.min_cbm);
					$('#tier_max_cbm').val(response.data.max_cbm);
					$('#tier_rate').val(response.data.rate_per_cbm);
					$('#tier_fixed').val(response.data.fixed_charge);
					$('#tier_active').val(response.data.active);
					$('#tierModal').modal('show');
				}
			}
		});
	};

	// Save Tier (Add/Edit)
	$("#tierForm").on('submit', function(event) {
		event.preventDefault();
		
		var data = $(this).serialize();
		var action = $('#tier_id').val() ? 'update' : 'add';
		data += '&action=' + action;
		
		$.ajax({
			type: "POST",
			url: "ajax/tools/config_cbm_tier_ajax.php",
			data: data,
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					$('#tierModal').modal('hide');
					$('#resultados_ajax').html(
						'<div class="alert alert-success alert-dismissible fade show" role="alert">' +
						'<i class="fas fa-check-circle"></i> ' + response.message +
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
						'<span aria-hidden="true">&times;</span></button></div>'
					);
					setTimeout(function() {
						location.reload();
					}, 1000);
				} else {
					alert(response.message);
				}
			}
		});
	});

	// Toggle Tier Status
	window.toggleTierStatus = function(id, currentStatus) {
		var newStatus = currentStatus == 1 ? 0 : 1;
		var statusText = newStatus == 1 ? 'activate' : 'deactivate';
		
		if (confirm('Are you sure you want to ' + statusText + ' this pricing tier?')) {
			$.ajax({
				type: "POST",
				url: "ajax/tools/config_cbm_tier_ajax.php",
				data: { action: 'toggle', id: id, status: newStatus },
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						location.reload();
					} else {
						alert(response.message);
					}
				}
			});
		}
	};

	// Delete Tier
	window.deleteTier = function(id) {
		if (confirm('Are you sure you want to delete this pricing tier? This action cannot be undone.')) {
			$.ajax({
				type: "POST",
				url: "ajax/tools/config_cbm_tier_ajax.php",
				data: { action: 'delete', id: id },
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						$('#resultados_ajax').html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert">' +
							'<i class="fas fa-check-circle"></i> ' + response.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
							'<span aria-hidden="true">&times;</span></button></div>'
						);
						setTimeout(function() {
							location.reload();
						}, 1000);
					} else {
						alert(response.message);
					}
				}
			});
		}
	};

	// Update CBM formula display based on measurement unit
	$('#cbm_measurement_unit').on('change', function() {
		var unit = $(this).val();
		var divisor, unitText;
		
		switch(unit) {
			case 'cm':
				divisor = '1,000,000';
				unitText = 'centimeters (cm)';
				break;
			case 'inch':
				divisor = '61,024';
				unitText = 'inches (in)';
				break;
			case 'm':
				divisor = '1';
				unitText = 'meters (m)';
				break;
			default:
				divisor = '1,000,000';
				unitText = 'centimeters (cm)';
		}
		
		$('#divisor').text(divisor);
		$('#unit-text').text(unitText);
	});

	// Initialize on page load
	$('#cbm_measurement_unit').trigger('change');

});
