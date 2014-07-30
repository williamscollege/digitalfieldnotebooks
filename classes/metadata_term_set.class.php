<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Metadata_Term_Set extends Db_Linked {
		public static $fields = array('metadata_term_set_id', 'created_at', 'updated_at', 'name', 'ordering', 'description', 'flag_delete');
		public static $primaryKeyField = 'metadata_term_set_id';
		public static $dbTable = 'metadata_term_sets';

        public $term_values;
        public $references;

        public function __construct($initsHash) {
            parent::__construct($initsHash);


            // now do custom stuff
            // e.g. automatically load all accessibility info associated with the user

            $this->term_values = array();
            $this->references = array();
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

        public function loadReferences() {
            $this->references = Metadata_Reference::getAllFromDb(['metadata_type'=>'term_set', 'metadata_id' => $this->metadata_term_set_id, 'flag_delete' => FALSE], $this->dbConnection);
            usort($this->references,'Metadata_Reference::cmp');
        }

    }
