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

    $action = $_REQUEST['action'];
    $results['which_action'] = $action;
    if ($action != 'image_upload') {
        $results['note'] = util_lang('not_supported');
        echo json_encode($results);
        exit;
    }

    # 1.5 verify required params passed in (for_specimen)
    $for_specimen = '';
    if (isset($_REQUEST['for_specimen'])) {
        $for_specimen = $_REQUEST['for_specimen'];
        if (! is_numeric($for_specimen)) {
            $results['note'] = 'parameter "for_specimen": '.util_lang('invalid_value');
            echo json_encode($results);
            exit;
        }
    }
    if (! $for_specimen) {
        $results['note'] = util_lang('msg_missing_parameter').' : for_specimen';
        echo json_encode($results);
        exit;
    }


    # 2. confirm that the user is allowed to take that action
    // global specimen create
    // additional permission checks are handled at the save point for notebook pages and authoritative plants respectively
    $has_permission = $USER->flag_is_system_admin;
    if (! $has_permission) {
        $USER->cacheRoleActionTargets();

        // check global specimen perms (indiv specimen perms are only for editing, not creating, as indiv perms require a specific object ID as a target for the permission)
        if (in_array('global_specimen',array_keys($USER->cached_role_action_targets_hash_by_target_type_by_id))) {
            foreach ($USER->cached_role_action_targets_hash_by_target_type_by_id['global_specimen'] as $glob_rat) {
                if ($glob_rat->action_id == $ACTIONS['create']->action_id) {
                    $has_permission = true;
                    break;
                }
            }
        }
    }

    if (! $has_permission) {
        $results['note'] = util_lang('no_permission');
        echo json_encode($results);
        exit;
    }


    # 3. branch behavior based on the action
    #      create - return an appropriate form field set

    if ($has_permission && ($action == 'image_upload')) {
        $results['html_output']  = '<li><div class="newly_uploaded_specimen_image">'."\n".'TODO: embed approp code here'."\n</div><li>";
        $results['status']       = 'success';
    }

echo json_encode($results);
exit;
?>