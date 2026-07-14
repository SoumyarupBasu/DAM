<?php

function HookEducational_content_organizerAllInitialise()
    {
    global $edu_fieldvars;
    config_register_core_fieldvars("Educational Content Organizer plugin", $edu_fieldvars);
    }

function HookEducational_content_organizerAllAfterresourcecreate($resource)
    {
    global $edu_subject_field, $edu_grade_field, $edu_state_field, $edu_content_type_field;
    global $edu_auto_assign_collections, $edu_collection_mapping;
    
    if (!$edu_auto_assign_collections)
        {
        return false;
        }
    
    // Get the metadata values for this resource
    $subject = get_data_by_field($resource, $edu_subject_field);
    $grade = get_data_by_field($resource, $edu_grade_field);
    $state = get_data_by_field($resource, $edu_state_field);
    $content_type = get_data_by_field($resource, $edu_content_type_field);
    
    debug("EDU_ORGANIZER: Resource {$resource} - Subject: {$subject}, Grade: {$grade}, State: {$state}, Content: {$content_type}");
    
    $collections_to_add = array();
    
    // Add to main subject collection
    if (!empty($subject) && isset($edu_collection_mapping[$subject]))
        {
        $collections_to_add[] = $edu_collection_mapping[$subject];
        debug("EDU_ORGANIZER: Adding to subject collection: " . $edu_collection_mapping[$subject]);
        }
    
    // Add to grade collection
    if (!empty($grade) && isset($edu_collection_mapping[$grade]))
        {
        $collections_to_add[] = $edu_collection_mapping[$grade];
        debug("EDU_ORGANIZER: Adding to grade collection: " . $edu_collection_mapping[$grade]);
        }
    
    // Add to state-specific collection (Grade + State combination)
    if (!empty($grade) && !empty($state))
        {
        $state_key = $grade . '_' . $state;
        if (isset($edu_collection_mapping[$state_key]))
            {
            $collections_to_add[] = $edu_collection_mapping[$state_key];
            debug("EDU_ORGANIZER: Adding to state collection: " . $edu_collection_mapping[$state_key]);
            }
        }
    
    // Add resource to all identified collections
    foreach ($collections_to_add as $collection_id)
        {
        add_resource_to_collection($resource, $collection_id);
        debug("EDU_ORGANIZER: Added resource {$resource} to collection {$collection_id}");
        }
    
    // Log the organization
    if (count($collections_to_add) > 0)
        {
        $collection_list = implode(', ', $collections_to_add);
        resource_log($resource, LOG_CODE_EDITED, 0, '', '', "Auto-organized into collections: {$collection_list}");
        }
    
    return true;
    }

function HookEducational_content_organizerAllAftersaveresourcedata($resource, $field)
    {
    global $edu_subject_field, $edu_grade_field, $edu_state_field, $edu_content_type_field;
    global $edu_auto_assign_collections;
    
    // Re-organize if any of the key fields were updated
    if ($edu_auto_assign_collections && in_array($field, array($edu_subject_field, $edu_grade_field, $edu_state_field, $edu_content_type_field)))
        {
        debug("EDU_ORGANIZER: Key field {$field} updated for resource {$resource}, re-organizing");
        
        // Remove from all educational collections first
        remove_resource_from_educational_collections($resource);
        
        // Re-add to appropriate collections
        HookEducational_content_organizerAllAfterresourcecreate($resource);
        }
    
    return true;
    }

function remove_resource_from_educational_collections($resource)
    {
    global $edu_collection_mapping;
    
    // Remove from all educational collections (IDs 1000-1999)
    foreach ($edu_collection_mapping as $collection_id)
        {
        remove_resource_from_collection($resource, $collection_id);
        }
    
    debug("EDU_ORGANIZER: Removed resource {$resource} from all educational collections");
    }