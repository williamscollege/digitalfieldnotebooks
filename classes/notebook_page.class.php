<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Notebook_Page extends Db_Linked {
		public static $fields = array('notebook_page_id', 'created_at', 'updated_at', 'notebook_id', 'authoritative_plant_id', 'notes', 'flag_workflow_published', 'flag_workflow_validated', 'flag_delete');
		public static $primaryKeyField = 'notebook_page_id';
		public static $dbTable = 'notebook_pages';

        public $page_fields;

		public function __construct($initsHash) {
			parent::__construct($initsHash);


			// now do custom stuff
			// e.g. automatically load all accessibility info associated with the user
            $this->flag_workflow_published = false;
            $this->flag_workflow_validated = false;
            $this->page_fields = array();
		}

		public static function cmp($a, $b) {
            if ($a->notebook_id == $b->notebook_id) {
//                echo "auth plant cmp";
                return Authoritative_Plant::cmp($a->getAuthoritativePlant(),$b->getAuthoritativePlant());
            }
//            echo "notebook cmp";
            return Notebook::cmp($a->getNotebook(),$b->getNotebook());
        }

        public function loadPageFields() {
            $this->page_fields = Notebook_Page_Field::getAllFromDb(['notebook_page_id' => $this->notebook_page_id,'flag_delete' => FALSE],$this->dbConnection);
            usort($this->page_fields,'Notebook_Page_Field::cmp');
        }

        public function getNotebook() {
            return Notebook::getOneFromDb(['notebook_id'=>$this->notebook_id],$this->dbConnection);
        }

        public function getAuthoritativePlant() {
            return Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>$this->authoritative_plant_id],$this->dbConnection);
        }

    }
