<?php
#
# auto_version_increment setup page
#

include '../../../include/boot.php';
include '../../../include/authenticate.php';

if (!checkperm('a'))
    {
    exit($lang['error-permissiondenied']);
    }

// Specify the name of this plugin and the heading to display for the page.
$plugin_name = 'auto_version_increment';
if (!in_array($plugin_name, $plugins))
    {
    plugin_activate_for_setup($plugin_name);
    }

$plugin_page_heading = $lang["auto_version_increment_configuration"];

// Build the page definition array
$page_def = array();

// Add intro text
$page_def[] = config_add_html('<div class="Question"><p>' . escape($lang["auto_version_increment_intro"]) . '</p><p><strong>' . escape($lang["auto_version_increment_example"]) . '</strong></p></div>');

// Add field selector - allow all field types
$page_def[] = config_add_single_ftype_select('auto_version_field_ref', $lang['auto_version_increment_field_ref'], 300, false, array());

// Add increment type selector
$increment_types = array(
    'minor' => $lang['auto_version_increment_minor'],
    'patch' => $lang['auto_version_increment_patch']
);
$page_def[] = config_add_single_select('auto_version_increment_type', $lang['auto_version_increment_type'], $increment_types);

// Do the page generation ritual -- don't change this section.
config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, null, $plugin_page_heading);

include '../../../include/footer.php';
?>
