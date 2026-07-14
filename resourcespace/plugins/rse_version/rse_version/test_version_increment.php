<?php
/**
 * Test script for version auto-increment functionality
 * Access this file to test if the version increment function works correctly
 */

include_once "../../../include/boot.php";
include_once "../../../include/authenticate.php";

if (!checkperm("a")) {
    exit("Permission denied. Admin access required.");
}

echo "<h1>RSE Version Auto-Increment Test</h1>";

// Test the increment function
include_once __DIR__ . "/hooks/all.php";

echo "<h2>Testing Version Increment Function</h2>";

$test_cases = array(
    array("input" => "1.0", "type" => "minor", "expected" => "1.1"),
    array("input" => "1.5", "type" => "minor", "expected" => "1.6"),
    array("input" => "2.9", "type" => "minor", "expected" => "2.10"),
    array("input" => "1.0", "type" => "major", "expected" => "2.0"),
    array("input" => "5.3", "type" => "major", "expected" => "6.0"),
    array("input" => "", "type" => "minor", "expected" => "1.0"),
    array("input" => "v1.0", "type" => "minor", "expected" => "1.1"),
);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Input</th><th>Type</th><th>Expected</th><th>Result</th><th>Status</th></tr>";

foreach($test_cases as $test) {
    $result = rse_version_increment_version_number($test['input'], $test['type']);
    $status = ($result === $test['expected']) ? "✓ PASS" : "✗ FAIL";
    $color = ($result === $test['expected']) ? "green" : "red";
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($test['input']) . "</td>";
    echo "<td>" . htmlspecialchars($test['type']) . "</td>";
    echo "<td>" . htmlspecialchars($test['expected']) . "</td>";
    echo "<td>" . htmlspecialchars($result) . "</td>";
    echo "<td style='color: $color; font-weight: bold;'>$status</td>";
    echo "</tr>";
}

echo "</table>";

// Check configuration
echo "<h2>Configuration Check</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";

global $rse_version_auto_increment_field, $rse_version_increment_type;

echo "<tr><td>Auto-increment field ID</td><td>" . (isset($rse_version_auto_increment_field) ? $rse_version_auto_increment_field : "NOT SET") . "</td></tr>";
echo "<tr><td>Increment type</td><td>" . (isset($rse_version_increment_type) ? $rse_version_increment_type : "NOT SET") . "</td></tr>";

// Check if field exists
if(isset($rse_version_auto_increment_field) && $rse_version_auto_increment_field > 0) {
    $field_check = ps_query("SELECT ref, name, title FROM resource_type_field WHERE ref = ?", array("i", $rse_version_auto_increment_field));
    if(count($field_check) > 0) {
        echo "<tr><td>Field exists</td><td style='color: green;'>✓ YES - " . htmlspecialchars($field_check[0]['title']) . " (name: " . htmlspecialchars($field_check[0]['name']) . ")</td></tr>";
    } else {
        echo "<tr><td>Field exists</td><td style='color: red;'>✗ NO - Field ID " . $rse_version_auto_increment_field . " not found!</td></tr>";
    }
}

echo "</table>";

// Test on a real resource (if provided)
$test_resource = getval("test_resource", 0, true);
if($test_resource > 0) {
    echo "<h2>Testing on Resource #$test_resource</h2>";
    
    $current_value = get_data_by_field($test_resource, $rse_version_auto_increment_field);
    echo "<p><strong>Current version value:</strong> " . htmlspecialchars($current_value) . "</p>";
    
    $new_value = rse_version_increment_version_number($current_value, $rse_version_increment_type);
    echo "<p><strong>Would increment to:</strong> " . htmlspecialchars($new_value) . "</p>";
    
    if(getval("do_update", "") == "yes") {
        $errors = array();
        update_field($test_resource, $rse_version_auto_increment_field, $new_value, $errors);
        
        if(empty($errors)) {
            echo "<p style='color: green;'><strong>✓ Successfully updated to $new_value</strong></p>";
            resource_log($test_resource, LOG_CODE_EDITED, $rse_version_auto_increment_field, "Manual test increment", $current_value, $new_value);
        } else {
            echo "<p style='color: red;'><strong>✗ Error updating:</strong> " . implode(", ", $errors) . "</p>";
        }
    } else {
        echo "<p><a href='?test_resource=$test_resource&do_update=yes'>Click here to actually update this resource</a></p>";
    }
}

echo "<h2>Test on Your Resource</h2>";
echo "<form method='get'>";
echo "Resource ID: <input type='text' name='test_resource' value='' />";
echo " <input type='submit' value='Test' />";
echo "</form>";

echo "<hr>";
echo "<p><strong>Instructions:</strong></p>";
echo "<ol>";
echo "<li>The function tests should all show PASS</li>";
echo "<li>Configuration should show field ID 89 and increment type 'minor'</li>";
echo "<li>Field should exist and be named 'versionnum'</li>";
echo "<li>Test on a real resource by entering its ID above</li>";
echo "<li>Try replacing a file on a resource to see if auto-increment works</li>";
echo "</ol>";
?>
