<?php
include "../../include/boot.php";
include "../../include/authenticate.php";

$ref = getval('ref', 0, true);

header('Content-Type: application/json');

if ($ref <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid collection reference']);
    exit;
}

// Get the collection
$collection = get_collection($ref);
if (!$collection) {
    echo json_encode(['success' => false, 'error' => 'Collection not found']);
    exit;
}

// Function to strip prefix characters
if (!function_exists('strip_prefix_chars')) {
    function strip_prefix_chars($text, $chars = '*') {
        return ltrim($text, $chars);
    }
}

// Function to build tree structure
function build_collection_tree($parent_ref, $prefix = '', $is_last = true) {
    $children = ps_query("SELECT ref, name FROM collection WHERE parent = ? ORDER BY name", ["i", $parent_ref]);
    
    if (empty($children)) {
        return '';
    }
    
    $output = '';
    $count = count($children);
    
    foreach ($children as $index => $child) {
        $is_last_child = ($index === $count - 1);
        $connector = $is_last_child ? '└─ ' : '├─ ';
        $child_prefix = $is_last_child ? '    ' : '│   ';
        
        $child_name = strip_prefix_chars($child['name'], '*');
        $output .= $prefix . $connector . $child_name . ' (ID-XX)' . "\n";
        
        // Recursively get children
        $output .= build_collection_tree($child['ref'], $prefix . $child_prefix, $is_last_child);
    }
    
    return $output;
}

// Build the structure
$collection_name = strip_prefix_chars($collection['name'], '*');
$structure = $collection_name . ' (ID-' . $ref . ')' . "\n";
$structure .= build_collection_tree($ref);

// If no children, show a message
$child_count = ps_value("SELECT COUNT(*) as value FROM collection WHERE parent = ?", ["i", $ref], 0);
if ($child_count == 0) {
    $structure .= '└─ (No subcollections to copy)';
}

echo json_encode([
    'success' => true,
    'structure' => $structure,
    'collection_name' => $collection['name'],
    'collection_ref' => $ref
]);
