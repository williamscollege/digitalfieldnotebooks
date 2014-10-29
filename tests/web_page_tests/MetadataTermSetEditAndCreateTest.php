<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class MetadataTermSetEditAndCreateTest extends WMSWebTestCase {

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

//    function goToNotebookView($notebook_id) {
//        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=view&notebook_id='.$notebook_id);
//    }
//
//    function goToNotebookEdit($notebook_id) {
//        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=edit&notebook_id='.$notebook_id);
//    }

    function checkBasicAsserts() {
        $this->assertNoText('IMPLEMENTED');
        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/fatal error/i');
    }

    //-----------------------------------------------------------------------------------------------------------------

    function testEditButtonExists() {
        $this->todo();
    }

    function testCreateButtonExists() {
        $this->todo();
    }

    function testBaseDataUpdate() {
        $this->todo();
//
//        $this->doLoginBasic();
//        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=edit&notebook_id=1001');
//
////      NOTE: the identifier to use for setField is the value of the name attribute of the field
//        $this->setField('name','new name for testnotebook1');
////        NOTE: the identifier to use for form buttons is the value of the value attribute of the button, or the interior html of a button element
//        $this->click('<i class="icon-ok-sign icon-white"></i> '.util_lang('update','properize'));
//
//
//        $this->checkBasicAsserts();
//        $this->assertText('new name for testnotebook1');
//
//        $n = Notebook::getOneFromDb(['notebook_id'=>1001],$this->DB);
//        $this->assertEqual($n->name,'new name for testnotebook1');
//
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

    function testDeleteNotebook() {
//        $this->doLoginBasic();
//        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=edit&notebook_id=1001');

        $this->todo();
    }
    function testToDo() {
//        $this->todo('');
    }

}