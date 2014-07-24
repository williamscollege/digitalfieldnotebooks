<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Metadata_Structure extends Db_Linked {
		public static $fields = array('metadata_structure_id', 'created_at', 'updated_at', 'parent_metadata_structure_id', 'name', 'ordering', 'description', 'details', 'metadata_term_set_id', 'flag_delete');
		public static $primaryKeyField = 'metadata_structure_id';
		public static $dbTable = 'metadata_structures';

        public $references;

        //  NOTE: returns 0 if there is no parent
		public function getParent() {
            if ($this->parent_metadata_structure_id > 0) {
                return Metadata_Structure::getOneFromDb(['metadata_structure_id' => $this->parent_metadata_structure_id, 'flag_delete' => FALSE], $this->dbConnection);
            }
            return 0;
		}

        public function getChildren() {
            return Metadata_Structure::getAllFromDb(['parent_metadata_structure_id' => $this->metadata_structure_id, 'flag_delete' => FALSE], $this->dbConnection);
        }

        public function loadReferences() {
            $this->references = Metadata_References::getAllFromDb(['metadata_type'=>'term_set', 'metadata_id' => $this->metadata_term_value_id, 'flag_delete' => FALSE], $this->dbConnection);
        }
	}
