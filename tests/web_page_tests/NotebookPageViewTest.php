<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class NotebookPageViewTest extends WMSWebTestCase {

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

    function goToNotebookPageView($notebook_page_id) {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=view&notebook_page_id='.$notebook_page_id);
    }

    //-----------------------------------------------------------------------------------------------------------------

    function testMissingIdGoesToNotebookList() {
        $this->doLoginBasic();
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?action=view');
        $this->checkBasicAsserts();

        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_notebook_page_specified'));
    }

    function testViewIsDefault() {
        $this->doLoginBasic();

        $this->goToNotebookPageView(1101);
        $this->checkBasicAsserts();

        $view_content = $this->getBrowser()->getContent();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?notebook_page_id=1101');
        $this->checkBasicAsserts();

        $no_action_given_content = $this->getBrowser()->getContent();

        $this->assertEqual($view_content,$no_action_given_content);
    }

    function testViewEditable() {
        $this->doLoginBasic();

        $this->goToNotebookPageView(1101);
        $this->checkBasicAsserts();

        // NOTE: most of the messy details of this are checked in the notebook_page object tests of renderAsView - see app_infrastructure_tests/TestOfNotebookPage.class.php
        // this just makes sure that the plant lable is actually showing up and that the appropriate edit buttons are there

        $ap1 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);

        // page heading text
        $this->assertText($ap1->renderAsShortText());

        // 'edit' control
        $this->assertEltByIdHasAttrOfValue('btn-edit','href',APP_ROOT_PATH.'/app_code/notebook_page.php?action=edit&notebook_page_id=1101');
        $this->assertLink(util_lang('edit'));

        // no 'add field' control - that's only in edit mode, not view mode
        $this->assertNoLink(util_lang('add_notebook_page_field'));

        // no 'add specimen' control - that's only in edit mode, not view mode
        $this->assertNoLink(util_lang('add_specimen'));

        $this->assertNoText('IMPLEMENTED');
    }

    function testViewNotEditable() {
        $this->doLoginBasic();

        $this->goToNotebookPageView(1104);
        $this->checkBasicAsserts();

        // NOTE: most of the messy details of this are checked in the notebook_page object tests of renderAsView - see app_infrastructure_tests/TestOfNotebookPage.class.php
        // this just makes sure that the plant lable is actually showing up and that the appropriate edit buttons are there

        $ap1 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);

        // page heading text
        $this->assertText($ap1->renderAsShortText());

        // NO 'edit' control
        $this->assertNoLink(util_lang('edit'));

        // NO 'add field' control
        $this->assertNoLink(util_lang('add_notebook_page_field'));

        // no 'add specimen' control
        $this->assertNoLink(util_lang('add_specimen'));

        $this->assertNoText('IMPLEMENTED');
    }
}