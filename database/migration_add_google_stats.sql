-- Migration: Add Google Stats initial settings
-- This helps the frontend display default rating info until the python/php cron runs

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('google_rating', '4.9'),
('google_total_ratings', '120');

