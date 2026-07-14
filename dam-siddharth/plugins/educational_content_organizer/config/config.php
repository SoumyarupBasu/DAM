<?php

// Field references for educational content organization
$edu_subject_field = "92";
$edu_grade_field = "93";
$edu_state_field = "94";
$edu_content_type_field = "95";
$edu_asset_type_field = "96";

// Enable automatic collection assignment
$edu_auto_assign_collections = true;

// Collection ID mapping
$edu_collection_mapping = array(
    // Main subject collections
    'MATH' => 1000,
    
    // Grade collections
    'Grade 1' => 1001,
    'Grade 2' => 1002,
    'Grade 3' => 1003,
    'Grade 4' => 1004,
    'Grade 5' => 1005,
    'Grade 6' => 1006,
    'Grade 7' => 1007,
    'Grade 8' => 1008,
    
    // State-specific collections (Grade + State combinations)
    'Grade 1_State 1' => 1011,
    'Grade 1_State 2' => 1012,
    'Grade 2_State 1' => 1021,
    'Grade 2_State 2' => 1022,
    'Grade 3_State 1' => 1031,
    'Grade 3_State 2' => 1032,
    'Grade 4_State 1' => 1041,
    'Grade 4_State 2' => 1042,
    'Grade 5_State 1' => 1051,
    'Grade 5_State 2' => 1052,
    'Grade 6_State 1' => 1061,
    'Grade 6_State 2' => 1062,
    'Grade 7_State 1' => 1071,
    'Grade 7_State 2' => 1072,
    'Grade 8_State 1' => 1081,
    'Grade 8_State 2' => 1082
);

// Add field vars to prevent deletion if plugin is in use
$edu_fieldvars = array(
    "edu_subject_field", 
    "edu_grade_field", 
    "edu_state_field", 
    "edu_content_type_field", 
    "edu_asset_type_field",
    "edu_auto_assign_collections"
);