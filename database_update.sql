-- Add cover_image column to books table
-- Run this SQL in your phpMyAdmin or MySQL client

ALTER TABLE books 
ADD COLUMN cover_image VARCHAR(255) NULL 
AFTER published_year;

-- This adds a new column to store the book cover image filename
-- The column is nullable, so existing books without images will work fine
