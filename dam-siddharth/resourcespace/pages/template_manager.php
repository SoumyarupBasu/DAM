<?php
include '../include/boot.php';
include '../include/authenticate.php';

if (!checkperm('c'))
    {
    exit('Permission denied');
    }

$page_heading = "Collection Template Manager";

// Handle form submissions
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
        ps_query("UPDATE collection SET name=? WHERE ref=?", array("s", $new_name, "i", $collection['ref']));
        $updated++;
        }
    
    $message = "✅ Updated {$updated} collection names with IDs";
    }

if (getval('action', '') == 'copy_template' && enforcePostRequest(false))
    {
    $target_subject = getval('target_subject', '');
    $start_id = getval('start_id', 0, true);
    
    if (empty($target_subject) || $start_id == 0)
        {
        $error = "❌ Please provide target subject name and starting ID";
        }
    else
        {
        // Get MATH collections (1000-1099 range)
        $math_collections = ps_query("SELECT * FROM collection WHERE ref BETWEEN 1000 AND 1099 ORDER BY ref");
        
        if (empty($math_collections))
            {
            $error = "❌ MATH template not found. Please create MATH collections first.";
            }
        else
            {
            $created = 0;
            $skipped = 0;
            
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
                
                if ($result) $created++;
                }
            
            if ($created > 0)
                {
                $message = "✅ Created {$created} new {$target_subject} collections starting from ID {$start_id}";
                if ($skipped > 0) $message .= " (Skipped {$skipped} existing collections)";
                }
            else
                {
                $error = "❌ No collections were created. They may already exist.";
                }
            }
        }
    }

include '../include/header.php';
?>

<style>
body { background: #f5f5f5; font-family: Arial, sans-serif; }
.BasicsBox { 
    background: white; 
    border-radius: 8px; 
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin: 20px auto;
    max-width: 1000px;
}
.BasicsBox, .BasicsBox * { color: #333 !important; }
.BasicsBox h1, .BasicsBox h2, .BasicsBox h3 { color: #2c5aa0 !important; }
.form-section { 
    background: #f8f9fa; 
    padding: 25px; 
    margin: 25px 0; 
    border-radius: 8px; 
    border-left: 5px solid #007cba; 
}
.form-section * { color: #333 !important; }
.button { 
    background: #007cba; 
    color: white !important; 
    padding: 15px 30px; 
    border: none; 
    border-radius: 6px; 
    cursor: pointer; 
    margin: 8px; 
    text-decoration: none; 
    display: inline-block; 
    font-size: 16px;
    font-weight: bold;
}
.button:hover { background: #005a87; }
.button.success { background: #28a745; }
.button.warning { background: #ffc107; color: #333 !important; }
.message { 
    background: #d4edda; 
    color: #155724 !important; 
    padding: 20px; 
    border-radius: 8px; 
    margin: 20px 0; 
    border: 1px solid #c3e6cb; 
    font-size: 18px;
    font-weight: bold;
}
.error { 
    background: #f8d7da; 
    color: #721c24 !important; 
    padding: 20px; 
    border-radius: 8px; 
    margin: 20px 0; 
    border: 1px solid #f5c6cb; 
    font-size: 18px;
    font-weight: bold;
}
table { 
    width: 100%; 
    border-collapse: collapse; 
    margin: 20px 0; 
    background: white;
}
th, td { 
    padding: 15px; 
    border: 1px solid #ddd; 
    text-align: left; 
    color: #333 !important;
}
th { 
    background: #f8f9fa !important; 
    font-weight: bold; 
    font-size: 16px;
}
.series-math { color: #4CAF50 !important; font-weight: bold; }
.series-science { color: #2196F3 !important; font-weight: bold; }
.series-english { color: #FF9800 !important; font-weight: bold; }
.series-social { color: #9C27B0 !important; font-weight: bold; }
input, select { 
    padding: 12px; 
    border: 1px solid #ddd; 
    border-radius: 5px; 
    margin: 8px; 
    color: #333;
    font-size: 16px;
    min-width: 200px;
}
label { 
    font-weight: bold; 
    color: #333 !important; 
    display: block;
    margin: 15px 0 8px 0;
    font-size: 16px;
}
.intro-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white !important;
    padding: 30px;
    border-radius: 10px;
    margin: 20px 0;
    text-align: center;
}
.intro-box * { color: white !important; }
</style>

<div class="BasicsBox">
    <div class="intro-box">
        <h1>📋 Collection Template Manager</h1>
        <p style="font-size: 18px;">Simple tool to manage collection templates with ID display</p>
    </div>
    
    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Step 1: Update Collection Names -->
    <div class="form-section">
        <h2>🔄 Step 1: Show Collection IDs</h2>
        <p style="font-size: 16px;">Update all collection names to show their IDs (e.g., "MATH" becomes "[1000] MATH")</p>
        
        <form method="post">
            <?php generateFormToken("update_names"); ?>
            <input type="hidden" name="action" value="update_names">
            <button type="submit" class="button success" onclick="return confirm('Update all collection names to show IDs?')">
                🔄 Update All Collection Names with IDs
            </button>
        </form>
        
        <p><strong>This will:</strong></p>
        <ul>
            <li>Add IDs to all collection names</li>
            <li>Make collections easily searchable by ID</li>
            <li>Enable color-coded organization</li>
        </ul>
    </div>
    
    <!-- Step 2: Copy MATH Template -->
    <div class="form-section">
        <h2>📋 Step 2: Copy MATH Template</h2>
        <p style="font-size: 16px;">Copy the MATH template structure to create new subject collections</p>
        
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
            <button type="submit" class="button" onclick="return confirm('Copy MATH template to create new subject collections?')">
                📋 Copy MATH Template
            </button>
        </form>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;">
            <h4 style="color: #856404 !important;">📊 What gets copied:</h4>
            <ul style="color: #856404 !important;">
                <li>Complete MATH structure (all grades and states)</li>
                <li>Sequential IDs in target series</li>
                <li>Updated names for new subject</li>
                <li>Same permissions and settings</li>
            </ul>
        </div>
    </div>
    
    <!-- Current Collections Overview -->
    <div class="form-section">
        <h2>📊 Current Collections Status</h2>
        <table>
            <tr>
                <th>Series</th>
                <th>ID Range</th>
                <th>Collections</th>
                <th>Sample Collections</th>
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
                
                $samples = ps_query("SELECT ref, name FROM collection WHERE ref BETWEEN ? AND ? ORDER BY ref LIMIT 2", 
                                   array("i", $info['start'], "i", $info['end']));
                $sample_text = '';
                foreach ($samples as $sample) {
                    $sample_text .= $sample['ref'] . ': ' . escape($sample['name']) . '<br>';
                }
                if (empty($sample_text)) $sample_text = 'No collections';
                
                $status = $count > 0 ? '✅ Ready' : '⚪ Empty';
                if ($name == 'MATH' && $count == 0) $status = '❌ Template Missing';
            ?>
                <tr>
                    <td class="<?php echo $info['class']; ?>" style="font-size: 16px; font-weight: bold;"><?php echo $name; ?></td>
                    <td style="font-size: 14px;"><?php echo $info['start'] . '-' . $info['end']; ?></td>
                    <td style="font-size: 16px; font-weight: bold;"><?php echo $count; ?></td>
                    <td style="font-size: 13px;"><?php echo $sample_text; ?></td>
                    <td style="font-size: 14px; font-weight: bold;"><?php echo $status; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <!-- Quick Actions -->
    <div class="form-section">
        <h2>🚀 Quick Actions</h2>
        <div style="text-align: center;">
            <a href="<?php echo $baseurl_short; ?>pages/collections.php" class="button">
                📚 View All Collections
            </a>
            
            <a href="<?php echo $baseurl_short; ?>pages/collection_edit.php" class="button success">
                ➕ Create New Collection
            </a>
            
            <a href="<?php echo $baseurl_short; ?>pages/search.php" class="button warning">
                🔍 Search Collections
            </a>
        </div>
    </div>
    
    <!-- Instructions -->
    <div class="form-section">
        <h2>📖 How It Works</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div>
                <h3 style="color: #28a745 !important;">✅ Simple Process:</h3>
                <ol style="font-size: 16px; line-height: 1.8;">
                    <li><strong>Update Names:</strong> Adds IDs to collection names</li>
                    <li><strong>Copy Template:</strong> Duplicates MATH structure</li>
                    <li><strong>View Results:</strong> See organized collections</li>
                    <li><strong>Modify:</strong> Edit collections as needed</li>
                </ol>
            </div>
            
            <div>
                <h3 style="color: #007cba !important;">🎨 Color System:</h3>
                <ul style="font-size: 16px; line-height: 1.8;">
                    <li><span class="series-math">MATH: 1000-1999</span> (Green)</li>
                    <li><span class="series-science">SCIENCE: 2000-2999</span> (Blue)</li>
                    <li><span class="series-english">ENGLISH: 3000-3999</span> (Orange)</li>
                    <li><span class="series-social">SOCIAL: 4000-4999</span> (Purple)</li>
                </ul>
            </div>
        </div>
        
        <div style="background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid #28a745;">
            <h3 style="color: #155724 !important;">🎯 Benefits:</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <ul style="color: #155724 !important; font-size: 16px;">
                    <li>Easy identification by ID numbers</li>
                    <li>Consistent structure across subjects</li>
                    <li>Simple one-click copying</li>
                </ul>
                <ul style="color: #155724 !important; font-size: 16px;">
                    <li>Color-coded visual organization</li>
                    <li>Search by ID functionality</li>
                    <li>No plugin dependencies</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../include/footer.php'; ?>