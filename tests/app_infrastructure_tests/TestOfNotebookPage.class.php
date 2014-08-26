<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfNotebookPage extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testNotebookPageAtributesExist() {
			$this->assertEqual(count(Notebook_Page::$fields), 9);

            $this->assertTrue(in_array('notebook_page_id', Notebook_Page::$fields));
            $this->assertTrue(in_array('created_at', Notebook_Page::$fields));
            $this->assertTrue(in_array('updated_at', Notebook_Page::$fields));
            $this->assertTrue(in_array('notebook_id', Notebook_Page::$fields));
            $this->assertTrue(in_array('authoritative_plant_id', Notebook_Page::$fields));
            $this->assertTrue(in_array('notes', Notebook_Page::$fields));
            $this->assertTrue(in_array('flag_workflow_published', Notebook_Page::$fields));
            $this->assertTrue(in_array('flag_workflow_validated', Notebook_Page::$fields));
            $this->assertTrue(in_array('flag_delete', Notebook_Page::$fields));
		}

		//// static methods

		function testCmp() {
            $n1 = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101],$this->DB);
            $n2 = Notebook_Page::getOneFromDb(['notebook_page_id'=>1102],$this->DB);

//            util_prePrintR($n1);
//            util_prePrintR($n2);

			$this->assertEqual(Notebook_Page::cmp($n1, $n2), -1);
			$this->assertEqual(Notebook_Page::cmp($n1, $n1), 0);
			$this->assertEqual(Notebook_Page::cmp($n2, $n1), 1);

            $nps = Notebook_Page::getAllFromDb([],$this->DB);

            usort($nps,'Notebook_Page::cmp');

            $this->assertEqual(1101,$nps[0]->notebook_page_id);
            $this->assertEqual(1102,$nps[1]->notebook_page_id);
            $this->assertEqual(1103,$nps[2]->notebook_page_id);
            $this->assertEqual(1104,$nps[3]->notebook_page_id);
//
//            $this->fail("TODO: implement this test");
        }

        //// instance methods - object itself

        function testRenderAsListItem_Editor() {
            $np = Notebook_Page::getOneFromDb(['notebook_page_id' => 1101], $this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $plant = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);

            $rendered = $np->renderAsListItem();
            $canonical = '<li data-notebook_page_id="1101" data-created_at="'.$np->created_at.'" data-updated_at="'.$np->updated_at.'" data-notebook_id="1001" data-authoritative_plant_id="5001" data-notes="testing notebook page the first in testnotebook1, owned by user 101" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0" data-can-edit="1"><a href="'.APP_FOLDER.'/app_code/notebook_page.php?action=view&notebook_page_id=1101">'.htmlentities($plant->renderAsShortText()).'</a></li>';
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsListItem_NonEditor() {
            $np = Notebook_Page::getOneFromDb(['notebook_page_id' => 1104], $this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $plant = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);

            $rendered = $np->renderAsListItem();
            $canonical = '<li data-notebook_page_id="1104" data-created_at="'.$np->created_at.'" data-updated_at="'.$np->updated_at.'" data-notebook_id="1004" data-authoritative_plant_id="5001" data-notes="first page of testnotebook4, owned by user 110" data-flag_workflow_published="0" data-flag_workflow_validated="0" data-flag_delete="0"><a href="'.APP_FOLDER.'/app_code/notebook_page.php?action=view&notebook_page_id=1104">'.htmlentities($plant->renderAsShortText()).'</a></li>';
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
        }

        //// instance methods - related data

        function testGetNotebook() {
            $np1 = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101], $this->DB);

            $n = $np1->getNotebook();

            $this->assertEqual(1001,$n->notebook_id);
        }

        function testGetAuthoritativePlant() {
            $np1 = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101], $this->DB);

            $p = $np1->getAuthoritativePlant();

            $this->assertEqual(5001,$p->authoritative_plant_id);
        }

        function testLoadPageFields() {
            $np1 = Notebook_Page::getOneFromDb(['notebook_page_id'=>1101], $this->DB);

            $np1->loadPageFields();

            $this->assertEqual(4,count($np1->page_fields));

            $this->assertEqual(1204,$np1->page_fields[0]->notebook_page_field_id);
            $this->assertEqual(1201,$np1->page_fields[1]->notebook_page_field_id);
            $this->assertEqual(1202,$np1->page_fields[2]->notebook_page_field_id);
            $this->assertEqual(1203,$np1->page_fields[3]->notebook_page_field_id);
        }
    }