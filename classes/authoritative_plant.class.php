<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Authoritative_Plant extends Db_Linked {
		public static $fields = array('authoritative_plant_id', 'created_at', 'updated_at',
                                      'class', 'order', 'family', 'genus', 'species', 'variety',
                                      'catalog_identifier', 'flag_delete');
		public static $primaryKeyField = 'authoritative_plant_id';
		public static $dbTable = 'authoritative_plants';

        public $extras;

        public function loadExtras() {
            $this->extras = Authoritative_Plant_Extra::getAllFromDb(['authoritative_plant_id' => $this->authoritative_plant_id, 'flag_delete' => FALSE], $this->dbConnection);
        }
	}
