<?php
// =============================================================
// AI PERMISSIONS HELPER
// Centralized permission checking for P-AI
// =============================================================

class AIPermissions {
    private $db;
    private $permissions = null;
    
    public function __construct() {
        $this->db = new Conexion;
        $this->loadPermissions();
    }
    
    /**
     * Load all permissions from database once
     */
    private function loadPermissions() {
        if ($this->permissions !== null) {
            return;
        }
        
        try {
            $this->db->cdp_query("SELECT * FROM cdb_settings LIMIT 1");
            $this->db->cdp_execute();
            $row = $this->db->cdp_registro();
            
            if ($row) {
                $this->permissions = $row;
            } else {
                // Default permissions if no settings row exists
                $this->permissions = new stdClass();
            }
        } catch (Exception $e) {
            $this->permissions = new stdClass();
        }
    }
    
    /**
     * Get a permission value (default to 0 if not set)
     */
    private function getPerm($field, $default = 0) {
        if (!isset($this->permissions->$field) || $this->permissions->$field === null) {
            return $default;
        }
        return (int)$this->permissions->$field;
    }
    
    // ============ AUTOPILOT ============
    public function isAutopilotEnabled() {
        return $this->getPerm('ai_autopilot_enabled', 0) === 1;
    }
    
    public function getAutopilotThreshold() {
        return $this->getPerm('ai_autopilot_threshold', 5);
    }
    
    // ============ READ PERMISSIONS ============
    public function canReadCustomers() {
        return $this->getPerm('ai_can_read_customers', 1) === 1;
    }
    
    public function canReadPackages() {
        return $this->getPerm('ai_can_read_packages', 1) === 1;
    }
    
    public function canReadFinancials() {
        return $this->getPerm('ai_can_read_financials', 1) === 1;
    }
    
    public function canReadDrivers() {
        return $this->getPerm('ai_can_read_drivers', 1) === 1;
    }
    
    public function canReadInventory() {
        return $this->getPerm('ai_can_read_inventory', 0) === 1;
    }
    
    // ============ ACTION PERMISSIONS ============
    public function canAssignDrivers() {
        return $this->getPerm('ai_can_assign_drivers', 1) === 1;
    }
    
    public function canConfirmPayments() {
        return $this->getPerm('ai_can_confirm_payments', 1) === 1;
    }
    
    public function canUpdateStatus() {
        return $this->getPerm('ai_can_update_status', 1) === 1;
    }
    
    public function canCreateShipments() {
        return $this->getPerm('ai_can_create_shipments', 0) === 1;
    }
    
    public function canEditShipments() {
        return $this->getPerm('ai_can_edit_shipments', 0) === 1;
    }
    
    public function canCancelShipments() {
        return $this->getPerm('ai_can_cancel_shipments', 0) === 1;
    }
    
    // ============ COMMUNICATION PERMISSIONS ============
    public function canSendSMS() {
        return $this->getPerm('ai_can_send_sms', 0) === 1;
    }
    
    public function canSendEmail() {
        return $this->getPerm('ai_can_send_email', 0) === 1;
    }
    
    public function canSendWhatsApp() {
        return $this->getPerm('ai_can_send_whatsapp', 0) === 1;
    }
    
    // ============ REPORTING PERMISSIONS ============
    public function canGenerateReports() {
        return $this->getPerm('ai_can_generate_reports', 1) === 1;
    }
    
    public function canExportData() {
        return $this->getPerm('ai_can_export_data', 1) === 1;
    }
    
    // ============ CUSTOMER MANAGEMENT ============
    public function canCreateCustomers() {
        return $this->getPerm('ai_can_create_customers', 0) === 1;
    }
    
    public function canEditCustomers() {
        return $this->getPerm('ai_can_edit_customers', 0) === 1;
    }
    
    // ============ FINANCIAL OPERATIONS ============
    public function canProcessRefunds() {
        return $this->getPerm('ai_can_process_refunds', 0) === 1;
    }
    
    public function canApplyDiscounts() {
        return $this->getPerm('ai_can_apply_discounts', 0) === 1;
    }
    
    // ============ ADVANCED FEATURES ============
    public function canPredictAnalytics() {
        return $this->getPerm('ai_can_predict_analytics', 1) === 1;
    }
    
    public function canOptimizeRoutes() {
        return $this->getPerm('ai_can_optimize_routes', 0) === 1;
    }
    
    // ============ UTILITY METHODS ============
    
    /**
     * Get all permissions as an array for system prompt
     */
    public function getAllPermissions() {
        return [
            'autopilot' => [
                'enabled' => $this->isAutopilotEnabled(),
                'threshold' => $this->getAutopilotThreshold(),
            ],
            'read' => [
                'customers' => $this->canReadCustomers(),
                'packages' => $this->canReadPackages(),
                'financials' => $this->canReadFinancials(),
                'drivers' => $this->canReadDrivers(),
                'inventory' => $this->canReadInventory(),
            ],
            'actions' => [
                'assign_drivers' => $this->canAssignDrivers(),
                'confirm_payments' => $this->canConfirmPayments(),
                'update_status' => $this->canUpdateStatus(),
                'create_shipments' => $this->canCreateShipments(),
                'edit_shipments' => $this->canEditShipments(),
                'cancel_shipments' => $this->canCancelShipments(),
            ],
            'communication' => [
                'send_sms' => $this->canSendSMS(),
                'send_email' => $this->canSendEmail(),
                'send_whatsapp' => $this->canSendWhatsApp(),
            ],
            'reporting' => [
                'generate_reports' => $this->canGenerateReports(),
                'export_data' => $this->canExportData(),
            ],
            'customer_management' => [
                'create_customers' => $this->canCreateCustomers(),
                'edit_customers' => $this->canEditCustomers(),
            ],
            'financial' => [
                'process_refunds' => $this->canProcessRefunds(),
                'apply_discounts' => $this->canApplyDiscounts(),
            ],
            'advanced' => [
                'predict_analytics' => $this->canPredictAnalytics(),
                'optimize_routes' => $this->canOptimizeRoutes(),
            ],
        ];
    }
    
    /**
     * Generate a human-readable permissions summary for the system prompt
     */
    public function getPermissionsSummary() {
        $summary = [];
        
        if ($this->isAutopilotEnabled()) {
            $summary[] = "Autopilot mode is ENABLED (threshold: " . $this->getAutopilotThreshold() . " items)";
        }
        
        $allowed = [];
        $denied = [];
        
        // Actions
        if ($this->canAssignDrivers()) $allowed[] = "assign drivers";
        if ($this->canConfirmPayments()) $allowed[] = "confirm payments";
        if ($this->canUpdateStatus()) $allowed[] = "update shipment status";
        if ($this->canCreateShipments()) $allowed[] = "create new shipments";
        if ($this->canEditShipments()) $allowed[] = "edit shipments";
        if ($this->canCancelShipments()) $allowed[] = "cancel shipments";
        
        // Communication
        if ($this->canSendSMS()) $allowed[] = "send SMS";
        if ($this->canSendEmail()) $allowed[] = "send emails";
        if ($this->canSendWhatsApp()) $allowed[] = "send WhatsApp messages";
        
        // Reporting
        if ($this->canGenerateReports()) $allowed[] = "generate reports";
        if ($this->canExportData()) $allowed[] = "export data";
        
        // Customer management
        if ($this->canCreateCustomers()) $allowed[] = "create customers";
        if ($this->canEditCustomers()) $allowed[] = "edit customers";
        
        // Financial
        if ($this->canProcessRefunds()) $allowed[] = "process refunds";
        if ($this->canApplyDiscounts()) $allowed[] = "apply discounts";
        
        // Advanced
        if ($this->canPredictAnalytics()) $allowed[] = "provide predictive analytics";
        if ($this->canOptimizeRoutes()) $allowed[] = "optimize routes";
        
        $summary[] = "YOU ARE ALLOWED TO: " . implode(", ", $allowed);
        
        // Denied actions
        if (!$this->canCreateShipments()) $denied[] = "create shipments";
        if (!$this->canEditShipments()) $denied[] = "edit shipments";
        if (!$this->canCancelShipments()) $denied[] = "cancel shipments";
        if (!$this->canSendSMS()) $denied[] = "send SMS";
        if (!$this->canSendEmail()) $denied[] = "send emails";
        if (!$this->canSendWhatsApp()) $denied[] = "send WhatsApp";
        if (!$this->canProcessRefunds()) $denied[] = "process refunds";
        if (!$this->canApplyDiscounts()) $denied[] = "apply discounts";
        
        if (!empty($denied)) {
            $summary[] = "YOU ARE NOT ALLOWED TO: " . implode(", ", $denied) . ". If user asks for these, explain they need to enable them in AI Settings.";
        }
        
        return implode("\n", $summary);
    }
}
