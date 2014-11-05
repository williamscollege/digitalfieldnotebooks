<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Metadata_Structure extends Db_Linked {
		public static $fields = array('metadata_structure_id', 'created_at', 'updated_at', 'parent_metadata_structure_id', 'name', 'ordering', 'description', 'details', 'metadata_term_set_id', 'flag_active', 'flag_delete');
		public static $primaryKeyField = 'metadata_structure_id';
		public static $dbTable = 'metadata_structures';
        public static $entity_type_label = 'metadata_structure';

        public $references;
        public $term_set;

        public function __construct($initsHash) {
            parent::__construct($initsHash);


            // now do custom stuff
            // e.g. automatically load all accessibility info associated with the user

            $this->references = array();
            $this->term_set = '';
        }

        public static function cmp($a, $b) {
            if ($a->parent_metadata_structure_id == $b->parent_metadata_structure_id) {
                if ($a->ordering == $b->ordering) {
                    if ($a->name == $b->name) {
                        return 0;
                    }
                    return ($a->name < $b->name) ? -1 : 1;
                }
                return ($a->ordering < $b->ordering) ? -1 : 1;
            }

            $lineageA = $a->getLineage();
            $lineageAIds = Db_Linked::arrayOfAttrValues($lineageA,'metadata_structure_id');
            if (in_array($b->metadata_structure_id,$lineageAIds)) {
                // b is some kind of parent of a, therefore b comes before a, i.e. a > b
                return 1;
            }

            $lineageB = $b->getLineage();
            $lineageBIds = Db_Linked::arrayOfAttrValues($lineageB,'metadata_structure_id');
            if (in_array($a->metadata_structure_id,$lineageBIds)) {
                // a is some kind of parent of b, therefore a comes before b, i.e. a < b
                return -1;
            }

//            util_prePrintR($lineageA);
//            util_prePrintR($lineageB);

            // trim off matching ancestories
            while ($lineageA[0]->metadata_structure_id == $lineageB[0]->metadata_structure_id) {
                array_shift($lineageA);
                array_shift($lineageB);
//                util_prePrintR($lineageA);
//                util_prePrintR($lineageB);
            }

            // return the cmp of the first descendents of the point of ancestral divergence
            return Metadata_Structure::cmp($lineageA[0],$lineageB[0]);
        }

        public static function renderControlSelectAllMetadataStructures($unique_id,$default_selected = 0,$first_item_text = '') {
            if (is_object($default_selected)) {
                $default_selected = $default_selected->metadata_structure_id;
            }

            if (! $unique_id) {
                $unique_id = 'metadata_structure_id';
            }

            global $DB;

            $all_mds = Metadata_Structure::getAllFromDb(['parent_metadata_structure_id'=>0,'flag_delete' => FALSE], $DB);
            usort($all_mds,'Metadata_Structure::cmp');

            if (! $first_item_text) {
                $first_item_text = util_lang('prompt_select');
            }

            $rendered = '<select name="'.$unique_id.'" id="'.$unique_id.'" class="metadata_structure_selector">'."\n";
            $rendered .= '<option value="-1">'.$first_item_text.'</option>'."\n";
            foreach ($all_mds as $mds) {
                $rendered .= $mds->renderAsOptionTree('',$default_selected);
            }
            $rendered .= '</select>';

            return $rendered;
        }

        public static function createNewMetadataStructure($parent_metadata_structure_id,$db_connection) {
            // 'metadata_structure_id', 'created_at', 'updated_at', 'parent_metadata_structure_id', 'name', 'ordering', 'description', 'details', 'metadata_term_set_id', 'flag_active', 'flag_delete'
            $n = new Metadata_Structure([
                'metadata_structure_id' => 'NEW',
                'created_at' => util_currentDateTimeString_asMySQL(),
                'updated_at' => util_currentDateTimeString_asMySQL(),
                'parent_metadata_structure_id' => $parent_metadata_structure_id,
                'name' => util_lang('new_metadata_structure_name'),
                'ordering' => 0,
                'description' => util_lang('new_metadata_structure_description'),
                'details' => util_lang('new_metadata_structure_details'),
                'metadata_term_set_id' => 0,
                'flag_active' => true,
                'flag_delete' => false,
                'DB'=>$db_connection]);
            return $n;
        }


        //-----------------------------------------------------------

        //  NOTE: returns 0 if there is no parent
		public function getParent() {
            if ($this->parent_metadata_structure_id > 0) {
                return Metadata_Structure::getOneFromDb(['metadata_structure_id' => $this->parent_metadata_structure_id, 'flag_delete' => FALSE], $this->dbConnection);
            }
            return 0;
		}

        // returns: the structure at the root of the structure tree (itself for structures with no parent, otherwise the most distant ancestor)
        public function getRoot() {
            if ($this->parent_metadata_structure_id == 0) {
                return $this;
            }
            $parent = Metadata_Structure::getOneFromDb(['metadata_structure_id' => $this->parent_metadata_structure_id, 'flag_delete' => FALSE], $this->dbConnection);
            return $parent->getRoot();
        }

        // returns: array of structures from this up to a structure with no parent
        public function getLineage() {
            if ($this->parent_metadata_structure_id == 0) {
                return [$this];
            }
            $parent = Metadata_Structure::getOneFromDb(['metadata_structure_id' => $this->parent_metadata_structure_id, 'flag_delete' => FALSE], $this->dbConnection);
            return array_merge($parent->getLineage(),[$this]);
        }

        public function getChildren() {
            $children = Metadata_Structure::getAllFromDb(['parent_metadata_structure_id' => $this->metadata_structure_id, 'flag_delete' => FALSE], $this->dbConnection);
//            util_prePrintR($children);
            usort($children,'Metadata_Structure::cmp');
            return $children;
        }

        public function loadReferences() {
            $this->references = Metadata_Reference::getAllFromDb(['metadata_type'=>'structure', 'metadata_id' => $this->metadata_structure_id, 'flag_delete' => FALSE], $this->dbConnection);
        }

        public function loadTermSetAndValues() {
            if (! $this->metadata_term_set_id) {
                $this->term_set = '';
                return;
            }

            $this->term_set = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id'=>$this->metadata_term_set_id],$this->dbConnection);
            $this->term_set->loadTermValues();
        }

        public function cacheTermSetAndValues() {
            if (! $this->term_set) {
                $this->loadTermSetAndValues();
            }
        }

        public function cacheReferences() {
            if (! $this->references) {
                $this->loadReferences();
            }
        }

        function renderAsLink($action='view') {
            $action = Action::sanitizeAction($action);
            $this->cacheTermSetAndValues();
//            $link = '<a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action='.$action.'&metadata_structure_id='.$this->metadata_structure_id.'">'.htmlentities($this->name).'</a>';
            $link = '<a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action='.$action.'&metadata_structure_id='.$this->metadata_structure_id.'">'.$this->renderAsFullName().' <i class="'.($this->flag_active ? 'icon-ok-circle' : 'icon-ban-circle').'"></i></a>';
            if ($this->term_set) {
                $link = '<a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action='.$action.'&metadata_structure_id='.$this->metadata_structure_id.'">'.$this->renderAsFullName().' ('.htmlentities($this->term_set->name).') <i class="'.($this->flag_active ? 'icon-ok-circle' : 'icon-ban-circle').'"></i></a>';
            }

            return $link;
        }

        public function renderAsButtonEdit() {
            $btn = '<a id="metadata_structure-btn-edit-'.$this->metadata_structure_id.'" href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=edit&metadata_structure_id='.$this->metadata_structure_id.'" title="'.util_lang('edit').'" class="edit_link btn" >'.util_lang('edit').'</a>';
            return $btn;
        }

        public function renderAsOption($display_prefix='',$default_selected=0) {
            $opt = '<option value="'.$this->metadata_structure_id.'" title="'.htmlentities($this->description).'" data-details="'.htmlentities($this->details).'"';
            if ($this->metadata_structure_id == $default_selected) {
                $opt .= ' selected="selected"';
            }
            $opt .= '>'.$display_prefix.htmlentities($this->name).'</option>';
            return $opt;
        }

        public function renderAsFullName() {
            $full_lineange = $this->getLineage();
            $full_name = '';
            foreach ($full_lineange as $lin_step) {
                if ($full_name) {
                    $full_name .= ' - ' . htmlentities($lin_step->name);
                } else {
                    $full_name .= htmlentities($lin_step->name);
                }
            }
            return $full_name;
        }

        public function renderAsView() {
            $this->loadTermSetAndValues();
            $this->loadReferences();

            //  '.$mds_parent->renderAsLink().' &gt;

            $rendered = '<div id="rendered_metadata_structure_'.$this->metadata_structure_id.'" class="view-rendered_metadata_structure" '.$this->fieldsAsDataAttribs().'>'."\n";

            $rendered .= '  <div class="metadata_lineage"><a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=list">'.util_lang('metadata').'</a> &gt;';
            $lineage = $this->getLineage();
            foreach ($lineage as $mds_ancestor) {
                if ($mds_ancestor->metadata_structure_id != $this->metadata_structure_id) {
                    $rendered .= ' '.$mds_ancestor->renderAsLink().' &gt;';
                }
            }
            $rendered .= '</div>'."\n";

            $rendered .= '  <div class="metadata-structure-header">'."\n";
            $rendered .= '    <h3>'.htmlentities($this->name).'</h3>'."\n";

            $rendered .= '    <div class="active_state_info"><i class="'.($this->flag_active ? 'icon-ok-circle' : 'icon-ban-circle').'"></i> '.($this->flag_active ? util_lang('active_true') : util_lang('active_false')).'</div>'."\n";

            if ($this->description) {
                $rendered .= '    <div class="description">'.$this->description.'</div>'."\n";
            }

            if ($this->details) {
                $rendered .= '    <div class="details">'.$this->details.'</div>'."\n";
            }

            $rendered .= Metadata_Reference::renderReferencesArrayAsListsView($this->references);

            $rendered .= '  </div>'."\n";

            if ($this->term_set && $this->term_set->matchesDb) {
                $rendered .= '  '.$this->term_set->renderAsViewEmbed();
            } else {
                $rendered .= '<span class="empty-metadata-msg info">'.util_lang('metadata_no_term_set').'</span>';
            }

            $children = $this->getChildren();
            if ($children) {
                $rendered .= '<h4>'.util_lang('metadata_children').':</h4>'."\n";
                $rendered .= '<ul class="metadata-structure-tree">'."\n";
                foreach ($children as $child) {
                    $rendered .= $child->renderAsListTree();
                }
                $rendered .= '</ul>';
            } else {
                $rendered .= '<span class="empty-metadata-msg info">'.util_lang('metadata_no_children').'</span>';
            }

//            if (! $this->term_set && ! $children) {
//                $rendered .= '<span class="empty-metadata-msg info">'.util_lang('metadata_no_children_no_values').'</span>';
//            }

            $rendered .= '</div>';

            return $rendered;
        }

        public function renderAsListItem_Lead($idstr='',$classes_array = [],$other_attribs_hash = []) {
            $li_elt_start = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt_start .= ' '.$this->fieldsAsDataAttribs().'>';
            return $li_elt_start;
        }
        public function renderAsListItem($idstr='',$classes_array = [],$other_attribs_hash = []) {
//            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
//            $li_elt .= ' '.$this->fieldsAsDataAttribs().'>';
            $li_elt = $this->renderAsListItem_Lead($idstr,$classes_array,$other_attribs_hash);
            $li_elt .= $this->renderAsLink();
            $li_elt .= '</li>';
            return $li_elt;
        }

        public function renderAsListTree() {
            $children = $this->getChildren();
            if ($children) {
                $rendered = $this->renderAsListItem_Lead();
                $rendered .= $this->renderAsLink();

                $rendered .= '<ul class="metadata-structure-tree">'."\n";
                foreach ($children as $child) {
                    $rendered .= $child->renderAsListTree();
                }
                $rendered .= '</ul>';

                $rendered .= '</li>'."\n";
                return $rendered;
            } else {
                return $this->renderAsListItem();
            }
        }

        public function renderAsListTreeEditable() {
            $children = $this->getChildren();
            $dom_id = 'item-metadata_structure_'.$this->metadata_structure_id;
            if ($children) {
                $rendered = $this->renderAsListItem_Lead($dom_id,['orderable']);
                $rendered .= util_orderingUpDownControls($dom_id).' ';
                $rendered .= $this->renderAsLink();
//                $rendered .= '<input type="hidden" name="original_ordering-'.$dom_id.'" id="original_ordering-'.$dom_id.'" value="'.$this->ordering.'"/>';
                $rendered .= '<input type="hidden" name="new_ordering-'.$dom_id.'" id="new_ordering-'.$dom_id.'" value="'.$this->ordering.'"/>';
                $rendered .= '<ul class="metadata-structure-tree">'."\n";
                foreach ($children as $child) {
                    $rendered .= $child->renderAsListTree();
                }
                $rendered .= '</ul>';

                $rendered .= '</li>'."\n";
                return $rendered;
            } else {
                $rendered = $this->renderAsListItem_Lead('',['orderable']);
                $rendered .= util_orderingUpDownControls($dom_id).' ';
                $rendered .= $this->renderAsLink();
//                $rendered .= '<input type="hidden" name="original_ordering-'.$dom_id.'" id="original_ordering-'.$dom_id.'" value="'.$this->ordering.'"/>';
                $rendered .= '<input type="hidden" name="new_ordering-'.$dom_id.'" id="new_ordering-'.$dom_id.'" value="'.$this->ordering.'"/>';
                $rendered .= '</li>'."\n";
                return $rendered;
            }
        }

        public function renderAsOptionTree($display_prefix='',$default_selected=0) {
            $children = $this->getChildren();
            if ($children) {
                $rendered = $this->renderAsOption($display_prefix,$default_selected)."\n";
                foreach ($children as $child) {
                    $new_display_prefix = $display_prefix . htmlentities($this->name).' - ';
//                    $rendered .= $child->renderAsOptionTree($display_prefix.'- ',$default_selected);
                    $rendered .= $child->renderAsOptionTree($new_display_prefix,$default_selected);
                }
                return $rendered;
            } else {
                return $this->renderAsOption($display_prefix,$default_selected)."\n";
            }
        }

        public function renderAsEdit() {
            if ($this->metadata_structure_id != 'NEW') {
                $this->loadTermSetAndValues();
                $this->loadReferences();
            }

            //  '.$mds_parent->renderAsLink().' &gt;
            $rendered = '';

            $rendered .= '<form id="form-edit-metadata-structure-base-data" action="'.APP_ROOT_PATH.'/app_code/metadata_structure.php">'."\n";
            $rendered .= '  <input type="hidden" name="action" value="update"/>'."\n";
            $rendered .= '  <input type="hidden" id="metadata_structure_id" name="metadata_structure_id" value="'.$this->metadata_structure_id.'"/>'."\n";

            $rendered .= '  <div id="actions">';
            $rendered .= '<button id="edit-submit-control" class="btn btn-success" type="submit" name="edit-submit-control" value="update"><i class="icon-ok-sign icon-white"></i> '.util_lang((($this->metadata_structure_id != 'NEW') ? 'update' : 'save'),'properize').'</button>'."\n";
            if ($this->metadata_structure_id != 'NEW') {
                $rendered .= '  <a id="edit-cancel-control" class="btn" href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=view&metadata_structure_id='.$this->metadata_structure_id.'"><i class="icon-remove"></i> '.util_lang('cancel','properize').'</a>';
                $rendered .= '  <a id="edit-delete-metadata-structure-control" class="btn btn-danger" href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=delete&metadata_structure_id='.$this->metadata_structure_id.'"><i class="icon-trash icon-white"></i> '.util_lang('delete','properize').'</a>';
            } else {
                $rendered .= '  <a id="edit-cancel-control" class="btn" href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=edit&metadata_structure_id='.$this->metadata_structure_id.'"><i class="icon-remove"></i> '.util_lang('cancel','properize').'</a>';
            }
            $rendered .= '  </div>'."\n";

            $rendered .= '<div id="edit-rendered_metadata_structure_'.$this->metadata_structure_id.'" class="edit-rendered_metadata_structure" '.$this->fieldsAsDataAttribs().'>
  <div class="metadata_lineage"><a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=list">'.util_lang('metadata').'</a> &gt;';
            $lineage = $this->getLineage();
            foreach ($lineage as $mds_ancestor) {
                if ($mds_ancestor->metadata_structure_id != $this->metadata_structure_id) {
                    $rendered .= ' '.$mds_ancestor->renderAsLink().' &gt;';
                }
            }
            $rendered .= '</div>'."\n";


            $rendered .= '  <div class="metadata-parent-controls">'.util_lang('label_metadata_structure_change_parent').': '.Metadata_Structure::renderControlSelectAllMetadataStructures('parent_metadata_structure_id',$this->parent_metadata_structure_id,util_lang('metadata_root_level')).'</div>'."\n";

            $rendered .= '  <div class="metadata-structure-header">'."\n";
            $rendered .= '    <h3><input id="" class="object-name-control" type="text" name="name" value="'.htmlentities($this->name).'"/></h3>'."\n";

            $rendered .= '    <div class="active-state-controls"><input type="checkbox" name="flag_active" value="1"'.($this->flag_active ? ' checked="checked"' : '').'/> '.util_lang('active').'</div>'."\n";

            $rendered .= '    <div class="description-controls"><input title="'.util_lang('title_description').'" class="description-control" type="text" name="description" value="'.htmlentities($this->description).'"/></div>'."\n";
            $rendered .= '    <div class="details-controls"><textarea title="'.util_lang('title_details').'" class="details-control" name="details">'.htmlentities($this->details).'</textarea></div>'."\n";

            if ($this->metadata_structure_id != 'NEW') {
                $rendered .= '    <h4>'.util_lang('metadata_references').'</h4>'."\n";
                $rendered .= Metadata_Reference::renderReferencesArrayAsListsEdit($this->references);
                $rendered .= '  </div>'."\n";
            }

            $rendered .= '  <div class="metadata-term-set-controls"><h4>'.util_lang('metadata_term_set')."</h4>\n".Metadata_Term_Set::renderAllAsSelectControl('',$this->term_set ? $this->term_set->metadata_term_set_id : 0)."</div>\n";

            if ($this->metadata_structure_id != 'NEW') {
                $rendered .= '  <h4>'.util_lang('metadata_children').':</h4>'."\n";
                $rendered .= '  <ul class="metadata-structure-tree">'."\n";
                $rendered .= '    <li><a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=create&parent_metadata_structure_id='.$this->metadata_structure_id.'" id="btn-add-metadata-structure" title="'.htmlentities(util_lang('add_metadata_structure')).'" class="creation_link btn">'.htmlentities(util_lang('add_metadata_structure')).'</a></li>'."\n";
                $children = $this->getChildren();
                if ($children) {
                    foreach ($children as $child) {
                        $rendered .= '    '.$child->renderAsListTreeEditable();
                    }
                }
                $rendered .= '  </ul>';

                if (! $this->term_set && ! $children) {
                    $rendered .= '<span class="empty-metadata-msg info">'.util_lang('metadata_no_children_no_values').'</span>';
                }
            }

            $rendered .= '</div>';

            return $rendered;
        }

	}
