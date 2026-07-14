-- Uninstall the collection ID trigger
-- Run this if you want to disable the automatic ID appending

DROP TRIGGER IF EXISTS collection_append_id_before;

SELECT 'Trigger uninstalled successfully!' AS status;