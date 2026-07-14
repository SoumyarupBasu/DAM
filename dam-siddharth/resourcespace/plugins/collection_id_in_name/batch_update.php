<?php

/**
 * Command-line batch update script for existing collections
 */

include '../../include/boot.php';
include 'config.php';

echo "Collection ID in Name - Batch Update\n";
echo "====================================\n\n";

// Get all collections that don't have ID suffixes
$collections = ps_query("SELECT ref, name FROM collection ORDER BY ref");

$updated_count = 0;
$skipped_count = 0;

foreach ($collections as $collection) {
    $collection_id = $collection['ref'];
    $current_name = $collection['name'];
    
    // Skip if already has ID suffix
    if (collection_name_has_id($current_name)) {
        echo "SKIP: {$current_name} (already has ID)\n";
        $skipped_count++;
        continue;
    }
    
    // Add ID suffix
    $new_name = add_collection_id_suffix($current_name, $collection_id);
    
    // Update in database
    if (update_collection_name_safe($collection_id, $new_name)) {
        echo "UPDATE: \"{$current_name}\" → \"{$new_name}\"\n";
        $updated_count++;
    } else {
        echo "ERROR: Failed to update collection ID {$collection_id}\n";
    }
}

echo "\n====================================\n";
echo "SUMMARY:\n";
echo "Updated: {$updated_count} collections\n";
echo "Skipped: {$skipped_count} collections\n";
echo "Total processed: " . ($updated_count + $skipped_count) . " collections\n";
echo "====================================\n";