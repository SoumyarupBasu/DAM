<?php

function HookSimple_collection_managerAllInitialise()
    {
    global $simple_collection_fieldvars;
    config_register_core_fieldvars("Simple Collection Manager plugin", $simple_collection_fieldvars);
    }

// Add menu item for collection template management
function HookSimple_collection_managerAllAdditional_header_content()
    {
    global $simple_collection_manager_enabled, $show_collection_ids;
    
    if (!$simple_collection_manager_enabled) return;
    
    // Add CSS for collection ID display
    if ($show_collection_ids)
        {
        echo "<style>
        .collection-id { font-weight: bold; color: #007cba; }
        .collection-math { color: #4CAF50; }
        .collection-science { color: #2196F3; }
        .collection-english { color: #FF9800; }
        .collection-social { color: #9C27B0; }
        </style>";
        }
    }

// Add menu item to collections menu
function HookSimple_collection_managerAllCollections_menu()
    {
    global $simple_collection_manager_enabled, $baseurl_short;
    
    if (!$simple_collection_manager_enabled) return;
    
    echo '<li><a href="' . $baseurl_short . 'plugins/simple_collection_manager/pages/manage.php">📋 Template Manager</a></li>';
    }