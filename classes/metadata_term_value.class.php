<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Metadata_Term_Value extends Db_Linked {
		public static $fields = array('metadata_term_value_id', 'created_at', 'updated_at', 'metadata_term_set_id', 'name', 'ordering', 'description', 'flag_delete');
		public static $primaryKeyField = 'metadata_term_value_id';
		public static $dbTable = 'metadata_term_values';

        public $references;

        //  NOTE: returns 0 if there is no parent
		public function getMetadataTermSet() {
            return Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => $this->metadata_term_set_id, 'flag_delete' => FALSE], $this->dbConnection);
		}

        public function loadReferences() {
            $this->references = Metadata_References::getAllFromDb(['metadata_type'=>'term_value', 'metadata_id' => $this->metadata_term_value_id, 'flag_delete' => FALSE], $this->dbConnection);
        }
	}
