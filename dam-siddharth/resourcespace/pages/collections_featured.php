<?php

include_once "../include/boot.php";

$k = trim(getval("k", ""));
$parent = (int) getval("parent", $featured_collections_root_collection, true);

if ($k == "" || !check_access_key_collection($parent, $k)) {
    include "../include/authenticate.php";
    $parent = (int) getval("parent", $featured_collections_root_collection, true);
} else {
    // Disable CSRF when someone is accessing an external share (public context)
    $CSRF_enabled = false;

    // Force simple view because otherwise it assumes you're logged in. The JS api function will use the native mode to
    // get the resource count and loading the actions always authenticates and both actions will (obviously) error.
    $themes_simple_view = true;
}

if (!$enable_themes) {
    http_response_code(403);
    exit($lang["error-permissiondenied"]);
}

// Access control
if ($parent > 0 && !featured_collection_check_access_control($parent)) {
    error_alert($lang["error-permissiondenied"], true, 403);
    exit();
}

$smart_rtf = (int) getval("smart_rtf", 0, true);
$smart_fc_parent = getval("smart_fc_parent", 0, true);
$smart_fc_parent = ($smart_fc_parent > 0 ? $smart_fc_parent : null);

$general_url_params = ($k == "" ? array() : array("k" => $k));

$parent_collection_data = get_collection($parent);
$parent_collection_data = (is_array($parent_collection_data) ? $parent_collection_data : array());


if (getval("new", "") == "true" && getval("cta", "") == "true") {
    new_featured_collection_form($parent);
    exit();
}

// List of all FCs. For huge trees, helps increase performance but might require an increase for memory_limit in php.ini
// Clear featured collections cache if we just created/edited a collection
if (getval("reload", "") == "true" || getval("ref", "") != "") {
    clear_query_cache("featured_collections");
    if (isset($GLOBALS['get_collection_cache'])) {
        $GLOBALS['get_collection_cache'] = array();
    }
}
$all_fcs = get_all_featured_collections();
include "../include/header.php";
?>

<div class="BasicsBox FeaturedSimpleLinks">
    <?php
    if ($parent > 0) {
        $links_trail = array(
            array(
                "title" => $lang["themes"],
                "href"  => generateURL("{$baseurl_short}pages/collections_featured.php", $general_url_params)
            )
        );

        $fc_branch_path = move_featured_collection_branch_path_root(compute_node_branch_path($all_fcs, $parent));

        if (empty($fc_branch_path)) {
            $links_trail = [];
        }

        // Add menu options for the current FC (category) node
        $current_fc_node = end($fc_branch_path);
        $current_fc_node_key = key($fc_branch_path);
        reset($fc_branch_path);
        if ($current_fc_node_key !== null) {
            if ($smart_rtf == 0 && get_smart_theme_headers() !== []) {
                $is_smart_featured_collection = true;
            } else if ($parent == 0 && $smart_rtf > 0 && metadata_field_view_access($smart_rtf)) {
                $is_smart_featured_collection = true;
            } else {
                $is_smart_featured_collection = false;
            }
            $is_featured_collection_category = is_featured_collection_category($current_fc_node);
            $is_featured_collection = (!$is_featured_collection_category && !$is_smart_featured_collection);
            $fc_category_has_children = $is_featured_collection_category && (isset($fc['has_children']) ? (bool) $fc['has_children'] : false);

            $collection_data = get_collection($current_fc_node['ref']);
            if (!is_array($collection_data)) {
                $collection_data = [];
            }

            if (($is_featured_collection || $is_featured_collection_category) && collection_readable($current_fc_node['ref'])) {
                $fc_branch_path[$current_fc_node_key]['context_menu'][] = [
                    'icon' => 'fa-solid fa-fw fa-circle-check',
                    'text' => $lang['action-select'],
                    'custom_onclick' => sprintf("return ChangeCollection(%s, '');", escape($current_fc_node['ref'])),
                ];
            }

            if (
                (
                    ($is_featured_collection && !$is_smart_featured_collection)
                    || $is_featured_collection_category
                )
                && allow_upload_to_collection($collection_data)
            ) {
                $fc_branch_path[$current_fc_node_key]['context_menu'][] = [
                    'href' => $GLOBALS['upload_then_edit']
                        ? generateURL("{$baseurl_short}pages/upload_batch.php", ['collection_add' => $current_fc_node['ref']])
                        : generateURL(
                            "{$baseurl_short}pages/edit.php",
                            [
                                'uploader' => $GLOBALS['upload_then_edit'],
                                'ref' => -$GLOBALS['userref'],
                                'collection_add' => $current_fc_node['ref']
                            ]
                        ),
                    'icon' => 'fa fa-fw fa-upload',
                    'text' => $lang['action-upload-to-collection'],
                ];
            }

            // Add Template option - always show for debugging
            $child_count = ps_value("SELECT COUNT(*) as value FROM collection WHERE parent = ?", ["i", $current_fc_node['ref']], 0);
            // Temporarily show for all collections to debug
            $fc_branch_path[$current_fc_node_key]['context_menu'][] = [
                'href' => '#',
                'onclick' => "showTemplateConfirmation(" . $current_fc_node['ref'] . ", '" . escape($current_fc_node['name']) . "'); return false;",
                'icon' => 'fa fa-fw fa-folder-plus',
                'text' => 'Add Template (Children: ' . $child_count . ')',
            ];

            if (($is_featured_collection || can_edit_featured_collection_category()) && collection_writeable($current_fc_node['ref'])) {
                $fc_branch_path[$current_fc_node_key]['context_menu'][] = [
                    'href' => generateURL(
                        "{$baseurl_short}pages/collection_edit.php",
                        [
                            'ref' => $current_fc_node['ref'],
                            'redirection_endpoint' => urlencode(
                                generateURL(
                                    "{$baseurl_short}pages/collections_featured.php",
                                    $general_url_params,
                                    ['parent' => $current_fc_node['parent']]
                                )
                            )
                        ]
                    ),
                    'icon' => 'fa-solid fa-fw fa-pen-to-square',
                    'text' => $lang['action-edit'],
                    'modal_load' => true,
                ];
            }

            if (
                can_delete_collection($collection_data, $userref, $k)
                && can_delete_featured_collection($current_fc_node['ref'])
            ) {
                $fc_branch_path[$current_fc_node_key]['context_menu'][] = [
                    'icon' => 'fa-solid fa-fw fa-trash-can',
                    'text' => $lang['action-deletecollection'],
                    'custom_onclick' => sprintf(
                        'return delete_collection(%s, \'%s\', \'%s\');',
                        escape($current_fc_node['ref']),
                        escape($lang['collectiondeleteconfirm']),
                        escape(generate_csrf_js_object('delete_collection'))
                    ),
                ];
            }
        }

        $branch_trail = array_map(function ($branch) use ($baseurl_short, $general_url_params) {
            $current_fc_node_menu = isset($branch['context_menu']) ? ['context_menu' => $branch['context_menu']] : [];

            return [
                "title" => strip_prefix_chars(i18n_get_translated($branch["name"]), "*"),
                "href"  => generateURL(
                    "{$baseurl_short}pages/collections_featured.php",
                    $general_url_params,
                    array("parent" => $branch["ref"])
                ),
                ...$current_fc_node_menu,
            ];
        }, $fc_branch_path);

        renderBreadcrumbs(array_merge($links_trail, $branch_trail), "", "BreadcrumbsBoxTheme");
    }

    // Default rendering options (should apply to both FCs and smart FCs)
    $full_width = !$themes_simple_view;
    $rendering_options = array(
        "full_width" => $full_width,
        "general_url_params" => $general_url_params,
        "all_fcs" => $all_fcs,
    );

    $featured_collections = ($smart_rtf == 0 ? get_featured_collections($parent, array()) : array());
    usort($featured_collections, "order_featured_collections");
    render_featured_collections(
        array_merge($rendering_options, ["reorder" => can_reorder_featured_collections()]),
        $featured_collections
    );

    $smart_fcs_list = array();

    if ($parent == 0 && $smart_rtf == 0) {
        // Root level - this is made up of all the fields that have a Smart theme name set.
        $smart_fc_headers = array_filter(get_smart_theme_headers(), function (array $v) {
            return metadata_field_view_access($v["ref"]);
        });

        $smart_fcs_list = array_map(function (array $v) use ($FEATURED_COLLECTION_BG_IMG_SELECTION_OPTIONS) {
            return array(
                "ref" => $v["ref"],
                "name" => $v["smart_theme_name"],
                "type" => COLLECTION_TYPE_FEATURED,
                "parent" => null,
                "thumbnail_selection_method" => $FEATURED_COLLECTION_BG_IMG_SELECTION_OPTIONS["most_popular_image"],
                "has_resources" => 0,
                "resource_type_field" => $v["ref"]);
        },
        $smart_fc_headers);
    } elseif ($parent == 0 && $smart_rtf > 0 && metadata_field_view_access($smart_rtf)) {
        // Smart fields. If a category tree, then a parent could be passed once user requests a lower level than root of the tree
        $resource_type_field = get_resource_type_field($smart_rtf);

        if ($resource_type_field !== false && in_array($resource_type_field["type"], $FIXED_LIST_FIELD_TYPES)) {
            // We go one level at a time so we don't need it to search recursively even if this is a FIELD_TYPE_CATEGORY_TREE
            $smart_fc_nodes = get_smart_themes_nodes($smart_rtf, false, $smart_fc_parent, $resource_type_field);
            $smart_fcs_list = array_map(function (array $v) use ($smart_rtf, $FEATURED_COLLECTION_BG_IMG_SELECTION_OPTIONS) {
                return array(
                    "ref" => $v["ref"],
                    "name" => $v["name"],
                    "type" => COLLECTION_TYPE_FEATURED,
                    "parent" => $v["ref"], # parent here is the node ID. When transformed to a FC this parent will be used for going to the next level down the branch
                    "thumbnail_selection_method" => $FEATURED_COLLECTION_BG_IMG_SELECTION_OPTIONS["most_popular_image"],
                    "has_resources" => 0,
                    "resource_type_field" => $smart_rtf,
                    "node_is_parent" => $v["is_parent"]
                );
            },
            $smart_fc_nodes);
        }
    }

    $rendering_options["smart"] = (count($smart_fcs_list) > 0);
    render_featured_collections($rendering_options, $smart_fcs_list);
    unset($rendering_options["smart"]);

    // If parent folder has resources AND subfolders, render the resources here too
    if ($parent > 0 && $smart_rtf == 0) {
        $parent_resources = ps_query(
            "SELECT cr.resource FROM collection_resource cr
             INNER JOIN resource r ON r.ref = cr.resource
             WHERE cr.collection = ? AND r.archive != 3
             ORDER BY cr.date_added DESC",
            ['i', $parent]
        );
        if (!empty($parent_resources)) {
            $resource_refs = array_column($parent_resources, 'resource');
            echo '<div class="FeaturedCollectionResources" style="display:contents;">';
            foreach ($resource_refs as $res_ref) {
                $res_data = get_resource_data((int)$res_ref);
                if (!$res_data) continue;
                if (get_resource_access($res_data) == RESOURCE_ACCESS_INVALID_REQUEST) continue;

                $title = strip_tags($res_data['field' . $view_title_field] ?? '');
                if (trim($title) == '') $title = ($lang['resource-1'] ?? 'Resource') . ' ' . $res_ref;

                // Get preview thumbnail URL
                $preview_path = get_resource_path((int)$res_ref, true, 'thm', false);
                $preview_url  = file_exists($preview_path) ? get_resource_path((int)$res_ref, false, 'thm', false) : '';

                $view_url = generateURL("{$baseurl_short}pages/view.php", array_merge($general_url_params, ['ref' => (int)$res_ref]));
                ?>
                <div class="FeaturedSimplePanel HomePanel DashTile FeaturedSimpleTile">
                    <div>
                        <a href="<?php echo escape($view_url); ?>" onclick="return CentralSpaceLoad(this, true);" class="FeaturedSimpleLink">
                            <div class="FeaturedSimpleTileContents">
                                <h2>
                                    <?php if ($preview_url != ''): ?>
                                        <div class="FeaturedImageTile" style="background-image:url('<?php echo escape($preview_url); ?>')"></div>
                                    <?php else: ?>
                                        <i class="fa fa-fw fa-file" aria-hidden="true"></i>
                                    <?php endif; ?>
                                    <?php echo escape($title); ?>
                                </h2>
                            </div>
                        </a>
                    </div>
                </div>
                <?php
            }
            echo '</div>';
        }
    }

    if ($k == "" && $smart_rtf == 0) {
        if (checkperm("h") && can_create_collections()) {
            // Render the + tile with our custom choice popup instead of directly opening the create dialog
            $create_url = generateURL(
                "{$baseurl_short}pages/collections_featured.php",
                array(
                    "new" => "true",
                    "cta" => "true",
                    "parent" => $parent,
                )
            );
            ?>
            <div id="FeaturedSimpleTile" class="FeaturedSimplePanel HomePanel DashTile FeaturedSimpleTile FeaturedCallToActionTile">
                <a href="#" onclick="rsShowCreateChoice('<?php echo escape($create_url); ?>', <?php echo (int)$parent; ?>); return false;">
                    <div class="FeaturedSimpleTileContents">
                        <div class="FeaturedSimpleTileText">
                            <h2><span class="fas fa-plus-circle"></span></h2>
                        </div>
                    </div>
                </a>
            </div>
            <?php
        }

        if (allow_upload_to_collection($parent_collection_data)) {
            $upload_url = generateURL(
                "{$baseurl_short}pages/edit.php",
                array(
                    "uploader" => $top_nav_upload_type,
                    "ref" => -$userref,
                    "collection_add" => $parent
                )
            );

            if ($upload_then_edit) {
                $upload_url = generateURL("{$baseurl_short}pages/upload_batch.php", array("collection_add" => $parent));
            }

            $rendering_options["html_h2_span_class"] = "fa fa-fw fa-upload";
            $rendering_options["centralspaceload"] = true;

            render_new_featured_collection_cta($upload_url, $rendering_options);
        }
    }
    ?>
</div><!-- End of BasicsBox FeaturedSimpleLinks -->

<script>
    /** Show the Featured Collection (category) context menu */
    function showContextMenu(el)
    {
        hideContextMenu();

        const top_right_menu_btn = jQuery(el);
        const context_menu = top_right_menu_btn.closest('.FeaturedSimpleTile, .BreadcrumbsBox').find('.context-menu-container');
        let menu_el_tmp = context_menu.clone().appendTo('.FeaturedSimpleLinks');
        menu_el_tmp.css({
            'display': 'block',
            'visibility': 'hidden',
        });
        const is_responsive = window.matchMedia("(max-width: 900px)").matches;
        const uicenter_el = document.getElementById('UICenter');
        console.debug('top_right_menu_btn = %o', top_right_menu_btn);
        console.debug('context_menu = %o', context_menu);
        console.debug('is_responsive = %o', is_responsive);

        let off_top = 0;
        let off_left = 0;
        let off_top_rev = 0;
        let off_left_rev = 0;
        const header_bb = document.getElementById('Header').getBoundingClientRect();
        const container_bb = document.querySelector('.FeaturedSimpleLinks').getBoundingClientRect();
        const menu_btn_bb = el.getBoundingClientRect();
        const menu_btn_computed_style = getComputedStyle(el);
        const menu_bb = menu_el_tmp[0].getBoundingClientRect();

        /*
        Determine the position offset for the menu so it's within the proximity of the calling top right menu icon. 
        Notes:
        - the bounding box (BB) ignores margins so we have to account for those too;
        - in responsive mode, instead of the UICenter, the body is overflowing vertically (Y axis);
        */
        if (is_responsive) {
            off_top += document.body.scrollTop
                - header_bb.height
                - document.getElementById('SearchBarContainer').getBoundingClientRect().height;
            off_top_rev = menu_bb.height - menu_btn_bb.height + 50;
            off_left_rev -= menu_bb.width + menu_btn_bb.width + parseInt(menu_btn_computed_style.margin);
        } else {
            const menu_btn_margin = 2 * parseInt(menu_btn_computed_style.margin);
            off_top += uicenter_el.scrollTop - header_bb.height;
            off_left += menu_btn_margin;
            off_left_rev -= menu_bb.width + menu_btn_bb.width - menu_btn_margin;
        }

        // For a better UX, check if the menu will go outside the container/view boundaries to ensure users always have
        // the menu in sight
        if ((menu_btn_bb.left + off_left + menu_bb.width) > container_bb.right) {
            off_left = off_left_rev;
        }
        if (is_responsive && (menu_btn_bb.top + menu_bb.height) > window.innerHeight) {
            off_top -= off_top_rev;
        }

        console.debug("off_top = %o -- off_left = %o", off_top, off_left);
        menu_el_tmp.remove();

        context_menu
            .css({
                display: 'none',
                top: menu_btn_bb.top + off_top,
                left: menu_btn_bb.left + off_left,
            })
            .slideDown(150);

        return false;
    }

    /** Hide the Featured Collection (category) context menu */
    function hideContextMenu()
    {
        let menu_content = jQuery('.FeaturedSimpleTile .context-menu-container, .BreadcrumbsBox .context-menu-container');
        if (menu_content.is(':visible')) {
            menu_content.slideUp(150);
        }
    }

    onkeydown = (e) => {
        // On esc, close down contextual menus 
        if (e.keyCode === 27) {
            hideContextMenu();
        }
    };
    onmousedown = (e) => {
        // Close menus when clicking away
        if (!e.target.closest('.context-menu-container')) {
            hideContextMenu();
        }
    };

    jQuery(document).ready(function () {
        // Get and update display for total resource count for each of the rendered featured collections (@see render_featured_collection() for more info)
        var fcs_waiting_total = jQuery('.FeaturedSimpleTile.FullWidth .FeaturedSimpleTileContents h2 span[data-tag="resources_count"]');
        var fc_refs = [];

        fcs_waiting_total.each(function(i, v) {
            fc_refs.push(jQuery(v).data('fc-ref'));
        });

        if (fc_refs.length > 0) {
            api('get_collections_resource_count', {'refs': fc_refs.join(',')}, function(response) {
                var lang_resource = '<?php echo escape($lang['youfoundresource']); ?>';
                var lang_resources = '<?php echo escape($lang['youfoundresources']); ?>';

                Object.keys(response).forEach(function(k) {
                    var total_count = response[k];
                    jQuery('.FeaturedSimpleTile.FullWidth .FeaturedSimpleTileContents h2 span[data-tag="resources_count"][data-fc-ref="' + k + '"]')
                        .text(total_count + ' ' + (total_count == 1 ? lang_resource : lang_resources));
                });
            },
            <?php echo generate_csrf_js_object('get_collections_resource_count'); ?>
            );
        }

        <?php if (!$themes_simple_view) { ?>
            // Load collection actions when dropdown is clicked
            jQuery('.fcollectionactions').on("focus", function(e) {
                var el = jQuery(this);

                if (el.attr('data-actions-populating') != '0') {
                    return false
                }

                el.attr('data-actions-populating','1');
                var action_selection_id = el.attr('id');
                var colref = el.attr('data-col-id');

                LoadActions('themes',action_selection_id,'collection',colref);
            });
        <?php } ?>
    });

    <?php if ($allow_fc_reorder) { ?>
        // Re-order capability
        jQuery(function() {
            // Disable for touch screens
            if (is_touch_device()) {
                return false;
            }

            jQuery('.BasicsBox.FeaturedSimpleLinks').sortable({
                items: '.SortableItem',
                distance: 20,
                update: function(event, ui) {
                    let html_ids_new_order = jQuery('.BasicsBox.FeaturedSimpleLinks').sortable('toArray');
                    let fcs_new_order = html_ids_new_order.map(id => jQuery('#' + id).data('fc-ref'));
                    console.debug('fcs_new_order=%o', fcs_new_order);
                    <?php if ($descthemesorder) { ?>
                        fcs_new_order = fcs_new_order.reverse();
                        console.debug('fcs_new_order_reversed=%o', fcs_new_order);
                    <?php } ?>
                    api(
                        'reorder_featured_collections',
                        {'refs': fcs_new_order},
                        null,
                        <?php echo generate_csrf_js_object('reorder_featured_collections'); ?>
                    );
                }
            });
        });
    <?php } ?>

    // Add Template functionality - Show template selection dialog
    function showTemplateConfirmation(collectionRef, collectionName) {
        showTemplateSelectionModal(collectionRef, collectionName);
    }
    
    // Show template selection modal with 3 options
    function showTemplateSelectionModal(collectionRef, collectionName) {
        var modalHtml = '<div id="templateSelectionModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 10000; display: flex; align-items: center; justify-content: center; font-family: Arial, sans-serif; direction: ltr;">' +
            '<div style="background: white; border-radius: 8px; padding: 30px 35px; max-width: 500px; width: 90%; box-shadow: 0 8px 30px rgba(0,0,0,0.2); text-align: left; direction: ltr;">' +
            
            // Icon and IP
            '<div style="display: flex; align-items: center; margin-bottom: 25px; direction: ltr;">' +
            '<i class="fa fa-globe" style="font-size: 28px; color: #5f6368; margin-right: 12px;"></i>' +
            '<h2 style="margin: 0; font-size: 20px; color: #202124; font-weight: 400; text-align: left;">192.168.1.46</h2>' +
            '</div>' +
            
            // Question
            '<h3 style="margin: 0 0 25px 0; font-size: 16px; color: #5f6368; font-weight: 400; text-align: left; direction: ltr;">Select a template to create:</h3>' +
            
            // Template options
            '<div style="margin-bottom: 30px;">' +
            '<div onclick="selectTemplate(' + collectionRef + ', \'' + collectionName + '\', \'complete\')" style="padding: 15px; margin-bottom: 12px; border: 2px solid #e8eaed; border-radius: 6px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor=\'#1a73e8\'; this.style.background=\'#f8f9fa\';" onmouseout="this.style.borderColor=\'#e8eaed\'; this.style.background=\'white\';">' +
            '<div style="font-weight: 500; color: #202124; margin-bottom: 5px; font-size: 15px;">1. Complete Template</div>' +
            '<div style="color: #5f6368; font-size: 13px;">Grade 1-8 with all nested folders</div>' +
            '</div>' +
            
            '<div onclick="selectTemplate(' + collectionRef + ', \'' + collectionName + '\', \'grade\')" style="padding: 15px; margin-bottom: 12px; border: 2px solid #e8eaed; border-radius: 6px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor=\'#1a73e8\'; this.style.background=\'#f8f9fa\';" onmouseout="this.style.borderColor=\'#e8eaed\'; this.style.background=\'white\';">' +
            '<div style="font-weight: 500; color: #202124; margin-bottom: 5px; font-size: 15px;">2. State Level Template</div>' +
            '<div style="color: #5f6368; font-size: 13px;">State 1 with Video, Worksheet, Lesson Plan, Assessment</div>' +
            '</div>' +
            
            '<div onclick="selectTemplate(' + collectionRef + ', \'' + collectionName + '\', \'state\')" style="padding: 15px; border: 2px solid #e8eaed; border-radius: 6px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor=\'#1a73e8\'; this.style.background=\'#f8f9fa\';" onmouseout="this.style.borderColor=\'#e8eaed\'; this.style.background=\'white\';">' +
            '<div style="font-weight: 500; color: #202124; margin-bottom: 5px; font-size: 15px;">3. Lesson Level Template</div>' +
            '<div style="color: #5f6368; font-size: 13px;">Video, Worksheet, Lesson Plan, Assessment only</div>' +
            '</div>' +
            '</div>' +
            
            // Cancel button
            '<div style="display: flex; justify-content: flex-end; direction: ltr;">' +
            '<button onclick="closeTemplateSelectionModal()" style="padding: 12px 32px; border: none; background: #e8eaed; border-radius: 4px; cursor: pointer; font-size: 15px; color: #202124; font-weight: 500;">Cancel</button>' +
            '</div>' +
            
            '</div>' +
            '</div>';
        
        jQuery('body').append(modalHtml);
    }
    
    function closeTemplateSelectionModal() {
        jQuery('#templateSelectionModal').remove();
    }
    
    function selectTemplate(collectionRef, collectionName, templateType) {
        closeTemplateSelectionModal();
        
        var structure = '';
        
        var stateBlock =
                '    └── State 1 (ID-XX)\n' +
                '        ├── Video (ID-XX)\n' +
                '        │   └── Video Topic (ID-XX)\n' +
                '        │       ├── Script (ID-XX)\n' +
                '        │       │   ├── Source File (ID-XX)\n' +
                '        │       │   └── Output File (ID-XX)\n' +
                '        │       ├── Audio (ID-XX)\n' +
                '        │       │   ├── Source File (ID-XX)\n' +
                '        │       │   └── Output File (ID-XX)\n' +
                '        │       ├── Video Source Files (ID-XX)\n' +
                '        │       │   ├── Flash Files (ID-XX)\n' +
                '        │       │   ├── AE Files (ID-XX)\n' +
                '        │       │   ├── AI Files (ID-XX)\n' +
                '        │       │   ├── Premier Source Files (ID-XX)\n' +
                '        │       │   └── Graphics Files (ID-XX)\n' +
                '        │       ├── Final Video (ID-XX)\n' +
                '        │       │   ├── High Resolution (ID-XX)\n' +
                '        │       │   └── Low Resolution (ID-XX)\n' +
                '        │       └── Thumbnail (ID-XX)\n' +
                '        │           ├── Source File (ID-XX)\n' +
                '        │           └── Thumbnail Image (ID-XX)\n' +
                '        ├── Worksheet (ID-XX)\n' +
                '        │   └── Worksheet Topic (ID-XX)\n' +
                '        │       ├── PDF (ID-XX)\n' +
                '        │       └── AI Source Files (ID-XX)\n' +
                '        ├── Lesson Plan (ID-XX)\n' +
                '        │   └── Lesson Plan Topic (ID-XX)\n' +
                '        │       ├── PDF (ID-XX)\n' +
                '        │       └── AI Source Files (ID-XX)\n' +
                '        └── Assessment (ID-XX)\n' +
                '            └── Assessment Topic (ID-XX)\n' +
                '                ├── PDF (ID-XX)\n' +
                '                └── AI Source Files (ID-XX)\n';

        if (templateType === 'complete') {
            structure = 'Grade 1 (ID-' + collectionRef + ')\n' +
                '├── Grade 1 (ID-XX)\n' + stateBlock +
                '├── Grade 2 (ID-XX)\n' + stateBlock +
                '├── Grade 3 (ID-XX)\n' + stateBlock +
                '├── Grade 4 (ID-XX)\n' + stateBlock +
                '├── Grade 5 (ID-XX)\n' + stateBlock +
                '├── Grade 6 (ID-XX)\n' + stateBlock +
                '├── Grade 7 (ID-XX)\n' + stateBlock +
                '└── Grade 8 (ID-XX)\n' + stateBlock;
        } else if (templateType === 'grade') {
            structure = 'Grade (ID-' + collectionRef + ')\n' +
                '└── State 1 (ID-XX)\n' +
                '    ├── Video (ID-XX)\n' +
                '    │   └── Video Topic (ID-XX)\n' +
                '    │       ├── Script (ID-XX)\n' +
                '    │       │   ├── Source File (ID-XX)\n' +
                '    │       │   └── Output File (ID-XX)\n' +
                '    │       ├── Audio (ID-XX)\n' +
                '    │       │   ├── Source File (ID-XX)\n' +
                '    │       │   └── Output File (ID-XX)\n' +
                '    │       ├── Video Source Files (ID-XX)\n' +
                '    │       │   ├── Flash Files (ID-XX)\n' +
                '    │       │   ├── AE Files (ID-XX)\n' +
                '    │       │   ├── AI Files (ID-XX)\n' +
                '    │       │   ├── Premier Source Files (ID-XX)\n' +
                '    │       │   └── Graphics Files (ID-XX)\n' +
                '    │       ├── Final Video (ID-XX)\n' +
                '    │       │   ├── High Resolution (ID-XX)\n' +
                '    │       │   └── Low Resolution (ID-XX)\n' +
                '    │       └── Thumbnail (ID-XX)\n' +
                '    │           ├── Source File (ID-XX)\n' +
                '    │           └── Thumbnail Image (ID-XX)\n' +
                '    ├── Worksheet (ID-XX)\n' +
                '    │   └── Worksheet Topic (ID-XX)\n' +
                '    │       ├── PDF (ID-XX)\n' +
                '    │       └── AI Source Files (ID-XX)\n' +
                '    ├── Lesson Plan (ID-XX)\n' +
                '    │   └── Lesson Plan Topic (ID-XX)\n' +
                '    │       ├── PDF (ID-XX)\n' +
                '    │       └── AI Source Files (ID-XX)\n' +
                '    └── Assessment (ID-XX)\n' +
                '        └── Assessment Topic (ID-XX)\n' +
                '            ├── PDF (ID-XX)\n' +
                '            └── AI Source Files (ID-XX)';
        } else if (templateType === 'state') {
            structure = 'State (ID-' + collectionRef + ')\n' +
                '├── Video (ID-XX)\n' +
                '│   └── Video Topic (ID-XX)\n' +
                '│       ├── Script (ID-XX)\n' +
                '│       │   ├── Source File (ID-XX)\n' +
                '│       │   └── Output File (ID-XX)\n' +
                '│       ├── Audio (ID-XX)\n' +
                '│       │   ├── Source File (ID-XX)\n' +
                '│       │   └── Output File (ID-XX)\n' +
                '│       ├── Video Source Files (ID-XX)\n' +
                '│       │   ├── Flash Files (ID-XX)\n' +
                '│       │   ├── AE Files (ID-XX)\n' +
                '│       │   ├── AI Files (ID-XX)\n' +
                '│       │   ├── Premier Source Files (ID-XX)\n' +
                '│       │   └── Graphics Files (ID-XX)\n' +
                '│       ├── Final Video (ID-XX)\n' +
                '│       │   ├── High Resolution (ID-XX)\n' +
                '│       │   └── Low Resolution (ID-XX)\n' +
                '│       └── Thumbnail (ID-XX)\n' +
                '│           ├── Source File (ID-XX)\n' +
                '│           └── Thumbnail Image (ID-XX)\n' +
                '├── Worksheet (ID-XX)\n' +
                '│   └── Worksheet Topic (ID-XX)\n' +
                '│       ├── PDF (ID-XX)\n' +
                '│       └── AI Source Files (ID-XX)\n' +
                '├── Lesson Plan (ID-XX)\n' +
                '│   └── Lesson Plan Topic (ID-XX)\n' +
                '│       ├── PDF (ID-XX)\n' +
                '│       └── AI Source Files (ID-XX)\n' +
                '└── Assessment (ID-XX)\n' +
                '    └── Assessment Topic (ID-XX)\n' +
                '        ├── PDF (ID-XX)\n' +
                '        └── AI Source Files (ID-XX)';
        }
        
        showTemplateModal(collectionRef, collectionName, structure, templateType);
    }
    
    function showTemplateModal(collectionRef, collectionName, structure, templateType) {
        var modalHtml = '<div id="templateModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 10000; display: flex; align-items: center; justify-content: center; font-family: Arial, sans-serif; direction: ltr;">' +
            '<div style="background: white; border-radius: 8px; padding: 30px 35px; max-width: 500px; width: 90%; box-shadow: 0 8px 30px rgba(0,0,0,0.2); text-align: left; direction: ltr;">' +
            
            // Icon and IP
            '<div style="display: flex; align-items: center; margin-bottom: 25px; direction: ltr;">' +
            '<i class="fa fa-globe" style="font-size: 28px; color: #5f6368; margin-right: 12px;"></i>' +
            '<h2 style="margin: 0; font-size: 20px; color: #202124; font-weight: 400; text-align: left;">192.168.1.2</h2>' +
            '</div>' +
            
            // Question
            '<h3 style="margin: 0 0 25px 0; font-size: 16px; color: #5f6368; font-weight: 400; text-align: left; direction: ltr;">Do you want to create these folders?</h3>' +
            
            // Structure box - using pre tag to preserve spacing
            '<pre style="background: white; padding: 0; margin: 0 0 25px 0; max-height: 450px; overflow-y: auto; font-family: Arial, sans-serif; font-size: 15px; line-height: 1.7; color: #5f6368; white-space: pre; border: none; text-align: left; direction: ltr;">' +
            structure +
            '</pre>' +
            
            // Bottom text
            '<p style="color: #5f6368; font-size: 15px; margin: 0 0 30px 0; text-align: left; direction: ltr;">Each folder will be created with a new unique ID.</p>' +
            
            // Buttons
            '<div style="display: flex; justify-content: flex-end; gap: 15px; direction: ltr;">' +
            '<button onclick="closeTemplateModal()" style="padding: 12px 32px; border: none; background: #e8eaed; border-radius: 4px; cursor: pointer; font-size: 15px; color: #202124; font-weight: 500;">Cancel</button>' +
            '<button onclick="createFromTemplate(' + collectionRef + ', \'' + templateType + '\')" style="padding: 12px 40px; border: 2px solid #1a73e8; background: #1a73e8; color: white; border-radius: 4px; cursor: pointer; font-size: 15px; font-weight: 500;">OK</button>' +
            '</div>' +
            
            '</div>' +
            '</div>';
        
        jQuery('body').append(modalHtml);
    }
    
    function closeTemplateModal() {
        jQuery('#templateModal').remove();
    }
    
    function createFromTemplate(collectionRef, templateType) {
        closeTemplateModal();
        
        // Show loading message
        jQuery('body').append('<div id="loadingModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 10001; display: flex; align-items: center; justify-content: center;"><div style="background: white; padding: 30px; border-radius: 8px; text-align: center;"><i class="fa fa-spinner fa-spin" style="font-size: 30px; color: #1a73e8; margin-bottom: 15px;"></i><p style="margin: 0; font-size: 16px; color: #202124;">Creating folders...</p></div></div>');
        
        // Call the API to create the structure
        jQuery.ajax({
            url: baseurl + '/pages/ajax/create_from_template.php',
            type: 'POST',
            data: {
                template_ref: collectionRef,
                template_type: templateType,
                ajax: 1
            },
            dataType: 'json',
            success: function(response) {
                jQuery('#loadingModal').remove();
                
                if (response.success) {
                    // Show success modal
                    var successHtml = '<div id="successModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 10000; display: flex; align-items: center; justify-content: center; font-family: Arial, sans-serif; direction: ltr;">' +
                        '<div style="background: white; border-radius: 8px; padding: 30px 35px; max-width: 400px; width: 90%; box-shadow: 0 8px 30px rgba(0,0,0,0.2); text-align: center;">' +
                        '<i class="fa fa-globe" style="font-size: 48px; color: #5f6368; margin-bottom: 20px;"></i>' +
                        '<h2 style="margin: 0 0 15px 0; font-size: 20px; color: #202124; font-weight: 400;">192.168.1.46</h2>' +
                        '<p style="color: #5f6368; font-size: 16px; margin: 0 0 25px 0;">Successfully created ' + response.created.length + ' folders.</p>' +
                        '<button onclick="jQuery(\'#successModal\').remove(); window.location.reload();" style="padding: 12px 40px; border: 2px solid #1a73e8; background: #1a73e8; color: white; border-radius: 4px; cursor: pointer; font-size: 15px; font-weight: 500;">OK</button>' +
                        '</div></div>';
                    jQuery('body').append(successHtml);
                } else {
                    // Show error modal
                    var errorHtml = '<div id="errorModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 10000; display: flex; align-items: center; justify-content: center; font-family: Arial, sans-serif; direction: ltr;">' +
                        '<div style="background: white; border-radius: 8px; padding: 30px 35px; max-width: 400px; width: 90%; box-shadow: 0 8px 30px rgba(0,0,0,0.2); text-align: left;">' +
                        '<i class="fa fa-globe" style="font-size: 28px; color: #5f6368; margin-bottom: 15px;"></i>' +
                        '<h2 style="margin: 0 0 15px 0; font-size: 20px; color: #202124; font-weight: 400;">192.168.1.46</h2>' +
                        '<p style="color: #5f6368; font-size: 16px; margin: 0 0 25px 0;">' + (response.error || 'Error creating folders. Please try again.') + '</p>' +
                        '<div style="text-align: right;"><button onclick="jQuery(\'#errorModal\').remove();" style="padding: 12px 40px; border: 2px solid #1a73e8; background: #1a73e8; color: white; border-radius: 4px; cursor: pointer; font-size: 15px; font-weight: 500;">OK</button></div>' +
                        '</div></div>';
                    jQuery('body').append(errorHtml);
                }
            },
            error: function(xhr, status, error) {
                jQuery('#loadingModal').remove();
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
                
                var errorMsg = 'Error creating folders. Please try again.';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        errorMsg = response.error;
                    }
                } catch(e) {
                    errorMsg = xhr.responseText || errorMsg;
                }
                
                var errorHtml = '<div id="errorModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 10000; display: flex; align-items: center; justify-content: center; font-family: Arial, sans-serif; direction: ltr;">' +
                    '<div style="background: white; border-radius: 8px; padding: 30px 35px; max-width: 400px; width: 90%; box-shadow: 0 8px 30px rgba(0,0,0,0.2); text-align: left;">' +
                    '<i class="fa fa-globe" style="font-size: 28px; color: #5f6368; margin-bottom: 15px;"></i>' +
                    '<h2 style="margin: 0 0 15px 0; font-size: 20px; color: #202124; font-weight: 400;">192.168.1.46</h2>' +
                    '<p style="color: #5f6368; font-size: 16px; margin: 0 0 25px 0;">' + errorMsg + '</p>' +
                    '<div style="text-align: right;"><button onclick="jQuery(\'#errorModal\').remove();" style="padding: 12px 40px; border: 2px solid #1a73e8; background: #1a73e8; color: white; border-radius: 4px; cursor: pointer; font-size: 15px; font-weight: 500;">OK</button></div>' +
                    '</div></div>';
                jQuery('body').append(errorHtml);
            }
        });
    }
</script>

<?php
include "../include/footer.php";