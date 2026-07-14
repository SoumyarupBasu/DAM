<?php

/**
 * Hook for collection_manage.php
 * Appends ID immediately after collection creation
 */

/**
 * Hook: Beforeheader
 * Runs BEFORE the page header, right after collection is created
 */
function HookCollection_id_in_nameCollection_manageBeforeheader()
{
    global $new;
    
    // Check if a new collection was just created
    if (isset($new) && is_numeric($new) && $new > 0) {
        // Get the current name
        $current_name = sql_value("SELECT name FROM collection WHERE ref = " . (int)$new, "");
        
        // Only append if ID is not already there
        if (!empty($current_name) && strpos($current_name, "(ID-") === false) {
            $new_name = $current_name . " (ID-" . $new . ")";
            sql_query("UPDATE collection SET name = '" . escape_check($new_name) . "' WHERE ref = " . (int)$new);
        }
    }
}