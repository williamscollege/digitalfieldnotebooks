<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Metadata_Term_Set extends Db_Linked {
		public static $fields = array('metadata_term_set_id', 'created_at', 'updated_at', 'name', 'ordering', 'description', 'flag_delete');
		public static $primaryKeyField = 'metadata_term_set_id';
		public static $dbTable = 'metadata_term_sets';
        public static $entity_type_label = 'metadata_term_set';

        public $term_values;
        public $references;
        public $structures;

        public function __construct($initsHash) {
            parent::__construct($initsHash);


            // now do custom stuff
            // e.g. automatically load all accessibility info associated with the user

            $this->term_values = array();
            $this->references = array();
            $this->structures = array();
        }

        //------------------------------------------------

        public static function cmp($a, $b) {
            if ($a->ordering == $b->ordering) {
                if ($a->name == $b->name) {
                    return 0;
                }
                return ($a->name < $b->name) ? -1 : 1;
            }
            return ($a->ordering < $b->ordering) ? -1 : 1;
        }

        public static function createNewMetadataTermSet($db_connection) {
            $new = new Metadata_Term_Set([
                'metadata_term_set_id' => 'NEW',
                'created_at' => util_currentDateTimeString_asMySQL(),
                'updated_at' => util_currentDateTimeString_asMySQL(),
                'name' => util_lang('new_metadata_term_set_name'),
                'ordering' => 0,
                'description' => util_lang('new_metadata_term_set_description'),
//                'flag_workflow_published' => false,
//                'flag_workflow_validated' => false,
                'flag_delete' => false,
                'DB' => $db_connection
            ]);

            return $new;
        }

        public static function renderAllAsSelectControl($unique_id='',$default_selected_id = 0, $first_item_text = '') {
            if (is_object($default_selected_id)) {
                $default_selected = $default_selected_id->metadata_term_set_id;
            }

            if (! $unique_id) {
                $unique_id = 'metadata_term_set_id';
            }

            global $DB;

            $all_mdts = Metadata_Term_Set::getAllFromDb(['flag_delete' => FALSE], $DB);
            usort($all_mdts,'Metadata_Term_Set::cmp');

            if (! $first_item_text) {
                $first_item_text = util_lang('prompt_select');
            }

            $rendered = '<select name="'.$unique_id.'" id="'.$unique_id.'" class="metadata_term_set_selector">'."\n";
            $rendered .= '<option value="-1">'.$first_item_text.'</option>'."\n";
            foreach ($all_mdts as $mdts) {
                $rendered .= $mdts->renderAsOption('',$default_selected_id)."\n";
            }
            $rendered .= '</select>';

            return $rendered;
        }

        //------------------------------------------------

        //  NOTE: returns 0 if there is no parent
		public function getMetadataStructures() {
            return Metadata_Structure::getAllFromDb(['metadata_term_set_id' => $this->metadata_term_set_id, 'flag_delete' => FALSE], $this->dbConnection);
		}

        public function loadTermValues() {
            $this->term_values = Metadata_Term_Value::getAllFromDb(['metadata_term_set_id' => $this->metadata_term_set_id, 'flag_delete' => FALSE], $this->dbConnection);
            usort($this->term_values,'Metadata_Term_Value::cmp');
        }

        public function cacheTermValues() {
            if (! $this->term_values) {
                $this->loadTermValues();
            }
        }

        public function loadReferences() {
            $this->references = Metadata_Reference::getAllFromDb(['metadata_type'=>'term_set', 'metadata_id' => $this->metadata_term_set_id, 'flag_delete' => FALSE], $this->dbConnection);
            usort($this->references,'Metadata_Reference::cmp');
        }

        public function cacheReferences() {
            if (! $this->references) {
                $this->loadReferences();
            }
        }

        public function loadStructures() {
            $this->structures = Metadata_Structure::getAllFromDb(['metadata_term_set_id'=>$this->metadata_term_set_id, 'flag_delete' => FALSE], $this->dbConnection);
            usort($this->structures,'Metadata_Structure::cmp');
        }

        public function cacheStructures() {
            if (! $this->structures) {
                $this->loadStructures();
            }
        }

        public function renderAsHtml() {
            $this->cacheReferences();
            $this->cacheTermValues();

            $rendered = '<div class="metadata-term-set-header"><h4>'.util_lang('metadata_term_set','properize').' : <a href="'.APP_ROOT_PATH.'/app_code/metadata_term_set.php?action=view&metadata_term_set_id='.$this->metadata_term_set_id.'" class="metadata_term_set_name_link" data-metadata_term_set_id="'.$this->metadata_term_set_id.'">'.htmlentities($this->name).'</a></h4>';
            $rendered .= $this->renderAsHtml_references();
            $rendered .= '</div>';
            $rendered .= $this->renderAsHtml_term_values();
            $rendered .= $this->renderAsHtml_structures();

            return $rendered;

        }

        public function renderAsHtml_references() {
            $this->cacheReferences();
            $rendered = Metadata_Reference::renderReferencesArrayAsListsView($this->references);
            return $rendered;
        }

        public function renderAsEdit_references() {
            $this->cacheTermValues();
            $rendered = Metadata_Reference::renderReferencesArrayAsListsEdit($this->references);
            return $rendered;
        }

        public function renderAsHtml_term_values() {
            $this->cacheTermValues();
            $rendered = '<h5>'.util_lang('metadata_values','ucfirst').'</h5>'."\n";
            $rendered .= '<ul class="metadata-term-values">';
            foreach ($this->term_values as $tv) {
                $rendered .= $tv->renderAsListItem();
            }
            $rendered .= '</ul>';

            return $rendered;
        }

        public function renderAsEdit_term_values() {
            $this->cacheTermValues();
//            $rendered = 'TO BE IMPLEMENTED: renderAsEdit_term_values';
            $rendered = '<h5>'.util_lang('metadata_values','ucfirst').'</h5>'."\n";
            $rendered .= '<ul class="metadata-term-values">'."\n";
            $rendered .= '    <li><a href="#" id="add_new_metadata_term_value_button" class="btn">'.util_lang('add_metadata_term_value').'</a></li>'."\n";
            foreach ($this->term_values as $tv) {
                $rendered .= $tv->renderAsListItemEdit();
            }
            $rendered .= '</ul>';

            return $rendered;
        }

        public function renderAsHtml_structures() {
            $this->cacheStructures();

            //used_by
            $rendered = '<h5>'.util_lang('used_by').'</h5>'."\n";
            $rendered .= '<div class="metadata-term-set-uses">';
            $rendered .= '<ul class="metadata-structures">';
            foreach ($this->structures as $s) {
                $rendered .= '<li>'.$s->renderAsLink().'</li>';
            }
            $rendered .= '</ul></div>';

            return $rendered;
        }

        public function renderAsListItem($idstr='',$classes_array = [],$other_attribs_hash = []) {
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().'>';
            $li_elt .= $this->renderAsViewEmbed();
            $li_elt .= '</li>';
            return $li_elt;
        }

        public function renderAsViewEmbed() {
            $rendered = '<div id="rendered_metadata_term_set_'.$this->metadata_term_set_id.'" class="rendered-metadata-term-set embedded" '.$this->fieldsAsDataAttribs().'>';
            $rendered .= $this->renderAsHtml();
            $rendered .= '</div>';
            return $rendered;
        }

        public function renderAsView() {
            $this->cacheReferences();
            $this->cacheTermValues();
            $rendered = '<div class="view-metadata-term-set">'."\n";
            $rendered .= '<div class="view-metadata-term-set-header"><a href="'.APP_ROOT_PATH.'/app_code/metadata_term_set.php?action=list">'.util_lang('all_metadata_term_sets').'</a> &gt; <h3>'.htmlentities($this->name).'</h3></div>';
            $rendered .= $this->renderAsHtml_references();
            $rendered .= $this->renderAsHtml_term_values();
            $rendered .= $this->renderAsHtml_structures();
            $rendered .= '</div>'."\n";
            $rendered .= '</div>'."\n";

            return $rendered;
        }

        public function renderAsEdit() {
            $rendered = '';

            $rendered .= '<form id="form-edit-metadata-term-set-base-data" action="'.APP_ROOT_PATH.'/app_code/metadata_term_set.php">'."\n";
            $rendered .= '  <input type="hidden" name="action" value="update"/>'."\n";
            $rendered .= '  <input type="hidden" id="metadata_term_set_id" name="metadata_term_set_id" value="'.$this->metadata_term_set_id.'"/>'."\n";

            $rendered .= '  <div id="actions">'."\n";
            $rendered .= '    <button id="edit-submit-control" class="btn btn-success" type="submit" name="edit-submit-control" value="update"><i class="icon-ok-sign icon-white"></i> '.util_lang((($this->metadata_term_set_id != 'NEW') ? 'update' : 'save'),'properize').'</button>'."\n";
            if ($this->metadata_term_set_id != 'NEW') {
                $rendered .= '    <a id="edit-cancel-control" class="btn" href="'.APP_ROOT_PATH.'/app_code/metadata_term_set.php?action=view&metadata_term_set_id='.$this->metadata_term_set_id.'"><i class="icon-remove"></i> '.util_lang('cancel','properize').'</a>'."\n";
                $rendered .= '    <a id="edit-delete-metadata-term-set-control" class="btn btn-danger" href="'.APP_ROOT_PATH.'/app_code/metadata_term_set.php?action=delete&metadata_term_set_id='.$this->metadata_term_set_id.'"><i class="icon-trash icon-white"></i> '.util_lang('delete','properize').'</a>'."\n";
            } else {
                $rendered .= '    <a id="edit-cancel-control" class="btn" href="'.APP_ROOT_PATH.'/app_code/metadata_term_set.php?action=edit&metadata_term_set_id='.$this->metadata_term_set_id.'"><i class="icon-remove"></i> '.util_lang('cancel','properize').'</a>';
            }
            $rendered .= '  </div>'."\n";

            $rendered .= '  <div class="edit-metadata-term-set" '.$this->fieldsAsDataAttribs().'>'."\n";
            $rendered .= '    <div class="edit-metadata-term-set-header">';
            $rendered .= '<a href="'.APP_ROOT_PATH.'/app_code/metadata_term_set.php?action=list">'.util_lang('all_metadata_term_sets').'</a> &gt;';
            $rendered .= '<h3><input class="object-name-control" id="mdts-name" name="name" type="text" value="'.htmlentities($this->name).'"/></h3>';
            $rendered .= '</div>';
            $rendered .= '    <div class="description-controls"><input title="'.util_lang('title_description').'" class="description-control" type="text" name="description" value="'.htmlentities($this->description).'"/></div>'."\n";
            $rendered .= $this->renderAsEdit_references();
            $rendered .= $this->renderAsEdit_term_values();
            $rendered .= $this->renderAsHtml_structures();
            $rendered .= '  </div>'."\n";
            $rendered .= '</form>'."\n";

            return $rendered;
        }

        public function renderAsOption($display_prefix='',$default_selected=0) {
            $opt = '<option value="'.$this->metadata_term_set_id.'" title="'.htmlentities($this->description).'"';
            if ($this->metadata_term_set_id == $default_selected) {
                $opt .= ' selected="selected"';
            }
            $opt .= '>'.$display_prefix.htmlentities($this->name).'</option>';
            return $opt;
        }

        public function renderAsSelectControl($name,$default_checked_value_id=0,$id='') {
            if (! $name) {
                $name = 'select_metadata_term_value';
            }
            if (! $id) {
                $id = $name;
            }

            $this->cacheTermValues();

            $rendered = '<select name="'.$name.'" id="'.$id.'" class="metadata_term_value_select_control">'."\n";
            $rendered .= '  <option value="-1">-- '.util_lang('nothing_from_the_list').' --</option>'."\n";
//            foreach ($this->term_values as $v) {
//                $rendered .= '  '.$v->renderAsOption($v->metadata_term_value_id == $default_checked_value_id)."\n";
//            }
            $rendered .= $this->renderValuesAsOptions($default_checked_value_id);
            $rendered .= '</select>';

            return $rendered;
        }

        public function renderValuesAsOptions($default_checked_value_id=0) {
            $this->cacheTermValues();
            $rendered = '';
            foreach ($this->term_values as $v) {
                $rendered .= '  '.$v->renderAsOption($v->metadata_term_value_id == $default_checked_value_id)."\n";
            }
            return $rendered;
        }
    }
