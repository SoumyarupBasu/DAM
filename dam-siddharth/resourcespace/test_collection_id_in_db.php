<?php
include "include/boot.php";
include "include/authenticate.php";

if (!checkperm("h")) {
    exit("Need admin permissions");
}

echo "<h1>Test: Check if Collection IDs are in Database</h1>";

// Get last 10 collections
$collections = ps_query("SELECT ref, name, type FROM collection ORDER BY ref DESC LIMIT 10");

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Has ID in Name?</th></tr>";

foreach ($collections as $col) {
    $has_id = preg_match('/\(ID-' . $col['ref'] . '\)/', $col['name']);
    $color = $has_id ? 'green' : 'red';
    $status = $has_id ? '✓ YES' : '✗ NO';
    
    echo "<tr>";
    echo "<td>" . $col['ref'] . "</td>";
    echo "<td>" . htmlspecialchars($col['name']) . "</td>";
    echo "<td>" . $col['type'] . "</td>";
    echo "<td style='color: $color; font-weight: bold;'>$status</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h2>Create a Test Collection</h2>";

if (isset($_POST['test_create'])) {
    $test_name = "Test " . date("H:i:s");
    $new_ref = create_collection($userref, $test_name);
    
    echo "<p style='color: green;'>✓ Created collection with ref: $new_ref</p>";
    
    // Check immediately
    $check = ps_query("SELECT name FROM collection WHERE ref = ?", array("i", $new_ref));
    if (count($check) > 0) {
        $db_name = $check[0]['name'];
        echo "<p><strong>Name in database:</strong> " . htmlspecialchars($db_name) . "</p>";
        
        if (strpos($db_name, "(ID-$new_ref)") !== false) {
            echo "<p style='color: green; font-weight: bold;'>✓ ID IS in the database!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>✗ ID is NOT in the database!</p>";
        }
    }
    
    echo "<p><a href='?'>Refresh to see in list</a></p>";
}

echo "<form method='post'>";
echo "<button type='submit' name='test_create'>Create Test Collection</button>";
echo "</form>";
?>
