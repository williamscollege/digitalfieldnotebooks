<?php
    require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('authoritative_plant'));
	require_once('../app_head.php');

    #############################
    # 1. figure out what action is being attempted (none/default is list)
    # 2. figure out which authoritative plant is being acted on (if none specified then list all visible)
    # 3. confirm that the user is allowed to take that action on that object (if not then list all visible with an appropriate warning)
    # 4. branch behavior based on the action
    #############################

    # 1. figure out what action is being attempted (none/default is list)
    $action = 'view';
//    if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'list')) {
//        $action = 'list';
//    } else
    if (isset($_REQUEST['action']) && in_array($_REQUEST['action'],Action::$VALID_ACTIONS)) {
        $action = $_REQUEST['action'];
    }

    # 2. figure out which authoritative plant is being acted on (if none specified then list all visible)
    $ap = '';
    if ($action == 'create') {
////        $mds = new Metadata_Structure(['name'=>util_lang('new_metadata_structure').' '.util_currentDateTimeString(),'DB'=>$DB]);
////        if ($_REQUEST['parent_metadata_structure_id'] && is_numeric($_REQUEST['parent_metadata_structure_id'])) {
////            $parent = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>$_REQUEST['parent_metadata_structure_id']],$DB);
////            if ($parent->matchesDb) {
////                $mds->parent_metadata_structure_id = $parent->metadata_structure_id;
////            }
////        }
        echo "TODO: create action support";
        exit;
    } elseif (($action != 'list')
              && isset($_REQUEST['authoritative_plant_id'])
              && is_numeric($_REQUEST['authoritative_plant_id'])) {
        $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>$_REQUEST['authoritative_plant_id']],$DB);
    }

    if ((! $ap) || (! $ap->matchesDb)) {
        $action = 'list';
    }
//
    # 3. confirm that the user is allowed to take that action on that object (if not, redirect them to the home page with an appropriate warning)
    if (($action != 'list') && (! $USER->canActOnTarget($ACTIONS[$action],$ap))) {
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
        echo '<h2>'.ucfirst(util_lang('authoritative_plants')).'</h2>'."\n";
        $all_ap = Authoritative_Plant::getAllFromDb(['flag_delete'=>false],$DB);
        echo '<ul class="">'."\n";
        foreach ($all_ap as $ap) {
            if ($USER->canActOnTarget($ACTIONS['view'],$ap)) {
                echo $ap->renderAsListItem();
            }
        }
        echo '</ul>'."\n";
//        echo 'TODO: implement list action';
    }

    if ($action == 'view') {
        if ($USER->canActOnTarget($ACTIONS['edit'],$ap)) {
            echo '<div id="actions">'.$ap->renderAsButtonEdit().'</div>'."\n";
        }
        echo $ap->renderAsView();
//        echo 'TODO: implement view action';
    } else
    if (($action == 'edit') || ($action == 'create')) {
        echo 'TODO: implement edit and create actions';
    } else
    if ($action == 'delete') {
        echo 'TODO: implement delete action';
    }
require_once('../foot.php');
?>