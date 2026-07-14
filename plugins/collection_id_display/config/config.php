<?php

// Collection ID Display Plugin Configuration

// Show collection IDs in collection names
$show_collection_ids = true;

// Enable searching by collection ID numbers
$enable_id_search = true;

// ID display format: 'brackets', 'badge', or 'prefix'
$id_display_format = 'brackets';

// Show IDs in search results
$show_ids_in_search = true;

// Show IDs when browsing collections
$show_ids_in_browse = true;

// Color coding for different ID series
$id_color_coding = array(
    '1000-1999' => '#4CAF50',  // Green - MATH
    '2000-2999' => '#2196F3',  // Blue - SCIENCE  
    '3000-3999' => '#FF9800',  // Orange - ENGLISH
    '4000-4999' => '#9C27B0',  // Purple - SOCIAL STUDIES
    '5000-5999' => '#607D8B',  // Blue Grey - GENERAL
    '7000-7999' => '#F44336',  // Red - TEMPLATES
);

/**
 * Format collection name with ID
 */
function format_collection_name_with_id($collection_id, $collection_name)
    {
    global $id_display_format;
    
    // Remove existing ID if present
    $clean_name = preg_replace('/^\[\d+\]\s*/', '', $collection_name);
    
    switch ($id_display_format)
        {
        case 'badge':
            return "<span class='collection-id-badge'>{$collection_id}</span> {$clean_name}";
        case 'prefix':
            return "{$collection_id}: {$clean_name}";
        case 'brackets':
        default:
            return "[{$collection_id}] {$clean_name}";
        }
    }

/**
 * Get color for collection ID series
 */
function get_collection_id_color($collection_id)
    {
    global $id_color_coding;
    
    foreach ($id_color_coding as $range => $color)
        {
        list($start, $end) = explode('-', $range);
        if ($collection_id >= intval($start) && $collection_id <= intval($end))
            {
            return $color;
            }
        }
    
    return '#666666'; // Default color
    }

/**
 * Display collection with ID and styling
 */
function display_collection_with_id($collection_id, $collection_name, $show_badge = false)
    {
    global $show_collection_ids;
    
    if (!$show_collection_ids)
        {
        return escape($collection_name);
        }
    
    $color = get_collection_id_color($collection_id);
    $formatted_name = format_collection_name_with_id($collection_id, $collection_name);
    
    if ($show_badge)
        {
        return "<span class='collection-with-id'>" .
               "<span class='collection-id-badge' style='background-color: {$color}; color: white;'>{$collection_id}</span>" .
               "<span class='collection-name'>" . escape(preg_replace('/^\[\d+\]\s*/', '', $collection_name)) . "</span>" .
               "</span>";
        }
    else
        {
        return "<span class='collection-with-id' style='color: {$color};'>" . 
               escape($formatted_name) .
               "</span>";
        }
    }