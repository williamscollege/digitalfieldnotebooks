<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class User extends Db_Linked {
		public static $fields = array('user_id', 'created_at', 'updated_at', 'username', 'screen_name', 'flag_is_system_admin', 'flag_is_banned', 'flag_delete');
		public static $primaryKeyField = 'user_id';
		public static $dbTable = 'users';

		public function __construct($initsHash) {
			parent::__construct($initsHash);


			// now do custom stuff
			// e.g. automatically load all accessibility info associated with the user

			//		$this->flag_is_system_admin = false;
			//		$this->flag_is_banned = false;
		}

		public static function cmp($a, $b) {
			if ($a->username == $b->username) {
				if ($a->screen_name == $b->screen_name) {
							return 0;
                }
                return ($a->screen_name < $b->screen_name) ? -1 : 1;
			}
			return ($a->username < $b->username) ? -1 : 1;
		}

        // returns: a very basic HTML representation of the user
        public function renderMinimal() {
            return '<div class="rendered-object user-render user-render-minimal user-render-'.$this->user_id.'" data-for-user="'.$this->user_id.'">'.$this->screen_name.'</div>';
        }

        // returns: an HTML representation of the user with a little extra info available as a mouse-over
        public function render() {
            return '<div class="rendered-object user-render user-render-minimal user-render-'.$this->user_id.'" data-for-user="'.$this->user_id.'">'.$this->screen_name.'</div>';
        }

        // returns: an HTML representation of the user with detailed extra info available in a subsidiary div (so it can be controlled via css
        public function renderRich() {
            $info = '<div class="rendered-object user-render user-render-minimal user-render-'.$this->user_id.'" data-for-user="'.$this->user_id.'">'.$this->screen_name.'</div>';

            return $info;
        }

		public function updateDbFromAuth($auth) {
			//echo "doing db update<br/>\n";
			//$this->refreshFromDb();

			// if we're passed in an array of auth data, convert it to an object
			if (is_array($auth)) {
                if ((! $auth['lastname']) || (! $auth['firstname'])) {
                    return FALSE;
                }
				$a              = new Auth_Base();
				$a->username    = $auth['username'];
                $a->screen_name = $auth['lastname'].', '.$auth['firstname'];
				$auth           = $a;
			} else {
                if ((! $auth->lname) || (! $auth->fname)) {
                    return FALSE;
                }
                $auth->screen_name = $auth->lname.', '.$auth->fname;
            }

			// update info if changed
			if ($this->screen_name != $auth->screen_name) {
				$this->screen_name = $auth->screen_name;
			}

			//User::getOneFromDb(['username'=>$this->username],$this->dbConnection)
			$this->updateDb();
			//echo "TESTUSERIDUPDATED=" . $this->user_id . "<br>";

			return TRUE;
		}


        public function getAccessibleNotebooks() {
            $notebooks = array();

            if ($this->flag_is_system_admin) {
                $notebooks = Notebook::getAllFromDb(['flag_delete' => FALSE],$this->dbConnection);
            } else {
                $notebooks = Notebook::getAllFromDb(['user_id' => $this->user_id],$this->dbConnection);
            }

            return $notebooks;
        }

	}
