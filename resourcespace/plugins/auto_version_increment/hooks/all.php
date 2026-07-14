<?php

function HookAuto_version_incrementAllInitialise()
    {
    global $auto_version_fieldvars;
    config_register_core_fieldvars("Auto Version Increment plugin", $auto_version_fieldvars);
    }

function HookAuto_version_incrementAllUpload_image_after_log_write($ref, $log_ref)
    {
    global $auto_version_field_ref, $auto_version_increment_type;
    
    // Check if version field is configured
    if (empty($auto_version_field_ref))
        {
        debug("AUTO_VERSION: Version field not configured");
        return false;
        }
    
    // Get current version value
    $current_version = get_data_by_field($ref, $auto_version_field_ref);
    
    debug("AUTO_VERSION: Current version for resource {$ref}: '{$current_version}'");
    
    if (empty($current_version))
        {
        // No version set, don't do anything
        debug("AUTO_VERSION: No version set, skipping");
        return false;
        }
    
    // Store the current version in the resource log for future reversion
    ps_query("UPDATE resource_log SET previous_value=? WHERE ref=?", array("s", $current_version, "i", $log_ref));
    
    // Increment the version
    $new_version = auto_version_increment_version($current_version, $auto_version_increment_type);
    
    debug("AUTO_VERSION: Incrementing version from '{$current_version}' to '{$new_version}'");
    
    // Update the version field
    update_field($ref, $auto_version_field_ref, $new_version);
    
    // Log the version change
    resource_log($ref, LOG_CODE_EDITED, 0, '', '', "Auto-incremented version from {$current_version} to {$new_version}");
    
    debug("AUTO_VERSION: Version updated successfully");
    
    return true;
    }

/**
 * Increment version number based on type
 * 
 * @param string $version Current version (e.g., "1", "1.1", "1.0.1")
 * @param string $type Increment type: "minor" or "patch"
 * @return string New version number
 */
function auto_version_increment_version($version, $type = "minor")
    {
    // Clean the version string
    $version = trim($version);
    
    // Split version into parts
    $parts = explode('.', $version);
    
    // Ensure we have at least major version
    if (count($parts) == 0 || empty($parts[0]))
        {
        return "1.0";
        }
    
    $major = intval($parts[0]);
    $minor = isset($parts[1]) ? intval($parts[1]) : 0;
    $patch = isset($parts[2]) ? intval($parts[2]) : 0;
    
    if ($type == "patch")
        {
        // Increment patch version (e.g., 1.0.1 → 1.0.2)
        $patch++;
        return "{$major}.{$minor}.{$patch}";
        }
    else
        {
        // Default: increment minor version (e.g., 1.1 → 1.2)
        $minor++;
        return "{$major}.{$minor}";
        }
    }
