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
//        echo "TODO: create action support";

        $ap = Authoritative_Plant::createNewAuthoritativePlant($DB);
        $action == 'edit';
//        exit;
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

    if (($action == 'create') || ($action == 'update')) {
        if (! $ap) {
            $action = 'list';
        }
    } elseif ((! $ap) || (! $ap->matchesDb)) {
        $action = 'list';
    }


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

    if ($action == 'update') {
//        echo 'TODO: implement update, verify, and publish actions';

//        util_prePrintR($_REQUEST);

        /////////////////////
        // first, the actual auth plant
        $changed = false;
        $updateable_fields = ['class', 'order', 'family', 'genus', 'species', 'variety', 'catalog_identifier'];
        $flag_fields = ['flag_active'];
        foreach ($updateable_fields as $fname) {
            $req_name = 'authoritative_plant-'.$fname.'_'.$ap->ID();
            if ((isset($_REQUEST[$req_name])) && ($ap->fieldValues[$fname] != $_REQUEST[$req_name])) {
                $changed = true;
                $ap->fieldValues[$fname] = $_REQUEST[$req_name]; // NOTE: this seems dangerous, but the data is sanitized on the way back out
            }
        }
        // special handling of 'flag_active' since it's a checkbox
        $active_check_state = false;
        if (isset($_REQUEST['flag_active'])) {
            $active_check_state = true;
        }
        if ($ap->flag_active != $active_check_state) {
            $changed = true;
            $ap->flag_active = $active_check_state;
        }

        if ($changed) {
            $ap->matchesDb = false;
            $ap->updateDb();
        }

        if ($_REQUEST['authoritative_plant_id'] != 'NEW') {

            /////////////////////
            // second, any auth plant extras

            // deleted ones - in comma-sep deletion list
            $deleted_authoritative_plant_extra_ids = explode(',',$_REQUEST['deleted_authoritative_plant_extra_ids']);
            if ($deleted_authoritative_plant_extra_ids) {
                foreach ($deleted_authoritative_plant_extra_ids as $deleted_authoritative_plant_extra_id) {
                    if ($deleted_authoritative_plant_extra_id) {
                        $del_ape = Authoritative_Plant_Extra::getOneFromDb(['authoritative_plant_extra_id'=>$deleted_authoritative_plant_extra_id],$DB);
                        if ($del_ape->matchesDb) {
                            $del_ape->doDelete();
                        }
                    }
                }
            }

            // edited ones - foreach ap->extras update it from $_REQUEST data
            $ap->cacheExtras();
            foreach ($ap->extras as $db_ape) {
                if ($db_ape->type != 'image') { // images can only be added and removed - not edited/modified
                    $req_ape_value = trim($_REQUEST['authoritative_plant_extra_'.$db_ape->authoritative_plant_extra_id.'-value']);
                    if ($db_ape->value != $req_ape_value) {
                        $db_ape->value = $req_ape_value;
                        $db_ape->updateDb();
                    }
                }
            }

            // new ones
            $created_authoritative_plant_extra_ids = explode(',',$_REQUEST['created_authoritative_plant_extra_ids']);
            foreach ($created_authoritative_plant_extra_ids as $created_authoritative_plant_extra_id) {
                if ($created_authoritative_plant_extra_id) {
                    //                echo "handling auth plant extra creation for $created_authoritative_plant_extra_id<br/>\n";
                    //                util_prePrintR($_REQUEST);
                    $new_ape_type = 'authoritative_plant_extra_'.$created_authoritative_plant_extra_id.'-type';
                    $new_ape = Authoritative_Plant_Extra::createNewAuthoritativePlantExtraFor($new_ape_type,$ap->authoritative_plant_id,$DB);
                    $new_ape->notebook_page_field_id = $created_authoritative_plant_extra_id;
                    $new_ape->setFromArray($_REQUEST);
                    if ($new_ape->value != false) {
                        $new_ape->authoritative_plant_extra_id = 'NEW';
                        $new_ape->updateDb();
                    }
                }
            }


            /////////////////////
            // third, any specimens

            // deleted
            $deleted_specimen_ids = explode(',',$_REQUEST['deleted_specimen_ids']);
            if ($deleted_specimen_ids) {
                foreach ($deleted_specimen_ids as $deleted_specimen_id) {
                    if ($deleted_specimen_id) {
                        $del_s = Specimen::getOneFromDb(['specimen_id'=>$deleted_specimen_id],$DB);
                        if ($del_s->matchesDb) {
                            $del_s->doDelete();
                        }
                    }
                }
            }

            // altered
            $ap->cacheSpecimens();
            foreach ($ap->specimens as $db_specimen) {
                $db_specimen->setFromArray($_REQUEST);
                $db_specimen->updateDb();
            }

            // created
            $created_specimen_ids = explode(',',$_REQUEST['created_specimen_ids']);
            foreach ($created_specimen_ids as $created_specimen_id) {
                if ($created_specimen_id) {
                    $new_s = Specimen::createNewSpecimenForAuthoritativePlant($ap->authoritative_plant_id,$DB);
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

    if ($action == 'list') {
        echo '<h2>'.ucfirst(util_lang('authoritative_plants')).'</h2>'."\n";
        if ($USER->canActOnTarget($ACTIONS['edit'],$ap)) {
            ?>
            <a href="<?php echo APP_ROOT_PATH.'/app_code/authoritative_plant.php?action=create&user_id='.$USER->user_id; ?>" class="btn" id="btn-add-authoritative_plant"><?php echo util_lang('add_authoritative_plant'); ?></a><?php
        }
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