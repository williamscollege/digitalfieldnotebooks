<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class AuthoritativePlantEditAndCreateTest extends WMSWebTestCase {

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
        $this->get('http://localhost/digitalfieldnotebooks/app_code/authoritative_plant.php?action=view&authoritative_plant_id='.$id);
    }

    function goToEdit($id) {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/authoritative_plant.php?action=edit&authoritative_plant_id='.$id);
    }

    function checkBasicAsserts() {
        $this->assertNoText('IMPLEMENTED');
        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/fatal error/i');
        $this->assertNoPattern('/UNKNOWN LANGUAGE LABEL/i');
    }

    //-----------------------------------------------------------------------------------------------------------------


    function testNoIdOnEdit() {
        $this->doLoginAdmin();

        // missing id -> list (w/ message)
        $this->get('http://localhost/digitalfieldnotebooks/app_code/authoritative_plant.php?action=edit');
        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('authoritative_plant','properize')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_authoritative_plant_specified'));
        $this->assertEltByIdHasAttrOfValue('list-of-authoritative-plants','id','list-of-authoritative-plants');

//        $this->showContent();
//        exit;
    }

    function testBadIdOnEdit() {
        $this->doLoginAdmin();

        // non-existent id -> list (w/ message)
        $this->get('http://localhost/digitalfieldnotebooks/app_code/authoritative_plant.php?action=edit&authoritative_plant_id=999');
        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('authoritative_plant','properize')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_authoritative_plant_found'));
        $this->assertEltByIdHasAttrOfValue('list-of-authoritative-plants','id','list-of-authoritative-plants');
    }

    function testNoPermissionToEdit() {
        $this->doLoginBasic();

        // no edit perms -> view (w/ message)
        $this->get('http://localhost/digitalfieldnotebooks/app_code/authoritative_plant.php?action=edit&authoritative_plant_id=5001');
        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('authoritative_plant','properize')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_permission'));
        $this->assertEltByIdHasAttrOfValue('authoritative_plant_view_5001','data-authoritative_plant_id','5001');
    }

    function testBaseDataUpdate() {
        $this->todo();

//        $this->doLoginBasic();
//        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=edit&notebook_page_id=1101');
//        $this->checkBasicAsserts();
//
//        $new_notes = 'new notes for the page';
//        $new_specimen_notes = 'new notes for the specimen';
//
//////      NOTE: the identifier to use for setField is the value of the name attribute of the field
//        $this->setField('notes',$new_notes);
//
//        // page field alteration
//        $this->assertTrue($this->setField('page_field_select_1201','6204'));
//        $this->assertTrue($this->setField('page_field_open_value_1204','new open value'));
//
//        // page field addition
////        $this->todo('figure out how to do page field addition');
////        $this->todo('figure out how to do page deletion');
//
//        // specimen alteration
//        $this->assertTrue($this->setField('specimen-notes_8002',$new_specimen_notes));
//
////        $this->todo('figure out how to do specimen addition');
////        $this->todo('figure out how to do specimen deletion');
//
////        $this->showContent();
//
//////        NOTE: the identifier to use for buttons is the value of the value attribute of the button
//        $this->click('<i class="icon-ok-sign icon-white"></i> '.util_lang('update','properize'));
////
////        $this->showContent();
////
//        $this->checkBasicAsserts();
//        $this->assertText($new_notes);
////
//        $np = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101],$this->DB);
//        $this->assertEqual($np->notes,$new_notes);
//
////        $this->todo('check page field alteration on 1201');
//        $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1201],$this->DB);
//        $this->assertEqual($npf->value_metadata_term_value_id,6204);
//        $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1204],$this->DB);
//        $this->assertEqual($npf->value_open,'new open value');
//
////        $this->todo('check page field addition');
////        $this->todo('check page field deletion');
//
////        $this->todo('check specimen alteration on 8002');
//        $s = Specimen::getOneFromDb(['specimen_id'=>8002],$this->DB);
//        $this->assertEqual($s->notes,$new_specimen_notes);
//
////        $this->todo('check specimen addition');
////        $this->todo('check specimen deletion');
////        util_prePrintR(htmlentities($this->getBrowser()->getContent()));
//
//        echo "<br><b>NOTE: skipping create and delete tests for pagefields and specimens because that requires javascript interaction</b><br/>\n";
//
    }

//    function testCreateButton() {
//        $n = Notebook::getOneFromDb(['notebook_id' => 1001], $this->DB);
//
//        $this->doLoginBasic();
//        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=edit&notebook_id=1001');
//        $this->checkBasicAsserts();
//
//
//        $this->click(util_lang('add_notebook_page'));
//
//        $this->checkBasicAsserts();
//        $this->assertTitle(LANG_APP_NAME . ': ' . ucfirst(util_lang('page')));
//        $this->assertLink(htmlentities($n->name));
//        $this->assertEltByIdHasAttrOfValue('rendered_notebook_page_NEW','id','rendered_notebook_page_NEW');
//
////        $this->showContent();
//    }
//
//
//    function testNewNotebookPage() {
//        $n = Notebook::getOneFromDb(['notebook_id' => 1001], $this->DB);
//
//        $this->doLoginBasic();
//        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=edit&notebook_id=1001');
//        $this->checkBasicAsserts();
//
//        $this->click(util_lang('add_notebook_page'));
//
//        $this->checkBasicAsserts();
//
//        $this->assertEltByIdHasAttrOfValue('form-edit-notebook-page-base-data','action',APP_ROOT_PATH.'/app_code/notebook_page.php');
//
////        $this->showContent();
//    }
//
//    function testDeleteNotebookPage() {
//        $np = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101],$this->DB);
//        $this->assertTrue($np->matchesDb);
//
//        $this->doLoginBasic();
//        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=delete&notebook_page_id=1101');
//        $this->checkBasicAsserts();
//
//        $np2 = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101],$this->DB);
//        $this->assertFalse($np2->matchesDb);
//    }

        function testToDo() {
//            $this->todo('test fall backs and default behaviors');
//            $this->todo('test access control to edit page');
    //        $this->todo('test data pre-population');
//            $this->todo('test existence of dynamic elements for in-place related data');
    //        $this->todo('  ----------  build in-place editing fragments for related data, and associated tests (not much for this, but gets messy once we get to pages)');
//            $this->todo('test updating base data');
            $this->todo('test create/add button action and form for creation of new authoritative plant');
            $this->todo('test updating related data, perhaps via ajax but probably mainly as a part of the base update call'); // NOTE: ajax used sporadically throughout the site - need to make the consistent at some point
//            $this->todo('test updating/saving new page fields - basic');
//            $this->todo('test updating/saving new page fields - with duplicate structures and differing values (do it)');
//            $this->todo('test updating/saving new page fields - with duplicate structures and same values (skip it)');
//            $this->todo('add deletion controls for page fields');
//            $this->todo('test delete fields on save/update');
//            $this->todo('front end implementation of deletion controls (grey out w/ "delete pending" note, have to click update to do actual delete)');

// NOTE: cannot test these as they require javascript - instead test the supporting actions via tests of rendering and tests of AJAX support code/pages
//            $this->todo('test adding auth plant extras - common name');
//            $this->todo('test adding auth plant extras - image');
//            $this->todo('test adding auth plant extras - text');

            $this->todo('test adding specimens');
            $this->todo('test deleting specimens');
            $this->todo('test adding specimen images');
            $this->todo('test deleting specimen images');
    }

}