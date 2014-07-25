<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Authoritative_Plant_Extra extends Db_Linked {
		public static $fields = array('authoritative_plant_extra_id', 'created_at', 'updated_at', 'authoritative_plant_id', 'type', 'value', 'ordering',
                                      'flag_delete');
		public static $primaryKeyField = 'authoritative_plant_extra_id';
		public static $dbTable = 'authoritative_plant_extras';

        public static $VALID_TYPES = ['common name', 'description', 'image'];

        public function getAuthoritativePlant() {
            return Authoritative_Plant::getOneFromDb(['authoritative_plant_id' => $this->authoritative_plant_id, 'flag_delete' => FALSE], $this->dbConnection);
        }
	}
