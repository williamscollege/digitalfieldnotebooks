<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Metadata_Structure extends Db_Linked {
		public static $fields = array('metadata_structure_id', 'created_at', 'updated_at', 'parent_metadata_structure_id', 'name', 'ordering', 'description', 'details', 'metadata_term_set_id', 'flag_delete');
		public static $primaryKeyField = 'metadata_structure_id';
		public static $dbTable = 'metadata_structures';

        public $references;

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

            // trim off matching ancestories
            while ($lineageA[0]->metadata_structure_id == $lineageB[0]->metadata_structure_id) {
                array_pop($lineageA);
                array_pop($lineageB);
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

        // returns: the structure at the root of the structure tree (itself for structures with no parent, otherwise the most distant ancestor)
        public function getLineage() {
            if ($this->parent_metadata_structure_id == 0) {
                return [$this];
            }
            $parent = Metadata_Structure::getOneFromDb(['metadata_structure_id' => $this->parent_metadata_structure_id, 'flag_delete' => FALSE], $this->dbConnection);
            return array_merge($parent->getLineage(),[$this]);
        }

        public function getChildren() {
            return Metadata_Structure::getAllFromDb(['parent_metadata_structure_id' => $this->metadata_structure_id, 'flag_delete' => FALSE], $this->dbConnection);
        }

        public function loadReferences() {
            $this->references = Metadata_References::getAllFromDb(['metadata_type'=>'term_set', 'metadata_id' => $this->metadata_term_value_id, 'flag_delete' => FALSE], $this->dbConnection);
        }
	}
