<?php
	require_once('head_ajax.php');

    #############################
    # 1. figure out what action is being attempted (default is 'none')
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

    # 2. confirm that the user is allowed to take that action
    $has_permission = $USER->flag_is_system_admin;
    if (! $has_permission) {
        $USER->cacheRoleActionTargets();
        if (in_array('global_notebook',array_keys($USER->cached_role_action_targets_hash_by_target_type_by_id))) {
            foreach ($USER->cached_role_action_targets_hash_by_target_type_by_id['global_notebook'] as $glob_rat) {
                if (($glob_rat->action_id == $ACTIONS['create']->action_id)
                    || ($glob_rat->action_id == $ACTIONS['edit']->action_id)) {
                    $has_permission = true;
                }
            }
        }
    }

    if (! $has_permission) {
        $results['note'] = util_lang('no_permission');
    }


    # 3. branch behavior based on the action
    #      create - return an appropriate form field set

    if ($has_permission && ($action == 'create')) {
        $unique_str = $_REQUEST['unique'];
        if (! preg_match('/^[A-Z0-9]+$/i',$unique_str)) {
            $results['note'] = 'parameter "unique": '.util_lang('invalid_string_base_chars_only');
        } else {
            $results['html_output']  = Notebook_Page_Field::renderFormInteriorForNewNotebookPageField($unique_str);
            $results['status']       = 'success';
        }
    }

echo json_encode($results);
exit;
?>