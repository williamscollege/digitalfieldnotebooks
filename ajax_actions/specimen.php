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
    if (isset($_REQUEST['action']) && in_array($_REQUEST['action'],Action::$VALID_ACTIONS)) {
        $action = $_REQUEST['action'];
    }

    if ($action != 'create') {
        $results['note'] = util_lang('not_supported');
    }

    $results['which_action'] = 'create';

    # 1.5 verify required params passed in (unique)
    $unique_str = '';
    if (isset($_REQUEST['unique'])) {
        $unique_str = $_REQUEST['unique'];
        if (! preg_match('/^[A-Z0-9]+$/i',$unique_str)) {
            $results['note'] = 'parameter "unique": '.util_lang('invalid_string_base_chars_only');
            echo json_encode($results);
            exit;
        }
    }
    if (! $unique_str) {
        $results['note'] = util_lang('msg_missing_parameter').' : unique';
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

    if ($has_permission && ($action == 'create')) {
        $results['html_output']  = Specimen::renderFormInteriorForNewSpecimen($unique_str,$DB);
        $results['status']       = 'success';
    }

echo json_encode($results);
exit;
?>