<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Specimen_Image extends Db_Linked {
		public static $fields = array('specimen_image_id', 'created_at', 'updated_at', 'specimen_id', 'user_id',
                                      'image_reference', 'ordering',
                                      'flag_workflow_published', 'flag_workflow_validated', 'flag_delete');
		public static $primaryKeyField = 'specimen_image_id';
		public static $dbTable = 'specimen_images';

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


        public function getSpecimen() {
            return Specimen::getOneFromDb(['specimen_id' => $this->specimen_id,, 'flag_delete' => FALSE],$this->dbConnection);
        }

	}
