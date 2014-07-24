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
            return 0;
		}

	}
