<?php
#
# Collection Template Manager setup page
#

include '../../../include/boot.php';
include '../../../include/authenticate.php';

if (!checkperm('a'))
    {
    exit($lang['error-permissiondenied']);
    }

$plugin_name = 'collection_template_manager';
if (!in_array($plugin_name, $plugins))
    {
    plugin_activate_for_setup($plugin_name);
    }

$plugin_page_heading = "Collection Template Manager Configuration";

// Add CSS to fix white text visibility issues
echo '<style>
/* Fix white text visibility issues */
.BasicsBox, .BasicsBox * {
    color: #333 !important;
}

.BasicsBox h1, .BasicsBox h2, .BasicsBox h3, .BasicsBox h4 {
    color: #2c5aa0 !important;
}

.BasicsBox p, .BasicsBox li, .BasicsBox td, .BasicsBox th {
    color: #333 !important;
}

.BasicsBox strong {
    color: #2c5aa0 !important;
}

.Question {
    background: #f8f9fa !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 5px;
    padding: 15px;
    margin: 15px 0;
}

.Question h3, .Question h4 {
    color: #2c5aa0 !important;
    margin-top: 0;
}

.Question p, .Question li {
    color: #333 !important;
}

.FormHelp {
    color: #666 !important;
}

/* Table styling */
table {
    background: white !important;
    border: 1px solid #ddd !important;
}

table th {
    background: #f8f9fa !important;
    color: #333 !important;
    font-weight: bold;
}

table td {
    color: #333 !important;
}

/* Button styling */
.button, input[type="submit"] {
    background: #007cba !important;
    color: white !important;
    border: none;
    padding: 10px 20px;
    border-radius: 3px;
    cursor: pointer;
    text-decoration: none;
}

.button:hover, input[type="submit"]:hover {
    background: #005a87 !important;
}
</style>';

// Build the page definition array
$page_def = array();

// Add intro text
$intro_html = '
<div class="Question">
    <h3>Collection Template System</h3>
    <p>This plugin allows users to copy complete collection structures from templates, ensuring consistent organization across subjects.</p>
    <p><strong>Key Features:</strong></p>
    <ul>
        <li>Copy entire MATH structure to create SCIENCE, ENGLISH, or SOCIAL STUDIES</li>
        <li>Automatic sequential ID assignment in target series</li>
        <li>Preserve hierarchical organization</li>
        <li>Update collection names for new subject</li>
        <li>Maintain consistent folder structure across all subjects</li>
    </ul>
</div>';

$page_def[] = config_add_html($intro_html);

// Add require template usage toggle
$page_def[] = config_add_boolean_select('require_template_usage', 'Require Template Usage Before Individual Collection Creation');

// Add template information
$template_info_html = '
<div class="Question">
    <h3>Available Templates</h3>
    <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 10px 0; border: 1px solid #ddd;">
        <h4 style="color: #2c5aa0;">MATH Template</h4>
        <p><strong>Source:</strong> MATH collection structure (IDs 1000-1082)</p>
        <p><strong>Includes:</strong></p>
        <ul>
            <li>Main subject collection (MATH → Target Subject)</li>
            <li>Grade 1-8 collections</li>
            <li>State 1 and State 2 variations for each grade</li>
            <li>Complete hierarchical structure</li>
        </ul>
        
        <h4 style="color: #2c5aa0;">Copy Targets</h4>
        <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
            <tr style="background: #e9e9e9;">
                <th style="padding: 10px; border: 1px solid #ddd;">Target Subject</th>
                <th style="padding: 10px; border: 1px solid #ddd;">ID Series</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Collections Created</th>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>SCIENCE</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">2000-2999</td>
                <td style="padding: 10px; border: 1px solid #ddd;">~25 collections</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>ENGLISH</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">3000-3999</td>
                <td style="padding: 10px; border: 1px solid #ddd;">~25 collections</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>SOCIAL STUDIES</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">4000-4999</td>
                <td style="padding: 10px; border: 1px solid #ddd;">~25 collections</td>
            </tr>
        </table>
    </div>
</div>';

$page_def[] = config_add_html($template_info_html);

// Add usage instructions
$usage_html = '
<div class="Question">
    <h3>How to Use Templates</h3>
    <div style="background: #e8f5e8; padding: 20px; border-radius: 5px; border-left: 4px solid #4CAF50; margin: 10px 0;">
        <h4 style="color: #2c5aa0; margin-top: 0;">For Users:</h4>
        <ol>
            <li>Go to <strong>Collections → Copy Template</strong></li>
            <li>Select "MATH Template"</li>
            <li>Choose target subject (SCIENCE, ENGLISH, or SOCIAL STUDIES)</li>
            <li>Click "Copy Template"</li>
            <li>System creates complete structure with sequential IDs</li>
        </ol>
        
        <h4 style="color: #2c5aa0;">Example Result:</h4>
        <p>Copying MATH template to SCIENCE creates:</p>
        <ul>
            <li>SCIENCE (2001) - Main collection</li>
            <li>Grade 1 (2002), Grade 2 (2003), ... Grade 8 (2009)</li>
            <li>Grade 1 - State 1 (2011), Grade 1 - State 2 (2012)</li>
            <li>Grade 2 - State 1 (2021), Grade 2 - State 2 (2022)</li>
            <li>... and so on</li>
        </ul>
    </div>
</div>';

$page_def[] = config_add_html($usage_html);

// Add quick action button
$action_html = '
<div class="Question">
    <h3>Quick Actions</h3>
    <div style="padding: 15px; background: #fff3cd; border-radius: 5px; border-left: 4px solid #ffc107;">
        <p><strong>Ready to copy templates?</strong></p>
        <a href="' . $baseurl_short . 'plugins/collection_template_manager/pages/copy_template.php" 
           class="button" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;">
           📋 Copy Collection Template
        </a>
    </div>
</div>';

$page_def[] = config_add_html($action_html);

// Do the page generation ritual
config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, null, $plugin_page_heading);

include '../../../include/footer.php';
?>