<?php
/**
 * Check if a revert just happened and restore the version number
 */

include '../../../include/boot.php';
include '../../../include/authenticate.php';

$resource = getval("resource", 0, true);

if ($resource == 0)
    {
    exit(json_encode(array("success" => false, "message" => "Invalid resource")));
    }

// Check edit permission
if (!get_edit_access($resource))
    {
    exit(json_encode(array("success" => false, "message" => "Permission denied")));
    }

global $auto_version_field_ref, $lang;

// Check if version field is configured
if (empty($auto_version_field_ref))
    {
    exit(json_encode(array("success" => false, "message" => "Version field not configured")));
    }

// Check if there was a recent revert operation (within last 10 seconds)
$recent_revert = ps_query(
    "SELECT rl.ref, rl.notes, rl.date
     FROM resource_log rl
     WHERE rl.resource=? AND rl.type=? 
     AND rl.date > DATE_SUB(NOW(), INTERVAL 10 SECOND)
     AND (rl.notes LIKE '%revert%' OR rl.notes LIKE '%Revert%')
     ORDER BY rl.date DESC LIMIT 1",
    array("i", $resource, "s", LOG_CODE_UPLOADED)
);

if (count($recent_revert) == 0)
    {
    exit(json_encode(array("success" => true, "version_restored" => false, "message" => "No recent revert found")));
    }

// Find the alternative file that was used for the revert
$revert_info = ps_query(
    "SELECT rl.previous_file_alt_ref, rl.previous_value
     FROM resource_log rl
     WHERE rl.resource=? AND rl.type=? AND rl.previous_file_alt_ref IS NOT NULL
     ORDER BY rl.date DESC LIMIT 2",
    array("i", $resource, "s", LOG_CODE_UPLOADED)
);

if (count($revert_info) >= 2)
    {
    // The second entry is the one we reverted TO (the first is the current state backup)
    $reverted_to_alt_ref = $revert_info[1]["previous_file_alt_ref"];
    
    // Find the version that was stored when that alt file was created
    $version_log = ps_query(
        "SELECT previous_value FROM resource_log 
         WHERE resource=? AND previous_file_alt_ref=? AND previous_value IS NOT NULL
         ORDER BY date ASC LIMIT 1",
        array("i", $resource, "i", $reverted_to_alt_ref)
    );
    
    if (count($version_log) > 0 && !empty($version_log[0]["previous_value"]))
        {
        $stored_version = $version_log[0]["previous_value"];
        $current_version = get_data_by_field($resource, $auto_version_field_ref);
        
        // Only restore if versions are different
        if ($stored_version != $current_version)
            {
            // Restore the old version number
            update_field($resource, $auto_version_field_ref, $stored_version);
            
            // Log the version reversion
            resource_log($resource, LOG_CODE_EDITED, 0, '', '', "Reverted version from {$current_version} to {$stored_version}");
            
            exit(json_encode(array(
                "success" => true,
                "version_restored" => true,
                "message" => "Version restored from {$current_version} to {$stored_version}",
                "old_version" => $current_version,
                "new_version" => $stored_version
            )));
            }
        }
    }

exit(json_encode(array("success" => true, "version_restored" => false, "message" => "Could not find stored version")));
