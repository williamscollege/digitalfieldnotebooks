<?php
    require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('page'));
	require_once('../app_head.php');

    #############################
    # 1. figure out what action is being attempted (none/default is view)
    # 2. figure out which notebook page is being acted on (if none specified then redirect to home page)
    # 3. confirm that the user is allowed to take that action on that object (if not, redirect them to the home page with an appropriate warning)
    # 4. branch behavior based on the action
    #############################

    # 1. figure out what action is being attempted (none/default is view)
    $action = 'view';
    if (isset($_REQUEST['action']) && in_array($_REQUEST['action'],Action::$VALID_ACTIONS)) {
        $action = $_REQUEST['action'];
    }

    # 2. figure out which notebook page is being acted on (if none specified then redirect to home page)
    $notebook_page = '';
    if ($action == 'create') {
        if ((! isset($_REQUEST['notebook_id'])) || (! is_numeric($_REQUEST['notebook_id']))) {
            util_redirectToAppPage('app_code/notebook.php?action=list','failure',util_lang('no_notebook_specified'));
        }
        $notebook_page = new Notebook_Page(['notebook_id' => $_REQUEST['notebook_id'],'DB'=>$DB]);
    } else {
        if ((! isset($_REQUEST['notebook_page_id'])) || (! is_numeric($_REQUEST['notebook_page_id']))) {
            util_redirectToAppPage('app_code/notebook.php?action=list','failure',util_lang('no_notebook_page_specified'));
        }
        $notebook_page = Notebook_Page::getOneFromDb(['notebook_page_id'=>$_REQUEST['notebook_page_id']],$DB);
        if (! $notebook_page->matchesDb) {
            util_redirectToAppPage('app_code/notebook.php?action=list','failure',util_lang('no_notebook_page_found'));
        }
    }

    # 3. confirm that the user is allowed to take that action on that object (if not, redirect them to the home page with an appropriate warning)
    if (! $USER->canActOnTarget($ACTIONS[$action],$notebook_page)) {
//        util_prePrintR("action is $action");
        if (($action != 'view') && isset($_REQUEST['notebook_page_id']) && is_numeric($_REQUEST['notebook_page_id'])) {
            util_redirectToAppPage('app_code/notebook_page.php?action=view&notebook_page_id='.$notebook_page->notebook_page_id,'failure',util_lang('no_permission'));
        }
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
        if ($USER->canActOnTarget($ACTIONS['edit'],$notebook_page)) {
            echo '<div id="actions">'.$notebook_page->renderAsButtonEdit().'</div>'."\n";
        }
        echo $notebook_page->renderAsView();
    } else
    if (($action == 'edit') || ($action == 'create')) {
        if ($USER->canActOnTarget($ACTIONS['edit'],$notebook_page)) {
            echo $notebook_page->renderAsEdit();
        }
        //echo 'TODO: implement edit and create actions';
    } else
    if ($action == 'delete') {
        echo 'TODO: implement delete action';
    }
require_once('../foot.php');
?>