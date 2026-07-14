<?php

// Collection Template Manager Configuration

// Available templates with their root collection IDs
$collection_templates = array(
    'MATH' => array(
        'name' => 'MATH Template',
        'description' => 'Complete Math curriculum structure with all grades and states',
        'root_collection' => 1000,
        'includes_children' => true,
        'target_series' => array(
            'SCIENCE' => 2000,
            'ENGLISH' => 3000,
            'SOCIAL_STUDIES' => 4000,
            'GENERAL' => 5000
        )
    )
);

// Template copy rules
$template_copy_rules = array(
    // What to copy from the template
    'copy_structure' => true,        // Copy the hierarchical structure
    'copy_metadata' => true,         // Copy collection metadata/settings
    'copy_permissions' => true,      // Copy access permissions
    'copy_keywords' => true,         // Copy keywords
    'copy_resources' => false,       // Don't copy actual resources (just structure)
    
    // ID assignment rules
    'use_sequential_ids' => true,    // Use sequential IDs from target series
    'preserve_hierarchy' => true,    // Maintain parent-child relationships
    'update_names' => true,          // Update collection names to reflect new subject
);

// Require template usage
$require_template_usage = true;     // Users must copy from template before creating individual collections

// Template access control
$template_access_groups = array();  // Empty = all groups can use templates

// Add field vars to prevent deletion if plugin is in use
$template_fieldvars = array(
    "require_template_usage",
    "template_access_groups"
);