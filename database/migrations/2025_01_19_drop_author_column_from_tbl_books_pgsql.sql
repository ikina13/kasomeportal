-- ============================================================================
-- PostgreSQL Migration Script: Drop author column from tbl_books
-- Date: 2025-01-19
-- Database: PostgreSQL
-- Description: Removes the 'author' character column from tbl_books
-- ============================================================================
-- 
-- IMPORTANT: Backup your database before running this script!
-- 
-- This script will:
-- 1. Verify author_id column exists
-- 2. Drop the 'author' column from tbl_books if it exists
--
-- Run this script in your PostgreSQL database
-- ============================================================================

-- Start transaction for safety
BEGIN;

-- ============================================================================
-- STEP 1: Verify author_id exists (we should use author_id, not author)
-- ============================================================================

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_books' 
        AND column_name = 'author_id'
    ) THEN
        RAISE EXCEPTION 'author_id column does not exist in tbl_books. Please add author_id first before removing author column.';
    END IF;
END $$;

-- ============================================================================
-- STEP 2: Drop the author column
-- ============================================================================

DO $$
BEGIN
    IF EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_books' 
        AND column_name = 'author'
    ) THEN
        ALTER TABLE tbl_books DROP COLUMN author;
        RAISE NOTICE 'Dropped author column from tbl_books';
    ELSE
        RAISE NOTICE 'Column author does not exist in tbl_books (already removed)';
    END IF;
END $$;

-- ============================================================================
-- STEP 3: Show summary
-- ============================================================================

DO $$
DECLARE
    total_books INTEGER;
    books_with_author_id INTEGER;
BEGIN
    SELECT COUNT(*) INTO total_books FROM tbl_books;
    SELECT COUNT(*) INTO books_with_author_id FROM tbl_books WHERE author_id IS NOT NULL;
    
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Migration Summary:';
    RAISE NOTICE 'Total books: %', total_books;
    RAISE NOTICE 'Books with author_id: %', books_with_author_id;
    RAISE NOTICE 'The author column has been removed.';
    RAISE NOTICE 'All books now use author_id only.';
    RAISE NOTICE '========================================';
END $$;

-- Commit transaction
COMMIT;

-- ============================================================================
-- Notes:
-- ============================================================================
-- 1. The 'author' column has been permanently removed
-- 2. All book entries now use author_id to reference tbl_author
-- 3. If you need to see the author name, join with tbl_author table
-- ============================================================================

