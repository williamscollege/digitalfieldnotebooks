<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Action extends Db_Linked {
		public static $fields = array('action_id', 'name', 'flag_delete');
		public static $primaryKeyField = 'action_id';
		public static $dbTable = 'actions';

		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with the user

			//		$this->flag_is_system_admin = false;
			//		$this->flag_is_banned = false;
		}

		public static function cmp($a, $b) {
			if ($a->name == $b->name) {
                return 0;
			}
			return ($a->name < $b->name) ? -1 : 1;
		}

	}
