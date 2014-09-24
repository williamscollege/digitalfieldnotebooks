<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Notebook extends Db_Linked {
		public static $fields = array('notebook_id', 'created_at', 'updated_at', 'user_id', 'name', 'notes', 'flag_workflow_published', 'flag_workflow_validated', 'flag_delete');
		public static $primaryKeyField = 'notebook_id';
		public static $dbTable = 'notebooks';

        public $pages;

		public function __construct($initsHash) {
			parent::__construct($initsHash);


			// now do custom stuff
			// e.g. automatically load all accessibility info associated with the user
            $this->flag_workflow_published = false;
            $this->flag_workflow_validated = false;
            $this->pages = array();
		}

		public static function cmp($a, $b) {
			if ($a->name == $b->name) {
                if ($a->user_id == $b->user_id) {
                    return 0;
                }
                $ua = User::getOneFromDb(['user_id' => $a->user_id], $a->dbConnection);
                $ub = User::getOneFromDb(['user_id' => $b->user_id], $b->dbConnection);
                return User::cmp($ua,$ub);
			}
			return ($a->name < $b->name) ? -1 : 1;
		}

        public function cachePages() {
            if (! $this->pages) {
                $this->loadPages();
            }
        }

        public function loadPages() {
            $this->pages = Notebook_Page::getAllFromDb(['notebook_id'=>$this->notebook_id],$this->dbConnection);
            usort($this->pages,'Notebook_Page::cmp');
        }

        public function getUser() {
            return User::getOneFromDb(['user_id'=>$this->user_id],$this->dbConnection);
        }

        public function renderAsListItem($idstr='',$classes_array = [],$other_attribs_hash = []) {
            global $USER,$ACTIONS;
            $actions_attribs = '';

            if ($USER->user_id == $this->user_id) {
                array_push($classes_array,'owned-object');
                $actions_attribs .= ' data-can-edit="1"';
            } elseif ($USER->canActOnTarget($ACTIONS['edit'],$this)) {
                $actions_attribs .= ' data-can-edit="1"';
            }
            $li_elt = substr(util_listItemTag($idstr,$classes_array,$other_attribs_hash),0,-1);
            $li_elt .= ' '.$this->fieldsAsDataAttribs().$actions_attribs.'>';
            $li_elt .= '<a href="'.APP_ROOT_PATH.'/app_code/notebook.php?notebook_id='.$this->notebook_id.'">'.htmlentities($this->name).'</a></li>';
            return $li_elt;
        }

        public function renderAsButtonEdit() {
            $btn = '<a id="btn-edit" href="'.APP_ROOT_PATH.'/app_code/notebook.php?action=edit&notebook_id='.$this->notebook_id.'" class="edit_link btn" >'.util_lang('edit').'</a>';
            return $btn;
        }

        function renderAsLink($action='view') {
            $action = Action::sanitizeAction($action);

            $link = '<a href="'.APP_ROOT_PATH.'/app_code/notebook.php?action='.$action.'&notebook_id='.$this->notebook_id.'">'.htmlentities($this->name).'</a>';

            return $link;
        }

        function renderAsView() {
            global $USER,$ACTIONS;
            $actions_attribs = '';

            if ($USER->user_id == $this->user_id) {
                $actions_attribs .= ' data-can-edit="1"';
            } elseif ($USER->canActOnTarget($ACTIONS['edit'],$this)) {
                $actions_attribs .= ' data-can-edit="1"';
            }

            $notebook_owner = $USER;
            if ($this->user_id != $USER->user_id) {
                $notebook_owner = $this->getUser();
            }

            $this->cachePages();

            $rendered = '<div id="rendered_notebook_'.$this->notebook_id.'" class="rendered_notebook" '.$this->fieldsAsDataAttribs().$actions_attribs.'>'."\n".
'  <h3 class="notebook_title">'.ucfirst(util_lang('notebook')).': '.$this->name.'</h3>'."\n".
'  <span class="created_at">'.util_lang('created_at').' '.util_datetimeFormatted($this->created_at).'</span>, <span class="updated_at">'.util_lang('updated_at').' '.util_datetimeFormatted($this->updated_at).'</span><br/>'."\n".
'  <span class="owner">'.util_lang('owned_by').' '.$notebook_owner->screen_name.'</span><br/>'."\n".
'  <span class="published_state">'.($this->flag_workflow_published ? util_lang('published_true') : util_lang('published_false'))
                .'</span>, <span class="verified_state">'.($this->flag_workflow_validated ? util_lang('verified_true') : util_lang('verified_false'))
                .'</span><br/>'."\n".
'  <div class="notebook_notes">'.htmlentities($this->notes).'</div>'."\n".
'  <h4>'.ucfirst(util_lang('pages')).'</h4>'."\n".
'  <ul id="list-of-notebook-pages" data-notebook-page-count="'.count($this->pages).'">'."\n";
            if (count($this->pages) > 0) {
                $page_counter = 0;
                foreach ($this->pages as $p) {
                    $page_counter++;
                    $rendered .= '    '.$p->renderAsListItem('notebook-page-item-'.$page_counter)."\n";
                }
            } else {
                $rendered .= '    <li>'.util_lang('zero_pages').'</li>'."\n";
            }
// NOTE: add page control only in edit mode, not view mode!
//            if ($USER->canActOnTarget($ACTIONS['edit'],$this)) {
//                $rendered .= '    <li><a href="'.APP_ROOT_PATH.'/app_code/notebook_page.php?action=create&notebook_id='.$this->notebook_id.'" id="btn-add-notebook-page" class="creation_link btn">'.util_lang('add_notebook_page').'</a></li>'."\n";
//            }
            $rendered .=
'  </ul>'."\n".
'</div>';

            return $rendered;
        }

        function renderAsEdit() {
            global $USER,$ACTIONS;
            $actions_attribs = '';

            if ($USER->user_id == $this->user_id) {
                $actions_attribs .= ' data-can-edit="1"';
            } elseif ($USER->canActOnTarget($ACTIONS['edit'],$this)) {
                $actions_attribs .= ' data-can-edit="1"';
            }

            $notebook_owner = $USER;
            if ($this->user_id != $USER->user_id) {
                $notebook_owner = $this->getUser();
            }

            $this->cachePages();

            $rendered = '<div id="edit_rendered_notebook_'.$this->notebook_id.'" class="edit_rendered_notebook" '.$this->fieldsAsDataAttribs().$actions_attribs.'>'."\n".
                '<form action="'.APP_ROOT_PATH.'/app_code/notebook.php">'."\n".
                '  <input type="hidden" name="action" value="update"/>'."\n".
                '  <input type="hidden" name="notebook_id" value="'.$this->notebook_id.'"/>'."\n".
                '  <h3 class="notebook_title">'.ucfirst(util_lang('notebook')).': <input id="notebook-name" type="text" name="name" value="'.$this->name.'"/></h3>'."\n".
                '  <span class="created_at">'.util_lang('created_at').' '.util_datetimeFormatted($this->created_at).'</span>, <span class="updated_at">'.util_lang('updated_at').' '.util_datetimeFormatted($this->updated_at).'</span><br/>'."\n".
                '  <span class="owner">'.util_lang('owned_by').' <a href="'.APP_ROOT_PATH.'/app_code/user.php?action=view&user_id='.$notebook_owner->user_id.'">'.$notebook_owner->screen_name.'</a></span><br/>'."\n";

            if ($USER->canActOnTarget('publish',$this)) {
                $rendered .= '  <span class="published_state"><input id="notebook-workflow-publish-control" type="checkbox" name="flag_workflow_published"'.($this->flag_workflow_published ?  ' checked="checked"' : '').' /> '
                    .util_lang('published').'</span>,';
            } else {
                $rendered .= '  <span class="published_state">'.($this->flag_workflow_published ? util_lang('published_true') : util_lang('published_false'))
                    .'</span>,';
            }

            if ($USER->canActOnTarget('verify',$this)) {
                $rendered .= '  <span class="verified_state"><input id="notebook-workflow-validate-control" type="checkbox" name="flag_workflow_validated"'.($this->flag_workflow_validated ?  ' checked="checked"' : '').' /> '
                    .util_lang('verified').'</span>,';
            } else {
                $rendered .= ' <span class="verified_state">'.($this->flag_workflow_validated ? util_lang('verified_true') : util_lang('verified_false'))
                    .'</span>';
            }

            $rendered .= '<br/>'."\n".
                '  <div class="notebook_notes"><textarea id="notebook-notes" name="notes" rows="4" cols="120">'.htmlentities($this->notes).'</textarea></div>'."\n".
                '</form>'."\n".
                '  <h4>'.ucfirst(util_lang('pages')).'</h4>'."\n".
                '  <a href="'.APP_ROOT_PATH.'/app_code/notebook_page.php?action=create&notebook_id='.$this->notebook_id.'" class="btn">'.util_lang('add_notebook_page').'</a>'."\n".

                '  <ul id="list-of-notebook-pages" data-notebook-page-count="'.count($this->pages).'">'."\n";
            if (count($this->pages) > 0) {
                $page_counter = 0;
                foreach ($this->pages as $p) {
                    $page_counter++;
                    $rendered .= '    '.$p->renderAsListItem('notebook-page-item-'.$page_counter)."\n";
                }
            } else {
                $rendered .= '    <li>'.util_lang('zero_pages').'</li>'."\n";
            }
// NOTE: add page control only in edit mode, not view mode!
//            if ($USER->canActOnTarget($ACTIONS['edit'],$this)) {
//                $rendered .= '    <li><a href="'.APP_ROOT_PATH.'/app_code/notebook_page.php?action=create&notebook_id='.$this->notebook_id.'" id="btn-add-notebook-page" class="creation_link btn">'.util_lang('add_notebook_page').'</a></li>'."\n";
//            }
            $rendered .=
                '  </ul>'."\n".
                    '</div>';

            return $rendered;
        }

    }
