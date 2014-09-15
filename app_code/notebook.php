<?php
    require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('notebook'));
	require_once('../app_head.php');

    #############################
    # 1. figure out what action is being attempted (none/default is view for a single notebook, list for none specified)
    # 2. figure out which notebook is being acted on (if none specified then redirect to home page for actions other than list)
    # 3. confirm that the user is allowed to take that action on that object (if not, redirect them to the home page with an appropriate warning)
    # 4. branch behavior based on the action
    #############################

    # 1. figure out what action is being attempted (none/default is view)
    $action = 'view';
    if (isset($_REQUEST['action']) && in_array($_REQUEST['action'],Action::$VALID_ACTIONS)) {
        $action = $_REQUEST['action'];
    }
    if (($action != 'list' ) && ((! isset($_REQUEST['notebook_id'])) || (! is_numeric($_REQUEST['notebook_id'])))) {
        util_redirectToAppPage('app_code/notebook.php?action=list','failure',util_lang('no_notebook_specified'));
    }

    # 2. figure out which notebook is being acted on (if none specified then redirect to home page for actions other than list)
    $notebook = '';
    $all_accessible_notebooks = '';
    if ($action == 'create') {
        $notebook = new Notebook(['user_id' => $USER->user_id, 'name'=>util_lang('new_notebook_title').' '.util_currentDateTimeString(),'DB'=>$DB]);
    } elseif ($action == 'list') {
        $all_accessible_notebooks = $USER->getAccessibleNotebooks($ACTIONS['view']);
        if (count($all_accessible_notebooks) < 1) {
            util_redirectToAppHome('failure',util_lang('no_notebooks_found'));
        }
        $notebook = $all_accessible_notebooks[0];
    } else {
//        if ((! isset($_REQUEST['notebook_id'])) || (! is_numeric($_REQUEST['notebook_id']))) {
////            util_redirectToAppHome('failure',util_lang('no_notebook_specified'));
//            util_redirectToAppPage('app_code/notebook.php?action=list','failure',util_lang('no_notebook_specified'));
//        }
        $notebook = Notebook::getOneFromDb(['notebook_id'=>$_REQUEST['notebook_id']],$DB);
        if (! $notebook->matchesDb) {
//            util_redirectToAppHome('failure',util_lang('no_notebook_found'));
            util_redirectToAppPage('app_code/notebook.php?action=list','failure',util_lang('no_notebook_found'));
        }
    }

    # 3. confirm that the user is allowed to take that action on that object (if not, redirect them to the home page with an appropriate warning)
    if (! $USER->canActOnTarget($ACTIONS[$action],$notebook)) {
//        util_redirectToAppHome('failure',util_lang('no_permission'));
        util_redirectToAppPage('app_code/notebook.php?action=list','failure',util_lang('no_permission'));
    }


    # 4. branch behavior based on the action
    #      update - update the object with the data coming in, then show the object (w/ 'saved' message)
    #      verify/publish - set the appropriate flag (true or false, depending on data coming in), then show the object (w/ 'saved' message)
    #      view - show the object
    #      create/edit - show a form with the object's current values ($action is 'update' on form submit)
    #      delete - delete the notebook, then go to home page w/ 'deleted' message

    if (($action == 'update') || ($action == 'verify') || ($action == 'publish')) {
        echo 'TODO: implement update, verify, and publish actions';
        $action = 'view';
    }

    if ($action == 'view') {
        if ($USER->canActOnTarget($ACTIONS['edit'],$notebook)) {
            echo '<div id="actions">'.$notebook->renderAsButtonEdit().'</div>'."\n";
        }
        echo $notebook->renderAsView();
    } elseif (($action == 'edit') || ($action == 'create')) {
        echo 'TODO: implement edit and create actions';
    } elseif ($action == 'delete') {
        echo 'TODO: implement delete action';
    } elseif ($action == 'list') {
        $counter = 0;
        $num_notebooks = count($all_accessible_notebooks);
        echo '<h2>'.ucfirst(util_lang('notebooks')).'</h2>';
        echo "<ul id=\"list-of-user-notebooks\" data-notebook-count=\"$num_notebooks\">\n";
        foreach ($all_accessible_notebooks as $notebook) {
            $counter++;
            echo $notebook->renderAsListItem('notebook-item-'.$counter)."\n";
        }
        echo "</ul>\n";
        if ($USER->canActOnTarget($ACTIONS['create'],new Notebook(['DB'=>$DB]))) {
            ?>
            <input type="button" id="btn-add-notebook" value="<?php echo util_lang('add_notebook'); ?>"/><?php
        }
    }
require_once('../foot.php');
?>