<?php

/**
 * Collection ID in Name - Hooks
 * Updates collections immediately after they're created
 */

/**
 * Hook: Initialise
 * Runs after ResourceSpace is fully loaded
 */
function HookCollection_id_in_nameAllInitialise()
{
    // Update collections created in the last 30 seconds
    ps_query("
        UPDATE collection 
        SET name = CONCAT(name, ' (ID-', ref, ')')
        WHERE name NOT LIKE '%(ID-%'
        AND created >= DATE_SUB(NOW(), INTERVAL 30 SECOND)
    ");
}

/**
 * Hook: Beforeheader
 * Runs before page header on every page
 */
function HookCollection_id_in_nameAllBeforeheader()
{
    // Update collections created in the last 30 seconds
    ps_query("
        UPDATE collection 
        SET name = CONCAT(name, ' (ID-', ref, ')')
        WHERE name NOT LIKE '%(ID-%'
        AND created >= DATE_SUB(NOW(), INTERVAL 30 SECOND)
    ");
}

/**
 * Hook: Afterheader
 * Runs after page header
 */
function HookCollection_id_in_nameAllAfterheader()
{
    // Update collections created in the last 30 seconds
    ps_query("
        UPDATE collection 
        SET name = CONCAT(name, ' (ID-', ref, ')')
        WHERE name NOT LIKE '%(ID-%'
        AND created >= DATE_SUB(NOW(), INTERVAL 30 SECOND)
    ");
}

/**
 * Hook: Pagefooter
 * Runs at page footer
 */
function HookCollection_id_in_nameAllPagefooter()
{
    // Final update before page ends
    ps_query("
        UPDATE collection 
        SET name = CONCAT(name, ' (ID-', ref, ')')
        WHERE name NOT LIKE '%(ID-%'
        AND created >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)
    ");
}
?>