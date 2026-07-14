<?php

/**
 * Batch Update Script for Existing Collections
 * 
 * Run this ONCE after plugin activation to add ID suffixes to all existing collections
 * that don't already have them.
 * 
 * URL: http://your-domain/plugins/collection_id_in_name/update_existing_collections.php
 */

// Include ResourceSpace core
include '../../include/boot.php';
include '../../include/authenticate.php';

// Require admin permissions
if (!checkperm('a')) {
    exit('ERROR: Administrator access required to run this script.');
}

// Set content type for HTML output
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Collection ID in Name - Batch Update</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c5aa0; }
        .success { color: #28a745; font-weight: bold; }
        .skipped { color: #6c757d; }
        .error { color: #dc3545; font-weight: bold; }
        .summary { background: #e7f3ff; padding: 15px; border-radius: 5px; margin-top: 20px; border-left: 4px solid #2c5aa0; }
        .collection-item { padding: 8px; border-bottom: 1px solid #eee; }
        .button { display: inline-block; padding: 10px 20px; background: #007cba; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .button:hover { background: #005a87; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔢 Collection ID in Name - Batch Update</h1>
        <p>Updating all existing collections to include ID suffixes...</p>
        <hr>
        
<?php

// Initialize counters
$updated_count = 0;
$skipped_count = 0;
$error_count = 0;

// Get all collections ordered by ID
$sql = "SELECT ref, name FROM collection ORDER BY ref";
$collections = sql_query($sql);

// Process each collection
foreach ($collections as $collection) {
    $collection_id = (int) $collection['ref'];
    $current_name = trim($collection['name']);
    
    // Skip if collection ID is invalid
    if ($collection_id <= 0) {
        echo "<div class='collection-item error'>ERROR: Invalid collection ID</div>\n";
        $error_count++;
        continue;
    }
    
    // Skip if name is empty
    if (empty($current_name)) {
        echo "<div class='collection-item error'>ERROR: Collection {$collection_id} has empty name</div>\n";
        $error_count++;
        continue;
    }
    
    // Check if ID suffix already exists
    if (preg_match('/\s*\(ID-\d+\)$/', $current_name)) {
        echo "<div class='collection-item skipped'>SKIPPED: {$current_name} (already has ID suffix)</div>\n";
        $skipped_count++;
        continue;
    }
    
    // Create new name with ID suffix
    $new_name = $current_name . " (ID-" . $collection_id . ")";
    
    // Update collection name in database
    $update_sql = "UPDATE collection 
                   SET name = '" . escape_check($new_name) . "' 
                   WHERE ref = '" . escape_check($collection_id) . "'";
    
    $result = sql_query($update_sql);
    
    if ($result !== false) {
        echo "<div class='collection-item success'>✓ UPDATED: \"{$current_name}\" → \"{$new_name}\"</div>\n";
        $updated_count++;
    } else {
        echo "<div class='collection-item error'>✗ FAILED: Could not update collection {$collection_id}</div>\n";
        $error_count++;
    }
    
    // Flush output buffer to show progress in real-time
    flush();
}

?>
        
        <div class="summary">
            <h2>📊 Summary</h2>
            <p><strong>Successfully Updated:</strong> <span class="success"><?php echo $updated_count; ?> collections</span></p>
            <p><strong>Skipped (already had ID):</strong> <span class="skipped"><?php echo $skipped_count; ?> collections</span></p>
            <?php if ($error_count > 0): ?>
            <p><strong>Errors:</strong> <span class="error"><?php echo $error_count; ?> collections</span></p>
            <?php endif; ?>
            <p><strong>Total Processed:</strong> <?php echo ($updated_count + $skipped_count + $error_count); ?> collections</p>
        </div>
        
        <a href="<?php echo $baseurl_short; ?>pages/collections.php" class="button">📚 View Collections</a>
        <a href="<?php echo $baseurl_short; ?>pages/team/team_plugins.php" class="button">🔌 Manage Plugins</a>
    </div>
</body>
</html>