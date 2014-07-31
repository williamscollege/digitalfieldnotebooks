<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfNotebookPageField extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testNotebookPageFieldAtributesExist() {
			$this->assertEqual(count(Notebook_Page_Field::$fields), 8);

            $this->assertTrue(in_array('notebook_page_field_id', Notebook_Page_Field::$fields));
            $this->assertTrue(in_array('created_at', Notebook_Page_Field::$fields));
            $this->assertTrue(in_array('updated_at', Notebook_Page_Field::$fields));
            $this->assertTrue(in_array('notebook_page_id', Notebook_Page_Field::$fields));
            $this->assertTrue(in_array('label_metadata_structure_id', Notebook_Page_Field::$fields));
            $this->assertTrue(in_array('value_metadata_term_value_id', Notebook_Page_Field::$fields));
            $this->assertTrue(in_array('value_open', Notebook_Page_Field::$fields));
            $this->assertTrue(in_array('flag_delete', Notebook_Page_Field::$fields));
		}

		//// static methods

		function testCmp() {
            $pfs = Notebook_Page_Field::getAllFromDb([],$this->DB);

            usort($pfs,'Notebook_Page_Field::cmp');

            $this->assertEqual(1204,$pfs[0]->notebook_page_field_id);
            $this->assertEqual(1201,$pfs[1]->notebook_page_field_id);
            $this->assertEqual(1202,$pfs[2]->notebook_page_field_id);
            $this->assertEqual(1203,$pfs[3]->notebook_page_field_id);
            $this->assertEqual(1205,$pfs[4]->notebook_page_field_id);
        }

        //// instance methods - object itself

        //// instance methods - related data

        function testGetNotebookPage() {
            $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1201],$this->DB);
            $p = $npf->getNotebookPage();
            $this->assertEqual(1101,$p->notebook_page_id);
        }

        function testGetMetadataStructure() {
            $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1201],$this->DB);
            $s = $npf->getMetadataStructure();
            $this->assertEqual(6002,$s->metadata_structure_id);
        }

        function testGetMetadataTermValue() {
            $npf = Notebook_Page_Field::getOneFromDb(['notebook_page_field_id'=>1201],$this->DB);
            $v = $npf->getMetadataTermValue();
            $this->assertEqual(6202,$v->metadata_term_value_id);
        }

    }