<?php
/**
 * Wrapper for create_collection function
 * This file is included BEFORE collections_functions.php
 * to wrap the create_collection function
 */

// Store the original function
if (!function_exists('original_create_collection')) {
    
    // Include the original file to get the function
    require_once __DIR__ . '/../../../include/collections_functions.php';
    
    // Rename the original function
    if (function_exists('create_collection')) {
        // Can't rename in PHP, so we'll override it
    }
}

// Override create_collection
if (!function_exists('create_collection_with_id')) {
    
function create_collection_with_id($userid, $name, $allowchanges = 0, $cant_delete = 0, $ref = 0, $public = false, $extraparams = array())
{
    // Call original create_collection logic
    global $username,$anonymous_login,$rs_session, $anonymous_user_session_collection;
    if (($username == $anonymous_login && $anonymous_user_session_collection) || upload_share_active()) {
        $rs_session = get_rs_session_id(true);
    } else {
        $rs_session = "";
    }

    $setcolumns = array();
    $extracolopts = array("type", "keywords", "saved_search", "session_id", "description", "savedsearch", "parent", "thumbnail_selection_method");
    
    foreach ($extracolopts as $coloption) {
        if (isset($extraparams[$coloption])) {
            $setcolumns[$coloption] = $extraparams[$coloption];
        }
    }

    $setcolumns["name"]             = mb_strcut($name, 0, 100);
    $setcolumns["user"]             = is_numeric($userid) ? $userid : 0;
    $setcolumns["allow_changes"]    = $allowchanges;
    $setcolumns["cant_delete"]      = $cant_delete;
    $setcolumns["public"]           = $public ? COLLECTION_TYPE_PUBLIC : COLLECTION_TYPE_STANDARD;
    
    if ($ref != 0) {
        $setcolumns["ref"] = (int)$ref;
    }
    if (is_int_loose(trim($rs_session))) {
        $setcolumns["session_id"]   = $rs_session;
    }
    if ($public) {
        $setcolumns["type"]         = COLLECTION_TYPE_PUBLIC;
    }

    $insert_columns = array_keys($setcolumns);
    $insert_values  = array_values($setcolumns);

    $sql = "INSERT INTO collection
            (" . implode(",", $insert_columns) . ", created)
            VALUES
            (" . ps_param_insert(count($insert_values)) . ",NOW())";

    ps_query($sql, ps_param_fill($insert_values, 's'));

    $ref = sql_insert_id();
    
    // **APPEND ID IMMEDIATELY**
    $original_name = mb_strcut($name, 0, 100);
    if (!preg_match('/\s*\(ID-\d+\)$/', $original_name)) {
        $new_name = $original_name . " (ID-" . $ref . ")";
        sql_query("UPDATE collection SET name = '" . escape_check($new_name) . "' WHERE ref = " . (int)$ref);
    }
    
    index_collection($ref);
    clear_query_cache('collection_access' . $userid);

    return $ref;
}

}
?>