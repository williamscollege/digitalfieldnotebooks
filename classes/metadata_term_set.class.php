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

        public static function cmp($a, $b) {
            if ($a->ordering == $b->ordering) {
                if ($a->name == $b->name) {
                    return 0;
                }
                return ($a->name < $b->name) ? -1 : 1;
            }
            return ($a->ordering < $b->ordering) ? -1 : 1;
        }

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

            $rendered = '<div class="metadata-term-set-header"><a class="metadata_term_set_name_link" data-metadata_term_set_id="'.$this->metadata_term_set_id.'">'.htmlentities($this->name).'</a>';
//            $rendered .= '<ul class="metadata-references">';
//            foreach ($this->references as $r) {
//                $rendered .= '<li>'.$r->renderAsViewEmbed().'</li>';
//            }
//            $rendered .= '</ul></div>';
//            $rendered .= '<ul class="metadata-term-values">';
//            foreach ($this->term_values as $tv) {
//                $rendered .= $tv->renderAsListItem();
//            }
//            $rendered .= '</ul>';
            $rendered .= $this->renderAsHtml_references();
            $rendered .= '</div>';
            $rendered .= $this->renderAsHtml_term_values();
            $rendered .= $this->renderAsHtml_structures();

            return $rendered;

        }

        public function renderAsHtml_references() {
            $this->cacheReferences();

            $rendered = '<ul class="metadata-references">';
            foreach ($this->references as $r) {
                $rendered .= '<li>'.$r->renderAsViewEmbed().'</li>';
            }
            $rendered .= '</ul>';

            return $rendered;
        }

        public function renderAsHtml_term_values() {
            $this->cacheTermValues();

            $rendered = '<ul class="metadata-term-values">';
            foreach ($this->term_values as $tv) {
                $rendered .= $tv->renderAsListItem();
            }
            $rendered .= '</ul>';

            return $rendered;
        }

        public function renderAsHtml_structures() {
            $this->cacheStructures();

            $rendered = '<div class="metadata-term-set-uses">';
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
            $li_elt .= $this->renderAsHtml();
            $li_elt .= '</li>';
            return $li_elt;
        }

        public function renderAsViewEmbed() {
            $rendered = '<div id="rendered_metadata_term_set_'.$this->metadata_term_set_id.'" class="rendered-metadata-term-set" '.$this->fieldsAsDataAttribs().'>';
            $rendered .= $this->renderAsHtml();
            $rendered .= '</div>';
            return $rendered;
        }

        public function renderAsView() {
            $this->cacheReferences();
            $this->cacheTermValues();

            $rendered = '<div class="metadata-term-set-header"><a href="'.APP_ROOT_PATH.'/app_code/metadata_term_set.php?action=list">'.util_lang('all_metadata_term_sets').'</a> &gt; '.htmlentities($this->name).'</div>';
            $rendered .= $this->renderAsHtml_references();
            $rendered .= $this->renderAsHtml_term_values();
            $rendered .= $this->renderAsHtml_structures();

            return $rendered;
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
            $rendered .= '  <option value="-1">-- nothing from the list --</option>'."\n";
            foreach ($this->term_values as $v) {
                $rendered .= '  '.$v->renderAsOption($v->metadata_term_value_id == $default_checked_value_id)."\n";
            }
            $rendered .= '</select>';

            return $rendered;
        }
    }
