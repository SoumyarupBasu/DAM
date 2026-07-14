<?php

// Auto ID Manager Configuration

// ID Series Configuration
$auto_id_series = array(
    // Educational Content Series
    'MATH' => array('start' => 1000, 'end' => 1999, 'current' => 1082), // Next: 1083
    'SCIENCE' => array('start' => 2000, 'end' => 2999, 'current' => 2000), // Next: 2001
    'ENGLISH' => array('start' => 3000, 'end' => 3999, 'current' => 3000), // Next: 3001
    'SOCIAL_STUDIES' => array('start' => 4000, 'end' => 4999, 'current' => 4000), // Next: 4001
    
    // General Content Series
    'GENERAL' => array('start' => 5000, 'end' => 5999, 'current' => 5000), // Next: 5001
    'ADMIN' => array('start' => 9000, 'end' => 9999, 'current' => 9000), // Next: 9001
);

// Default series for new collections (if no category specified)
$auto_id_default_series = 'GENERAL';

// Enable automatic ID assignment
$auto_id_enabled = true;

// Show ID in collection name (e.g., "MATH (1000)" instead of just "MATH")
$auto_id_show_in_name = true;

// ID assignment rules
$auto_id_rules = array(
    // Keywords that trigger specific series
    'math' => 'MATH',
    'mathematics' => 'MATH',
    'science' => 'SCIENCE',
    'physics' => 'SCIENCE',
    'chemistry' => 'SCIENCE',
    'biology' => 'SCIENCE',
    'english' => 'ENGLISH',
    'language' => 'ENGLISH',
    'literature' => 'ENGLISH',
    'social' => 'SOCIAL_STUDIES',
    'history' => 'SOCIAL_STUDIES',
    'geography' => 'SOCIAL_STUDIES',
    'grade' => 'MATH', // Grade collections go under MATH series by default
);

// Add field vars to prevent deletion if plugin is in use
$auto_id_fieldvars = array(
    "auto_id_enabled", 
    "auto_id_default_series", 
    "auto_id_show_in_name"
);