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

        $this->assertEltByIdHasAttrOfValue('edit-submit-control','value',util_lang('update','properize'));

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

        $this->assertEltByIdHasAttrOfValue('edit-submit-control','value',util_lang('update','properize'));
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

//        $this->todo('check specimens');
        $this->assertEltByIdHasAttrOfValue('form-edit-specimen-8002','class','form-edit-specimen');
        $this->assertEltByIdHasAttrOfValue('specimen-image-8103','data-specimen_image_id','8103');
        $this->assertEltByIdHasAttrOfValue('specimen-image-8104','data-specimen_image_id','8104');
        $this->assertEltByIdHasAttrOfValue('form-edit-specimen-8003','class','form-edit-specimen');

    }

    function testBaseDataUpdate() {
        $this->doLoginBasic();
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit&notebook_page_id=1101');
//
        $new_notes = 'new notes for the page';

////      NOTE: the identifier to use for setField is the value of the name attribute of the field
        $this->setField('notes',$new_notes);

////        NOTE: the identifier to use for buttons is the value of the value attribute of the button
        $this->click(util_lang('update','properize'));
//
//
        $this->checkBasicAsserts();
        $this->assertText($new_notes);
//
        $np = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101],$this->DB);
        $this->assertEqual($np->notes,$new_notes);

//        util_prePrintR(htmlentities($this->getBrowser()->getContent()));
    }

    function testCreateButton() {
        $this->todo();
//        $this->doLoginBasic();
//        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=list');
//
//        $this->click(util_lang('add_notebook'));
//
//        $this->checkBasicAsserts();
//        $this->assertPattern('/'.util_lang('new_notebook_title').'/');

//        $this->showContent();
    }

    function testToDo() {
//        $this->todo('test fall backs and default behaviors');
//        $this->todo('test access control to edit page');
// NOTE: nothing here for notebooks       $this->todo('test look-up data form fields (not much for this, but gets messy once we get to pages)');
//        $this->todo('test data pre-population');
//        $this->todo('test existence of dynamic elements for in-place related data');
//        $this->todo('  ----------  build in-place editing fragments for related data, and associated tests (not much for this, but gets messy once we get to pages)');
//        $this->todo('test updating base data');
//        $this->todo('test updating related data via ajax');
    }

}