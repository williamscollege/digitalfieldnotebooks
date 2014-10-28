<?php
    require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('metadata_term_set'));
	require_once('../app_head.php');

    #############################
    # 1. figure out what action is being attempted (none/default is view)
    # 2. figure out which metadata term set is being acted on (if none specified then list all visible)
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

    # 2. figure out which metadata term set is being acted on (if none specified then list all visible)
    $mdts = '';
    if ($action == 'create') {
        $mdts = new Metadata_Term_Set(['name'=>util_lang('new_metadata_term_set').' '.util_currentDateTimeString(),'DB'=>$DB]);
    } elseif (($action != 'list')
              && isset($_REQUEST['metadata_term_set_id'])
              && is_numeric($_REQUEST['metadata_term_set_id'])) {
        $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id'=>$_REQUEST['metadata_term_set_id']],$DB);
    }

    if ((! $mdts) || (! $mdts->matchesDb)) {
        $action = 'list';
    }

    # 3. confirm that the user is allowed to take that action on that object (if not, redirect them to the home page with an appropriate warning)
    if (($action != 'list') && (! $USER->canActOnTarget($ACTIONS[$action],$mdts))) {
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
        echo '<h2>'.util_lang('all_metadata_term_sets','properize').'</h2>'."\n";
        $all_metadata_term_sets = Metadata_Term_Set::getAllFromDb([],$DB);
        echo '<ul class="all-metadata-term-sets">'."\n";
        foreach ($all_metadata_term_sets as $a_mdts) {
            if ($USER->canActOnTarget($ACTIONS['view'],$a_mdts)) {
                echo $a_mdts->renderAsListItem();
            }
        }
        echo '</ul>'."\n";
    }

    if ($action == 'view') {
        if ($USER->canActOnTarget($ACTIONS['edit'],$mdts)) {
            echo '<div id="actions">'.$mdts->renderAsButtonEdit().'</div>'."\n";
        }
        echo $mdts->renderAsView();
    } else
    if (($action == 'edit') || ($action == 'create')) {
        echo 'TODO: implement edit and create actions';
    } else
    if ($action == 'delete') {
        echo 'TODO: implement delete action';
    }
require_once('../foot.php');
?>