<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Notebook_Page extends Db_Linked {
		public static $fields = array('notebook_page_id', 'created_at', 'updated_at', 'notebook_id', 'authoritative_plant_id', 'notes', 'flag_workflow_published', 'flag_workflow_validated', 'flag_delete');
		public static $primaryKeyField = 'notebook_page_id';
		public static $dbTable = 'notebook_pages';

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
            $btn = '<a id="btn-edit" href="'.APP_ROOT_PATH.'/app_code/notebook_page.php?action=edit&notebook_page_id='.$this->notebook_page_id.'" class="edit_link btn" >'.util_lang('edit').'</a>';
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
'  <h3 class="notebook_page_title">'.$n->renderAsLink().': '.$ap->renderAsShortText()."</h3>\n".
'  <span class="created_at">'.util_lang('created_at').' '.util_datetimeFormatted($this->created_at).'</span>, <span class="updated_at">'.util_lang('updated_at').' '.util_datetimeFormatted($this->updated_at)."</span><br/>\n".
'  <span class="owner">'.util_lang('owned_by').' <a href="'.APP_ROOT_PATH.'/app_code/user.php?action=view&user_id='.$owner->user_id.'">'.$owner->screen_name.'</a></span><br/>'."\n".
'  <span class="published_state">'.($this->flag_workflow_published ? util_lang('published_true') : util_lang('published_false'))
    .'</span>, <span class="verified_state">'.($this->flag_workflow_validated ? util_lang('verified_true') : util_lang('verified_false'))
    .'</span><br/>'."\n".
'  <div class="notebook_page_notes">'.htmlentities($this->notes)."</div>\n".
'  '.$ap->renderAsViewEmbed()."\n".
'  <ul class="notebook_page_fields">'."\n";
            foreach ($this->page_fields as $pf) {
                $rendered .= '    '.$pf->renderAsListItem()."\n";
            }
//            $rendered .= $add_field_button_li;
            $rendered .='  </ul>'."\n".
'  <h4>'.ucfirst(util_lang('specimens'))."</h4>\n".
'  <ul class="specimens">'."\n";
            foreach ($this->specimens as $specimen) {
                $rendered .= '    <li>'.$specimen->renderAsViewEmbed()."</li>\n";
            }
            $rendered .= "  </ul>\n</div>";

            return $rendered;
        }

        public function renderAsEdit() {
            return 'TO BE IMPLEMENTED';
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
            return Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>$this->authoritative_plant_id],$this->dbConnection);
        }

    }
