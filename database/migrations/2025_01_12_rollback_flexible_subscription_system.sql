-- ============================================================================
-- SQL Rollback Script: Flexible Subscription System
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
START TRANSACTION;

-- ============================================================================
-- STEP 1: Drop foreign key constraints and indexes from tbl_course_subscriptions
-- ============================================================================

-- Check if table exists before dropping
SET @table_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tbl_course_subscriptions'
);

SET @sql = IF(@table_exists > 0,
    'DROP TABLE IF EXISTS tbl_course_subscriptions',
    'SELECT ''Table tbl_course_subscriptions does not exist'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- STEP 2: Remove indexes added in migration
-- ============================================================================

-- Remove idx_subscription_type index
SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tbl_subscriptions' 
    AND INDEX_NAME = 'idx_subscription_type'
);

SET @sql = IF(@index_exists > 0,
    'ALTER TABLE tbl_subscriptions DROP INDEX idx_subscription_type',
    'SELECT ''Index idx_subscription_type does not exist'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Remove idx_status_type index
SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tbl_subscriptions' 
    AND INDEX_NAME = 'idx_status_type'
);

SET @sql = IF(@index_exists > 0,
    'ALTER TABLE tbl_subscriptions DROP INDEX idx_status_type',
    'SELECT ''Index idx_status_type does not exist'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Remove idx_user_active index
SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tbl_subscriptions' 
    AND INDEX_NAME = 'idx_user_active'
);

SET @sql = IF(@index_exists > 0,
    'ALTER TABLE tbl_subscriptions DROP INDEX idx_user_active',
    'SELECT ''Index idx_user_active does not exist'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- STEP 3: Remove subscription_type column
-- ============================================================================

-- Check if column exists before removing
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tbl_subscriptions' 
    AND COLUMN_NAME = 'subscription_type'
);

SET @sql = IF(@column_exists > 0,
    'ALTER TABLE tbl_subscriptions DROP COLUMN subscription_type',
    'SELECT ''Column subscription_type does not exist'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- VERIFICATION QUERIES (for testing)
-- ============================================================================

-- Verify subscription_type column was removed
SELECT 
    'Verification: subscription_type column removed' AS check_name,
    CASE 
        WHEN COUNT(*) = 0 THEN 'PASSED'
        ELSE 'FAILED - Column still exists'
    END AS status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tbl_subscriptions' 
AND COLUMN_NAME = 'subscription_type';

-- Verify tbl_course_subscriptions table was removed
SELECT 
    'Verification: tbl_course_subscriptions table removed' AS check_name,
    CASE 
        WHEN COUNT(*) = 0 THEN 'PASSED'
        ELSE 'FAILED - Table still exists'
    END AS status
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tbl_course_subscriptions';

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
-- ============================================================================

