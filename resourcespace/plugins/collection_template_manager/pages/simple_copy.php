<?php
include '../../../include/boot.php';
include '../../../include/authenticate.php';

if (!checkperm('c'))
    {
    exit('Permission denied');
    }

$page_heading = "Simple Template Copy";

// Handle form submission
$message = '';
$error = '';

if (getval('action', '') == 'copy_template' && enforcePostRequest(false))
    {
    $target_subject = getval('target_subject', '');
    $start_id = getval('start_id', 0, true);
    
    if (empty($target_subject) || $start_id == 0)
        {
        $error = "Please provide target subject name and starting ID";
        }
    else
        {
        // Get MATH collections (1000-1099 range)
        $math_collections = ps_query("SELECT * FROM collection WHERE ref BETWEEN 1000 AND 1099 ORDER BY ref");
        
        if (empty($math_collections))
            {
            $error = "MATH template not found. Please create MATH collections first.";
            }
        else
            {
            $created = 0;
            $skipped = 0;
            $created_list = array();
            
            foreach ($math_collections as $math_col)
                {
                // Calculate new ID (preserve the offset from 1000)
                $offset = $math_col['ref'] - 1000;
                $new_id = $start_id + $offset;
                
                // Check if collection already exists
                $exists = ps_value("SELECT COUNT(*) FROM collection WHERE ref=?", array("i", $new_id), 0);
                if ($exists > 0) 
                    {
                    $skipped++;
                    continue;
                    }
                
                // Create new collection name
                $old_name = $math_col['name'];
                // Remove existing ID brackets if present
                $clean_name = preg_replace('/^\[\d+\]\s*/', '', $old_name);
                // Replace MATH with target subject
                $new_name = str_replace('MATH', $target_subject, $clean_name);
                // Add new ID
                $new_name = "[{$new_id}] {$new_name}";
                
                // Create new collection
                $result = ps_query(
                    "INSERT INTO collection (ref, name, user, created, public, allow_changes, keywords) VALUES (?, ?, ?, NOW(), ?, ?, ?)",
                    array(
                        "i", $new_id, 
                        "s", $new_name, 
                        "i", $userref, 
                        "i", $math_col['public'], 
                        "i", $math_col['allow_changes'], 
                        "s", $math_col['keywords']
                    )
                );
                
                if ($result) 
                    {
                    $created++;
                    $created_list[] = array('id' => $new_id, 'name' => $new_name, 'original' => $math_col['ref']);
                    }
                }
            
            if ($created > 0)
                {
                $message = "✅ Successfully created {$created} new {$target_subject} collections!";
                if ($skipped > 0) $message .= " (Skipped {$skipped} existing collections)";
                }
            else
                {
                $error = "❌ No collections were created. They may already exist.";
                }
            }
        }
    }

include '../../../include/header.php';
?>

<style>
body { background: #f5f5f5; font-family: Arial, sans-serif; }
.container { max-width: 900px; margin: 20px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
h1, h2, h3 { color: #2c5aa0; }
.success { background: #d4edda; color: #155724; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #c3e6cb; font-size: 16px; font-weight: bold; }
.error { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #f5c6cb; font-size: 16px; font-weight: bold; }
.form-section { background: #f8f9fa; padding: 25px; margin: 25px 0; border-radius: 8px; border-left: 5px solid #007cba; }
.button { background: #007cba; color: white; padding: 15px 30px; border: none; border-radius: 6px; cursor: pointer; margin: 10px; text-decoration: none; display: inline-block; font-size: 16px; font-weight: bold; }
.button:hover { background: #005a87; }
.button.success { background: #28a745; }
input, select { padding: 12px; border: 1px solid #ddd; border-radius: 5px; margin: 8px; font-size: 16px; min-width: 250px; }
label { font-weight: bold; color: #333; display: block; margin: 15px 0 8px 0; font-size: 16px; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; }
th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
th { background: #f8f9fa; font-weight: bold; }
.series-math { color: #4CAF50; font-weight: bold; }
.series-science { color: #2196F3; font-weight: bold; }
.series-english { color: #FF9800; font-weight: bold; }
.series-social { color: #9C27B0; font-weight: bold; }
</style>

<div class="container">
    <h1>📋 Simple Template Copy</h1>
    <p style="font-size: 18px; color: #666;">Copy MATH template structure to create new subject collections</p>
    
    <?php if ($message): ?>
        <div class="success"><?php echo $message; ?></div>
        
        <?php if (isset($created_list) && !empty($created_list)): ?>
            <div class="form-section">
                <h3>📊 Collections Created</h3>
                <table>
                    <tr>
                        <th>Original ID</th>
                        <th>New ID</th>
                        <th>Collection Name</th>
                    </tr>
                    <?php foreach ($created_list as $item): ?>
                        <tr>
                            <td class="series-math"><?php echo $item['original']; ?></td>
                            <td class="series-science"><strong><?php echo $item['id']; ?></strong></td>
                            <td><?php echo escape($item['name']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="form-section">
        <h2>🚀 Copy MATH Template</h2>
        
        <form method="post">
            <?php generateFormToken("copy_template"); ?>
            <input type="hidden" name="action" value="copy_template">
            
            <label>Target Subject Name:</label>
            <input type="text" name="target_subject" placeholder="e.g., SCIENCE, ENGLISH, HISTORY" required>
            
            <label>Starting ID Series:</label>
            <select name="start_id" required>
                <option value="">Choose series...</option>
                <option value="2000">2000 - SCIENCE Series (2000-2999)</option>
                <option value="3000">3000 - ENGLISH Series (3000-3999)</option>
                <option value="4000">4000 - SOCIAL STUDIES Series (4000-4999)</option>
                <option value="5000">5000 - GENERAL Series (5000-5999)</option>
                <option value="6000">6000 - CUSTOM Series (6000-6999)</option>
            </select>
            
            <br><br>
            <button type="submit" class="button success" onclick="return confirm('Copy MATH template to create new subject collections?')">
                📋 Copy MATH Template
            </button>
        </form>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;">
            <h4 style="color: #856404;">📊 What will be copied:</h4>
            <ul style="color: #856404;">
                <li>Complete MATH structure (all grades and states)</li>
                <li>Sequential IDs in target series</li>
                <li>Updated names for new subject</li>
                <li>Same permissions and settings</li>
            </ul>
        </div>
    </div>
    
    <div class="form-section">
        <h2>📊 Current Status</h2>
        <table>
            <tr>
                <th>Series</th>
                <th>ID Range</th>
                <th>Collections</th>
                <th>Status</th>
            </tr>
            <?php
            $series = array(
                'MATH' => array('start' => 1000, 'end' => 1999, 'class' => 'series-math'),
                'SCIENCE' => array('start' => 2000, 'end' => 2999, 'class' => 'series-science'),
                'ENGLISH' => array('start' => 3000, 'end' => 3999, 'class' => 'series-english'),
                'SOCIAL STUDIES' => array('start' => 4000, 'end' => 4999, 'class' => 'series-social'),
            );
            
            foreach ($series as $name => $info):
                $count = ps_value("SELECT COUNT(*) FROM collection WHERE ref BETWEEN ? AND ?", 
                                 array("i", $info['start'], "i", $info['end']), 0);
                
                $status = $count > 0 ? '✅ Ready' : '⚪ Empty';
                if ($name == 'MATH' && $count == 0) $status = '❌ Template Missing';
            ?>
                <tr>
                    <td class="<?php echo $info['class']; ?>"><?php echo $name; ?></td>
                    <td><?php echo $info['start'] . '-' . $info['end']; ?></td>
                    <td><strong><?php echo $count; ?></strong></td>
                    <td><?php echo $status; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="form-section">
        <h2>🔗 Quick Links</h2>
        <a href="<?php echo $baseurl_short; ?>pages/collections.php" class="button">📚 View Collections</a>
        <a href="<?php echo $baseurl_short; ?>pages/collection_edit.php" class="button success">➕ Create Collection</a>
        <a href="<?php echo $baseurl_short; ?>pages/template_manager.php" class="button">🔧 Full Template Manager</a>
    </div>
</div>

<?php include '../../../include/footer.php'; ?>