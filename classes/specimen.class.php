<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Specimen extends Db_Linked {
		public static $fields = array('specimen_id', 'created_at', 'updated_at', 'user_id', 'link_to_type', 'link_to_id',
                                      'name', 'gps_x', 'gps_y', 'notes', 'ordering', 'catalog_identifier',
                                      'flag_workflow_published', 'flag_workflow_validated', 'flag_delete');
		public static $primaryKeyField = 'specimen_id';
		public static $dbTable = 'specimens';

        public static $VALID_LINK_TO_TYPES =  ['authoritative_plant', 'notebook_page'];

        public $images;

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


        public function loadImages() {
            $this->images = Specimen_Image::getAllFromDb(['specimen_id' => $this->specimen_id, 'flag_delete' => FALSE],$this->dbConnection);
        }

	}
