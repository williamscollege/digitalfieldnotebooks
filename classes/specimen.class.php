<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Specimen extends Db_Linked {
		public static $fields = array('specimen_id', 'created_at', 'updated_at', 'user_id', 'link_to_type', 'link_to_id',
                                      'name', 'gps_x', 'gps_y', 'notes', 'ordering', 'catalog_identifier',
                                      'flag_workflow_published', 'flag_workflow_validated', 'flag_delete');
		public static $primaryKeyField = 'specimen_id';
		public static $dbTable = 'specimens';

        public static $VALID_LINK_TO_TYPES =  ['authoritative_plant', 'notebook_page'];

        public static $SORT_PRIORITIES_FOR_LINK_TO_TYPES = ['authoritative_plant'=>1,'notebook_page'=>2];

        public $images;

		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with the user
            $this->images = array();
            $this->flag_workflow_published = false;
            $this->flag_workflow_validated = false;
		}

		public static function cmp($a, $b) {
            if (Specimen::$SORT_PRIORITIES_FOR_LINK_TO_TYPES[$a->link_to_type] == Specimen::$SORT_PRIORITIES_FOR_LINK_TO_TYPES[$b->link_to_type]) {
                if ($a->user_id == $b->user_id) {
                    if ($a->link_to_id == $b->link_to_id) {
                        if ($a->ordering == $b->ordering) {
                            if ($a->name == $b->name) {
                                if ($a->catalog_identifier == $b->catalog_identifier) {
                                    return 0;
                                }
                                return ($a->catalog_identifier < $b->catalog_identifier) ? -1 : 1;
                            }
                            return ($a->name < $b->name) ? -1 : 1;
                        }
                        return ($a->ordering < $b->ordering) ? -1 : 1;
                    }
                    if ($a->link_to_type == 'authoritative_plant') {
                        return Authoritative_Plant::cmp($a->getLinked(),$b->getLinked());
                    }
                    if ($a->link_to_type == 'notebook_page') {
                        return Notebook_Page::cmp($a->getLinked(),$b->getLinked());
                    }
                    return 0;
                }
                return User::cmp($a->getUser(),$b->getUser());
            }
            return (Specimen::$SORT_PRIORITIES_FOR_LINK_TO_TYPES[$a->link_to_type] < Specimen::$SORT_PRIORITIES_FOR_LINK_TO_TYPES[$b->link_to_type]) ? -1 : 1;
		}


        public function loadImages() {
            $this->images = Specimen_Image::getAllFromDb(['specimen_id' => $this->specimen_id, 'flag_delete' => FALSE],$this->dbConnection);
            usort($this->images,'Specimen_Image::cmp');
        }


        public function getUser() {
            return User::getOneFromDb(['user_id'=>$this->user_id],$this->dbConnection);
        }


        public function getLinked() {
            if ($this->link_to_type == 'authoritative_plant') {
                return Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>$this->link_to_id],$this->dbConnection);
            }
            if ($this->link_to_type == 'notebook_page') {
                return Notebook_Page::getOneFromDb(['notebook_page_id'=>$this->link_to_id],$this->dbConnection);
            }

            return 0;
        }
	}
