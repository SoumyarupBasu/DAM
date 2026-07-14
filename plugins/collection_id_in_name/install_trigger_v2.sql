-- Simple and reliable trigger for collection ID appending
-- Version 2 - Simplified approach

DELIMITER $$

DROP TRIGGER IF EXISTS collection_id_append$$

CREATE TRIGGER collection_id_append
BEFORE INSERT ON collection
FOR EACH ROW
BEGIN
    -- Only append if name doesn't already contain (ID-
    IF NEW.name NOT LIKE '%(ID-%' THEN
        -- If ref is set, use it; otherwise MySQL will auto-assign
        IF NEW.ref IS NOT NULL AND NEW.ref > 0 THEN
            SET NEW.name = CONCAT(NEW.name, ' (ID-', NEW.ref, ')');
        END IF;
    END IF;
END$$

DELIMITER ;

SELECT 'Trigger v2 installed!' AS status;