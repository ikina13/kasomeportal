-- ============================================================================
-- QUICK START: Flexible Subscription System Migration
-- ============================================================================
-- This is a simplified version for quick deployment
-- For detailed explanations and safer version, see: 2025_01_12_add_flexible_subscription_system.sql
-- ============================================================================
-- NOTE: This version uses IF NOT EXISTS which works in MySQL 5.7.4+
-- For older MySQL versions, use the full migration script instead
-- ============================================================================

START TRANSACTION;

-- Step 1: Add subscription_type column (MySQL 5.7.4+)
-- For older versions, check column exists first manually
SET @dbname = DATABASE();
SET @tablename = 'tbl_subscriptions';
SET @columnname = 'subscription_type';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT ''Column subscription_type already exists'' AS message',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' ENUM(''all_courses'', ''specific_courses'') NOT NULL DEFAULT ''all_courses'' AFTER status')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Step 2: Create course subscriptions pivot table
CREATE TABLE IF NOT EXISTS tbl_course_subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subscription_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL COMMENT 'References tbl_practical_video.id',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pivot table linking subscriptions to specific courses';

-- Step 3: Set existing subscriptions to 'all_courses'
UPDATE tbl_subscriptions 
SET subscription_type = 'all_courses' 
WHERE subscription_type IS NULL OR subscription_type = '';

-- Step 4: Add performance indexes (MySQL 5.7.4+)
-- Check and create indexes safely
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
   WHERE table_schema = @dbname AND table_name = @tablename AND index_name = 'idx_subscription_type') > 0,
  'SELECT ''Index idx_subscription_type already exists'' AS message',
  CONCAT('CREATE INDEX idx_subscription_type ON ', @tablename, '(subscription_type)')
));
PREPARE createIndexIfNotExists FROM @preparedStatement;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
   WHERE table_schema = @dbname AND table_name = @tablename AND index_name = 'idx_status_type') > 0,
  'SELECT ''Index idx_status_type already exists'' AS message',
  CONCAT('CREATE INDEX idx_status_type ON ', @tablename, '(status, subscription_type)')
));
PREPARE createIndexIfNotExists FROM @preparedStatement;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;

SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
   WHERE table_schema = @dbname AND table_name = @tablename AND index_name = 'idx_user_active') > 0,
  'SELECT ''Index idx_user_active already exists'' AS message',
  CONCAT('CREATE INDEX idx_user_active ON ', @tablename, '(user_id, status, start_date, end_date)')
));
PREPARE createIndexIfNotExists FROM @preparedStatement;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;

COMMIT;

-- Verification Queries
SELECT 'Migration completed successfully!' AS status;
SELECT 
    COUNT(*) AS total_subscriptions, 
    SUM(CASE WHEN subscription_type = 'all_courses' THEN 1 ELSE 0 END) AS all_courses,
    SUM(CASE WHEN subscription_type = 'specific_courses' THEN 1 ELSE 0 END) AS specific_courses
FROM tbl_subscriptions;

