<?php
/**
 * Hook into rse_version revert page to restore version numbers
 * This file will be loaded when the rse_version/pages/revert.php page is accessed
 */

if (!function_exists('auto_version_restore_on_revert'))
    {
    function auto_version_restore_on_revert($resource, $log_ref)
        {
        global $auto_version_field_ref;
        
        // Check if version field is configured
        if (empty($auto_version_field_ref))
            {
            debug("AUTO_VERSION_REVERT: Version field not configured");
            return false;
            }
        
        debug("AUTO_VERSION_REVERT: Starting version restore for resource {$resource}, log ref {$log_ref}");
        
        // Get the log entry we're reverting to
        $log = ps_query(
            "SELECT previous_file_alt_ref, previous_value FROM resource_log WHERE ref=?",
            array("i", $log_ref)
        );
        
        if (count($log) == 0 || empty($log[0]["previous_file_alt_ref"]))
            {
            debug("AUTO_VERSION_REVERT: No alt_ref found in log entry");
            return false;
            }
        
        $alt_ref = $log[0]["previous_file_alt_ref"];
        debug("AUTO_VERSION_REVERT: Found alt_ref: {$alt_ref}");
        
        // Find the version that was stored when this alt file was created
        $version_log = ps_query(
            "SELECT previous_value FROM resource_log 
             WHERE resource=? AND previous_file_alt_ref=? AND previous_value IS NOT NULL
             ORDER BY date ASC LIMIT 1",
            array("i", $resource, "i", $alt_ref)
        );
        
        if (count($version_log) > 0 && !empty($version_log[0]["previous_value"]))
            {
            $stored_version = $version_log[0]["previous_value"];
            $current_version = get_data_by_field($resource, $auto_version_field_ref);
            
            debug("AUTO_VERSION_REVERT: Stored version: {$stored_version}, Current version: {$current_version}");
            
            // Only restore if versions are different
            if ($stored_version != $current_version)
                {
                // Restore the old version number
                update_field($resource, $auto_version_field_ref, $stored_version);
                
                // Log the version reversion
                resource_log($resource, LOG_CODE_EDITED, 0, '', '', "Reverted version from {$current_version} to {$stored_version}");
                
                debug("AUTO_VERSION_REVERT: Version restored successfully");
                return true;
                }
            else
                {
                debug("AUTO_VERSION_REVERT: Versions are the same, no restore needed");
                }
            }
        else
            {
            debug("AUTO_VERSION_REVERT: No stored version found for alt_ref {$alt_ref}");
            }
        
        return false;
        }
    }
