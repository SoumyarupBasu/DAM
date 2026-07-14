<?php

/**
 * Hook for collections.php page
 */

/**
 * Hook: Afterpageload
 * Runs after the collections page loads
 */
function HookCollection_id_in_nameCollectionsAfterpageload()
{
    // Update any collections without IDs
    $sql = "UPDATE collection 
            SET name = CONCAT(name, ' (ID-', ref, ')')
            WHERE name NOT LIKE '%(ID-%'
            AND created >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
    
    sql_query($sql);
}

/**
 * Hook: Pagefooter
 * Runs at the end of the page
 */
function HookCollection_id_in_nameCollectionsPagefooter()
{
    // Final check before page ends
    $sql = "UPDATE collection 
            SET name = CONCAT(name, ' (ID-', ref, ')')
            WHERE name NOT LIKE '%(ID-%'
            AND created >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
    
    sql_query($sql);
}