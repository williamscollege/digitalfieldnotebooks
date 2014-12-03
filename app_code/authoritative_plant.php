<?php
    require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('authoritative_plant','properize'));
//	require_once('../app_head.php');

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
    } else {
        if (isset($_REQUEST['authoritative_plant_id'])) {
            if ($_REQUEST['authoritative_plant_id'] == 'NEW') {
                $ap = Authoritative_Plant::createNewAuthoritativePlant($DB);
            } elseif (is_numeric($_REQUEST['authoritative_plant_id'])) {
                $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>$_REQUEST['authoritative_plant_id']],$DB);
                if (! $ap->matchesDb) {
                    util_redirectToAppPage('app_code/authoritative_plant.php?action=list','failure',util_lang('no_authoritative_plant_found'));
                }
            } else {
                util_redirectToAppPage('app_code/authoritative_plant.php?action=list','failure',util_lang('no_authoritative_plant_found'));
            }
        } else { // no authoritative_plant_id in request
            if ($action != 'list') { // list actions can have no id - all other actions need an id
                util_redirectToAppPage('app_code/authoritative_plant.php?action=list','failure',util_lang('no_authoritative_plant_specified'));
            }
            $ap = Authoritative_Plant::createNewAuthoritativePlant($DB);
        }
    }
//elseif (($action != 'list')
//              && isset($_REQUEST['authoritative_plant_id'])
//              && is_numeric($_REQUEST['authoritative_plant_id'])) {
//        $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>$_REQUEST['authoritative_plant_id']],$DB);
//    }

    if ((! $ap) || (! $ap->matchesDb)) {
        $action = 'list';
    }
//
    # 3. confirm that the user is allowed to take that action on that object (if not, redirect them to the home page with an appropriate warning)
    if (($action == 'list') && (! $USER->canActOnTarget($ACTIONS[$action],$ap))) {
//        util_prePrintR($USER);
//        exit;
        util_redirectToAppHome('failure',util_lang('no_permission'));
    } elseif (($action != 'list') && (! $USER->canActOnTarget($ACTIONS[$action],$ap))) {
        if ($action == 'view') {
            util_redirectToAppPage('app_code/authoritative_plant.php?action=list','failure',util_lang('no_permission'));
        }
        util_redirectToAppPage('app_code/authoritative_plant.php?action=view&authoritative_plant_id='.$_REQUEST['authoritative_plant_id'],'failure',util_lang('no_permission'));
    }

    if ($action != 'delete') {
        require_once('../app_head.php');
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
        echo '<ul id="list-of-authoritative-plants" class="all-authoritative-plants">'."\n";
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
        echo '<script src="'.APP_ROOT_PATH.'/js/plant_image_viewer.js"></script>'."\n";
//        echo 'TODO: implement view action';
    } else
    if (($action == 'edit') || ($action == 'create')) {
//        echo 'TODO: implement edit and create actions';
        echo $ap->renderAsEdit();
        echo '<script src="'.APP_ROOT_PATH.'/js/plant_image_viewer.js"></script>'."\n";
        echo '<script src="'.APP_ROOT_PATH.'/js/ordering_controls.js"></script>'."\n";
        echo '<script src="'.APP_ROOT_PATH.'/js/authoritative_plant_edit.js"></script>'."\n";

    } else
    if ($action == 'delete') {
        echo 'TODO: implement delete action';
    }
require_once('../foot.php');
?>