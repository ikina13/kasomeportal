# PostgreSQL Migration Summary - Flexible Subscription System

## 📁 Files Created

### 1. **Main Migration Script (PostgreSQL)**
- **File:** `2025_01_12_add_flexible_subscription_system_pgsql.sql`
- **Purpose:** Complete PostgreSQL migration with safety checks and verification
- **Use:** Production deployment (recommended)

### 2. **Rollback Script (PostgreSQL)**
- **File:** `2025_01_12_rollback_flexible_subscription_system_pgsql.sql`
- **Purpose:** Revert all changes if needed
- **Use:** Emergency rollback only

### 3. **Database Analysis & Plan**
- **File:** `DATABASE_ANALYSIS_AND_PLAN.md`
- **Purpose:** Complete analysis of current database state and migration plan
- **Use:** Review before migration

---

## 🚀 Quick Deployment Steps

### Step 1: Backup Database
```bash
pg_dump -U username -d kasome_stage_db -F c -f backup_before_subscription_migration.dump
```

Or using psql:
```bash
pg_dump -U username kasome_stage_db > backup_before_subscription_migration.sql
```

### Step 2: Run Migration

**Option A: Command Line (Recommended)**
```bash
psql -U username -d kasome_stage_db -f 2025_01_12_add_flexible_subscription_system_pgsql.sql
```

**Option B: Interactive psql**
```bash
psql -U username -d kasome_stage_db
\i 2025_01_12_add_flexible_subscription_system_pgsql.sql
```

**Option C: Using Laravel Artisan Tinker**
```bash
cd /path/to/kasomeportal
php artisan tinker
>>> DB::unprepared(file_get_contents('database/migrations/2025_01_12_add_flexible_subscription_system_pgsql.sql'));
```

---

## 📊 What Gets Changed

### Tables Modified

#### `tbl_subscriptions`
- ✅ **NEW COLUMN:** `subscription_type` (VARCHAR(20), CHECK constraint)
- ✅ **NEW CONSTRAINT:** `chk_subscription_type` (ensures 'all_courses' or 'specific_courses')
- ✅ **NEW INDEXES:** 
  - `idx_subscription_type`
  - `idx_subscription_access`
  - `idx_subscription_dates`

#### New Table Created

#### `tbl_course_subscriptions` (Pivot Table)
- Links subscriptions to specific courses
- Foreign keys to `tbl_subscriptions` and `tbl_practical_video` with CASCADE
- Unique constraint on `(subscription_id, course_id)`
- Indexes on both foreign keys for performance

### Data Migration
- All existing subscriptions (12 records) automatically set to `subscription_type = 'all_courses'`
- **No data loss** - maintains backward compatibility

---

## ✅ Pre-Migration Checklist

Before running on production:

- [ ] Database backup created
- [ ] Tested on staging/development environment
- [ ] Verified database user has required permissions (ALTER, CREATE, INDEX)
- [ ] Tables `tbl_subscriptions` and `tbl_practical_video` exist
- [ ] Noted current subscription count (currently: 12 subscriptions)
- [ ] Scheduled migration during low-traffic period (recommended)

---

## 🔍 Verification Queries

After migration, run these to verify success:

```sql
-- 1. Verify column added
SELECT column_name, data_type, is_nullable, column_default
FROM information_schema.columns
WHERE table_schema = 'public'
AND table_name = 'tbl_subscriptions'
AND column_name = 'subscription_type';

-- 2. Verify table created
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = 'public'
AND table_name = 'tbl_course_subscriptions';

-- 3. Check all subscriptions have type
SELECT 
    subscription_type,
    COUNT(*) as count,
    MIN(start_date) as earliest,
    MAX(end_date) as latest
FROM tbl_subscriptions
GROUP BY subscription_type;

-- 4. Verify indexes
SELECT indexname, indexdef 
FROM pg_indexes 
WHERE schemaname = 'public' 
AND tablename = 'tbl_subscriptions'
AND indexname LIKE 'idx_%'
ORDER BY indexname;

-- 5. Check foreign keys
SELECT
    tc.constraint_name,
    tc.table_name,
    kcu.column_name,
    ccu.table_name AS foreign_table_name,
    ccu.column_name AS foreign_column_name
FROM information_schema.table_constraints AS tc
JOIN information_schema.key_column_usage AS kcu
    ON tc.constraint_name = kcu.constraint_name
JOIN information_schema.constraint_column_usage AS ccu
    ON ccu.constraint_name = tc.constraint_name
WHERE tc.constraint_type = 'FOREIGN KEY'
AND tc.table_schema = 'public'
AND tc.table_name = 'tbl_course_subscriptions';
```

---

## 🔄 Rollback (If Needed)

**WARNING:** Only rollback if absolutely necessary! This will delete data.

```bash
psql -U username -d kasome_stage_db -f 2025_01_12_rollback_flexible_subscription_system_pgsql.sql
```

**What gets removed:**
- ❌ `tbl_course_subscriptions` table (and all its data)
- ❌ `subscription_type` column from `tbl_subscriptions`
- ❌ `chk_subscription_type` constraint
- ❌ Added indexes

**What stays:**
- ✅ All other subscription data
- ✅ All other tables unchanged

---

## ⚠️ Important Notes

### PostgreSQL-Specific Features Used
- ✅ `BIGSERIAL` for auto-incrementing primary keys
- ✅ `DO $$ ... END $$` blocks for conditional logic
- ✅ `RAISE NOTICE` for migration feedback
- ✅ `CHECK` constraints for data validation
- ✅ Transaction-based with `BEGIN`/`COMMIT`

### Backward Compatibility
- ✅ Existing subscriptions continue to work
- ✅ All existing subscriptions default to `all_courses` type
- ✅ No breaking changes to current functionality

### Performance
- ✅ Indexes added for optimal query performance
- ✅ Foreign key constraints ensure data integrity
- ✅ Unique constraints prevent duplicate relationships
- ⚠️ Migration may take a few minutes on large tables (adding indexes)

### Safety Features
- ✅ Transaction-based (can rollback on error)
- ✅ Existence checks before creating (safe to run multiple times)
- ✅ No data deletion or modification (only additions)

---

## 🆘 Troubleshooting

### Error: "column already exists"
✅ **Safe to ignore** - The script checks before creating

### Error: "table already exists"
✅ **Safe to ignore** - The script checks before creating

### Error: "permission denied"
❌ **Solution:**
- Ensure database user has ALTER, CREATE, INDEX permissions
- Grant required permissions:
  ```sql
  GRANT ALTER, CREATE ON DATABASE kasome_stage_db TO username;
  GRANT ALL ON ALL TABLES IN SCHEMA public TO username;
  GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO username;
  ```

### Error: "foreign key constraint fails"
❌ **Check:**
- Tables `tbl_subscriptions` and `tbl_practical_video` exist
- Data types match between tables
- No orphaned records in subscriptions

---

## 📅 Migration Log

**Date:** 2025-01-12  
**Database:** PostgreSQL  
**Version:** 1.0  
**Status:** ✅ Ready for deployment  
**Current Subscriptions:** 12  
**Current Class Subscriptions:** 0

---

**Last Updated:** 2025-01-12

