<?php

// Configuration variables
$template_fieldvars = array(
    "require_template_usage",
    "template_access_groups",
    "template_modify_groups",
    "show_collection_ids",
    "enable_id_search",
    "id_display_format"
);

// Default values
$require_template_usage = false;
$template_access_groups = array();
$template_modify_groups = array();
$show_collection_ids = true;
$enable_id_search = true;
$id_display_format = "[ID] Name";

function HookCollection_template_managerAllInitialise()
    {
    global $template_fieldvars;
    config_register_core_fieldvars("Collection Template Manager plugin", $template_fieldvars);
    }

// Add CSS for collection ID display
function HookCollection_template_managerAllAdditional_header_content()
    {
    global $show_collection_ids;
    
    if (!$show_collection_ids) return;
    
    echo "<style>
    .collection-id { font-weight: bold; color: #007cba; }
    .collection-math { color: #4CAF50; }
    .collection-science { color: #2196F3; }
    .collection-english { color: #FF9800; }
    .collection-social { color: #9C27B0; }
    .collection-general { color: #607D8B; }
    .collection-template { color: #F44336; }
    </style>";
    }

// Simple template copying function
function copy_collection_template($template_name, $target_subject, $user_id)
    {
    // Define series mappings
    $series_mapping = array(
        'SCIENCE' => 2000,
        'ENGLISH' => 3000,
        'SOCIAL_STUDIES' => 4000,
        'GENERAL' => 5000
    );
    
    if (!isset($series_mapping[$target_subject]))
        {
        return false;
        }
    
    $start_id = $series_mapping[$target_subject];
    
    // Get MATH collections (1000-1099 range)
    $math_collections = ps_query("SELECT * FROM collection WHERE ref BETWEEN 1000 AND 1099 ORDER BY ref");
    
    if (empty($math_collections))
        {
        return false;
        }
    
    $created = 0;
    $id_mapping = array();
    
    foreach ($math_collections as $math_col)
        {
        // Calculate new ID (preserve the offset from 1000)
        $offset = $math_col['ref'] - 1000;
        $new_id = $start_id + $offset;
        
        // Check if collection already exists
        $exists = ps_value("SELECT COUNT(*) FROM collection WHERE ref=?", array("i", $new_id), 0);
        if ($exists > 0) continue;
        
        // Create new collection name
        $old_name = $math_col['name'];
        // Remove existing ID brackets if present
        $clean_name = preg_replace('/^\[\d+\]\s*/', '', $old_name);
        // Replace MATH with target subject
        $new_name = str_replace('MATH', $target_subject, $clean_name);
        // Add new ID
        $new_name = "[{$new_id}] {$new_name}";
        
        // Create new collection
        $result = ps_query(
            "INSERT INTO collection (ref, name, user, created, public, allow_changes, keywords) VALUES (?, ?, ?, NOW(), ?, ?, ?)",
            array(
                "i", $new_id, 
                "s", $new_name, 
                "i", $user_id, 
                "i", $math_col['public'], 
                "i", $math_col['allow_changes'], 
                "s", $math_col['keywords']
            )
        );
        
        if ($result)
            {
            $created++;
            $id_mapping[$math_col['ref']] = $new_id;
            }
        }
    
    return array(
        'collections_created' => $created,
        'id_mapping' => $id_mapping
    );
    }