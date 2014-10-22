<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfNotebookPage extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testNotebookPageAtributesExist() {
			$this->assertEqual(count(Notebook_Page::$fields), 9);

            $this->assertTrue(in_array('notebook_page_id', Notebook_Page::$fields));
            $this->assertTrue(in_array('created_at', Notebook_Page::$fields));
            $this->assertTrue(in_array('updated_at', Notebook_Page::$fields));
            $this->assertTrue(in_array('notebook_id', Notebook_Page::$fields));
            $this->assertTrue(in_array('authoritative_plant_id', Notebook_Page::$fields));
            $this->assertTrue(in_array('notes', Notebook_Page::$fields));
            $this->assertTrue(in_array('flag_workflow_published', Notebook_Page::$fields));
            $this->assertTrue(in_array('flag_workflow_validated', Notebook_Page::$fields));
            $this->assertTrue(in_array('flag_delete', Notebook_Page::$fields));
		}

		//// static methods

		function testCmp() {
            $n1 = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101],$this->DB);
            $n2 = Notebook_Page::getOneFromDb(['notebook_page_id'=>1102],$this->DB);

//            util_prePrintR($n1);
//            util_prePrintR($n2);

			$this->assertEqual(Notebook_Page::cmp($n1, $n2), -1);
			$this->assertEqual(Notebook_Page::cmp($n1, $n1), 0);
			$this->assertEqual(Notebook_Page::cmp($n2, $n1), 1);

            $nps = Notebook_Page::getAllFromDb([],$this->DB);

            usort($nps,'Notebook_Page::cmp');

            $this->assertEqual(1101,$nps[0]->notebook_page_id);
            $this->assertEqual(1102,$nps[1]->notebook_page_id);
            $this->assertEqual(1103,$nps[2]->notebook_page_id);
            $this->assertEqual(1104,$nps[3]->notebook_page_id);
        }

        function testCreateNewNotebookPageForNotebook() {
            $np = Notebook_Page::createNewNotebookPageForNotebook(1001,$this->DB);

            $this->assertEqual('NEW',$np->notebook_page_id);
            $this->assertNotEqual('',$np->created_at);
            $this->assertNotEqual('',$np->updated_at);
            $this->assertEqual(1001, $np->notebook_id);
            $this->assertEqual('0',$np->authoritative_plant_id);
            $this->assertEqual(util_lang('new_notebook_page_notes'),$np->notes);
            $this->assertEqual('',$np->flag_workflow_published);
            $this->assertEqual('',$np->flag_workflow_validated);
            $this->assertEqual('',$np->flag_delete);
        }

        //// instance methods - related data

        function testGetNotebook() {
            $np1 = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101], $this->DB);

            $n = $np1->getNotebook();

            $this->assertEqual(1001,$n->notebook_id);
        }

        function testGetAuthoritativePlant() {
            $np1 = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101], $this->DB);

            $p = $np1->getAuthoritativePlant();

            $this->assertEqual(5001,$p->authoritative_plant_id);
        }

        function testGetAuthoritativePlant_newNotebook() {
            $np = Notebook_Page::createNewNotebookPageForNotebook(1001,$this->DB);

            $ap = $np->getAuthoritativePlant();

            $this->assertEqual(false,$ap);
        }

        function testLoadPageFields() {
            $np1 = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101], $this->DB);

            $np1->loadPageFields();

            $this->assertEqual(4,count($np1->page_fields));

            $this->assertEqual(1204,$np1->page_fields[0]->notebook_page_field_id);
            $this->assertEqual(1201,$np1->page_fields[1]->notebook_page_field_id);
            $this->assertEqual(1202,$np1->page_fields[2]->notebook_page_field_id);
            $this->assertEqual(1203,$np1->page_fields[3]->notebook_page_field_id);
        }

        function testLoadPageSpecimens() {
            $np1 = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101], $this->DB);

            $np1->loadSpecimens();

            $this->assertEqual(2,count($np1->specimens));

            $this->assertEqual(8003,$np1->specimens[0]->specimen_id);
            $this->assertEqual(8002,$np1->specimens[1]->specimen_id);
        }

        //// instance methods - object itself

        function testRenderAsListItem_Editor() {
            $np = Notebook_Page::getOneFromDb(['notebook_page_id' => 1101], $this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $plant = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);

            $rendered = $np->renderAsListItem();
            $canonical = '<li data-notebook_page_id="1101" data-created_at="'.$np->created_at.'" data-updated_at="'.$np->updated_at.'" data-notebook_id="1001" data-authoritative_plant_id="5001" data-notes="testing notebook page the first in testnotebook1, owned by user 101" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0" data-can-edit="1"><a href="'.APP_ROOT_PATH.'/app_code/notebook_page.php?action=view&notebook_page_id=1101">'.htmlentities($plant->renderAsShortText()).'</a></li>';
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsListItem_NonEditor() {
            $np = Notebook_Page::getOneFromDb(['notebook_page_id' => 1104], $this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $plant = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);

            $rendered = $np->renderAsListItem();
            $canonical = '<li data-notebook_page_id="1104" data-created_at="'.$np->created_at.'" data-updated_at="'.$np->updated_at.'" data-notebook_id="1004" data-authoritative_plant_id="5001" data-notes="first page of testnotebook4, owned by user 110" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0"><a href="'.APP_ROOT_PATH.'/app_code/notebook_page.php?action=view&notebook_page_id=1104">'.htmlentities($plant->renderAsShortText()).'</a></li>';
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsListItemForNotebook() {
            $np = Notebook_Page::getOneFromDb(['notebook_page_id' => 1101], $this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $nb = Notebook::getOneFromDb(['notebook_id'=>1001],$this->DB);

            $rendered = $np->renderAsListItemForNotebook();
            $canonical = '<li data-notebook_page_id="1101" data-created_at="'.$np->created_at.'" data-updated_at="'.$np->updated_at.'" data-notebook_id="1001" data-authoritative_plant_id="5001" data-notes="testing notebook page the first in testnotebook1, owned by user 101" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0" data-can-edit="1"><a href="'.APP_ROOT_PATH.'/app_code/notebook_page.php?action=view&notebook_page_id=1101">'.util_lang('page_in_notebook').' '.htmlentities($nb->name).'</a></li>';
//            echo "<pre>\n".htmlentities($canonical)."\n--------------\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsButtonEdit() {
            $np = Notebook_Page::getOneFromDb(['notebook_page_id' => 1101], $this->DB);

            $canonical = '<a id="btn-edit" href="'.APP_ROOT_PATH.'/app_code/notebook_page.php?action=edit&notebook_page_id='.$np->notebook_page_id.'" class="edit_link btn" ><i class="icon-edit"></i> '.util_lang('edit').'</a>';
            $rendered = $np->renderAsButtonEdit();

//            echo "<pre>\n".htmlentities($canonical)."\n--------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsView() {
            $np = Notebook_Page::getOneFromDb(['notebook_page_id' => 1101], $this->DB);
            $np->loadPageFields();
            $np->loadSpecimens();
            $n = $np->getNotebook();
            $ap = $np->getAuthoritativePlant();


//            $this->assertEqual(2,count($np->specimens));


            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $canonical = '<div id="rendered_notebook_page_1101" class="rendered_notebook_page" '.$np->fieldsAsDataAttribs().' data-can-edit="1">
  <h3 class="notebook_page_title">'.$n->renderAsLink().':</h3>
  '.$ap->renderAsViewEmbed().'
  <div class="info-timestamps"><span class="created_at">'.util_lang('created_at').' '.util_datetimeFormatted($np->created_at).'</span>, <span class="updated_at">'.util_lang('updated_at').' '.util_datetimeFormatted($np->updated_at).'</span></div>
  <div class="info-owner">'.util_lang('owned_by').' <a href="'.APP_ROOT_PATH.'/app_code/user.php?action=view&user_id=101">'.$USER->screen_name.'</a></div>
  <div class="info-workflow"><span class="published_state">'.util_lang('published_false').'</span>, <span class="verified_state">'.util_lang('verified_false').'</span></div>
  <div class="notebook-page-notes">testing notebook page the first in testnotebook1, owned by user 101</div>
  <h4>'.ucfirst(util_lang('metadata')).'</h4>'."\n";

            $canonical .= '  <ul class="notebook-page-fields">'."\n";
            if ($np->page_fields) {
                $prev_pf_structure_id = $np->page_fields[0]->label_metadata_structure_id;
                foreach ($np->page_fields as $pf) {
                    $spacer_class = '';
                    if ($pf->label_metadata_structure_id != $prev_pf_structure_id) {
                        $spacer_class = 'spacing-list-item';
                    }
                    $canonical .= '    '.$pf->renderAsListItem('list_item-notebook_page_field_'.$pf->notebook_page_field_id,[$spacer_class])."\n";
                    $prev_pf_structure_id = $pf->label_metadata_structure_id;
                }
                //            $rendered .= $add_field_button_li;
            } else {
                $canonical .= '<li>'.util_lang('no_metadata','ucfirst').'</li>'."\n";
            }
            $canonical .='  </ul>'."\n";

//            $canonical .= '  <ul class="notebook-page-fields">
//    <li id="list_item-notebook_page_field_1204" class="" data-notebook_page_field_id="1204" data-created_at="2014-10-22 12:41:08" data-updated_at="2014-10-22 12:41:08" data-notebook_page_id="1101" data-label_metadata_structure_id="6004" data-value_metadata_term_value_id="0" data-value_open="wavy-ish" data-flag_delete="0"><div class="notebook-page-field-label field-label" title="info about the individual leaves of the plant">leaf</div> : <div class="notebook-page-field-value field-value"><span class="open-value">wavy-ish</span></div></li>
//    <li id="list_item-notebook_page_field_1201" class="spacing-list-item" data-notebook_page_field_id="1201" data-created_at="2014-10-22 12:41:08" data-updated_at="2014-10-22 12:41:08" data-notebook_page_id="1101" data-label_metadata_structure_id="6002" data-value_metadata_term_value_id="6202" data-value_open="" data-flag_delete="0"><div class="notebook-page-field-label field-label" title="the size of the flower in its largest dimension">flower size</div> : <div class="notebook-page-field-value field-value" title="smaller than the smallest thickness of your pinkie finger">3 mm - 1cm</div></li>
//    <li id="list_item-notebook_page_field_1202" class="" data-notebook_page_field_id="1202" data-created_at="2014-10-22 12:41:08" data-updated_at="2014-10-22 12:41:08" data-notebook_page_id="1101" data-label_metadata_structure_id="6002" data-value_metadata_term_value_id="6203" data-value_open="" data-flag_delete="0"><div class="notebook-page-field-label field-label" title="the size of the flower in its largest dimension">flower size</div> : <div class="notebook-page-field-value field-value" title="up to the largest thickness of your thumb">1-3 cm</div></li>
//    <li id="list_item-notebook_page_field_1203" class="spacing-list-item" data-notebook_page_field_id="1203" data-created_at="2014-10-22 12:41:08" data-updated_at="2014-10-22 12:41:08" data-notebook_page_id="1101" data-label_metadata_structure_id="6003" data-value_metadata_term_value_id="6211" data-value_open="" data-flag_delete="0"><div class="notebook-page-field-label field-label" title="the primary / dominant color of the flower">flower primary color</div> : <div class="notebook-page-field-value field-value" title="">red</div></li>
//  </ul>';
            $canonical .= '  <h4>'.ucfirst(util_lang('specimens')).'</h4>
  <ul class="specimens">
';
            foreach ($np->specimens as $specimen) {
                $canonical .= '    <li>'.$specimen->renderAsViewEmbed()."</li>\n";
            }
            $canonical .= '  </ul>
</div>';

            $rendered = $np->renderAsView();

//            echo "<pre>
//-----------
//".htmlentities($canonical)."
//-----------
//".htmlentities($rendered)."
//-----------
//</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsEdit_owner() {
//            $this->todo();
            $np = Notebook_Page::getOneFromDb(['notebook_page_id' => 1101], $this->DB);
            $np->loadPageFields();
            $np->loadSpecimens();
            $n = $np->getNotebook();
            $ap = $np->getAuthoritativePlant();

            global $USER,$DB;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);
            $DB = $this->DB;

//            $this->todo('add canonical code for how to handle authoritative plant selection');

//            <h3 class="notebook_page_title">'.$n->renderAsLink().': '.$ap->renderAsShortText().'</h3>

                $canonical = '<div id="rendered_notebook_page_1101" class="rendered_notebook_page edit_rendered_notebook_page" '.$np->fieldsAsDataAttribs().' data-can-edit="1">
<form id="form-edit-notebook-page-base-data" action="'.APP_ROOT_PATH.'/app_code/notebook_page.php">
  <input type="hidden" name="action" value="update"/>
  <input type="hidden" name="notebook_page_id" value="'.$np->notebook_page_id.'"/>
  <input type="hidden" name="notebook_id" value="1001"/>
  <div id="actions"><button id="edit-submit-control" class="btn btn-success" type="submit" name="edit-submit-control" value="update"><i class="icon-ok-sign icon-white"></i> Update</button>
  <a id="edit-cancel-control" class="btn" href="/digitalfieldnotebooks/app_code/notebook_page.php?action=view&notebook_page_id=1101"><i class="icon-remove"></i> Cancel</a>  <a id="edit-delete-notebook-page-control" class="btn btn-danger" href="/digitalfieldnotebooks/app_code/notebook_page.php?action=delete&notebook_page_id=1101"><i class="icon-trash icon-white"></i> Delete</a></div>
<h4>'.util_lang('page_in_notebook','ucfirst').' <a href="'.APP_ROOT_PATH.'/app_code/notebook.php?action=view&notebook_id='.$n->notebook_id.'" id="parent-notebook-link">'.htmlentities($n->name).'</a></h4>
<a class="show-hide-control" href="#" data-for_elt_id="select_new_authoritative_plant_1101">select or change the plant</a>  <div id="select_new_authoritative_plant_1101" class="select_new_authoritative_plant">'.Authoritative_Plant::renderControlSelectAllAuthoritativePlants($ap->authoritative_plant_id).'</div>
  '.$ap->renderAsViewEmbed().'
  <div class="info-timestamps"><span class="created_at">'.util_lang('created_at').' '.util_datetimeFormatted($np->created_at).'</span>, <span class="updated_at">'.util_lang('updated_at').' '.util_datetimeFormatted($np->updated_at).'</span></div>
  <div class="info-owner">'.util_lang('owned_by').' <a href="'.APP_ROOT_PATH.'/app_code/user.php?action=view&user_id=101">'.htmlentities($USER->screen_name).'</a></div>
<div class="control-workflows">  <span class="published_state workflow-control"><input id="notebook-page-workflow-publish-control" type="checkbox" name="flag_workflow_published" value="1" /> '.util_lang('publish').'</span>, <span class="verified_state workflow-info">'.util_lang('verified_false').'</span><br/>
</div>
  <div class="notebook_page_notes"><textarea id="notebook-page-notes" name="notes" rows="4" cols="120">testing notebook page the first in testnotebook1, owned by user 101</textarea></div>
  <h4>'.ucfirst(util_lang('metadata')).'</h4>
  <ul class="notebook-page-fields">
';

            $canonical .= '    <li><a href="#" id="add_new_notebook_page_field_button" class="btn">'.util_lang('add_notebook_page_field').'</a></li>'."\n";
            if ($np->page_fields) {
                $prev_pf_structure_id = $np->page_fields[0]->label_metadata_structure_id;
                foreach ($np->page_fields as $pf) {
                    $spacer_class = '';
                    if ($pf->label_metadata_structure_id != $prev_pf_structure_id) {
                        $spacer_class = 'spacing-list-item';
                    }
                    $canonical .= '    '.$pf->renderAsListItemEdit('list_item-notebook_page_field_'.$pf->notebook_page_field_id,[$spacer_class])."\n";
                    $prev_pf_structure_id = $pf->label_metadata_structure_id;
                }
            } else {
                $canonical .= '<li>'.util_lang('no_metadata','ucfirst').'</li>'."\n";
            }


            $canonical .= '  </ul>
  <h4>'.ucfirst(util_lang('specimens')).'</h4>
  <ul class="specimens">
';
//            $this->todo('refine canonical code for new page specimen');
            $canonical .= '    <li><a href="#" id="add_new_specimen_button" class="btn">'.util_lang('add_specimen').'</a></li>'."\n";
            foreach ($np->specimens as $specimen) {
                $canonical .= '    <li>'.$specimen->renderAsEditEmbed()."</li>\n";
            }
            $canonical .= '  </ul>'."\n";

            $canonical .= '<input type="hidden" id="initial_page_field_ids" name="initial_page_field_ids" value="'.implode(',', Db_Linked::arrayOfAttrValues($np->page_fields,'notebook_page_field_id') ).'"/>'."\n";
            $canonical .= '<input type="hidden" id="created_page_field_ids" name="created_page_field_ids" value=""/>'."\n";
            $canonical .= '<input type="hidden" id="deleted_page_field_ids" name="deleted_page_field_ids" value=""/>'."\n";
            $canonical .= '<input type="hidden" id="initial_specimen_ids" name="initial_specimen_ids" value="'.implode(',', Db_Linked::arrayOfAttrValues($np->specimens,'specimen_id') ).'"/>'."\n";
            $canonical .= '<input type="hidden" id="created_specimen_ids" name="created_specimen_ids" value=""/>'."\n";
            $canonical .= '<input type="hidden" id="deleted_specimen_ids" name="deleted_specimen_ids" value=""/>'."\n";

            $canonical .= '</form>
</div>';

            $rendered = $np->renderAsEdit();

//            echo "<pre>
//-----------
//".htmlentities($canonical)."
//-----------
//".htmlentities($rendered)."
//-----------
//</pre>";
            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsEdit_newNotebookPage() {
            $np = Notebook_Page::createNewNotebookPageForNotebook(1001,$this->DB);
            $n = $np->getNotebook();

            global $USER,$DB;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);
            $DB = $this->DB;

            $canonical = '<div id="rendered_notebook_page_NEW" class="rendered_notebook_page edit_rendered_notebook_page" '.$np->fieldsAsDataAttribs().' data-can-edit="1">
<form id="form-edit-notebook-page-base-data" action="'.APP_ROOT_PATH.'/app_code/notebook_page.php">
  <input type="hidden" name="action" value="update"/>
  <input type="hidden" name="notebook_page_id" value="NEW"/>
  <input type="hidden" name="notebook_id" value="1001"/>
  <div id="actions"><button id="edit-submit-control" class="btn btn-success" type="submit" name="edit-submit-control" value="update"><i class="icon-ok-sign icon-white"></i> Save</button>
  <a id="edit-cancel-control" class="btn" href="'.APP_ROOT_PATH.'/app_code/notebook.php?action=edit&notebook_id=1001"><i class="icon-remove"></i> Cancel</a></div>
<h4>In notebook <a href="'.APP_ROOT_PATH.'/app_code/notebook.php?action=view&notebook_id=1001" id="parent-notebook-link">testnotebook1</a></h4>
  <div id="select_new_authoritative_plant_NEW" class="NEW_select_new_authoritative_plant">'.Authoritative_Plant::renderControlSelectAllAuthoritativePlants().'</div>
  <div class="info-timestamps"><span class="created_at">'.util_lang('created_at').' '.util_datetimeFormatted($np->created_at).'</span>, <span class="updated_at">'.util_lang('updated_at').' '.util_datetimeFormatted($np->updated_at).'</span></div>
  <div class="info-owner">owned by <a href="'.APP_ROOT_PATH.'/app_code/user.php?action=view&user_id=101">'.htmlentities($USER->screen_name).'</a></div>
<div class="control-workflows">  <span class="published_state workflow-info">'.util_lang('published_false').'</span>, <span class="verified_state workflow-info">'.util_lang('verified_false').'</span></div>
  <div class="notebook_page_notes"><textarea id="notebook-page-notes" name="notes" rows="4" cols="120">'.util_lang('new_notebook_page_notes').'</textarea></div>
</form>
</div>';
            $rendered = $np->renderAsEdit();

//            echo "<pre>
//-----------
//".htmlentities($canonical)."
//-----------
//".htmlentities($rendered)."
//-----------
//</pre>";
            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testDoDelete() {
            $np = Notebook_Page::getOneFromDb(['notebook_page_id' => 1101], $this->DB);
            $np->loadSpecimens();
            $np->loadPageFields();

            $this->assertTrue($np->matchesDb);
            $this->assertTrue($np->page_fields[0]->matchesDb);
            $this->assertTrue($np->page_fields[1]->matchesDb);
            $this->assertTrue($np->page_fields[2]->matchesDb);
            $this->assertTrue($np->page_fields[3]->matchesDb);
            $this->assertTrue($np->specimens[0]->matchesDb);
            $this->assertTrue($np->specimens[1]->matchesDb);

            //***********
            $np->doDelete();
            //***********

            $np2 = Notebook_Page::createNewNotebookPageForNotebook(1001,$this->DB);
            $this->assertFalse($np2->matchesDb);


            $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1201],$this->DB);
            $this->assertFalse($npf->matchesDb);

            $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1202],$this->DB);
            $this->assertFalse($npf->matchesDb);

            $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1203],$this->DB);
            $this->assertFalse($npf->matchesDb);

            $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1204],$this->DB);
            $this->assertFalse($npf->matchesDb);


            $s = Specimen::getOneFromDb(['specimen_id'=>8002],$this->DB);
            $this->assertFalse($s->matchesDb);

            $s = Specimen::getOneFromDb(['specimen_id'=>8003],$this->DB);
            $this->assertFalse($s->matchesDb);


            $si = Specimen_Image::getOneFromDb(['specimen_image_id'=>8103],$this->DB);
            $this->assertFalse($si->matchesDb);

            $si = Specimen_Image::getOneFromDb(['specimen_image_id'=>8104],$this->DB);
            $this->assertFalse($si->matchesDb);
        }

    }