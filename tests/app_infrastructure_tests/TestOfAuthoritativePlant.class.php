<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfAuthoritativePlant extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testAuthoritativePlantAtributesExist() {
			$this->assertEqual(count(Authoritative_Plant::$fields), 11);

//			  $this->assertTrue(in_array('action_id', Action::$fields));
//            $this->assertTrue(in_array('created_at', Action::$fields));
//            $this->assertTrue(in_array('updated_at', Action::$fields));
//			  $this->assertTrue(in_array('user_id', Action::$fields));
//            $this->assertTrue(in_array('name', Action::$fields));
//            $this->assertTrue(in_array('notes', Action::$fields));
//			  $this->assertTrue(in_array('flag_delete', Action::$fields));

            $this->fail("TODO: implement this test");
		}

		//// static methods

		function testCmp() {
//            $n1 = new Action(['action_id' => 50, 'name' => 'nA', 'DB' => $this->DB]);
//            $n2 = new Action(['action_id' => 60, 'name' => 'nB', 'DB' => $this->DB]);
//
//			$this->assertEqual(Action::cmp($n1, $n2), -1);
//			$this->assertEqual(Action::cmp($n1, $n1), 0);
//			$this->assertEqual(Action::cmp($n2, $n1), 1);

            $this->fail("TODO: implement this test");
        }

        //// instance methods - object itself

        //// instance methods - related data

    }