<?php
	require_once('simpletest/autorun.php');
	require_once('simpletest/WMS_web_tester.php');
	SimpleTest::prefer(new TextReporter());

	require_once('../institution.cfg.php');
	require_once('../lang.cfg.php');
    require_once('../util.php');

	class TestOfWebSuite extends TestSuite {
		function TestOfWebSuite() {
			$this->TestSuite('Web page tests');

			# Tests: Index page
			$this->addFile('web_page_tests/IndexPageLoadTest.php');
			$this->addFile('web_page_tests/IndexPageAuthTest.php');
			$this->addFile('web_page_tests/IndexPageLoggedInTest.php');

            # Sound Effect
			$this->addFile('soundForTesting.php');
		}
	}

?>