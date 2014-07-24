<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Role_Action_Target extends Db_Linked {
		public static $fields = array('role_action_target_link_id', 'created_at', 'updated_at', 'last_user_id', 'role_id', 'action_id', 'target_type', 'target_id', 'flag_delete');
		public static $primaryKeyField = 'role_action_target_link_id';
		public static $dbTable = 'role_action_target_links';

		// instance attributes
        public $role = '';
        public $action = '';
        public $target = '';

        // NOTE: roles are basically fixed; role_id of 1 corresponds to manager, 2 to assistant, 3 to field user, and 4 to public
		public function loadRole() {
			$this->role = Role::getOneFromDb(['role_id' => $this->role_id, 'flag_delete' => FALSE], $this->dbConnection);
		}

        public function loadAction() {
            $this->action = Action::getOneFromDb(['action_id' => $this-action_id, 'flag_delete' => FALSE], $this->dbConnection);
        }

        public function loadTarget() {
            // TODO: implement this! - need to branch appropriately on target type
        }
	}
