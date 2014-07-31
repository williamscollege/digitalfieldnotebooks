<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Notebook extends Db_Linked {
		public static $fields = array('notebook_id', 'created_at', 'updated_at', 'user_id', 'name', 'notes', 'flag_workflow_published', 'flag_workflow_validated', 'flag_delete');
		public static $primaryKeyField = 'notebook_id';
		public static $dbTable = 'notebooks';

		public function __construct($initsHash) {
			parent::__construct($initsHash);


			// now do custom stuff
			// e.g. automatically load all accessibility info associated with the user
            $this->flag_workflow_published = false;
            $this->flag_workflow_validated = false;
		}

		public static function cmp($a, $b) {
			if ($a->name == $b->name) {
                if ($a->user_id == $b->user_id) {
                    return 0;
                }
                $ua = User::getOneFromDb(['user_id' => $a->user_id], $a->dbConnection);
                $ub = User::getOneFromDb(['user_id' => $b->user_id], $b->dbConnection);
                return User::cmp($ua,$ub);
			}
			return ($a->name < $b->name) ? -1 : 1;
		}

	}
