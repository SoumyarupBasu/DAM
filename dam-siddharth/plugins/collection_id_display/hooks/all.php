<?php

// Include the configuration
include_once dirname(__FILE__) . '/../config/config.php';

/**
 * Hook to modify collection names in all collection listings
 */
function HookCollection_id_displayAllRender_collection_list($collections)
    {
    global $show_collection_ids;
    
    if (!$show_collection_ids || empty($collections))
        {
        return $collections;
        }
    
    // Modify each collection to show ID
    foreach ($collections as &$collection)
        {
        if (isset($collection['ref']) && isset($collection['name']))
            {
            $collection['name'] = format_collection_name_with_id($collection['ref'], $collection['name']);
            }
        }
    
    return $collections;
    }

/**
 * Hook to modify collection display in search results
 */
function HookCollection_id_displayAllSearch_collection_results($results)
    {
    global $show_ids_in_search;
    
    if (!$show_ids_in_search || empty($results))
        {
        return $results;
        }
    
    foreach ($results as &$result)
        {
        if (isset($result['ref']) && isset($result['name']))
            {
            $result['name'] = format_collection_name_with_id($result['ref'], $result['name']);
            }
        }
    
    return $results;
    }

/**
 * Hook to add CSS styling for collection IDs
 */
function HookCollection_id_displayAllAdditional_header_content()
    {
    global $show_collection_ids, $id_color_coding;
    
    if (!$show_collection_ids)
        {
        return;
        }
    
    echo "<style>\n";
    echo "/* Collection ID Display Styling */\n";
    echo ".collection-with-id { display: inline-block; }\n";
    echo ".collection-id { font-weight: bold; margin-right: 5px; }\n";
    echo ".collection-id-badge { \n";
    echo "    background: #f0f0f0; \n";
    echo "    padding: 2px 6px; \n";
    echo "    border-radius: 3px; \n";
    echo "    font-size: 0.9em; \n";
    echo "    margin-right: 5px; \n";
    echo "    font-weight: bold;\n";
    echo "}\n";
    
    // Add color coding for different series
    foreach ($id_color_coding as $range => $color)
        {
        list($start, $end) = explode('-', $range);
        echo ".collection-id-{$start}-{$end} { color: {$color}; }\n";
        echo ".collection-id-badge-{$start}-{$end} { background-color: {$color}; color: white; }\n";
        }
    
    echo "/* Collection list styling */\n";
    echo ".CollectionPanelShell .collection-name { font-weight: normal; }\n";
    echo ".CollectionPanelShell .collection-id { font-weight: bold; }\n";
    echo ".collection-search-result { padding: 10px; border-bottom: 1px solid #eee; }\n";
    echo ".collection-search-result:hover { background: #f8f9fa; }\n";
    echo "</style>\n";
    }

/**
 * Hook to modify collection names when they are displayed
 */
function HookCollection_id_displayAllCollection_name_display($collection_id, $collection_name)
    {
    global $show_collection_ids;
    
    if (!$show_collection_ids)
        {
        return $collection_name;
        }
    
    return format_collection_name_with_id($collection_id, $collection_name);
    }

/**
 * Hook to enable ID-based search
 */
function HookCollection_id_displayAllCollection_search($search_term)
    {
    global $enable_id_search;
    
    if (!$enable_id_search || !is_numeric(trim($search_term)))
        {
        return false;
        }
    
    $collection_id = intval(trim($search_term));
    
    // Search for collections with this exact ID
    $results = ps_query(
        "SELECT ref, name, user, created, public FROM collection 
         WHERE ref=? 
         ORDER BY ref",
        array("i", $collection_id)
    );
    
    return $results;
    }

/**
 * Hook to modify collection names in dropdown selects
 */
function HookCollection_id_displayAllCollection_dropdown_options($collections)
    {
    global $show_collection_ids;
    
    if (!$show_collection_ids || empty($collections))
        {
        return $collections;
        }
    
    foreach ($collections as &$collection)
        {
        if (isset($collection['ref']) && isset($collection['name']))
            {
            $collection['name'] = format_collection_name_with_id($collection['ref'], $collection['name']);
            }
        }
    
    return $collections;
    }

/**
 * Hook to modify collection names in breadcrumbs
 */
function HookCollection_id_displayAllBreadcrumb_collection_name($collection_id, $collection_name)
    {
    global $show_collection_ids;
    
    if (!$show_collection_ids)
        {
        return $collection_name;
        }
    
    return format_collection_name_with_id($collection_id, $collection_name);
    }

/**
 * Hook to modify collection names in page titles
 */
function HookCollection_id_displayAllPage_title_collection($collection_id, $collection_name)
    {
    global $show_collection_ids;
    
    if (!$show_collection_ids)
        {
        return $collection_name;
        }
    
    return format_collection_name_with_id($collection_id, $collection_name);
    }