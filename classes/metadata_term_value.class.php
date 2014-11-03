<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Metadata_Term_Value extends Db_Linked {
		public static $fields = array('metadata_term_value_id', 'created_at', 'updated_at', 'metadata_term_set_id', 'name', 'ordering', 'description', 'flag_delete');
		public static $primaryKeyField = 'metadata_term_value_id';
		public static $dbTable = 'metadata_term_values';
        public static $entity_type_label = 'metadata_term_value';

        public $references;

        public function __construct($initsHash) {
            parent::__construct($initsHash);


            // now do custom stuff
            // e.g. automatically load all accessibility info associated with the user
            $this->references = array();
        }

        //--------------------------------------------------------------------

        public static function cmp($a, $b) {
            if ($a->metadata_term_set_id == $b->metadata_term_set_id) {
                if ($a->ordering == $b->ordering) {
                    if ($a->name == $b->name) {
                        return 0;
                    }
                    return ($a->name < $b->name) ? -1 : 1;
                }
                return ($a->ordering < $b->ordering) ? -1 : 1;
            }
            return ($a->metadata_term_set_id < $b->metadata_term_set_id) ? -1 : 1;
        }

        public static function createNewMetadataTermValue($for_term_set_id,$db_connection) {
            $new = new Metadata_Term_Value([
                'metadata_term_value_id' => 'NEW',
                'created_at' => util_currentDateTimeString_asMySQL(),
                'updated_at' => util_currentDateTimeString_asMySQL(),
                'metadata_term_set_id' => $for_term_set_id,
                'name' => util_lang('new_metadata_term_value_name'),
                'ordering' => 0,
                'description' => util_lang('new_metadata_term_value_description'),
//                'flag_workflow_published' => false,
//                'flag_workflow_validated' => false,
                'flag_delete' => false,
                'DB' => $db_connection
            ]);

            return $new;
        }

        public static function renderFormInteriorForNewMetadataTermValue($unique_str) {
            $rendered = '';
            $rendered .= '<div class="new-metadata-term-value edit-metadata-term-value">';
            $rendered .= '  <div class="edit-metadata-term-value-name">';
            $rendered .= '<input type="text" name="metadata-term-value-name-'.$unique_str.'" value="'.htmlentities(util_lang('new_metadata_term_value_name')).'"/>';
            $rendered .= '</div>'."\n";
            $rendered .= '  <div class="edit-metadata-term-value-description">';
            $rendered .= '<input type="text" name="metadata-term-value-description-'.$unique_str.'" value="'.htmlentities(util_lang('new_metadata_term_value_description')).'"/>';
            $rendered .= '</div>'."\n";
            $rendered .= '</div>';

            return $rendered;        }

        //--------------------------------------------------------------------

        //  NOTE: returns 0 if there is no parent
		public function getMetadataTermSet() {
            return Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => $this->metadata_term_set_id, 'flag_delete' => FALSE], $this->dbConnection);
		}

        public function loadReferences() {
            $this->references = Metadata_Reference::getAllFromDb(['metadata_type'=>'term_value', 'metadata_id' => $this->metadata_term_value_id, 'flag_delete' => FALSE], $this->dbConnection);
            usort($this->references,'Metadata_Reference::cmp');
        }

        public function cacheReferences() {
            if (! $this->references) {
                $this->loadReferences();
            }
        }

        public function renderAsReferencesListView() {
            $rendered = '';
            $this->cacheReferences();
            $rendered .= '<div class="metadata-references">';
            $rendered .= Metadata_Reference::renderReferencesArrayAsListsView($this->references);
//            if (count($this->references) > 0) {
//                $rendered .= '<ul class="metadata-references">';
//                foreach ($this->references as $r) {
//                    $rendered .= '<li>'.$r->renderAsViewEmbed().'</li>';
//                }
//                $rendered .= '</ul>';
//            }
            $rendered .= '</div>';
            return $rendered;
        }

        public function renderAsReferencesListEdit() {
            $rendered = '';
            $this->cacheReferences();
            $rendered .= '<div class="metadata-references">';
            $rendered .= Metadata_Reference::renderReferencesArrayAsListsEdit($this->references);
//            if (count($this->references) > 0) {
//                $rendered .= '<ul class="metadata-references">'."\n";
//                $rendered .= '<li><a href="#" id="add_new_metadata_reference_button-for_metadata_term_value_'.$this->metadata_term_value_id.'" class="btn" data-for_metadata_term_value="'.$this->metadata_term_value_id.'">'.util_lang('add_metadata_reference').'</a></li>'."\n";
//                foreach ($this->references as $r) {
//                    $rendered .= '<li>'.$r->renderAsEditEmbed().'</li>'."\n";
//                }
//                $rendered .= '</ul>'."\n";
//            }
            $rendered .= '</div>';
            return $rendered;
        }

        public function renderAsHtml() {
            $rendered = '<span class="term_value" title="'.htmlentities($this->description).'">'.$this->name.'</span>';
            $rendered .= $this->renderAsReferencesListView();
            return $rendered;
        }

        public function renderAsListItem($idstr='',$classes_array = [],$other_attribs_hash = []) {
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().'>';
            $li_elt .= $this->renderAsHtml();
            $li_elt .= '</li>';
            return $li_elt;
        }

        public function renderAsViewEmbed() {
            $rendered = '<div id="rendered_metadata_term_value_'.$this->metadata_term_value_id.'" class="rendered-metadata-term-value embedded" '.$this->fieldsAsDataAttribs().'>';
            $rendered .= $this->renderAsHtml();
            $rendered .= '</div>';
            return $rendered;
        }

        public function renderAsOption($flag_is_selected=false) {
            $this->cacheReferences();

            $rendered = '<option data-metadata_term_value_id="'.$this->metadata_term_value_id.'" data-description="'.htmlentities($this->description).'" data-ARRAY_metadata_reference_ids="'.implode(',',Db_Linked::arrayOfAttrValues($this->references,'metadata_reference_id')).'" title="'.htmlentities($this->description).'" value="'.$this->metadata_term_value_id.'"'.($flag_is_selected ? ' selected="selected"' : '').'>'.htmlentities($this->name).'</option>';

            return $rendered;
        }

        public function renderAsListItemEdit($idstr='',$classes_array = [],$other_attribs_hash = []) {
            if (! $idstr) {
                $idstr = 'list-item-metadata-term-value-'.$this->metadata_term_value_id;
            }
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().'>';
            $li_elt .= util_orderingUpDownControls($idstr).' ';
            $li_elt .= $this->renderAsEdit();
            $li_elt .= '<input type="hidden" name="original_ordering-'.$idstr.'" id="original_ordering-'.$idstr.'" value="'.$this->ordering.'"/>';
            $li_elt .= '<input type="hidden" name="new_ordering-'.$idstr.'" id="new_ordering-'.$idstr.'" value="'.$this->ordering.'"/>';
            $li_elt .= '</li>';
            return $li_elt;
        }

        public function renderAsEdit() {
            $rendered = '';

            $rendered .= '<div id="edit-metadata-term-value-'.$this->metadata_term_value_id.'" class="edit-metadata-term-value" '.$this->fieldsAsDataAttribs().'>'."\n";
            $rendered .= '  <div class="edit-metadata-term-value-name">';
            $rendered .= '<input type="text" name="metadata-term-value-name-'.$this->metadata_term_value_id.'" value="'.htmlentities($this->name).'"/>';
            $rendered .= '</div>'."\n";
            $rendered .= '  <div class="edit-metadata-term-value-description">';
            $rendered .= '<input type="text" name="metadata-term-value-description-'.$this->metadata_term_value_id.'" value="'.htmlentities($this->description).'"/>';
            $rendered .= '</div>'."\n";
            $rendered .= '  <div class="edit-metadata-term-value-references">'."\n";

            $rendered .= '    <ul class="add-metadata-references">'."\n";
            $rendered .= '      <li><a href="#" id="add_new_metadata_reference_button-for_metadata_term_value_6210" class="btn" data-for_metadata_term_value="6210">+ Add Reference +</a></li>'."\n";
            $rendered .= '    </ul>'."\n";

            $rendered .= $this->renderAsReferencesListEdit();
            $rendered .= '  </div>'."\n";

            $rendered .= '</div>'."\n";

            return $rendered;
        }
	}
