<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class IndexPageLoadTest extends WMSWebTestCase {
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

    }

}