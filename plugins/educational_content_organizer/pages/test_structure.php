<?php
include '../../../include/boot.php';
include '../../../include/authenticate.php';

if (!checkperm('a'))
    {
    exit($lang['error-permissiondenied']);
    }

echo "<h1>Educational Content Structure Test</h1>";

echo "<h2>Collections Created</h2>";
$collections = ps_query("SELECT ref, name FROM collection WHERE ref BETWEEN 1000 AND 1100 ORDER BY ref");
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Collection ID</th><th>Collection Name</th></tr>";
foreach ($collections as $collection)
    {
    echo "<tr><td>{$collection['ref']}</td><td>{$collection['name']}</td></tr>";
    }
echo "</table>";

echo "<h2>Metadata Fields Created</h2>";
$fields = ps_query("SELECT ref, name, title FROM resource_type_field WHERE ref BETWEEN 92 AND 96 ORDER BY ref");
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Field ID</th><th>Field Name</th><th>Field Title</th></tr>";
foreach ($fields as $field)
    {
    echo "<tr><td>{$field['ref']}</td><td>{$field['name']}</td><td>{$field['title']}</td></tr>";
    }
echo "</table>";

echo "<h2>Dropdown Options</h2>";
foreach ($fields as $field)
    {
    echo "<h3>{$field['title']} (Field {$field['ref']})</h3>";
    $nodes = ps_query("SELECT name FROM node WHERE resource_type_field=? ORDER BY order_by", array("i", $field['ref']));
    echo "<ul>";
    foreach ($nodes as $node)
        {
        echo "<li>{$node['name']}</li>";
        }
    echo "</ul>";
    }

echo "<h2>How It Works</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
echo "<p><strong>Example:</strong> When you upload a file and set:</p>";
echo "<ul>";
echo "<li>Subject: MATH</li>";
echo "<li>Grade Level: Grade 3</li>";
echo "<li>State Curriculum: State 1</li>";
echo "<li>Content Type: Video</li>";
echo "</ul>";
echo "<p>The system will automatically add the resource to these collections:</p>";
echo "<ul>";
echo "<li>MATH (ID: 1000)</li>";
echo "<li>Grade 3 (ID: 1003)</li>";
echo "<li>Grade 3 - State 1 (ID: 1031)</li>";
echo "</ul>";
echo "</div>";

echo "<h2>Plugin Status</h2>";
$plugin = ps_query("SELECT * FROM plugins WHERE name='educational_content_organizer'");
if (count($plugin) > 0)
    {
    echo "<p style='color: green;'>✅ Plugin is installed and ready to use!</p>";
    echo "<p><a href='../setup.php'>Configure Plugin</a></p>";
    }
else
    {
    echo "<p style='color: red;'>❌ Plugin not found in database</p>";
    }
?>