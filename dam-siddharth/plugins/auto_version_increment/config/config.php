<?php

// Field reference for the version metadata field
// You need to set this to the actual field ref of your version field
$auto_version_field_ref = "";

// Version increment format: "minor" for 1.1, 1.2, etc. or "patch" for 1.0.1, 1.0.2, etc.
$auto_version_increment_type = "minor";

// Add field vars to prevent deletion if plugin is in use
$auto_version_fieldvars = array("auto_version_field_ref", "auto_version_increment_type");
