<?php
// =============================================================
// PRYRO LOCAL AI ENGINE — fallback when API credits are gone
// Responds using live context data. User never sees an error.
// =============================================================

function cdp_detect_intent(string $msg): string {
    $m = strtolower(trim($msg));

    // Create / add intents — must come BEFORE generic list checks
    if (preg_match('/\b(create|add|new|register|make)\b.{0,20}\b(customer|client)\b/', $m)) return 'create_customer';
    if (preg_match('/\b(create|add|new|register|make)\b.{0,20}\b(driver)\b/', $m))          return 'create_driver';
    if (preg_match('/\b(create|add|new|register|make)\b.{0,20}\b(employee|staff|user)\b/', $m)) return 'create_employee';
    if (preg_match('/\b(create|add|new|register)\b.{0,20}\b(recipient|consignee)\b/', $m))  return 'create_recipient';
    if (preg_match('/\b(create|add|new)\b.{0,20}\b(shipment|order|courier)\b/', $m))        return 'create_shipment';
    if (preg_match('/\b(add|new)\b.{0,20}\b(pre.?alert|prealert)\b/', $m))                  return 'add_prealert';
    if (preg_match('/\b(schedule|book|arrange)\b.{0,20}\b(pickup|pick.up)\b/', $m))         return 'schedule_pickup';

    // Delete intents
    if (preg_match('/\b(delete|remove|deactivate)\b.{0,20}\b(customer|client)\b/', $m))     return 'delete_customer';
    if (preg_match('/\b(delete|remove|deactivate)\b.{0,20}\b(driver)\b/', $m))              return 'delete_driver';
    if (preg_match('/\b(delete|remove)\b.{0,20}\b(shipment|order)\b/', $m))                 return 'delete_shipment';

    // Update / edit intents
    if (preg_match('/\b(update|edit|change)\b.{0,20}\b(customer|client)\b/', $m))           return 'update_customer';
    if (preg_match('/\b(update|edit|change)\b.{0,20}\b(driver)\b/', $m))                    return 'edit_driver';
    if (preg_match('/\b(reset|change)\b.{0,20}\b(password)\b/', $m))                        return 'reset_customer_password';
    if (preg_match('/\b(mark|set)\b.{0,20}\b(delivered)\b/', $m))                           return 'mark_delivered';
    if (preg_match('/\b(update|add)\b.{0,20}\b(tracking|status)\b/', $m))                   return 'add_tracking_note';
    if (preg_match('/\b(cancel)\b.{0,20}\b(shipment|order|courier)\b/', $m))                return 'cancel_shipment';
    if (preg_match('/\b(accept)\b.{0,20}\b(pickup)\b/', $m))                                return 'accept_pickup';
    if (preg_match('/\b(cancel)\b.{0,20}\b(pickup)\b/', $m))                                return 'cancel_pickup';
    if (preg_match('/\b(bulk|multiple)\b.{0,20}\b(assign|driver)\b/', $m))                  return 'bulk_assign_driver';
    if (preg_match('/\b(bulk|multiple)\b.{0,20}\b(status|update)\b/', $m))                  return 'bulk_update_status';
    if (preg_match('/\b(assign|set)\b.{0,20}\b(driver)\b/', $m))                            return 'assign_driver';

    // Payment intents
    if (preg_match('/\b(confirm)\b.{0,20}\b(payment|invoice)\b/', $m))                      return 'confirm_payment';
    if (preg_match('/\b(record|log)\b.{0,20}\b(payment|charge)\b/', $m))                    return 'record_payment';
    if (preg_match('/\b(refund)\b/', $m))                                                    return 'refund_payment';
    if (preg_match('/\b(discount)\b/', $m))                                                  return 'apply_discount';
    if (preg_match('/\b(payment|invoice|overdue|unpaid|due|wire)\b/', $m))                   return 'payments';

    // Communication intents
    if (preg_match('/\b(send|bulk)\b.{0,20}\b(sms|text)\b/', $m))                           return 'send_sms';
    if (preg_match('/\b(send|write)\b.{0,20}\b(email|mail)\b/', $m))                        return 'send_email';
    if (preg_match('/\b(send)\b.{0,20}\b(whatsapp|wa)\b/', $m))                             return 'send_whatsapp';

    // Report intents
    if (preg_match('/\b(report|statistic|stat|analytics)\b/', $m))                          return 'reports';

    // ── List / view intents — broad matching, catches "all customers", "customers", etc.
    if (preg_match('/\b(all|list|show|view|our|the)\b.{0,15}\b(customer|client)s?\b/', $m)) return 'customers';
    if (preg_match('/^(customer|client)s?\s*$/', $m))                                        return 'customers';
    if (preg_match('/\b(customer|client)s?\b/', $m) && !preg_match('/\b(create|add|new|delete|update)\b/', $m)) return 'customers';

    if (preg_match('/\b(all|list|show|view)\b.{0,15}\b(driver)s?\b/', $m))                  return 'drivers';
    if (preg_match('/\b(driver)s?\b/', $m) && !preg_match('/\b(create|add|new|delete|assign)\b/', $m)) return 'drivers';

    if (preg_match('/\b(all|list|show|view)\b.{0,15}\b(shipment|order)s?\b/', $m))          return 'shipments';
    if (preg_match('/\b(all|list|show|view)\b.{0,15}\b(pickup)s?\b/', $m))                  return 'pickups';

    // Info intents
    if (preg_match('/\b(briefing|overview|summary|dashboard|how.*(things|going|today))\b/', $m)) return 'briefing';
    if (preg_match('/\b(stuck|delayed|not moving|stalled)\b/', $m))                          return 'stuck';
    if (preg_match('/\b(revenue|income|earnings|profit|this month|last month)\b/', $m))      return 'revenue';
    if (preg_match('/\b(pre.?alert|prealert)\b/', $m))                                       return 'prealerts';
    if (preg_match('/\b(pickup|pick.up)\b/', $m))                                            return 'pickups';
    if (preg_match('/\b(shipment|order|package|track)\b/', $m))                              return 'shipments';
    if (preg_match('/\b(help|what can you do|commands)\b/', $m))                             return 'help';
    if (preg_match('/\b(hello|hi|hey|good morning|good afternoon|bonjour|muraho|salut)\b/', $m)) return 'greeting';

    return 'unknown';
}

// Main engine — returns reply text
function cdp_local_ai_engine(string $message, array $ctx, string $currency, $perms): string {
    $intent = cdp_detect_intent($message);

    // Extract data from message for pre-filling modals
    $fname = $lname = $email = $phone = '';
    if (preg_match('/called?\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)?)/i', $message, $m)) {
        $parts = explode(' ', trim($m[1])); $fname = $parts[0] ?? ''; $lname = $parts[1] ?? '';
    }
    if (preg_match('/[\w.+-]+@[\w-]+\.[a-zA-Z]{2,}/', $message, $m)) $email = $m[0];
    if (preg_match('/\+?\d[\d\s\-]{8,14}\d/', $message, $m)) $phone = preg_replace('/\s+/', '', $m[0]);

    switch ($intent) {
        case 'greeting':
            $hour = (int)date('H');
            $greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
            $stuck = count($ctx['stuck_shipments'] ?? []);
            $pay   = count($ctx['pending_payments'] ?? []);
            return "$greet! I'm Pryro AI. Here's a quick snapshot:\n\n"
                . ($stuck > 0 ? "- ⚠️ **$stuck stuck shipment(s)** need attention\n" : "- ✅ No stuck shipments\n")
                . ($pay > 0   ? "- 💰 **$pay pending payment(s)** waiting\n" : "- ✅ All payments up to date\n")
                . "- 👥 **" . ($ctx['total_customers'] ?? 0) . " customers** on record\n"
                . "- 📦 **" . ($ctx['unassigned_shipments'] ?? 0) . " unassigned shipments**\n\n"
                . "What would you like to do?\n"
                . 'SUGGESTIONS:["briefing","stuck shipments","pending payments","all customers","revenue","all drivers","help"]';

        case 'briefing': return cdp_local_briefing($ctx, $currency);
        case 'stuck':    return cdp_local_stuck($ctx, $currency);
        case 'payments': return cdp_local_payments($ctx, $currency);
        case 'drivers':  return cdp_local_drivers($ctx);
        case 'revenue':  return cdp_local_revenue($ctx, $currency);
        case 'customers':return cdp_local_customers($ctx, $currency);
        case 'shipments':return cdp_local_shipments($ctx, $currency);

        case 'create_customer':
            $who = $fname ? " for **".trim("$fname $lname")."**" : '';
            return "I'll open the form to create a new customer$who. Fill in any missing fields and click **Execute Action** to save.";

        case 'create_driver':
            return "I'll open the form to add a new driver. Fill in their details and click **Execute Action**.\nSUGGESTIONS:[\"all drivers\",\"briefing\",\"assign driver\"]";

        case 'create_employee':
            return "I'll open the form to create a new employee/staff account. Fill in the required fields and click **Execute Action**.\nSUGGESTIONS:[\"briefing\",\"all customers\",\"all drivers\"]";

        case 'create_recipient':
            return "I'll open the form to create a new recipient (consignee). Fill in their name, phone and address, then click **Execute Action**.\nSUGGESTIONS:[\"all customers\",\"briefing\"]";

        case 'create_shipment':
            return "I'll open the form to create a new shipment. You'll need the sender ID and recipient name. Click **Execute Action** to proceed.\nSUGGESTIONS:[\"all customers\",\"all drivers\",\"stuck shipments\",\"briefing\"]";

        case 'add_prealert':
            return "I'll open the pre-alert form. You'll need the tracking number and customer ID. Fill in the details and click **Execute Action**.\nSUGGESTIONS:[\"all customers\",\"briefing\",\"stuck shipments\"]";

        case 'schedule_pickup':
            return "I'll open the pickup scheduling form. Provide the customer ID, pickup address and date, then click **Execute Action**.\nSUGGESTIONS:[\"all customers\",\"all drivers\",\"briefing\"]";

        case 'delete_customer':
            return "To delete a customer, I need their Customer ID. Type **DELETE** in the confirm field.\n\n⚠️ Customers with existing shipments cannot be deleted.\nSUGGESTIONS:[\"all customers\",\"update customer\",\"briefing\"]";

        case 'delete_driver':
            return "To delete a driver, I need their Driver ID. Type **DELETE** in the confirm field.\n\n⚠️ Drivers with active shipments cannot be deleted — reassign first.\nSUGGESTIONS:[\"all drivers\",\"assign driver\",\"briefing\"]";

        case 'delete_shipment':
            return "To delete a shipment, I need the Order ID. Use with caution — this permanently removes the record.\nSUGGESTIONS:[\"stuck shipments\",\"cancel shipment\",\"briefing\"]";

        case 'update_customer':
            return "To update a customer, I need their Customer ID and which fields to change (phone, email, or address). I'll open the form.\nSUGGESTIONS:[\"all customers\",\"add new customer\",\"briefing\"]";

        case 'edit_driver':
            return "To edit a driver, I need their Driver ID and which fields to update (name, phone, vehicle). I'll open the form.\nSUGGESTIONS:[\"all drivers\",\"add new driver\",\"briefing\"]";

        case 'reset_customer_password':
            return "To reset a customer's password, I need their Customer ID. Leave new password blank to auto-generate one.\nSUGGESTIONS:[\"all customers\",\"update customer\",\"briefing\"]";

        case 'mark_delivered':
            return "To mark a shipment as delivered, I need the Order ID, name of person who received it, and Driver ID. I'll open the form.\nSUGGESTIONS:[\"stuck shipments\",\"all drivers\",\"briefing\"]";

        case 'add_tracking_note':
            return "To add a tracking note, I need the Order ID, Status ID (4=In Transit, 5=Out for Delivery, 8=Delivered), and a comment. I'll open the form.\nSUGGESTIONS:[\"stuck shipments\",\"mark delivered\",\"briefing\"]";

        case 'cancel_shipment':
            return "To cancel a shipment, I need the Order ID and a reason. I'll open the cancellation form.\nSUGGESTIONS:[\"stuck shipments\",\"pending payments\",\"briefing\"]";

        case 'assign_driver':
            return cdp_local_drivers($ctx) . "\n\nI'll open the driver assignment form. Provide the Order ID and Driver ID.\nSUGGESTIONS:[\"bulk assign driver\",\"stuck shipments\",\"briefing\"]";

        case 'bulk_assign_driver':
            return "To assign one driver to multiple shipments, provide the Order IDs (comma-separated) and Driver ID. I'll open the form.\nSUGGESTIONS:[\"all drivers\",\"stuck shipments\",\"briefing\"]";

        case 'bulk_update_status':
            return "To update status on multiple shipments, provide the Order IDs (comma-separated) and Status ID (4=In Transit, 8=Delivered). I'll open the form.\nSUGGESTIONS:[\"stuck shipments\",\"assign driver\",\"briefing\"]";

        case 'accept_pickup':
            return "To accept a pickup request, I need the Pickup Order ID and optionally a Driver ID. I'll open the form.\nSUGGESTIONS:[\"cancel pickup\",\"schedule pickup\",\"all drivers\",\"briefing\"]";

        case 'cancel_pickup':
            return "To cancel a pickup, I need the Pickup Order ID and an optional reason. I'll open the form.\nSUGGESTIONS:[\"accept pickup\",\"schedule pickup\",\"briefing\"]";

        case 'confirm_payment':
            return cdp_local_payments($ctx, $currency);

        case 'record_payment':
            return "To record a payment, I need the Order ID, amount paid, and payment method ID. I'll open the form.\nSUGGESTIONS:[\"pending payments\",\"all customers\",\"briefing\"]";

        case 'refund_payment':
            return "To process a refund, I need the Order ID, refund amount, and reason. I'll open the refund form.\nSUGGESTIONS:[\"pending payments\",\"all customers\",\"briefing\"]";

        case 'apply_discount':
            return "To apply a discount, I need the Order ID and discount amount. I'll open the discount form.\nSUGGESTIONS:[\"pending payments\",\"all customers\",\"briefing\"]";

        case 'send_sms':
            return "To send an SMS, I need the phone number and message text. I'll open the SMS form.\nSUGGESTIONS:[\"all customers\",\"briefing\"]";

        case 'send_email':
            return "To send an email, I need the recipient email, subject, and message. I'll open the email form.\nSUGGESTIONS:[\"all customers\",\"briefing\"]";

        case 'send_whatsapp':
            return "To send a WhatsApp message, I need the phone number and message. I'll open the WhatsApp form.\nSUGGESTIONS:[\"all customers\",\"briefing\"]";

        case 'reports':
            return "I can generate these reports — click one:\n\nSUGGESTIONS:[\"payments report\",\"driver report\",\"customer balance report\",\"general report\",\"packages report\",\"pickup report\"]";

        case 'prealerts':
            $pending = $ctx['pending_prealerts'] ?? 0;
            return "There are **$pending pending pre-alert(s)**.\nSUGGESTIONS:[\"add prealert\",\"delete prealert\",\"briefing\",\"all customers\"]";

        case 'pickups':
            return "I can help with pickups:\nSUGGESTIONS:[\"schedule pickup\",\"accept pickup\",\"cancel pickup\",\"pickup report\",\"briefing\"]";

        case 'help':
            return cdp_local_help();

        default:
            return cdp_local_unknown($message, $ctx, $currency);
    }
}

// -------------------------------------------------------------------
// DATA HELPERS
// -------------------------------------------------------------------
function cdp_local_briefing(array $ctx, string $currency): string {
    $stuck  = count($ctx['stuck_shipments'] ?? []);
    $pay    = count($ctx['pending_payments'] ?? []);
    $over   = count($ctx['overdue_invoices'] ?? []);
    $unassg = $ctx['unassigned_shipments'] ?? 0;
    $pre    = $ctx['pending_prealerts'] ?? 0;
    $rev    = number_format($ctx['revenue_this_month'] ?? 0, 2);
    $revL   = number_format($ctx['revenue_last_month'] ?? 0, 2);
    $diff   = ($ctx['revenue_this_month'] ?? 0) - ($ctx['revenue_last_month'] ?? 0);
    $trend  = $diff >= 0 ? "📈 +$currency".number_format(abs($diff),2)." vs last month" : "📉 -$currency".number_format(abs($diff),2)." vs last month";
    $new24h = $ctx['new_shipments_24h'] ?? 0;
    $newCust= $ctx['new_customers_week'] ?? 0;

    $r  = "**System Briefing** — ".date('D, d M Y H:i')."\n\n";
    $r .= "**Last 24h:** $new24h new shipments | ".($ctx['payments_received_24h']??0)." payments | ".($ctx['cancellations_24h']??0)." cancellations\n\n";
    $r .= "**Operations**\n";
    $r .= "- Stuck: **$stuck**".($stuck>0?" ⚠️":" ✅")."\n";
    $r .= "- Unassigned: **$unassg**".($unassg>0?" ⚠️":" ✅")."\n";
    $r .= "- Pending payments: **$pay** | Overdue: **$over**".($over>0?" 🔴":" ✅")."\n";
    $r .= "- Pending pre-alerts: **$pre**\n\n";
    $r .= "**Revenue**\n- This month: **$currency $rev** ($trend)\n- Last month: **$currency $revL**\n\n";
    $r .= "**Customers:** ".($ctx['total_customers']??0)." total | **$newCust** new this week\n";
    if (!empty($ctx['top_customers'])) {
        $r .= "\n**Top Customers This Month**\n";
        foreach (array_slice($ctx['top_customers'],0,3) as $c)
            $r .= "- ".$c['name'].": ".$c['shipments']." shipments, $currency ".number_format($c['revenue'],2)."\n";
    }

    // Dynamic chips based on what needs attention
    $chips = ['"briefing"'];
    if ($stuck > 0)  $chips[] = '"stuck shipments"';
    if ($pay > 0)    $chips[] = '"pending payments"';
    if ($unassg > 0) $chips[] = '"assign driver"';
    $chips[] = '"all customers"';
    $chips[] = '"revenue"';
    $chips[] = '"all drivers"';
    $r .= "\nSUGGESTIONS:[" . implode(',', $chips) . "]";
    return $r;
}

function cdp_local_stuck(array $ctx, string $currency): string {
    $stuck = $ctx['stuck_shipments'] ?? [];
    if (empty($stuck)) return "✅ No stuck shipments right now. All shipments are moving normally.";
    $r = "**".count($stuck)." Stuck Shipment(s):**\n\n";
    foreach ($stuck as $s) {
        $days = round(($s['hours_stuck']??0)/24,1);
        $r .= "- **".$s['tracking']."** — $days days | ".$s['customer']." | Driver: ".$s['driver']." | $currency ".number_format($s['value'],2)."\n";
    }
    $r .= "\nWould you like to update the status or reassign drivers?";
    $r .= "\nSUGGESTIONS:[\"update status\",\"assign driver\",\"briefing\",\"pending payments\"]";
    return $r;
}

function cdp_local_payments(array $ctx, string $currency): string {
    $pending = $ctx['pending_payments'] ?? [];
    $overdue = $ctx['overdue_invoices'] ?? [];
    if (empty($pending) && empty($overdue)) return "✅ No pending or overdue payments right now.";
    $r = "";
    if (!empty($overdue)) {
        $tot = array_sum(array_column($overdue,'amount'));
        $r .= "**".count($overdue)." Overdue Invoice(s)** — Total: $currency ".number_format($tot,2)."\n";
        foreach (array_slice($overdue,0,5) as $o)
            $r .= "- **".$o['tracking']."** — ".$o['customer']." | $currency ".number_format($o['amount'],2)." | ".$o['days_overdue']." days\n";
        $r .= "\n";
    }
    if (!empty($pending)) {
        $tot = array_sum(array_column($pending,'amount'));
        $r .= "**".count($pending)." Pending Payment(s)** — Total: $currency ".number_format($tot,2)."\n";
        foreach (array_slice($pending,0,5) as $p)
            $r .= "- **".$p['tracking']."** — ".$p['customer']." | $currency ".number_format($p['amount'],2)."\n";
    }
    $r .= "\nClick a **Confirm Payment** button below to process, or say which one to confirm.";
    $r .= "\nSUGGESTIONS:[\"briefing\",\"stuck shipments\",\"all customers\",\"revenue\"]";
    return $r;
}

function cdp_local_drivers(array $ctx): string {
    $drivers = $ctx['drivers'] ?? [];
    if (empty($drivers)) return "No drivers found in the system.\nSUGGESTIONS:[\"add new driver\",\"briefing\",\"all customers\"]";
    $unassg = $ctx['unassigned_shipments'] ?? 0;
    $r = "**Driver Workload** — $unassg unassigned shipment(s)\n\n";
    foreach ($drivers as $d) {
        $icon = $d['active_shipments'] <= 2 ? "🟢" : ($d['active_shipments'] <= 5 ? "🟡" : "🔴");
        $r .= "$icon **".$d['name']."** (ID: ".$d['driver_id'].") — ".$d['active_shipments']." active\n";
    }
    if ($unassg > 0) {
        usort($drivers, fn($a,$b) => $a['active_shipments'] - $b['active_shipments']);
        $r .= "\n💡 Best available: **".$drivers[0]['name']."** — ".$drivers[0]['active_shipments']." active shipments.";
    }
    $r .= "\nSUGGESTIONS:[\"assign driver\",\"bulk assign driver\",\"add new driver\",\"stuck shipments\",\"briefing\"]";
    return $r;
}

function cdp_local_revenue(array $ctx, string $currency): string {
    $tm   = $ctx['revenue_this_month'] ?? 0;
    $lm   = $ctx['revenue_last_month'] ?? 0;
    $diff = $tm - $lm;
    $pct  = $lm > 0 ? round(abs($diff)/$lm*100,1) : 0;
    $r  = "**Revenue Analysis**\n\n";
    $r .= "- This month: **$currency ".number_format($tm,2)."**\n";
    $r .= "- Last month: **$currency ".number_format($lm,2)."**\n";
    $r .= "- Trend: ".($diff>=0?"📈 +$pct%":"📉 -$pct%")." vs last month\n";
    if (!empty($ctx['top_customers'])) {
        $r .= "\n**Top Customers**\n";
        foreach ($ctx['top_customers'] as $i => $c)
            $r .= ($i+1).". **".$c['name']."** — ".$c['shipments']." shipments, $currency ".number_format($c['revenue'],2)."\n";
    }
    $r .= "\nSUGGESTIONS:[\"briefing\",\"all customers\",\"pending payments\",\"all drivers\",\"reports\"]";
    return $r;
}

function cdp_local_customers(array $ctx, string $currency): string {
    $customers = $ctx['all_customers'] ?? [];
    $total     = $ctx['total_customers'] ?? 0;
    if (empty($customers)) return "No customers yet.\nSUGGESTIONS:[\"add new customer\",\"briefing\",\"all drivers\"]";
    $r = "**All Customers** ($total total)\n\n";
    foreach (array_slice($customers,0,25) as $i => $c) {
        $r .= ($i+1).". **".$c['name']."**";
        if ($c['email']) $r .= " — ".$c['email'];
        if ($c['phone']) $r .= " | ".$c['phone'];
        if ($c['total_shipments']>0) $r .= " | ".$c['total_shipments']." shipments";
        $r .= "\n";
    }
    if ($total > 25) $r .= "\n... and ".($total-25)." more. Ask about a specific customer for details.\n";
    $r .= "\nSay **\"add new customer\"** to create one, or ask about a specific customer.";
    $r .= "\nSUGGESTIONS:[\"add new customer\",\"briefing\",\"pending payments\",\"all drivers\"]";
    return $r;
}

function cdp_local_shipments(array $ctx, string $currency): string {
    $stuck  = count($ctx['stuck_shipments'] ?? []);
    $unassg = $ctx['unassigned_shipments'] ?? 0;
    $new24h = $ctx['new_shipments_24h'] ?? 0;
    $r  = "**Shipment Overview**\n- New (24h): **$new24h** | Stuck: **$stuck** | Unassigned: **$unassg**\n\n";
    if ($stuck > 0) {
        $r .= "**Stuck Shipments:**\n";
        foreach (array_slice($ctx['stuck_shipments'],0,5) as $s)
            $r .= "- **".$s['tracking']."** — ".$s['customer']." (".round(($s['hours_stuck']??0)/24,1)." days)\n";
        $r .= "\n";
    }
    $r .= "What would you like to do?\n";
    $r .= 'SUGGESTIONS:["mark delivered","update status","assign driver","cancel shipment","stuck shipments","bulk update status","briefing"]';
    return $r;
}

function cdp_local_help(): string {
    $chips = 'SUGGESTIONS:["briefing","stuck shipments","pending payments","all customers","all drivers","revenue","add new customer","add new driver","add new employee","schedule pickup","add prealert","reports","help"]';
    return "**What I can do — click any suggestion below:**\n\n$chips\n\n"
        . "**📦 Shipments:** mark delivered · update status · assign driver · cancel · bulk update\n"
        . "**👥 People:** create/update/delete customers, drivers, employees, recipients\n"
        . "**💰 Finance:** confirm payments · record payment · refund · discount\n"
        . "**📬 Communication:** send SMS · email · WhatsApp\n"
        . "**📊 Reports:** general · payments · drivers · customer balance · packages";
}

function cdp_local_unknown(string $message, array $ctx, string $currency): string {
    // Try to match a tracking number in the message
    if (preg_match('/\b([A-Z]{2,6}\d{4,10})\b/i', $message, $m)) {
        $tracking = strtoupper($m[1]);
        foreach ($ctx['stuck_shipments'] ?? [] as $s) {
            if (stripos($s['tracking'], $tracking) !== false)
                return "**Shipment $tracking:**\n- Customer: ".$s['customer']."\n- Driver: ".$s['driver']."\n- Status: Stuck ".round(($s['hours_stuck']??0)/24,1)." days\n- Value: $currency ".number_format($s['value'],2);
        }
        return "Shipment **$tracking** is not in the stuck shipments list — it may be delivered or processing normally.";
    }
    // Try customer name match
    foreach ($ctx['all_customers'] ?? [] as $c) {
        $first = explode(' ', $c['name'])[0] ?? '';
        if (strlen($first) > 2 && stripos($message, $first) !== false)
            return "**Customer: ".$c['name']."**\n- Email: ".($c['email']?:'N/A')."\n- Phone: ".($c['phone']?:'N/A')."\n- Shipments: ".$c['total_shipments']."\n- Total spent: $currency ".number_format($c['total_spent'],2);
    }

    $stuck = count($ctx['stuck_shipments'] ?? []);
    $pay   = count($ctx['pending_payments'] ?? []);

    $suggestions = ['"briefing"', '"stuck shipments"', '"pending payments"', '"all customers"', '"add new customer"', '"add new driver"', '"help"'];
    if ($stuck > 0) array_unshift($suggestions, '"stuck shipments"');
    if ($pay > 0)   array_unshift($suggestions, '"pending payments"');
    $suggestions = array_unique($suggestions);

    $chips = 'SUGGESTIONS:[' . implode(',', $suggestions) . ']';

    return "I'm running in **local mode** (no API credits). Click a suggestion below or type your question:\n\n$chips";
}

// -------------------------------------------------------------------
// ACTION GENERATOR — returns quick action buttons from context
// -------------------------------------------------------------------
function cdp_local_ai_actions(string $message, array $ctx, $perms): array {
    $actions = [];
    $intent  = cdp_detect_intent($message);

    // Extract data from message
    $fname = $lname = $email = $phone = '';
    if (preg_match('/called?\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)?)/i', $message, $m)) {
        $parts = explode(' ', trim($m[1])); $fname = $parts[0]??''; $lname = $parts[1]??'';
    }
    if (preg_match('/[\w.+-]+@[\w-]+\.[a-zA-Z]{2,}/', $message, $m)) $email = $m[0];
    if (preg_match('/\+?\d[\d\s\-]{8,14}\d/', $message, $m)) $phone = preg_replace('/\s+/','',$m[0]);

    // Always open modal for creation intents
    if ($intent === 'create_customer') {
        $actions[] = ['action'=>'create_customer','label'=>'Create Customer'.($fname?" — $fname $lname":''),'fname'=>$fname,'lname'=>$lname,'email'=>$email,'phone'=>$phone];
    }
    if ($intent === 'create_driver') {
        $actions[] = ['action'=>'create_driver','label'=>'Create Driver'.($fname?" — $fname $lname":''),'fname'=>$fname,'lname'=>$lname,'email'=>$email,'phone'=>$phone,'vehicle'=>''];
    }
    if ($intent === 'create_employee') {
        $actions[] = ['action'=>'create_employee','label'=>'Create Employee'.($fname?" — $fname $lname":''),'fname'=>$fname,'lname'=>$lname,'email'=>$email,'phone'=>$phone,'username'=>''];
    }
    if ($intent === 'create_recipient') {
        $actions[] = ['action'=>'create_recipient','label'=>'Create Recipient'.($fname?" — $fname $lname":''),'fname'=>$fname,'lname'=>$lname,'phone'=>$phone,'email'=>$email,'address'=>''];
    }
    if ($intent === 'create_shipment') {
        $actions[] = ['action'=>'create_shipment','label'=>'Create Shipment','sender_id'=>0,'recipient_name'=>'','recipient_addr'=>''];
    }
    if ($intent === 'add_prealert') {
        $actions[] = ['action'=>'add_prealert','label'=>'Add Pre-Alert','tracking'=>'','customer_id'=>0,'description'=>'','weight'=>''];
    }
    if ($intent === 'schedule_pickup') {
        $actions[] = ['action'=>'schedule_pickup','label'=>'Schedule Pickup','customer_id'=>0,'address'=>'','date'=>date('Y-m-d'),'notes'=>''];
    }
    if ($intent === 'mark_delivered') {
        $actions[] = ['action'=>'mark_delivered','label'=>'Mark Delivered','order_id'=>0,'person_receives'=>'','driver_id'=>0,'comment'=>''];
    }
    if ($intent === 'add_tracking_note') {
        $actions[] = ['action'=>'add_tracking_note','label'=>'Add Tracking Note','order_id'=>0,'status_id'=>4,'comment'=>''];
    }
    if ($intent === 'cancel_shipment') {
        $actions[] = ['action'=>'cancel_shipment','label'=>'Cancel Shipment','order_id'=>0,'reason'=>''];
    }
    if ($intent === 'assign_driver') {
        $actions[] = ['action'=>'assign_driver','label'=>'Assign Driver','order_id'=>0,'driver_id'=>0];
    }
    if ($intent === 'bulk_assign_driver') {
        $actions[] = ['action'=>'bulk_assign_driver','label'=>'Bulk Assign Driver','order_ids'=>'','driver_id'=>0];
    }
    if ($intent === 'bulk_update_status') {
        $actions[] = ['action'=>'bulk_update_status','label'=>'Bulk Update Status','order_ids'=>'','status_id'=>4];
    }
    if ($intent === 'record_payment') {
        $actions[] = ['action'=>'record_payment','label'=>'Record Payment','order_id'=>0,'amount'=>0,'payment_type'=>1,'notes'=>''];
    }
    if ($intent === 'refund_payment') {
        $actions[] = ['action'=>'refund_payment','label'=>'Process Refund','order_id'=>0,'amount'=>0,'reason'=>''];
    }
    if ($intent === 'apply_discount') {
        $actions[] = ['action'=>'apply_discount','label'=>'Apply Discount','order_id'=>0,'discount'=>0];
    }
    if ($intent === 'send_sms') {
        $actions[] = ['action'=>'send_sms','label'=>'Send SMS','phone'=>$phone,'message'=>''];
    }
    if ($intent === 'send_email') {
        $actions[] = ['action'=>'send_email','label'=>'Send Email','email'=>$email,'subject'=>'','message'=>''];
    }
    if ($intent === 'send_whatsapp') {
        $actions[] = ['action'=>'send_whatsapp','label'=>'Send WhatsApp','phone'=>$phone,'message'=>''];
    }
    if ($intent === 'accept_pickup') {
        $actions[] = ['action'=>'accept_pickup','label'=>'Accept Pickup','pickup_id'=>0,'driver_id'=>0];
    }
    if ($intent === 'cancel_pickup') {
        $actions[] = ['action'=>'cancel_pickup','label'=>'Cancel Pickup','pickup_id'=>0,'reason'=>''];
    }
    if ($intent === 'delete_customer') {
        $actions[] = ['action'=>'delete_customer','label'=>'Delete Customer','customer_id'=>0,'confirm'=>''];
    }
    if ($intent === 'delete_driver') {
        $actions[] = ['action'=>'delete_driver','label'=>'Delete Driver','driver_id'=>0,'confirm'=>''];
    }
    if ($intent === 'delete_shipment') {
        $actions[] = ['action'=>'delete_shipment','label'=>'Delete Shipment','order_id'=>0];
    }
    if ($intent === 'update_customer') {
        $actions[] = ['action'=>'update_customer','label'=>'Update Customer','customer_id'=>0,'phone'=>$phone,'email'=>$email,'address'=>''];
    }
    if ($intent === 'edit_driver') {
        $actions[] = ['action'=>'edit_driver','label'=>'Edit Driver','driver_id'=>0,'fname'=>'','lname'=>'','phone'=>'','vehicle'=>''];
    }
    if ($intent === 'reset_customer_password') {
        $actions[] = ['action'=>'reset_customer_password','label'=>'Reset Password','customer_id'=>0,'new_password'=>''];
    }

    // Confirm payment buttons for payment intents
    if (in_array($intent, ['payments','confirm_payment','briefing']) && $perms->canConfirmPayments()) {
        foreach (array_slice($ctx['pending_payments'] ?? [], 0, 3) as $p) {
            $actions[] = ['action'=>'confirm_payment','label'=>'Confirm — '.$p['tracking'],'order_id'=>$p['order_id'],'order_type'=>'courier','description'=>'Confirm payment for '.$p['tracking']];
        }
        if (count($ctx['overdue_invoices'] ?? []) >= 2) {
            $actions[] = ['action'=>'confirm_all_wire_payments','label'=>'Confirm All Overdue ('.count($ctx['overdue_invoices']).')','description'=>'Mark all overdue invoices paid'];
        }
    }

    // Status update buttons for stuck shipments
    if (in_array($intent, ['stuck','briefing','shipments']) && $perms->canUpdateStatus()) {
        foreach (array_slice($ctx['stuck_shipments'] ?? [], 0, 3) as $s) {
            $actions[] = ['action'=>'update_status','label'=>'Mark In Transit — '.$s['tracking'],'order_id'=>$s['order_id'],'status_id'=>4,'order_type'=>'courier','description'=>'Update '.$s['tracking'].' to In Transit'];
        }
    }

    return $actions;
}
