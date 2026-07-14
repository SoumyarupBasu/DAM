-- MySQL Event to automatically append IDs to new collections
-- Runs every 5 seconds and updates collections created in the last minute

-- Enable event scheduler
SET GLOBAL event_scheduler = ON;

-- Drop existing event if it exists
DROP EVENT IF EXISTS append_collection_ids;

-- Create the event
CREATE EVENT append_collection_ids
ON SCHEDULE EVERY 5 SECOND
DO
  UPDATE collection 
  SET name = CONCAT(name, ' (ID-', ref, ')')
  WHERE name NOT LIKE '%(ID-%'
  AND created >= DATE_SUB(NOW(), INTERVAL 1 MINUTE);

SELECT 'Event scheduler installed and running!' AS status;