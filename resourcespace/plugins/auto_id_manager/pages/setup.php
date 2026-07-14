<?php
#
# Auto ID Manager setup page
#

include '../../../include/boot.php';
include '../../../include/authenticate.php';

if (!checkperm('a'))
    {
    exit($lang['error-permissiondenied']);
    }

$plugin_name = 'auto_id_manager';
if (!in_array($plugin_name, $plugins))
    {
    plugin_activate_for_setup($plugin_name);
    }

$plugin_page_heading = "Auto ID Manager Configuration";

// Build the page definition array
$page_def = array();

// Add intro text
$intro_html = '
<div class="Question">
    <h3>Automatic ID Assignment System</h3>
    <p>This plugin automatically assigns sequential, predictable IDs to new collections instead of random numbers.</p>
    <p><strong>Benefits:</strong></p>
    <ul>
        <li>Predictable ID sequences for better record keeping</li>
        <li>Series-based organization (Math: 1000-1999, Science: 2000-2999, etc.)</li>
        <li>No more random collection IDs</li>
        <li>Easy identification by ID number</li>
    </ul>
</div>';

$page_def[] = config_add_html($intro_html);

// Add enable/disable toggle
$page_def[] = config_add_boolean_select('auto_id_enabled', 'Enable Automatic ID Assignment');

// Add show ID in name toggle
$page_def[] = config_add_boolean_select('auto_id_show_in_name', 'Show ID in Collection Name (e.g., "MATH (1000)")');

// Add series information
$series_html = '
<div class="Question">
    <h3>ID Series Configuration</h3>
    <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 10px 0; border: 1px solid #ddd;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="background: #e9e9e9;">
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Subject/Category</th>
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">ID Range</th>
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Current Position</th>
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Next Available</th>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>MATH</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">1000 - 1999</td>
                <td style="padding: 10px; border: 1px solid #ddd;">1082</td>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>1083</strong></td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>SCIENCE</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">2000 - 2999</td>
                <td style="padding: 10px; border: 1px solid #ddd;">2000</td>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>2001</strong></td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>ENGLISH</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">3000 - 3999</td>
                <td style="padding: 10px; border: 1px solid #ddd;">3000</td>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>3001</strong></td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>SOCIAL STUDIES</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">4000 - 4999</td>
                <td style="padding: 10px; border: 1px solid #ddd;">4000</td>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>4001</strong></td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>GENERAL</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">5000 - 5999</td>
                <td style="padding: 10px; border: 1px solid #ddd;">5000</td>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>5001</strong></td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>ADMIN</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">9000 - 9999</td>
                <td style="padding: 10px; border: 1px solid #ddd;">9000</td>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>9001</strong></td>
            </tr>
        </table>
    </div>
</div>';

$page_def[] = config_add_html($series_html);

// Add keyword rules
$rules_html = '
<div class="Question">
    <h3>Automatic Series Detection</h3>
    <div style="background: #e8f5e8; padding: 15px; border-radius: 5px; border-left: 4px solid #4CAF50; margin: 10px 0;">
        <p><strong>The system automatically detects which series to use based on collection name keywords:</strong></p>
        <ul>
            <li><strong>MATH Series (1000-1999):</strong> math, mathematics, grade</li>
            <li><strong>SCIENCE Series (2000-2999):</strong> science, physics, chemistry, biology</li>
            <li><strong>ENGLISH Series (3000-3999):</strong> english, language, literature</li>
            <li><strong>SOCIAL STUDIES Series (4000-4999):</strong> social, history, geography</li>
            <li><strong>GENERAL Series (5000-5999):</strong> everything else</li>
        </ul>
    </div>
</div>';

$page_def[] = config_add_html($rules_html);

// Add example
$example_html = '
<div class="Question">
    <h3>Examples</h3>
    <div style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 10px 0;">
        <p><strong>When you create collections:</strong></p>
        <ul>
            <li>"Grade 9 Math" → Gets ID <strong>1083</strong> (MATH series)</li>
            <li>"Science Lab" → Gets ID <strong>2001</strong> (SCIENCE series)</li>
            <li>"English Literature" → Gets ID <strong>3001</strong> (ENGLISH series)</li>
            <li>"History Documents" → Gets ID <strong>4001</strong> (SOCIAL STUDIES series)</li>
            <li>"General Files" → Gets ID <strong>5001</strong> (GENERAL series)</li>
        </ul>
        <p><strong>No more random IDs!</strong> Everything follows a predictable sequence.</p>
    </div>
</div>';

$page_def[] = config_add_html($example_html);

// Do the page generation ritual
config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, null, $plugin_page_heading);

include '../../../include/footer.php';
?>