<?php

/**
 * Auto-append Collection IDs
 * 
 * This script should be run via cron every minute:
 * * * * * * php /var/www/resourcespace/plugins/collection_id_in_name/auto_append_ids.php
 * 
 * Or add to ResourceSpace cron: batch/cron.php
 */

// Include ResourceSpace
$script_path = dirname(__FILE__);
include $script_path . '/../../include/db.php';
include $script_path . '/../../include/general.php';

// Update collections created in the last 2 minutes that don't have IDs
$sql = "UPDATE collection 
        SET name = CONCAT(name, ' (ID-', ref, ')')
        WHERE name NOT LIKE '%(ID-%'
        AND created >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)";

sql_query($sql);

// Log the operation
$updated = sql_affected_rows();
if ($updated > 0) {
    error_log("Collection ID Plugin: Appended IDs to {$updated} collections");
}
?>