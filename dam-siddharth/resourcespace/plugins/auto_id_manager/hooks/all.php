<?php

function HookAuto_id_managerAllInitialise()
    {
    global $auto_id_fieldvars;
    config_register_core_fieldvars("Auto ID Manager plugin", $auto_id_fieldvars);
    }

function HookAuto_id_managerAllBeforecollectioncreate(&$name, &$user, &$public, &$allow_changes)
    {
    global $auto_id_enabled, $auto_id_series, $auto_id_default_series, $auto_id_rules, $auto_id_show_in_name;
    
    if (!$auto_id_enabled)
        {
        return false;
        }
    
    // Determine which series this collection should belong to
    $series = determine_collection_series($name);
    
    // Get the next available ID for this series
    $next_id = get_next_series_id($series);
    
    if ($next_id === false)
        {
        debug("AUTO_ID: Could not get next ID for series: {$series}");
        return false;
        }
    
    // Check if this ID is already taken
    if (collection_id_exists($next_id))
        {
        debug("AUTO_ID: ID {$next_id} already exists, finding next available");
        $next_id = find_next_available_id($series);
        }
    
    if ($next_id === false)
        {
        debug("AUTO_ID: No available IDs in series: {$series}");
        return false;
        }
    
    // Update the collection name to include ID if configured
    if ($auto_id_show_in_name && !preg_match('/\(\d+\)/', $name))
        {
        $name = $name . " ({$next_id})";
        }
    
    // Force the collection to use our assigned ID
    force_collection_id($next_id);
    
    // Update the series current pointer
    update_series_current_id($series, $next_id);
    
    debug("AUTO_ID: Assigned ID {$next_id} to collection '{$name}' in series '{$series}'");
    
    return true;
    }

function determine_collection_series($name)
    {
    global $auto_id_rules, $auto_id_default_series;
    
    $name_lower = strtolower($name);
    
    // Check each rule to see if it matches
    foreach ($auto_id_rules as $keyword => $series)
        {
        if (strpos($name_lower, $keyword) !== false)
            {
            debug("AUTO_ID: Collection '{$name}' matched keyword '{$keyword}' -> series '{$series}'");
            return $series;
            }
        }
    
    debug("AUTO_ID: Collection '{$name}' using default series '{$auto_id_default_series}'");
    return $auto_id_default_series;
    }

function get_next_series_id($series)
    {
    global $auto_id_series;
    
    if (!isset($auto_id_series[$series]))
        {
        debug("AUTO_ID: Series '{$series}' not found in configuration");
        return false;
        }
    
    $series_config = $auto_id_series[$series];
    $next_id = $series_config['current'] + 1;
    
    // Check if we've exceeded the series range
    if ($next_id > $series_config['end'])
        {
        debug("AUTO_ID: Series '{$series}' has reached maximum ID ({$series_config['end']})");
        return false;
        }
    
    return $next_id;
    }

function find_next_available_id($series)
    {
    global $auto_id_series;
    
    if (!isset($auto_id_series[$series]))
        {
        return false;
        }
    
    $series_config = $auto_id_series[$series];
    
    // Search for next available ID in the series range
    for ($id = $series_config['current'] + 1; $id <= $series_config['end']; $id++)
        {
        if (!collection_id_exists($id))
            {
            return $id;
            }
        }
    
    return false;
    }

function collection_id_exists($id)
    {
    $result = ps_value("SELECT COUNT(*) FROM collection WHERE ref=?", array("i", $id), 0);
    return ($result > 0);
    }

function force_collection_id($id)
    {
    // This is a bit tricky - we need to ensure the next collection gets this specific ID
    // We'll store it in a global variable that the collection creation process can use
    global $forced_collection_id;
    $forced_collection_id = $id;
    
    // Also set it in the database if possible
    ps_query("INSERT INTO collection (ref, name, user, created) VALUES (?, 'TEMP_PLACEHOLDER', 1, NOW()) ON DUPLICATE KEY UPDATE name=name", array("i", $id));
    ps_query("DELETE FROM collection WHERE ref=? AND name='TEMP_PLACEHOLDER'", array("i", $id));
    }

function update_series_current_id($series, $new_id)
    {
    // Update the configuration (this would need to be persisted to database or config file)
    global $auto_id_series;
    $auto_id_series[$series]['current'] = $new_id;
    
    // Store in database for persistence
    ps_query("INSERT INTO sysvars (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value=?", 
             array("s", "auto_id_series_{$series}_current", "s", $new_id, "s", $new_id));
    
    debug("AUTO_ID: Updated series '{$series}' current ID to {$new_id}");
    }

// Load current series positions from database on initialization
function load_series_current_positions()
    {
    global $auto_id_series;
    
    foreach ($auto_id_series as $series => &$config)
        {
        $current = ps_value("SELECT value FROM sysvars WHERE name=?", 
                           array("s", "auto_id_series_{$series}_current"), $config['current']);
        $config['current'] = intval($current);
        }
    }

// Initialize series positions when plugin loads
load_series_current_positions();