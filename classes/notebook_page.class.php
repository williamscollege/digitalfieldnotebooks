<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Notebook_Page extends Db_Linked {
		public static $fields = array('notebook_page_id', 'created_at', 'updated_at', 'notebook_id', 'authoritative_plant_id', 'notes', 'flag_workflow_published', 'flag_workflow_validated', 'flag_delete');
		public static $primaryKeyField = 'notebook_page_id';
		public static $dbTable = 'notebook_pages';
        public static $entity_type_label = 'notebook_page';

        public $page_fields;
        public $specimens;

		public function __construct($initsHash) {
			parent::__construct($initsHash);


			// now do custom stuff
			// e.g. automatically load all accessibility info associated with the user
            $this->flag_workflow_published = false;
            $this->flag_workflow_validated = false;
            $this->page_fields = array();
		}

		public static function cmp($a, $b) {
            if ($a->notebook_id == $b->notebook_id) {
//                echo "auth plant cmp";
                return Authoritative_Plant::cmp($a->getAuthoritativePlant(),$b->getAuthoritativePlant());
            }
//            echo "notebook cmp";
            return Notebook::cmp($a->getNotebook(),$b->getNotebook());
        }

        public static function createNewNotebookPageForNotebook($notebook_id,$db_connection) {
            $n = new Notebook_Page([
                'notebook_page_id' => 'NEW',
                'created_at' => util_currentDateTimeString_asMySQL(),
                'updated_at' => util_currentDateTimeString_asMySQL(),
                'notebook_id' => $notebook_id,
                'authoritative_plant_id' => 0,
                'notes' => util_lang('new_notebook_page_notes'),
                'flag_workflow_published' => false,
                'flag_workflow_validated' => false,
                'flag_delete' => false,
                'DB'=>$db_connection]);
            return $n;
        }

        private function _startRenderedListItem($idstr,$classes_array,$other_attribs_hash) {
            global $USER,$ACTIONS;
            $actions_attribs = '';

            if ($USER->canActOnTarget($ACTIONS['edit'],$this)) {
                $actions_attribs .= ' data-can-edit="1"';
            }
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().$actions_attribs.'>';
            $li_elt .= '<a href="'.APP_ROOT_PATH.'/app_code/notebook_page.php?action=view&notebook_page_id='.$this->notebook_page_id.'">';
            return $li_elt;
        }

        public function renderAsListItem($idstr='',$classes_array = [],$other_attribs_hash = []) {
//            global $USER,$ACTIONS;
//            $actions_attribs = '';
////
////            util_prePrintR($USER);
////            util_prePrintR($ACTIONS);
//
//            if ($USER->canActOnTarget($ACTIONS['edit'],$this)) {
//                $actions_attribs .= ' data-can-edit="1"';
//            }
//            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
//            $li_elt .= ' '.$this->fieldsAsDataAttribs().$actions_attribs.'>';
//            $li_elt .= '<a href="'.APP_ROOT_PATH.'/app_code/notebook_page.php?action=view&notebook_page_id='.$this->notebook_page_id.'">'.htmlentities($this->getAuthoritativePlant()->renderAsShortText()).'</a></li>';
            $li_elt = $this->_startRenderedListItem($idstr,$classes_array,$other_attribs_hash);
            $li_elt .= htmlentities($this->getAuthoritativePlant()->renderAsShortText()).'</a></li>';
            return $li_elt;
        }

        public function renderAsListItemForNotebook($idstr='',$classes_array = [],$other_attribs_hash = []) {
            $nb = $this->getNotebook();
            $li_elt = $this->_startRenderedListItem($idstr,$classes_array,$other_attribs_hash);
            $li_elt .= util_lang('page_in_notebook').' '.htmlentities($nb->name).'</a></li>';
            return $li_elt;
        }

        public function renderAsButtonEdit() {
            $btn = '<a id="btn-edit" href="'.APP_ROOT_PATH.'/app_code/notebook_page.php?action=edit&notebook_page_id='.$this->notebook_page_id.'" class="edit_link btn" ><i class="icon-edit"></i> '.util_lang('edit').'</a>';
            return $btn;
        }

        function renderAsView() {
            $this->loadPageFields();
            $this->loadSpecimens();
            $n = $this->getNotebook();
            $ap = $this->getAuthoritativePlant();

            global $USER,$ACTIONS;

            $actions_attribs = '';
//            $add_field_button_li = '';
            if ($USER->canActOnTarget($ACTIONS['edit'],$this)) {
                $actions_attribs .= ' data-can-edit="1"';
//                $add_field_button_li = '    <li><a href="" id="btn-add-notebook-page-field" class="creation_link btn">'.util_lang('add_notebook_page_field').'</a></li>'."\n";
            }

            $owner = $USER;
            if ($n->user_id != $USER->user_id) {
                $owner = $n->getUser();
            }

            $rendered = '<div id="rendered_notebook_page_'.$this->notebook_page_id.'" class="rendered_notebook_page" '.$this->fieldsAsDataAttribs().$actions_attribs.">\n".
'  <h3 class="notebook_page_title">'.$n->renderAsLink().":</h3>\n".
'  '.$ap->renderAsViewEmbed()."\n".
'  <div class="info-timestamps"><span class="created_at">'.util_lang('created_at').' '.util_datetimeFormatted($this->created_at).'</span>, <span class="updated_at">'.util_lang('updated_at').' '.util_datetimeFormatted($this->updated_at)."</span></div>\n".
'  <div class="info-owner">'.util_lang('owned_by').' <a href="'.APP_ROOT_PATH.'/app_code/user.php?action=view&user_id='.$owner->user_id.'">'.htmlentities($owner->screen_name).'</a></div>'."\n".
'  <div class="info-workflow"><span class="published_state">'.($this->flag_workflow_published ? util_lang('published_true') : util_lang('published_false'))
    .'</span>, <span class="verified_state">'.($this->flag_workflow_validated ? util_lang('verified_true') : util_lang('verified_false'))
    .'</span></div>'."\n".
'  <div class="notebook-page-notes">'.htmlentities($this->notes)."</div>\n".
'  <h4>'.ucfirst(util_lang('metadata'))."</h4>\n";

            $rendered .= '  <ul class="notebook-page-fields">'."\n";
            if ($this->page_fields) {
                $prev_pf_structure_id = $this->page_fields[0]->label_metadata_structure_id;
                foreach ($this->page_fields as $pf) {
                    $spacer_class = '';
                    if ($pf->label_metadata_structure_id != $prev_pf_structure_id) {
                        $spacer_class = 'spacing-list-item';
                    }
                    $rendered .= '    '.$pf->renderAsListItem('list_item-notebook_page_field_'.$pf->notebook_page_field_id,[$spacer_class])."\n";
                    $prev_pf_structure_id = $pf->label_metadata_structure_id;
                }
    //            $rendered .= $add_field_button_li;
            } else {
                $rendered .= '<li>'.util_lang('no_metadata','ucfirst').'</li>'."\n";
            }
            $rendered .='  </ul>'."\n";

            $rendered .= '  <h4>'.ucfirst(util_lang('specimens'))."</h4>\n";
            $rendered .= '  <ul class="specimens">'."\n";

            if ($this->specimens) {
                foreach ($this->specimens as $specimen) {
                    $rendered .= '    <li>'.$specimen->renderAsViewEmbed()."</li>\n";
                }
            } else {
                $rendered .= '<li>'.util_lang('no_specimens','ucfirst').'</li>'."\n";
            }

            $rendered .= "  </ul>\n</div>";

            return $rendered;
        }

        public function renderAsEdit() {
            $this->loadPageFields();
            $this->loadSpecimens();
            $n = $this->getNotebook();
            $ap = '';
            if ($this->notebook_page_id != 'NEW') {
                $ap = $this->getAuthoritativePlant();
            }

//            util_prePrintR('TO BE IMPLEMENTED: handle auth plant for new pages (i.e. where auth plant id == 0)');

//            util_prePrintR($this);

            global $USER,$ACTIONS;

            $actions_attribs = '';
//            $add_field_button_li = '';
            if ($USER->canActOnTarget($ACTIONS['edit'],$this)) {
                $actions_attribs .= ' data-can-edit="1"';
//                $add_field_button_li = '    <li><a href="" id="btn-add-notebook-page-field" class="creation_link btn">'.util_lang('add_notebook_page_field').'</a></li>'."\n";
            }

            $owner = $USER;
            if ($n->user_id != $USER->user_id) {
                $owner = $n->getUser();
            }

            $rendered =
                '<div id="rendered_notebook_page_'.$this->notebook_page_id.'" class="rendered_notebook_page edit_rendered_notebook_page" '.$this->fieldsAsDataAttribs().$actions_attribs.">\n".
                '<form id="form-edit-notebook-page-base-data" action="'.APP_ROOT_PATH.'/app_code/notebook_page.php">'."\n".
                '  <input type="hidden" name="action" value="update"/>'."\n".
                '  <input type="hidden" name="notebook_page_id" value="'.$this->notebook_page_id.'"/>'."\n".
                '  <input type="hidden" name="notebook_id" value="'.$this->notebook_id.'"/>'."\n";
            $rendered .= '  <div id="actions">';
            $rendered .= '<button id="edit-submit-control" class="btn btn-success" type="submit" name="edit-submit-control" value="update"><i class="icon-ok-sign icon-white"></i> '.util_lang((($this->notebook_page_id != 'NEW') ? 'update' : 'save'),'properize').'</button>'."\n";
            if ($this->notebook_page_id != 'NEW') {
                $rendered .= '  <a id="edit-cancel-control" class="btn" href="'.APP_ROOT_PATH.'/app_code/notebook_page.php?action=view&notebook_page_id='.$this->notebook_page_id.'"><i class="icon-remove"></i> '.util_lang('cancel','properize').'</a>';
                $rendered .= '  <a id="edit-delete-notebook-page-control" class="btn btn-danger" href="'.APP_ROOT_PATH.'/app_code/notebook_page.php?action=delete&notebook_page_id='.$this->notebook_page_id.'"><i class="icon-trash icon-white"></i> '.util_lang('delete','properize').'</a>';
            } else {
                $rendered .= '  <a id="edit-cancel-control" class="btn" href="'.APP_ROOT_PATH.'/app_code/notebook.php?action=edit&notebook_id='.$this->notebook_id.'"><i class="icon-remove"></i> '.util_lang('cancel','properize').'</a>';
            }

            $rendered .= '</div>'."\n";
            $rendered .= '<h4>'.util_lang('page_in_notebook','ucfirst').' <a href="'.APP_ROOT_PATH.'/app_code/notebook.php?action=view&notebook_id='.$n->notebook_id.'" id="parent-notebook-link">'.htmlentities($n->name).'</a></h4>'."\n";

            if ($this->notebook_page_id != 'NEW') {
                $rendered .= '<a class="show-hide-control" href="#" data-for_elt_id="select_new_authoritative_plant_'.$this->notebook_page_id.'">'.util_lang('change_authoritative_plant').'</a>';
                $rendered .= '  <div id="select_new_authoritative_plant_'.$this->notebook_page_id.'" class="select_new_authoritative_plant">'.Authoritative_Plant::renderControlSelectAllAuthoritativePlants((($this->notebook_page_id != 'NEW') ? $ap->authoritative_plant_id : 0)).'</div>'."\n";
                $rendered .= '  '.$ap->renderAsViewEmbed()."\n";
            } else {
                $rendered .= '  <div id="select_new_authoritative_plant_'.$this->notebook_page_id.'" class="NEW_select_new_authoritative_plant">'.Authoritative_Plant::renderControlSelectAllAuthoritativePlants(0).'</div>'."\n";
            }

            $rendered .= '  <div class="info-timestamps"><span class="created_at">'.util_lang('created_at').' '.util_datetimeFormatted($this->created_at).'</span>, <span class="updated_at">'.util_lang('updated_at').' '.util_datetimeFormatted($this->updated_at)."</span></div>\n".
                '  <div class="info-owner">'.util_lang('owned_by').' <a href="'.APP_ROOT_PATH.'/app_code/user.php?action=view&user_id='.$owner->user_id.'">'.htmlentities($owner->screen_name).'</a></div>'."\n";

            $rendered .= '<div class="control-workflows">';
            if ($this->notebook_page_id != 'NEW') {
                if ($USER->canActOnTarget('publish',$this)) {
                    $rendered .= '  <span class="published_state workflow-control"><input id="notebook-page-workflow-publish-control" type="checkbox" name="flag_workflow_published" value="1"'.($this->flag_workflow_published ?  ' checked="checked"' : '').' /> '
                        .util_lang('publish').'</span>,';
                } else {
                    $rendered .= '  <span class="published_state workflow-info">'.($this->flag_workflow_published ? util_lang('published_true') : util_lang('published_false'))
                        .'</span>,';
                }

                if ($USER->canActOnTarget('verify',$this)) {
                    $rendered .= '  <span class="verified_state workflow-control"><input id="notebook-page-workflow-validate-control" type="checkbox" name="flag_workflow_validated" value="1"'.($this->flag_workflow_validated ?  ' checked="checked"' : '').' /> '
                        .util_lang('verify').'</span>';
                } else {
                    $rendered .= ' <span class="verified_state workflow-info">'.($this->flag_workflow_validated ? util_lang('verified_true') : util_lang('verified_false'))
                        .'</span>';
                }
                $rendered .= '<br/>'."\n";
            } else {
                $rendered .= '  <span class="published_state workflow-info">'.($this->flag_workflow_published ? util_lang('published_true') : util_lang('published_false'))
                    .'</span>,';
                $rendered .= ' <span class="verified_state workflow-info">'.($this->flag_workflow_validated ? util_lang('verified_true') : util_lang('verified_false'))
                    .'</span>';
            }
            $rendered .= "</div>\n";

            $rendered .= '  <div class="notebook_page_notes"><textarea id="notebook-page-notes" name="notes" rows="4" cols="120">'.htmlentities($this->notes).'</textarea></div>'."\n";

            if ($this->notebook_page_id != 'NEW') {
                $rendered .= '  <h4>'.ucfirst(util_lang('metadata'))."</h4>\n";
                $rendered .= '  <ul class="notebook-page-fields">'."\n";
                $rendered .= '    <li><a href="#" id="add_new_notebook_page_field_button" class="btn">'.util_lang('add_notebook_page_field').'</a></li>'."\n";
                if ($this->page_fields) {
                    $prev_pf_structure_id = $this->page_fields[0]->label_metadata_structure_id;
                    foreach ($this->page_fields as $pf) {
                        $spacer_class = '';
                        if ($pf->label_metadata_structure_id != $prev_pf_structure_id) {
                            $spacer_class = 'spacing-list-item';
                        }
                        $rendered .= '    '.$pf->renderAsListItemEdit('list_item-notebook_page_field_'.$pf->notebook_page_field_id,[$spacer_class])."\n";
                        $prev_pf_structure_id = $pf->label_metadata_structure_id;
                    }
                } else {
                    $rendered .= '<li>'.util_lang('no_metadata','ucfirst').'</li>'."\n";
                }
                $rendered .= '  </ul>'."\n";

                $rendered .= '  <h4>'.ucfirst(util_lang('specimens'))."</h4>\n".
                    '  <ul class="specimens">'."\n";
                $rendered .= '    <li><a href="#" id="add_new_specimen_button" class="btn">'.util_lang('add_specimen').'</a></li>'."\n";
                if ($this->specimens) {
                    foreach ($this->specimens as $specimen) {
                        $rendered .= '    <li>'.$specimen->renderAsEditEmbed()."</li>\n";
                    }
                } else {
                    $rendered .= '<li>'.util_lang('no_metadata','ucfirst').'</li>'."\n";
                }
                $rendered .= "  </ul>\n";

                $rendered .= '<input type="hidden" id="initial_page_field_ids" name="initial_page_field_ids" value="'.implode(',', Db_Linked::arrayOfAttrValues($this->page_fields,'notebook_page_field_id') ).'"/>'."\n";
                $rendered .= '<input type="hidden" id="created_page_field_ids" name="created_page_field_ids" value=""/>'."\n";
                $rendered .= '<input type="hidden" id="deleted_page_field_ids" name="deleted_page_field_ids" value=""/>'."\n";
                $rendered .= '<input type="hidden" id="initial_specimen_ids" name="initial_specimen_ids" value="'.implode(',', Db_Linked::arrayOfAttrValues($this->specimens,'specimen_id') ).'"/>'."\n";
                $rendered .= '<input type="hidden" id="created_specimen_ids" name="created_specimen_ids" value=""/>'."\n";
                $rendered .= '<input type="hidden" id="deleted_specimen_ids" name="deleted_specimen_ids" value=""/>'."\n";
            }
            $rendered .= '</form>'."\n";
            $rendered .= "</div>";

            return $rendered;
        }

        //---------------------------------------

        public function doDelete($debug = 0) {
            $this->loadPageFields();
            $this->loadSpecimens();

            foreach ($this->page_fields as $pf) {
                $pf->doDelete($debug);
            }

            foreach ($this->specimens as $s) {
                $s->doDelete($debug);
            }

            parent::doDelete($debug);
        }

        public function loadPageFields() {
            $this->page_fields = Notebook_Page_Field::getAllFromDb(['notebook_page_id' => $this->notebook_page_id,'flag_delete' => FALSE],$this->dbConnection);
            usort($this->page_fields,'Notebook_Page_Field::cmp');
        }

        public function loadSpecimens() {
            $this->specimens = Specimen::getAllFromDb(['link_to_type'=>'notebook_page','link_to_id' => $this->notebook_page_id,'flag_delete' => FALSE],$this->dbConnection);
            usort($this->specimens,'Specimen::cmp');
        }

        public function getNotebook() {
            return Notebook::getOneFromDb(['notebook_id'=>$this->notebook_id],$this->dbConnection);
        }

        public function getAuthoritativePlant() {
            if ($this->notebook_page_id == 'NEW') {
                return '';
            }
            return Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>$this->authoritative_plant_id],$this->dbConnection);
        }

    }
