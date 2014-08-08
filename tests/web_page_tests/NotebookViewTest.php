<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class NotebookViewTest extends WMSWebTestCase {

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

    function goToNotebookView($notebook_id) {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?notebook_id='.$notebook_id);
    }

    function testViewEditable() {
        $this->doLoginBasic();

        $this->goToNotebookView(1001);

        echo htmlentities($this->getBrowser()->getContent());

        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');

        $n = Notebook::getOneFromDb(['notebook_id'=>1001],$this->DB);
        $ap1 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);
        $ap2 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5008],$this->DB);

        // page heading text
        $this->assertText(ucfirst(util_lang('notebook')));

        $this->assertText($n->name);
        $this->assertText($n->description);

        $this->assertEltByIdHasAttrOfValue('workflow-status','data-is-published','0');
        $this->assertEltByIdHasAttrOfValue('workflow-status','data-is-verified','0');

        // 'edit' control
        $this->assertEltByIdHasAttrOfValue('btn-edit','value',util_lang('edit'));

        // number of notebook pages
        $this->assertEltByIdHasAttrOfValue('list-of-notebook-pages','data-notebook-page-count','2');
        $this->assertEltByIdHasAttrOfValue('notebook-page-item-1','data-notebook_page_id','1101');
        $this->assertEltByIdHasAttrOfValue('notebook-page-item-2','data-notebook_page_id','1102');

        $this->assertLink($ap1->renderAsShortText());
        $this->assertLink($ap2->renderAsShortText());

        // 'add page' control
        $this->assertEltByIdHasAttrOfValue('btn-add-notebook-page','value',util_lang('add_notebook_page'));
    }
}