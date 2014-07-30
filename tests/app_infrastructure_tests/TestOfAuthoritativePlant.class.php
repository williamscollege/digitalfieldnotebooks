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

            $this->assertTrue(in_array('authoritative_plant_id', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('created_at', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('updated_at', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('class', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('order', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('family', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('genus', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('species', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('variety', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('catalog_identifier', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('flag_delete', Action::$fields));
		}

		//// static methods

		function testCmp() {
            $p1 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);
            $p2 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5002],$this->DB);

            $this->assertEqual(Authoritative_Plant::cmp($p1, $p2), -1);
            $this->assertEqual(Authoritative_Plant::cmp($p1, $p1), 0);
            $this->assertEqual(Authoritative_Plant::cmp($p2, $p1), 1);

            $ps = Authoritative_Plant::getAllFromDb([],$this->DB);

            usort($ps,'Authoritative_Plant::cmp');

            $this->assertEqual('AP_1_CI',$ps[0]->catalog_identifier);
            $this->assertEqual('AP_2_CI',$ps[1]->catalog_identifier);
            $this->assertEqual('AP_3_CI',$ps[2]->catalog_identifier);
            $this->assertEqual('AP_4_CI',$ps[3]->catalog_identifier);
            $this->assertEqual('AP_5_CI',$ps[4]->catalog_identifier);
            $this->assertEqual('AP_6_CI',$ps[5]->catalog_identifier);
            $this->assertEqual('AP_7_CI',$ps[6]->catalog_identifier);
            $this->assertEqual('AP_8_CI',$ps[7]->catalog_identifier);
        }

        //// instance methods - object itself

        //// instance methods - related data

        function testLoadExtras() {
            $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id' => 5001],$this->DB);
            $this->assertEqual(0,count($ap->extras));

            $ap->loadExtras();

            $this->assertEqual(6,count($ap->extras));

            $this->assertEqual(5103,$ap->extras[0]->authoritative_plant_extra_id);
            $this->assertEqual(5101,$ap->extras[1]->authoritative_plant_extra_id);
            $this->assertEqual(5102,$ap->extras[2]->authoritative_plant_extra_id);
            $this->assertEqual(5106,$ap->extras[3]->authoritative_plant_extra_id);
            $this->assertEqual(5105,$ap->extras[4]->authoritative_plant_extra_id);
            $this->assertEqual(5104,$ap->extras[5]->authoritative_plant_extra_id);
        }

    }