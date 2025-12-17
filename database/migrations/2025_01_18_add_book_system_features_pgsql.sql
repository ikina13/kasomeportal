-- ============================================================================
-- PostgreSQL Migration Script: Book System Features
-- Date: 2025-01-18
-- Database: PostgreSQL
-- Description: Adds book management features including download tracking, tokens, and author linking
-- ============================================================================
-- 
-- IMPORTANT: Backup your database before running this script!
-- 
-- This script will:
-- 1. Add missing fields to tbl_books (author_id, download_url, file_size, etc.)
-- 2. Add download token fields to tbl_book_purchases
-- 3. Create tbl_book_downloads table for detailed tracking
-- 4. Update tbl_books_payment to link to purchases
-- 5. Add necessary indexes for performance
--
-- Run this script in your PostgreSQL database
-- ============================================================================

-- Start transaction for safety
BEGIN;

-- ============================================================================
-- STEP 1: Update tbl_books table
-- ============================================================================

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
        ADD COLUMN author_id BIGINT REFERENCES tbl_author(id);
        RAISE NOTICE 'Added author_id column to tbl_books';
    ELSE
        RAISE NOTICE 'Column author_id already exists in tbl_books';
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

-- Add preview_url if it doesn't exist
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_books' 
        AND column_name = 'preview_url'
    ) THEN
        ALTER TABLE tbl_books 
        ADD COLUMN preview_url TEXT;
        RAISE NOTICE 'Added preview_url column to tbl_books';
    ELSE
        RAISE NOTICE 'Column preview_url already exists in tbl_books';
    END IF;
END $$;

-- ============================================================================
-- STEP 2: Update tbl_book_purchases table
-- ============================================================================

-- Add download_token if it doesn't exist
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_book_purchases' 
        AND column_name = 'download_token'
    ) THEN
        ALTER TABLE tbl_book_purchases 
        ADD COLUMN download_token VARCHAR(255);
        RAISE NOTICE 'Added download_token column to tbl_book_purchases';
    ELSE
        RAISE NOTICE 'Column download_token already exists in tbl_book_purchases';
    END IF;
END $$;

-- Add unique constraint to download_token if column exists
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_book_purchases' 
        AND column_name = 'download_token'
    ) THEN
        -- Drop existing constraint if it exists
        IF EXISTS (
            SELECT 1 FROM pg_constraint 
            WHERE conname = 'tbl_book_purchases_download_token_unique'
        ) THEN
            ALTER TABLE tbl_book_purchases DROP CONSTRAINT tbl_book_purchases_download_token_unique;
        END IF;
        
        -- Add unique constraint
        CREATE UNIQUE INDEX IF NOT EXISTS idx_book_purchases_download_token 
        ON tbl_book_purchases(download_token) 
        WHERE download_token IS NOT NULL;
        
        RAISE NOTICE 'Added unique index on download_token';
    END IF;
END $$;

-- Add token_expires_at if it doesn't exist
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_book_purchases' 
        AND column_name = 'token_expires_at'
    ) THEN
        ALTER TABLE tbl_book_purchases 
        ADD COLUMN token_expires_at TIMESTAMP;
        RAISE NOTICE 'Added token_expires_at column to tbl_book_purchases';
    ELSE
        RAISE NOTICE 'Column token_expires_at already exists in tbl_book_purchases';
    END IF;
END $$;

-- Add purchase_type if it doesn't exist
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_book_purchases' 
        AND column_name = 'purchase_type'
    ) THEN
        ALTER TABLE tbl_book_purchases 
        ADD COLUMN purchase_type VARCHAR(50) DEFAULT 'purchase';
        RAISE NOTICE 'Added purchase_type column to tbl_book_purchases';
    ELSE
        RAISE NOTICE 'Column purchase_type already exists in tbl_book_purchases';
    END IF;
END $$;

-- Ensure max_downloads has a default value if it doesn't exist or is NULL
UPDATE tbl_book_purchases 
SET max_downloads = 5 
WHERE max_downloads IS NULL;

-- ============================================================================
-- STEP 3: Create tbl_book_downloads table
-- ============================================================================

CREATE TABLE IF NOT EXISTS tbl_book_downloads (
    id BIGSERIAL PRIMARY KEY,
    book_purchase_id BIGINT NOT NULL REFERENCES tbl_book_purchases(id) ON DELETE CASCADE,
    user_id BIGINT NOT NULL REFERENCES tbl_users(id) ON DELETE CASCADE,
    book_id BIGINT NOT NULL REFERENCES tbl_books(id) ON DELETE CASCADE,
    download_token VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    file_size BIGINT,
    download_status VARCHAR(50) DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP
);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_book_downloads_purchase ON tbl_book_downloads(book_purchase_id);
CREATE INDEX IF NOT EXISTS idx_book_downloads_user ON tbl_book_downloads(user_id);
CREATE INDEX IF NOT EXISTS idx_book_downloads_book ON tbl_book_downloads(book_id);
CREATE INDEX IF NOT EXISTS idx_book_downloads_token ON tbl_book_downloads(download_token);
CREATE INDEX IF NOT EXISTS idx_book_downloads_created_at ON tbl_book_downloads(created_at);

-- ============================================================================
-- STEP 4: Update tbl_books_payment table
-- ============================================================================

-- Add book_purchase_id if it doesn't exist
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'tbl_books_payment' 
        AND column_name = 'book_purchase_id'
    ) THEN
        ALTER TABLE tbl_books_payment 
        ADD COLUMN book_purchase_id BIGINT REFERENCES tbl_book_purchases(id);
        RAISE NOTICE 'Added book_purchase_id column to tbl_books_payment';
    ELSE
        RAISE NOTICE 'Column book_purchase_id already exists in tbl_books_payment';
    END IF;
END $$;

-- Create index on book_purchase_id
CREATE INDEX IF NOT EXISTS idx_books_payment_purchase ON tbl_books_payment(book_purchase_id);

-- ============================================================================
-- STEP 5: Add indexes to tbl_books for performance
-- ============================================================================

CREATE INDEX IF NOT EXISTS idx_books_author_id ON tbl_books(author_id);
CREATE INDEX IF NOT EXISTS idx_books_language ON tbl_books(language);
CREATE INDEX IF NOT EXISTS idx_books_is_active ON tbl_books(is_active);
CREATE INDEX IF NOT EXISTS idx_books_created_at ON tbl_books(created_at);

-- ============================================================================
-- STEP 6: Add indexes to tbl_book_purchases for performance
-- ============================================================================

CREATE INDEX IF NOT EXISTS idx_book_purchases_user_id ON tbl_book_purchases(user_id);
CREATE INDEX IF NOT EXISTS idx_book_purchases_book_id ON tbl_book_purchases(book_id);
CREATE INDEX IF NOT EXISTS idx_book_purchases_status ON tbl_book_purchases(status);
CREATE INDEX IF NOT EXISTS idx_book_purchases_purchased_at ON tbl_book_purchases(purchased_at);

-- Commit transaction
COMMIT;

-- ============================================================================
-- Migration completed successfully!
-- ============================================================================

