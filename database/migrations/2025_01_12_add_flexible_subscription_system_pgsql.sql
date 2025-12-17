-- ============================================================================
-- PostgreSQL Migration Script: Flexible Subscription System
-- Date: 2025-01-12
-- Database: PostgreSQL
-- Description: Adds support for course-specific subscriptions
-- ============================================================================
-- 
-- IMPORTANT: Backup your database before running this script!
-- 
-- This script will:
-- 1. Add subscription_type column to tbl_subscriptions
-- 2. Create tbl_course_subscriptions pivot table
-- 3. Migrate existing data (set all to 'all_courses')
-- 4. Add necessary indexes for performance
--
-- Run this script in your PostgreSQL database
-- ============================================================================

-- Start transaction for safety
BEGIN;

-- ============================================================================
-- STEP 1: Add subscription_type column to tbl_subscriptions
-- ============================================================================

-- Check if column exists before adding
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_subscriptions' 
        AND column_name = 'subscription_type'
    ) THEN
        ALTER TABLE tbl_subscriptions 
        ADD COLUMN subscription_type VARCHAR(20) NOT NULL DEFAULT 'all_courses';
        
        -- Add check constraint
        ALTER TABLE tbl_subscriptions 
        ADD CONSTRAINT chk_subscription_type 
        CHECK (subscription_type IN ('all_courses', 'specific_courses'));
        
        RAISE NOTICE 'Added subscription_type column to tbl_subscriptions';
    ELSE
        RAISE NOTICE 'Column subscription_type already exists in tbl_subscriptions';
    END IF;
END $$;

-- ============================================================================
-- STEP 2: Create tbl_course_subscriptions pivot table
-- ============================================================================

-- Check if table exists before creating
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_course_subscriptions'
    ) THEN
        CREATE TABLE tbl_course_subscriptions (
            id BIGSERIAL PRIMARY KEY,
            subscription_id BIGINT NOT NULL,
            course_id BIGINT NOT NULL,
            created_at TIMESTAMP DEFAULT NOW(),
            created_by BIGINT,
            
            -- Foreign Keys
            CONSTRAINT fk_course_subscription_subscription 
                FOREIGN KEY (subscription_id) 
                REFERENCES tbl_subscriptions(id) 
                ON DELETE CASCADE
                ON UPDATE CASCADE,
            
            CONSTRAINT fk_course_subscription_course 
                FOREIGN KEY (course_id) 
                REFERENCES tbl_practical_video(id) 
                ON DELETE CASCADE
                ON UPDATE CASCADE,
            
            -- Unique Constraint: Prevent duplicate course-subscription pairs
            CONSTRAINT unique_subscription_course 
                UNIQUE (subscription_id, course_id)
        );
        
        -- Add comment
        COMMENT ON TABLE tbl_course_subscriptions IS 'Pivot table linking subscriptions to specific courses';
        COMMENT ON COLUMN tbl_course_subscriptions.subscription_id IS 'Foreign key to tbl_subscriptions.id';
        COMMENT ON COLUMN tbl_course_subscriptions.course_id IS 'Foreign key to tbl_practical_video.id';
        
        RAISE NOTICE 'Created tbl_course_subscriptions table';
    ELSE
        RAISE NOTICE 'Table tbl_course_subscriptions already exists';
    END IF;
END $$;

-- ============================================================================
-- STEP 3: Migrate existing data
-- ============================================================================

-- Set all existing subscriptions to 'all_courses' type
-- This ensures backward compatibility
UPDATE tbl_subscriptions 
SET subscription_type = 'all_courses' 
WHERE subscription_type IS NULL OR subscription_type = '';

-- ============================================================================
-- STEP 4: Add indexes for performance
-- ============================================================================

-- Index on subscription_type for fast filtering
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM pg_indexes 
        WHERE schemaname = 'public' 
        AND tablename = 'tbl_subscriptions' 
        AND indexname = 'idx_subscription_type'
    ) THEN
        CREATE INDEX idx_subscription_type 
        ON tbl_subscriptions(subscription_type);
        RAISE NOTICE 'Created index idx_subscription_type';
    ELSE
        RAISE NOTICE 'Index idx_subscription_type already exists';
    END IF;
END $$;

-- Composite index for access queries (user_id, status, subscription_type, dates)
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM pg_indexes 
        WHERE schemaname = 'public' 
        AND tablename = 'tbl_subscriptions' 
        AND indexname = 'idx_subscription_access'
    ) THEN
        CREATE INDEX idx_subscription_access 
        ON tbl_subscriptions(user_id, status, subscription_type, start_date, end_date);
        RAISE NOTICE 'Created index idx_subscription_access';
    ELSE
        RAISE NOTICE 'Index idx_subscription_access already exists';
    END IF;
END $$;

-- Index for date range queries
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM pg_indexes 
        WHERE schemaname = 'public' 
        AND tablename = 'tbl_subscriptions' 
        AND indexname = 'idx_subscription_dates'
    ) THEN
        CREATE INDEX idx_subscription_dates 
        ON tbl_subscriptions(start_date, end_date);
        RAISE NOTICE 'Created index idx_subscription_dates';
    ELSE
        RAISE NOTICE 'Index idx_subscription_dates already exists';
    END IF;
END $$;

-- Index on course_id in pivot table for fast course lookups
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM pg_indexes 
        WHERE schemaname = 'public' 
        AND tablename = 'tbl_course_subscriptions' 
        AND indexname = 'idx_course_subscription_course'
    ) THEN
        CREATE INDEX idx_course_subscription_course 
        ON tbl_course_subscriptions(course_id);
        RAISE NOTICE 'Created index idx_course_subscription_course';
    ELSE
        RAISE NOTICE 'Index idx_course_subscription_course already exists';
    END IF;
END $$;

-- Index on subscription_id in pivot table for fast subscription lookups
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM pg_indexes 
        WHERE schemaname = 'public' 
        AND tablename = 'tbl_course_subscriptions' 
        AND indexname = 'idx_course_subscription_subscription'
    ) THEN
        CREATE INDEX idx_course_subscription_subscription 
        ON tbl_course_subscriptions(subscription_id);
        RAISE NOTICE 'Created index idx_course_subscription_subscription';
    ELSE
        RAISE NOTICE 'Index idx_course_subscription_subscription already exists';
    END IF;
END $$;

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================

-- Verify subscription_type column was added
DO $$
DECLARE
    column_exists INTEGER;
BEGIN
    SELECT COUNT(*) INTO column_exists
    FROM information_schema.columns 
    WHERE table_schema = 'public'
    AND table_name = 'tbl_subscriptions' 
    AND column_name = 'subscription_type';
    
    IF column_exists > 0 THEN
        RAISE NOTICE '✓ VERIFICATION: subscription_type column exists';
    ELSE
        RAISE WARNING '✗ VERIFICATION FAILED: subscription_type column NOT found';
    END IF;
END $$;

-- Verify tbl_course_subscriptions table was created
DO $$
DECLARE
    table_exists INTEGER;
BEGIN
    SELECT COUNT(*) INTO table_exists
    FROM information_schema.tables 
    WHERE table_schema = 'public'
    AND table_name = 'tbl_course_subscriptions';
    
    IF table_exists > 0 THEN
        RAISE NOTICE '✓ VERIFICATION: tbl_course_subscriptions table exists';
    ELSE
        RAISE WARNING '✗ VERIFICATION FAILED: tbl_course_subscriptions table NOT found';
    END IF;
END $$;

-- Show subscription type distribution
DO $$
DECLARE
    all_courses_count INTEGER;
    specific_courses_count INTEGER;
    total_count INTEGER;
BEGIN
    SELECT COUNT(*) INTO total_count FROM tbl_subscriptions;
    SELECT COUNT(*) INTO all_courses_count FROM tbl_subscriptions WHERE subscription_type = 'all_courses';
    SELECT COUNT(*) INTO specific_courses_count FROM tbl_subscriptions WHERE subscription_type = 'specific_courses';
    
    RAISE NOTICE '✓ VERIFICATION: Subscription distribution';
    RAISE NOTICE '  Total subscriptions: %', total_count;
    RAISE NOTICE '  All courses: %', all_courses_count;
    RAISE NOTICE '  Specific courses: %', specific_courses_count;
END $$;

-- ============================================================================
-- COMMIT TRANSACTION
-- ============================================================================

-- If everything looks good, commit the transaction
-- If there are any issues, rollback with: ROLLBACK;
COMMIT;

-- ============================================================================
-- POST-MIGRATION VERIFICATION QUERIES
-- ============================================================================
-- Run these queries after migration to verify everything is correct:

-- 1. Check column structure
-- SELECT column_name, data_type, is_nullable, column_default
-- FROM information_schema.columns
-- WHERE table_schema = 'public' AND table_name = 'tbl_subscriptions'
-- ORDER BY ordinal_position;

-- 2. Check table structure
-- \d tbl_course_subscriptions

-- 3. Check indexes
-- SELECT indexname, indexdef 
-- FROM pg_indexes 
-- WHERE schemaname = 'public' 
-- AND (tablename = 'tbl_subscriptions' OR tablename = 'tbl_course_subscriptions')
-- ORDER BY tablename, indexname;

-- 4. Check foreign keys
-- SELECT
--     tc.constraint_name,
--     tc.table_name,
--     kcu.column_name,
--     ccu.table_name AS foreign_table_name,
--     ccu.column_name AS foreign_column_name
-- FROM information_schema.table_constraints AS tc
-- JOIN information_schema.key_column_usage AS kcu
--     ON tc.constraint_name = kcu.constraint_name
-- JOIN information_schema.constraint_column_usage AS ccu
--     ON ccu.constraint_name = tc.constraint_name
-- WHERE tc.constraint_type = 'FOREIGN KEY'
-- AND tc.table_schema = 'public'
-- AND tc.table_name = 'tbl_course_subscriptions';

-- ============================================================================
-- SCRIPT COMPLETED
-- ============================================================================
-- 
-- Next steps:
-- 1. Verify all changes were applied successfully
-- 2. Test the application with the new subscription system
-- 3. Monitor for any issues
--
-- If you need to rollback, use the rollback script provided separately
-- ============================================================================

