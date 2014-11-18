<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Specimen_Image extends Db_Linked {
		public static $fields = array('specimen_image_id', 'created_at', 'updated_at', 'specimen_id', 'user_id',
                                      'image_reference', 'ordering',
                                      'flag_workflow_published', 'flag_workflow_validated', 'flag_delete');
		public static $primaryKeyField = 'specimen_image_id';
		public static $dbTable = 'specimen_images';
        public static $entity_type_label = 'specimen_image';

        public $images;

		public function __construct($initsHash) {
			parent::__construct($initsHash);


			// now do custom stuff
			// e.g. automatically load all accessibility info associated with the user

			//		$this->flag_is_system_admin = false;
			//		$this->flag_is_banned = false;
		}

		public static function cmp($a, $b) {
            if ($a->specimen_id == $b->specimen_id) {
                if ($a->ordering == $b->ordering) {
                    return strcmp($a->image_reference,$b->image_reference);
                }
                return ($a->ordering < $b->ordering) ? -1 : 1;
            }
            return Specimen::cmp($a->getSpecimen(),$b->getSpecimen());
		}


        public function getSpecimen() {
            return Specimen::getOneFromDb(['specimen_id' => $this->specimen_id, 'flag_delete' => FALSE],$this->dbConnection);
        }

        public static function createNewSpecimenImageForSpecimen($specimen_id,$db_connection) {
            global $USER;
            $s = new Specimen_Image([
                'specimen_image_id' => 'NEW',
                'created_at' => util_currentDateTimeString_asMySQL(),
                'updated_at' => util_currentDateTimeString_asMySQL(),
                'specimen_id' => $specimen_id,
                'user_id' => $USER->user_id,
                'image_reference' => '',
                'ordering' => 0,
                'flag_workflow_published' => false,
                'flag_workflow_validated' => false,
                'flag_delete' => false,
                'DB'=>$db_connection]);
            return $s;
        }

        //-----------------------------------------------------------------------------------

        public function renderAsHtml() {
            $rendered = '';

            if (preg_match('/^http/i',$this->image_reference)) {
                // NOTE: external references are NOT sanitized! That is beyond the security scope of this app (i.e. only pre-trusted users have data entry privs)
                $rendered = '<img id="specimen_image_'.$this->specimen_image_id.'" class="plant-image external-reference" src="'. $this->image_reference.'" />';
            } else {
                $rendered = '<img id="specimen_image_'.$this->specimen_image_id.'" class="plant-image" src="'.APP_ROOT_PATH.'/image_data/specimen/'. util_sanitizeFileReference($this->image_reference).'" />';
            }

            return $rendered;
        }

        public function renderAsListItem($idstr='',$classes_array = [],$other_attribs_hash = []) {
            array_unshift($classes_array,'specimen-image');
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().'>';
            $li_elt .= $this->renderAsHtml().'</li>';
            return $li_elt;
        }

        public function renderAsListItemEdit($idstr='',$classes_array = [],$other_attribs_hash = []) {
//            return 'TO BE IMPLEMENTED';
//
            global $USER;

            if (! $idstr) {
                $idstr = 'specimen-image-'.$this->specimen_image_id;
            }

            array_unshift($classes_array,'specimen-image');
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().'>';
            $li_elt .= '<button type="button" class="btn btn-danger button-delete-specimen-image" title="'.util_lang('prompt_confirm_delete','ucfirst').'" data-specimen_image_id="'.$this->specimen_image_id.'" data-dom_id="'.$idstr.'"><i class="icon-remove icon-white"></i></button><br/>';
            $li_elt .= $this->renderAsHtml();

            $li_elt .= '<div class="controls">';
            // publish, verify, reordering handle
            $li_elt .= util_orderingLeftRightControls($idstr);
            $li_elt .= '<input type="hidden" name="new_ordering-'.$idstr.'" id="new_ordering-'.$idstr.'" value="'.$this->ordering.'"/>';
            if ($this->specimen_image_id != 'NEW') {
                $li_elt .= '<div class="control-workflows">';
                if ($USER->canActOnTarget('publish',$this)) {
                    $li_elt .= '<span class="control-publish"><input id="flag_workflow_published_'.$this->specimen_image_id.'-control" type="checkbox" name="flag_workflow_published" value="1"'.($this->flag_workflow_published ?  ' checked="checked"' : '').' /> '
                        .util_lang('publish').'</span>, ';
                } else {
                    $li_elt .= '<span class="control-publish">'.($this->flag_workflow_published ? util_lang('published_true') : util_lang('published_false'))
                        .'</span>, ';
                }

                if ($USER->canActOnTarget('verify',$this)) {
                    $li_elt .= '<span class="control-verify"><input id="flag_workflow_validated_'.$this->specimen_image_id.'-control" type="checkbox" name="flag_workflow_validated" value="1"'.($this->flag_workflow_validated ?  ' checked="checked"' : '').' /> '
                        .util_lang('verify').'</span>';
                } else {
                    $li_elt .= '<span class="control-verify">'.($this->flag_workflow_validated ? util_lang('verified_true') : util_lang('verified_false'))
                        .'</span>';
                }
                $li_elt .= '</div>';
            }


//            $li_elt .= '<span class="ordering-handle">&lt; &gt;</span>';
            $li_elt .= '</div>';

            $li_elt .= '</li>';

            return $li_elt;
        }

        //---------------------------------------

        public function doDelete($debug = 0) {
            $origFile = $_SERVER['DOCUMENT_ROOT'].APP_ROOT_PATH.'/image_data/specimen/'.$this->image_reference;
            if (rename($origFile,$origFile.'.DEL')) {
                parent::doDelete($debug);
            }
        }
	}
