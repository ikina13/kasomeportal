# Flexible Subscription System - Database Migration Guide

## Overview
This migration adds support for flexible course subscriptions where:
- Users/Schools can subscribe to **all courses** or **specific courses**
- Course-specific subscriptions are tracked in a new pivot table

## Files Included

### 1. `2025_01_12_add_flexible_subscription_system.sql`
Main migration script that adds:
- `subscription_type` column to `tbl_subscriptions`
- `tbl_course_subscriptions` pivot table
- Performance indexes
- Data migration for existing records

### 2. `2025_01_12_rollback_flexible_subscription_system.sql`
Rollback script to revert all changes if needed.

---

## Pre-Migration Checklist

Before running the migration:

- [ ] **Backup your database** (CRITICAL!)
- [ ] Test the migration on a staging/development database first
- [ ] Verify database user has ALTER, CREATE, INDEX, and FOREIGN KEY permissions
- [ ] Check that tables `tbl_subscriptions` and `tbl_practical_video` exist
- [ ] Ensure minimal user activity during migration (recommended)
- [ ] Note the current number of subscriptions for verification

---

## Running the Migration

### Option 1: Using MySQL Command Line
```bash
mysql -u your_username -p your_database_name < 2025_01_12_add_flexible_subscription_system.sql
```

### Option 2: Using phpMyAdmin
1. Log into phpMyAdmin
2. Select your database
3. Go to "SQL" tab
4. Copy and paste the entire migration script
5. Click "Go" to execute

### Option 3: Using MySQL Workbench
1. Open MySQL Workbench
2. Connect to your database
3. Open the SQL file
4. Execute the script

### Option 4: Using Laravel Artisan (if applicable)
If you want to convert this to a Laravel migration:
```bash
php artisan migrate
```

---

## What the Migration Does

### Step 1: Add `subscription_type` Column
- Adds ENUM column with values: `'all_courses'` or `'specific_courses'`
- Defaults to `'all_courses'` for backward compatibility
- Positioned after `status` column

### Step 2: Create Pivot Table
Creates `tbl_course_subscriptions` table with:
- `id` (primary key)
- `subscription_id` (foreign key to `tbl_subscriptions`)
- `course_id` (foreign key to `tbl_practical_video`)
- `created_at` timestamp
- `created_by` user ID
- Unique constraint on `(subscription_id, course_id)` pair
- Foreign key constraints with CASCADE delete

### Step 3: Data Migration
- Sets all existing subscriptions to `subscription_type = 'all_courses'`
- Maintains backward compatibility

### Step 4: Performance Indexes
Adds indexes for:
- Fast filtering by `subscription_type`
- Composite index on `(status, subscription_type)`
- Composite index on `(user_id, status, start_date, end_date)` for access queries

---

## Verification After Migration

After running the migration, verify:

```sql
-- 1. Check subscription_type column exists
DESCRIBE tbl_subscriptions;

-- 2. Verify all subscriptions have subscription_type set
SELECT subscription_type, COUNT(*) as count 
FROM tbl_subscriptions 
GROUP BY subscription_type;

-- 3. Check pivot table structure
DESCRIBE tbl_course_subscriptions;

-- 4. Verify foreign keys
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tbl_course_subscriptions'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- 5. Check indexes
SHOW INDEXES FROM tbl_subscriptions WHERE Key_name LIKE 'idx_%';
```

---

## Expected Results

After successful migration:

✅ `tbl_subscriptions` should have `subscription_type` column
✅ `tbl_course_subscriptions` table should exist
✅ All existing subscriptions should have `subscription_type = 'all_courses'`
✅ Three new indexes should exist on `tbl_subscriptions`
✅ Foreign key constraints should be active

---

## Troubleshooting

### Error: Column already exists
- The script checks for existing columns/tables before creating them
- This is safe and won't cause errors

### Error: Foreign key constraint fails
- Verify that `tbl_subscriptions` and `tbl_practical_video` tables exist
- Check that referenced columns have matching data types
- Ensure there are no orphaned records

### Error: Table already exists
- The script checks for existing tables
- You can safely run it multiple times

### Performance Issues
- The migration includes indexes for performance
- Large tables might take a few minutes to add indexes
- Consider running during low-traffic periods

---

## Rollback Procedure

If you need to revert the changes:

```bash
mysql -u your_username -p your_database_name < 2025_01_12_rollback_flexible_subscription_system.sql
```

**Warning:** This will:
- Delete the `tbl_course_subscriptions` table and all its data
- Remove the `subscription_type` column from `tbl_subscriptions`
- Remove added indexes

**Make sure you have a backup before rolling back!**

---

## Post-Migration Steps

After successful migration:

1. **Update Application Code**
   - Update `Subscription` model to include `subscription_type`
   - Add relationships for course subscriptions
   - Update access checking logic

2. **Test Functionality**
   - Test creating subscriptions with `subscription_type = 'all_courses'`
   - Test creating subscriptions with `subscription_type = 'specific_courses'`
   - Verify course access logic works correctly

3. **Monitor Performance**
   - Check query performance with new indexes
   - Monitor for any slow queries related to subscriptions

---

## Support

If you encounter issues:
1. Check the error message carefully
2. Verify your database structure matches expectations
3. Check MySQL error logs
4. Ensure you have proper database permissions

---

## Database Schema After Migration

### tbl_subscriptions
```
+-------------------+------------------------------------------+------+-----+------------------+
| Field             | Type                                     | Null | Key | Default          |
+-------------------+------------------------------------------+------+-----+------------------+
| id                | bigint(20) unsigned                      | NO   | PRI | NULL             |
| user_id           | bigint(20) unsigned                      | NO   |     | NULL             |
| amount            | decimal(10,2)                            | YES  |     | NULL             |
| start_date        | datetime                                 | YES  |     | NULL             |
| end_date          | datetime                                 | YES  |     | NULL             |
| status            | varchar(255)                             | YES  |     | NULL             |
| subscription_type | enum('all_courses','specific_courses')   | NO   | MUL | all_courses      |
| created_by        | bigint(20) unsigned                      | YES  |     | NULL             |
| updated_by        | bigint(20) unsigned                      | YES  |     | NULL             |
+-------------------+------------------------------------------+------+-----+------------------+
```

### tbl_course_subscriptions
```
+----------------+---------------------+------+-----+-------------------+-------------------+
| Field          | Type                | Null | Key | Default           | Extra             |
+----------------+---------------------+------+-----+-------------------+-------------------+
| id             | bigint(20) unsigned | NO   | PRI | NULL              | auto_increment    |
| subscription_id| bigint(20) unsigned | NO   | MUL | NULL              |                   |
| course_id      | bigint(20) unsigned | NO   | MUL | NULL              |                   |
| created_at     | timestamp           | YES  |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
| created_by     | bigint(20) unsigned | YES  |     | NULL              |                   |
+----------------+---------------------+------+-----+-------------------+-------------------+
```

---

**Last Updated:** 2025-01-12
**Version:** 1.0

