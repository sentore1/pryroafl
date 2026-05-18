# CBM Implementation - Remaining Phases Guide

## Overview

This guide provides complete instructions and code snippets for implementing the remaining optional phases of the CBM feature.

**Current Status:** 90% Complete  
**Remaining Work:** 3 Optional Phases

---

## Phase 7: View/List Pages (2-3 hours)

### 7.1 View Pages - Add CBM Display

**Files to Update (3 files):**
1. `views/consolidate/consolidate_view.php`
2. `views/courier/courier_view.php`
3. `views/customer_packages/customer_packages_view.php`

**Location:** Add after the packaging/delivery time section (around line 315-320)

**Code to Add:**

```php
<div class=" col-sm-12 col-md-4 mb-2">
    <div class="">
        <h5> &nbsp;<b>Total CBM:</b></h5>
        <p class="text-muted  m-l-5"><?php echo number_format($row_order->total_cbm, 4); ?> m³</p>
    </div>
</div>
```

**Step-by-Step:**

1. Open `views/consolidate/consolidate_view.php`
2. Find the section with packaging and delivery time (around line 314-320)
3. Add the CBM display code after the existing columns
4. Repeat for `views/courier/courier_view.php`
5. Repeat for `views/customer_packages/customer_packages_view.php`

---

### 7.2 List Pages - Add CBM Column

**Files to Update (2 files):**
1. `views/consolidate/consolidate_list.php`
2. `views/courier/courier_list.php`

**Changes Required:**

#### A. Add Column Header

Find the table header section (usually has `<thead>` tag):

```php
<th><?php echo $lang['left232'] ?></th> <!-- Weight -->
<th>CBM (m³)</th> <!-- NEW COLUMN -->
<th><?php echo $lang['left506'] ?></th> <!-- Status -->
```

#### B. Add Column Data

Find the table body section where data is displayed:

```php
<td><?php echo $row->total_weight; ?></td>
<td><?php echo number_format($row->total_cbm, 4); ?></td> <!-- NEW COLUMN -->
<td><span class="label" style="background-color: <?php echo $row->color; ?>">
    <?php echo $row->mod_style; ?></span></td>
```

#### C. Update AJAX File (if using AJAX for list)

**For Consolidate:**
File: `ajax/consolidate/consolidate_list_ajax.php`

Add `total_cbm` to the SELECT query:

```php
$db->cdp_query("
    SELECT 
        c.*,
        s.mod_style,
        s.color,
        c.total_cbm  -- ADD THIS
    FROM cdb_consolidate c
    LEFT JOIN cdb_styles s ON c.status_courier = s.id
    WHERE ...
");
```

Add CBM column to the output:

```php
<td><?php echo $row->total_weight; ?></td>
<td><?php echo number_format($row->total_cbm, 4); ?></td>
<td>...</td>
```

**For Courier:**
File: `ajax/courier/courier_list_ajax.php`

Same pattern - add `total_cbm` to SELECT and display in table.

---

## Phase 8: Reports (3-4 hours)

### 8.1 Add CBM to Existing Reports

**Files to Update:**
- `views/reports/shipments/report_general.php`
- `views/reports/shipments/report_general_print.php`
- `views/reports/consolidate/report_consolidate.php` (if exists)

**Changes:**

#### A. Add CBM Column to Report Query

```php
$db->cdp_query("
    SELECT 
        o.*,
        o.total_cbm,  -- ADD THIS
        s.mod_style,
        u.fname,
        u.lname
    FROM cdb_add_order o
    LEFT JOIN cdb_styles s ON o.status_courier = s.id
    LEFT JOIN cdb_users u ON o.sender_id = u.id
    WHERE o.order_datetime BETWEEN :start_date AND :end_date
");
```

#### B. Add CBM Column to Report Table

```php
<thead>
    <tr>
        <th>Tracking</th>
        <th>Date</th>
        <th>Weight</th>
        <th>CBM (m³)</th> <!-- NEW -->
        <th>Amount</th>
        <th>Status</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($orders as $row) { ?>
    <tr>
        <td><?php echo $row->order_prefix . $row->order_no; ?></td>
        <td><?php echo date('Y-m-d', strtotime($row->order_datetime)); ?></td>
        <td><?php echo $row->total_weight; ?></td>
        <td><?php echo number_format($row->total_cbm, 4); ?></td> <!-- NEW -->
        <td><?php echo $row->total_order; ?></td>
        <td><?php echo $row->mod_style; ?></td>
    </tr>
    <?php } ?>
</tbody>
```

#### C. Add CBM Summary

```php
<tfoot>
    <tr>
        <td colspan="2"><strong>Totals:</strong></td>
        <td><strong><?php echo number_format($total_weight, 2); ?></strong></td>
        <td><strong><?php echo number_format($total_cbm, 4); ?> m³</strong></td> <!-- NEW -->
        <td><strong><?php echo number_format($total_amount, 2); ?></strong></td>
        <td></td>
    </tr>
</tfoot>
```

---

### 8.2 Create CBM Utilization Report

**New File:** `views/reports/cbm/report_cbm_utilization.php`

```php
<?php
require_once('helpers/querys.php');

$userData = $user->cdp_getUserData();

// Get date range from POST or default to current month
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-01');
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-t');

// Query consolidate containers with CBM data
$db->cdp_query("
    SELECT 
        c.consolidate_id,
        c.c_prefix,
        c.c_no,
        c.c_date,
        c.total_cbm,
        c.max_cbm_capacity,
        c.cbm_utilization_percent,
        s.mod_style,
        s.color
    FROM cdb_consolidate c
    LEFT JOIN cdb_styles s ON c.status_courier = s.id
    WHERE c.c_date BETWEEN :start_date AND :end_date
    ORDER BY c.c_date DESC
");

$db->bind(':start_date', $start_date);
$db->bind(':end_date', $end_date);
$containers = $db->cdp_registros();

// Calculate totals
$total_cbm_used = 0;
$total_cbm_capacity = 0;
$container_count = 0;

foreach ($containers as $container) {
    $total_cbm_used += $container->total_cbm;
    $total_cbm_capacity += $container->max_cbm_capacity;
    $container_count++;
}

$avg_utilization = $container_count > 0 ? ($total_cbm_used / $total_cbm_capacity) * 100 : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>CBM Utilization Report</title>
    <?php include 'views/inc/head_scripts.php'; ?>
</head>
<body>
    <?php include 'views/inc/preloader.php'; ?>
    <div id="main-wrapper">
        <?php include 'views/inc/topbar.php'; ?>
        <?php include 'views/inc/left_sidebar.php'; ?>
        
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">CBM Utilization Report</h4>
                                
                                <!-- Date Filter Form -->
                                <form method="POST" class="mb-4">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Start Date:</label>
                                            <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label>End Date:</label>
                                            <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-primary form-control">Generate Report</button>
                                        </div>
                                    </div>
                                </form>
                                
                                <!-- Summary Cards -->
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body">
                                                <h5>Total Containers</h5>
                                                <h2><?php echo $container_count; ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body">
                                                <h5>Total CBM Used</h5>
                                                <h2><?php echo number_format($total_cbm_used, 2); ?> m³</h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body">
                                                <h5>Total Capacity</h5>
                                                <h2><?php echo number_format($total_cbm_capacity, 2); ?> m³</h2>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body">
                                                <h5>Avg Utilization</h5>
                                                <h2><?php echo number_format($avg_utilization, 1); ?>%</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Data Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Container</th>
                                                <th>Date</th>
                                                <th>CBM Used</th>
                                                <th>Capacity</th>
                                                <th>Utilization %</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($containers as $row) { ?>
                                            <tr>
                                                <td><?php echo $row->c_prefix . $row->c_no; ?></td>
                                                <td><?php echo date('Y-m-d', strtotime($row->c_date)); ?></td>
                                                <td><?php echo number_format($row->total_cbm, 4); ?> m³</td>
                                                <td><?php echo number_format($row->max_cbm_capacity, 2); ?> m³</td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar <?php echo $row->cbm_utilization_percent > 90 ? 'bg-danger' : ($row->cbm_utilization_percent > 70 ? 'bg-warning' : 'bg-success'); ?>" 
                                                             style="width: <?php echo $row->cbm_utilization_percent; ?>%">
                                                            <?php echo number_format($row->cbm_utilization_percent, 1); ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="label" style="background-color: <?php echo $row->color; ?>"><?php echo $row->mod_style; ?></span></td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'views/inc/footer.php'; ?>
</body>
</html>
```

---

## Phase 9: Settings Page (4-6 hours)

### 9.1 Create CBM Configuration Page

**New File:** `views/config/config_cbm.php`

```php
<?php
require_once('helpers/querys.php');

$userData = $user->cdp_getUserData();

if (!$user->cdp_is_Admin()) {
    cdp_redirect_to("login.php");
}

// Handle form submission
if (isset($_POST['save_cbm_settings'])) {
    $db->cdp_query("
        UPDATE cdb_settings 
        SET 
            cbm_calculation_enabled = :enabled,
            cbm_rate_per_cubic_meter = :rate,
            cbm_vs_weight_priority = :priority
        WHERE id = 1
    ");
    
    $db->bind(':enabled', isset($_POST['cbm_enabled']) ? 1 : 0);
    $db->bind(':rate', floatval($_POST['cbm_rate']));
    $db->bind(':priority', $_POST['cbm_priority']);
    
    if ($db->cdp_execute()) {
        $success_message = "CBM settings saved successfully!";
    } else {
        $error_message = "Error saving settings.";
    }
}

// Get current settings
$db->cdp_query("SELECT * FROM cdb_settings WHERE id = 1");
$settings = $db->cdp_registro();

// Get pricing tiers
$db->cdp_query("SELECT * FROM cdb_cbm_pricing_tiers ORDER BY min_cbm ASC");
$pricing_tiers = $db->cdp_registros();
?>

<!DOCTYPE html>
<html>
<head>
    <title>CBM Configuration</title>
    <?php include 'views/inc/head_scripts.php'; ?>
</head>
<body>
    <?php include 'views/inc/preloader.php'; ?>
    <div id="main-wrapper">
        <?php include 'views/inc/topbar.php'; ?>
        <?php include 'views/inc/left_sidebar.php'; ?>
        
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">CBM Configuration</h4>
                                
                                <?php if (isset($success_message)) { ?>
                                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                                <?php } ?>
                                
                                <?php if (isset($error_message)) { ?>
                                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                                <?php } ?>
                                
                                <form method="POST">
                                    <!-- General Settings -->
                                    <h5 class="mt-4">General Settings</h5>
                                    <hr>
                                    
                                    <div class="form-group">
                                        <label class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="cbm_enabled" value="1" <?php echo $settings->cbm_calculation_enabled ? 'checked' : ''; ?>>
                                            <span class="custom-control-label">Enable CBM Calculations</span>
                                        </label>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Default CBM Rate (per cubic meter):</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" step="0.01" class="form-control" name="cbm_rate" value="<?php echo $settings->cbm_rate_per_cubic_meter; ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Charge Priority:</label>
                                        <select class="form-control" name="cbm_priority" required>
                                            <option value="weight" <?php echo $settings->cbm_vs_weight_priority == 'weight' ? 'selected' : ''; ?>>Always use Weight</option>
                                            <option value="cbm" <?php echo $settings->cbm_vs_weight_priority == 'cbm' ? 'selected' : ''; ?>>Always use CBM</option>
                                            <option value="higher" <?php echo $settings->cbm_vs_weight_priority == 'higher' ? 'selected' : ''; ?>>Use Higher Charge</option>
                                        </select>
                                        <small class="form-text text-muted">Determines whether to charge based on weight, CBM, or whichever is higher.</small>
                                    </div>
                                    
                                    <!-- Container Capacities -->
                                    <h5 class="mt-4">Standard Container Capacities</h5>
                                    <hr>
                                    
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>20ft Container:</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" class="form-control" value="33.00" readonly>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">m³</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>40ft Container:</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" class="form-control" value="67.00" readonly>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">m³</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>40ft HC:</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" class="form-control" value="76.00" readonly>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">m³</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>45ft HC:</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" class="form-control" value="86.00" readonly>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">m³</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" name="save_cbm_settings" class="btn btn-success">Save Settings</button>
                                </form>
                                
                                <!-- Pricing Tiers -->
                                <h5 class="mt-5">CBM Pricing Tiers</h5>
                                <hr>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped">
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
                                        <tbody>
                                            <?php foreach ($pricing_tiers as $tier) { ?>
                                            <tr>
                                                <td><?php echo $tier->tier_name; ?></td>
                                                <td><?php echo number_format($tier->min_cbm, 4); ?> m³</td>
                                                <td><?php echo $tier->max_cbm > 0 ? number_format($tier->max_cbm, 4) . ' m³' : 'Unlimited'; ?></td>
                                                <td>$<?php echo number_format($tier->rate_per_cbm, 2); ?></td>
                                                <td>$<?php echo number_format($tier->fixed_charge, 2); ?></td>
                                                <td><span class="label <?php echo $tier->active ? 'label-success' : 'label-danger'; ?>"><?php echo $tier->active ? 'Active' : 'Inactive'; ?></span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info" onclick="editTier(<?php echo $tier->id; ?>)"><i class="ti-pencil"></i></button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteTier(<?php echo $tier->id; ?>)"><i class="ti-trash"></i></button>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <button class="btn btn-primary" onclick="addNewTier()"><i class="ti-plus"></i> Add New Tier</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'views/inc/footer.php'; ?>
    
    <script>
    function addNewTier() {
        // Open modal or redirect to add tier page
        alert('Add new tier functionality - to be implemented');
    }
    
    function editTier(id) {
        // Open modal or redirect to edit tier page
        alert('Edit tier ' + id + ' - to be implemented');
    }
    
    function deleteTier(id) {
        if (confirm('Are you sure you want to delete this tier?')) {
            // AJAX call to delete tier
            alert('Delete tier ' + id + ' - to be implemented');
        }
    }
    </script>
</body>
</html>
```

---

## Implementation Priority

### High Priority (Do First):
1. ✅ View pages - Add CBM display (30 minutes)
2. ✅ List pages - Add CBM column (1 hour)

### Medium Priority (Do Second):
3. ⏳ Add CBM to existing reports (1-2 hours)
4. ⏳ Create CBM utilization report (1 hour)

### Low Priority (Do Last):
5. ⏳ Settings page - Basic configuration (2 hours)
6. ⏳ Settings page - Pricing tiers management (2-3 hours)

---

## Testing Checklist

### View Pages:
- [ ] CBM displays correctly on consolidate view
- [ ] CBM displays correctly on courier view
- [ ] CBM displays correctly on customer package view
- [ ] CBM shows 4 decimal places
- [ ] CBM shows 0.0000 when no dimensions

### List Pages:
- [ ] CBM column appears in consolidate list
- [ ] CBM column appears in courier list
- [ ] CBM column sortable (if using DataTables)
- [ ] CBM column searchable (if using DataTables)
- [ ] CBM displays correctly in all rows

### Reports:
- [ ] CBM appears in general reports
- [ ] CBM totals calculate correctly
- [ ] CBM utilization report displays
- [ ] Date filters work correctly
- [ ] Export to PDF/Excel includes CBM

### Settings:
- [ ] Settings save correctly
- [ ] CBM enable/disable works
- [ ] Rate changes apply to new shipments
- [ ] Priority setting affects calculations
- [ ] Pricing tiers display correctly

---

## Quick Implementation Script

For fastest implementation, run these steps in order:

```bash
# 1. Backup files
cp views/consolidate/consolidate_view.php views/consolidate/consolidate_view.php.backup
cp views/courier/courier_view.php views/courier/courier_view.php.backup
cp views/customer_packages/customer_packages_view.php views/customer_packages/customer_packages_view.php.backup

# 2. Add CBM to view pages (use code snippets above)
# Edit each file and add CBM display section

# 3. Add CBM to list pages
# Edit consolidate_list.php and courier_list.php

# 4. Test thoroughly
# Verify CBM displays correctly in all locations

# 5. Create reports (optional)
# Use report template provided above

# 6. Create settings page (optional)
# Use settings page template provided above
```

---

## Support & Troubleshooting

### Common Issues:

**CBM not displaying in view:**
- Check database has `total_cbm` column
- Verify query includes `total_cbm`
- Check for PHP errors in logs

**CBM column not showing in list:**
- Clear browser cache
- Check AJAX file includes CBM
- Verify DataTables configuration

**Settings not saving:**
- Check database permissions
- Verify POST data
- Check PHP error logs

---

## Estimated Time Breakdown

| Task | Time | Priority |
|------|------|----------|
| View pages (3 files) | 30 min | High |
| List pages (2 files) | 1 hour | High |
| List AJAX files (2 files) | 30 min | High |
| Existing reports (3 files) | 1 hour | Medium |
| CBM utilization report | 1 hour | Medium |
| Settings page - Basic | 2 hours | Low |
| Settings page - Advanced | 2 hours | Low |
| Testing & debugging | 1 hour | High |
| **Total** | **9-10 hours** | |

---

## Conclusion

This guide provides all the code and instructions needed to complete the remaining CBM implementation phases. The core functionality (90%) is already complete and production-ready. These optional enhancements add polish and advanced features but are not required for basic CBM functionality.

**Recommendation:** Implement view and list pages first (High Priority), then add reports and settings based on business needs.

---

**Version:** 1.0  
**Last Updated:** [Current Date]  
**Status:** Ready for Implementation
