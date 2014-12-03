<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class NotebookPageEditAndCreateTest extends WMSWebTestCase {

    function setUp() {
        createAllTestData($this->DB);
        global $CUR_LANG_SET;
        $CUR_LANG_SET = 'en';
    }

    function tearDown() {
        removeAllTestData($this->DB);
    }

    function doLoginBasic() {
        $this->get('http://localhost/digitalfieldnotebooks/');
        $this->assertCookie('PHPSESSID');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);

        $this->click('Sign in');

        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');
    }

    function doLoginAdmin() {
        makeAuthedTestUserAdmin($this->DB);
        $this->doLoginBasic();
    }

    function goToView($id) {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=view&notebook_page_id='.$id);
    }

    function goToEdit($id) {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit&notebook_page_id='.$id);
    }

    function checkBasicAsserts() {
        $this->assertNoText('IMPLEMENTED');
        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/fatal error/i');
    }

    //-----------------------------------------------------------------------------------------------------------------

    function testMissingNotebookPageIdShowsNotebookListInstead() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit');
//
////        $this->showContent();
//
        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_notebook_page_specified'));
        $this->assertEltByIdHasAttrOfValue('list-of-user-notebooks','id','list-of-user-notebooks');
//        exit;
    }

    function testNonexistentNotebookPageShowsNotebookListInstead() {
//        $this->todo();
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit&notebook_page_id=999');

        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_notebook_page_found'));
        $this->assertEltByIdHasAttrOfValue('list-of-user-notebooks','id','list-of-user-notebooks');
    }

    function testNoEditPermDefaultsToView() {
//        $this->todo();
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit&notebook_page_id=1104');

        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook_page')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_permission'));
        $this->assertEltByIdHasAttrOfValue('rendered_notebook_page_1104','class','rendered_notebook_page');

//        $this->showContent();
    }

    function testEditAccessControl_public() {
//        $this->todo('basic public access check - no access defaults to view');
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit&notebook_page_id=1104');

        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook_page')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_permission'));
        $this->assertEltByIdHasAttrOfValue('rendered_notebook_page_1104','class','rendered_notebook_page');
    }

    function testEditAccessControl_owner() {
//        $this->todo('basic access check as owner - no edit notebook owned by another');
        // NOTE: the latter is handled by testNoEditPermDefaultsToView

        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit&notebook_page_id=1101');

        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook_page')) ,$this->getBrowser()->getTitle());
//
////        $this->todo('basic access check as owner - can edit owned notebook');
        $this->assertNoText(util_lang('no_permission'));

//        $this->todo('editable fields exist');
        $this->assertFieldById('authoritative-plant-id');

//        $this->todo('publish option, no verify option');
        $this->assertFieldById('notebook-page-workflow-publish-control');
        $this->assertNoFieldById('notebook-page-workflow-validate-control');

        $this->assertEltByIdHasAttrOfValue('edit-submit-control','value','update');

//        $this->showContent();
    }

    function testEditAccessControl_admin() {
//        $this->todo('basic access check as admin');
        $this->doLoginAdmin();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit&notebook_page_id=1104');

        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook_page')) ,$this->getBrowser()->getTitle());

//        $this->todo('basic access check as admin - can edit non-owned notebook');
        $this->assertNoText(util_lang('no_permission'));

//        $this->todo('editable fields exist');
        $this->assertFieldById('authoritative-plant-id');

//        $this->todo('publish option and verify option');
        $this->assertFieldById('notebook-page-workflow-publish-control');
        $this->assertFieldById('notebook-page-workflow-validate-control');

        $this->assertEltByIdHasAttrOfValue('edit-submit-control','value','update');
    }

//
//    function testFormFieldLookups() {
//        $this->todo();
//    }

    function testRelatedDataListing() {
        // NOTE: this test is a serious integration test - it checks on a couple of levels down of related data (e.g. that the related specimen has the right related images)

        $np = Notebook_Page::getOneFromDb(['notebook_page_id' => 1101], $this->DB);
        $np->loadPageFields();
        $np->loadSpecimens();
        $n = $np->getNotebook();
        $ap = $np->getAuthoritativePlant();
        global $USER, $DB;
        $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);
        $DB = $this->DB;

        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit&notebook_page_id=1101');
        $this->checkBasicAsserts();

//        $this->todo('check link to containing notebook');
        $this->assertLink(htmlentities($n->name));

//        $this->todo('check link to owner');
        $this->assertLink(htmlentities($USER->screen_name));

//        $this->todo('check authoritative plant info');
        $this->assertEltByIdHasAttrOfValue('authoritative_plant_embed_5001','data-authoritative_plant_id','5001');
        $this->assertEltByIdHasAttrOfValue('authoritative_plant_extra_5101','data-authoritative_plant_extra_id','5101');

//        $this->todo('check notebook page fields');
        $this->assertEltByIdHasAttrOfValue('page_field_select_1201','name','page_field_select_1201');
        $this->assertEltByIdHasAttrOfValue('page_field_select_1202','name','page_field_select_1202');
        $this->assertEltByIdHasAttrOfValue('page_field_select_1203','name','page_field_select_1203');
        $this->assertEltByIdHasAttrOfValue('page_field_open_value_1204','name','page_field_open_value_1204');

        $this->assertEltByIdHasAttrOfValue('initial_page_field_ids','value','1204,1201,1202,1203');
        $this->assertEltByIdHasAttrOfValue('created_page_field_ids','value','');
        $this->assertEltByIdHasAttrOfValue('deleted_page_field_ids','value','');

//        $this->todo('check specimens');
        $this->assertEltByIdHasAttrOfValue('form-edit-specimen-8002','class','form-edit-specimen');
        $this->assertEltByIdHasAttrOfValue('specimen-image-8103','data-specimen_image_id','8103');
        $this->assertEltByIdHasAttrOfValue('specimen-image-8104','data-specimen_image_id','8104');
        $this->assertEltByIdHasAttrOfValue('form-edit-specimen-8003','class','form-edit-specimen');

        $this->assertEltByIdHasAttrOfValue('initial_specimen_ids','value','8003,8002');
        $this->assertEltByIdHasAttrOfValue('created_specimen_ids','value','');
        $this->assertEltByIdHasAttrOfValue('deleted_specimen_ids','value','');

    }

    function testBaseDataUpdate() {
        $this->doLoginBasic();
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit&notebook_page_id=1101');
        $this->checkBasicAsserts();

        $new_notes = 'new notes for the page';
        $new_specimen_notes = 'new notes for the specimen';

////      NOTE: the identifier to use for setField is the value of the name attribute of the field
        $this->setField('notes',$new_notes);

        // page field alteration
        $this->assertTrue($this->setField('page_field_select_1201','6204'));
        $this->assertTrue($this->setField('page_field_open_value_1204','new open value'));

        // page field addition
//        $this->todo('figure out how to do page field addition');
//        $this->todo('figure out how to do page deletion');

        // specimen alteration
        $this->assertTrue($this->setField('specimen-notes_8002',$new_specimen_notes));

//        $this->todo('figure out how to do specimen addition');
//        $this->todo('figure out how to do specimen deletion');

//        $this->showContent();

////        NOTE: the identifier to use for buttons is the value of the value attribute of the button
        $this->click('<i class="icon-ok-sign icon-white"></i> '.util_lang('update','properize'));
//
//        $this->showContent();
//
        $this->checkBasicAsserts();
        $this->assertText($new_notes);
//
        $np = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101],$this->DB);
        $this->assertEqual($np->notes,$new_notes);

//        $this->todo('check page field alteration on 1201');
        $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1201],$this->DB);
        $this->assertEqual($npf->value_metadata_term_value_id,6204);
        $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1204],$this->DB);
        $this->assertEqual($npf->value_open,'new open value');

//        $this->todo('check page field addition');
//        $this->todo('check page field deletion');

//        $this->todo('check specimen alteration on 8002');
        $s = Specimen::getOneFromDb(['specimen_id'=>8002],$this->DB);
        $this->assertEqual($s->notes,$new_specimen_notes);

//        $this->todo('check specimen addition');
//        $this->todo('check specimen deletion');
//        util_prePrintR(htmlentities($this->getBrowser()->getContent()));

        echo "<br><b>NOTE: skipping create and delete tests for pagefields and specimens because that requires javascript interaction</b><br/>\n";

    }

    function testCreateButton() {
        $n = Notebook::getOneFromDb(['notebook_id' => 1001], $this->DB);

        $this->doLoginBasic();
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=edit&notebook_id=1001');
        $this->checkBasicAsserts();


        $this->click(util_lang('add_notebook_page'));

        $this->checkBasicAsserts();
        $this->assertTitle(LANG_APP_NAME . ': ' . ucfirst(util_lang('page')));
        $this->assertLink(htmlentities($n->name));
        $this->assertEltByIdHasAttrOfValue('rendered_notebook_page_NEW','id','rendered_notebook_page_NEW');

//        $this->showContent();
    }


    function testNewNotebookPage() {
        $n = Notebook::getOneFromDb(['notebook_id' => 1001], $this->DB);

        $this->doLoginBasic();
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=edit&notebook_id=1001');
        $this->checkBasicAsserts();

        $this->click(util_lang('add_notebook_page'));

        $this->checkBasicAsserts();

        $this->assertEltByIdHasAttrOfValue('form-edit-notebook-page-base-data','action',APP_ROOT_PATH.'/app_code/notebook_page.php');

//        $this->showContent();
    }

    function testDeleteNotebookPage() {
        $np = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101],$this->DB);
        $this->assertTrue($np->matchesDb);

        $this->doLoginBasic();
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=delete&notebook_page_id=1101');
        $this->checkBasicAsserts();

        $np2 = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101],$this->DB);
        $this->assertFalse($np2->matchesDb);
    }

        function testToDo() {
        $this->todo('reordering controls for specimens, w/ corresponding update implementation on back end');
//        $this->todo('test fall backs and default behaviors');
//        $this->todo('test access control to edit page');
// NOTE: nothing here for notebooks       $this->todo('test look-up data form fields (not much for this, but gets messy once we get to pages)');
//        $this->todo('test data pre-population');
//        $this->todo('test existence of dynamic elements for in-place related data');
//        $this->todo('  ----------  build in-place editing fragments for related data, and associated tests (not much for this, but gets messy once we get to pages)');
//        $this->todo('test updating base data');
//        $this->todo('test updating related data via ajax');
//            $this->todo('test updating/saving new page fields - basic');
//            $this->todo('test updating/saving new page fields - with duplicate structures and differing values (do it)');
//            $this->todo('test updating/saving new page fields - with duplicate structures and same values (skip it)');
//            $this->todo('add deletion controls for page fields');
//            $this->todo('test delete fields on save/update');
//            $this->todo('front end implementation of deletion controls (grey out w/ "delete pending" note, have to click update to do actual delete)');

// NOTE: cannot test these as they require javascript - instead test the supporting actions via tests of rendering and tests of AJAX support code/pages
//            $this->todo('test adding specimens');
//            $this->todo('test deleting specimens');
//            $this->todo('test adding specimen images');
//            $this->todo('test deleting specimen images');
    }

}