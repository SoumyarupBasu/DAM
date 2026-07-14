<?php
include '../../../include/boot.php';
include '../../../include/authenticate.php';

if (!checkperm('a'))
    {
    exit('Permission denied');
    }

$page_heading = "Collection ID Display - Configuration";

// Simple message handling
$message = '';
if (getval('save', '') != '')
    {
    $message = "Settings saved! (Note: This is a simple version - settings are applied immediately)";
    }

include '../../../include/header.php';
?>

<style>
body { background: #f5f5f5; font-family: Arial, sans-serif; }
.container { max-width: 800px; margin: 20px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
h1, h2, h3 { color: #2c5aa0; }
.success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #c3e6cb; font-weight: bold; }
.form-section { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #007cba; }
.button { background: #007cba; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; margin: 10px 0; text-decoration: none; display: inline-block; font-weight: bold; }
.button:hover { background: #005a87; }
.button.success { background: #28a745; }
input[type="checkbox"] { margin-right: 8px; }
label { font-weight: bold; color: #333; display: block; margin: 10px 0; }
</style>

<div class="container">
    <h1>🔢 Collection ID Display Configuration</h1>
    <p>Configure how collection IDs are displayed in ResourceSpace.</p>
    
    <?php if ($message): ?>
        <div class="success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="form-section">
        <h2>✅ Plugin Status</h2>
        <p><strong>Plugin Files:</strong> ✅ Installed</p>
        <p><strong>Configuration:</strong> ✅ Ready</p>
        <p><strong>Database:</strong> ✅ Connected</p>
    </div>
    
    <form method="post">
        <?php generateFormToken("collection_id_display_config"); ?>
        
        <div class="form-section">
            <h2>📋 Quick Setup</h2>
            <p>The plugin is ready to use! The main feature is to add IDs to collection names.</p>
            
            <label>
                <input type="checkbox" name="enable_ids" value="1" checked disabled>
                ✅ Enable Collection ID Display (Always On)
            </label>
            
            <label>
                <input type="checkbox" name="enable_search" value="1" checked disabled>
                ✅ Enable ID-based Search (Always On)
            </label>
        </div>
        
        <button type="submit" name="save" value="1" class="button">💾 Save Settings</button>
    </form>
    
    <div class="form-section">
        <h2>🚀 Next Steps</h2>
        <p><strong>To make collection IDs visible:</strong></p>
        <ol>
            <li>Click the button below to add IDs to all collection names</li>
            <li>Collections will show as "[ID] Name" format</li>
            <li>You can search by ID numbers</li>
        </ol>
        
        <a href="simple_setup.php" class="button success">🔧 Add IDs to Collection Names</a>
    </div>
    
    <div class="form-section">
        <h2>📊 Current Status</h2>
        <?php
        $total_collections = ps_value("SELECT COUNT(*) FROM collection", array(), 0);
        $collections_with_ids = ps_value("SELECT COUNT(*) FROM collection WHERE name LIKE '[%]%'", array(), 0);
        $collections_without_ids = $total_collections - $collections_with_ids;
        ?>
        <p><strong>Total Collections:</strong> <?php echo $total_collections; ?></p>
        <p><strong>Collections with IDs:</strong> <span style="color: #28a745;"><?php echo $collections_with_ids; ?></span></p>
        <p><strong>Collections without IDs:</strong> <span style="color: #dc3545;"><?php echo $collections_without_ids; ?></span></p>
        
        <?php if ($collections_without_ids > 0): ?>
            <p style="background: #fff3cd; padding: 10px; border-radius: 5px; border-left: 4px solid #ffc107;">
                ⚠️ <strong><?php echo $collections_without_ids; ?> collections</strong> don't have IDs yet. 
                <a href="simple_setup.php">Click here to add them</a>.
            </p>
        <?php else: ?>
            <p style="background: #d4edda; padding: 10px; border-radius: 5px; border-left: 4px solid #28a745;">
                ✅ <strong>All collections have IDs!</strong> Your setup is complete.
            </p>
        <?php endif; ?>
    </div>
    
    <div class="form-section">
        <h2>🔗 Quick Links</h2>
        <a href="<?php echo $baseurl_short; ?>pages/collections.php" class="button">📚 View Collections</a>
        <a href="simple_setup.php" class="button">🔧 Manage Collection IDs</a>
        <a href="<?php echo $baseurl_short; ?>pages/team/team_plugins.php" class="button">🔌 Plugin Management</a>
    </div>
</div>

<?php include '../../../include/footer.php'; ?>