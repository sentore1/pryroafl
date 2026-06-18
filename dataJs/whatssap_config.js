"use strict";

$(document).ready(function () {

    // Show/hide sections based on selected provider
    function switchProvider(provider) {
        $('#section_ultramsg, #section_twilio, #section_meta').hide();
        $('#section_' + provider).show();

        // Update card active state
        $('.provider-card').removeClass('active');
        $('#card_' + provider).addClass('active');
    }

    // Init on page load
    var initialProvider = $('input[name="whatsapp_provider"]:checked').val() || 'ultramsg';
    switchProvider(initialProvider);

    // On provider change
    $('input[name="whatsapp_provider"]').on('change', function () {
        switchProvider($(this).val());
    });

    // Handle WhatsApp method change
    function updateMethodUI() {
        var method = $('#whatsapp_method').val();
        
        if (method === 'both') {
            $('#default_action_section').show();
        } else {
            $('#default_action_section').hide();
        }
        
        // Show/hide relevant switches based on method, but don't force their values
        if (method === 'api') {
            // API only - user can't use direct link buttons
            $('#enable_direct_link_buttons').closest('.col-md-6').hide();
            $('#enable_api_buttons').closest('.col-md-6').show();
        } else if (method === 'direct_link') {
            // Direct Link only - user can't use API buttons
            $('#enable_api_buttons').closest('.col-md-6').hide();
            $('#enable_direct_link_buttons').closest('.col-md-6').show();
        } else {
            // Both - show both switches
            $('#enable_direct_link_buttons').closest('.col-md-6').show();
            $('#enable_api_buttons').closest('.col-md-6').show();
        }
    }
    
    // Initialize method UI
    updateMethodUI();
    
    // On method change
    $('#whatsapp_method').on('change', function() {
        updateMethodUI();
    });

    // Highlight on input
    $('.ultramsg-field, .twilio-field, .meta-field').on('input', function () {
        $(this).toggleClass('highlight', $(this).val() === '');
    });

    // Form submit
    $("#save_data").submit(function (event) {
        event.preventDefault();

        var provider = $('input[name="whatsapp_provider"]:checked').val();
        var method = $('#whatsapp_method').val();
        var emptyFields = [];

        // Only validate API fields if API method is enabled
        if (method === 'api' || method === 'both') {
            if (provider === 'ultramsg') {
                if ($('#api_ws_url').val() === '')   emptyFields.push('api_ws_url');
                if ($('#api_ws_token').val() === '')  emptyFields.push('api_ws_token');
            } else if (provider === 'twilio') {
                if ($('#twilio_wa_sid').val() === '')    emptyFields.push('twilio_wa_sid');
                if ($('#twilio_wa_token').val() === '')  emptyFields.push('twilio_wa_token');
                if ($('#twilio_wa_number').val() === '') emptyFields.push('twilio_wa_number');
            } else if (provider === 'meta') {
                if ($('#meta_wa_token').val() === '')    emptyFields.push('meta_wa_token');
                if ($('#meta_wa_phone_id').val() === '') emptyFields.push('meta_wa_phone_id');
            }
        }

        if (emptyFields.length > 0 && (method === 'api' || method === 'both')) {
            Swal.fire({
                type: 'error',
                title: message_error_form21,
                text: 'Please fill in all required fields for the selected API provider',
                confirmButtonColor: '#336aea',
                showConfirmButton: true,
            });
            emptyFields.forEach(function (id) {
                $('#' + id).addClass('highlight');
            });
            return;
        }

        var data = new FormData($("#save_data")[0]);

        $.ajax({
            url: "./ajax/tools/api_whatsapp_config_ajax.php",
            type: 'POST',
            data: data,
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                Swal.fire({
                    title: message_error_form6,
                    text: message_error_form14,
                    type: 'info',
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    onBeforeOpen: function () { Swal.showLoading(); },
                });
            },
            success: function (response) {
                Swal.close();
                if (response.status === 'success') {
                    Swal.fire({
                        type: 'success',
                        title: 'Settings Saved Successfully!',
                        text: 'WhatsApp method: ' + method.toUpperCase(),
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true,
                    }).then(function () {
                        window.location.href = 'config_whatsapp.php';
                    });
                } else {
                    Swal.fire({
                        type: 'error',
                        title: message_error_form15,
                        text: response.message || message_error_form17,
                        confirmButtonColor: '#336aea',
                        showConfirmButton: true,
                    });
                }
            },
            error: function () {
                Swal.close();
                Swal.fire({
                    type: 'error',
                    title: message_error_form18,
                    text: message_error_form19,
                    confirmButtonColor: '#336aea',
                    showConfirmButton: true,
                });
            }
        });
    });
});
