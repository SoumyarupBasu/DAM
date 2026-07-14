<?php

/**
 * Hook into the view page to add JavaScript that handles version restoration after revert
 */

function HookAuto_version_incrementViewAfterresourceactions()
    {
    global $ref, $baseurl_short, $auto_version_field_ref;
    
    // Only add this if version field is configured
    if (empty($auto_version_field_ref))
        {
        return false;
        }
    
    ?>
    <script>
    // Auto Version Increment: Check if we just reverted and need to restore version
    jQuery(document).ready(function() {
        // Check if there's a revert parameter in the URL (coming from rse_version revert page)
        var urlParams = new URLSearchParams(window.location.search);
        var fromRevert = urlParams.get('from_revert');
        
        if (fromRevert === '1') {
            // Call our handler to restore the version
            jQuery.ajax({
                url: '<?php echo $baseurl_short; ?>plugins/auto_version_increment/pages/check_and_restore_version.php',
                type: 'POST',
                data: {
                    resource: <?php echo (int)$ref; ?>
                },
                success: function(response) {
                    try {
                        var data = JSON.parse(response);
                        if (data.success && data.version_restored) {
                            console.log('Version restored: ' + data.message);
                            // Reload the page to show the updated version
                            window.location.href = '<?php echo $baseurl_short; ?>pages/view.php?ref=<?php echo (int)$ref; ?>';
                        }
                    } catch(e) {
                        console.error('Error parsing response:', e);
                    }
                },
                error: function() {
                    console.error('Failed to restore version');
                }
            });
        }
    });
    </script>
    <?php
    }
