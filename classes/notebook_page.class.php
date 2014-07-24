<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Notebook_Page extends Db_Linked {
		public static $fields = array('notebook_page_id', 'created_at', 'updated_at', 'notebook_id', 'authoritative_plant_id', 'notes', 'flag_delete');
		public static $primaryKeyField = 'notebook_page_id';
		public static $dbTable = 'notebook_pages';

        public $page_fields;

		public function __construct($initsHash) {
			parent::__construct($initsHash);


			// now do custom stuff
			// e.g. automatically load all accessibility info associated with the user

			//		$this->flag_is_system_admin = false;
			//		$this->flag_is_banned = false;
		}

		public static function cmp($a, $b) {
            return 0;
		}

        public function loadPageFields() {
            $this->page_fields = Notebook_Page_Field::getAllFromDb(['notebook_page_id' => $this->notebook_page_id,'flag_delete' => FALSE],$this->dbConnection);
        }
	}
