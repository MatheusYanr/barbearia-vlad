-- Migration: Fix accents in existing settings data
-- Run this once on the production database to correct "Sabados" to "Sábados"
-- and ensure all settings values have proper Portuguese accents.

UPDATE settings
SET setting_value = 'Sábados - 9h às 12h e das 13h30 às 17h'
WHERE setting_key = 'saturday_hours'
  AND setting_value LIKE '%Sabados%';

UPDATE settings
SET setting_value = 'Segunda a Sexta - 9h às 12h e das 13h30 às 19h30'
WHERE setting_key = 'weekday_hours'
  AND setting_value LIKE '%as 12h%';

UPDATE settings
SET setting_value = 'Nosso objetivo é proporcionar uma experiência única, combinando técnicas tradicionais com tendências atuais, sempre com conforto e qualidade.'
WHERE setting_key = 'about_text'
  AND setting_value LIKE '%experiencia unica%';
