<?php

/**
 * Hook for upload_batch.php page
 * Collections can be created during upload
 */

/**
 * Hook: Pageheader
 * Runs after collection creation during upload
 */
function HookCollection_id_in_nameUpload_batchPageheader()
{
    // Check if a collection was just created
    global $collection_add;
    
    if (isset($collection_add) && is_numeric($collection_add) && $collection_add > 0) {
        // Get the collection name
        $current_name = sql_value(
            "SELECT name FROM collection WHERE ref = '" . escape_check($collection_add) . "'",
            ""
        );
        
        if (!empty($current_name) && !preg_match('/\s*\(ID-\d+\)$/', $current_name)) {
            // Append ID to name
            $new_name = trim($current_name) . " (ID-" . $collection_add . ")";
            
            sql_query(
                "UPDATE collection 
                 SET name = '" . escape_check($new_name) . "' 
                 WHERE ref = '" . escape_check($collection_add) . "'"
            );
        }
    }
}