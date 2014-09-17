<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class AuthoritativePlantViewTest extends WMSWebTestCase {

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

    //-----------------------------------------------------------------------------------------------------------------

    function testListOfAll() {
        $ap1 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);
        $ap2 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5002],$this->DB);
        $ap3 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5003],$this->DB);
        $ap4 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5004],$this->DB);
        $ap5 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5005],$this->DB);
        $ap6 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5006],$this->DB);
        $ap7 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5007],$this->DB);
        $ap8 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5008],$this->DB);

        $this->get('http://localhost/digitalfieldnotebooks/app_code/authoritative_plant.php?action=list');

        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');
        $this->assertNoText('IMPLEMENTED');

//        $this->todo();
        $this->assertPattern('/'.util_lang('authoritative_plants').'/i');
//        $this->assertText(util_lang('authoritative_plants'));

        $this->assertLink($ap1->renderAsShortText());
        $this->assertLink($ap2->renderAsShortText());
        $this->assertLink($ap3->renderAsShortText());
        $this->assertLink($ap4->renderAsShortText());
        $this->assertLink($ap5->renderAsShortText());
        $this->assertLink($ap6->renderAsShortText());
        $this->assertLink($ap7->renderAsShortText());
        $this->assertLink($ap8->renderAsShortText());
    }

    function testViewIsDefaultActionForSpecific() {
        $this->doLoginBasic();

        $this->goToView(5001);

        $view_content = $this->getBrowser()->getContent();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/authoritative_plant.php?authoritative_plant_id=5001');

        $no_action_given_content = $this->getBrowser()->getContent();

        $this->assertEqual($view_content,$no_action_given_content);
    }

    function testMissingIdRedirectsToFullList() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/authoritative_plant.php?action=view');

        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('authoritative_plant')) ,$this->getBrowser()->getTitle());
        $this->assertPattern('/'.util_lang('authoritative_plants').'/i');
    }

    function testNonexistentRedirectsToFullList() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/authoritative_plant.php?authoritative_plant_id=999');

        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('authoritative_plant')) ,$this->getBrowser()->getTitle());
        $this->assertPattern('/'.util_lang('authoritative_plants').'/i');
    }

    function testActionNotAllowedRedirectsToFullList() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/authoritative_plant.php?action=edit&authoritative_plant_id=5001');

        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('authoritative_plant')) ,$this->getBrowser()->getTitle());
        $this->assertPattern('/'.util_lang('authoritative_plants').'/i');
    }

    function testViewNotEditable() {
//        $this->doLoginBasic();
        $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);

        $this->goToView($ap->authoritative_plant_id);

//        echo htmlentities($this->getBrowser()->getContent());

        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');
        $this->assertNoText('IMPLEMENTED');


        // page heading
        $this->assertLink(ucfirst(util_lang('authoritative_plant')));

        // NO 'edit' control
        $this->assertNoLink(util_lang('edit'));

        // MORE!!!!
        $this->todo("check for auth plant info");

        $this->todo("check for extras info");
        $this->todo("check for notebook pages");
    }
}