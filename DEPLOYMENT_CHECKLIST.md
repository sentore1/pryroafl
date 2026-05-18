# 🚀 CBM Feature - Deployment Checklist

## Pre-Deployment Checklist

### 1. Database Preparation ✅
- [ ] Backup current database
- [ ] Review migration script (`sql/cbm_migration.sql`)
- [ ] Test migration on staging environment
- [ ] Verify all 7 tables will be updated
- [ ] Check pricing tiers data

### 2. File Verification ✅
- [ ] Verify all 20 modified files are in place
- [ ] Check PHP functions in `helpers/functions.php`
- [ ] Verify JavaScript files updated (8 files)
- [ ] Confirm view files updated (5 files)
- [ ] Verify PDF templates updated (3 files)
- [ ] Check bulk upload integration

### 3. Testing Environment ✅
- [ ] Set up test environment
- [ ] Run database migration on test
- [ ] Test consolidate add/edit
- [ ] Test courier add/edit
- [ ] Test customer packages add/edit
- [ ] Test bulk upload with CBM
- [ ] Generate test PDF invoices
- [ ] Verify CBM displays on PDFs

---

## Deployment Steps

### Step 1: Database Migration
```bash
# Backup database first!
mysqldump -u username -p database_name > backup_before_cbm.sql

# Run migration
mysql -u username -p database_name < sql/cbm_migration.sql
```

**Verify:**
```sql
-- Check new columns exist
DESCRIBE cdb_add_order_item;
DESCRIBE cdb_consolidate;

-- Check pricing tiers
SELECT * FROM cdb_cbm_pricing_tiers;

-- Verify existing data updated
SELECT COUNT(*) FROM cdb_add_order_item WHERE cbm > 0;
```

### Step 2: File Deployment
```bash
# Upload modified files to server
# Ensure proper permissions

# PHP files
chmod 644 helpers/functions.php
chmod 644 ajax/consolidate/process_bulk_upload_ajax.php

# View files
chmod 644 views/consolidate/*.php
chmod 644 views/courier/*.php
chmod 644 views/customer_packages/*.php

# PDF templates
chmod 644 pdf/documentos/html/*.php

# JavaScript files
chmod 644 dataJs/*.js
```

### Step 3: Clear Cache
```bash
# Clear PHP cache if using OPcache
php -r "opcache_reset();"

# Clear browser cache
# Instruct users to hard refresh (Ctrl+F5)
```

### Step 4: Configuration (Optional)
```sql
-- Enable CBM and set default rates
UPDATE cdb_settings 
SET 
    cbm_calculation_enabled = 1,
    cbm_rate_per_cubic_meter = 50.00,
    cbm_vs_weight_priority = 'higher'
WHERE id = 1;
```

---

## Post-Deployment Testing

### Test 1: Consolidate Container ✅
- [ ] Go to Consolidate > Add Container
- [ ] Add shipment: 50cm × 40cm × 30cm
- [ ] Verify CBM shows: 0.0600 m³
- [ ] Submit form successfully
- [ ] Check database: `SELECT total_cbm FROM cdb_consolidate ORDER BY id DESC LIMIT 1;`
- [ ] Generate PDF invoice
- [ ] Verify CBM appears on PDF

### Test 2: Courier Shipment ✅
- [ ] Go to Courier > Add Shipment
- [ ] Add package: 40cm × 30cm × 25cm
- [ ] Verify CBM shows: 0.0300 m³
- [ ] Submit form successfully
- [ ] Check database: `SELECT total_cbm FROM cdb_add_order ORDER BY order_id DESC LIMIT 1;`
- [ ] Generate PDF invoice
- [ ] Verify CBM appears on PDF

### Test 3: Customer Package ✅
- [ ] Go to Customer Packages > Add
- [ ] Add package: 30cm × 20cm × 15cm
- [ ] Verify CBM shows: 0.0090 m³
- [ ] Submit form successfully
- [ ] Check database: `SELECT total_cbm FROM cdb_customers_packages ORDER BY order_id DESC LIMIT 1;`
- [ ] Generate PDF invoice
- [ ] Verify CBM appears on PDF

### Test 4: Bulk Upload ✅
- [ ] Go to Consolidate > Bulk Upload
- [ ] Download template
- [ ] Add 3 rows with dimensions
- [ ] Upload file
- [ ] Verify success message
- [ ] Check database for CBM values
- [ ] Generate PDF for uploaded items
- [ ] Verify CBM on PDFs

### Test 5: Edit Functionality ✅
- [ ] Edit existing consolidate container
- [ ] Modify dimensions
- [ ] Verify CBM recalculates
- [ ] Save changes
- [ ] Verify database updated
- [ ] Generate PDF
- [ ] Verify updated CBM on PDF

---

## User Training Checklist

### Training Materials ✅
- [ ] Review `CBM_IMPLEMENTATION_GUIDE.md`
- [ ] Review `README_CBM.md`
- [ ] Prepare training presentation
- [ ] Create sample data for practice
- [ ] Prepare FAQ document

### Training Topics ✅
- [ ] What is CBM and why it matters
- [ ] How CBM is calculated
- [ ] Where CBM appears in the system
- [ ] How to enter dimensions correctly
- [ ] Understanding CBM vs weight pricing
- [ ] Bulk upload with CBM
- [ ] Generating PDF invoices with CBM
- [ ] Troubleshooting common issues

### Staff Training ✅
- [ ] Train administrators
- [ ] Train data entry staff
- [ ] Train customer service
- [ ] Train accounting/billing
- [ ] Provide documentation access
- [ ] Schedule follow-up sessions

---

## Monitoring & Validation

### Week 1: Daily Monitoring ✅
- [ ] Check for PHP errors in logs
- [ ] Monitor JavaScript console errors
- [ ] Verify CBM calculations accurate
- [ ] Check PDF generation working
- [ ] Review user feedback
- [ ] Monitor database performance

### Week 2-4: Regular Checks ✅
- [ ] Weekly error log review
- [ ] Sample CBM calculation verification
- [ ] PDF invoice spot checks
- [ ] User satisfaction survey
- [ ] Performance metrics review

### Month 1: Full Review ✅
- [ ] Comprehensive functionality review
- [ ] User feedback analysis
- [ ] Performance optimization
- [ ] Documentation updates
- [ ] Training effectiveness review
- [ ] Plan for optional phases

---

## Rollback Plan (If Needed)

### Emergency Rollback Steps:
```bash
# 1. Restore database backup
mysql -u username -p database_name < backup_before_cbm.sql

# 2. Restore original files
# Replace modified files with backups

# 3. Clear cache
php -r "opcache_reset();"

# 4. Notify users
# Send communication about temporary rollback
```

### Rollback Triggers:
- Critical calculation errors
- Database corruption
- Widespread user issues
- Performance degradation
- PDF generation failures

---

## Success Metrics

### Technical Metrics ✅
- [ ] Zero critical errors in logs
- [ ] CBM calculations 100% accurate
- [ ] PDF generation success rate > 99%
- [ ] Page load time < 3 seconds
- [ ] Database queries optimized

### Business Metrics ✅
- [ ] User adoption rate > 80%
- [ ] Positive user feedback
- [ ] Accurate invoicing
- [ ] Improved pricing accuracy
- [ ] Better container utilization

### User Satisfaction ✅
- [ ] Staff comfortable using feature
- [ ] Customers understand CBM charges
- [ ] Reduced pricing disputes
- [ ] Positive feedback from team
- [ ] Smooth workflow integration

---

## Documentation Checklist

### Available Documentation ✅
- [x] `CBM_IMPLEMENTATION_COMPLETE.md` - Overall summary
- [x] `CBM_IMPLEMENTATION_GUIDE.md` - Complete usage guide
- [x] `CBM_PDF_TEMPLATES_COMPLETE.md` - PDF template details
- [x] `CBM_FINAL_SUMMARY.md` - Executive summary
- [x] `INSTALL_CBM.md` - Installation guide
- [x] `README_CBM.md` - Quick reference
- [x] `sql/cbm_migration.sql` - Database migration
- [x] `sql/cbm_test_queries.sql` - Test queries
- [x] `DEPLOYMENT_CHECKLIST.md` - This file

### Documentation Access ✅
- [ ] All staff have access to documentation
- [ ] Documentation stored in accessible location
- [ ] Quick reference guide printed/posted
- [ ] FAQ document created
- [ ] Support contact information provided

---

## Support Plan

### Level 1: Self-Service ✅
- Documentation files
- FAQ document
- Video tutorials (if created)
- Quick reference guide

### Level 2: Internal Support ✅
- Designated CBM feature expert
- Internal support ticket system
- Regular office hours for questions
- Email support

### Level 3: Technical Support ✅
- Database administrator
- PHP developer
- System administrator
- Escalation process

---

## Communication Plan

### Pre-Deployment ✅
- [ ] Announce feature to all users
- [ ] Schedule training sessions
- [ ] Provide documentation access
- [ ] Set expectations for rollout

### During Deployment ✅
- [ ] Notify users of deployment window
- [ ] Provide status updates
- [ ] Announce completion
- [ ] Share quick start guide

### Post-Deployment ✅
- [ ] Send success announcement
- [ ] Provide feedback channels
- [ ] Schedule follow-up training
- [ ] Share tips and best practices

---

## Final Sign-Off

### Technical Team ✅
- [ ] Database Administrator approval
- [ ] PHP Developer approval
- [ ] System Administrator approval
- [ ] QA Team approval

### Business Team ✅
- [ ] Operations Manager approval
- [ ] Finance/Accounting approval
- [ ] Customer Service approval
- [ ] Executive approval

### Deployment Authorization ✅
- [ ] All tests passed
- [ ] All approvals received
- [ ] Rollback plan in place
- [ ] Support team ready
- [ ] Documentation complete

**Deployment Date:** _______________  
**Deployed By:** _______________  
**Approved By:** _______________

---

## Post-Deployment Notes

### Issues Encountered:
```
[Document any issues found during deployment]
```

### Resolutions Applied:
```
[Document how issues were resolved]
```

### Lessons Learned:
```
[Document lessons for future deployments]
```

### Recommendations:
```
[Document recommendations for improvements]
```

---

**Status:** Ready for Deployment ✅  
**Risk Level:** Low  
**Estimated Downtime:** < 5 minutes  
**Rollback Time:** < 10 minutes

**Good luck with your deployment! 🚀**
