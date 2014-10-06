<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Notebook_Page_Field extends Db_Linked {
		public static $fields = array('notebook_page_field_id', 'created_at', 'updated_at',
                                      'notebook_page_id', 'label_metadata_structure_id', 'value_metadata_term_value_id', 'value_open', 'flag_delete');
		public static $primaryKeyField = 'notebook_page_field_id';
		public static $dbTable = 'notebook_page_fields';

		public function __construct($initsHash) {
			parent::__construct($initsHash);


			// now do custom stuff
			// e.g. automatically load all accessibility info associated with the user

			//		$this->flag_is_system_admin = false;
			//		$this->flag_is_banned = false;
		}

		public static function cmp($a, $b) {
            if ($a->notebook_page_id == $b->notebook_page_id) {
                if ($a->label_metadata_structure_id == $b->label_metadata_structure_id) {
                    if ($a-> value_metadata_term_value_id && $b->value_metadata_term_value_id) {
                        return Metadata_Structure::cmp($a->getMetadataTermValue(),$b->getMetadataTermValue());
                    } else {
                        return strcmp($a->value_open,$b->value_open);
                    }
                }
                return Metadata_Structure::cmp($a->getMetadataStructure(),$b->getMetadataStructure());
            }
            return Notebook_Page::cmp($a->getNotebookPage(),$b->getNotebookPage());
        }

        //------------------------------------------

        public function getNotebookPage() {
            return Notebook_Page::getOneFromDb(['notebook_page_id'=>$this->notebook_page_id],$this->dbConnection);
        }

        public function getMetadataStructure() {
            return Metadata_Structure::getOneFromDb(['metadata_structure_id'=>$this->label_metadata_structure_id],$this->dbConnection);
        }

        public function getMetadataTermValue() {
            return Metadata_Term_Value::getOneFromDb(['metadata_term_value_id'=>$this->value_metadata_term_value_id],$this->dbConnection);
        }

        //------------------------------------------

        public function renderAsListItem($idstr='',$classes_array = [],$other_attribs_hash = []) {
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().'>';

            $mds = $this->getMetadataStructure();
            $li_elt .= '<span class="notebook-page-field-label" title="'.htmlentities($mds->description).'">'.htmlentities($mds->name).'</span> : ';

            $val_title = '';
            $val_name = '';
            $val_open = '';
            if ($this->value_metadata_term_value_id > 0) {
                $mdtv = $this->getMetadataTermValue();
                $val_title = ' title="'.htmlentities($mdtv->description).'"';
                $val_name = htmlentities($mdtv->name);
            }
            if ($this->value_open) {
                $val_open = '<span class="open-value">'.htmlentities($this->value_open).'</span>';
                if ($val_name) {
                    $val_open = '; '.$val_open;
                }
            }
            $li_elt .= '<span class="notebook-page-field-value"'.$val_title.'>'.$val_name.$val_open.'</span>';

            $li_elt .= '</li>';
            return $li_elt;
        }

        public function renderAsListItemEdit($idstr='',$classes_array = [],$other_attribs_hash = []) {
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().'>';

            $mds = $this->getMetadataStructure();
            $mds->loadTermSetAndValues();

//            util_prePrintR($mds);

            $li_elt .= '<span class="notebook-page-field-label" title="'.htmlentities($mds->description).'">'.htmlentities($mds->name).'</span> : ';
            if ($mds->term_set) {
                $li_elt .= $mds->term_set->renderAsSelectControl('page_field_select_'.$this->notebook_page_field_id,$this->value_metadata_term_value_id);
            }
            else {
                $li_elt .= util_lang('metadata_structure_has_no_term_set');
            }
            $li_elt .= '; <input type="text" name="page_field_open_value_'.$this->notebook_page_field_id.'" id="page_field_open_value_'.$this->notebook_page_field_id.'" class="page_field_open_value" value="'.htmlentities($this->value_open).'"/>';

            $li_elt .= '</li>';
            return $li_elt;
        }

	}
