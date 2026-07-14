<?php
include '../../../include/boot.php';
include '../../../include/authenticate.php';

if (!checkperm('c'))
    {
    exit($lang['error-permissiondenied']);
    }

$page_heading = "Test Template System Functionality";

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

.info-box {
    background: #f8f9fa !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 5px;
    padding: 20px;
    margin: 20px 0;
}

.info-box h3, .info-box h4 {
    color: #2c5aa0 !important;
    margin-top: 0;
}

.info-box p, .info-box li {
    color: #333 !important;
}

.success-box {
    background: #d4edda !important;
    border: 1px solid #c3e6cb !important;
    color: #155724 !important;
}

.warning-box {
    background: #fff3cd !important;
    border: 1px solid #ffeaa7 !important;
    color: #856404 !important;
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

include '../../../include/header.php';
?>

<div class="BasicsBox">
    <h1><?php echo escape($page_heading); ?></h1>
    
    <div class="info-box">
        <h3>🧪 System Status Check</h3>
        <p>This page tests the functionality of both the Collection Template Manager and Collection ID Display plugins.</p>
    </div>
    
    <!-- Plugin Status -->
    <div class="info-box">
        <h3>📦 Plugin Status</h3>
        <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
            <tr>
                <th style="padding: 10px; border: 1px solid #ddd;">Plugin</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Configuration</th>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>Collection Template Manager</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">
                    <?php echo in_array('collection_template_manager', $plugins) ? '✅ Active' : '❌ Inactive'; ?>
                </td>
                <td style="padding: 10px; border: 1px solid #ddd;">
                    <a href="<?php echo $baseurl_short; ?>plugins/collection_template_manager/pages/setup.php" class="button">Configure</a>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd;"><strong>Collection ID Display</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd;">
                    <?php echo in_array('collection_id_display', $plugins) ? '✅ Active' : '❌ Inactive'; ?>
                </td>
                <td style="padding: 10px; border: 1px solid #ddd;">
                    <a href="<?php echo $baseurl_short; ?>plugins/collection_id_display/pages/setup.php" class="button">Configure</a>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Collection Series Status -->
    <div class="info-box">
        <h3>📊 Collection Series Status</h3>
        <table style="width: 100%; border-collapse: collapse; margin: 10px 0;">
            <tr>
                <th style="padding: 10px; border: 1px solid #ddd;">Series</th>
                <th style="padding: 10px; border: 1px solid #ddd;">ID Range</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Collections</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
            </tr>
            <?php
            $series_info = array(
                'MATH' => array('range' => '1000-1999', 'start' => 1000, 'end' => 1999, 'color' => '#4CAF50'),
                'SCIENCE' => array('range' => '2000-2999', 'start' => 2000, 'end' => 2999, 'color' => '#2196F3'),
                'ENGLISH' => array('range' => '3000-3999', 'start' => 3000, 'end' => 3999, 'color' => '#FF9800'),
                'SOCIAL STUDIES' => array('range' => '4000-4999', 'start' => 4000, 'end' => 4999, 'color' => '#9C27B0'),
                'GENERAL' => array('range' => '5000-5999', 'start' => 5000, 'end' => 5999, 'color' => '#607D8B'),
                'TEMPLATES' => array('range' => '7000-7999', 'start' => 7000, 'end' => 7999, 'color' => '#F44336'),
            );
            
            foreach ($series_info as $series_name => $info):
                $count = ps_value("SELECT COUNT(*) FROM collection WHERE ref BETWEEN ? AND ?", 
                                 array("i", $info['start'], "i", $info['end']), 0);
                $status = $count > 0 ? "✅ Ready" : "⚪ Empty";
                
                // Get sample collection names
                $samples = ps_query("SELECT ref, name FROM collection WHERE ref BETWEEN ? AND ? ORDER BY ref LIMIT 3", 
                                   array("i", $info['start'], "i", $info['end']));
            ?>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        <strong style="color: <?php echo $info['color']; ?>;"><?php echo $series_name; ?></strong>
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $info['range']; ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $count; ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $status; ?></td>
                </tr>
                <?php if (!empty($samples)): ?>
                    <tr>
                        <td colspan="4" style="padding: 5px 10px; border: 1px solid #ddd; background: #f8f9fa; font-size: 0.9em;">
                            <strong>Sample collections:</strong>
                            <?php foreach ($samples as $sample): ?>
                                <span style="margin-right: 15px; color: <?php echo $info['color']; ?>;">
                                    <?php echo $sample['ref']; ?>: <?php echo escape($sample['name']); ?>
                                </span>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </table>
    </div>
    
    <!-- Template Functionality Test -->
    <div class="info-box">
        <h3>🔧 Template System Functions</h3>
        <p><strong>Available Actions:</strong></p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
            <div style="padding: 15px; background: #e3f2fd; border-radius: 5px;">
                <h4 style="margin-top: 0; color: #1976D2;">📋 Copy Templates</h4>
                <p>Copy the MATH template structure to create SCIENCE, ENGLISH, or SOCIAL STUDIES collections.</p>
                <a href="<?php echo $baseurl_short; ?>plugins/collection_template_manager/pages/copy_template.php" class="button">
                    Copy Template
                </a>
            </div>
            
            <div style="padding: 15px; background: #f3e5f5; border-radius: 5px;">
                <h4 style="margin-top: 0; color: #7B1FA2;">🏗️ Manage Templates</h4>
                <p>Create custom templates in the 7000 series for specialized structures.</p>
                <a href="<?php echo $baseurl_short; ?>plugins/collection_template_manager/pages/manage_templates.php" class="button">
                    Manage Templates
                </a>
            </div>
            
            <div style="padding: 15px; background: #e8f5e8; border-radius: 5px;">
                <h4 style="margin-top: 0; color: #388E3C;">🔍 Update Collection Names</h4>
                <p>Add IDs to all collection names for better identification and search.</p>
                <a href="<?php echo $baseurl_short; ?>plugins/collection_id_display/pages/update_collection_names.php" class="button">
                    Update Names
                </a>
            </div>
            
            <div style="padding: 15px; background: #fff3e0; border-radius: 5px;">
                <h4 style="margin-top: 0; color: #F57C00;">📚 View Collections</h4>
                <p>See all collections with their IDs and color-coded series.</p>
                <a href="<?php echo $baseurl_short; ?>pages/collections.php" class="button">
                    View Collections
                </a>
            </div>
        </div>
    </div>
    
    <!-- Quick Setup Guide -->
    <div class="success-box">
        <h3>🚀 Quick Setup Guide</h3>
        <p><strong>To get started with the template system:</strong></p>
        <ol>
            <li><strong>Activate Plugins:</strong> Ensure both plugins are active (check status above)</li>
            <li><strong>Update Collection Names:</strong> Click "Update Names" to add IDs to existing collections</li>
            <li><strong>Copy Templates:</strong> Use "Copy Template" to create SCIENCE from MATH structure</li>
            <li><strong>View Results:</strong> Check "View Collections" to see the organized structure</li>
            <li><strong>Search by ID:</strong> Try searching for "1000" to find MATH collection</li>
        </ol>
    </div>
    
    <!-- System Requirements Check -->
    <div class="warning-box">
        <h3>⚠️ System Requirements</h3>
        <p><strong>For optimal functionality, ensure:</strong></p>
        <ul>
            <li>✅ MATH template exists (collections 1000-1082)</li>
            <li>✅ Database permissions for collection creation</li>
            <li>✅ User has collection management permissions</li>
            <li>✅ Plugins are properly activated</li>
        </ul>
        
        <?php
        $math_count = ps_value("SELECT COUNT(*) FROM collection WHERE ref BETWEEN 1000 AND 1099", array(), 0);
        if ($math_count == 0):
        ?>
            <p style="color: #dc3545;"><strong>⚠️ Warning:</strong> MATH template not found. Please set up the Educational Content Organizer first.</p>
        <?php else: ?>
            <p style="color: #28a745;"><strong>✅ Ready:</strong> MATH template found with <?php echo $math_count; ?> collections.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../../../include/footer.php'; ?>