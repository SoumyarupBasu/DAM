<?php
/**
 * Manual Trigger - Run this after creating a collection
 * URL: http://192.168.29.156/plugins/collection_id_in_name/trigger_now.php
 */

include __DIR__ . '/../../include/boot.php';

// Update all collections without IDs
$sql = "UPDATE collection 
        SET name = CONCAT(name, ' (ID-', ref, ')')
        WHERE name NOT LIKE '%(ID-%'";

ps_query($sql);

// Count total collections with IDs
$result = ps_query("SELECT COUNT(*) as total FROM collection WHERE name LIKE '%(ID-%'");
$updated = $result[0]['total'];

echo "<!DOCTYPE html><html><head><title>Collection ID Trigger</title></head><body>";
echo "<h2>✅ Collection IDs Updated!</h2>";
echo "<p>Updated <strong>{$updated}</strong> collections.</p>";
echo "<p><a href='../../pages/collections.php'>View Collections</a></p>";
echo "</body></html>";
?>