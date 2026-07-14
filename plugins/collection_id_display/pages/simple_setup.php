<?php
include '../../../include/boot.php';
include '../../../include/authenticate.php';

if (!checkperm('a'))
    {
    exit('Permission denied');
    }

$page_heading = "Collection ID Display - Simple Setup";

// Handle form submission
$message = '';
$error = '';

if (getval('action', '') == 'update_names' && enforcePostRequest(false))
    {
    // Update collection names to show IDs
    $collections = ps_query("SELECT ref, name FROM collection WHERE name NOT LIKE '[%]%' ORDER BY ref");
    $updated = 0;
    
    foreach ($collections as $collection)
        {
        $new_name = "[{$collection['ref']}] {$collection['name']}";
        $result = ps_query("UPDATE collection SET name=? WHERE ref=?", 
                          array("s", $new_name, "i", $collection['ref']));
        if ($result) 
            {
            $updated++;
            }
        }
    
    if ($updated > 0)
        {
        $message = "✅ Successfully updated {$updated} collection names with IDs!";
        }
    else
        {
        $message = "ℹ️ All collections already have IDs in their names.";
        }
    }

if (getval('action', '') == 'remove_ids' && enforcePostRequest(false))
    {
    // Remove IDs from collection names
    $collections = ps_query("SELECT ref, name FROM collection WHERE name LIKE '[%]%' ORDER BY ref");
    $updated = 0;
    
    foreach ($collections as $collection)
        {
        // Remove the ID part from the name
        $new_name = preg_replace('/^\[\d+\]\s*/', '', $collection['name']);
        $result = ps_query("UPDATE collection SET name=? WHERE ref=?", 
                          array("s", $new_name, "i", $collection['ref']));
        if ($result) 
            {
            $updated++;
            }
        }
    
    if ($updated > 0)
        {
        $message = "✅ Successfully removed IDs from {$updated} collection names!";
        }
    else
        {
        $message = "ℹ️ No collections had IDs to remove.";
        }
    }

// Get current statistics
$total_collections = ps_value("SELECT COUNT(*) FROM collection", array(), 0);
$collections_with_ids = ps_value("SELECT COUNT(*) FROM collection WHERE name LIKE '[%]%'", array(), 0);
$collections_without_ids = $total_collections - $collections_with_ids;

include '../../../include/header.php';
?>

<style>
body { background: #f5f5f5; font-family: Arial, sans-serif; }
.container { max-width: 900px; margin: 20px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
h1, h2, h3 { color: #2c5aa0; }
.success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #c3e6cb; font-weight: bold; }
.error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #f5c6cb; font-weight: bold; }
.info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #bee5eb; }
.form-section { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #007cba; }
.button { background: #007cba; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; margin: 10px 5px; text-decoration: none; display: inline-block; font-weight: bold; }
.button:hover { background: #005a87; }
.button.success { background: #28a745; }
.button.danger { background: #dc3545; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
.stat-card { background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; border: 1px solid #dee2e6; }
.stat-number { font-size: 2em; font-weight: bold; color: #007cba; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
th { background: #f8f9fa; font-weight: bold; }
</style>

<div class="container">
    <h1>🔢 Collection ID Display - Simple Setup</h1>
    <p>This tool will add collection IDs to all collection names so they show as "[ID] Name".</p>
    
    <?php if ($message): ?>
        <div class="success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="form-section">
        <h2>📊 Current Status</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_collections; ?></div>
                <div>Total Collections</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #28a745;"><?php echo $collections_with_ids; ?></div>
                <div>With IDs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #dc3545;"><?php echo $collections_without_ids; ?></div>
                <div>Without IDs</div>
            </div>
        </div>
    </div>
    
    <div class="form-section">
        <h2>🔧 Actions</h2>
        
        <form method="post" style="display: inline-block;">
            <?php generateFormToken("update_collection_names"); ?>
            <input type="hidden" name="action" value="update_names">
            <button type="submit" class="button success" 
                    onclick="return confirm('Add IDs to all collection names that don\'t have them?')"
                    <?php echo $collections_without_ids == 0 ? 'disabled' : ''; ?>>
                ➕ Add IDs to Collection Names (<?php echo $collections_without_ids; ?> collections)
            </button>
        </form>
        
        <form method="post" style="display: inline-block;">
            <?php generateFormToken("remove_collection_ids"); ?>
            <input type="hidden" name="action" value="remove_ids">
            <button type="submit" class="button danger" 
                    onclick="return confirm('Remove IDs from all collection names? This cannot be undone!')"
                    <?php echo $collections_with_ids == 0 ? 'disabled' : ''; ?>>
                ➖ Remove IDs from Collection Names (<?php echo $collections_with_ids; ?> collections)
            </button>
        </form>
    </div>
    
    <?php if ($collections_without_ids > 0): ?>
    <div class="form-section">
        <h3>📋 Preview: Collections That Will Be Updated</h3>
        <table>
            <tr>
                <th>Collection ID</th>
                <th>Current Name</th>
                <th>Will Become</th>
            </tr>
            <?php
            $preview_collections = ps_query("SELECT ref, name FROM collection WHERE name NOT LIKE '[%]%' ORDER BY ref LIMIT 10");
            foreach ($preview_collections as $collection):
            ?>
                <tr>
                    <td><strong><?php echo $collection['ref']; ?></strong></td>
                    <td><?php echo escape($collection['name']); ?></td>
                    <td style="color: #28a745; font-weight: bold;">[<?php echo $collection['ref']; ?>] <?php echo escape($collection['name']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($collections_without_ids > 10): ?>
                <tr>
                    <td colspan="3" style="text-align: center; font-style: italic;">
                        ... and <?php echo $collections_without_ids - 10; ?> more collections
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
    <?php endif; ?>
    
    <div class="form-section">
        <h2>ℹ️ How This Works</h2>
        <p><strong>What happens when you click "Add IDs":</strong></p>
        <ul>
            <li>✅ "MATH" becomes "[1000] MATH"</li>
            <li>✅ "Grade 1" becomes "[1001] Grade 1"</li>
            <li>✅ "Science" becomes "[2001] Science"</li>
            <li>✅ All collections will show their ID numbers</li>
        </ul>
        
        <p><strong>Benefits:</strong></p>
        <ul>
            <li>🔍 Easy to search by ID number</li>
            <li>📊 Better organization and sorting</li>
            <li>🎯 Quick identification of collections</li>
            <li>📋 Consistent naming across the system</li>
        </ul>
    </div>
    
    <div class="form-section">
        <h2>🔗 Quick Links</h2>
        <a href="<?php echo $baseurl_short; ?>pages/collections.php" class="button">📚 View Collections</a>
        <a href="<?php echo $baseurl_short; ?>pages/collection_edit.php" class="button">➕ Create New Collection</a>
        <a href="<?php echo $baseurl_short; ?>plugins/collection_template_manager/pages/test_functionality.php" class="button">🧪 Test Template System</a>
    </div>
</div>

<?php include '../../../include/footer.php'; ?>