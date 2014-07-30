<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Metadata_Reference extends Db_Linked {
		public static $fields = array('metadata_reference_id', 'created_at', 'updated_at', 'metadata_type', 'metadata_id', 'type', 'external_reference', 'description', 'ordering', 'flag_delete');
		public static $primaryKeyField = 'metadata_reference_id';
		public static $dbTable = 'metadata_references';

        public static $VALID_METADATA_TYPES = ['structure', 'term_set', 'term_value'];
        public static $VALID_TYPES = ['text', 'image', 'link'];

        public static $SORT_PRIORITIES_FOR_METADATA_TYPES = ['structure'=>1,'term_set'=>2,'term_value'=>3];
        public static $SORT_PRIORITIES_FOR_TYPES = ['image'=>1,'link'=>2,'text'=>3];

        public static function cmp($a, $b) {
            if (Metadata_Reference::$SORT_PRIORITIES_FOR_METADATA_TYPES[$a->metadata_type] == Metadata_Reference::$SORT_PRIORITIES_FOR_METADATA_TYPES[$b->metadata_type]) {
                if ($a->metadata_id == $b->metadata_id) {
                    if (Metadata_Reference::$SORT_PRIORITIES_FOR_TYPES[$a->type] == Metadata_Reference::$SORT_PRIORITIES_FOR_TYPES[$b->type]) {
                        if ($a->ordering == $b->ordering) {
                            if ($a->external_reference == $b->external_reference) {
                                return 0;
                            }
                            return ($a->external_reference < $b->external_reference) ? -1 : 1;
                        }
                        return ($a->ordering < $b->ordering) ? -1 : 1;
                    }
                    return (Metadata_Reference::$SORT_PRIORITIES_FOR_TYPES[$a->type] < Metadata_Reference::$SORT_PRIORITIES_FOR_TYPES[$b->type]) ? -1 : 1;
                }
                return ($a->metadata_id < $b->metadata_id) ? -1 : 1;
            }
            return (Metadata_Reference::$SORT_PRIORITIES_FOR_METADATA_TYPES[$a->metadata_type] < Metadata_Reference::$SORT_PRIORITIES_FOR_METADATA_TYPES[$b->metadata_type]) ? -1 : 1;
        }

		public function getReferrent() {
            if ($this->metadata_type == 'structure') {
                return Metadata_Structure::getOneFromDb(['metadata_structure_id' => $this->metadata_id, 'flag_delete' => FALSE], $this->dbConnection);
            } elseif ($this->metadata_type == 'term_set') {
                return Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => $this->metadata_id, 'flag_delete' => FALSE], $this->dbConnection);
            } elseif ($this->metadata_type == 'term_value') {
                return Metadata_Term_Value::getOneFromDb(['metadata_term_value_id' => $this->metadata_id, 'flag_delete' => FALSE], $this->dbConnection);
            } else {
                return 'UNKNOWN METADATA_TYPE: /'.$this->metadata_type.'/';
            }
		}
	}
