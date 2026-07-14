<?php
echo "<h1>Simple Plugin Test</h1>";
echo "<p>If you can see this, the plugin directory is accessible.</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";

// Test if we can include ResourceSpace
if (file_exists('../../../include/boot.php'))
    {
    echo "<p>✅ ResourceSpace boot file found</p>";
    }
else
    {
    echo "<p>❌ ResourceSpace boot file not found</p>";
    }

// Test plugin database entry
try {
    include '../../../include/boot.php';
    $plugin_check = ps_query("SELECT name, title, enabled_groups FROM plugins WHERE name='educational_content_organizer'");
    if (count($plugin_check) > 0)
        {
        echo "<p>✅ Plugin found in database</p>";
        echo "<p>Title: " . $plugin_check[0]['title'] . "</p>";
        echo "<p>Enabled for: " . ($plugin_check[0]['enabled_groups'] ?: 'No groups') . "</p>";
        }
    else
        {
        echo "<p>❌ Plugin not found in database</p>";
        }
} catch (Exception $e) {
    echo "<p>❌ Error connecting to database: " . $e->getMessage() . "</p>";
}
?>