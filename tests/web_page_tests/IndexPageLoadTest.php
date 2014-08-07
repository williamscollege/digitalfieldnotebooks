<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class IndexPageLoadTest extends WMSWebTestCase {

    function setUp() {
        createAllTestData($this->DB);
        global $CUR_LANG_SET;
        $CUR_LANG_SET = 'en';
    }

    function tearDown() {
        removeAllTestData($this->DB);
    }

    function testIndexPageLoad() {
        $this->get('http://localhost/digitalfieldnotebooks/');
        $this->assertResponse(200);
    }

    function testIndexPageLoadsErrorAndWarningFree() {
        $this->get('http://localhost/digitalfieldnotebooks/');
        $this->assertNoPattern('/error/i');
        $this->assertNoPattern('/warning/i');
    }

    function testIndexPageLoadsCorrectText() {
        $this->get('http://localhost/digitalfieldnotebooks/');

        $this->assertTitle(new PatternExpectation('/'.LANG_APP_NAME.': /'));

        $this->assertNoPattern('/'.util_lang('app_signed_in_status').': \<a[^\>]*\>'.TESTINGUSER.'\<\/a\>/');
        $this->assertPattern('/'.util_lang('app_sign_in_action').'/');

        $this->assertPattern('/'.util_lang('app_short_description').'/');
        $this->assertPattern('/'.util_lang('app_sign_in_msg').'/');

        // check for published, verfied notebooks that are publically viewable
        $this->assertText(ucfirst(util_lang('public')).' '.ucfirst(util_lang('notebooks')));
        $this->assertEltByIdHasAttrOfValue('listOfUserNotebooks','data-notebook-count','1');
        $this->assertEltByIdHasAttrOfValue('notebook_item_1','data-notebook_id','1004');
        $this->assertLink('testnotebook4');

    }

}