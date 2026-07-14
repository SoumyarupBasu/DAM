<?php
/**
 * Force convert ALL collections to Featured Collections - NO CONFIRMATION
 */

include "include/boot.php";
include "include/authenticate.php";

if (!checkperm("h")) {
    exit("You need admin permissions.");
}

echo "<h1>Force Converting All Collections to Featured</h1>";

// Get the featured collection type constant
echo "<p>COLLECTION_TYPE_FEATURED = " . COLLECTION_TYPE_FEATURED . "</p>";

// Update ALL collections to be featured type
$result = ps_query(
    "UPDATE collection SET type = ? WHERE type != ?",
    array("i", COLLECTION_TYPE_FEATURED, "i", COLLECTION_TYPE_FEATURED)
);

echo "<p style='color: green; font-size: 18px;'><strong>✓ Updated all collections to Featured type</strong></p>";

// Get count of all collections
$all_collections = ps_query("SELECT ref, name, type FROM collection ORDER BY ref ASC");

echo "<h2>All Collections (After Conversion)</h2>";
echo "<table border='1' cellpadding='5' style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Type</th></tr>";

foreach ($all_collections as $col) {
    $is_featured = ($col['type'] == COLLECTION_TYPE_FEATURED);
    $row_color = $is_featured ? "#d4edda" : "#f8d7da";
    
    echo "<tr style='background: $row_color;'>";
    echo "<td>" . $col['ref'] . "</td>";
    echo "<td>" . htmlspecialchars($col['name']) . "</td>";
    echo "<td>" . $col['type'] . " " . ($is_featured ? "✓ FEATURED" : "✗ NOT FEATURED") . "</td>";
    echo "</tr>";
}

echo "</table>";

// Clear all caches aggressively
echo "<h2>Clearing All Caches</h2>";

// Clear query caches
clear_query_cache("collection");
clear_query_cache("featured_collections");

// Clear global caches
if (isset($GLOBALS['get_collection_cache'])) {
    $GLOBALS['get_collection_cache'] = array();
}
if (isset($GLOBALS['CACHE_FC_PERMS_FILTER_SQL'])) {
    $GLOBALS['CACHE_FC_PERMS_FILTER_SQL'] = array();
}

// Clear file-based cache directory
$cache_location = get_query_cache_location();
if (file_exists($cache_location)) {
    $cache_files = scandir($cache_location);
    $deleted = 0;
    foreach ($cache_files as $file) {
        if ($file != '.' && $file != '..' && file_exists($cache_location . "/" . $file)) {
            if (unlink($cache_location . "/" . $file)) {
                $deleted++;
            }
        }
    }
    echo "<p style='color: green;'>✓ Deleted $deleted cache files</p>";
}

echo "<p style='color: green;'>✓ All caches cleared</p>";

echo "<hr>";
echo "<div style='background: #d4edda; padding: 20px; border: 2px solid #28a745; margin: 20px 0;'>";
echo "<h2 style='color: #155724;'>✓ Conversion Complete!</h2>";
echo "<p><strong>All collections are now Featured Collections.</strong></p>";
echo "<p><strong>IMPORTANT: You MUST do the following:</strong></p>";
echo "<ol>";
echo "<li><strong>Close your browser completely</strong></li>";
echo "<li><strong>Clear browser cache (Ctrl+Shift+Delete)</strong></li>";
echo "<li><strong>Reopen browser and login again</strong></li>";
echo "<li>All collections should now appear under FEATURED COLLECTIONS</li>";
echo "<li>You can now create sub-collections</li>";
echo "</ol>";
echo "</div>";

echo "<p><a href='pages/collections_featured.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Featured Collections</a></p>";
