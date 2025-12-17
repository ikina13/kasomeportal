# Database Analysis & Migration Plan
**Date:** 2025-01-12  
**Database:** PostgreSQL (kasome_stage_db)  
**Connection:** pgsql (localhost)

---

## 📊 CURRENT DATABASE STATE

### Existing Subscription-Related Tables

#### 1. `tbl_subscriptions` (General Subscriptions)
**Current Structure:**
```sql
- id (bigint, PK, auto-increment)
- user_id (bigint, nullable)
- amount (bigint, nullable)
- start_date (timestamp with time zone, NOT NULL)
- end_date (timestamp with time zone, NOT NULL)
- status (varchar(20), NOT NULL, default: 'active')
- created_at (timestamp, nullable, default: now())
- created_by (bigint, nullable)
- updated_at (timestamp, nullable, default: now())
- updated_by (bigint, nullable)
```

**Current Data:**
- ✅ 12 subscriptions exist
- ✅ Currently used for "all courses" access
- ⚠️ **MISSING:** `subscription_type` column

**Current Indexes:**
- `tbl_subscriptions_pkey` (primary key on `id`)

---

#### 2. `tbl_class_subscriptions` (Class-Level Subscriptions)
**Current Structure:**
```sql
- id (bigint, PK, auto-increment)
- user_id (bigint, nullable)
- class_id (bigint, nullable)  ← Links to tbl_class.id
- amount (bigint, nullable)
- start_date (timestamp with time zone, NOT NULL)
- end_date (timestamp with time zone, NOT NULL)
- status (varchar(20), NOT NULL, default: 'active')
- created_at (timestamp, nullable, default: now())
- created_by (bigint, nullable)
- updated_at (timestamp, nullable, default: now())
- updated_by (bigint, nullable)
```

**Current Data:**
- ✅ Table exists
- ⚠️ 0 records (empty)
- ✅ Structure ready for class-level subscriptions

**Current Indexes:**
- Primary key only

---

#### 3. `tbl_practical_video` (Courses)
**Key Columns:**
```sql
- id (bigint, PK)
- name (varchar)
- class_id (bigint, nullable)  ← Links courses to classes
- subject_id (bigint, nullable)
- price (bigint, nullable)
- status (enum: 'active')
- ...
```

**Relevance:**
- This is the main course/video table
- Has `class_id` for class-based grouping
- Need to link subscriptions to specific courses

---

#### 4. `tbl_users`
**Key Columns:**
```sql
- id (bigint, PK)
- user_type (varchar)  ← 'student' or 'school'
- ...
```

**Relevance:**
- Distinguishes between regular users and schools
- Both can have subscriptions

---

## 🎯 WHAT NEEDS TO CHANGE

### **MODIFICATIONS** (Changes to Existing Tables)

#### 1. Modify `tbl_subscriptions` Table
**Action:** Add `subscription_type` column

**Change:**
```sql
ALTER TABLE tbl_subscriptions 
ADD COLUMN subscription_type VARCHAR(20) NOT NULL DEFAULT 'all_courses'
CHECK (subscription_type IN ('all_courses', 'specific_courses'));
```

**Reason:**
- Need to differentiate between "all courses" and "specific courses" subscriptions
- Defaults to 'all_courses' for backward compatibility
- Existing 12 subscriptions will automatically get 'all_courses' type

**Data Migration:**
- All existing subscriptions: Set `subscription_type = 'all_courses'`
- No data loss
- Fully backward compatible

---

### **ADDITIONS** (New Tables/Indexes)

#### 2. Create `tbl_course_subscriptions` Pivot Table
**Action:** Create new table for many-to-many relationship

**New Table Structure:**
```sql
CREATE TABLE tbl_course_subscriptions (
    id BIGSERIAL PRIMARY KEY,
    subscription_id BIGINT NOT NULL,
    course_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    created_by BIGINT,
    
    -- Foreign Keys
    CONSTRAINT fk_subscription 
        FOREIGN KEY (subscription_id) 
        REFERENCES tbl_subscriptions(id) 
        ON DELETE CASCADE,
    
    CONSTRAINT fk_course 
        FOREIGN KEY (course_id) 
        REFERENCES tbl_practical_video(id) 
        ON DELETE CASCADE,
    
    -- Unique Constraint: One subscription can't have the same course twice
    CONSTRAINT unique_subscription_course 
        UNIQUE (subscription_id, course_id)
);
```

**Purpose:**
- Links subscriptions to specific courses
- When `subscription_type = 'specific_courses'`, this table stores which courses are included
- When `subscription_type = 'all_courses'`, this table remains empty

---

#### 3. Add Performance Indexes

**On `tbl_subscriptions`:**
```sql
-- Index for filtering by subscription type
CREATE INDEX idx_subscription_type 
    ON tbl_subscriptions(subscription_type);

-- Composite index for access queries
CREATE INDEX idx_subscription_access 
    ON tbl_subscriptions(user_id, status, subscription_type, start_date, end_date);

-- Index for date range queries
CREATE INDEX idx_subscription_dates 
    ON tbl_subscriptions(start_date, end_date);
```

**On `tbl_course_subscriptions`:**
```sql
-- Index for fast course lookups
CREATE INDEX idx_course_subscription_course 
    ON tbl_course_subscriptions(course_id);

-- Index for subscription lookups
CREATE INDEX idx_course_subscription_subscription 
    ON tbl_course_subscriptions(subscription_id);
```

---

## 📋 COMPLETE MIGRATION SUMMARY

### What Gets **MODIFIED**:
1. ✅ `tbl_subscriptions` table
   - **ADD:** `subscription_type` column (VARCHAR, default: 'all_courses')
   - **ADD:** 3 new indexes for performance

### What Gets **CREATED**:
2. ✅ `tbl_course_subscriptions` table (new pivot table)
   - Stores subscription-to-course relationships
   - Foreign keys with CASCADE delete
   - Unique constraint

3. ✅ Performance indexes (5 new indexes total)

### What **STAYS THE SAME**:
- ✅ `tbl_class_subscriptions` - No changes needed (already perfect)
- ✅ `tbl_practical_video` - No changes needed
- ✅ `tbl_users` - No changes needed
- ✅ All existing data preserved
- ✅ All existing functionality maintained

---

## 🔄 MIGRATION IMPACT

### Existing Subscriptions (12 records)
- ✅ **No breaking changes**
- ✅ Automatically get `subscription_type = 'all_courses'`
- ✅ Continue working exactly as before
- ✅ Access to ALL courses maintained

### New Functionality
- ✅ Schools can now subscribe to **specific courses** instead of all
- ✅ Users can now subscribe to **specific courses** instead of all
- ✅ Class subscriptions remain unchanged
- ✅ Individual course payments remain unchanged

---

## 📊 FINAL DATABASE STRUCTURE

### Subscription Flow:
```
tbl_users
    ├── tbl_subscriptions (subscription_type: 'all_courses' OR 'specific_courses')
    │       └── tbl_course_subscriptions (only if subscription_type = 'specific_courses')
    │
    └── tbl_class_subscriptions (links to tbl_class via class_id)
```

### Access Priority:
1. **Free video?** → Always accessible
2. **Individual payment?** → Check `tbl_payments`
3. **Class subscription?** → Check `tbl_class_subscriptions` + course's `class_id`
4. **General subscription?**
   - If `subscription_type = 'all_courses'` → Access all
   - If `subscription_type = 'specific_courses'` → Check `tbl_course_subscriptions`

---

## ⚠️ IMPORTANT NOTES

### Backward Compatibility
- ✅ **100% backward compatible**
- ✅ Existing subscriptions continue to work
- ✅ No application code changes required initially
- ✅ Can deploy database changes first, code later

### Data Safety
- ✅ **Zero data loss**
- ✅ All existing subscriptions preserved
- ✅ Transaction-based (rollback safe)
- ✅ Can run multiple times safely (checks for existing columns/tables)

### Performance
- ✅ Indexes added for optimal query performance
- ✅ Foreign key constraints ensure data integrity
- ✅ Unique constraints prevent duplicate relationships

---

## 🚀 NEXT STEPS

1. **Review this plan** - Ensure it matches your requirements
2. **Run migration script** - PostgreSQL-compatible SQL provided
3. **Verify changes** - Run verification queries
4. **Update application code** - Models, controllers, API endpoints
5. **Test functionality** - Create test subscriptions
6. **Deploy to production** - When ready

---

**Document Status:** ✅ Ready for Review  
**SQL Scripts:** PostgreSQL-compatible  
**Risk Level:** 🟢 Low (backward compatible, no data loss)

