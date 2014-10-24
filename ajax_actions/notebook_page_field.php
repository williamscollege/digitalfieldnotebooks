<?php
	require_once('head_ajax.php');

    #############################
    # 1. figure out what action is being attempted (default is 'none')
    # 1.5 verify required params passed in
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
        echo json_encode($results);
        exit;
    }

    $results['which_action'] = 'create';


    # 1.5 verify required params passed in
    $notebook_page_id = 0;
    if (isset($_REQUEST['notebook_page_id']) && is_numeric($_REQUEST['notebook_page_id'])) {
        $notebook_page_id = $_REQUEST['notebook_page_id'];
    }
    if (! $notebook_page_id) {
        $results['note'] = util_lang('msg_missing_parameter').' : notebook_page_id';
        echo json_encode($results);
        exit;
    }

    # 2. confirm that the user is allowed to take that action
    $has_permission = $USER->flag_is_system_admin;
    if (! $has_permission) {
        $USER->cacheRoleActionTargets();

        // first check global notebook perms
        if (in_array('global_notebook',array_keys($USER->cached_role_action_targets_hash_by_target_type_by_id))) {
            foreach ($USER->cached_role_action_targets_hash_by_target_type_by_id['global_notebook'] as $glob_rat) {
                if (($glob_rat->action_id == $ACTIONS['create']->action_id)
                    || ($glob_rat->action_id == $ACTIONS['edit']->action_id)) {
                    $has_permission = true;
                    break;
                }
            }
        }

        // and if not that, then check specific perms
        if (! $has_permission) {
            $notebook_page = Notebook_Page::getOneFromDb(['notebook_page_id'=>$notebook_page_id],$DB);
            if (! $notebook_page->matchesDb) {
                $results['note'] = util_lang('msg_record_missing').' : notebook_page '.htmlentities($notebook_page_id);
                echo json_encode($results);
                exit;
            }
            $has_permission = $USER->canActOnTarget($ACTIONS['edit'],$notebook_page);
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