<?php
    require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('metadata'));

    #############################
    # 1. figure out what action is being attempted (none/default is view)
    # 2. figure out which metadata structure is being acted on (if none specified then list all visible)
    # 2.5 determine whether user has permissions to perform the action on the target
    # 3. confirm that the user is allowed to take that action on that object (if not then list all visible with an appropriate warning)
    # 4. branch behavior based on the action
    #############################

//util_prePrintR('#a#');

    # 1. figure out what action is being attempted (none/default is view)
    $action = 'view';
    if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'list')) {
        //util_prePrintR('#b#');
        $action = 'list';
    } elseif (isset($_REQUEST['action']) && in_array($_REQUEST['action'],Action::$VALID_ACTIONS)) {
        //util_prePrintR('#c#');
        $action = $_REQUEST['action'];
    }

//util_prePrintR('#d#');

    # 2. figure out which metadata structure is being acted on (if none specified then list all visible)
    $mds = '';
    if ($action == 'create') {
        //util_prePrintR('#e#');
//        $mds = new Metadata_Structure(['name'=>util_lang('new_metadata_structure').' '.util_currentDateTimeString(),'DB'=>$DB]);
        $mds = Metadata_Structure::createNewMetadataStructure(0,$DB);
        if (isset($_REQUEST['parent_metadata_structure_id']) && is_numeric($_REQUEST['parent_metadata_structure_id'])) {
            $parent = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>$_REQUEST['parent_metadata_structure_id']],$DB);
            if ($parent && $parent->matchesDb) {
                $mds->parent_metadata_structure_id = $parent->metadata_structure_id;
            }
        }
    } elseif (($action == 'update') && ($_REQUEST['metadata_structure_id'] == 'NEW'))  {
        //util_prePrintR('#f#');
        $mds = Metadata_Structure::createNewMetadataStructure(0,$DB);
    } elseif (($action != 'list')
              && isset($_REQUEST['metadata_structure_id'])
              && is_numeric($_REQUEST['metadata_structure_id'])) {
        //util_prePrintR('#g#');
        $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>$_REQUEST['metadata_structure_id']],$DB);
    }

//util_prePrintR('#h#');

    if ((! $mds) || (($mds->metadata_structure_id != 'NEW') && (! $mds->matchesDb))) {
        //util_prePrintR('#i#');
        $action = 'list';
    }

    # 3. confirm that the user is allowed to take that action on that object (if not, redirect them to the home page with an appropriate warning)
//util_prePrintR('#j#');
    if (($action != 'list') && (! $USER->canActOnTarget($ACTIONS[$action],$mds))) {
        //util_prePrintR('#k#');
        util_redirectToAppPage('app_code/metadata_structure.php?action=list','failure',util_lang('no_permission'));
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

    if ($action == 'update') {
        // standard fields (check 'parent_metadata_structure_id', 'name', 'ordering', 'description', 'details', 'metadata_term_set_id', 'flag_active')
        // if any changes, save
        $changed = false;
        $updateable_fields = ['parent_metadata_structure_id', 'name', 'description', 'details', 'metadata_term_set_id'];

//        util_prePrintR($_REQUEST);
//
//        util_prePrintR($mds);
        foreach ($updateable_fields as $fname) {
            if ((isset($_REQUEST[$fname])) && ($mds->fieldValues[$fname] != $_REQUEST[$fname])) {
                $changed = true;
                $mds->fieldValues[$fname] = $_REQUEST[$fname]; // NOTE: this seems dangerous, but the data is sanitized on the way back out
            }
        }

        // special handling of 'flag_active' since it's a checkbox
        $active_check_state = false;
        if (isset($_REQUEST['flag_active'])) {
            $active_check_state = true;
        }
        if ($mds->flag_active != $active_check_state) {
            $changed = true;
            $mds->flag_active = $active_check_state;
        }

//        util_prePrintR($_REQUEST);

        if ($changed) {
            $mds->matchesDb = false;
            $mds->updateDb();
        }

        // for each child structure (get children of current structure from DB), check if orig ordering == new orderings - if they differ, update the ordering for that child structure (fetch it, alter it, save it)
        $substructures = $mds->getChildren();

//        util_prePrintR($substructures);

        foreach ($substructures as $ss) {
            $req_key = 'new_ordering-item-metadata_structure_' . $ss->metadata_structure_id;
//            util_prePrintR('handling '.$req_key);
            if (isset($_REQUEST[$req_key]) && is_numeric($_REQUEST[$req_key])) {
                $new_ordering = $_REQUEST[$req_key];
                if ($new_ordering != $ss->ordering) {
                    $ss->ordering = $new_ordering;
                    $ss->updateDb();
                }
            }
        }

        $action = 'view';
    }

    if ($action == 'list') {
        echo '<h2>'.util_lang('all_metadata','properize').'</h2>'."\n";
        $all_metadata_structures = Metadata_Structure::getAllFromDb(['parent_metadata_structure_id'=>0],$DB);
        if ($USER->canActOnTarget($ACTIONS['create'],$all_metadata_structures[0])) {
            echo '<div id="actions"><a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=create&parent_metadata_structure_id=0" id="btn-add-metadata_structure-ROOT" class="creation_link btn" title="'.htmlentities(util_lang('add_metadata_structure')).'">'.htmlentities(util_lang('add_metadata_structure')).'</a></div>'."\n";
        }
        echo '<ul class="all-metadata-structures">'."\n";
        foreach ($all_metadata_structures as $a_mds) {
            if ($USER->canActOnTarget($ACTIONS['view'],$a_mds)) {
                echo $a_mds->renderAsListTree();
            }
        }
        echo '</ul>'."\n";
//        if ($USER->canActOnTarget($ACTIONS['create'],new Metadata_Structure(['DB'=>$DB]))) {
//            ?>
<!--            <a href="--><?php //echo APP_ROOT_PATH.'/app_code/metadata_structure.php?action=create&user_id='.$USER->user_id; ?><!--" class="btn" id="btn-add-metadata-structure">--><?php //echo util_lang('add_metadata_structure'); ?><!--</a>--><?php
//        }
    }

    if ($action == 'view') {
        if ($USER->canActOnTarget($ACTIONS['edit'],$mds)) {
            echo '<div id="actions">'.$mds->renderAsButtonEdit().'</div>'."\n";
        }
        echo $mds->renderAsView();
    } else
    if (($action == 'edit') || ($action == 'create')) {
        //echo 'TO BE IMPLEMENTED: edit and create actions';
        echo $mds->renderAsEdit();
    } else
    if ($action == 'delete') {
        $parent_mds = $mds->getParent();

        $mds->doDelete();

        if ($parent_mds) {
            util_redirectToAppPage('app_code/metadata_structure.php?action=view&metadata_structure_id='.$parent_mds->metadata_structure_id,'info',util_lang('msg_metadata_structure_deleted'));
        } else {
            util_redirectToAppPage('app_code/metadata_structure.php?action=list','info',util_lang('msg_metadata_structure_deleted'));
        }

    }
require_once('../foot.php');
?>