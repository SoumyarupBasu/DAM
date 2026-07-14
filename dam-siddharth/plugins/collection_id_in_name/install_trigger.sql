-- MySQL Trigger to automatically append collection ID to name
-- Uses BEFORE INSERT to modify the name before it's saved

DELIMITER $$

DROP TRIGGER IF EXISTS collection_append_id_before$$

CREATE TRIGGER collection_append_id_before
BEFORE INSERT ON collection
FOR EACH ROW
BEGIN
    -- Declare variable to hold the next auto-increment value
    DECLARE next_id INT;
    
    -- Get the next auto-increment value
    SELECT AUTO_INCREMENT INTO next_id
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'collection';
    
    -- If ref is not set, use the auto-increment value
    IF NEW.ref IS NULL OR NEW.ref = 0 THEN
        SET NEW.ref = next_id;
    END IF;
    
    -- Only append if the name doesn't already contain (ID-
    IF NEW.name NOT LIKE '%(ID-%' THEN
        SET NEW.name = CONCAT(NEW.name, ' (ID-', NEW.ref, ')');
    END IF;
END$$

DELIMITER ;

-- Test the trigger
SELECT 'Trigger installed successfully!' AS status;