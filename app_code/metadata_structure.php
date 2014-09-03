<?php
    require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('metadata'));
	require_once('../app_head.php');

    #############################
    # 1. figure out what action is being attempted (none/default is view)
    # 2. figure out which metadata structure is being acted on (if none specified then list all visible)
    # 3. confirm that the user is allowed to take that action on that object (if not then list all visible with an appropriate warning)
    # 4. branch behavior based on the action
    #############################

    # 1. figure out what action is being attempted (none/default is view)
    $action = 'view';
    if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'list')) {
        $action = 'list';
    } elseif (isset($_REQUEST['action']) && in_array($_REQUEST['action'],Action::$VALID_ACTIONS)) {
        $action = $_REQUEST['action'];
    }

    # 2. figure out which metadata structure is being acted on (if none specified then list all visible)
    $mds = '';
    if ($action == 'create') {
        $mds = new Metadata_Structure(['name'=>util_lang('new_metadata_structure').' '.util_currentDateTimeString(),'DB'=>$DB]);
        if ($_REQUEST['parent_metadata_structure_id'] && is_numeric($_REQUEST['parent_metadata_structure_id'])) {
            $parent = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>$_REQUEST['parent_metadata_structure_id']],$DB);
            if ($parent->matchesDb) {
                $mds->parent_metadata_structure_id = $parent->metadata_structure_id;
            }
        }
    } elseif (($action != 'list')
              && isset($_REQUEST['metadata_structure_id'])
              && is_numeric($_REQUEST['metadata_structure_id'])) {
        $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>$_REQUEST['metadata_structure_id']],$DB);
    }

    if ((! $mds) || (! $mds->matchesDb)) {
        $action = 'list';
    }

    # 3. confirm that the user is allowed to take that action on that object (if not, redirect them to the home page with an appropriate warning)
    if (($action != 'list') && (! $USER->canActOnTarget($ACTIONS[$action],$mds))) {
        $action = 'list';
    }


    # 4. branch behavior based on the action
    #      update - update the object with the data coming in, then show the object (w/ 'saved' message)
    #      verify/publish - set the appropriate flag (true or false, depending on data coming in), then show the object (w/ 'saved' message)
    #      *list* - not a standard action; show a list (tree) of all metadata to which the user has view access
    #      view - show the object
    #      create/edit - show a form with the object's current values ($action is 'update' on form submit)
    #      delete - delete the metadata_structure, then go to list w/ 'deleted' message

    if (($action == 'update') || ($action == 'verify') || ($action == 'publish')) {
        echo 'TODO: implement update, verify, and publish actions';
        $action = 'view';
    }

    if ($action == 'list') {
        echo '<h2>'.util_lang('all_metadata').'</h2>'."\n";
        $all_metadata_structures = Metadata_Structure::getAllFromDb(['parent_metadata_structure_id'=>0],$DB);
        echo '<ul class="">'."\n";
        foreach ($all_metadata_structures as $a_mds) {
            if ($USER->canActOnTarget($ACTIONS['view'],$a_mds)) {
                echo $a_mds->renderAsListTree();
            }
        }
        echo '</ul>'."\n";
    }

    if ($action == 'view') {
        if ($USER->canActOnTarget($ACTIONS['edit'],$mds)) {
            echo '<div id="actions">'.$mds->renderAsButtonEdit().'</div>'."\n";
        }
        echo $mds->renderAsView();
    } else
    if (($action == 'edit') || ($action == 'create')) {
        echo 'TODO: implement edit and create actions';
    } else
    if ($action == 'delete') {
        echo 'TODO: implement delete action';
    }
require_once('../foot.php');
?>