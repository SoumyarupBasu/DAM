<?php
// Check Grade 2 structure in detail

include "include/boot.php";
include "include/authenticate.php";

header('Content-Type: text/plain');

echo "=== Checking Grade 2 Structure ===\n\n";

// Find all Grades under MATH (ID-54)
$grades = ps_query("SELECT ref, name FROM collection WHERE parent = 54 ORDER BY name", []);

echo "All children of MATH (ID-54):\n";
foreach ($grades as $grade) {
    echo "  - " . $grade['name'] . " (ID-" . $grade['ref'] . ")\n";
    
    // Check if this is Grade 2
    $clean_name = preg_replace('/\s*\(ID-\d+\)$/', '', $grade['name']);
    if (stripos($clean_name, 'Grade 2') !== false) {
        echo "\n    >>> This is Grade 2! <<<\n";
        
        // Get its children
        $children = ps_query("SELECT ref, name FROM collection WHERE parent = ?", ["i", $grade['ref']]);
        
        if (empty($children)) {
            echo "    Grade 2 has NO children\n";
        } else {
            echo "    Grade 2 has " . count($children) . " children:\n";
            foreach ($children as $child) {
                $child_clean = preg_replace('/\s*\(ID-\d+\)$/', '', $child['name']);
                echo "      - " . $child['name'] . "\n";
                
                // Check if it would be filtered
                $will_skip = (
                    stripos($child_clean, 'State 2') !== false || 
                    stripos($child_clean, 'test math') !== false || 
                    stripos($child_clean, 'TEST') !== false ||
                    stripos($child_clean, 'digit') !== false ||
                    stripos($child_clean, 'number') !== false
                );
                
                if ($will_skip) {
                    echo "        [WILL BE FILTERED OUT]\n";
                }
            }
        }
        echo "\n";
    }
}
