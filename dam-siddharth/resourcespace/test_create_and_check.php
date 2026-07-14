<?php
/**
 * Test: Create collection and immediately check if ID appears
 */

include "include/boot.php";
include "include/authenticate.php";

if (!checkperm("h")) {
    exit("You need admin permissions.");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Collection ID Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>Collection ID Display Test</h1>
    
    <?php
    if (isset($_POST['create'])) {
        $test_name = trim($_POST['collection_name']);
        if (empty($test_name)) {
            $test_name = "Test " . date("H:i:s");
        }
        
        echo "<h2>Creating Collection: " . htmlspecialchars($test_name) . "</h2>";
        
        // Create the collection
        $new_ref = create_collection($userref, $test_name, 0, 0, 0, false);
        
        if ($new_ref) {
            echo "<p class='success'>✓ Collection created with ID: $new_ref</p>";
            
            // Wait a tiny bit to ensure database write is complete
            usleep(100000); // 0.1 seconds
            
            // Test 1: Direct database query
            echo "<h3>Test 1: Direct Database Query</h3>";
            $db_result = ps_query("SELECT name FROM collection WHERE ref = ?", array("i", $new_ref));
            if (count($db_result) > 0) {
                $db_name = $db_result[0]['name'];
                echo "<p>Database name: <strong>" . htmlspecialchars($db_name) . "</strong></p>";
                if (strpos($db_name, "(ID-$new_ref)") !== false) {
                    echo "<p class='success'>✓ ID is in database!</p>";
                } else {
                    echo "<p class='error'>✗ ID is NOT in database!</p>";
                }
            }
            
            // Test 2: get_collection()
            echo "<h3>Test 2: get_collection() Function</h3>";
            $collection = get_collection($new_ref, false);
            if ($collection) {
                echo "<p>get_collection() name: <strong>" . htmlspecialchars($collection['name']) . "</strong></p>";
                if (strpos($collection['name'], "(ID-$new_ref)") !== false) {
                    echo "<p class='success'>✓ ID appears in get_collection()!</p>";
                } else {
                    echo "<p class='error'>✗ ID does NOT appear in get_collection()!</p>";
                }
            }
            
            // Test 3: Make it featured and check
            echo "<h3>Test 3: Featured Collections</h3>";
            ps_query("UPDATE collection SET type = ? WHERE ref = ?", array("i", COLLECTION_TYPE_FEATURED, "i", $new_ref));
            echo "<p class='info'>Converted to featured collection</p>";
            
            // Clear caches manually
            clear_query_cache("featured_collections");
            if (isset($GLOBALS['get_collection_cache'][$new_ref])) {
                unset($GLOBALS['get_collection_cache'][$new_ref]);
            }
            
            // Check in featured collections list
            $all_fcs = get_all_featured_collections();
            $found = false;
            foreach ($all_fcs as $fc) {
                if ($fc['ref'] == $new_ref) {
                    echo "<p>Featured collections name: <strong>" . htmlspecialchars($fc['name']) . "</strong></p>";
                    if (strpos($fc['name'], "(ID-$new_ref)") !== false) {
                        echo "<p class='success'>✓ ID appears in featured collections list!</p>";
                    } else {
                        echo "<p class='error'>✗ ID does NOT appear in featured collections list!</p>";
                    }
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                echo "<p class='error'>✗ Collection not found in featured collections list</p>";
            }
            
            echo "<hr>";
            echo "<p><a href='pages/collections_featured.php' target='_blank'>Open Featured Collections Page (new tab)</a></p>";
            echo "<p><a href='debug_collection_name.php?ref=$new_ref' target='_blank'>Debug this collection (new tab)</a></p>";
            echo "<p><a href='?'>Create another test collection</a></p>";
            
            // Show delete option
            echo "<hr>";
            echo "<form method='post' style='display:inline;'>";
            echo "<input type='hidden' name='delete_ref' value='$new_ref'>";
            echo "<button type='submit' name='delete' onclick='return confirm(\"Delete test collection?\")'>Delete Test Collection $new_ref</button>";
            echo "</form>";
        } else {
            echo "<p class='error'>✗ Failed to create collection</p>";
        }
    }
    
    if (isset($_POST['delete'])) {
        $delete_ref = (int)$_POST['delete_ref'];
        if ($delete_ref > 0) {
            delete_collection($delete_ref);
            echo "<p class='success'>✓ Collection $delete_ref deleted</p>";
            echo "<p><a href='?'>Create new test</a></p>";
        }
    }
    
    if (!isset($_POST['create']) && !isset($_POST['delete'])) {
    ?>
        <form method="post">
            <p>
                <label>Collection Name (optional):</label><br>
                <input type="text" name="collection_name" placeholder="Leave empty for auto-generated name" style="width: 300px;">
            </p>
            <p>
                <button type="submit" name="create">Create Test Collection</button>
            </p>
        </form>
        
        <hr>
        <p><a href="pages/collections_featured.php">Go to Featured Collections Page</a></p>
        <p><a href="debug_collection_name.php">Debug Tool</a></p>
    <?php
    }
    ?>
</body>
</html>
