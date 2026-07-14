<?php
// Script to show collection hierarchy tree

include "include/boot.php";
include "include/authenticate.php";

$collection_id = getval("id", 54, true); // Default to MATH ID-54

/**
 * Recursively get all children and subchildren of a collection
 */
function get_collection_tree($parent_id, $level = 0) {
    $children = ps_query(
        "SELECT ref, name, parent, type, created 
         FROM collection 
         WHERE parent = ? 
         ORDER BY name ASC",
        ["i", $parent_id]
    );
    
    $tree = [];
    
    foreach ($children as $child) {
        $child_data = [
            'id' => $child['ref'],
            'name' => $child['name'],
            'parent' => $child['parent'],
            'type' => $child['type'],
            'created' => $child['created'],
            'level' => $level,
            'children' => get_collection_tree($child['ref'], $level + 1)
        ];
        
        $tree[] = $child_data;
    }
    
    return $tree;
}

/**
 * Display tree in a readable format
 */
function display_tree($tree, $indent = 0) {
    foreach ($tree as $node) {
        $prefix = str_repeat("  ", $indent);
        echo $prefix . "├─ " . htmlspecialchars($node['name']) . " (ID: " . $node['id'] . ")\n";
        
        if (!empty($node['children'])) {
            display_tree($node['children'], $indent + 1);
        }
    }
}

/**
 * Convert tree to dictionary format
 */
function tree_to_dict($tree) {
    $dict = [];
    
    foreach ($tree as $node) {
        $dict[$node['id']] = [
            'name' => $node['name'],
            'parent' => $node['parent'],
            'type' => $node['type'],
            'level' => $node['level'],
            'children' => !empty($node['children']) ? tree_to_dict($node['children']) : []
        ];
    }
    
    return $dict;
}

// Get the parent collection info
$parent_collection = ps_query(
    "SELECT ref, name, parent, type, created FROM collection WHERE ref = ?",
    ["i", $collection_id]
);

if (empty($parent_collection)) {
    echo "<h2>Collection ID-{$collection_id} not found!</h2>";
    exit;
}

$parent = $parent_collection[0];

echo "<h2>Collection Tree for: " . htmlspecialchars($parent['name']) . " (ID-{$collection_id})</h2>";

// Get the tree
$tree = get_collection_tree($collection_id);

// Display as tree
echo "<h3>Tree View:</h3>";
echo "<pre>";
echo htmlspecialchars($parent['name']) . " (ID: {$collection_id}) [ROOT]\n";
if (!empty($tree)) {
    display_tree($tree);
} else {
    echo "  └─ (No children)\n";
}
echo "</pre>";

// Display as dictionary
echo "<h3>Dictionary Format (JSON):</h3>";
echo "<pre>";
$dict = [
    $collection_id => [
        'name' => $parent['name'],
        'parent' => $parent['parent'],
        'type' => $parent['type'],
        'level' => 0,
        'children' => tree_to_dict($tree)
    ]
];
echo json_encode($dict, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";

// Display as PHP array
echo "<h3>PHP Array Format:</h3>";
echo "<pre>";
print_r($dict);
echo "</pre>";

// Count statistics
function count_descendants($tree) {
    $count = count($tree);
    foreach ($tree as $node) {
        if (!empty($node['children'])) {
            $count += count_descendants($node['children']);
        }
    }
    return $count;
}

$total_descendants = count_descendants($tree);

echo "<h3>Statistics:</h3>";
echo "<ul>";
echo "<li>Direct Children: " . count($tree) . "</li>";
echo "<li>Total Descendants: " . $total_descendants . "</li>";
echo "<li>Max Depth: " . (empty($tree) ? 0 : max(array_column($tree, 'level')) + 1) . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='?id=54'>MATH (ID-54)</a> | ";
echo "<a href='?id=1148'>Numan (ID-1148)</a> | ";
echo "<a href='?id=1209'>Numan (ID-1209)</a> | ";
echo "<a href='?id=1210'>Ankur (ID-1210)</a></p>";
