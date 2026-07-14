<?php

/**
 * Hook to modify collection display in collections.php page
 */
function HookCollection_id_displayCollectionsRender_collection_list($collections)
    {
    global $show_collection_ids, $id_display_format;
    
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
 * Hook to add ID search functionality to collection search
 */
function HookCollection_id_displayCollectionsCollection_search($search_term)
    {
    global $enable_id_search;
    
    if (!$enable_id_search || !is_numeric(trim($search_term)))
        {
        return false;
        }
    
    $collection_id = intval(trim($search_term));
    
    // Search for collections with this exact ID or containing this ID in name
    $results = ps_query(
        "SELECT ref, name, user, created, public FROM collection 
         WHERE ref=? OR name LIKE ? 
         ORDER BY ref",
        array("i", $collection_id, "s", "%{$collection_id}%")
    );
    
    return $results;
    }

/**
 * Hook to modify collection names in search results
 */
function HookCollection_id_displayCollectionsSearch_collection_results($results)
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
            $result['display_name'] = display_collection_with_id($result['ref'], $result['name'], true);
            }
        }
    
    return $results;
    }

/**
 * Add CSS for collection ID styling in collections page
 */
function HookCollection_id_displayCollectionsAdditional_header_content()
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
 * Enhanced collection name display with proper ID formatting
 */
function enhanced_collection_display($collection_id, $collection_name, $show_badge = false)
    {
    global $show_collection_ids, $id_color_coding;
    
    if (!$show_collection_ids)
        {
        return escape($collection_name);
        }
    
    // Get color for this ID series
    $color = '#666666'; // default
    foreach ($id_color_coding as $range => $series_color)
        {
        list($start, $end) = explode('-', $range);
        if ($collection_id >= intval($start) && $collection_id <= intval($end))
            {
            $color = $series_color;
            break;
            }
        }
    
    $formatted_name = format_collection_name_with_id($collection_id, $collection_name);
    
    if ($show_badge)
        {
        return "<span class='collection-with-id'>" .
               "<span class='collection-id-badge' style='background-color: {$color}; color: white;'>{$collection_id}</span>" .
               "<span class='collection-name'>" . escape($collection_name) . "</span>" .
               "</span>";
        }
    else
        {
        return "<span class='collection-with-id' style='color: {$color};'>" . 
               "<span class='collection-id'>[{$collection_id}]</span> " .
               "<span class='collection-name'>" . escape($collection_name) . "</span>" .
               "</span>";
        }
    }