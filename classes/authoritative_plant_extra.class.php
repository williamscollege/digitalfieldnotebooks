<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Authoritative_Plant_Extra extends Db_Linked {
		public static $fields = array('authoritative_plant_extra_id', 'created_at', 'updated_at', 'authoritative_plant_id', 'type', 'value', 'ordering',
                                      'flag_delete');
		public static $primaryKeyField = 'authoritative_plant_extra_id';
		public static $dbTable = 'authoritative_plant_extras';

        public static $VALID_TYPES = ['common name', 'description', 'image'];

        public static $SORT_PRIORITIES_FOR_TYPES = ['common name'=>1,'image'=>2,'description'=>3];

        public static function cmp($a, $b) {
                if ($a->authoritative_plant_id == $b->authoritative_plant_id) {
                    if (Authoritative_Plant_Extra::$SORT_PRIORITIES_FOR_TYPES[$a->type] == Authoritative_Plant_Extra::$SORT_PRIORITIES_FOR_TYPES[$b->type]) {
                        if ($a->ordering == $b->ordering) {
                            if ($a->value == $b->value) {
                                return 0;
                            }
                            return ($a->value < $b->value) ? -1 : 1;
                        }
                        return ($a->ordering < $b->ordering) ? -1 : 1;
                    }
                    return (Authoritative_Plant_Extra::$SORT_PRIORITIES_FOR_TYPES[$a->type] < Authoritative_Plant_Extra::$SORT_PRIORITIES_FOR_TYPES[$b->type]) ? -1 : 1;
                }
                return ($a->authoritative_plant_id < $b->authoritative_plant_id) ? -1 : 1;
        }

        public function getAuthoritativePlant() {
            return Authoritative_Plant::getOneFromDb(['authoritative_plant_id' => $this->authoritative_plant_id, 'flag_delete' => FALSE], $this->dbConnection);
        }

        public function renderAsHtml() {
            $rendered = 'UNKNOWN TYPE';

            if ($this->type == 'common name') {
                $rendered = "<span class=\"taxonomy-common-name\">'".htmlentities($this->value)."'</span>";
            } elseif ($this->type == 'image') {
                if (preg_match('/^http/i',$this->value)) {
                    // NOTE: external references are NOT sanitized! That is beyond the security scope of this app (i.e. only pre-trusted users have data entry privs)
                    $rendered = '<img class="plant-image external-reference" src="'. $this->value.'"/>';
                } else {
                    $rendered = '<img class="plant-image" src="'.APP_ROOT_PATH.'/image_data/authoritative/'. util_sanitizeFileReference($this->value).'"/>';
                }
            } elseif ($this->type == 'description') {
                $rendered = "<div class=\"plant-description\">".htmlentities($this->value)."</div>";
            }

            return $rendered;
        }
	}
