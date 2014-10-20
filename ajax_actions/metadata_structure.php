<?php
	require_once('head_ajax.php');

    #############################
    # 1. figure out what action is being attempted (default is 'none')
    # 2. confirm that the user is allowed to take that action on that object (if not, return approp msg)
    # 3. branch behavior based on the action
    #############################

    # 1. figure out what action is being attempted (none/default is view)
    $local_actions = ['value_options'];

    $action = 'none';
    if (isset($_REQUEST['action']) && (in_array($_REQUEST['action'],$local_actions) || (in_array($_REQUEST['action'],Action::$VALID_ACTIONS)))) {
        $action = $_REQUEST['action'];
    }

    if ($action != 'value_options') {
        $results['note'] = util_lang('not_supported');
    }

    $results['which_action'] = 'value_options';

    # 2. confirm that the user is allowed to take that action
    $has_permission = $USER->flag_is_system_admin;
    if (! $has_permission) {
        $USER->cacheRoleActionTargets();
        if (in_array('global_metadata',array_keys($USER->cached_role_action_targets_hash_by_target_type_by_id))) {
            foreach ($USER->cached_role_action_targets_hash_by_target_type_by_id['global_metadata'] as $glob_rat) {
                if ($glob_rat->action_id == $ACTIONS['view']->action_id) {
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

    if ($has_permission && ($action == 'value_options')) {
        $target_mds_id = 0;
        if (isset($_REQUEST['metadata_structure_id'])) {
            $target_mds_id = $_REQUEST['metadata_structure_id'];
        }
        if (! $target_mds_id) {
            $results['note'] = util_lang('msg_missing_metadata_structure');
        } else {
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>$target_mds_id],$DB);
            if (! $mds->matchesDb) {
                $results['note'] = util_lang('msg_record_missing');
            } else {
                $mds->loadTermSetAndValues();
                if (! $mds->term_set) {
                    $results['html_output']  = '  <option value="-1">'.util_lang('metadata_structure_has_no_term_set').'</option>'."\n";
                } else {
                    $results['html_output']  = '  <option value="-1">'.util_lang('prompt_select').'</option>'."\n" . $mds->term_set->renderValuesAsOptions();
                }
                $results['status']       = 'success';
            }
        }
    }

echo json_encode($results);
exit;
?>