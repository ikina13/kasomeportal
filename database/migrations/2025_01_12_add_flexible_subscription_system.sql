-- ============================================================================
-- SQL Migration Script: Flexible Subscription System
-- Date: 2025-01-12
-- Description: Adds support for course-specific subscriptions and subscription types
-- ============================================================================
-- 
-- IMPORTANT: Backup your database before running this script!
-- 
-- This script will:
-- 1. Add subscription_type column to tbl_subscriptions
-- 2. Create tbl_course_subscriptions pivot table
-- 3. Migrate existing data
-- 4. Add necessary indexes
--
-- Run this script in your production database
-- ============================================================================

-- Start transaction for safety
START TRANSACTION;

-- ============================================================================
-- STEP 1: Add subscription_type column to tbl_subscriptions
-- ============================================================================

-- Check if column exists before adding
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tbl_subscriptions' 
    AND COLUMN_NAME = 'subscription_type'
);

-- Add subscription_type column if it doesn't exist
SET @sql = IF(@column_exists = 0,
    'ALTER TABLE tbl_subscriptions 
     ADD COLUMN subscription_type ENUM(''all_courses'', ''specific_courses'') 
     NOT NULL DEFAULT ''all_courses'' 
     AFTER status',
    'SELECT ''Column subscription_type already exists'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- STEP 2: Create tbl_course_subscriptions pivot table
-- ============================================================================

-- Check if table exists
SET @table_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tbl_course_subscriptions'
);

-- Create table if it doesn't exist
SET @sql = IF(@table_exists = 0,
    'CREATE TABLE tbl_course_subscriptions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        subscription_id BIGINT UNSIGNED NOT NULL,
        course_id BIGINT UNSIGNED NOT NULL COMMENT ''References tbl_practical_video.id'',
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        created_by BIGINT UNSIGNED NULL,
        INDEX idx_subscription_id (subscription_id),
        INDEX idx_course_id (course_id),
        INDEX idx_subscription_course (subscription_id, course_id),
        UNIQUE KEY unique_subscription_course (subscription_id, course_id),
        CONSTRAINT fk_course_subscription_subscription 
            FOREIGN KEY (subscription_id) 
            REFERENCES tbl_subscriptions(id) 
            ON DELETE CASCADE
            ON UPDATE CASCADE,
        CONSTRAINT fk_course_subscription_course 
            FOREIGN KEY (course_id) 
            REFERENCES tbl_practical_video(id) 
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=''Pivot table linking subscriptions to specific courses''',
    'SELECT ''Table tbl_course_subscriptions already exists'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- STEP 3: Migrate existing data
-- ============================================================================

-- Set all existing subscriptions to 'all_courses' type (maintains backward compatibility)
UPDATE tbl_subscriptions 
SET subscription_type = 'all_courses' 
WHERE subscription_type IS NULL OR subscription_type = '';

-- ============================================================================
-- STEP 4: Add indexes for performance (if they don't exist)
-- ============================================================================

-- Add index on subscription_type for faster filtering
SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tbl_subscriptions' 
    AND INDEX_NAME = 'idx_subscription_type'
);

SET @sql = IF(@index_exists = 0,
    'CREATE INDEX idx_subscription_type ON tbl_subscriptions(subscription_type)',
    'SELECT ''Index idx_subscription_type already exists'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add composite index on status and subscription_type for access checks
SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tbl_subscriptions' 
    AND INDEX_NAME = 'idx_status_type'
);

SET @sql = IF(@index_exists = 0,
    'CREATE INDEX idx_status_type ON tbl_subscriptions(status, subscription_type)',
    'SELECT ''Index idx_status_type already exists'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add composite index on user_id, status, and dates for faster access queries
SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tbl_subscriptions' 
    AND INDEX_NAME = 'idx_user_active'
);

SET @sql = IF(@index_exists = 0,
    'CREATE INDEX idx_user_active ON tbl_subscriptions(user_id, status, start_date, end_date)',
    'SELECT ''Index idx_user_active already exists'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- VERIFICATION QUERIES (for testing)
-- ============================================================================

-- Verify subscription_type column was added
SELECT 
    'Verification: subscription_type column' AS check_name,
    CASE 
        WHEN COUNT(*) > 0 THEN 'PASSED'
        ELSE 'FAILED'
    END AS status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tbl_subscriptions' 
AND COLUMN_NAME = 'subscription_type';

-- Verify tbl_course_subscriptions table was created
SELECT 
    'Verification: tbl_course_subscriptions table' AS check_name,
    CASE 
        WHEN COUNT(*) > 0 THEN 'PASSED'
        ELSE 'FAILED'
    END AS status
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'tbl_course_subscriptions';

-- Verify existing subscriptions were migrated
SELECT 
    'Verification: Data migration' AS check_name,
    COUNT(*) AS subscriptions_with_type,
    SUM(CASE WHEN subscription_type = 'all_courses' THEN 1 ELSE 0 END) AS all_courses_count,
    SUM(CASE WHEN subscription_type = 'specific_courses' THEN 1 ELSE 0 END) AS specific_courses_count
FROM tbl_subscriptions;

-- ============================================================================
-- COMMIT TRANSACTION
-- ============================================================================

-- If everything looks good, commit the transaction
-- If there are any issues, rollback with: ROLLBACK;
COMMIT;

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

