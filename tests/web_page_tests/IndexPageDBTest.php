<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class IndexPageDBTest extends WMSWebTestCase {

	function setUp() {
		removeTestData_Users($this->DB);
	}

	function tearDown() {
		removeTestData_Users($this->DB);
	}

//    function testInstGroupMembershipCreatedOnLogIn() {
//    }

}