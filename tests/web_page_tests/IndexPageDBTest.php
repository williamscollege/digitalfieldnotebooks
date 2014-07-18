<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class IndexPageDBTest extends WMSWebTestCase {

	function setUp() {
		removeTestData_Users($this->DB);
	}

	function tearDown() {
		removeTestData_Users($this->DB);
	}

    function testInstGroupMembershipCreatedOnLogIn() {
        $this->get('http://localhost/digitalfieldnotebooks/');
        $this->assertCookie('PHPSESSID');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);


        $this->click('Sign in');

//		$this->dump($this->getBrowser()->getContent());

		$this->assertPattern('/Signed in: \<a[^\>]*\>'.TESTINGUSER.'\<\/a\>/');

		$u = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);
		$this->assertTrue($u->matchesDb);

		//exit;
    }

}