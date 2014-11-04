<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Metadata_Reference extends Db_Linked {
		public static $fields = array('metadata_reference_id', 'created_at', 'updated_at', 'metadata_type', 'metadata_id', 'type', 'external_reference', 'description', 'ordering', 'flag_active', 'flag_delete');
		public static $primaryKeyField = 'metadata_reference_id';
		public static $dbTable = 'metadata_references';
        public static $entity_type_label = 'metadata_reference';

        public static $VALID_METADATA_TYPES = ['structure', 'term_set', 'term_value'];
        public static $VALID_TYPES = ['text', 'image', 'link'];

        public static $SORT_PRIORITIES_FOR_METADATA_TYPES = ['structure'=>1,'term_set'=>2,'term_value'=>3];
        public static $SORT_PRIORITIES_FOR_TYPES = ['image'=>1,'link'=>2,'text'=>3];

        //-----------------------------------------

        public static function cmp($a, $b) {
            if (Metadata_Reference::$SORT_PRIORITIES_FOR_METADATA_TYPES[$a->metadata_type] == Metadata_Reference::$SORT_PRIORITIES_FOR_METADATA_TYPES[$b->metadata_type]) {
                if ($a->metadata_id == $b->metadata_id) {
                    if (Metadata_Reference::$SORT_PRIORITIES_FOR_TYPES[$a->type] == Metadata_Reference::$SORT_PRIORITIES_FOR_TYPES[$b->type]) {
                        if ($a->ordering == $b->ordering) {
                            if ($a->external_reference == $b->external_reference) {
                                return 0;
                            }
                            return ($a->external_reference < $b->external_reference) ? -1 : 1;
                        }
                        return ($a->ordering < $b->ordering) ? -1 : 1;
                    }
                    return (Metadata_Reference::$SORT_PRIORITIES_FOR_TYPES[$a->type] < Metadata_Reference::$SORT_PRIORITIES_FOR_TYPES[$b->type]) ? -1 : 1;
                }
                return ($a->metadata_id < $b->metadata_id) ? -1 : 1;
            }
            return (Metadata_Reference::$SORT_PRIORITIES_FOR_METADATA_TYPES[$a->metadata_type] < Metadata_Reference::$SORT_PRIORITIES_FOR_METADATA_TYPES[$b->metadata_type]) ? -1 : 1;
        }

        public static function createNewMetadataReference($for_metadata_object) {
            return "TO BE IMPLEMENTED: createNewMetadataReference";
        }

        public static function renderReferencesArrayAsListsView($ref_ar) {
            $rendered = '';

            $list_items_image = '';
            $list_items_link = '';
            $list_items_text = '';
            foreach ($ref_ar as $r) {
                if ($r->type == 'image') {
                    $list_items_image .= '    <li>'.$r->renderAsViewEmbed().'</li>'."\n";
                } elseif ($r->type == 'link') {
                    $list_items_link .= '    <li>'.$r->renderAsViewEmbed().'</li>'."\n";
                } elseif ($r->type == 'text') {
                    $list_items_text .= '    <li>'.$r->renderAsViewEmbed().'</li>'."\n";
                }
            }

            $rendered .= '<div class="metadata-references">'."\n";
            $rendered .= '  <ul class="metadata-references metadata-references-images">'."\n";
            $rendered .= $list_items_image;
            $rendered .= '  </ul>'."\n";
            $rendered .= '  <ul class="metadata-references metadata-references-links">'."\n";
            $rendered .= $list_items_link;
            $rendered .= '  </ul>'."\n";
            $rendered .= '  <ul class="metadata-references metadata-references-texts">'."\n";
            $rendered .= $list_items_text;
            $rendered .= '  </ul>'."\n";
            $rendered .= '</div>'."\n";

            return $rendered;
        }

        public static function renderReferencesArrayAsListsEdit($ref_ar) {
            $rendered = '';

            $list_items_image = '';
            $list_items_link = '';
            $list_items_text = '';
            foreach ($ref_ar as $r) {
                if ($r->type == 'image') {
                    $list_items_image .= '    <li>'.$r->renderAsEditEmbed().'</li>'."\n";
                } elseif ($r->type == 'link') {
                    $list_items_link .= '    <li>'.$r->renderAsEditEmbed().'</li>'."\n";
                } elseif ($r->type == 'text') {
                    $list_items_text .= '    <li>'.$r->renderAsEditEmbed().'</li>'."\n";
                }
            }

            $rendered .= '<div class="metadata-references edit-metadata-references">'."\n";
            $rendered .= '  <ul class="metadata-references metadata-references-images edit-metadata-references-images">'."\n";
            $rendered .= $list_items_image;
            $rendered .= '  </ul>'."\n";
            $rendered .= '  <ul class="metadata-references metadata-references-links edit-metadata-references-links">'."\n";
            $rendered .= $list_items_link;
            $rendered .= '  </ul>'."\n";
            $rendered .= '  <ul class="metadata-references metadata-references-texts edit-metadata-references-texts">'."\n";
            $rendered .= $list_items_text;
            $rendered .= '  </ul>'."\n";
            $rendered .= '</div>'."\n";

            return $rendered;
        }

        //-----------------------------------------

		public function getReferrent() {
            if ($this->metadata_type == 'structure') {
                return Metadata_Structure::getOneFromDb(['metadata_structure_id' => $this->metadata_id, 'flag_delete' => FALSE], $this->dbConnection);
            } elseif ($this->metadata_type == 'term_set') {
                return Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => $this->metadata_id, 'flag_delete' => FALSE], $this->dbConnection);
            } elseif ($this->metadata_type == 'term_value') {
                return Metadata_Term_Value::getOneFromDb(['metadata_term_value_id' => $this->metadata_id, 'flag_delete' => FALSE], $this->dbConnection);
            } else {
                return 'UNKNOWN METADATA_TYPE: /'.$this->metadata_type.'/';
            }
		}

        public function renderAsHtml() {
            $rendered = '';

            if ($this->type == 'text') {
                $file_path = $_SERVER["DOCUMENT_ROOT"].APP_ROOT_PATH.'/text_data/'.util_sanitizeFileReference($this->external_reference);
                $text_data = file_get_contents( $file_path);
                $text_data = preg_replace('/\\r/',"",$text_data);
                $rendered = '<div class="text_data" title="'.htmlentities($this->description).'">'.htmlentities($text_data).'</div>';
            }
            elseif ($this->type == 'image') {
                if (preg_match('/^http/i',$this->external_reference)) {
                    // NOTE: external references are NOT sanitized! That is beyond the security scope of this app (i.e. only pre-trusted users have data entry privs)
                    $rendered = '<img class="metadata-reference-image external-reference" src="'. $this->external_reference.'" alt="'.htmlentities($this->description).'"/>';
                } else {
                    $rendered = '<img class="metadata-reference-image" src="'.APP_ROOT_PATH.'/image_data/reference/'. util_sanitizeFileReference($this->external_reference).'" alt="'.htmlentities($this->description).'"/>';
                }
            }
            elseif ($this->type == 'link') {
                // NOTE: external references are NOT sanitized! That is beyond the security scope of this app (i.e. only pre-trusted users have data entry privs)
                $rendered = '<a href="'. $this->external_reference.'" title="'.htmlentities($this->description).'">'.htmlentities($this->description).'</a>';
            }

            return $rendered;
        }

        public function renderAsViewEmbed() {
            $reference_class='';

            if ($this->type == 'text') {
                $reference_class='rendered_metadata_reference_text';
            }
            elseif ($this->type == 'image') {
                $reference_class='rendered_metadata_reference_image';
            }
            elseif ($this->type == 'link') {
                $reference_class='rendered_metadata_reference_link';
            } else {
                return '';
            }

            $rendered = '<div id="rendered_metadata_reference_'.$this->metadata_reference_id.'" class="embedded rendered_metadata_reference '.$reference_class.'">'.$this->renderAsHtml().'</div>';
            return $rendered;
        }

        public function renderAsEdit() {
            return 'not yet supported';
        }

        public function renderAsEditEmbed() {
            $reference_class='';

            if ($this->type == 'text') {
                $reference_class='rendered_metadata_reference_text';
            }
            elseif ($this->type == 'image') {
                $reference_class='rendered_metadata_reference_image';
            }
            elseif ($this->type == 'link') {
                $reference_class='rendered_metadata_reference_link';
            } else {
                return '';
            }

            $rendered = '<div id="edit_rendered_metadata_reference_'.$this->metadata_reference_id.'" class="embedded rendered_metadata_reference edit_rendered_metadata_reference '.$reference_class.'">'.$this->renderAsEdit().'</div>';
            return $rendered;
        }
	}
