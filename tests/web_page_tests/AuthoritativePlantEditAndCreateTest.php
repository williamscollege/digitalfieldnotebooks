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
        $this->doLoginAdmin();
        $this->get('http://localhost/digitalfieldnotebooks/app_code/authoritative_plant.php?action=edit&authoritative_plant_id=5001');
        $this->checkBasicAsserts();

        ///////////////////////////////
        //// set form fields

//////      NOTE: the identifier to use for setField is the value of the name attribute of the field
        $this->assertTrue($this->setField('authoritative_plant-class_5001',''));
        $this->assertTrue($this->setField('authoritative_plant-order_5001','neworder'));
        $this->assertTrue($this->setField('authoritative_plant-species_5001','newspecies'));

//        // alter common name
        $this->assertTrue($this->setField('authoritative_plant_extra_5103-value','altered common name'));

//        // alter description
        $this->assertTrue($this->setField('authoritative_plant_extra_5104-value','altered description'));

//        // alter specimen
        $this->assertTrue($this->setField('specimen-name_8001','altered specimen name'));

        // JS-driven - can't test here
//        $this->todo('add new APE');
//        $this->todo('delete existing APE');
//
//        $this->todo('add new specimen');
//        $this->todo('delete specimen');

//        $this->showContent();
//        exit;

////        NOTE: the identifier to use for buttons is the value of the value attribute of the button
        ///////////////////////////////
        //// submit the form
        $this->click('<i class="icon-ok-sign icon-white"></i> '.util_lang('update','properize'));

//        $this->showContent();

//        exit;
////
        ///////////////////////////////
        //// check the resulting page
        $this->checkBasicAsserts();

        $this->assertNoText('class :');
        $this->assertText('neworder');
        $this->assertText('newspecies');

        $this->assertText('altered common name');
        $this->assertText('altered description');

        $this->assertText('altered specimen name');

        // JS-driven - can't test here
//        $this->todo('web - check add new APE');
//        $this->todo('web - check delete existing APE');
//        $this->todo('web - add new specimen');
//        $this->todo('web - delete specimen');

        ///////////////////////////////
        //// check the db records

        $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);

        $this->assertEqual($ap->class,'');
        $this->assertEqual($ap->order,'neworder');
        $this->assertEqual($ap->genus,'AP_A_genus');
        $this->assertEqual($ap->species,'newspecies');

        $apeCN = Authoritative_Plant_Extra::getOneFromDb(['authoritative_plant_extra_id'=>5103],$this->DB);
        $apeDe = Authoritative_Plant_Extra::getOneFromDb(['authoritative_plant_extra_id'=>5104],$this->DB);
        $spec = Specimen::getOneFromDb(['specimen_id'=>8001],$this->DB);

        $this->assertEqual('altered common name',$apeCN->value);
        $this->assertEqual('altered description',$apeDe->value);
        $this->assertEqual('altered specimen name',$spec->name);

        // JS-driven - can't test here
//        $this->todo('db - check add new APE');
//        $this->todo('db - check delete existing APE');
//        $this->todo('db - add new specimen');
//        $this->todo('db - delete specimen');
    }

    function testCreateButton() {
//        $n = Notebook::getOneFromDb(['notebook_id' => 1001], $this->DB);

        $this->doLoginAdmin();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/authoritative_plant.php?action=create');

        $this->checkBasicAsserts();
        $this->assertTitle(LANG_APP_NAME . ': ' . util_lang('authoritative_plant','properize'));
        $this->assertEltByIdHasAttrOfValue('rendered_authoritative_plant_NEW','id','rendered_authoritative_plant_NEW');
//        $this->showContent();
    }

    function testCreationOfNewPlant() {
        $this->doLoginAdmin();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/authoritative_plant.php?action=create');
        $this->checkBasicAsserts();

        $this->assertTrue($this->setField('authoritative_plant-genus_NEW','uniquenewgenus'));
        $this->assertTrue($this->setField('authoritative_plant-species_NEW','uniquenewspecies'));
        $this->assertTrue($this->setField('flag_active',true));

        ///////////////////////////////
        //// submit the form
        $this->click('<i class="icon-ok-sign icon-white"></i> '.util_lang('update','properize'));

        $new_ap = Authoritative_Plant::getOneFromDb(['species'=>'uniquenewspecies'],$this->DB);

        $this->assertTrue($new_ap->matchesDb);
        $this->assertTrue($new_ap->flag_active);
    }
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
//            $this->todo('test create/add button action and form for creation of new authoritative plant');
//            $this->todo('test updating related data, perhaps via ajax but probably mainly as a part of the base update call'); // NOTE: ajax used sporadically throughout the site - need to make the consistent at some point
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

//            $this->todo('test adding specimens');
//            $this->todo('test deleting specimens');
//            $this->todo('test adding specimen images');
//            $this->todo('test deleting specimen images');
    }

}