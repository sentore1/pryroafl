<?php
// =============================================================
// AI PERMISSIONS HELPER — Per-user AI permission system
// Priority: user column > global cdb_settings > hard default
// NULL in user column = inherit from global setting
// Super admin (userlevel=9) gets everything unless explicitly 0
// =============================================================

class AIPermissions {
    private $db;
    private $settings  = null; // global row from cdb_settings
    private $userPerms = null; // logged-in user's row from cdb_users

    public function __construct() {
        $this->db = new Conexion;
        $this->loadPermissions();
    }

    private function loadPermissions(): void {
        if ($this->settings !== null) return;
        try {
            // Global settings
            $this->db->cdp_query("SELECT * FROM cdb_settings LIMIT 1");
            $this->db->cdp_execute();
            $this->settings = $this->db->cdp_registro() ?: new stdClass();

            // Per-user overrides
            $uid = isset($_SESSION['userid']) ? (int)$_SESSION['userid'] : 0;
            if ($uid > 0) {
                $this->db->cdp_query(
                    "SELECT userlevel, ai_access,
                     ai_can_assign_drivers, ai_can_confirm_payments, ai_can_update_status,
                     ai_can_create_shipments, ai_can_edit_shipments, ai_can_cancel_shipments,
                     ai_can_send_sms, ai_can_send_email, ai_can_send_whatsapp,
                     ai_can_generate_reports, ai_can_export_data,
                     ai_can_create_customers, ai_can_edit_customers,
                     ai_can_process_refunds, ai_can_apply_discounts
                     FROM cdb_users WHERE id=:id LIMIT 1"
                );
                $this->db->bind(':id', $uid);
                $this->db->cdp_execute();
                $this->userPerms = $this->db->cdp_registro() ?: null;
            }
        } catch (Exception $e) {
            $this->settings  = new stdClass();
            $this->userPerms = null;
        }
    }

    /** Resolve a single permission with 3-level fallback */
    private function getPerm(string $field, int $default = 0): int {
        $level = $this->userPerms ? (int)($this->userPerms->userlevel ?? 0) : 0;

        // Explicit user override (0 or 1, not NULL)
        if ($this->userPerms && property_exists($this->userPerms, $field) && $this->userPerms->$field !== null) {
            return (int)$this->userPerms->$field;
        }

        // Super admin gets everything by default
        if ($level === 9) return 1;

        // Global setting fallback
        if ($this->settings && property_exists($this->settings, $field) && $this->settings->$field !== null) {
            return (int)$this->settings->$field;
        }

        return $default;
    }

    private function getGlobal(string $field, int $default = 0): int {
        if ($this->settings && property_exists($this->settings, $field) && $this->settings->$field !== null)
            return (int)$this->settings->$field;
        return $default;
    }

    // ── AI Panel Access ────────────────────────────────────────────────
    public function canAccessAI(): bool {
        if (!$this->userPerms) return false;
        $level = (int)($this->userPerms->userlevel ?? 0);
        if ($level === 9) return true;
        if ($level === 2) {
            if (property_exists($this->userPerms, 'ai_access') && $this->userPerms->ai_access !== null)
                return (bool)(int)$this->userPerms->ai_access;
            return true; // employees can access by default
        }
        return false;
    }

    // ── Autopilot (global only) ────────────────────────────────────────
    public function isAutopilotEnabled(): bool { return (bool)$this->getGlobal('ai_autopilot_enabled', 0); }
    public function getAutopilotThreshold(): int { return $this->getGlobal('ai_autopilot_threshold', 5); }

    // ── Action Permissions ─────────────────────────────────────────────
    public function canAssignDrivers():   bool { return (bool)$this->getPerm('ai_can_assign_drivers',   1); }
    public function canConfirmPayments(): bool { return (bool)$this->getPerm('ai_can_confirm_payments', 1); }
    public function canUpdateStatus():    bool { return (bool)$this->getPerm('ai_can_update_status',    1); }
    public function canCreateShipments(): bool { return (bool)$this->getPerm('ai_can_create_shipments', 0); }
    public function canEditShipments():   bool { return (bool)$this->getPerm('ai_can_edit_shipments',   0); }
    public function canCancelShipments(): bool { return (bool)$this->getPerm('ai_can_cancel_shipments', 0); }

    // ── Communication ──────────────────────────────────────────────────
    public function canSendSMS():      bool { return (bool)$this->getPerm('ai_can_send_sms',      0); }
    public function canSendEmail():    bool { return (bool)$this->getPerm('ai_can_send_email',    0); }
    public function canSendWhatsApp(): bool { return (bool)$this->getPerm('ai_can_send_whatsapp', 0); }

    // ── Reporting ──────────────────────────────────────────────────────
    public function canGenerateReports(): bool { return (bool)$this->getPerm('ai_can_generate_reports', 1); }
    public function canExportData():      bool { return (bool)$this->getPerm('ai_can_export_data',      1); }

    // ── Customer Management ────────────────────────────────────────────
    public function canCreateCustomers(): bool { return (bool)$this->getPerm('ai_can_create_customers', 0); }
    public function canEditCustomers():   bool { return (bool)$this->getPerm('ai_can_edit_customers',   0); }

    // ── Financial ─────────────────────────────────────────────────────
    public function canProcessRefunds(): bool { return (bool)$this->getPerm('ai_can_process_refunds', 0); }
    public function canApplyDiscounts(): bool { return (bool)$this->getPerm('ai_can_apply_discounts', 0); }

    // ── Legacy stubs (always true — data is always readable by AI) ─────
    public function canReadCustomers():    bool { return true; }
    public function canReadPackages():     bool { return true; }
    public function canReadFinancials():   bool { return true; }
    public function canReadDrivers():      bool { return true; }
    public function canReadInventory():    bool { return true; }
    public function canPredictAnalytics(): bool { return true; }
    public function canOptimizeRoutes():   bool { return false; }

    // ── Full array for AI panel UI ─────────────────────────────────────
    public function getAllPermissions(): array {
        return [
            'autopilot' => [
                'enabled'   => $this->isAutopilotEnabled(),
                'threshold' => $this->getAutopilotThreshold(),
            ],
            'actions' => [
                'assign_drivers'   => $this->canAssignDrivers(),
                'confirm_payments' => $this->canConfirmPayments(),
                'update_status'    => $this->canUpdateStatus(),
                'create_shipments' => $this->canCreateShipments(),
                'edit_shipments'   => $this->canEditShipments(),
                'cancel_shipments' => $this->canCancelShipments(),
            ],
            'communication' => [
                'send_sms'       => $this->canSendSMS(),
                'send_email'     => $this->canSendEmail(),
                'send_whatsapp'  => $this->canSendWhatsApp(),
            ],
            'reporting' => [
                'generate_reports' => $this->canGenerateReports(),
                'export_data'      => $this->canExportData(),
            ],
            'customer_management' => [
                'create_customers' => $this->canCreateCustomers(),
                'edit_customers'   => $this->canEditCustomers(),
            ],
            'financial' => [
                'process_refunds'  => $this->canProcessRefunds(),
                'apply_discounts'  => $this->canApplyDiscounts(),
            ],
            'advanced' => [
                'predict_analytics' => $this->canPredictAnalytics(),
                'optimize_routes'   => $this->canOptimizeRoutes(),
            ],
        ];
    }

    // ── Human-readable summary for the AI system prompt ───────────────
    public function getPermissionsSummary(): string {
        $allowed = []; $denied = [];
        if ($this->canAssignDrivers())   $allowed[] = 'assign drivers';
        if ($this->canConfirmPayments()) $allowed[] = 'confirm payments';
        if ($this->canUpdateStatus())    $allowed[] = 'update shipment status';
        if ($this->canCreateShipments()) $allowed[] = 'create shipments';
        if ($this->canEditShipments())   $allowed[] = 'edit shipments';
        if ($this->canCancelShipments()) $allowed[] = 'cancel shipments';
        if ($this->canSendSMS())         $allowed[] = 'send SMS';
        if ($this->canSendEmail())       $allowed[] = 'send emails';
        if ($this->canSendWhatsApp())    $allowed[] = 'send WhatsApp';
        if ($this->canGenerateReports()) $allowed[] = 'generate reports';
        if ($this->canExportData())      $allowed[] = 'export data';
        if ($this->canCreateCustomers()) $allowed[] = 'create customers';
        if ($this->canEditCustomers())   $allowed[] = 'edit customers';
        if ($this->canProcessRefunds())  $allowed[] = 'process refunds';
        if ($this->canApplyDiscounts())  $allowed[] = 'apply discounts';

        if (!$this->canCancelShipments()) $denied[] = 'cancel shipments';
        if (!$this->canSendSMS())         $denied[] = 'send SMS';
        if (!$this->canSendEmail())       $denied[] = 'send emails';
        if (!$this->canSendWhatsApp())    $denied[] = 'send WhatsApp';
        if (!$this->canProcessRefunds())  $denied[] = 'process refunds';
        if (!$this->canApplyDiscounts())  $denied[] = 'apply discounts';

        $lines = ['YOU ARE ALLOWED TO: ' . implode(', ', $allowed)];
        if (!empty($denied))
            $lines[] = 'YOU ARE NOT ALLOWED TO: ' . implode(', ', $denied) . '. If user asks, explain they need admin to enable it in AI Settings.';
        if ($this->isAutopilotEnabled())
            $lines[] = 'Autopilot mode is ENABLED (threshold: ' . $this->getAutopilotThreshold() . ' items)';
        return implode("\n", $lines);
    }
}
