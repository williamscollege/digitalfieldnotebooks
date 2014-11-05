<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class MetadataTermSetViewTest extends WMSWebTestCase {

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
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_term_set.php?action=view&metadata_term_set_id='.$id);
    }

    //-----------------------------------------------------------------------------------------------------------------

    function testListOfAll() {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_term_set.php?action=list');

        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');
        $this->assertNoText('IMPLEMENTED');

        $this->assertText(util_lang('all_metadata_term_sets'));

        $this->assertLink('small lengths');
        $this->assertLink('colors');
        $this->assertLink('margin styles');
        $this->assertLink('habitats');
    }

    function testViewIsDefaultActionForSpecific() {
        $this->doLoginBasic();

        $this->goToView(6101);

        $this->checkBasicAsserts();

        $view_content = $this->getBrowser()->getContent();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_term_set.php?metadata_term_set_id=6101');
        $this->checkBasicAsserts();

        $no_action_given_content = $this->getBrowser()->getContent();

        $this->assertEqual($view_content,$no_action_given_content);
    }

    function testMissingIdRedirectsToFullList() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_term_set.php?action=view');
        $this->checkBasicAsserts();

        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('metadata_term_set')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('all_metadata_term_sets'));
    }

    function testNonexistentRedirectsToFullList() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_term_set.php?metadata_term_set_id=999');
        $this->checkBasicAsserts();

        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('metadata_term_set')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('all_metadata_term_sets'));
    }

    function testActionNotAllowedRedirectsToFullList() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_term_set.php?action=edit&metadata_term_set_id=6004');
        $this->checkBasicAsserts();

        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('metadata_term_set')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('all_metadata_term_sets'));
    }

    function testViewNotEditable() {
        $this->goToView(6101);

        $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);

        $this->checkBasicAsserts();

        // page heading
        $this->assertLink(util_lang('all_metadata_term_sets'));

        // NO 'edit' control
        $this->assertNoLink(util_lang('edit'));

        // MORE!!!!
        $this->assertLink(htmlentities($mds->name));

        $this->assertText('small lengths');
        $this->assertText('3 mm - 1cm');
    }
}