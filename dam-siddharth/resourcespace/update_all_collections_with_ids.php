<?php
/**
 * Update ALL collections to include IDs in their names
 */

include "include/boot.php";
include "include/authenticate.php";

if (!checkperm("h")) {
    exit("You need admin permissions to run this script.");
}

echo "<h1>Update All Collections with IDs</h1>";

// Get all collections
$all_collections = ps_query("SELECT ref, name, type FROM collection ORDER BY ref ASC");

echo "<p>Found " . count($all_collections) . " collections</p>";

$updated = 0;
$skipped = 0;

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Old Name</th><th>New Name</th><th>Status</th></tr>";

foreach ($all_collections as $collection) {
    $ref = $collection['ref'];
    $name = $collection['name'];
    
    // Check if ID is already in the name
    if (preg_match('/\(ID-' . $ref . '\)$/', $name)) {
        echo "<tr>";
        echo "<td>$ref</td>";
        echo "<td colspan='2'>" . htmlspecialchars($name) . "</td>";
        echo "<td style='color: gray;'>Already has ID</td>";
        echo "</tr>";
        $skipped++;
        continue;
    }
    
    // Remove any old ID format first
    $clean_name = preg_replace('/\s*\(ID-\d+\)$/', '', $name);
    
    // Add the correct ID
    $new_name = $clean_name . " (ID-" . $ref . ")";
    
    // Truncate if too long
    if (strlen($new_name) > 100) {
        $clean_name = mb_strcut($clean_name, 0, 100 - strlen(" (ID-" . $ref . ")"));
        $new_name = $clean_name . " (ID-" . $ref . ")";
    }
    
    // Update the collection
    ps_query("UPDATE collection SET name = ? WHERE ref = ?", array("s", $new_name, "i", $ref));
    
    echo "<tr>";
    echo "<td>$ref</td>";
    echo "<td>" . htmlspecialchars($name) . "</td>";
    echo "<td>" . htmlspecialchars($new_name) . "</td>";
    echo "<td style='color: green;'>✓ Updated</td>";
    echo "</tr>";
    
    $updated++;
}

echo "</table>";

echo "<h2>Summary</h2>";
echo "<p><strong>Updated:</strong> $updated collections</p>";
echo "<p><strong>Skipped:</strong> $skipped collections (already had IDs)</p>";

// Clear all caches
echo "<h2>Clearing Caches</h2>";
clear_query_cache("collection");
clear_query_cache("featured_collections");
if (isset($GLOBALS['get_collection_cache'])) {
    $GLOBALS['get_collection_cache'] = array();
}
echo "<p style='color: green;'>✓ All caches cleared</p>";

echo "<hr>";
echo "<p><strong>DONE!</strong> Please refresh your ResourceSpace page to see the changes.</p>";
echo "<p><a href='pages/collections_featured.php'>Go to Featured Collections</a></p>";
echo "<p><a href='pages/collection_manage.php'>Go to My Collections</a></p>";
