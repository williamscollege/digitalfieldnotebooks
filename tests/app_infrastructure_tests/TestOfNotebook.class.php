<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfNotebook extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testNotebookAtributesExist() {
			$this->assertEqual(count(Notebook::$fields), 9);

			$this->assertTrue(in_array('notebook_id', Notebook::$fields));
            $this->assertTrue(in_array('created_at', Notebook::$fields));
            $this->assertTrue(in_array('updated_at', Notebook::$fields));
			$this->assertTrue(in_array('user_id', Notebook::$fields));
            $this->assertTrue(in_array('name', Notebook::$fields));
            $this->assertTrue(in_array('notes', Notebook::$fields));
			$this->assertTrue(in_array('flag_delete', Notebook::$fields));
		}

		//// static methods

		function testCmp() {
            $n1 = new Notebook(['notebook_id' => 50, 'name' => 'nA', 'user_id'=> 101, 'DB' => $this->DB]);
            $n2 = new Notebook(['notebook_id' => 60, 'name' => 'nB', 'user_id'=> 101, 'DB' => $this->DB]);
            $n3 = new Notebook(['notebook_id' => 70, 'name' => 'nB', 'user_id'=> 102, 'DB' => $this->DB]);

			$this->assertEqual(Notebook::cmp($n1, $n2), -1);
			$this->assertEqual(Notebook::cmp($n1, $n1), 0);
			$this->assertEqual(Notebook::cmp($n2, $n1), 1);

			$this->assertEqual(Notebook::cmp($n2, $n3), -1);
		}


		//// DB interaction tests

		function testNotebookDBInsert() {
			$n = new Notebook(['notebook_id' => 50, 'user_id' => 101, 'name' => 'testInsertNotebook', 'notes' => 'this is a test notebook', 'DB' => $this->DB]);

			$n->updateDb();

			$n2 = Notebook::getOneFromDb(['notebook_id' => 50], $this->DB);

			$this->assertTrue($n2->matchesDb);
            $this->assertEqual($n2->name, 'testInsertNotebook');
            $this->assertEqual($n2->notes, 'this is a test notebook');
		}

		function testNotebookRetrievedFromDb() {
			$n = new Notebook(['notebook_id' => 1001, 'DB' => $this->DB]);
			$this->assertNull($n->name);

			$n->refreshFromDb();
			$this->assertEqual($n->name, 'testnotebook1');
		}

        //// instance methods - object itself

        //// instance methods - related data

    }