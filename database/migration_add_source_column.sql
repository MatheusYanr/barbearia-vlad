-- Migration: Add 'source' column to reviews table
-- This column distinguishes between manual reviews (added via admin panel)
-- and reviews imported from Google Places API.
--
-- Run this ONCE on the production database via phpMyAdmin.

ALTER TABLE reviews
ADD COLUMN source VARCHAR(20) NOT NULL DEFAULT 'manual'
AFTER is_active;

-- Mark all existing reviews as 'manual' (they were created via admin panel)
UPDATE reviews SET source = 'manual' WHERE source = '';
