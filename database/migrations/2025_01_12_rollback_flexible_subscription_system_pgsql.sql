-- ============================================================================
-- PostgreSQL Rollback Script: Flexible Subscription System
-- Date: 2025-01-12
-- Description: Reverts changes made by the flexible subscription system migration
-- ============================================================================
-- 
-- WARNING: This will remove the subscription_type column and course_subscriptions table!
-- Make sure you have a backup before running this!
-- 
-- This script will:
-- 1. Drop tbl_course_subscriptions table
-- 2. Remove subscription_type column from tbl_subscriptions
-- 3. Remove added indexes
--
-- ============================================================================

-- Start transaction for safety
BEGIN;

-- ============================================================================
-- STEP 1: Drop indexes first (to avoid dependency issues)
-- ============================================================================

-- Drop index on subscription_id in pivot table
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 
        FROM pg_indexes 
        WHERE schemaname = 'public' 
        AND tablename = 'tbl_course_subscriptions' 
        AND indexname = 'idx_course_subscription_subscription'
    ) THEN
        DROP INDEX IF EXISTS idx_course_subscription_subscription;
        RAISE NOTICE 'Dropped index idx_course_subscription_subscription';
    END IF;
END $$;

-- Drop index on course_id in pivot table
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 
        FROM pg_indexes 
        WHERE schemaname = 'public' 
        AND tablename = 'tbl_course_subscriptions' 
        AND indexname = 'idx_course_subscription_course'
    ) THEN
        DROP INDEX IF EXISTS idx_course_subscription_course;
        RAISE NOTICE 'Dropped index idx_course_subscription_course';
    END IF;
END $$;

-- Drop index on subscription dates
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 
        FROM pg_indexes 
        WHERE schemaname = 'public' 
        AND tablename = 'tbl_subscriptions' 
        AND indexname = 'idx_subscription_dates'
    ) THEN
        DROP INDEX IF EXISTS idx_subscription_dates;
        RAISE NOTICE 'Dropped index idx_subscription_dates';
    END IF;
END $$;

-- Drop composite index for access queries
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 
        FROM pg_indexes 
        WHERE schemaname = 'public' 
        AND tablename = 'tbl_subscriptions' 
        AND indexname = 'idx_subscription_access'
    ) THEN
        DROP INDEX IF EXISTS idx_subscription_access;
        RAISE NOTICE 'Dropped index idx_subscription_access';
    END IF;
END $$;

-- Drop index on subscription_type
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 
        FROM pg_indexes 
        WHERE schemaname = 'public' 
        AND tablename = 'tbl_subscriptions' 
        AND indexname = 'idx_subscription_type'
    ) THEN
        DROP INDEX IF EXISTS idx_subscription_type;
        RAISE NOTICE 'Dropped index idx_subscription_type';
    END IF;
END $$;

-- ============================================================================
-- STEP 2: Drop tbl_course_subscriptions table
-- ============================================================================

DO $$
BEGIN
    IF EXISTS (
        SELECT 1 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_course_subscriptions'
    ) THEN
        DROP TABLE IF EXISTS tbl_course_subscriptions CASCADE;
        RAISE NOTICE 'Dropped table tbl_course_subscriptions';
    ELSE
        RAISE NOTICE 'Table tbl_course_subscriptions does not exist';
    END IF;
END $$;

-- ============================================================================
-- STEP 3: Remove check constraint and subscription_type column
-- ============================================================================

-- Drop check constraint first
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 
        FROM information_schema.table_constraints 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_subscriptions' 
        AND constraint_name = 'chk_subscription_type'
    ) THEN
        ALTER TABLE tbl_subscriptions DROP CONSTRAINT IF EXISTS chk_subscription_type;
        RAISE NOTICE 'Dropped constraint chk_subscription_type';
    END IF;
END $$;

-- Drop subscription_type column
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_subscriptions' 
        AND column_name = 'subscription_type'
    ) THEN
        ALTER TABLE tbl_subscriptions DROP COLUMN IF EXISTS subscription_type;
        RAISE NOTICE 'Dropped column subscription_type from tbl_subscriptions';
    ELSE
        RAISE NOTICE 'Column subscription_type does not exist in tbl_subscriptions';
    END IF;
END $$;

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================

-- Verify subscription_type column was removed
DO $$
DECLARE
    column_exists INTEGER;
BEGIN
    SELECT COUNT(*) INTO column_exists
    FROM information_schema.columns 
    WHERE table_schema = 'public'
    AND table_name = 'tbl_subscriptions' 
    AND column_name = 'subscription_type';
    
    IF column_exists = 0 THEN
        RAISE NOTICE '✓ VERIFICATION: subscription_type column removed';
    ELSE
        RAISE WARNING '✗ VERIFICATION FAILED: subscription_type column still exists';
    END IF;
END $$;

-- Verify tbl_course_subscriptions table was removed
DO $$
DECLARE
    table_exists INTEGER;
BEGIN
    SELECT COUNT(*) INTO table_exists
    FROM information_schema.tables 
    WHERE table_schema = 'public'
    AND table_name = 'tbl_course_subscriptions';
    
    IF table_exists = 0 THEN
        RAISE NOTICE '✓ VERIFICATION: tbl_course_subscriptions table removed';
    ELSE
        RAISE WARNING '✗ VERIFICATION FAILED: tbl_course_subscriptions table still exists';
    END IF;
END $$;

-- ============================================================================
-- COMMIT TRANSACTION
-- ============================================================================

-- If everything looks good, commit the transaction
-- If there are any issues, rollback with: ROLLBACK;
COMMIT;

-- ============================================================================
-- ROLLBACK COMPLETED
-- ============================================================================
-- 
-- The database has been reverted to its state before the flexible subscription
-- system migration was applied.
-- 
-- WARNING: All data in tbl_course_subscriptions has been permanently deleted!
-- ============================================================================

