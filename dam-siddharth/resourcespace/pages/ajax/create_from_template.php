<?php
include "../../include/boot.php";

// Check if this is an AJAX request to bypass CSRF for this endpoint
$ajax = getval('ajax', 0, true);
if ($ajax) {
    $CSRF_enabled = false;
}

include "../../include/authenticate.php";

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display, but log

// Check permissions
if (!checkperm('c')) {
    echo json_encode(['success' => false, 'error' => 'Permission denied - you need collection creation permission']);
    exit;
}

$template_ref = getval('template_ref', 0, true);
$template_type = getval('template_type', 'complete');

if ($template_ref <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid template reference: ' . $template_ref]);
    exit;
}

// Get the template collection
$template = get_collection($template_ref);
if (!$template) {
    echo json_encode(['success' => false, 'error' => 'Template collection not found for ref: ' . $template_ref]);
    exit;
}

// Check if user can write to this collection
if (!collection_writeable($template_ref)) {
    echo json_encode(['success' => false, 'error' => 'You do not have permission to add collections to this parent']);
    exit;
}

// Define the structures based on template type
$structure = [];

// Reusable State 1 structure (shared across template types)
$state_structure = [
        ['name' => 'State 1', 'children' => [
            ['name' => 'Video', 'children' => [
                ['name' => 'Video Topic', 'children' => [
                    ['name' => 'Script', 'children' => [
                        ['name' => 'Source File', 'children' => []],
                        ['name' => 'Output File', 'children' => []]
                    ]],
                    ['name' => 'Audio', 'children' => [
                        ['name' => 'Source File', 'children' => []],
                        ['name' => 'Output File', 'children' => []]
                    ]],
                    ['name' => 'Video Source Files', 'children' => [
                        ['name' => 'Flash Files', 'children' => []],
                        ['name' => 'AE Files', 'children' => []],
                        ['name' => 'AI Files', 'children' => []],
                        ['name' => 'Premier Source Files', 'children' => []],
                        ['name' => 'Graphics Files', 'children' => []]
                    ]],
                    ['name' => 'Final Video', 'children' => [
                        ['name' => 'High Resolution', 'children' => []],
                        ['name' => 'Low Resolution', 'children' => []]
                    ]],
                    ['name' => 'Thumbnail', 'children' => [
                        ['name' => 'Source File', 'children' => []],
                        ['name' => 'Thumbnail Image', 'children' => []]
                    ]]
                ]]
            ]],
            ['name' => 'Worksheet', 'children' => [
                ['name' => 'Worksheet Topic', 'children' => [
                    ['name' => 'PDF', 'children' => []],
                    ['name' => 'AI Source Files', 'children' => []]
                ]]
            ]],
            ['name' => 'Lesson Plan', 'children' => [
                ['name' => 'Lesson Plan Topic', 'children' => [
                    ['name' => 'PDF', 'children' => []],
                    ['name' => 'AI Source Files', 'children' => []]
                ]]
            ]],
            ['name' => 'Assessment', 'children' => [
                ['name' => 'Assessment Topic', 'children' => [
                    ['name' => 'PDF', 'children' => []],
                    ['name' => 'AI Source Files', 'children' => []]
                ]]
            ]]
        ]]
];

if ($template_type === 'complete') {
    // Complete template - Grade 1-8, each with full State 1 structure
    $structure = [
        ['name' => 'Grade 1', 'children' => $state_structure],
        ['name' => 'Grade 2', 'children' => $state_structure],
        ['name' => 'Grade 3', 'children' => $state_structure],
        ['name' => 'Grade 4', 'children' => $state_structure],
        ['name' => 'Grade 5', 'children' => $state_structure],
        ['name' => 'Grade 6', 'children' => $state_structure],
        ['name' => 'Grade 7', 'children' => $state_structure],
        ['name' => 'Grade 8', 'children' => $state_structure]
    ];
} elseif ($template_type === 'grade') {
    // Grade level template - State 1 with full children
    $structure = $state_structure;
} elseif ($template_type === 'state') {
    // State level template - Just the lesson components
    $structure = [
        ['name' => 'Video', 'children' => [
            ['name' => 'Video Topic', 'children' => [
                ['name' => 'Script', 'children' => [
                    ['name' => 'Source File', 'children' => []],
                    ['name' => 'Output File', 'children' => []]
                ]],
                ['name' => 'Audio', 'children' => [
                    ['name' => 'Source File', 'children' => []],
                    ['name' => 'Output File', 'children' => []]
                ]],
                ['name' => 'Video Source Files', 'children' => [
                    ['name' => 'Flash Files', 'children' => []],
                    ['name' => 'AE Files', 'children' => []],
                    ['name' => 'AI Files', 'children' => []],
                    ['name' => 'Premier Source Files', 'children' => []],
                    ['name' => 'Graphics Files', 'children' => []]
                ]],
                ['name' => 'Final Video', 'children' => [
                    ['name' => 'High Resolution', 'children' => []],
                    ['name' => 'Low Resolution', 'children' => []]
                ]],
                ['name' => 'Thumbnail', 'children' => [
                    ['name' => 'Source File', 'children' => []],
                    ['name' => 'Thumbnail Image', 'children' => []]
                ]]
            ]]
        ]],
        ['name' => 'Worksheet', 'children' => [
            ['name' => 'Worksheet Topic', 'children' => [
                ['name' => 'PDF', 'children' => []],
                ['name' => 'AI Source Files', 'children' => []]
            ]]
        ]],
        ['name' => 'Lesson Plan', 'children' => [
            ['name' => 'Lesson Plan Topic', 'children' => [
                ['name' => 'PDF', 'children' => []],
                ['name' => 'AI Source Files', 'children' => []]
            ]]
        ]],
        ['name' => 'Assessment', 'children' => [
            ['name' => 'Assessment Topic', 'children' => [
                ['name' => 'PDF', 'children' => []],
                ['name' => 'AI Source Files', 'children' => []]
            ]]
        ]]
    ];
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid template type: ' . $template_type]);
    exit;
}

// Function to recursively create collections
function create_collection_structure($parent_ref, $structure_array, $user_ref) {
    $created = [];
    
    foreach ($structure_array as $item) {
        try {
            // Create the collection with parent in extraparams
            $new_ref = create_collection(
                $user_ref,
                $item['name'], // Just the name, ID will be added after creation
                0, // allow_changes
                0, // cant_delete
                0, // ref (0 = auto-generate)
                false, // public
                array('parent' => $parent_ref, 'type' => 3) // Set parent and type to featured
            );
            
            if ($new_ref && $new_ref > 0) {
                // Update the name to include the ID
                $new_name = $item['name'] . ' (ID-' . $new_ref . ')';
                ps_query("UPDATE collection SET name = ? WHERE ref = ?", 
                    ["s", $new_name, "i", $new_ref]);
                
                $created[] = [
                    'name' => $new_name,
                    'ref' => $new_ref,
                    'parent' => $parent_ref
                ];
                
                // Recursively create children
                if (!empty($item['children'])) {
                    $child_created = create_collection_structure($new_ref, $item['children'], $user_ref);
                    $created = array_merge($created, $child_created);
                }
            } else {
                throw new Exception('Failed to create collection: ' . $item['name']);
            }
        } catch (Exception $e) {
            throw new Exception('Error creating "' . $item['name'] . '": ' . $e->getMessage());
        }
    }
    
    return $created;
}

// Create the structure
try {
    global $userref;
    
    if (!isset($userref) || $userref <= 0) {
        throw new Exception('Invalid user reference');
    }
    
    $created_collections = create_collection_structure($template_ref, $structure, $userref);
    
    // Clear cache
    clear_query_cache("featured_collections");
    
    echo json_encode([
        'success' => true,
        'message' => 'Successfully created ' . count($created_collections) . ' collections',
        'created' => $created_collections,
        'parent_ref' => $template_ref
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
