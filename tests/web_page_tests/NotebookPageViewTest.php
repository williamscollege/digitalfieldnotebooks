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

    function testViewIsDefault() {
        $this->doLoginBasic();

        $this->goToNotebookPageView(1101);

        $view_content = $this->getBrowser()->getContent();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook_page.php?notebook_page_id=1101');

        $no_action_given_content = $this->getBrowser()->getContent();

        $this->assertEqual($view_content,$no_action_given_content);
    }

    function testViewEditable() {
        $this->doLoginBasic();

        $this->goToNotebookView(1101);

//        echo htmlentities($this->getBrowser()->getContent());

        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');

        $n = Notebook::getOneFromDb(['notebook_page_id'=>1101],$this->DB);

//        util_prePrintR($n);

        $ap1 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);

        // page heading text
        $this->assertText(ucfirst(util_lang('notebook_page')));

        $this->assertText($ap1->renderAsShortText());
        $this->assertText($n->notes);

        // 'edit' control
        $this->assertEltByIdHasAttrOfValue('btn-edit','href',APP_ROOT_PATH.'/app_code/notebook_page.php?action=edit&notebook_page=1101');
        $this->assertLink(util_lang('edit'));

        // data fields for the page
        $this->todo();

        // 'add field' control
        $this->assertEltByIdHasAttrOfValue('btn-add-notebook-page-field','id','btn-add-notebook-page-field');
        $this->assertLink(util_lang('add_notebook_page_field'));
    }

    function ASIDE_testViewNotEditable() {
        $this->doLoginBasic();

        $this->goToNotebookView(1004);

//        echo htmlentities($this->getBrowser()->getContent());

        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');

        $n = Notebook::getOneFromDb(['notebook_id'=>1004],$this->DB);

//        util_prePrintR($n);

        $ap1 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);

        // page heading text
        $this->assertText(ucfirst(util_lang('notebook')));

        $this->assertText($n->name);
        $this->assertText($n->notes);

        // NO 'edit' control
        $this->assertNoLink(util_lang('edit'));

        // number of notebook pages
        $this->assertEltByIdHasAttrOfValue('list-of-notebook-pages','data-notebook-page-count','1');
        $this->assertEltByIdHasAttrOfValue('notebook-page-item-1','data-notebook_page_id','1104');

        $this->assertLink($ap1->renderAsShortText());

        // 'add page' control
        $this->assertNoLink(util_lang('add_notebook_page'));
    }
}