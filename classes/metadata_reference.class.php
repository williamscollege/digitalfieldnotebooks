<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Metadata_Reference extends Db_Linked {
		public static $fields = array('metadata_reference_id', 'created_at', 'updated_at', 'metadata_type', 'metadata_id', 'type', 'external_reference', 'description', 'ordering', 'flag_delete');
		public static $primaryKeyField = 'metadata_reference_id';
		public static $dbTable = 'metadata_references';

        public static $VALID_METADATA_TYPES = ['structure', 'set', 'value'];
        public static $VALID_TYPES = ['text', 'image', 'link'];

		public function getReferrent() {
            if ($this->metadata_type == 'structure') {
                return Metadata_Structure::getOneFromDb(['metadata_structure_id' => $this->metadata_id, 'flag_delete' => FALSE], $this->dbConnection);
            } elseif ($this->metadata_type == 'term_set') {
                return Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => $this->metadata_id, 'flag_delete' => FALSE], $this->dbConnection);
            } elseif ($this->metadata_type == 'term_value') {
                return Metadata_Term_Value::getOneFromDb(['metadata_term_value_id' => $this->metadata_id, 'flag_delete' => FALSE], $this->dbConnection);
            } else {
                return 'UNKNOWN METADATA_TYPE: ['.$this->metadata_type.']';
            }
		}
	}
