<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Authoritative_Plant_Extra extends Db_Linked {
		public static $fields = array('authoritative_plant_extra_id', 'created_at', 'updated_at', 'authoritative_plant_id', 'type', 'value', 'ordering',
                                      'flag_active', 'flag_delete');
		public static $primaryKeyField = 'authoritative_plant_extra_id';
		public static $dbTable = 'authoritative_plant_extras';
        public static $entity_type_label = 'authoritative_plant_extra';

        public static $VALID_TYPES = ['common name', 'description', 'image'];

        public static $SORT_PRIORITIES_FOR_TYPES = ['common name'=>1,'image'=>3,'description'=>2];

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


        public static function createNewAuthoritativePlantExtraFor($type,$authoritative_plant_id,$db_connection) {
            $n = new Authoritative_Plant_Extra([
                'authoritative_plant_extra_id' => 'NEW',
                'created_at' => util_currentDateTimeString_asMySQL(),
                'updated_at' => util_currentDateTimeString_asMySQL(),
                'authoritative_plant_id' => $authoritative_plant_id,
                'type' => $type,
                'value' => '',
                'ordering' => 0,
                'flag_active' => false,
                'flag_delete' => false,
                'DB'=>$db_connection]);
            return $n;
        }

        //----------------------------------------------------------------------------

        public function getAuthoritativePlant() {
            return Authoritative_Plant::getOneFromDb(['authoritative_plant_id' => $this->authoritative_plant_id, 'flag_delete' => FALSE], $this->dbConnection);
        }

        public function renderAsHtml() {
            $rendered = 'UNKNOWN TYPE';

            if ($this->type == 'common name') {
                $rendered = "<div class=\"field-label\">".util_lang('common_name')." : </div><div class=\"field-value taxonomy taxonomy-common-name\">\"".htmlentities($this->value)."\"</div>";
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

        public function renderAsListItem($idstr='',$classes_array = [],$other_attribs_hash = []) {
            array_unshift($classes_array,'authoritative-plant-extra');
//            id=\"authoritative_plant_extra_5101\" data-authoritative_plant_extra_id=\"5101\"
            if (! $idstr) {
                $idstr = 'authoritative_plant_extra_'.$this->authoritative_plant_extra_id;
            }
            $other_attribs_hash['data-authoritative_plant_extra_id'] = $this->authoritative_plant_extra_id;
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= '>';
            $li_elt .= $this->renderAsHtml().'</li>';
            return $li_elt;
        }

        public function renderAsListItemEdit($idstr='',$classes_array = [],$other_attribs_hash = []) {
            array_unshift($classes_array,'authoritative-plant-extra');
            array_unshift($classes_array,'authoritative-plant-extra-edit');
            if (! $idstr) {
                $idstr = 'authoritative_plant_extra_'.$this->authoritative_plant_extra_id;
            }
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().'>';

            $li_elt .= "\n".'  '.util_orderingUpDownControls($idstr)."\n";

            // common frame
            $li_elt .= '  <div class="authoritative-plant-extra embedded">'."\n";
            $li_elt .= '    <div id="form-edit-authoritative-plant-extra-'.$this->authoritative_plant_extra_id.'" class="form-edit-authoritative-plant-extra" data-authoritative_plant_extra_id="'.$this->authoritative_plant_extra_id.'">'."\n";

            // ordering controls here?

            // branch on type
            if ($this->type == 'common name') {
                $li_elt .= '      <div class="field-label">'.util_lang('common_name').' : </div><div class="field-value"><input type="text" name="authoritative_plant_extra-common_name_'.$this->authoritative_plant_extra_id.'" id="authoritative_plant_extra-common_name_'.$this->authoritative_plant_extra_id.'" value="'.htmlentities($this->value).'"/></div>'."\n";
            } elseif ($this->type == 'image') {
                $li_elt .= '      '.$this->renderAsHtml()."\n";
            } elseif ($this->type == 'description') {
                $li_elt .= '      <div class="field-label">'.util_lang('description').' : </div><div class="field-value"><input type="text" name="authoritative_plant_extra-description_'.$this->authoritative_plant_extra_id.'" id="authoritative_plant_extra-description_'.$this->authoritative_plant_extra_id.'" value="'.htmlentities($this->value).'"/></div>'."\n";
            }

            // close common frame
            $li_elt .= '    </div>'."\n";
            $li_elt .= '    <button class="btn btn-danger button-mark-authoritative-plant-extra-for-delete" title="'.util_lang('mark_for_delete','ucfirst').'" data-do-mark-title="'.util_lang('mark_for_delete','ucfirst').'" data-remove-mark-title="'.util_lang('unmark_for_delete','ucfirst').'" data-for_dom_id="'.$idstr.'" data-authoritative_plant_extra_id="'.$this->authoritative_plant_extra_id.'"><i class="icon-remove-sign icon-white"></i></button>'."\n";
            $li_elt .= '  </div>'."\n";

            $li_elt .= '</li>';

            return $li_elt;
        }


	}
