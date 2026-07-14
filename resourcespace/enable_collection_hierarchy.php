<?php
/**
 * Enable Collection Hierarchy - Complete Solution
 * This script will:
 * 1. Convert all regular collections to featured collections
 * 2. Verify the configuration is correct
 * 3. Clear all caches
 * 4. Provide instructions for enabling hierarchy UI
 */

include "include/boot.php";
include "include/authenticate.php";

if (!checkperm("h")) {
    exit("You need admin permissions to run this script.");
}

echo "<h1>Enable Collection Hierarchy - Complete Setup</h1>";

// Step 1: Check current configuration
echo "<h2>Step 1: Configuration Check</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Setting</th><th>Current Value</th><th>Required Value</th><th>Status</th></tr>";

$config_ok = true;

// Check themes_in_my_collections
global $themes_in_my_collections;
$themes_status = $themes_in_my_collections ? "✓ Enabled" : "✗ Disabled";
$themes_color = $themes_in_my_collections ? "green" : "red";
if (!$themes_in_my_collections) $config_ok = false;

echo "<tr>";
echo "<td><code>\$themes_in_my_collections</code></td>";
echo "<td>" . ($themes_in_my_collections ? "true" : "false") . "</td>";
echo "<td>true</td>";
echo "<td style='color: $themes_color;'>$themes_status</td>";
echo "</tr>";

// Check enable_themes
global $enable_themes;
$enable_themes_status = $enable_themes ? "✓ Enabled" : "✗ Disabled";
$enable_themes_color = $enable_themes ? "green" : "red";
if (!$enable_themes) $config_ok = false;

echo "<tr>";
echo "<td><code>\$enable_themes</code></td>";
echo "<td>" . ($enable_themes ? "true" : "false") . "</td>";
echo "<td>true</td>";
echo "<td style='color: $enable_themes_color;'>$enable_themes_status</td>";
echo "</tr>";

echo "</table>";

if (!$config_ok) {
    echo "<div style='background: #f8d7da; padding: 20px; border: 2px solid #dc3545; margin: 20px 0;'>";
    echo "<h3 style='color: #721c24;'>⚠️ Configuration Issue</h3>";
    echo "<p>Please add the following to your <code>include/config.php</code> file:</p>";
    echo "<pre style='background: #fff; padding: 10px; border: 1px solid #ccc;'>";
    if (!$themes_in_my_collections) {
        echo "\$themes_in_my_collections = true;\n";
    }
    if (!$enable_themes) {
        echo "\$enable_themes = true;\n";
    }
    echo "</pre>";
    echo "<p>After adding these settings, refresh this page.</p>";
    echo "</div>";
    exit;
}

echo "<p style='color: green; font-weight: bold;'>✓ Configuration is correct!</p>";

// Step 2: Convert collections
echo "<h2>Step 2: Convert Collections to Featured Type</h2>";

$regular_collections = ps_query(
    "SELECT ref, name, type, parent FROM collection WHERE type != ? ORDER BY ref ASC",
    array("i", COLLECTION_TYPE_FEATURED)
);

echo "<p>Found <strong>" . count($regular_collections) . "</strong> regular collections</p>";

if (count($regular_collections) > 0) {
    if (!isset($_GET['convert'])) {
        echo "<div style='background: #fff3cd; padding: 20px; border: 2px solid #ffc107; margin: 20px 0;'>";
        echo "<p><strong>Ready to convert " . count($regular_collections) . " collections to Featured type.</strong></p>";
        echo "<p>This will enable hierarchy support for all collections.</p>";
        echo "<p><a href='?convert=yes' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Convert Now</a></p>";
        echo "</div>";
        exit;
    }
    
    // Convert collections
    ps_query(
        "UPDATE collection SET type = ? WHERE type != ?",
        array("i", COLLECTION_TYPE_FEATURED, "i", COLLECTION_TYPE_FEATURED)
    );
    
    echo "<p style='color: green; font-weight: bold;'>✓ Converted " . count($regular_collections) . " collections to Featured type</p>";
} else {
    echo "<p style='color: green; font-weight: bold;'>✓ All collections are already Featured type</p>";
}

// Step 3: Clear caches
echo "<h2>Step 3: Clear All Caches</h2>";

clear_query_cache("collection");
clear_query_cache("featured_collections");

if (isset($GLOBALS['get_collection_cache'])) {
    $GLOBALS['get_collection_cache'] = array();
}

// Clear file cache
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
    echo "<p>✓ Deleted $deleted cache files</p>";
}

echo "<p style='color: green; font-weight: bold;'>✓ All caches cleared</p>";

// Step 4: Verify setup
echo "<h2>Step 4: Verification</h2>";

$all_collections = ps_query("SELECT ref, name, type, parent FROM collection ORDER BY ref ASC LIMIT 10");

echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Type</th><th>Parent</th><th>Status</th></tr>";

foreach ($all_collections as $col) {
    $is_featured = ($col['type'] == COLLECTION_TYPE_FEATURED);
    $row_color = $is_featured ? "#d4edda" : "#f8d7da";
    $status = $is_featured ? "✓ Featured (Hierarchy Enabled)" : "✗ Regular (No Hierarchy)";
    
    echo "<tr style='background: $row_color;'>";
    echo "<td>" . $col['ref'] . "</td>";
    echo "<td>" . htmlspecialchars($col['name']) . "</td>";
    echo "<td>" . $col['type'] . "</td>";
    echo "<td>" . ($col['parent'] ? $col['parent'] : '-') . "</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

// Final instructions
echo "<hr>";
echo "<div style='background: #d4edda; padding: 20px; border: 2px solid #28a745; margin: 20px 0;'>";
echo "<h2 style='color: #155724;'>✓ Setup Complete!</h2>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li><strong>Close your browser completely</strong></li>";
echo "<li><strong>Clear browser cache</strong> (Ctrl+Shift+Delete or Cmd+Shift+Delete)</li>";
echo "<li><strong>Reopen browser and login</strong></li>";
echo "<li>Go to any page with the sidebar</li>";
echo "<li>Look for collections under both FEATURED COLLECTIONS and COLLECTIONS sections</li>";
echo "<li>Click the arrow (▶) next to any collection to expand it</li>";
echo "<li>Click the <strong>+ Create</strong> button to create a sub-collection</li>";
echo "</ol>";

echo "<h3>How to Create Sub-Collections:</h3>";
echo "<ol>";
echo "<li>Find a collection in the sidebar (either section)</li>";
echo "<li>Click the arrow icon (▶) to expand it</li>";
echo "<li>Click the <strong>+ Create</strong> button that appears</li>";
echo "<li>Enter the sub-collection name</li>";
echo "<li>The sub-collection will be created as a child</li>";
echo "</ol>";

echo "<h3>Troubleshooting:</h3>";
echo "<p>If you still don't see the hierarchy:</p>";
echo "<ul>";
echo "<li>Make sure you cleared browser cache completely</li>";
echo "<li>Try a different browser or incognito mode</li>";
echo "<li>Check that <code>\$themes_in_my_collections = true;</code> is in config.php</li>";
echo "<li>Run this script again to verify all collections are Featured type</li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='pages/collections_featured.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-right: 10px;'>Go to Featured Collections</a>";
echo "<a href='pages/collection_manage.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to My Collections</a></p>";
