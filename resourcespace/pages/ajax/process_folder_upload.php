<?php
/**
 * Process folder uploads with nested file structure
 * Handles uploading a complete folder structure with files inside
 * Creates featured collections for folder hierarchy and resources for files
 */
include "../../include/boot.php";
$CSRF_enabled = false;
include "../../include/authenticate.php";

header('Content-Type: application/json');
ini_set('display_errors', 0);

try {
    if (!checkperm('c') && !checkperm('d') && !checkperm('h')) {
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        exit;
    }

    $action = getval('action', '', false);
    $parent_collection = (int) getval('parent_collection', 0, true);
    
    if ($action === 'upload_folder_structure') {
        global $userref;
        
        // Get the folder structure data from POST
        $folder_data = json_decode(file_get_contents('php://input'), true);
        
        if (!is_array($folder_data)) {
            echo json_encode(['success' => false, 'error' => 'Invalid folder data']);
            exit;
        }
        
        $root_folder_name = $folder_data['name'] ?? 'Upload';
        $structure = $folder_data['structure'] ?? [];
        
        // Create root folder
        $root_folder_id = create_folder_structure($root_folder_name, $parent_collection, $userref);
        
        if (!$root_folder_id) {
            echo json_encode(['success' => false, 'error' => 'Failed to create root folder']);
            exit;
        }
        
        // Process the structure recursively
        $result = [
            'success' => true,
            'root_folder_id' => $root_folder_id,
            'folders_created' => 0,
            'files_created' => 0,
            'structure_map' => []
        ];
        
        process_folder_structure($structure, $root_folder_id, $result);
        
        echo json_encode($result);
        exit;
    }
    
    elseif ($action === 'create_file_resource') {
        // Create a resource for a file in a folder
        $folder_id = (int) getval('folder_id', 0, true);
        $filename = trim(getval('filename', '', false));
        $file_path = trim(getval('file_path', '', false));
        $resource_type = (int) getval('resource_type', 0, true);
        
        if (!$folder_id || !$filename || !$file_path) {
            echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
            exit;
        }
        
        // Verify folder exists and is featured collection
        $folder_check = ps_query("SELECT ref, type FROM collection WHERE ref = ? LIMIT 1", ['i', $folder_id]);
        if (!is_array($folder_check) || count($folder_check) == 0 || $folder_check[0]['type'] != 3) {
            echo json_encode(['success' => false, 'error' => 'Invalid folder']);
            exit;
        }
        
        // Create resource
        $resource_ref = create_resource($resource_type, -1, $userref, 'Folder upload');
        
        if (!$resource_ref) {
            echo json_encode(['success' => false, 'error' => 'Failed to create resource']);
            exit;
        }
        
        // Add to folder
        add_resource_to_collection($resource_ref, $folder_id, false, '', $resource_type);
        
        // Store original filename
        global $filename_field;
        if ($filename_field) {
            update_field($resource_ref, $filename_field, $filename);
            // Also set title from filename
            update_field($resource_ref, 8, preg_replace('/\.[^.]*$/', '', $filename));
        }
        
        echo json_encode([
            'success' => true,
            'resource_id' => $resource_ref,
            'filename' => $filename
        ]);
        exit;
    }
    
    echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);
    
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Create a single folder (featured collection)
 */
function create_folder_structure($folder_name, $parent_ref = 0, $userref = 0) {
    $extra = ['type' => 3]; // COLLECTION_TYPE_FEATURED
    
    if ($parent_ref > 0) {
        $extra['parent'] = $parent_ref;
    }
    
    $folder_id = create_collection($userref, $folder_name, 0, 0, 0, false, $extra);
    
    if ($folder_id > 0) {
        // Force the type to 3
        ps_query("UPDATE collection SET type = 3, public = 1 WHERE ref = ?", ['i', $folder_id]);
        clear_query_cache("featured_collections");
        return $folder_id;
    }
    
    return false;
}

/**
 * Recursively process folder structure
 */
function process_folder_structure($structure, $parent_id, &$result, $path_prefix = '') {
    foreach ($structure as $item) {
        if (isset($item['type']) && $item['type'] === 'folder') {
            // Create subfolder
            $folder_id = create_folder_structure($item['name'], $parent_id);
            
            if ($folder_id) {
                $result['folders_created']++;
                
                // Build full path for this folder
                $current_path = $path_prefix ? $path_prefix . '/' . $item['name'] : $item['name'];
                
                // Store with full path as key
                $result['structure_map'][$current_path] = $folder_id;
                
                // Also store by full folder path for file matching
                $result['folder_paths'][$current_path] = [
                    'id' => $folder_id,
                    'name' => $item['name'],
                    'path' => $current_path
                ];
                
                // Process children recursively
                if (isset($item['children']) && is_array($item['children'])) {
                    process_folder_structure($item['children'], $folder_id, $result, $current_path);
                }
            }
        }
        elseif (isset($item['type']) && $item['type'] === 'file') {
            // File placeholder - will be created during metadata editing
            $result['files_created']++;
        }
    }
}
