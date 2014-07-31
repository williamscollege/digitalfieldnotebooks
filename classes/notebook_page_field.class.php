<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Notebook_Page_Field extends Db_Linked {
		public static $fields = array('notebook_page_field_id', 'created_at', 'updated_at',
                                      'notebook_page_id', 'label_metadata_structure_id', 'value_metadata_term_value_id', 'value_open', 'flag_delete');
		public static $primaryKeyField = 'notebook_page_field_id';
		public static $dbTable = 'notebook_page_fields';

		public function __construct($initsHash) {
			parent::__construct($initsHash);


			// now do custom stuff
			// e.g. automatically load all accessibility info associated with the user

			//		$this->flag_is_system_admin = false;
			//		$this->flag_is_banned = false;
		}

		public static function cmp($a, $b) {
            if ($a->notebook_page_id == $b->notebook_page_id) {
                if ($a->label_metadata_structure_id == $b->label_metadata_structure_id) {
                    if ($a-> value_metadata_term_value_id && $b->value_metadata_term_value_id) {
                        return Metadata_Structure::cmp($a->getMetadataTermValue(),$b->getMetadataTermValue());
                    } else {
                        return strcmp($a->value_open,$b->value_open);
                    }
                }
                return Metadata_Structure::cmp($a->getMetadataStructure(),$b->getMetadataStructure());
            }
            return Notebook_Page::cmp($a->getNotebookPage(),$b->getNotebookPage());
        }

        //------------------------------------------

        public function getNotebookPage() {
            return Notebook_Page::getOneFromDb(['notebook_page_id'=>$this->notebook_page_id],$this->dbConnection);
        }

        public function getMetadataStructure() {
            return Metadata_Structure::getOneFromDb(['metadata_structure_id'=>$this->label_metadata_structure_id],$this->dbConnection);
        }

        public function getMetadataTermValue() {
            return Metadata_Term_Value::getOneFromDb(['metadata_term_value_id'=>$this->value_metadata_term_value_id],$this->dbConnection);
        }

	}
