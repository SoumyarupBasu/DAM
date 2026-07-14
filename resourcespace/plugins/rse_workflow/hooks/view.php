<?php

function HookRse_workflowViewPageevaluation()
    {
    include_once __DIR__ . "/../include/rse_workflow_functions.php";
    global $lang;
    global $ref;
    global $resource;
    global $baseurl;
    global $search;
    global $offset;
    global $order_by;
    global $archive;
    global $sort;
    global $k;
    global $userref;
    # Retrieve list of existing defined actions 
    $workflowactions = rse_workflow_get_actions();
      
    foreach ($workflowactions as $workflowaction)
        {
        if(getval("rse_workflow_action_" . $workflowaction["ref"],"")!="" && enforcePostRequest(false))
            {
            // Check if resource status has already been changed between form being loaded and submitted
            $resource_status_check_name = "resource_status_check_" . $workflowaction["ref"];
            $resource_status_check = getval($resource_status_check_name,"");
            if($resource_status_check != "" && $resource_status_check != $resource["archive"])
                {
                $errors["status"] = $lang["status"] . ': ' . $lang["save-conflict-error"];
                echo "<div class=\"PageInformal\">" . $lang["error"] . ": " . $lang["status"] . " - " . $lang["save-conflict-error"] . "</div>";
                }
            else
                {
                $validstates = explode(',', $workflowaction['statusfrom']);
                $edit_access = get_edit_access($ref,$resource['archive'], $resource);
    
                if('' != $k || ($resource["lock_user"] > 0 && $resource["lock_user"] != $userref))
                    {
                    $edit_access = 0;
                    }
                    
                if(
                    in_array($resource['archive'], $validstates)
                    && (
                            (
                                $edit_access
                                && checkperm("e{$workflowaction['statusto']}")
                            )
                            || checkperm("wf{$workflowaction['ref']}")
                       )
                    )
                    {
                    // Check whether More notes are present
                    $more_notes_text = getval("more_workflow_action_" . $workflowaction["ref"],"");

                    // Prevent workflow state change if required metadata fields are empty.
                    $result = update_archive_required_fields_check($ref, $workflowaction["statusto"]);
                    if (is_array($result) && count($result) > 0) {
                        echo "<div class=\"PageInformal\">" . escape(str_replace(array('%%ARCHIVE%%', '%%FIELDS%%'), array($lang["status" . $workflowaction["statusto"]], implode(', ', array_column($result, 'title'))), $lang['rse_workflow_state_change_failed_required_fields'])) . "</div>";
                        return;
                    }

                    update_archive_status($ref, $workflowaction["statusto"], $resource["archive"], 0, $more_notes_text);

                    hook("rse_wf_archivechange","",array($ref,$resource["archive"],$workflowaction["statusto"]));
                                                
                    if (checkperm("z" . $workflowaction["statusto"]))
                        {
                        ?>
                        <script type="text/javascript">
                        styledalert('<?php echo escape($lang["success"]); ?>','<?php echo escape($lang["rse_workflow_saved"]) . "&nbsp;" . escape($lang["status" . $workflowaction["statusto"]]);?>');
                        if(jQuery("#modal").is(":visible"))
                            {
                            ModalClose();
                            }
                        else
                            {
                            window.setTimeout(function(){CentralSpaceLoad(baseurl_short);},1000);
                            }
                        </script>
                        <?php
                        exit();
                        }
                    else
                        { 
                        echo "<div class=\"PageInformal\">" . $lang["rse_workflow_saved"] . " " . $lang["status" . $workflowaction["statusto"]] . "</div>";
                        $resource["archive"]=$workflowaction["statusto"];
                        }
                    } 
                }
            }
        }
    }

function HookRse_workflowViewAdditionaldownloadtabs()
    {
    include_once __DIR__ . "/../include/rse_workflow_functions.php";

    global $lang, $ref, $resource, $baseurl_short, $search, $offset, $order_by, $archive, $sort, $edit_access, $curpos,
           $userref, $k, $internal_share_access,$modal;

    if(!empty($resource["lock_user"]) && $resource["lock_user"] != 0 && $resource["lock_user"] != $userref)
        {
        return false;
        }

    if($k != "" && $internal_share_access === false)
        {
        return false;
        }

    $validactions = rse_workflow_get_valid_actions(rse_workflow_get_actions(), false);

    if(count($validactions)>0)
        {?>
        <div class="RecordDownloadSpace" id="ResourceWorkflowActions" style="display:none;">
        <p><?php echo escape($lang['rse_workflow_user_info']); ?></p>
        <script type="text/javascript">
        function open_notes(action_ref) {
            var workflow_action = jQuery('#rse_workflow_action_' + action_ref);
            var more_link = jQuery('#more_link_' + action_ref);

            // Populate textarea with any text there may already be present
            var more_text_hidden = jQuery('#more_workflow_action_' + action_ref).val();

            more_link.after('<textarea id="more_for_workflow_action_' + action_ref 
                + '" name="more_for_workflow_action_' + action_ref 
                + '" style="width: 100%; resize: none;" rows="6">' + more_text_hidden + '</textarea>');
            more_link.after('<p id="notes_for_workflow_action_' + action_ref + '"><?php echo escape($lang["rse_workflow_more_notes_title"]); ?></p>');

            more_link.text('<?php echo escape($lang["rse_workflow_link_close"]); ?>');
            more_link.attr('onClick', 'close_notes(' + action_ref + ');');

            // Bind the input textarea 'more_for_workflow_action' value to the hidden 'more_workflow_action' field
            jQuery('#more_for_workflow_action_' + action_ref).keyup(function (event) {
                var notes = this.value;
                jQuery('#more_workflow_action_' + action_ref).val(notes);
            });
        }

        function close_notes(action_ref) {

            var more_link = jQuery('#more_link_' + action_ref);
            var notes_title = jQuery('#notes_for_workflow_action_' + action_ref);
            var notes_textarea = jQuery('#more_for_workflow_action_' + action_ref);

            // Remove Notes title and textarea from DOM:
            notes_title.remove();
            notes_textarea.remove();

            more_link.text('<?php echo escape($lang["rse_workflow_link_open"]); ?>');
            more_link.attr('onClick', 'open_notes(' + action_ref + ');');

        }
        </script>
        <style>
        #rse_workflow_confirm_overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.45);
            z-index: 99999;
            align-items: center;
            justify-content: center;
        }
        #rse_workflow_confirm_overlay.active { display: flex; }
        #rse_workflow_confirm_box {
            background: #fff;
            border-radius: 8px;
            padding: 28px 32px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
            text-align: center;
            font-family: Arial, sans-serif;
        }
        #rse_workflow_confirm_box h3 {
            margin: 0 0 10px;
            font-size: 18px;
            color: #222;
        }
        #rse_workflow_confirm_box p {
            margin: 0 0 22px;
            color: #555;
            font-size: 14px;
        }
        #rse_workflow_confirm_box .wf-confirm-btns {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        #rse_workflow_confirm_box .wf-btn-confirm {
            background: #1679c0;
            color: #fff;
            border: 1px solid transparent;
            padding: 9px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.25);
        }
        #rse_workflow_confirm_box .wf-btn-confirm:hover {
            filter: brightness(1.08);
        }
        #rse_workflow_confirm_box .wf-btn-cancel {
            background: #e0e0e0;
            color: #333;
            border: none;
            padding: 9px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        </style>

        <!-- Confirmation overlay -->
        <div id="rse_workflow_confirm_overlay">
            <div id="rse_workflow_confirm_box">
                <h3 id="rse_wf_confirm_title"></h3>
                <p id="rse_wf_confirm_msg"></p>
                <div class="wf-confirm-btns">
                    <button class="wf-btn-confirm" id="rse_wf_confirm_yes">Confirm</button>
                    <button class="wf-btn-cancel" onclick="rseWfCloseConfirm()">Cancel</button>
                </div>
            </div>
        </div>

        <script>
        var rseWfPendingForm = null;
        var rseWfPendingModal = false;

        function rseWfShowConfirm(formId, isModal, actionLabel) {
            // Check if comment box has text OR at least one comment already exists
            var commentBox = document.getElementById('comment_form_body');
            var hasTypedComment = commentBox && commentBox.value.trim() !== '';
            var hasExistingComment = jQuery('#CommentsContainer .CommentBody').length > 0;

            if (!hasTypedComment && !hasExistingComment) {
                // Highlight the comment box
                if (commentBox) {
                    commentBox.style.border = '2px solid #c0392b';
                    commentBox.focus();
                    commentBox.addEventListener('input', function() {
                        commentBox.style.border = '';
                        var errEl = document.getElementById('rse_wf_comment_error');
                        if (errEl) errEl.remove();
                    }, { once: true });
                }
                // Show error message
                var errEl = document.getElementById('rse_wf_comment_error');
                if (!errEl) {
                    errEl = document.createElement('p');
                    errEl.id = 'rse_wf_comment_error';
                    errEl.style.cssText = 'color:#c0392b;font-size:13px;margin:6px 0 0;text-align:center;';
                    errEl.textContent = 'Please add a comment before proceeding.';
                    if (commentBox) {
                        commentBox.parentNode.insertBefore(errEl, commentBox.nextSibling);
                    }
                }
                // Scroll to comment section
                if (commentBox) {
                    commentBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }

            rseWfPendingForm = document.getElementById(formId);
            rseWfPendingModal = isModal;
            document.getElementById('rse_wf_confirm_title').textContent = 'Confirm Action';
            document.getElementById('rse_wf_confirm_msg').textContent = 'Are you sure you want to "' + actionLabel + '" this resource?';
            document.getElementById('rse_workflow_confirm_overlay').classList.add('active');
            document.getElementById('rse_wf_confirm_yes').onclick = function() {
                var formToSubmit = rseWfPendingForm;
                var isModalSubmit = rseWfPendingModal;
                rseWfCloseConfirm();
                if (isModalSubmit) {
                    ModalPost(formToSubmit, true);
                } else {
                    CentralSpacePost(formToSubmit, true);
                }
            };
            return false;
        }

        function rseWfCloseConfirm() {
            document.getElementById('rse_workflow_confirm_overlay').classList.remove('active');
            rseWfPendingForm = null;
        }

        // Close on overlay click
        document.getElementById('rse_workflow_confirm_overlay').addEventListener('click', function(e) {
            if (e.target === this) { rseWfCloseConfirm(); }
        });
        </script>

        <table cellpadding="0" cellspacing="0" id="ResourceWorkflowTable">
            <tbody>
            <?php
         
        foreach($validactions as $validaction)
            {
                $show_more_link = false;
                if(!empty($validaction['more_notes_flag']) && $validaction['more_notes_flag'] == 1) {
                    $show_more_link = true;
                }
                $action_label = escape(i18n_get_translated($validaction["buttontext"],"workflow-actions"));
                $is_reject = ($validaction['statusto'] == 2);
                $btn_class = $is_reject ? ' wf-btn-reject' : '';
            ?>
             <tr class="DownloadDBlend">
                <td><?php echo escape(i18n_get_translated($validaction["text"],"workflow-actions")); if($show_more_link) { ?><a href="#" id="more_link_<?php echo $validaction["ref"]; ?>" onClick="open_notes(<?php echo $validaction["ref"]; ?>);" style="float: right;"><?php echo escape($lang['rse_workflow_link_open']); ?></a><?php } ?></td>
                <td>
                    <form action="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref)?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset)?>&order_by=<?php echo urlencode($order_by)?>&sort=<?php echo urlencode($sort)?>&archive=<?php echo urlencode($archive)?>&curpos=<?php echo urlencode($curpos)?>&workflowaction=<?php echo urlencode($validaction["ref"])?>" 
                          id="resource_<?php echo $ref; ?>_workflowaction<?php echo $validaction['ref']; ?>">
                    <input id='resource_status_checksum_<?php echo $validaction["ref"]; ?>' name='resource_status_check_<?php echo $validaction["ref"]; ?>' type='hidden' value='<?php echo $resource["archive"]; ?>'>
                    <?php
                if(isset($modal) && $modal=="true")
                    {
                    ?>
                    <input type="hidden" name="modal" id="rse_workflow_modal_<?php echo $validaction["ref"]; ?>" value="true" >
                    <?php
                    }
                    ?>
                    <input type="hidden" name="rse_workflow_action_<?php echo $validaction["ref"]; ?>" id="rse_workflow_action_<?php echo $validaction["ref"]; ?>" value="true" >
                    <input type="hidden" name="more_workflow_action_<?php echo $validaction["ref"]; ?>" id="more_workflow_action_<?php echo $validaction["ref"]; ?>" value="" >       
                    <input type="submit" name="rse_workflow_action_submit_<?php echo $validaction["ref"]; ?>" id="rse_workflow_action_submit_<?php echo $validaction["ref"]; ?>" class="<?php echo $btn_class; ?>" value="&nbsp;<?php echo $action_label; ?>&nbsp;" onClick="return rseWfShowConfirm('resource_<?php echo $ref; ?>_workflowaction<?php echo $validaction['ref']; ?>', <?php echo $modal ? 'true' : 'false'; ?>, '<?php echo $action_label; ?>');" >
                    <?php
                    generateFormToken("resource_{$ref}_workflowaction{$validaction['ref']}");
                    hook("rse_wf_formend","",array($resource["archive"],$validaction["statusto"]));
                    ?>
                </form>
                </td>
            </tr>                               
            
            
            
            <?php
            }?>
        </tbody></table>
        </div><!-- End of RecordDownloadSpace-->
        <?php
        }
    }
    
function HookRse_workflowViewAdditionaldownloadtabbuttons()
    {
    global $lang, $modal;

    $validactions = rse_workflow_get_valid_actions(rse_workflow_get_actions(), false);

    if (count($validactions) > 0)
        {
        ?>
        <div class="Tab" id="ResourceWorkflowActionsButton">
            <a href="#" onclick="selectDownloadTab('ResourceWorkflowActions',<?php echo $modal ? 'true' : 'false'; ?>);">
                <?php echo escape($lang["rse_workflow_actions_heading"]) ?>
            </a>
        </div>
        <?php
        }
    }

function HookRse_workflowViewReplacetitleprefix($state)
    {
    global $lang,$additional_archive_states;

    if ($state<=3) {return false;} # For custom states only.

    $name=ps_value("SELECT name value FROM archive_states WHERE code = ?",["i",$state],"");
    
    ?><span class="ResourceTitleWorkflow<?php echo $state ?>"><?php echo i18n_get_translated($name) ?>:</span>&nbsp;<?php
    return true;
    }
    
    
