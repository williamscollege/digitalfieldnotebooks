<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Authoritative_Plant extends Db_Linked {
		public static $fields = array('authoritative_plant_id', 'created_at', 'updated_at',
                                      'class', 'order', 'family', 'genus', 'species', 'variety',
                                      'catalog_identifier', 'flag_active', 'flag_delete');
		public static $primaryKeyField = 'authoritative_plant_id';
		public static $dbTable = 'authoritative_plants';
        public static $entity_type_label = 'authoritative_plant';

        public $extras;
        public $notebook_pages;
        public $specimens;

        public function __construct($initsHash) {
            parent::__construct($initsHash);


            // now do custom stuff
            // e.g. automatically load all accessibility info associated with the user

            $this->extras = array();
            $this->notebook_pages = array();
            $this->specimens = array();
        }

        public static function cmp($a, $b) {
//            if ($a->class == $b->class) {
//                if ($a->order == $b->order) {
//                    if ($a->family == $b->family) {
                        if ($a->genus == $b->genus) {
                            if ($a->species == $b->species) {
                                if ($a->variety == $b->variety) {
                                    if ($a->catalog_identifier == $b->catalog_identifier) {
                                        return 0;
                                    }
                                    return ($a->catalog_identifier < $b->catalog_identifier) ? -1 : 1;
                                }
                                return ($a->variety < $b->variety) ? -1 : 1;
                            }
                            return ($a->species < $b->species) ? -1 : 1;
                        }
                        return ($a->genus < $b->genus) ? -1 : 1;
//                    }
//                    return ($a->family < $b->family) ? -1 : 1;
//                }
//                return ($a->order < $b->order) ? -1 : 1;
//            }
//            return ($a->class < $b->class) ? -1 : 1;
        }

        public static function cmpExtended($a, $b) {
            if ($a->class == $b->class) {
                if ($a->order == $b->order) {
                    if ($a->family == $b->family) {
                        if ($a->genus == $b->genus) {
                            if ($a->species == $b->species) {
                                if ($a->variety == $b->variety) {
                                    if ($a->catalog_identifier == $b->catalog_identifier) {
                                        return 0;
                                    }
                                    return ($a->catalog_identifier < $b->catalog_identifier) ? -1 : 1;
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

        public static function renderControlSelectAllAuthoritativePlants($default_selected = 0) {
            if (is_object($default_selected)) {
               $default_selected = $default_selected->authoritative_plant_id;
            }

            global $DB;

            $all_ap = Authoritative_Plant::getAllFromDb(['flag_active'=>true,'flag_delete' => FALSE], $DB);
            usort($all_ap,'Authoritative_Plant::cmp');

            $rendered = '<select name="authoritative_plant_id" id="authoritative-plant-id">'."\n";
            foreach ($all_ap as $ap) {
               $rendered .= '  '.$ap->renderAsOption($ap->authoritative_plant_id == $default_selected)."\n";
            }
            $rendered .= '</select>';

            return $rendered;
        }

        public static function createNewAuthoritativePlant($db_connection) {
            $n = new Authoritative_Plant([
                'authoritative_plant_id' => 'NEW',
                'created_at' => util_currentDateTimeString_asMySQL(),
                'updated_at' => util_currentDateTimeString_asMySQL(),
                'class' => '',
                'order' => '',
                'family' => '',
                'genus' => '',
                'species' => '',
                'variety' => '',
                'catalog_identifier' => '',
                'flag_active' => false,
                'flag_delete' => false,
                'DB'=>$db_connection]);
            return $n;
        }

        //------------------------------------------------------------------------------------

        public function loadExtras() {
            $this->extras = Authoritative_Plant_Extra::getAllFromDb(['authoritative_plant_id' => $this->authoritative_plant_id, 'flag_delete' => FALSE], $this->dbConnection);
            usort($this->extras,'Authoritative_Plant_Extra::cmp');
        }

        public function cacheExtras() {
            if (! $this->extras) {
                $this->loadExtras();
            }
        }

        public function loadNotebookPages() {
            $this->notebook_pages = Notebook_Page::getAllFromDb(['authoritative_plant_id' => $this->authoritative_plant_id, 'flag_delete' => FALSE], $this->dbConnection);
            usort($this->notebook_pages,'Notebook_Page::cmp');
        }

        public function cacheNotebookPages() {
            if (! $this->notebook_pages) {
                $this->loadNotebookPages();
            }
        }

        public function loadSpecimens() {
            $this->specimens = Specimen::getAllFromDb(['link_to_type' => 'authoritative_plant', 'link_to_id' => $this->authoritative_plant_id, 'flag_delete' => FALSE], $this->dbConnection);
            usort($this->specimens,'Specimen::cmp');
        }

        public function cacheSpecimens() {
            if (! $this->specimens) {
                $this->loadSpecimens();
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

        function renderAsLink($action='view') {
            $action = Action::sanitizeAction($action);

            $link = '<a href="'.APP_ROOT_PATH.'/app_code/authoritative_plant.php?action='.$action.'&authoritative_plant_id='.$this->authoritative_plant_id.'">'.htmlentities($this->renderAsShortText()).'</a>';

            return $link;
        }

        public function renderAsButtonEdit() {
            $btn = '<a id="btn-edit" href="'.APP_ROOT_PATH.'/app_code/authoritative_plant.php?action=edit&authoritative_plant_id='.$this->authoritative_plant_id.'" class="edit_link btn" ><i class="icon-edit"></i> '.util_lang('edit').'</a>';
            return $btn;
        }

        public function renderAsListItem($idstr='',$classes_array = [],$other_attribs_hash = []) {
            global $USER,$ACTIONS;
            $actions_attribs = '';

            if ($USER->canActOnTarget($ACTIONS['edit'],$this)) {
                $actions_attribs .= ' data-can-edit="1"';
            }
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().$actions_attribs.'>';
            if ($this->flag_active) {
                $li_elt .= '<i class="icon-ok"></i> ';
            } else {
                $li_elt .= '<i class="icon-ban-circle"></i> ';
            }
            $li_elt .= $this->renderAsLink();
            $li_elt .= '</li>';
//            $li_elt .= '<a href="/app_code/authoritative_plant.php?authoritative_plant_id='.$this->authoritative_plant_id.'">'.htmlentities($this->renderAsShortText()).'</a></li>';
            return $li_elt;
        }

        private function _renderBaseInfo() {
            $rendered = '';

            $rendered .= '  <ul class="base-info">'."\n";
            if ($this->class) { $rendered .= '    <li><div class="field-label">'.util_lang('class').' : </div><div class="field-value taxonomy taxonomy-class">'.htmlentities($this->class).'</div></li>'."\n"; }
            if ($this->order) { $rendered .= '    <li><div class="field-label">'.util_lang('order').' : </div><div class="field-value taxonomy taxonomy-order">'.htmlentities($this->order).'</div></li>'."\n"; }
            if ($this->family) { $rendered .= '    <li><div class="field-label">'.util_lang('family').' : </div><div class="field-value taxonomy taxonomy-family">'.htmlentities($this->family).'</div></li>'."\n"; }
            if ($this->genus) { $rendered .= '    <li><div class="field-label">'.util_lang('genus').' : </div><div class="field-value taxonomy taxonomy-genus">'.htmlentities($this->genus).'</div></li>'."\n"; }
            if ($this->species) { $rendered .= '    <li><div class="field-label">'.util_lang('species').' : </div><div class="field-value taxonomy taxonomy-species">'.htmlentities($this->species).'</div></li>'."\n"; }
            if ($this->variety) { $rendered .= '    <li><div class="field-label">'.util_lang('variety').' : </div><div class="field-value taxonomy taxonomy-variety">\''.htmlentities($this->variety).'\'</div></li>'."\n"; }
            if ($this->catalog_identifier) { $rendered .= '    <li><div class="field-label">'.util_lang('catalog_identifier').' : </div><div class="field-value">'.htmlentities($this->catalog_identifier).'</div></li>'."\n"; }
            $rendered .= '  </ul><br/>'."\n";

            return $rendered;
        }

        public function renderAsViewEmbed() {
            $this->cacheExtras();
            $this->cacheSpecimens();

            $canonical_image = '';
            foreach ($this->extras as $extra) {
                if ($extra->type == 'image') {
                    $canonical_image .= $extra->renderAsHtml();
                    break;
                }
            }

            $rendered = '<div id="authoritative_plant_embed_'.$this->authoritative_plant_id.'" class="authoritative-plant embedded" data-authoritative_plant_id="'.$this->authoritative_plant_id.'">
  <h3>'.$this->renderAsLink().'</h3>
  <div class="canonical_image">'.$canonical_image.'</div>'."\n";
            $rendered .= $this->_renderBaseInfo();
//  <ul class="base-info">'."\n";
//            $rendered .= '    <li><div class="field-label">'.util_lang('class').' : </div><div class="field-value taxonomy taxonomy-class">'.htmlentities($this->class).'</div></li>'."\n";
//            $rendered .= '    <li><div class="field-label">'.util_lang('order').' : </div><div class="field-value taxonomy taxonomy-order">'.htmlentities($this->order).'</div></li>'."\n";
//            $rendered .= '    <li><div class="field-label">'.util_lang('family').' : </div><div class="field-value taxonomy taxonomy-family">'.htmlentities($this->family).'</div></li>'."\n";
//            $rendered .= '    <li><div class="field-label">'.util_lang('genus').' : </div><div class="field-value taxonomy taxonomy-genus">'.htmlentities($this->genus).'</div></li>'."\n";
//            $rendered .= '    <li><div class="field-label">'.util_lang('species').' : </div><div class="field-value taxonomy taxonomy-species">'.htmlentities($this->species).'</div></li>'."\n";
//            $rendered .= '    <li><div class="field-label">'.util_lang('variety').' : </div><div class="field-value taxonomy taxonomy-variety">\''.htmlentities($this->variety).'\'</div></li>'."\n";
//            $rendered .= '    <li><div class="field-label">'.util_lang('catalog_identifier').' : </div><div class="field-value">'.htmlentities($this->catalog_identifier).'</div></li>'."\n";
//            $rendered .= '  </ul><br/>
            $rendered .= '  <a class="show-hide-control" href="#" data-for_elt_id="authoritative_plant-details_'.$this->authoritative_plant_id.'">'.util_lang('show_hide').' '.util_lang('extra_info').'</a>
  <div class="details-info" id="authoritative_plant-details_'.$this->authoritative_plant_id.'">
  <ul class="extra-info" id="authoritative_plant-extra_info_'.$this->authoritative_plant_id.'">
';
            if ($this->extras) {
                foreach ($this->extras as $extra) {
                    $rendered .='    '.$extra->renderAsListItem()."\n";
                }
            } else {
                $rendered .='    <li>'.util_lang('no_authoritative_plant_extra_info','ucfirst').'</li>'."\n";
            }
            $rendered .='  </ul>'."\n";

$rendered .= '  <h4>'.util_lang('specimens','properize').'</h4>
  <ul class="specimens">
';
            if ($this->specimens) {
                foreach ($this->specimens as $specimen) {
                    $rendered .= '    <li>'.$specimen->renderAsViewEmbed()."</li>\n";
                }
            } else {
                $rendered .='    <li>'.util_lang('no_authoritative_plant_specimens','ucfirst').'</li>'."\n";
            }
            $rendered .='  </ul>
</div>
</div>';

            return $rendered;
        }


        public function renderAsView() {
            $this->cacheExtras();
            $this->cacheNotebookPages();
            $this->cacheSpecimens();

//            $rendered = '<div id="rendered_metadata_structure_'.$this->metadata_structure_id.'" class="view-rendered_metadata_structure" '.$this->fieldsAsDataAttribs().'>
//  <div class="metadata_lineage"><a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=list">metadata</a> &gt;';

            $rendered = '<div id="authoritative_plant_view_'.$this->authoritative_plant_id.'" class="authoritative-plant view-authoritative-plant" data-authoritative_plant_id="'.$this->authoritative_plant_id.'">'."\n";
            $rendered .='  <span class="authoritative-plant-breadcrumb"><a href="'.APP_ROOT_PATH.'/app_code/authoritative_plant.php?action=list">'.util_lang('authoritative_plant').'</a> &gt;</span>'."\n";
            $rendered .='  <h3>'.$this->renderAsShortText().'</h3>'."\n";
            $rendered .= $this->_renderBaseInfo();
//            $rendered .='  <ul class="base-info">
//    <li><span class="field-label">'.util_lang('class').'</span> : <span class="field-value taxonomy taxonomy-class">'.htmlentities($this->class).'</span></li>
//    <li><span class="field-label">'.util_lang('order').'</span> : <span class="field-value taxonomy taxonomy-order">'.htmlentities($this->order).'</span></li>
//    <li><span class="field-label">'.util_lang('family').'</span> : <span class="field-value taxonomy taxonomy-family">'.htmlentities($this->family).'</span></li>
//    <li><span class="field-label">'.util_lang('genus').'</span> : <span class="field-value taxonomy taxonomy-genus">'.htmlentities($this->genus).'</span></li>
//    <li><span class="field-label">'.util_lang('species').'</span> : <span class="field-value taxonomy taxonomy-species">'.htmlentities($this->species).'</span></li>
//    <li><span class="field-label">'.util_lang('variety').'</span> : <span class="field-value taxonomy taxonomy-variety">\''.htmlentities($this->variety).'\'</span></li>
//    <li><span class="field-label">'.util_lang('catalog_identifier').'</span> : <span class="field-value">'.htmlentities($this->catalog_identifier).'</span></li>
//  </ul>
            $rendered .='  <div class="active_state_info">';
            if ($this->flag_active) {
                $rendered .= '<i class="icon-ok"></i> '.util_lang('active_true');
            } else {
                $rendered .= '<i class="icon-ban-circle"></i> '.util_lang('active_false');
            }
            $rendered .='</div>
  <h4>'.util_lang('details','properize').'</h4>
  <ul class="extra-info">
';

//
            if ($this->extras) {
                foreach ($this->extras as $extra) {
                    $rendered .='    '.$extra->renderAsListItem()."\n";
                }
            } else {
                $rendered .='    <li>'.util_lang('no_authoritative_plant_extra_info','ucfirst').'</li>'."\n";
            }
            $rendered .='  </ul>
  <h4>'.util_lang('notebook_pages','properize').'</h4>
  <ul class="notebook-pages">
';
            global $USER,$ACTIONS;
            if ($this->notebook_pages) {
                foreach ($this->notebook_pages as $np) {
                    if ($USER->canActOnTarget($ACTIONS['view'],$np)) {
                        $rendered .='    '.$np->renderAsListItemForNotebook()."\n";
                    }
                }
            } else {
                $rendered .='    <li>'.util_lang('no_authoritative_plant_notebook_pages','ucfirst').'</li>'."\n";
            }

            $rendered .= '  </ul>
  <h4>'.util_lang('specimens','properize').'</h4>
  <ul class="specimens">
';
            if ($this->specimens) {
                foreach ($this->specimens as $specimen) {
                    $rendered .= '    <li>'.$specimen->renderAsViewEmbed()."</li>\n";
                }
            } else {
                $rendered .='    <li>'.util_lang('no_authoritative_plant_specimens','ucfirst').'</li>'."\n";
            }

            $rendered .='  </ul>
</div>';

            return $rendered;
        }

        public function renderAsOption($flag_is_selected=false) {
            $rendered = '<option data-authoritative_plant_id="'.$this->authoritative_plant_id.'" value="'.$this->authoritative_plant_id.'"'.($flag_is_selected ? ' selected="selected"' : '').'>'.$this->renderAsShortText().'</option>';
            return $rendered;
        }

        function renderAsEdit() {
            $this->cacheExtras();
            $this->cacheNotebookPages();
            $this->cacheSpecimens();


            $rendered = '<div id="rendered_authoritative_plant_'.$this->authoritative_plant_id.'" class="authoritative-plant edit-authoritative-plant" '.$this->fieldsAsDataAttribs().' data-can-edit="1">'."\n";
            $rendered .= '  <form id="form-edit-authoritative-plant" action="'.APP_ROOT_PATH.'/app_code/authoritative_plant.php">'."\n";
            $rendered .= '    <input type="hidden" name="action" value="update"/>'."\n";
            $rendered .= '    <input type="hidden" id="authoritative_plant_id" name="authoritative_plant_id" value="'.$this->authoritative_plant_id.'"/>'."\n";
            $rendered .= '    <div id="actions"><button id="edit-submit-control" class="btn btn-success" type="submit" name="edit-submit-control" value="update"><i class="icon-ok-sign icon-white"></i> '.util_lang('update','properize').'</button>'."\n";
            $rendered .= '    <a id="edit-cancel-control" class="btn" href="/digitalfieldnotebooks/app_code/authoritative_plant.php?action=view&authoritative_plant_id='.$this->authoritative_plant_id.'"><i class="icon-remove"></i> '.util_lang('cancel','properize').'</a>  <a id="edit-delete-authoritative-plant-control" class="btn btn-danger" href="/digitalfieldnotebooks/app_code/authoritative_plant.php?action=delete&authoritative_plant_id='.$this->authoritative_plant_id.'"><i class="icon-trash icon-white"></i> '.util_lang('delete','properize').'</a></div>'."\n";

// basic data fields
            $rendered .= '    <ul class="base-info">'."\n";
            $rendered .= '      <li><div class="field-label">'.util_lang('class').'</div> : <div class="field-value taxonomy taxonomy-class"><input type="text" name="authoritative_plant-class_'.$this->authoritative_plant_id.'" id="authoritative_plant-class_'.$this->authoritative_plant_id.'" value="'.htmlentities($this->class).'"/></div></li>'."\n";
            $rendered .= '      <li><div class="field-label">'.util_lang('order').'</div> : <div class="field-value taxonomy taxonomy-order"><input type="text" name="authoritative_plant-order_'.$this->authoritative_plant_id.'" id="authoritative_plant-order_'.$this->authoritative_plant_id.'" value="'.htmlentities($this->order).'"/></div></li>'."\n";
            $rendered .= '      <li><div class="field-label">'.util_lang('family').'</div> : <div class="field-value taxonomy taxonomy-family"><input type="text" name="authoritative_plant-family_'.$this->authoritative_plant_id.'" id="authoritative_plant-family_'.$this->authoritative_plant_id.'" value="'.htmlentities($this->family).'"/></div></li>'."\n";
            $rendered .= '      <li><div class="field-label">'.util_lang('genus').'</div> : <div class="field-value taxonomy taxonomy-genus"><input type="text" name="authoritative_plant-genus_'.$this->authoritative_plant_id.'" id="authoritative_plant-genus_'.$this->authoritative_plant_id.'" value="'.htmlentities($this->genus).'"/></div></li>'."\n";
            $rendered .= '      <li><div class="field-label">'.util_lang('species').'</div> : <div class="field-value taxonomy taxonomy-species"><input type="text" name="authoritative_plant-species_'.$this->authoritative_plant_id.'" id="authoritative_plant-species_'.$this->authoritative_plant_id.'" value="'.htmlentities($this->species).'"/></div></li>'."\n";
            $rendered .= '      <li><div class="field-label">'.util_lang('variety').'</div> : <div class="field-value taxonomy taxonomy-variety"><input type="text" name="authoritative_plant-variety_'.$this->authoritative_plant_id.'" id="authoritative_plant-variety_'.$this->authoritative_plant_id.'" value="'.htmlentities($this->variety).'"/></div></li>'."\n";
            $rendered .= '      <li><div class="field-label">'.util_lang('catalog_identifier').'</div> : <div class="field-value" taxonomy taxonomy-catalog_identifier><input type="text" name="authoritative_plant-catalog_identifier_'.$this->authoritative_plant_id.'" id="authoritative_plant-catalog_identifier_'.$this->authoritative_plant_id.'" value="'.htmlentities($this->catalog_identifier).'"/></div></li>'."\n";
            $rendered .= '    </ul>'."\n";

// flag active control
            $rendered .= '    <div class="active-state-controls"><input type="checkbox" name="flag_active" value="1"'.($this->flag_active ? ' checked="checked"' : '').'/> '.util_lang('active').'</div>'."\n";

// extra info : common names (w/ reordering controls)

            $rendered .= '    <h5>'.util_lang('common_names','properize').'</h5>'."\n";
            $rendered .= '    <ul class="authoritative-plant-extras authoritative-plant-extra-common_name">'."\n";
            $rendered .= '      <li><a href="#" id="add_new_authoritative_plant_common_name_button" class="btn">'.util_lang('add_common_name').'</a></li>'."\n";
            foreach ($this->extras as $ae) {
                if ($ae->type == 'common name') {
                    $rendered .= '      '.$ae->renderAsListItemEdit()."\n";
                }
            }
            $rendered .= '    </ul>'."\n";

// extra info : images (w/ reordering controls)
            $rendered .= '    <h5>'.util_lang('images','properize').'</h5>'."\n";
            $rendered .= '    <ul class="authoritative-plant-extras authoritative-plant-extra-image">'."\n";
            $rendered .= '      <li><a href="#" id="add_new_authoritative_plant_image_button" class="btn">'.util_lang('add_image').'</a></li>'."\n";
            foreach ($this->extras as $ae) {
                if ($ae->type == 'image') {
                    $rendered .= '      '.$ae->renderAsListItemEdit()."\n";
                }
            }
            $rendered .= '    </ul>'."\n";

// extra info : text (w/ reordering controls)
            $rendered .= '    <h5>'.util_lang('descriptions','properize').'</h5>'."\n";
            $rendered .= '    <ul class="authoritative-plant-extras authoritative-plant-extra-description">'."\n";
            $rendered .= '      <li><a href="#" id="add_new_authoritative_plant_description_button" class="btn">'.util_lang('add_description').'</a></li>'."\n";
            foreach ($this->extras as $ae) {
                if ($ae->type == 'description') {
                    $rendered .= '      '.$ae->renderAsListItemEdit()."\n";
                }
            }
            $rendered .= '    </ul>'."\n";

            $rendered .= Specimen::renderSpecimenListBlock($this->specimens);

            $rendered .= '  </form>'."\n";
            $rendered .= '</div>'."\n";

            return $rendered;
        }

    }