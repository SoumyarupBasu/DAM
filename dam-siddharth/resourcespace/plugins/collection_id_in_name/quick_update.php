<?php

/**
 * Quick Update Script - Fast batch update for existing collections
 * Adds ID suffix to collections that don't have it
 */

// Include ResourceSpace
include '../../include/boot.php';
include '../../include/authenticate.php';

// Check admin permission
if (!checkperm('a')) {
    die('Admin access required');
}

// Set execution time limit
set_time_limit(300);

// Simple HTML output
header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Quick Update</title></head><body>";
echo "<h2>Collection ID Quick Update</h2>";
echo "<p>Processing collections...</p>";

// Get collections without ID suffix
$sql = "SELECT ref, name FROM collection WHERE name NOT LIKE '%(ID-%' ORDER BY ref";
$collections = sql_query($sql);

$count = 0;

if (is_array($collections)) {
    foreach ($collections as $col) {
        $id = (int)$col['ref'];
        $name = $col['name'];
        
        if ($id > 0 && !empty($name)) {
            $new_name = $name . " (ID-" . $id . ")";
            sql_query("UPDATE collection SET name = '" . escape_check($new_name) . "' WHERE ref = " . $id);
            echo "✓ Updated: {$name} → {$new_name}<br>";
            $count++;
            flush();
        }
    }
}

echo "<hr>";
echo "<h3>Done! Updated {$count} collections.</h3>";
echo "<p><a href='../../pages/collections.php'>View Collections</a></p>";
echo "</body></html>";
?>