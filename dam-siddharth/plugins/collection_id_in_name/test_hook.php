<?php
// Test if the hook is working
include __DIR__ . '/../../include/boot.php';
include __DIR__ . '/../../include/collections_functions.php';

echo "Testing collection creation with ID append...\n\n";

// Create a test collection
$test_name = "HookTest_" . time();
$collection_id = create_collection(1, $test_name);

echo "Created collection ID: $collection_id\n";

// Check the name
$actual_name = sql_value("SELECT name FROM collection WHERE ref = $collection_id", "");
echo "Collection name: $actual_name\n\n";

// Verify
if (strpos($actual_name, "(ID-$collection_id)") !== false) {
    echo "✅ SUCCESS! ID was appended automatically!\n";
    echo "Expected: $test_name (ID-$collection_id)\n";
    echo "Got: $actual_name\n";
} else {
    echo "❌ FAILED! ID was NOT appended.\n";
    echo "Expected: $test_name (ID-$collection_id)\n";
    echo "Got: $actual_name\n";
}
?>