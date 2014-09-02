<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Metadata_Term_Value extends Db_Linked {
		public static $fields = array('metadata_term_value_id', 'created_at', 'updated_at', 'metadata_term_set_id', 'name', 'ordering', 'description', 'flag_delete');
		public static $primaryKeyField = 'metadata_term_value_id';
		public static $dbTable = 'metadata_term_values';

        public $references;

        public function __construct($initsHash) {
            parent::__construct($initsHash);


            // now do custom stuff
            // e.g. automatically load all accessibility info associated with the user
            $this->references = array();
        }

        public static function cmp($a, $b) {
            if ($a->metadata_term_set_id == $b->metadata_term_set_id) {
                if ($a->ordering == $b->ordering) {
                    if ($a->name == $b->name) {
                        return 0;
                    }
                    return ($a->name < $b->name) ? -1 : 1;
                }
                return ($a->ordering < $b->ordering) ? -1 : 1;
            }
            return ($a->metadata_term_set_id < $b->metadata_term_set_id) ? -1 : 1;
        }

        //  NOTE: returns 0 if there is no parent
		public function getMetadataTermSet() {
            return Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => $this->metadata_term_set_id, 'flag_delete' => FALSE], $this->dbConnection);
		}

        public function loadReferences() {
            $this->references = Metadata_Reference::getAllFromDb(['metadata_type'=>'term_value', 'metadata_id' => $this->metadata_term_value_id, 'flag_delete' => FALSE], $this->dbConnection);
            usort($this->references,'Metadata_Reference::cmp');
        }

        public function renderAsHtml() {
            $rendered = '<span class="term_value" title="'.htmlentities($this->description).'">'.$this->name.'</span>';
            $this->loadReferences();
            if (count($this->references) > 0) {
                $rendered .= '<ul class="metadata_references">';
                foreach ($this->references as $r) {
                    $rendered .= '<li>'.$r->renderAsViewEmbed().'</li>';
                }
                $rendered .= '</ul>';
            }
            return $rendered;
        }

        public function renderAsListItem($idstr='',$classes_array = [],$other_attribs_hash = []) {
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().'>';

//            $li_elt .= '<span class="term_value" title="'.htmlentities($this->description).'">'.$this->name.'</span>';
//            $this->loadReferences();
//            if (count($this->references) > 0) {
//                $li_elt .= '<ul class="metadata_references">';
//                foreach ($this->references as $r) {
//                    $li_elt .= '<li>'.$r->renderAsViewEmbed().'</li>';
//                }
//                $li_elt .= '</ul>';
//            }
            $li_elt .= $this->renderAsHtml();
            $li_elt .= '</li>';
            return $li_elt;
        }
	}
