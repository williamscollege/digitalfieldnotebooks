<?php
    require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('page'));
//	require_once('../app_head.php');

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
//        $notebook_page = new Notebook_Page(['notebook_id' => $_REQUEST['notebook_id'],'DB'=>$DB]);
//        $notebook_page->notebook_page_id = 'NEW';
//
        $notebook_page = Notebook_Page::createNewNotebookPageForNotebook($_REQUEST['notebook_id'],$DB);

    } else {
        if ($_REQUEST['notebook_page_id'] == 'NEW') {
            $notebook_page = Notebook_Page::createNewNotebookPageForNotebook($_REQUEST['notebook_id'],$DB);
        } else {
            if ((! isset($_REQUEST['notebook_page_id'])) || (! is_numeric($_REQUEST['notebook_page_id']))) {
                util_redirectToAppPage('app_code/notebook.php?action=list','failure',util_lang('no_notebook_page_specified'));
            }
            $notebook_page = Notebook_Page::getOneFromDb(['notebook_page_id'=>$_REQUEST['notebook_page_id']],$DB);
            if (! $notebook_page->matchesDb) {
                util_redirectToAppPage('app_code/notebook.php?action=list','failure',util_lang('no_notebook_page_found'));
            }
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

    if ($action != 'delete') {
        require_once('../app_head.php');
    }

    # 4. branch behavior based on the action
    #      update - update the object with the data coming in, then show the object (w/ 'saved' message)
    #      verify/publish - set the appropriate flag (true or false, depending on data coming in), then show the object (w/ 'saved' message)
    #      view - show the object
    #      create/edit - show a form with the object's current values ($action is 'update' on form submit)
    #      delete - delete the notebook, then go to home page w/ 'deleted' message

    if (($action == 'update') || ($action == 'verify') || ($action == 'publish')) {
        $changed = false;
        if ($notebook_page->authoritative_plant_id != $_REQUEST['authoritative_plant_id']) {
            $changed = true;
            $notebook_page->authoritative_plant_id = $_REQUEST['authoritative_plant_id']; // NOTE: this seems dangerous, but the data is sanitized on the way back out
        }
        if ($notebook_page->notes != $_REQUEST['notes']) {
            $changed = true;
            $notebook_page->notes = $_REQUEST['notes']; // NOTE: this seems dangerous, but the data is sanitized on the way back out
        }

        if ($USER->canActOnTarget($ACTIONS['publish'],$notebook_page)) {
            if (isset($_REQUEST['flag_workflow_published'])) {
                if ($_REQUEST['flag_workflow_published'] && ! $notebook_page->flag_workflow_published) {
                    $changed = true;
                    $notebook_page->flag_workflow_published = true;
                }
            } else {
                if ($notebook_page->flag_workflow_published) {
                    $changed = true;
                    $notebook_page->flag_workflow_published = false;
                }
            }
        }

        if ($USER->canActOnTarget($ACTIONS['verify'],$notebook_page)) {
            if (isset($_REQUEST['flag_workflow_validated'])) {
                if ($_REQUEST['flag_workflow_validated'] && ! $notebook_page->flag_workflow_validated) {
                    $changed = true;
                    $notebook_page->flag_workflow_validated = true;
                }
            } else {
                if ($notebook_page->flag_workflow_validated) {
                    $changed = true;
                    $notebook_page->flag_workflow_validated = false;
                }
            }
        }

        if ($changed) {
            $notebook_page->updateDb();
        }

        if ($_REQUEST['notebook_page_id'] != 'NEW') {
            $deleted_notebook_page_field_ids = explode(',',$_REQUEST['deleted_page_field_ids']);
    //        util_prePrintR($deleted_notebook_page_field_ids);
            if ($deleted_notebook_page_field_ids) {
                foreach ($deleted_notebook_page_field_ids as $deleted_notebook_page_fied_id) {
                    if ($deleted_notebook_page_fied_id) {
                        $del_npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>$deleted_notebook_page_fied_id],$DB);
                        if ($del_npf->matchesDb) {
                            $del_npf->doDelete();
                        }
                    }
                }
            }

            $intitial_notebook_page_field_ids = explode(',',$_REQUEST['initial_page_field_ids']);
            foreach ($intitial_notebook_page_field_ids as $notebook_page_field_id) {
                if (! in_array($notebook_page_field_id,$deleted_notebook_page_field_ids)) {
                    $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>$notebook_page_field_id],$DB);
                    if ($npf->matchesDb) {
                        $new_npf_vals = [
                            'notebook_page_field-value_open_'.$notebook_page_field_id =>$_REQUEST['page_field_open_value_'.$notebook_page_field_id]
                        ];
                        if (isset($_REQUEST['page_field_select_'.$notebook_page_field_id])) {
                            $new_term_value_id = $_REQUEST['page_field_select_'.$notebook_page_field_id];
                            if ($new_term_value_id < 1) {
                                $new_term_value_id = 0;
                            }
                            $new_npf_vals['notebook_page_field-value_metadata_term_value_id_'.$notebook_page_field_id] = $new_term_value_id;
                        }
                        $npf->setFromArray($new_npf_vals);
                        $npf->updateDb();
                    }
                }
            }

            $created_notebook_page_field_ids = explode(',',$_REQUEST['created_page_field_ids']);
            foreach ($created_notebook_page_field_ids as $created_page_field_id) {
    //            echo "TO BE IMPLEMENTED: handle creation of new notebook page fields";
                if ($created_page_field_id) {
    //                echo "handling page field creation for $created_page_field_id<br/>\n";
    //                util_prePrintR($_REQUEST);
                    $new_npf = Notebook_Page_Field::createNewNotebookPageFieldForNotebookPage($notebook_page->notebook_page_id,$DB);
                    $new_npf->notebook_page_field_id = $created_page_field_id;
                    $new_npf->setFromArray($_REQUEST);
                    if ($new_npf->label_metadata_structure_id != 0) {
                        $new_npf->notebook_page_field_id = 'NEW';
                        $new_npf->updateDb();
                    }
                }
            }


            $deleted_specimen_ids = explode(',',$_REQUEST['deleted_specimen_ids']);
//            util_prePrintR($deleted_specimen_ids);
            if ($deleted_specimen_ids) {
                foreach ($deleted_specimen_ids as $deleted_specimen_id) {
//                    util_prePrintR("handling $deleted_specimen_id\n");
                    if ($deleted_specimen_id) {
//                        util_prePrintR("fetching for delete $deleted_specimen_id\n");
                        $del_s = Specimen::getOneFromDb(['specimen_id'=>$deleted_specimen_id],$DB);
                        if ($del_s->matchesDb) {
//                            util_prePrintR("doing delete $deleted_specimen_id\n");
                            $del_s->doDelete();
//                            util_prePrintR("complete delete $deleted_specimen_id\n");
                        }
                    }
                }
            }

            $intitial_specimen_ids = explode(',',$_REQUEST['initial_specimen_ids']);
            foreach ($intitial_specimen_ids as $specimen_id) {
                if (! in_array($specimen_id,$deleted_specimen_ids)) {
                    $s = Specimen::getOneFromDb(['specimen_id'=>$specimen_id],$DB);
                    if ($s->matchesDb) {
                        $s->setFromArray($_REQUEST);
                        $s->updateDb();
                    }
                }
            }

            $created_specimen_ids = explode(',',$_REQUEST['created_specimen_ids']);
            foreach ($created_specimen_ids as $created_specimen_id) {
    //            echo "TO BE IMPLEMENTED: handle creation of specimens";
                if ($created_specimen_id) {
                    $new_s = Specimen::createNewSpecimenForNotebookPage($notebook_page->notebook_page_id,$DB);
                    $new_s->specimen_id = $created_specimen_id;
                    $new_s->setFromArray($_REQUEST);
                    if ($new_s->name != util_lang('new_specimen_name')) {
                        $new_s->specimen_id = 'NEW';
                        $new_s->updateDb();
                    }
                }
            }
        }

        $action = 'view';
    }

    echo '<script src="'.APP_ROOT_PATH.'/js/notebook_page_general.js"></script>'."\n";
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
        echo '<script src="'.APP_ROOT_PATH.'/js/notebook_page_edit.js"></script>'."\n";
        echo '<script src="'.APP_ROOT_PATH.'/js/ordering_controls.js"></script>'."\n";
        //echo 'TODO: implement edit and create actions';
    } else
    if ($action == 'delete') {
        $notebook = $notebook_page->getNotebook();

        $notebook_page->doDelete();

        util_redirectToAppPage('app_code/notebook.php?action=view&notebook_id='.$notebook->notebook_id,'info',util_lang('msg_notebook_page_deleted'));
    }
require_once('../foot.php');
?>