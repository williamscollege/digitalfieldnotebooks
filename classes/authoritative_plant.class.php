<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Authoritative_Plant extends Db_Linked {
		public static $fields = array('authoritative_plant_id', 'created_at', 'updated_at',
                                      'class', 'order', 'family', 'genus', 'species', 'variety',
                                      'catalog_identifier', 'flag_delete');
		public static $primaryKeyField = 'authoritative_plant_id';
		public static $dbTable = 'authoritative_plants';

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


       public static function renderControlSelectAllAuthoritativePlants($default_selected = 0) {
           if (is_object($default_selected)) {
               $default_selected = $default_selected->authoritative_plant_id;
           }

           global $DB;

           $all_ap = Authoritative_Plant::getAllFromDb(['flag_delete' => FALSE], $DB);
           usort($all_ap,'Authoritative_Plant::cmp');

           $rendered = '<select name="authoritative-plant-id" id="authoritative-plant-id">'."\n";
           foreach ($all_ap as $ap) {
               $rendered .= '  '.$ap->renderAsOption($ap->authoritative_plant_id == $default_selected)."\n";
           }
           $rendered .= '</select>';

           return $rendered;
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

        public function renderAsListItem($idstr='',$classes_array = [],$other_attribs_hash = []) {
            global $USER,$ACTIONS;
            $actions_attribs = '';

            if ($USER->canActOnTarget($ACTIONS['edit'],$this)) {
                $actions_attribs .= ' data-can-edit="1"';
            }
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().$actions_attribs.'>';
            $li_elt .= $this->renderAsLink().'</li>';
//            $li_elt .= '<a href="/app_code/authoritative_plant.php?authoritative_plant_id='.$this->authoritative_plant_id.'">'.htmlentities($this->renderAsShortText()).'</a></li>';
            return $li_elt;
        }

        public function renderAsViewEmbed() {
            $this->cacheExtras();

            $rendered = '<div id="authoritative_plant_embed_'.$this->authoritative_plant_id.'" class="authoritative-plant embedded" data-authoritative_plant_id="'.$this->authoritative_plant_id.'">
  <h3>'.$this->renderAsShortText().'</h3>
  <ul class="base-info">
    <li><span class="field-label">'.util_lang('class').'</span> : <span class="field-value taxonomy taxonomy-class">'.htmlentities($this->class).'</span></li>
    <li><span class="field-label">'.util_lang('order').'</span> : <span class="field-value taxonomy taxonomy-order">'.htmlentities($this->order).'</span></li>
    <li><span class="field-label">'.util_lang('family').'</span> : <span class="field-value taxonomy taxonomy-family">'.htmlentities($this->family).'</span></li>
    <li><span class="field-label">'.util_lang('genus').'</span> : <span class="field-value taxonomy taxonomy-genus">'.htmlentities($this->genus).'</span></li>
    <li><span class="field-label">'.util_lang('species').'</span> : <span class="field-value taxonomy taxonomy-species">'.htmlentities($this->species).'</span></li>
    <li><span class="field-label">'.util_lang('variety').'</span> : <span class="field-value taxonomy taxonomy-variety">\''.htmlentities($this->variety).'\'</span></li>
    <li><span class="field-label">'.util_lang('catalog_identifier').'</span> : <span class="field-value">'.htmlentities($this->catalog_identifier).'</span></li>
  </ul>
  <ul class="extra-info">
';
            foreach ($this->extras as $extra) {
                $rendered .='    '.$extra->renderAsListItem()."\n";
            }
            $rendered .='  </ul>
</div>';

            return $rendered;
        }


        public function renderAsView() {
            $this->cacheExtras();
            $this->cacheNotebookPages();
            $this->cacheSpecimens();

            $rendered = '<div id="authoritative_plant_view_'.$this->authoritative_plant_id.'" class="authoritative-plant" data-authoritative_plant_id="'.$this->authoritative_plant_id.'">
  <h3><a href="'.APP_ROOT_PATH.'/app_code/authoritative_plant.php?action=list">'.util_lang('authoritative_plant','properize').'</a>: '.$this->renderAsShortText().'</h3>
  <ul class="base-info">
    <li><span class="field-label">'.util_lang('class').'</span> : <span class="field-value taxonomy taxonomy-class">'.htmlentities($this->class).'</span></li>
    <li><span class="field-label">'.util_lang('order').'</span> : <span class="field-value taxonomy taxonomy-order">'.htmlentities($this->order).'</span></li>
    <li><span class="field-label">'.util_lang('family').'</span> : <span class="field-value taxonomy taxonomy-family">'.htmlentities($this->family).'</span></li>
    <li><span class="field-label">'.util_lang('genus').'</span> : <span class="field-value taxonomy taxonomy-genus">'.htmlentities($this->genus).'</span></li>
    <li><span class="field-label">'.util_lang('species').'</span> : <span class="field-value taxonomy taxonomy-species">'.htmlentities($this->species).'</span></li>
    <li><span class="field-label">'.util_lang('variety').'</span> : <span class="field-value taxonomy taxonomy-variety">\''.htmlentities($this->variety).'\'</span></li>
    <li><span class="field-label">'.util_lang('catalog_identifier').'</span> : <span class="field-value">'.htmlentities($this->catalog_identifier).'</span></li>
  </ul>
  <h4>'.util_lang('details','properize').'</h4>
  <ul class="extra-info">
';
            foreach ($this->extras as $extra) {
                $rendered .='    '.$extra->renderAsListItem()."\n";
            }
            $rendered .='  </ul>
  <h4>'.util_lang('notebook_pages','properize').'</h4>
  <ul class="notebook-pages">
';
            global $USER,$ACTIONS;
            foreach ($this->notebook_pages as $np) {
                if ($USER->canActOnTarget($ACTIONS['view'],$np)) {
                    $rendered .='    '.$np->renderAsListItemForNotebook()."\n";
                }
            }

            $rendered .= '  </ul>
  <h4>'.util_lang('specimens','properize').'</h4>
  <ul class="specimens">
';
            foreach ($this->specimens as $specimen) {
                $rendered .= '    <li>'.$specimen->renderAsViewEmbed()."</li>\n";
            }

            $rendered .='  </ul>
</div>';

            return $rendered;
        }

        public function renderAsOption($flag_is_selected=false) {
            $rendered = '<option data-authoritative_plant_id="'.$this->authoritative_plant_id.'" value="'.$this->authoritative_plant_id.'"'.($flag_is_selected ? ' selected="selected"' : '').'>'.$this->renderAsShortText().'</option>';
            return $rendered;
        }
    }