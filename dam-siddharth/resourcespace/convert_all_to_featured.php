<?php
/**
 * Convert ALL regular collections to Featured Collections
 * This will enable sub-collection support for all collections
 */

include "include/boot.php";
include "include/authenticate.php";

if (!checkperm("h")) {
    exit("You need admin permissions to run this script.");
}

echo "<h1>Convert All Collections to Featured Collections</h1>";
echo "<p>This will enable sub-collection support for ALL collections.</p>";

if (!isset($_GET['confirm'])) {
    echo "<div style='background: #fff3cd; padding: 20px; border: 2px solid #ffc107; margin: 20px 0;'>";
    echo "<h2>⚠️ Warning</h2>";
    echo "<p>This will convert ALL regular collections to Featured Collections.</p>";
    echo "<p><strong>What this means:</strong></p>";
    echo "<ul>";
    echo "<li>All collections will appear in the FEATURED COLLECTIONS section</li>";
    echo "<li>All collections will support sub-collections (hierarchy)</li>";
    echo "<li>The regular COLLECTIONS section will be empty</li>";
    echo "<li>Users will still have access based on their permissions</li>";
    echo "</ul>";
    echo "<p><strong>Are you sure you want to proceed?</strong></p>";
    echo "<p>";
    echo "<a href='?confirm=yes' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-right: 10px;'>Yes, Convert All Collections</a>";
    echo "<a href='pages/collection_manage.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Cancel</a>";
    echo "</p>";
    echo "</div>";
    exit;
}

// Get all non-featured collections
$regular_collections = ps_query(
    "SELECT ref, name, type FROM collection WHERE type != ? ORDER BY ref ASC",
    array("i", COLLECTION_TYPE_FEATURED)
);

echo "<p>Found " . count($regular_collections) . " regular collections to convert</p>";

if (count($regular_collections) == 0) {
    echo "<p style='color: green;'>✓ All collections are already Featured Collections!</p>";
    echo "<p><a href='pages/collections_featured.php'>Go to Featured Collections</a></p>";
    exit;
}

$converted = 0;

echo "<table border='1' cellpadding='5' style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Old Type</th><th>Status</th></tr>";

foreach ($regular_collections as $collection) {
    $ref = $collection['ref'];
    $name = $collection['name'];
    $old_type = $collection['type'];
    
    // Convert to featured collection
    ps_query(
        "UPDATE collection SET type = ? WHERE ref = ?",
        array("i", COLLECTION_TYPE_FEATURED, "i", $ref)
    );
    
    echo "<tr>";
    echo "<td>$ref</td>";
    echo "<td>" . htmlspecialchars($name) . "</td>";
    echo "<td>$old_type</td>";
    echo "<td style='color: green;'>✓ Converted to Featured</td>";
    echo "</tr>";
    
    $converted++;
}

echo "</table>";

echo "<h2>Summary</h2>";
echo "<p style='font-size: 18px;'><strong>Converted:</strong> $converted collections</p>";

// Clear all caches
echo "<h2>Clearing Caches</h2>";
clear_query_cache("collection");
clear_query_cache("featured_collections");
if (isset($GLOBALS['get_collection_cache'])) {
    $GLOBALS['get_collection_cache'] = array();
}
echo "<p style='color: green;'>✓ All caches cleared</p>";

echo "<hr>";
echo "<div style='background: #d4edda; padding: 20px; border: 2px solid #28a745; margin: 20px 0;'>";
echo "<h2 style='color: #155724;'>✓ Conversion Complete!</h2>";
echo "<p>All collections are now Featured Collections and support sub-collections.</p>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Refresh your ResourceSpace page</li>";
echo "<li>All collections will now appear under FEATURED COLLECTIONS</li>";
echo "<li>You can now create sub-collections by clicking the arrow next to any collection</li>";
echo "</ol>";
echo "</div>";

echo "<p><a href='pages/collections_featured.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Featured Collections</a></p>";
