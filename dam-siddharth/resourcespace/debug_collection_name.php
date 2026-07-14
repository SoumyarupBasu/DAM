<?php
/**
 * Debug script to check collection names in different contexts
 */

include "include/boot.php";
include "include/authenticate.php";

if (!checkperm("h")) {
    exit("You need admin permissions to run this script.");
}

$test_ref = (int)getval("ref", 0);

if ($test_ref == 0) {
    echo "<h1>Collection Name Debug Tool</h1>";
    echo "<p>Enter a collection ID to debug:</p>";
    echo "<form method='get'>";
    echo "<input type='number' name='ref' placeholder='Collection ID' required>";
    echo "<button type='submit'>Check</button>";
    echo "</form>";
    exit;
}

echo "<h1>Collection Name Debug - ID: $test_ref</h1>";

// 1. Direct database query
echo "<h2>1. Direct Database Query</h2>";
$db_result = ps_query("SELECT ref, name, type FROM collection WHERE ref = ?", array("i", $test_ref));
if (count($db_result) > 0) {
    echo "<pre>";
    print_r($db_result[0]);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>Collection not found in database!</p>";
}

// 2. get_collection() function
echo "<h2>2. get_collection() Function (no cache)</h2>";
if (isset($GLOBALS['get_collection_cache'][$test_ref])) {
    unset($GLOBALS['get_collection_cache'][$test_ref]);
}
$collection = get_collection($test_ref, false);
if ($collection) {
    echo "<p><strong>Name:</strong> " . htmlspecialchars($collection['name']) . "</p>";
    echo "<p><strong>Type:</strong> " . $collection['type'] . "</p>";
} else {
    echo "<p style='color: red;'>get_collection() returned false</p>";
}

// 3. get_collection() with cache
echo "<h2>3. get_collection() Function (with cache)</h2>";
$collection_cached = get_collection($test_ref, true);
if ($collection_cached) {
    echo "<p><strong>Name:</strong> " . htmlspecialchars($collection_cached['name']) . "</p>";
} else {
    echo "<p style='color: red;'>get_collection() with cache returned false</p>";
}

// 4. get_all_featured_collections()
echo "<h2>4. get_all_featured_collections() Function</h2>";
$all_fcs = get_all_featured_collections();
$found_in_fc = false;
foreach ($all_fcs as $fc) {
    if ($fc['ref'] == $test_ref) {
        echo "<p><strong>Name:</strong> " . htmlspecialchars($fc['name']) . "</p>";
        echo "<p><strong>Type:</strong> " . $fc['type'] . "</p>";
        $found_in_fc = true;
        break;
    }
}
if (!$found_in_fc) {
    echo "<p style='color: orange;'>Collection not found in featured collections list</p>";
}

// 5. i18n_get_collection_name()
echo "<h2>5. i18n_get_collection_name() Function</h2>";
if ($collection) {
    $translated_name = i18n_get_collection_name($collection);
    echo "<p><strong>Translated Name:</strong> " . $translated_name . "</p>";
}

// 6. Check query cache
echo "<h2>6. Query Cache Status</h2>";
echo "<p>Clearing all collection-related caches...</p>";
clear_query_cache("collection");
clear_query_cache("collection" . $test_ref);
clear_query_cache("featured_collections");
echo "<p style='color: green;'>✓ Caches cleared</p>";

// 7. Re-check after cache clear
echo "<h2>7. After Cache Clear</h2>";
if (isset($GLOBALS['get_collection_cache'][$test_ref])) {
    unset($GLOBALS['get_collection_cache'][$test_ref]);
}
$collection_after = get_collection($test_ref, false);
if ($collection_after) {
    echo "<p><strong>Name:</strong> " . htmlspecialchars($collection_after['name']) . "</p>";
}

$all_fcs_after = get_all_featured_collections();
foreach ($all_fcs_after as $fc) {
    if ($fc['ref'] == $test_ref) {
        echo "<p><strong>Featured Collections Name:</strong> " . htmlspecialchars($fc['name']) . "</p>";
        break;
    }
}

echo "<hr>";
echo "<p><a href='pages/collections_featured.php'>Go to Featured Collections Page</a></p>";
echo "<p><a href='?ref=$test_ref'>Refresh this page</a></p>";
echo "<p><a href='?'>Check another collection</a></p>";
