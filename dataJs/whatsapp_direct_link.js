/**
 * WhatsApp Direct Link Utility
 * Allows sending WhatsApp messages via wa.me links
 * Works for single and bulk operations
 */

var WhatsAppDirectLink = (function() {
    'use strict';

    /**
     * Format phone number for WhatsApp (remove special chars, ensure starts with country code)
     * @param {string} phone - Phone number
     * @returns {string} Formatted phone number
     */
    function formatPhoneNumber(phone) {
        if (!phone) return '';
        
        // Remove all non-numeric characters except +
        var cleaned = phone.replace(/[^\d+]/g, '');
        
        // Remove leading zeros and ensure no +
        cleaned = cleaned.replace(/^0+/, '').replace(/^\+/, '');
        
        return cleaned;
    }

    /**
     * Generate WhatsApp message with tracking info
     * @param {object} data - Shipment data
     * @returns {string} Formatted message
     */
    function generateMessage(data) {
        var companyName = data.companyName || 'Our Company';
        var trackingNumber = data.trackingNumber || '';
        var customerName = data.customerName || 'Customer';
        var status = data.status || '';
        var url = data.siteUrl || window.location.origin;

        var message = `Hello ${customerName},\n\n`;
        
        if (trackingNumber) {
            message += `Your shipment tracking number is: *${trackingNumber}*\n\n`;
        }
        
        if (status) {
            message += `Status: ${status}\n\n`;
        }
        
        message += `Track your shipment here:\n${url}/track.php?tracking_id=${trackingNumber}\n\n`;
        message += `Best regards,\n${companyName}`;

        return encodeURIComponent(message);
    }

    /**
     * Open WhatsApp with pre-filled message
     * @param {string} phone - Phone number
     * @param {string} message - Message to send
     * @param {boolean} newTab - Open in new tab (default: true)
     */
    function openWhatsApp(phone, message, newTab) {
        newTab = (newTab === undefined) ? true : newTab;
        
        var formattedPhone = formatPhoneNumber(phone);
        
        if (!formattedPhone) {
            swal('Error', 'Invalid phone number', 'error');
            return false;
        }

        var encodedMessage = encodeURIComponent(message);
        var whatsappUrl = `https://wa.me/${formattedPhone}?text=${encodedMessage}`;
        
        if (newTab) {
            window.open(whatsappUrl, '_blank');
        } else {
            window.location.href = whatsappUrl;
        }
        
        return true;
    }

    /**
     * Send WhatsApp to single shipment
     * @param {object} shipmentData - Contains phone, trackingNumber, customerName, etc.
     */
    function sendToSingleShipment(shipmentData) {
        var message = shipmentData.customMessage || generateMessage(shipmentData);
        openWhatsApp(shipmentData.phone, message, true);
    }

    /**
     * Send WhatsApp to multiple shipments (opens sequentially)
     * @param {array} shipmentsArray - Array of shipment objects
     * @param {number} delay - Delay between opening tabs (milliseconds, default: 1500)
     */
    function sendToBulkShipments(shipmentsArray, delay) {
        delay = delay || 1500; // Default 1.5 seconds between tabs
        
        if (!shipmentsArray || shipmentsArray.length === 0) {
            swal('Error', 'No shipments selected', 'error');
            return;
        }

        // Ask for confirmation
        swal({
            title: 'Send WhatsApp to ' + shipmentsArray.length + ' customers?',
            text: 'This will open ' + shipmentsArray.length + ' WhatsApp tabs',
            icon: 'warning',
            buttons: {
                cancel: 'Cancel',
                confirm: 'Yes, send!'
            }
        }).then(function(confirmed) {
            if (confirmed) {
                sendBulkWithDelay(shipmentsArray, delay, 0);
            }
        });
    }

    /**
     * Internal function to send bulk with delay
     * @param {array} shipments - Array of shipments
     * @param {number} delay - Delay between sends
     * @param {number} index - Current index
     */
    function sendBulkWithDelay(shipments, delay, index) {
        if (index >= shipments.length) {
            swal('Success', 'All WhatsApp messages opened!', 'success');
            return;
        }

        var shipment = shipments[index];
        var message = shipment.customMessage || generateMessage(shipment);
        
        openWhatsApp(shipment.phone, message, true);
        
        // Show progress
        console.log('Sent ' + (index + 1) + ' of ' + shipments.length);
        
        // Schedule next
        setTimeout(function() {
            sendBulkWithDelay(shipments, delay, index + 1);
        }, delay);
    }

    /**
     * Send WhatsApp from AJAX call (logs to server)
     * @param {string} orderId - Order ID
     * @param {string} recipientType - 'sender' or 'receiver'
     */
    function sendWithLogging(orderId, recipientType) {
        $.ajax({
            type: 'POST',
            url: 'ajax/whatsapp/get_whatsapp_link_data.php',
            data: {
                order_id: orderId,
                recipient_type: recipientType
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    openWhatsApp(response.phone, response.message, true);
                    
                    // Log the action
                    logWhatsAppAction(orderId, recipientType);
                } else {
                    swal('Error', response.message || 'Failed to get WhatsApp data', 'error');
                }
            },
            error: function() {
                swal('Error', 'Failed to connect to server', 'error');
            }
        });
    }

    /**
     * Log WhatsApp action to database
     * @param {string} orderId - Order ID
     * @param {string} recipientType - 'sender' or 'receiver'
     */
    function logWhatsAppAction(orderId, recipientType) {
        $.ajax({
            type: 'POST',
            url: 'ajax/whatsapp/log_whatsapp_action.php',
            data: {
                order_id: orderId,
                recipient_type: recipientType,
                action_type: 'direct_link'
            }
        });
    }

    /**
     * Send from bulk checkboxes (like existing bulk operations)
     */
    function sendFromBulkCheckboxes() {
        var selectedShipments = [];
        
        $('input[name="checkbox[]"]:checked').each(function() {
            var orderNo = $(this).val();
            var $row = $(this).closest('tr');
            
            // Extract data from row (you may need to adjust selectors)
            selectedShipments.push({
                orderNo: orderNo,
                // Add data-attributes to your table rows for easy access
                phone: $row.data('sender-phone') || $row.data('receiver-phone'),
                trackingNumber: $row.find('td:eq(1) a').text().trim(),
                customerName: $row.find('td:eq(3)').text().trim() || $row.find('td:eq(2)').text().trim(),
                companyName: window.COMPANY_NAME || 'Our Company',
                siteUrl: window.SITE_URL || window.location.origin
            });
        });

        if (selectedShipments.length === 0) {
            swal('Error', 'Please select at least one shipment', 'warning');
            return;
        }

        // Send via AJAX to get proper phone numbers
        sendBulkViaAjax(selectedShipments);
    }

    /**
     * Send bulk via AJAX to get proper data
     * @param {array} shipments - Selected shipments
     */
    function sendBulkViaAjax(shipments) {
        var orderNumbers = shipments.map(function(s) { return s.orderNo; });
        
        $.ajax({
            type: 'POST',
            url: 'ajax/whatsapp/get_bulk_whatsapp_data.php',
            data: {
                order_numbers: JSON.stringify(orderNumbers)
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.shipments) {
                    sendToBulkShipments(response.shipments, 1500);
                } else {
                    swal('Error', response.message || 'Failed to get shipment data', 'error');
                }
            },
            error: function() {
                swal('Error', 'Failed to connect to server', 'error');
            }
        });
    }

    // Public API
    return {
        openWhatsApp: openWhatsApp,
        sendToSingle: sendToSingleShipment,
        sendToBulk: sendToBulkShipments,
        sendWithLogging: sendWithLogging,
        sendFromBulkCheckboxes: sendFromBulkCheckboxes,
        generateMessage: generateMessage,
        formatPhone: formatPhoneNumber
    };
})();


// jQuery helper functions
$(document).ready(function() {
    
    // Single WhatsApp button click
    $(document).on('click', '.btn-whatsapp-single', function(e) {
        e.preventDefault();
        var orderId = $(this).data('order-id');
        var recipientType = $(this).data('recipient-type') || 'sender';
        
        WhatsAppDirectLink.sendWithLogging(orderId, recipientType);
    });

    // Bulk WhatsApp button click
    $(document).on('click', '#btn-whatsapp-bulk', function(e) {
        e.preventDefault();
        WhatsAppDirectLink.sendFromBulkCheckboxes();
    });
});
