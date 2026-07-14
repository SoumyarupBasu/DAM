<?php
/**
 * Handle folder upload - creates featured collection structure.
 */
include "../../include/boot.php";

$CSRF_enabled = false;
include "../../include/authenticate.php";

header('Content-Type: application/json');
ini_set('display_errors', 0);

try {
    if (!checkperm('c') && !checkperm('h')) {
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        exit;
    }

    $action     = getval('action', '', false);
    $parent_ref = (int) getval('parent_ref', 0, true);

    if ($action === 'create_folder') {
        $folder_name = trim(getval('folder_name', '', false));
        if (!$folder_name) {
            echo json_encode(['success' => false, 'error' => 'Missing folder_name']);
            exit;
        }

        global $userref;

        // Build the extra params — use type=3 (COLLECTION_TYPE_FEATURED) directly
        $extra = ['type' => 3]; // COLLECTION_TYPE_FEATURED
        if ($parent_ref > 0) {
            $extra['parent'] = $parent_ref;
        }

        // Pass $public=false so create_collection doesn't override our type
        $new_ref = create_collection($userref, $folder_name, 0, 0, 0, false, $extra);

        if ($new_ref > 0) {
            // Force the type to 3 in case create_collection changed it
            ps_query("UPDATE collection SET type = 3, public = 1 WHERE ref = ?", ['i', $new_ref]);
            clear_query_cache("featured_collections");
            echo json_encode(['success' => true, 'ref' => $new_ref]);
        } else {
            echo json_encode(['success' => false, 'error' => 'create_collection returned 0']);
        }
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
