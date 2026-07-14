<?php

function HookCollection_template_managerCollectionsCollections_top_nav()
    {
    global $baseurl_short, $lang;
    ?>
    <div class="TopInpageNav">
        <a href="<?php echo $baseurl_short; ?>plugins/collection_template_manager/pages/copy_template.php" 
           class="button" style="background: #007cba; color: white; margin-right: 10px;">
            <i class="fa fa-copy"></i> Copy Collection Template
        </a>
    </div>
    <?php
    }