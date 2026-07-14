<?php
/**
 * AJAX endpoint to append collection IDs
 * Called automatically by JavaScript after collection creation
 */

include __DIR__ . '/../../include/boot.php';

// Update collections created in the last 2 minutes without IDs
$sql = "UPDATE collection 
        SET name = CONCAT(name, ' (ID-', ref, ')')
        WHERE name NOT LIKE '%(ID-%'
        AND created >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)";

ps_query($sql);

// Count updated
$result = ps_query("SELECT COUNT(*) as total FROM collection WHERE name LIKE '%(ID-%' AND created >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)");
$updated = isset($result[0]['total']) ? $result[0]['total'] : 0;

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'updated' => $updated,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>