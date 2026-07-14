<?php
/**
 * Check collection types in database
 */

include "include/boot.php";
include "include/authenticate.php";

if (!checkperm("h")) {
    exit("You need admin permissions.");
}

echo "<h1>Collection Types Check</h1>";

// Get all collections with their types
$all_collections = ps_query("SELECT ref, name, type, parent FROM collection ORDER BY type, ref ASC");

echo "<h2>Collection Type Constants</h2>";
echo "<pre>";
echo "COLLECTION_TYPE_STANDARD = " . COLLECTION_TYPE_STANDARD . "\n";
echo "COLLECTION_TYPE_PUBLIC = " . COLLECTION_TYPE_PUBLIC . "\n";
echo "COLLECTION_TYPE_FEATURED = " . COLLECTION_TYPE_FEATURED . "\n";
echo "</pre>";

echo "<h2>All Collections</h2>";
echo "<table border='1' cellpadding='5' style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Type</th><th>Parent</th><th>Type Name</th></tr>";

$featured_count = 0;
$regular_count = 0;

foreach ($all_collections as $col) {
    $type_name = "";
    if ($col['type'] == COLLECTION_TYPE_FEATURED) {
        $type_name = "FEATURED";
        $featured_count++;
        $row_color = "#d4edda";
    } elseif ($col['type'] == COLLECTION_TYPE_PUBLIC) {
        $type_name = "PUBLIC";
        $regular_count++;
        $row_color = "#fff3cd";
    } else {
        $type_name = "STANDARD";
        $regular_count++;
        $row_color = "#f8d7da";
    }
    
    echo "<tr style='background: $row_color;'>";
    echo "<td>" . $col['ref'] . "</td>";
    echo "<td>" . htmlspecialchars($col['name']) . "</td>";
    echo "<td>" . $col['type'] . "</td>";
    echo "<td>" . ($col['parent'] ? $col['parent'] : '-') . "</td>";
    echo "<td><strong>$type_name</strong></td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Summary</h2>";
echo "<p><strong>Featured Collections:</strong> $featured_count</p>";
echo "<p><strong>Regular Collections:</strong> $regular_count</p>";

if ($regular_count > 0) {
    echo "<hr>";
    echo "<p style='color: red;'><strong>You still have $regular_count regular collections!</strong></p>";
    echo "<p><a href='convert_all_to_featured.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Convert Them Now</a></p>";
}

echo "<hr>";
echo "<p><a href='pages/collections_featured.php'>Go to Featured Collections</a></p>";
echo "<p><a href='pages/collection_manage.php'>Go to My Collections</a></p>";
