<?php
/**
 * Test script to verify collection ID is appended and displayed correctly
 * Access this at: http://your-domain/test_collection_id_display.php
 */

include "include/boot.php";
include "include/authenticate.php";

if (!checkperm("h")) {
    exit("You need admin permissions to run this test.");
}

echo "<h1>Collection ID Display Test</h1>";

// Test 1: Create a new collection
echo "<h2>Test 1: Creating a new collection</h2>";
$test_name = "Test Collection " . date("Y-m-d H:i:s");
$new_ref = create_collection($userref, $test_name);

if ($new_ref) {
    echo "<p>✓ Collection created with ref: $new_ref</p>";
    
    // Fetch the collection immediately
    $collection = get_collection($new_ref);
    
    if ($collection) {
        echo "<p>Collection name from database: <strong>" . htmlspecialchars($collection['name']) . "</strong></p>";
        
        if (preg_match('/\(ID-' . $new_ref . '\)$/', $collection['name'])) {
            echo "<p style='color: green;'>✓ SUCCESS: Collection ID is appended correctly!</p>";
        } else {
            echo "<p style='color: red;'>✗ FAILED: Collection ID is NOT appended!</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Could not fetch collection data</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Failed to create collection</p>";
}

// Test 2: Check if it appears in featured collections list
echo "<h2>Test 2: Checking featured collections cache</h2>";

// Make it a featured collection
ps_query("UPDATE collection SET type = ? WHERE ref = ?", array("i", COLLECTION_TYPE_FEATURED, "i", $new_ref));

// Clear caches
clear_query_cache("featured_collections");
if (isset($GLOBALS['get_collection_cache'][$new_ref])) {
    unset($GLOBALS['get_collection_cache'][$new_ref]);
}

// Fetch all featured collections
$all_fcs = get_all_featured_collections();
$found = false;

foreach ($all_fcs as $fc) {
    if ($fc['ref'] == $new_ref) {
        $found = true;
        echo "<p>Found in featured collections: <strong>" . htmlspecialchars($fc['name']) . "</strong></p>";
        
        if (preg_match('/\(ID-' . $new_ref . '\)$/', $fc['name'])) {
            echo "<p style='color: green;'>✓ SUCCESS: Collection ID appears in featured collections list!</p>";
        } else {
            echo "<p style='color: red;'>✗ FAILED: Collection ID does NOT appear in featured collections list!</p>";
        }
        break;
    }
}

if (!$found) {
    echo "<p style='color: orange;'>⚠ Collection not found in featured collections list (may need to refresh)</p>";
}

// Cleanup
echo "<h2>Cleanup</h2>";
echo "<p><a href='?cleanup=$new_ref'>Click here to delete the test collection</a></p>";

if (isset($_GET['cleanup']) && is_numeric($_GET['cleanup'])) {
    $cleanup_ref = (int)$_GET['cleanup'];
    delete_collection($cleanup_ref);
    echo "<p style='color: green;'>✓ Test collection $cleanup_ref deleted</p>";
    echo "<p><a href='?'>Run test again</a></p>";
}

echo "<hr>";
echo "<p><a href='pages/collections_featured.php'>Go to Featured Collections Page</a></p>";
echo "<p><a href='pages/collection_manage.php'>Go to My Collections</a></p>";
