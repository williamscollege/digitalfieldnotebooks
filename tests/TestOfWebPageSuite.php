<?php
	require_once('simpletest/autorun.php');
	require_once('simpletest/WMS_web_tester.php');
	SimpleTest::prefer(new TextReporter());

    require_once('../institution.cfg.php');
    require_once('../lang.cfg.php');

	class TestOfWebSuite extends TestSuite {
		function TestOfWebSuite() {
			$this->TestSuite('Web page tests');

            $this->addFile('web_page_tests/PublicPagesAccessTest.php');

            # Tests: Index page
			$this->addFile('web_page_tests/IndexPagePublicTest.php');
			$this->addFile('web_page_tests/IndexPageAuthTest.php');
			$this->addFile('web_page_tests/IndexPageLoggedInTest.php');

            $this->addFile('web_page_tests/NotebookViewTest.php');
            $this->addFile('web_page_tests/NotebookEditAndCreateTest.php');

            $this->addFile('web_page_tests/NotebookPageViewTest.php');

            $this->addFile('web_page_tests/MetadataViewTest.php');
            $this->addFile('web_page_tests/MetadataTermSetViewTest.php');

            $this->addFile('web_page_tests/AuthoritativePlantViewTest.php');

            # Sound Effect
			$this->addFile('soundForTesting.php');
		}
	}

?>