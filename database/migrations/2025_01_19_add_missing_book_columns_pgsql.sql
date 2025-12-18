-- ============================================================================
-- PostgreSQL Migration Script: Add Missing Book Columns
-- Date: 2025-01-19
-- Database: PostgreSQL
-- Description: Adds missing columns to tbl_books (is_donation_enabled, donation_min_amount, etc.)
-- ============================================================================
-- 
-- IMPORTANT: Backup your database before running this script!
-- 
-- Run this script in your PostgreSQL database
-- ============================================================================

-- Start transaction for safety
BEGIN;

-- Add is_donation_enabled if it doesn't exist
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_books' 
        AND column_name = 'is_donation_enabled'
    ) THEN
        ALTER TABLE tbl_books 
        ADD COLUMN is_donation_enabled BOOLEAN DEFAULT false;
        RAISE NOTICE 'Added is_donation_enabled column to tbl_books';
    ELSE
        RAISE NOTICE 'Column is_donation_enabled already exists in tbl_books';
    END IF;
END $$;

-- Add donation_min_amount if it doesn't exist
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_books' 
        AND column_name = 'donation_min_amount'
    ) THEN
        ALTER TABLE tbl_books 
        ADD COLUMN donation_min_amount NUMERIC DEFAULT 0;
        RAISE NOTICE 'Added donation_min_amount column to tbl_books';
    ELSE
        RAISE NOTICE 'Column donation_min_amount already exists in tbl_books';
    END IF;
END $$;

-- Add download_url if it doesn't exist
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_books' 
        AND column_name = 'download_url'
    ) THEN
        ALTER TABLE tbl_books 
        ADD COLUMN download_url TEXT;
        RAISE NOTICE 'Added download_url column to tbl_books';
    ELSE
        RAISE NOTICE 'Column download_url already exists in tbl_books';
    END IF;
END $$;

-- Add file_size if it doesn't exist
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_books' 
        AND column_name = 'file_size'
    ) THEN
        ALTER TABLE tbl_books 
        ADD COLUMN file_size BIGINT;
        RAISE NOTICE 'Added file_size column to tbl_books';
    ELSE
        RAISE NOTICE 'Column file_size already exists in tbl_books';
    END IF;
END $$;

-- Add file_type if it doesn't exist
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_books' 
        AND column_name = 'file_type'
    ) THEN
        ALTER TABLE tbl_books 
        ADD COLUMN file_type VARCHAR(50);
        RAISE NOTICE 'Added file_type column to tbl_books';
    ELSE
        RAISE NOTICE 'Column file_type already exists in tbl_books';
    END IF;
END $$;

-- Add author_id if it doesn't exist
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_books' 
        AND column_name = 'author_id'
    ) THEN
        ALTER TABLE tbl_books 
        ADD COLUMN author_id BIGINT;
        RAISE NOTICE 'Added author_id column to tbl_books';
    ELSE
        RAISE NOTICE 'Column author_id already exists in tbl_books';
    END IF;
END $$;

-- Add foreign key constraint for author_id if it doesn't exist
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.table_constraints 
        WHERE constraint_schema = 'public' 
        AND table_name = 'tbl_books' 
        AND constraint_name = 'tbl_books_author_id_foreign'
    ) THEN
        ALTER TABLE tbl_books 
        ADD CONSTRAINT tbl_books_author_id_foreign 
        FOREIGN KEY (author_id) 
        REFERENCES tbl_author(id) 
        ON DELETE SET NULL;
        RAISE NOTICE 'Added foreign key constraint on author_id';
    ELSE
        RAISE NOTICE 'Foreign key constraint on author_id already exists';
    END IF;
END $$;

-- Commit transaction
COMMIT;

-- ============================================================================
-- Verification Query (run separately to check)
-- ============================================================================
-- SELECT column_name, data_type, is_nullable, column_default
-- FROM information_schema.columns 
-- WHERE table_name = 'tbl_books' 
-- AND column_name IN ('is_donation_enabled', 'donation_min_amount', 'download_url', 'file_size', 'file_type', 'author_id')
-- ORDER BY column_name;
-- ============================================================================

