<?php
#
# Educational Content Organizer setup page
#

include '../../../include/boot.php';
include '../../../include/authenticate.php';

if (!checkperm('a'))
    {
    exit($lang['error-permissiondenied']);
    }

// Specify the name of this plugin and the heading to display for the page.
$plugin_name = 'educational_content_organizer';
if (!in_array($plugin_name, $plugins))
    {
    plugin_activate_for_setup($plugin_name);
    }

$plugin_page_heading = $lang["educational_content_organizer_configuration"];

// Build the page definition array
$page_def = array();

// Add intro text
$page_def[] = config_add_html('<div class="Question"><p>' . escape($lang["educational_content_organizer_intro"]) . '</p></div>');

// Add auto-assign toggle
$page_def[] = config_add_boolean_select('edu_auto_assign_collections', $lang['educational_content_organizer_auto_assign']);

// Add structure information with better styling
$structure_html = '
<div class="Question">
    <h3 style="color: #333; margin-bottom: 15px;">Collection Structure</h3>
    <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 10px 0; border: 1px solid #ddd; color: #333;">
        
        <h4 style="color: #2c5aa0; margin-top: 0;">Main Subject Collections</h4>
        <ul style="color: #333; margin-bottom: 20px;">
            <li><strong>MATH</strong> (ID: 1000)</li>
        </ul>
        
        <h4 style="color: #2c5aa0;">Grade Level Collections</h4>
        <ul style="color: #333; margin-bottom: 20px;">
            <li><strong>Grade 1</strong> (ID: 1001)</li>
            <li><strong>Grade 2</strong> (ID: 1002)</li>
            <li><strong>Grade 3</strong> (ID: 1003)</li>
            <li><strong>Grade 4</strong> (ID: 1004)</li>
            <li><strong>Grade 5</strong> (ID: 1005)</li>
            <li><strong>Grade 6</strong> (ID: 1006)</li>
            <li><strong>Grade 7</strong> (ID: 1007)</li>
            <li><strong>Grade 8</strong> (ID: 1008)</li>
        </ul>
        
        <h4 style="color: #2c5aa0;">State-Specific Collections</h4>
        <ul style="color: #333; margin-bottom: 20px;">
            <li><strong>Grade 1 - State 1</strong> (ID: 1011), <strong>Grade 1 - State 2</strong> (ID: 1012)</li>
            <li><strong>Grade 2 - State 1</strong> (ID: 1021), <strong>Grade 2 - State 2</strong> (ID: 1022)</li>
            <li><strong>Grade 3 - State 1</strong> (ID: 1031), <strong>Grade 3 - State 2</strong> (ID: 1032)</li>
            <li><strong>Grade 4 - State 1</strong> (ID: 1041), <strong>Grade 4 - State 2</strong> (ID: 1042)</li>
            <li><strong>Grade 5 - State 1</strong> (ID: 1051), <strong>Grade 5 - State 2</strong> (ID: 1052)</li>
            <li><strong>Grade 6 - State 1</strong> (ID: 1061), <strong>Grade 6 - State 2</strong> (ID: 1062)</li>
            <li><strong>Grade 7 - State 1</strong> (ID: 1071), <strong>Grade 7 - State 2</strong> (ID: 1072)</li>
            <li><strong>Grade 8 - State 1</strong> (ID: 1081), <strong>Grade 8 - State 2</strong> (ID: 1082)</li>
        </ul>
    </div>
    
    <div style="background: #e8f5e8; padding: 20px; border-radius: 5px; border-left: 4px solid #4CAF50; margin: 10px 0; color: #333;">
        <h4 style="color: #2c5aa0; margin-top: 0;">How It Works - Example:</h4>
        <p style="color: #333; margin-bottom: 10px;">When you upload a <strong>Grade 3 Math video for State 1</strong>, the system will automatically add it to these collections:</p>
        <ul style="color: #333;">
            <li><strong>MATH</strong> (ID: 1000)</li>
            <li><strong>Grade 3</strong> (ID: 1003)</li>
            <li><strong>Grade 3 - State 1</strong> (ID: 1031)</li>
        </ul>
    </div>
    
    <div style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 10px 0; color: #333;">
        <h4 style="color: #856404; margin-top: 0;">Metadata Fields Created:</h4>
        <ul style="color: #333;">
            <li><strong>Subject</strong> (Field 92): MATH, SCIENCE, ENGLISH, SOCIAL STUDIES</li>
            <li><strong>Grade Level</strong> (Field 93): Grade 1 through Grade 8</li>
            <li><strong>State Curriculum</strong> (Field 94): State 1, State 2</li>
            <li><strong>Content Type</strong> (Field 95): Video, Worksheet, Lesson Plan, Assessment</li>
            <li><strong>Asset Type</strong> (Field 96): Script, Audio, Video Source, Final Video, etc.</li>
        </ul>
    </div>
</div>';

$page_def[] = config_add_html($structure_html);

// Do the page generation ritual
config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, null, $plugin_page_heading);

include '../../../include/footer.php';
?>