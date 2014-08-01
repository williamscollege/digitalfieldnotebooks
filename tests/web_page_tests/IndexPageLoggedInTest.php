<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class IndexPageLoggedInTest extends WMSWebTestCase {

    function setUp() {
        createAllTestData($this->DB);
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


    function testIndexBasic() {
        $this->doLoginBasic();

        $this->assertNoPattern('/UNKNOWN LANGUAGE LABEL/i');

        // page heading text
        $this->assertText(ucfirst(util_lang('you_possesive')).' '.ucfirst(util_lang('notebooks')));

        // number of notebooks shown
        $this->assertEltByIdHasAttrOfValue('listOfUserNotebooks','data-notebook-count','3');

        $this->assertEltByIdHasAttrOfValue('notebook_item_1','data-notebook_id','1001');
        $this->assertEltByIdHasAttrOfValue('notebook_item_2','data-notebook_id','1002');
        $this->assertEltByIdHasAttrOfValue('notebook_item_3','data-notebook_id','1004');

        $this->assertEltByIdHasAttrOfValue('notebook_item_1','class','owned-object');
        $this->assertEltByIdHasAttrOfValue('notebook_item_2','class','owned-object');

        $this->assertLink('testnotebook1');
        $this->assertLink('testnotebook2');
        $this->assertLink('testnotebook4');

        // 'add notebook' control
        $this->assertEltByIdHasAttrOfValue('btn-add-notebook','value',util_lang('add_notebook'));

        // link to main/front page
        $this->assertEltByIdHasAttrOfValue('home-link','href',APP_FOLDER);

//        $this->assertFalse($this->setField('password','bar')); //$value
//        $this->assertPattern('/Signed in: \<a[^\>]*\>'.TESTINGUSER.'\<\/a\>/');
//        $this->assertNoPattern('/Sign in failed/i');
//        $this->assertEltByIdHasAttrOfValue('submit_signout','value',new PatternExpectation('/Sign\s?out/i'));

    }

    function testIndexAdmin() {
        $this->doLoginAdmin();

//        $this->assertFalse($this->setField('password','bar')); //$value
//        $this->assertPattern('/Signed in: \<a[^\>]*\>'.TESTINGUSER.'\<\/a\>/');
//        $this->assertNoPattern('/Sign in failed/i');
//        $this->assertEltByIdHasAttrOfValue('submit_signout','value',new PatternExpectation('/Sign\s?out/i'));

        $this->fail('TODO: implement this test');
    }

}