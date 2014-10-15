<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Specimen extends Db_Linked {
		public static $fields = array('specimen_id', 'created_at', 'updated_at', 'user_id', 'link_to_type', 'link_to_id',
                                      'name', 'gps_longitude', 'gps_latitude', 'notes', 'ordering', 'catalog_identifier',
                                      'flag_workflow_published', 'flag_workflow_validated', 'flag_delete');
		public static $primaryKeyField = 'specimen_id';
		public static $dbTable = 'specimens';
        public static $entity_type_label = 'specimen';

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

        public static function createNewSpecimenForNotebookPage($notebook_page_id,$db_connection) {
            global $USER;
            $s = new Specimen([
                'specimen_id' => 'NEW',
                'created_at' => util_currentDateTimeString_asMySQL(),
                'updated_at' => util_currentDateTimeString_asMySQL(),
                'user_id' => $USER->user_id,
                'link_to_type' => 'notebook_page',
                'link_to_id' => $notebook_page_id,
                'name' => util_lang('new_specimen_name'),
                'gps_longitude' => 0,
                'gps_latitude' => 0,
                'notes' => util_lang('new_specimen_notes'),
                'ordering' => 0,
                'catalog_identifier' => '',
                'flag_workflow_published' => false,
                'flag_workflow_validated' => false,
                'flag_delete' => false,
                'DB'=>$db_connection]);
            return $s;
        }

        public static function createNewSpecimenForAuthoritativePlant($authoritative_plant_id,$db_connection) {
            global $USER;
            $s = new Specimen([
                'specimen_id' => 'NEW',
                'created_at' => util_currentDateTimeString_asMySQL(),
                'updated_at' => util_currentDateTimeString_asMySQL(),
                'user_id' => $USER->user_id,
                'link_to_type' => 'authoritative_plant',
                'link_to_id' => $authoritative_plant_id,
                'name' => util_lang('new_specimen_name'),
                'gps_longitude' => 0,
                'gps_latitude' => 0,
                'notes' => util_lang('new_specimen_notes'),
                'ordering' => 0,
                'catalog_identifier' => '',
                'flag_workflow_published' => false,
                'flag_workflow_validated' => false,
                'flag_delete' => false,
                'DB'=>$db_connection]);
            return $s;
        }

        //---------------

        public function loadImages() {
            $this->images = Specimen_Image::getAllFromDb(['specimen_id' => $this->specimen_id, 'flag_delete' => FALSE],$this->dbConnection);
            usort($this->images,'Specimen_Image::cmp');
        }

        public function cacheImages() {
            if (! $this->images) {
                $this->loadImages();
            }
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

        public function renderAsViewEmbed() {
            $this->cacheImages();

            $rendered = '<div class="specimen">'."\n".
'  <h3>'.htmlentities($this->name).'</h3>'."\n".
'  <ul class="base-info">'."\n";
            if ($this->gps_longitude && $this->gps_latitude) {
                $rendered .= '    <li><span class="field-label">'.util_lang('coordinates').'</span> : <span class="field-value"><a href="'.util_coordsMapLink($this->gps_longitude,$this->gps_latitude).'">'.htmlentities($this->gps_longitude).','.htmlentities($this->gps_latitude).'</a></span></li>'."\n";
            }
            if ($this->notes) {
                $rendered .= '    <li><span class="field-label">'.util_lang('notes').'</span> : <span class="field-value">'.htmlentities($this->notes).'</span></li>'."\n";
            }
            if ($this->catalog_identifier) {
                $rendered .= '    <li><span class="field-label">'.util_lang('catalog_identifier').'</span> : <span class="field-value">'.htmlentities($this->catalog_identifier).'</span></li>'."\n";
            }
            $rendered .= '  </ul>'."\n";

            if (count($this->images) > 0) {
                $rendered .= '  <ul class="specimen-images">'."\n";
                foreach ($this->images as $image) {
                    $rendered .= '    '.$image->renderAsListItem()."\n";
                }

                $rendered .='  </ul>'."\n";
            }

            $rendered .= '</div>';

            return $rendered;
        }

        public function renderAsListItem($idstr='',$classes_array = [],$other_attribs_hash = []) {
            global $USER,$ACTIONS;
            $actions_attribs = '';

            if ($USER->canActOnTarget($ACTIONS['edit'],$this)) {
                $actions_attribs .= ' data-can-edit="1"';
            }
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().$actions_attribs.'>';
            $li_elt .= '<a href="'.APP_ROOT_PATH.'/app_code/specimen.php?specimen_id='.$this->specimen_id.'">'.htmlentities($this->name).'</a></li>';
            return $li_elt;
        }

        public function renderAsEditEmbed() {
            $this->cacheImages();
            global $USER;

            $rendered = '<div class="specimen">'."\n".
                '<div id="form-edit-specimen-'.$this->specimen_id.'" class="form-edit-specimen" data-specimen_id="'.$this->specimen_id.'">'."\n".
                '  <h3><input type="text" name="specimen-name_'.$this->specimen_id.'" id="specimen-name_'.$this->specimen_id.'" value="'.htmlentities($this->name).'"/></h3>'."\n";

            if ($this->specimen_id != 'NEW') {
                if ($USER->canActOnTarget('publish',$this)) {
                    $rendered .= '  <span class="published_state"><input id="specimen-workflow-publish-control_'.$this->specimen_id.'" type="checkbox" name="specimen-flag_workflow_published_'.$this->specimen_id.'" value="1"'.($this->flag_workflow_published ?  ' checked="checked"' : '').' /> '
                        .util_lang('publish').'</span>,';
                } else {
                    $rendered .= '  <span class="published_state">'.($this->flag_workflow_published ? util_lang('published_true') : util_lang('published_false'))
                        .'</span>,';
                }

                if ($USER->canActOnTarget('verify',$this)) {
                    $rendered .= '  <span class="verified_state"><input id="specimen-workflow-validate-control_'.$this->specimen_id.'" type="checkbox" name="specimen-flag_workflow_validated_'.$this->specimen_id.'" value="1"'.($this->flag_workflow_validated ?  ' checked="checked"' : '').' /> '
                        .util_lang('verify').'</span>';
                } else {
                    $rendered .= ' <span class="verified_state">'.($this->flag_workflow_validated ? util_lang('verified_true') : util_lang('verified_false'))
                        .'</span>';
                }
                $rendered .= '<br/>'."\n";
            }

            $rendered .= '  <ul class="base-info">'."\n";
            $rendered .= '    <li><span class="field-label">'.util_lang('coordinates').'</span> : <input type="text" name="specimen-gps_longitude_'.$this->specimen_id.'" id="specimen-gps_longitude_'.$this->specimen_id.'" value="'.$this->gps_longitude.'"/>, <input type="text" name="specimen-gps_latitude_'.$this->specimen_id.'" id="specimen-gps_latitude_'.$this->specimen_id.'" value="'.$this->gps_latitude.'"/></li>'."\n";
            $rendered .= '    <li><span class="field-label">'.util_lang('notes').'</span> : <textarea name="specimen-notes_'.$this->specimen_id.'" id="specimen-notes_'.$this->specimen_id.'" row="4" cols="120">'.htmlentities($this->notes).'</textarea></li>'."\n";
            $rendered .= '    <li><span class="field-label">'.util_lang('catalog_identifier').'</span> : <input type="text" name="specimen-catalog_identifier_'.$this->specimen_id.'" id="specimen-catalog_identifier_'.$this->specimen_id.'" value="'.htmlentities($this->catalog_identifier).'"/></li>'."\n";
            $rendered .= '  </ul>'."\n";

            if (count($this->images) > 0) {
                $rendered .= '  <ul class="specimen-images">'."\n";
                $rendered .= '    <li><a href="#" id="specimen-control-add-image-for-'.$this->specimen_id.'" class="btn add-specimen-image-button" data-for-specimen="'.$this->specimen_id.'">'.util_lang('add_specimen_image').'</a></li>'."\n";

                foreach ($this->images as $image) {
                    $rendered .= '    '.$image->renderAsListItemEdit()."\n";
                }

                $rendered .='  </ul>'."\n";
            }

            $rendered .= "</div>\n</div>";

            return $rendered;
        }

    }
