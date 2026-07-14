<?php
include '../../../include/boot.php';
include '../../../include/authenticate.php';

if (!checkperm('a'))
    {
    exit($lang['error-permissiondenied']);
    }

$plugin_name = 'simple_collection_manager';
if (!in_array($plugin_name, $plugins))
    {
    plugin_activate_for_setup($plugin_name);
    }

$plugin_page_heading = "Simple Collection Manager Setup";

// Build the page definition array
$page_def = array();

// Add intro text
$intro_html = '
<div class="Question" style="background: #f8f9fa; padding: 20px; border-radius: 5px; color: #333;">
    <h3 style="color: #2c5aa0;">Simple Collection Template Manager</h3>
    <p style="color: #333;">This plugin provides a simple way to:</p>
    <ul style="color: #333;">
        <li>Show collection IDs in names (e.g., "[1000] MATH")</li>
        <li>Copy MATH template to create SCIENCE, ENGLISH, etc.</li>
        <li>Manage collection templates easily</li>
        <li>Color-code collections by subject series</li>
    </ul>
    <p style="color: #333;"><strong>Access the manager:</strong> 
    <a href="' . $baseurl_short . 'plugins/simple_collection_manager/pages/manage.php" 
       style="background: #007cba; color: white; padding: 8px 16px; text-decoration: none; border-radius: 3px;">
       📋 Open Template Manager
    </a></p>
</div>';

$page_def[] = config_add_html($intro_html);

// Add configuration options
$page_def[] = config_add_boolean_select('simple_collection_manager_enabled', 'Enable Simple Collection Manager');
$page_def[] = config_add_boolean_select('show_collection_ids', 'Show Collection IDs in Names');
$page_def[] = config_add_boolean_select('enable_template_copying', 'Enable Template Copying');

// Add status information
$status_html = '
<div class="Question" style="background: #e8f5e8; padding: 20px; border-radius: 5px; color: #333;">
    <h3 style="color: #2c5aa0;">Current Status</h3>
    <table style="width: 100%; border-collapse: collapse; color: #333;">
        <tr style="background: #f8f9fa;">
            <th style="padding: 10px; border: 1px solid #ddd; color: #333;">Series</th>
            <th style="padding: 10px; border: 1px solid #ddd; color: #333;">ID Range</th>
            <th style="padding: 10px; border: 1px solid #ddd; color: #333;">Collections</th>
        </tr>';

$series_info = array(
    'MATH' => array('start' => 1000, 'end' => 1999),
    'SCIENCE' => array('start' => 2000, 'end' => 2999),
    'ENGLISH' => array('start' => 3000, 'end' => 3999),
    'SOCIAL STUDIES' => array('start' => 4000, 'end' => 4999),
);

foreach ($series_info as $series_name => $info)
    {
    $count = ps_value("SELECT COUNT(*) FROM collection WHERE ref BETWEEN ? AND ?", 
                     array("i", $info['start'], "i", $info['end']), 0);
    
    $status_html .= "
        <tr>
            <td style='padding: 10px; border: 1px solid #ddd; color: #333;'><strong>{$series_name}</strong></td>
            <td style='padding: 10px; border: 1px solid #ddd; color: #333;'>{$info['start']}-{$info['end']}</td>
            <td style='padding: 10px; border: 1px solid #ddd; color: #333;'>{$count} collections</td>
        </tr>";
    }

$status_html .= '
    </table>
</div>';

$page_def[] = config_add_html($status_html);

// Do the page generation ritual
config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';

// Add CSS to fix visibility
echo '<style>
.BasicsBox, .BasicsBox * { color: #333 !important; }
.BasicsBox h1, .BasicsBox h2, .BasicsBox h3 { color: #2c5aa0 !important; }
.Question { background: #f8f9fa !important; border: 1px solid #dee2e6 !important; }
.Question * { color: #333 !important; }
table th { background: #f8f9fa !important; color: #333 !important; }
table td { color: #333 !important; }
</style>';

config_gen_setup_html($page_def, $plugin_name, null, $plugin_page_heading);

include '../../../include/footer.php';
?>