<?php
include '../../../include/boot.php';
include '../../../include/authenticate.php';

if (!checkperm('c'))
    {
    exit($lang['error-permissiondenied']);
    }

$page_heading = "Copy Collection Template";

// Handle form submission
if (getval("action", "") == "copy_template" && enforcePostRequest(false))
    {
    $template_name = getval("template_name", "");
    $target_subject = getval("target_subject", "");
    $user_id = $userref;
    
    if (empty($template_name) || empty($target_subject))
        {
        $error = "Please select both a template and target subject.";
        }
    else
        {
        // Include the template functions
        include_once '../hooks/all.php';
        
        $result = copy_collection_template($template_name, $target_subject, $user_id);
        
        if ($result !== false)
            {
            $success_message = "Successfully created {$result['collections_created']} collections for {$target_subject}!";
            $collections_created = $result['collections_created'];
            $id_mapping = $result['id_mapping'];
            }
        else
            {
            $error = "Failed to copy template. Please check the logs for details.";
            }
        }
    }

include '../../../include/header.php';
?>

<div class="BasicsBox">
    <h1><?php echo escape($page_heading); ?></h1>
    
    <?php if (isset($success_message)): ?>
        <div class="PageInfoMessage" style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb;">
            <h3>✅ Template Copied Successfully!</h3>
            <p><?php echo escape($success_message); ?></p>
            
            <?php if (isset($id_mapping) && !empty($id_mapping)): ?>
                <h4>Collections Created:</h4>
                <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 8px; border: 1px solid #ddd;">Original ID</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">New ID</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Collection Name</th>
                    </tr>
                    <?php foreach ($id_mapping as $old_id => $new_id): 
                        $collection_name = ps_value("SELECT name FROM collection WHERE ref=?", array("i", $new_id), "Unknown");
                    ?>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;"><?php echo $old_id; ?></td>
                            <td style="padding: 8px; border: 1px solid #ddd;"><strong><?php echo $new_id; ?></strong></td>
                            <td style="padding: 8px; border: 1px solid #ddd;"><?php echo escape($collection_name); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
            
            <p><a href="<?php echo $baseurl_short; ?>pages/collections.php">View Collections</a></p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="PageInfoMessage" style="background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
            <h3>❌ Error</h3>
            <p><?php echo escape($error); ?></p>
        </div>
    <?php endif; ?>
    
    <div style="background: #e3f2fd; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #2196F3;">
        <h3 style="margin-top: 0; color: #1976D2;">📋 How Template Copying Works</h3>
        <p><strong>When you copy the MATH template:</strong></p>
        <ul>
            <li>✅ <strong>Complete structure copied</strong> - All Grade 1-8 and State collections</li>
            <li>✅ <strong>Sequential IDs assigned</strong> - New collections get proper series IDs</li>
            <li>✅ <strong>Names updated</strong> - "MATH" becomes your chosen subject</li>
            <li>✅ <strong>Hierarchy preserved</strong> - Same organizational structure</li>
            <li>❌ <strong>Resources not copied</strong> - Only the folder structure, not the files</li>
        </ul>
    </div>
    
    <form method="post" action="<?php echo $baseurl_short; ?>plugins/collection_template_manager/pages/copy_template.php">
        <?php generateFormToken("copy_template"); ?>
        <input type="hidden" name="action" value="copy_template">
        
        <div class="Question">
            <label for="template_name">Select Template to Copy:</label>
            <select name="template_name" id="template_name" class="stdwidth" required>
                <option value="">Choose a template...</option>
                <option value="MATH">MATH Template (Complete K-8 structure with states)</option>
            </select>
            <div class="clearerleft"></div>
            <div class="FormHelp">The MATH template includes all Grade 1-8 collections and State 1/State 2 variations</div>
        </div>
        
        <div class="Question">
            <label for="target_subject">Target Subject:</label>
            <select name="target_subject" id="target_subject" class="stdwidth" required>
                <option value="">Choose target subject...</option>
                <option value="SCIENCE">SCIENCE (IDs: 2000-2999)</option>
                <option value="ENGLISH">ENGLISH (IDs: 3000-3999)</option>
                <option value="SOCIAL_STUDIES">SOCIAL STUDIES (IDs: 4000-4999)</option>
            </select>
            <div class="clearerleft"></div>
            <div class="FormHelp">The new collections will be created in this subject's ID series</div>
        </div>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 15px 0;">
            <h4 style="margin-top: 0; color: #856404;">⚠️ Preview of What Will Be Created</h4>
            <p><strong>If you copy MATH template to SCIENCE:</strong></p>
            <ul style="margin: 10px 0;">
                <li>MATH (1000) → <strong>SCIENCE (2001)</strong></li>
                <li>Grade 1 (1001) → <strong>Grade 1 (2002)</strong></li>
                <li>Grade 2 (1002) → <strong>Grade 2 (2003)</strong></li>
                <li>Grade 1 - State 1 (1011) → <strong>Grade 1 - State 1 (2011)</strong></li>
                <li>... and so on for all 25 collections</li>
            </ul>
            <p><strong>Total collections that will be created: ~25</strong></p>
        </div>
        
        <div class="QuestionSubmit">
            <input type="submit" value="Copy Template" onclick="return confirm('This will create approximately 25 new collections. Are you sure you want to proceed?');">
        </div>
    </form>
    
    <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <h3>📊 Current Template Status</h3>
        <?php
        // Show current template structure
        $math_collections = ps_query("SELECT ref, name FROM collection WHERE ref BETWEEN 1000 AND 1099 ORDER BY ref");
        if (!empty($math_collections)):
        ?>
            <p><strong>MATH Template Structure (<?php echo count($math_collections); ?> collections):</strong></p>
            <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                <?php foreach ($math_collections as $collection): ?>
                    <div>ID <?php echo $collection['ref']; ?>: <?php echo escape($collection['name']); ?></div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: #dc3545;">❌ MATH template not found. Please ensure the educational content organizer is set up first.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../../../include/footer.php'; ?>