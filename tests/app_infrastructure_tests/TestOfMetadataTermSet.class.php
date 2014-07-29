<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfMetadataTermSet extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testMetadataTermSetAtributesExist() {
			$this->assertEqual(count(Metadata_Term_Set::$fields), 7);

            $this->assertTrue(in_array('metadata_term_set_id', Metadata_Term_Set::$fields));
            $this->assertTrue(in_array('created_at', Metadata_Term_Set::$fields));
            $this->assertTrue(in_array('updated_at', Metadata_Term_Set::$fields));
            $this->assertTrue(in_array('name', Metadata_Term_Set::$fields));
            $this->assertTrue(in_array('ordering', Metadata_Term_Set::$fields));
            $this->assertTrue(in_array('description', Metadata_Term_Set::$fields));
            $this->assertTrue(in_array('flag_delete', Metadata_Term_Set::$fields));
		}

		//// static methods

		function testCmp() {
            $n1 = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);
            $n2 = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6102],$this->DB);
            $n3 = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6103],$this->DB);

			$this->assertEqual(Metadata_Term_Set::cmp($n1, $n2), -1);
			$this->assertEqual(Metadata_Term_Set::cmp($n1, $n1), 0);
			$this->assertEqual(Metadata_Term_Set::cmp($n2, $n1), 1);

            $this->assertEqual(Metadata_Term_Set::cmp($n3, $n1), -1);
        }

        //// instance methods - object itself

        //// instance methods - related data

        function testGetMetadataStructures() {
            $this->fail('TODO: implement test for getMetadataStructures');
        }

        function testLoadTermValues() {
            $this->fail('TODO: implement test for loadTermValues');
        }

        function testLoadReferences() {
            $this->fail('TODO: implement test for loadReferences');
        }

    }