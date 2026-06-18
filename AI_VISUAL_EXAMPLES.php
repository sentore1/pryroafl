<?php
/**
 * AI Visual Elements - Example Implementations
 * 
 * This file shows how to modify ai_chat_ajax.php to include visual elements
 * in AI responses.
 */

// ============================================================================
// EXAMPLE 1: System Status with Stat Cards
// ============================================================================

function example_system_status_with_cards() {
    // Get your data from database
    $stuck_count = 7;
    $unassigned_count = 3;
    $overdue_count = 5;
    $delivered_today = 24;
    
    // Build visual response
    $visual_cards = [
        'stats' => [
            [
                'icon' => '⚠️',
                'value' => $stuck_count,
                'label' => 'Stuck Shipments',
                'color' => '#dc3545',
                'action' => "cdp_quickAction('stuck')",
                'actionLabel' => 'View All'
            ],
            [
                'icon' => '📦',
                'value' => $unassigned_count,
                'label' => 'Unassigned',
                'color' => '#ffc107',
                'action' => "alert('Assign drivers')",
                'actionLabel' => 'Assign'
            ],
            [
                'icon' => '💰',
                'value' => $overdue_count,
                'label' => 'Overdue Payments',
                'color' => '#28a745',
                'action' => "cdp_quickAction('payments')",
                'actionLabel' => 'Process'
            ],
            [
                'icon' => '✅',
                'value' => $delivered_today,
                'label' => 'Delivered Today',
                'color' => '#0d6efd'
            ]
        ]
    ];
    
    $reply = "Here's your system overview:\n\n";
    $reply .= "VISUAL_CARDS:" . json_encode($visual_cards) . "\n\n";
    $reply .= "You have " . $stuck_count . " stuck shipments that need attention, ";
    $reply .= $unassigned_count . " unassigned shipments, and ";
    $reply .= $overdue_count . " overdue payments. ";
    $reply .= "Great job on " . $delivered_today . " deliveries today!";
    
    return $reply;
}

// ============================================================================
// EXAMPLE 2: Revenue Trend with Line Chart
// ============================================================================

function example_revenue_trend() {
    // Get revenue data for last 6 months from database
    $revenue_data = [
        ['month' => 'January', 'revenue' => 120000],
        ['month' => 'February', 'revenue' => 135000],
        ['month' => 'March', 'revenue' => 128000],
        ['month' => 'April', 'revenue' => 145000],
        ['month' => 'May', 'revenue' => 150000],
        ['month' => 'June', 'revenue' => 148000],
    ];
    
    // Extract labels and values
    $labels = array_map(function($item) { return substr($item['month'], 0, 3); }, $revenue_data);
    $values = array_map(function($item) { return $item['revenue']; }, $revenue_data);
    
    // Build chart data
    $chart_data = [
        'title' => 'Revenue Trend (Last 6 Months)',
        'label' => 'Revenue (FRw)',
        'labels' => $labels,
        'values' => $values,
        'color' => '#28a745',
        'bgColor' => 'rgba(40, 167, 69, 0.1)'
    ];
    
    $reply = "Your revenue is showing steady growth!\n\n";
    $reply .= "LINE_CHART:" . json_encode($chart_data) . "\n\n";
    
    // Calculate growth
    $growth_rate = (($values[5] - $values[0]) / $values[0]) * 100;
    $reply .= "That's a " . number_format($growth_rate, 1) . "% increase from January to June. ";
    $reply .= "Keep up the excellent work! 🎉";
    
    return $reply;
}

// ============================================================================
// EXAMPLE 3: Driver Workload with Bar Chart
// ============================================================================

function example_driver_workload() {
    // Get driver workload from database
    $drivers = [
        ['name' => 'Mike Johnson', 'active' => 12],
        ['name' => 'Sarah Lee', 'active' => 8],
        ['name' => 'John Doe', 'active' => 3],
        ['name' => 'Emma Brown', 'active' => 15],
        ['name' => 'David Kim', 'active' => 7],
    ];
    
    // Extract data
    $names = array_map(function($d) { return $d['name']; }, $drivers);
    $workload = array_map(function($d) { return $d['active']; }, $drivers);
    
    // Color code by workload (red = high, yellow = medium, green = low)
    $colors = array_map(function($count) {
        if ($count >= 12) return '#dc3545'; // Red - overloaded
        if ($count >= 8) return '#ffc107';  // Yellow - moderate
        return '#28a745';                   // Green - available
    }, $workload);
    
    $chart_data = [
        'title' => 'Active Shipments per Driver',
        'label' => 'Shipments',
        'labels' => $names,
        'values' => $workload,
        'colors' => $colors
    ];
    
    $reply = "Current driver workload distribution:\n\n";
    $reply .= "BAR_CHART:" . json_encode($chart_data) . "\n\n";
    
    // Find overloaded and available drivers
    $overloaded = array_filter($drivers, function($d) { return $d['active'] >= 12; });
    $available = array_filter($drivers, function($d) { return $d['active'] <= 5; });
    
    if (!empty($overloaded)) {
        $reply .= "⚠️ Overloaded: ";
        $reply .= implode(', ', array_map(function($d) { return $d['name']; }, $overloaded));
        $reply .= "\n";
    }
    
    if (!empty($available)) {
        $reply .= "✅ Available: ";
        $reply .= implode(', ', array_map(function($d) { return $d['name']; }, $available));
        $reply .= "\n";
    }
    
    $reply .= "\nConsider redistributing shipments from overloaded drivers.";
    
    return $reply;
}

// ============================================================================
// EXAMPLE 4: Shipment Status with Pie Chart
// ============================================================================

function example_shipment_status_distribution() {
    // Get shipment counts by status
    $status_counts = [
        'Delivered' => 95,
        'In Transit' => 35,
        'Processing' => 18,
        'Stuck' => 7,
        'Pending' => 12
    ];
    
    $chart_data = [
        'title' => 'Shipment Status Distribution',
        'labels' => array_keys($status_counts),
        'values' => array_values($status_counts),
        'colors' => ['#28a745', '#0d6efd', '#ffc107', '#dc3545', '#6c757d']
    ];
    
    $total = array_sum($status_counts);
    $delivered_percent = ($status_counts['Delivered'] / $total) * 100;
    
    $reply = "Current shipment status breakdown:\n\n";
    $reply .= "PIE_CHART:" . json_encode($chart_data) . "\n\n";
    $reply .= "Out of " . $total . " total shipments, ";
    $reply .= number_format($delivered_percent, 1) . "% have been successfully delivered. ";
    $reply .= "You're doing great! 🚀";
    
    return $reply;
}

// ============================================================================
// EXAMPLE 5: Top Customers with Data Table
// ============================================================================

function example_top_customers() {
    // Get top customers from database
    $customers = [
        ['name' => 'John Doe', 'shipments' => 45, 'revenue' => 150000, 'status' => 'Active'],
        ['name' => 'Jane Smith', 'shipments' => 38, 'revenue' => 125000, 'status' => 'Active'],
        ['name' => 'Acme Corp', 'shipments' => 32, 'revenue' => 98000, 'status' => 'Pending'],
        ['name' => 'Tech Solutions', 'shipments' => 28, 'revenue' => 85000, 'status' => 'Active'],
        ['name' => 'Global Trading', 'shipments' => 25, 'revenue' => 78000, 'status' => 'Active'],
    ];
    
    // Build table rows
    $rows = array_map(function($c) {
        $status_icon = $c['status'] === 'Active' ? '✅' : '⚠️';
        return [
            $c['name'],
            $c['shipments'],
            number_format($c['revenue']) . ' FRw',
            $status_icon . ' ' . $c['status']
        ];
    }, $customers);
    
    $table_data = [
        'columns' => ['Customer', 'Shipments', 'Revenue', 'Status'],
        'rows' => $rows
    ];
    
    $reply = "Your top 5 customers this month:\n\n";
    $reply .= "DATA_TABLE:" . json_encode($table_data) . "\n\n";
    $reply .= "These customers generated " . number_format(array_sum(array_column($customers, 'revenue'))) . " FRw in revenue. ";
    $reply .= "Make sure to keep them happy! 😊";
    
    return $reply;
}

// ============================================================================
// EXAMPLE 6: Complete Dashboard (Multiple Visuals)
// ============================================================================

function example_complete_dashboard() {
    // Combine multiple visual elements
    
    $reply = "📊 **Your Complete Operations Dashboard**\n\n";
    
    // 1. Key Metrics Cards
    $cards = [
        'stats' => [
            ['icon' => '📦', 'value' => '127', 'label' => 'Total Shipments', 'color' => '#0d6efd'],
            ['icon' => '✅', 'value' => '95', 'label' => 'Delivered', 'color' => '#28a745'],
            ['icon' => '⚠️', 'value' => '7', 'label' => 'Stuck', 'color' => '#dc3545'],
            ['icon' => '🚛', 'value' => '15', 'label' => 'In Transit', 'color' => '#ffc107']
        ]
    ];
    $reply .= "VISUAL_CARDS:" . json_encode($cards) . "\n\n";
    
    $reply .= "**Performance Overview:**\n\n";
    
    // 2. Revenue Trend
    $revenue_chart = [
        'title' => 'Weekly Revenue Trend',
        'label' => 'FRw',
        'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        'values' => [25000, 32000, 28000, 35000, 38000, 42000, 30000],
        'color' => '#28a745'
    ];
    $reply .= "LINE_CHART:" . json_encode($revenue_chart) . "\n\n";
    
    $reply .= "**Driver Performance:**\n\n";
    
    // 3. Driver Workload
    $driver_chart = [
        'title' => 'Deliveries Completed This Week',
        'label' => 'Deliveries',
        'labels' => ['Mike', 'Sarah', 'John', 'Emma'],
        'values' => [24, 18, 12, 28],
        'colors' => ['#0d6efd', '#6610f2', '#6f42c1', '#d63384']
    ];
    $reply .= "BAR_CHART:" . json_encode($driver_chart) . "\n\n";
    
    $reply .= "Everything is running smoothly! Keep it up! 🎉";
    
    return $reply;
}

// ============================================================================
// HOW TO INTEGRATE INTO ai_chat_ajax.php
// ============================================================================

/*
In your ai_chat_ajax.php, modify the system prompt to include instructions:

$system_prompt = <<<PROMPT
You are Pryro AI...

VISUAL ELEMENTS:
You can include visual elements in your responses using these markers:

1. VISUAL_CARDS:{"stats":[...]} - For metric cards
2. LINE_CHART:{...} - For trends over time
3. BAR_CHART:{...} - For comparisons
4. PIE_CHART:{...} - For distributions
5. DATA_TABLE:{...} - For detailed data

Example response with visuals:
"Your system overview:

VISUAL_CARDS:{"stats":[
    {"icon":"⚠️","value":"7","label":"Stuck Shipments","color":"#dc3545","action":"cdp_quickAction('stuck')","actionLabel":"View All"}
]}

You have 7 stuck shipments that need attention."

Use visuals when showing:
- Metrics and KPIs (use VISUAL_CARDS)
- Trends over time (use LINE_CHART)
- Comparisons (use BAR_CHART)
- Distributions (use PIE_CHART)
- Lists with details (use DATA_TABLE)

Here is the current live system data:
{$context_json}
PROMPT;

Then in your response handling, the visuals will automatically render!
*/

// ============================================================================
// QUICK INTEGRATION EXAMPLE
// ============================================================================

/*
// In ai_chat_ajax.php, AFTER getting the AI response:

$full_reply = $result['choices'][0]['message']['content'];

// If asking about system status, append visual cards
if (stripos($message, 'system status') !== false || stripos($message, 'briefing') !== false) {
    // Add stat cards
    $cards = [
        'stats' => [
            ['icon' => '⚠️', 'value' => count($context['stuck_shipments']), 'label' => 'Stuck', 'color' => '#dc3545'],
            ['icon' => '📦', 'value' => $context['unassigned_shipments'], 'label' => 'Unassigned', 'color' => '#ffc107'],
            ['icon' => '💰', 'value' => count($context['overdue_invoices']), 'label' => 'Overdue', 'color' => '#28a745']
        ]
    ];
    $full_reply .= "\n\nVISUAL_CARDS:" . json_encode($cards);
}

// If asking about revenue, add chart
if (stripos($message, 'revenue') !== false) {
    // Build revenue chart from your data
    $chart = [
        'title' => 'Revenue Trend',
        'label' => 'FRw',
        'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        'values' => [/* your revenue data */],
        'color' => '#28a745'
    ];
    $full_reply .= "\n\nLINE_CHART:" . json_encode($chart);
}
*/

?>
