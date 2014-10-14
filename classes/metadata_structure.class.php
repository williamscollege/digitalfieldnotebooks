<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Metadata_Structure extends Db_Linked {
		public static $fields = array('metadata_structure_id', 'created_at', 'updated_at', 'parent_metadata_structure_id', 'name', 'ordering', 'description', 'details', 'metadata_term_set_id', 'flag_delete');
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


        function renderAsLink($action='view') {
            $action = Action::sanitizeAction($action);

            $link = '<a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action='.$action.'&metadata_structure_id='.$this->metadata_structure_id.'">'.htmlentities($this->name).'</a>';

            return $link;
        }

        public function renderAsButtonEdit() {
            $btn = '<a id="btn-edit" href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=edit&metadata_structure_id='.$this->metadata_structure_id.'" class="edit_link btn" >'.util_lang('edit').'</a>';
            return $btn;
        }

        public function renderAsView() {
            $this->loadTermSetAndValues();
            $this->loadReferences();

            //  '.$mds_parent->renderAsLink().' &gt;

            $rendered = '<div id="rendered_metadata_structure_'.$this->metadata_structure_id.'" class="rendered_metadata_structure" '.$this->fieldsAsDataAttribs().'>
  <div class="metadata_lineage"><a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=list">metadata</a> &gt;';
            $lineage = $this->getLineage();
            foreach ($lineage as $mds_ancestor) {
                if ($mds_ancestor->metadata_structure_id != $this->metadata_structure_id) {
                    $rendered .= ' '.$mds_ancestor->renderAsLink().' &gt;';
                }
            }
            $rendered .= '</div>'."\n".
'  <div class="metadata-structure-header">'.ucfirst(util_lang('metadata')).' : '.htmlentities($this->name);
            $rendered .= '<ul class="metadata-references">';
            foreach ($this->references as $r) {
                $rendered .= '<li>'.$r->renderAsViewEmbed().'</li>';
            }
            $rendered .= '</ul></div>'."\n";
            if ($this->description) {
                $rendered .= '  <div class="description">'.$this->description.'</div>'."\n";
            }
            if ($this->details) {
                $rendered .= '  <div class="details">'.$this->details.'</div>'."\n";
            }
            if ($this->term_set) {
                $rendered .= '  '.$this->term_set->renderAsViewEmbed();
            } else {
                $children = $this->getChildren();
                if ($children) {
                    $rendered .= '<ul class="metadata-structure-tree">'."\n";
                    foreach ($children as $child) {
                        $rendered .= $child->renderAsListTree();
                    }
                    $rendered .= '</ul>';
                } else {
                    $rendered .= '<span class="info">'.util_lang('metadata_no_children_no_values').'</span>';
                }
            }

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

                $rendered .= '</li>';
                return $rendered;
            } else {
                return $this->renderAsListItem();
            }
        }

	}
