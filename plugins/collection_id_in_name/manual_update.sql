-- Manual SQL Update for Collection IDs
-- Run this directly in MySQL/phpMyAdmin if the PHP scripts timeout

-- Update all collections that don't have (ID-XXX) suffix
UPDATE collection 
SET name = CONCAT(name, ' (ID-', ref, ')')
WHERE name NOT LIKE '%(ID-%';

-- Verify the update
SELECT ref, name FROM collection ORDER BY ref;