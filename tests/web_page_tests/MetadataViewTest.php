<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class MetadataViewTest extends WMSWebTestCase {

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
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=view&metadata_structure_id='.$id);
    }

    //-----------------------------------------------------------------------------------------------------------------

    function testListOfAll() {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=list');

        $this->checkBasicAsserts();

        $this->assertText(util_lang('all_metadata','properize'));

        $this->assertText('flower');
        $this->assertText('flower - flower size');
        $this->assertText('flower - flower primary color');
        $this->assertText('leaf');
    }

    function testViewIsDefaultActionForSpecific() {
        $this->doLoginBasic();

        $this->goToView(6001);
        $this->checkBasicAsserts();

        $view_content = $this->getBrowser()->getContent();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?metadata_structure_id=6001');
        $this->checkBasicAsserts();

        $no_action_given_content = $this->getBrowser()->getContent();

        $this->assertEqual($view_content,$no_action_given_content);
    }

    function testMissingIdRedirectsToFullList() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=view');
        $this->checkBasicAsserts();

        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('metadata')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('all_metadata','properize'));
    }

    function testNonexistentRedirectsToFullList() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?metadata_structure_id=999');
        $this->checkBasicAsserts();

        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('metadata')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('all_metadata','properize'));
    }

    function testActionNotAllowedRedirectsToFullList() {
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=edit&metadata_structure_id=6004');
        $this->checkBasicAsserts();

        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('metadata')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('all_metadata','properize'));
    }

    function testViewNotEditable_LEAF() {
//        $this->doLoginBasic();

        $this->goToView(6002);
        $this->checkBasicAsserts();

//        echo htmlentities($this->getBrowser()->getContent());

        $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);

//        util_prePrintR($n);

//        $this->showContent();

        // page heading
        $this->assertLink(util_lang('metadata'));
        $this->assertLink('flower');

        $this->assertText('flower size');

        $this->assertText($mds->description);
        $this->assertText($mds->details);
        $this->assertEltByIdHasAttrOfValue('rendered_metadata_reference_6302','class','embedded rendered_metadata_reference rendered_metadata_reference_text');

        // NO 'edit' control
        $this->assertNoLink(util_lang('edit'));

        // MORE!!!!
//        $this->todo('additional metadata view checks/asserts');
        // term set & values

        $this->assertText('small lengths');
        $this->assertText('3 mm - 1cm');
    }

    function testViewNotEditable_BRANCH() {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=view&metadata_structure_id=6001');
        $this->checkBasicAsserts();

//        $this->showContent();
//
        $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);

        $this->assertText($mds->description);

        $this->assertLink(util_lang('metadata'));
        $this->assertNoLink('flower');
        $this->assertText('flower - flower size');
        $this->assertText('flower - flower primary color');
        $this->assertNoLink('leaf');

        $this->assertEltByIdHasAttrOfValue('rendered_metadata_reference_6301','class','embedded rendered_metadata_reference rendered_metadata_reference_text');
    }


    function testViewNotEditable_BUD() {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=view&metadata_structure_id=6004');
        $this->checkBasicAsserts();

        $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6004],$this->DB);

        $this->assertText($mds->description);

        $this->assertLink(util_lang('metadata'));

        $this->assertNoLink('flower');
        $this->assertNoLink('flower - flower size');
        $this->assertNoLink('flower - flower primary color');
        $this->assertNoLink('leaf');

        $this->assertText(util_lang('metadata_no_term_set'));
        $this->assertText(util_lang('metadata_no_children'));
    }
}