<?php
/**
 * WhatsApp Helper Functions
 * Controls which WhatsApp methods are available based on settings
 */

/**
 * Check if API method is enabled
 * @return bool
 */
function isWhatsAppAPIEnabled() {
    $core = new Core();
    
    if (!isset($core->active_whatsapp) || $core->active_whatsapp != 1) {
        return false;
    }
    
    $method = $core->whatsapp_method ?? 'api';
    return in_array($method, ['api', 'both']);
}

/**
 * Check if Direct Link method is enabled
 * @return bool
 */
function isWhatsAppDirectLinkEnabled() {
    $core = new Core();
    
    if (!isset($core->active_whatsapp) || $core->active_whatsapp != 1) {
        return false;
    }
    
    $method = $core->whatsapp_method ?? 'api';
    return in_array($method, ['direct_link', 'both']);
}

/**
 * Check if Direct Link buttons should be shown
 * @return bool
 */
function showDirectLinkButtons() {
    $core = new Core();
    
    if (!isWhatsAppDirectLinkEnabled()) {
        return false;
    }
    
    return isset($core->enable_direct_link_buttons) && $core->enable_direct_link_buttons == 1;
}

/**
 * Check if API notification checkboxes should be shown
 * @return bool
 */
function showAPINotificationCheckboxes() {
    $core = new Core();
    
    if (!isWhatsAppAPIEnabled()) {
        return false;
    }
    
    return isset($core->enable_api_buttons) && $core->enable_api_buttons == 1;
}

/**
 * Get the default WhatsApp action for automatic notifications
 * @return string 'api', 'direct_link', or 'none'
 */
function getWhatsAppDefaultAction() {
    $core = new Core();
    return $core->whatsapp_default_action ?? 'api';
}

/**
 * Check if automatic WhatsApp notifications should be sent via API
 * @return bool
 */
function shouldSendAutoWhatsAppViaAPI() {
    if (!isWhatsAppAPIEnabled()) {
        return false;
    }
    
    $defaultAction = getWhatsAppDefaultAction();
    return $defaultAction === 'api';
}

/**
 * Get WhatsApp method configuration info
 * @return array
 */
function getWhatsAppMethodInfo() {
    $core = new Core();
    
    return [
        'active' => isset($core->active_whatsapp) && $core->active_whatsapp == 1,
        'method' => $core->whatsapp_method ?? 'api',
        'default_action' => $core->whatsapp_default_action ?? 'api',
        'show_direct_link_buttons' => showDirectLinkButtons(),
        'show_api_checkboxes' => showAPINotificationCheckboxes(),
        'api_enabled' => isWhatsAppAPIEnabled(),
        'direct_link_enabled' => isWhatsAppDirectLinkEnabled(),
    ];
}

/**
 * Get WhatsApp button HTML for single shipment
 * @param int $orderId
 * @param string $recipientType 'sender' or 'receiver'
 * @param string $phone
 * @return string HTML
 */
function getWhatsAppButton($orderId, $recipientType = 'sender', $phone = '') {
    if (!showDirectLinkButtons()) {
        return '';
    }
    
    if (empty($phone)) {
        return '<a class="dropdown-item disabled" href="#" title="No phone number">
            <i class="fab fa-whatsapp" style="color:#ccc"></i>
            &nbsp;WhatsApp ' . ucfirst($recipientType) . ' (No Phone)
        </a>';
    }
    
    return '<a class="dropdown-item btn-whatsapp-single" 
       href="#" 
       data-order-id="' . htmlspecialchars($orderId) . '" 
       data-recipient-type="' . htmlspecialchars($recipientType) . '">
        <i class="fab fa-whatsapp" style="color:#25D366"></i>
        &nbsp;WhatsApp ' . ucfirst($recipientType) . '
    </a>';
}

/**
 * Get bulk WhatsApp button HTML
 * @return string HTML
 */
function getBulkWhatsAppButton() {
    if (!showDirectLinkButtons()) {
        return '';
    }
    
    return '<button type="button" id="btn-whatsapp-bulk" class="btn btn-success btn-sm">
        <i class="fab fa-whatsapp"></i> Send WhatsApp (Bulk)
    </button>';
}

/**
 * Include WhatsApp Direct Link JavaScript if enabled
 * @return string HTML
 */
function includeWhatsAppDirectLinkJS() {
    if (!isWhatsAppDirectLinkEnabled()) {
        return '';
    }
    
    return '<script src="dataJs/whatsapp_direct_link.js"></script>';
}

/**
 * Check if notification checkbox should trigger API send
 * Used in courier add/edit forms
 * @param bool $checkboxChecked
 * @return bool
 */
function shouldProcessAPINotification($checkboxChecked) {
    if (!$checkboxChecked) {
        return false;
    }
    
    return shouldSendAutoWhatsAppViaAPI();
}
