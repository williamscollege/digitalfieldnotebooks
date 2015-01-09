<?php
	require_once('head_ajax.php');

    #############################
    # 1. figure out what action is being attempted (default is 'none')
    # 1.5 verify required data has been passed in
    # 2. confirm that the user is allowed to take that action on that object (if not, return approp msg)
    # 3. branch behavior based on the action
    #############################

    # 1. figure out what action is being attempted (none/default is view)
    $action = 'none';
    $results['which_action'] = $action;
    if (! isset($_REQUEST['action'])) {
        $results['note'] = util_lang('msg_missing_parameter').' : action';
        echo json_encode($results);
        exit;
    }

    $VIABLE_ACTIONS = ['image_upload','delete','reorder'];

    $action = $_REQUEST['action'];
    $results['which_action'] = $action;
//    if ($action != 'image_upload') {
    if (! in_array($action,$VIABLE_ACTIONS)) {
        $results['note'] = util_lang('not_supported');
        echo json_encode($results);
        exit;
    }
    $results['which_action'] = $action;

    # 1.5 verify required params passed in (for_specimen)
    $specimenId = '';
    $specimen = '';
    $specimenImageId = '';
    if (($action == 'image_upload') || ($action == 'reorder')) {
        if (isset($_REQUEST['for_specimen'])) {
            $specimenId = $_REQUEST['for_specimen'];
            if (! is_numeric($specimenId)) {
                $results['note'] = 'parameter "for_specimen": '.util_lang('invalid_value');
                echo json_encode($results);
                exit;
            }
        }
        if (! $specimenId) {
            $results['note'] = util_lang('msg_missing_parameter').' : for_specimen';
            echo json_encode($results);
            exit;
        }
    } elseif ($action == 'delete') {
        if (isset($_REQUEST['specimen_image_id'])) {
            $specimenImageId = $_REQUEST['specimen_image_id'];
            if (! is_numeric($specimenImageId)) {
                $results['note'] = 'parameter "specimen_image_id": '.util_lang('invalid_value');
                echo json_encode($results);
                exit;
            }
        }
        if (! $specimenImageId) {
            $results['note'] = util_lang('msg_missing_parameter').' : specimen_image_id';
            echo json_encode($results);
            exit;
        }
    }

    $specimenImage = '';
    if ($specimenImageId) {
        $specimenImage = Specimen_Image::getOneFromDb(['specimen_image_id'=>$specimenImageId],$DB);
        if (! $specimenImage->matchesDb) {
            $results['note'] = util_lang('msg_record_missing').' : '.$specimenImageId;
            echo json_encode($results);
            exit;
        }
    }

    # 2. confirm that the user is allowed to take that action
    // global specimen create
    // additional permission checks are handled at the save point for notebook pages and authoritative plants respectively
    $has_permission = $USER->flag_is_system_admin;
    if (! $has_permission) {
        $USER->cacheRoleActionTargets();

        if ($action == 'image_upload') {
            // check global specimen perms (indiv specimen perms are only for editing, not creating, as indiv perms require a specific object ID as a target for the permission)
            if (in_array('global_specimen',array_keys($USER->cached_role_action_targets_hash_by_target_type_by_id))) {
                foreach ($USER->cached_role_action_targets_hash_by_target_type_by_id['global_specimen'] as $glob_rat) {
                    if ($glob_rat->action_id == $ACTIONS['create']->action_id) {
                        $has_permission = true;
                        break;
                    }
                }
            }
        } elseif ($action == 'delete') {
            $has_permission = $USER->canActOnTarget($ACTIONS['delete'],$specimenImage);
        } elseif ($action == 'reorder') {
            $specimen = Specimen::getOneFromDb(['specimen_id'=>$specimenId],$DB);
            $has_permission = $USER->canActOnTarget($ACTIONS['edit'],$specimen);
        }
    }

    if (! $has_permission) {
        $results['note'] = util_lang('no_permission');
        echo json_encode($results);
        exit;
    }


    # 3. branch behavior based on the action
    #      image_upload - process the uploaded image and return an approp. list item
    #      TODO: delete - delete the given specimen image
    #      TODO: save_ordering - set the ordering values as approp for all the given specimen images (perhaps handled at specimen level... probably here is best...?)

    if ($has_permission && ($action == 'image_upload')) {
        // attempt to save the file (end w/ approp error & note on failure)
//        util_prePrintR($_FILES);
//        exit;

        if (! isset($_FILES['upload_file'])) {
            $results['note'] = util_lang('msg_no_file_to_upload');
            echo json_encode($results);
            exit;
        }

        $file = $_FILES['upload_file'];
        $target_file_name = basename($file['name']);
        $target_file_name = preg_replace("/[^a-zA-Z0-9\\.]/", "_", $target_file_name);
        $file_reference = $specimenId.'/'.$target_file_name;
        $uploaddir = $_SERVER['DOCUMENT_ROOT'].APP_ROOT_PATH.'/image_data/specimen/'.$specimenId.'/';
        if (false) {
            $uploaddir = preg_replace("/\\//","\\",$uploaddir);
        }

        if (!file_exists($uploaddir)) {
            mkdir($uploaddir, 0777, true);
        }

//        error_log( print_R($file,TRUE) );
//        error_log($target_file_name);
//        error_log($file_reference);
//        error_log($uploaddir);


        if(! move_uploaded_file($file['tmp_name'], $uploaddir .$target_file_name)) {
            $results['note'] = util_lang('msg_file_upload_failed_copy_from_temp');
            echo json_encode($results);
            exit;
        }

        // get all the specimen images for the given specimen
        $otherImages = Specimen_Image::getAllFromDb(['specimen_id'=>$specimenId],$DB);

        // figure out the lowest ordering value - the ordering for the new image will be that - 1
        $new_spec_ordering = 0;
        foreach ($otherImages as $otherImage) {
            if ($otherImage->ordering <= $new_spec_ordering) {
                $new_spec_ordering = $otherImage->ordering - 1;
            }
        }

        // create a new specimen image object, linked to that specimen and with the approp ordering and file info
        $newSpecimenImage = Specimen_Image::createNewSpecimenImageForSpecimen($specimenId,$DB);
        $newSpecimenImage->ordering = $new_spec_ordering;
        $newSpecimenImage->image_reference = $file_reference;

        // save the new specimen image object (end w/ approp error & note on failure)
        $newSpecimenImage->updateDb();
        if (! $newSpecimenImage->matchesDb) {
            $results['note'] = util_lang('msg_database_update_failed');
            echo json_encode($results);
            exit;
        }

        // render the new specimen image as a list item for editing & return that
//        $results['html_output']  = '<li><div class="newly_uploaded_specimen_image">'."\n".'TODO: embed approp code here'."\n</div><li>";
        $results['html_output']  = $newSpecimenImage->renderAsListItemEdit();
        $results['status']       = 'success';
    }
    elseif ($has_permission && ($action == 'delete')) {
        $specimenImage->doDelete();
        $specimenImageDel = Specimen_Image::getOneFromDb(['specimen_image_id'=>$specimenImageId],$DB);
        if (! $specimenImageDel->matchesDb) {
            $results['status']       = 'success';
        } else {
            $results['note'] = util_lang('msg_delete_failed');
            echo json_encode($results);
            exit;
        }
    }
    elseif ($has_permission && ($action == 'reorder')) {

        // get the specimen
        if (! $specimen) {
            $specimen = Specimen::getOneFromDb(['specimen_id'=>$specimenId],$DB);
        }

        // get the image for it
        $specimen->loadImages();
        foreach ($specimen->images as $si) {
            $req_key = 'ordering_' . $si->specimen_image_id;
            if (array_key_exists($req_key,$_REQUEST) && is_numeric($_REQUEST[$req_key])) {
                $si->ordering = $_REQUEST[$req_key];
                $si->updateDb();
                if (! $si->matchesDb) {
                    $results['note'] = util_lang('msg_database_update_failed');
                    echo json_encode($results);
                    exit;
                }
            }
        }

        $results['status']       = 'success';
    }



        echo json_encode($results);
exit;
?>