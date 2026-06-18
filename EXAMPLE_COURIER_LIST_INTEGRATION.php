<?php
/**
 * EXAMPLE: How to integrate WhatsApp Direct Link buttons in courier_list_ajax.php
 * 
 * This file shows the modifications needed to add WhatsApp functionality
 * Copy the relevant sections to your actual courier_list_ajax.php file
 */
?>

<!-- STEP 1: Add this in the <head> section of your page (or in footer after jQuery) -->
<!-- In courier.php or wherever courier_list_ajax.php is loaded -->
<script src="dataJs/whatsapp_direct_link.js"></script>

<!-- STEP 2: Add bulk WhatsApp button near your other bulk action buttons -->
<!-- Usually placed above the table -->
<div class="row mb-3">
    <div class="col-md-12">
        <?php if ($userData->userlevel == 9) { ?>
            <button type="button" id="btn-whatsapp-bulk" class="btn btn-success btn-sm">
                <i class="fab fa-whatsapp"></i> Send WhatsApp to Selected
            </button>
            <button type="button" class="btn btn-info btn-sm" id="btn-whatsapp-bulk-receiver">
                <i class="fab fa-whatsapp"></i> WhatsApp Receivers
            </button>
        <?php } ?>
    </div>
</div>

<!-- STEP 3: In the table loop, modify your <tr> to include data attributes -->
<?php
// Inside your foreach ($data as $row) loop, after fetching sender_data and receiver_data:
?>
<tr class="card-hovera" 
    data-sender-phone="<?php echo htmlspecialchars($sender_data->phone ?? ''); ?>"
    data-receiver-phone="<?php echo htmlspecialchars($receiver_data->phone ?? ''); ?>"
    data-tracking="<?php echo $row->order_prefix . $row->order_no; ?>"
    data-order-id="<?php echo $row->order_id; ?>">

<!-- STEP 4: Add WhatsApp options in the dropdown menu -->
<!-- Inside the dropdown menu, add these after the email option: -->

<?php if ($userData->userlevel == 9 || $userData->userlevel == 2 || $userData->userlevel == 3) { ?>

    <!-- Divider before WhatsApp options -->
    <div class="dropdown-divider"></div>
    
    <!-- WhatsApp to Sender -->
    <?php if (!empty($sender_data->phone)) { ?>
        <a class="dropdown-item btn-whatsapp-single" 
           href="#" 
           data-order-id="<?php echo $row->order_id; ?>" 
           data-recipient-type="sender">
            <i class="fab fa-whatsapp" style="color:#25D366"></i>
            &nbsp;WhatsApp Sender
        </a>
    <?php } else { ?>
        <a class="dropdown-item disabled" href="#" title="No phone number">
            <i class="fab fa-whatsapp" style="color:#ccc"></i>
            &nbsp;WhatsApp Sender (No Phone)
        </a>
    <?php } ?>
    
    <!-- WhatsApp to Receiver -->
    <?php if (!empty($receiver_data->phone)) { ?>
        <a class="dropdown-item btn-whatsapp-single" 
           href="#" 
           data-order-id="<?php echo $row->order_id; ?>" 
           data-recipient-type="receiver">
            <i class="fab fa-whatsapp" style="color:#25D366"></i>
            &nbsp;WhatsApp Receiver
        </a>
    <?php } else { ?>
        <a class="dropdown-item disabled" href="#" title="No phone number">
            <i class="fab fa-whatsapp" style="color:#ccc"></i>
            &nbsp;WhatsApp Receiver (No Phone)
        </a>
    <?php } ?>
    
    <!-- Quick WhatsApp to Both -->
    <?php if (!empty($sender_data->phone) && !empty($receiver_data->phone)) { ?>
        <a class="dropdown-item btn-whatsapp-both" 
           href="#" 
           data-order-id="<?php echo $row->order_id; ?>">
            <i class="fab fa-whatsapp" style="color:#25D366"></i>
            &nbsp;WhatsApp Both (Sender & Receiver)
        </a>
    <?php } ?>

<?php } ?>

<!-- STEP 5: Add additional JavaScript for "WhatsApp Both" and "WhatsApp Receivers" features -->
<script>
$(document).ready(function() {
    
    // Handle "WhatsApp Both" button click
    $(document).on('click', '.btn-whatsapp-both', function(e) {
        e.preventDefault();
        var orderId = $(this).data('order-id');
        
        // Send to sender
        WhatsAppDirectLink.sendWithLogging(orderId, 'sender');
        
        // Send to receiver after 2 seconds
        setTimeout(function() {
            WhatsAppDirectLink.sendWithLogging(orderId, 'receiver');
        }, 2000);
    });
    
    // Handle bulk WhatsApp to receivers
    $(document).on('click', '#btn-whatsapp-bulk-receiver', function(e) {
        e.preventDefault();
        sendBulkWhatsAppToReceivers();
    });
    
    /**
     * Send bulk WhatsApp to receivers instead of senders
     */
    function sendBulkWhatsAppToReceivers() {
        var selectedShipments = [];
        
        $('input[name="checkbox[]"]:checked').each(function() {
            var orderNo = $(this).val();
            var $row = $(this).closest('tr');
            var receiverPhone = $row.data('receiver-phone');
            var tracking = $row.data('tracking');
            var orderId = $row.data('order-id');
            
            if (receiverPhone) {
                selectedShipments.push({
                    orderNo: orderNo,
                    orderId: orderId,
                    phone: receiverPhone,
                    trackingNumber: tracking
                });
            }
        });
        
        if (selectedShipments.length === 0) {
            swal('Error', 'Please select shipments with receiver phone numbers', 'warning');
            return;
        }
        
        // Fetch proper data and send
        var orderIds = selectedShipments.map(function(s) { return s.orderId; });
        
        $.ajax({
            type: 'POST',
            url: 'ajax/whatsapp/get_bulk_whatsapp_data_receivers.php',
            data: {
                order_ids: JSON.stringify(orderIds)
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.shipments) {
                    WhatsAppDirectLink.sendToBulk(response.shipments, 1500);
                } else {
                    swal('Error', response.message || 'Failed to get shipment data', 'error');
                }
            },
            error: function() {
                swal('Error', 'Failed to connect to server', 'error');
            }
        });
    }
});
</script>

<?php
/**
 * ALTERNATIVE: Direct WhatsApp Link Column
 * Add this as a new column in your table if you prefer icon buttons
 */
?>

<!-- In table header <thead> -->
<th class="text-center"><b>WhatsApp</b></th>

<!-- In table body <tbody> -->
<td class="text-center">
    <?php if (!empty($sender_data->phone)) { ?>
        <a href="#" 
           class="btn btn-success btn-xs btn-whatsapp-single" 
           data-order-id="<?php echo $row->order_id; ?>" 
           data-recipient-type="sender"
           title="WhatsApp Sender">
            <i class="fab fa-whatsapp"></i>
        </a>
    <?php } ?>
    
    <?php if (!empty($receiver_data->phone)) { ?>
        <a href="#" 
           class="btn btn-info btn-xs btn-whatsapp-single" 
           data-order-id="<?php echo $row->order_id; ?>" 
           data-recipient-type="receiver"
           title="WhatsApp Receiver">
            <i class="fab fa-whatsapp"></i>
        </a>
    <?php } ?>
</td>
