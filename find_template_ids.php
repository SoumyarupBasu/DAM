<?php
// Find the correct IDs for templates

include "include/boot.php";
include "include/authenticate.php";

echo "<h2>Finding Template Starting Points</h2>";

// Find Grade 3 (should have State 1 and State 2)
echo "<h3>Looking for Grade 3 (for Template 2):</h3>";
$grade3 = ps_query("SELECT ref, name, parent FROM collection WHERE name LIKE '%Grade 3%' AND parent = 54", []);
if (!empty($grade3)) {
    foreach ($grade3 as $g) {
        echo "Found: " . htmlspecialchars($g['name']) . " (ID-" . $g['ref'] . ")<br>";
        
        // Check its children
        $children = ps_query("SELECT ref, name FROM collection WHERE parent = ?", ["i", $g['ref']]);
        echo "Children:<br>";
        foreach ($children as $child) {
            echo "&nbsp;&nbsp;- " . htmlspecialchars($child['name']) . " (ID-" . $child['ref'] . ")<br>";
        }
    }
} else {
    echo "No Grade 3 found!<br>";
}

// Find State 1 (should have Video, Worksheet, etc.)
echo "<h3>Looking for State 1 (for Template 3):</h3>";
$state1 = ps_query("SELECT ref, name, parent FROM collection WHERE name LIKE '%State 1%'", []);
if (!empty($state1)) {
    foreach ($state1 as $s) {
        echo "Found: " . htmlspecialchars($s['name']) . " (ID-" . $s['ref'] . ")<br>";
        
        // Check its children
        $children = ps_query("SELECT ref, name FROM collection WHERE parent = ?", ["i", $s['ref']]);
        echo "Children:<br>";
        foreach ($children as $child) {
            echo "&nbsp;&nbsp;- " . htmlspecialchars($child['name']) . " (ID-" . $child['ref'] . ")<br>";
        }
    }
} else {
    echo "No State 1 found!<br>";
}

echo "<h3>All children of MATH (ID-54):</h3>";
$math_children = ps_query("SELECT ref, name FROM collection WHERE parent = 54 ORDER BY name", []);
foreach ($math_children as $child) {
    echo htmlspecialchars($child['name']) . " (ID-" . $child['ref'] . ")<br>";
}
