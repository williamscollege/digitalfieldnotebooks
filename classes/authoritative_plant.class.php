<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Authoritative_Plant extends Db_Linked {
		public static $fields = array('authoritative_plant_id', 'created_at', 'updated_at',
                                      'class', 'order', 'family', 'genus', 'species', 'variety',
                                      'catalog_identifier', 'flag_delete');
		public static $primaryKeyField = 'authoritative_plant_id';
		public static $dbTable = 'authoritative_plants';

        public $extras;

        public function __construct($initsHash) {
            parent::__construct($initsHash);


            // now do custom stuff
            // e.g. automatically load all accessibility info associated with the user

            $this->extras = array();
        }

        public static function cmp($a, $b) {
            if ($a->class == $b->class) {
                if ($a->order == $b->order) {
                    if ($a->family == $b->family) {
                        if ($a->genus == $b->genus) {
                            if ($a->species == $b->species) {
                                if ($a->variety == $b->variety) {
                                        return 0;
                                }
                                return ($a->variety < $b->variety) ? -1 : 1;
                            }
                            return ($a->species < $b->species) ? -1 : 1;
                        }
                        return ($a->genus < $b->genus) ? -1 : 1;
                    }
                    return ($a->family < $b->family) ? -1 : 1;
                }
                return ($a->order < $b->order) ? -1 : 1;
            }
            return ($a->class < $b->class) ? -1 : 1;
        }

        public function loadExtras() {
            $this->extras = Authoritative_Plant_Extra::getAllFromDb(['authoritative_plant_id' => $this->authoritative_plant_id, 'flag_delete' => FALSE], $this->dbConnection);
            usort($this->extras,'Authoritative_Plant_Extra::cmp');
        }

        public function cacheExtras() {
            if (! $this->extras) {
                $this->loadExtras();
            }
        }

        public function renderAsShortText() {
            $this->cacheExtras();
            $text = ucfirst(strtolower($this->genus)).' '.strtolower($this->species);
            if ($this->variety) {
                $text .= " '".$this->variety."'";
            }
            foreach ($this->extras as $extra) {
                if ($extra->type == 'common name') {
                    $text .= ' ("'.$extra->value.'")';
                    break;
                }
            }
            if ($this->catalog_identifier) {
                $text .= ' ['.$this->catalog_identifier.']';
            }
            return $text;
        }

        public function renderAsListItem($idstr='',$classes_array = [],$other_attribs_hash = []) {
            global $USER,$ACTIONS;
            $actions_attribs = '';

            if ($USER->canActOnTarget($ACTIONS['edit'],$this)) {
                $actions_attribs .= ' data-can-edit="1"';
            }
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().$actions_attribs.'>';
            $li_elt .= '<a href="/app_code/authoritative_plant.php?authoritative_plant_id='.$this->authoritative_plant_id.'">'.htmlentities($this->renderAsShortText()).'</a></li>';
            return $li_elt;
        }

        public function renderAsViewEmbed() {
            return 'to be implemented';
        }
    }