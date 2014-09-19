<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class NotebookEditTest extends WMSWebTestCase {

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
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=view&notebook_id='.$notebook_id);
    }

    function goToNotebookEdit($notebook_id) {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=edit&notebook_id='.$notebook_id);
    }

    function checkBasicAsserts() {
        $this->assertNoText('IMPLEMENTED');
        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/fatal error/i');
    }

    function showContent() {
        echo "<pre>\n";
        echo htmlentities($this->getBrowser()->getContent());
        echo "\n</pre>";
    }
    //-----------------------------------------------------------------------------------------------------------------

    function testMissingNotebookIdShowsNotebookListInstead() {
//        $this->todo();
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=edit');

//        $this->showContent();

        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_notebook_specified'));
        $this->assertEltByIdHasAttrOfValue('list-of-user-notebooks','id','list-of-user-notebooks');
//        $this->todo();
//        exit;
    }

    function testNonexistentNotebookShowsNotebookListInstead() {
//        $this->todo();
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=edit&notebook_id=999');

        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_notebook_found'));
        $this->assertEltByIdHasAttrOfValue('list-of-user-notebooks','id','list-of-user-notebooks');
//        $this->todo();
    }

    function testNoEditPermDefaultsToView() {
//        $this->todo();
        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=edit&notebook_id=1004');

        $this->checkBasicAsserts();
        $this->assertEqual(LANG_APP_NAME . ': ' . ucfirst(util_lang('notebook')) ,$this->getBrowser()->getTitle());
        $this->assertText(util_lang('no_permission'));
        $this->assertEltByIdHasAttrOfValue('rendered_notebook_1004','class','rendered_notebook');
    }

    function testEditAccessControl_public() {
        $this->todo('basic public access check - no access defaults to view');
    }

    function testEditAccessControl_logged_in() {
        $this->todo('basic access check as owner - can edit owned notebook');
        $this->todo('publish option, no verify option');
        $this->todo('basic access check as owner - no edit notebook owned by another');
    }

    function testEditAccessControl_admin() {
        $this->todo('basic access check - can edit non-owned notebook');
        $this->todo('publish option, verify option');
    }

    function testInitialFormValuesFromExistingObject() {
        $this->todo();
    }
//
//    function testFormFieldLookups() {
//        $this->todo();
//    }

    function testRelatedDataListing() {
        $this->todo('owner name has link to user page');
        $this->todo('notebook pages are listed and linked');
    }

    function testToDo() {
//        $this->todo('test fall backs and default behaviors');
//        $this->todo('test access control to edit page');
// NOTE: nothing here for notebooks       $this->todo('test look-up data form fields (not much for this, but gets messy once we get to pages)');
//        $this->todo('test data pre-population');
//        $this->todo('test existence of dynamic elements for in-place related data');
//        $this->todo('  ----------  build in-place editing fragments for related data, and associated tests (not much for this, but gets messy once we get to pages)');
        $this->todo('test updating base data');
        $this->todo('test updating related data via ajax');
    }

}