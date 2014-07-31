<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfSpecimen extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testSpecimenAtributesExist() {
			$this->assertEqual(count(Specimen::$fields), 15);

            $this->assertTrue(in_array('specimen_id', Specimen::$fields));
            $this->assertTrue(in_array('created_at', Specimen::$fields));
            $this->assertTrue(in_array('updated_at', Specimen::$fields));

            $this->assertTrue(in_array('user_id', Specimen::$fields));
            $this->assertTrue(in_array('link_to_type', Specimen::$fields));
            $this->assertTrue(in_array('link_to_id', Specimen::$fields));
            $this->assertTrue(in_array('user_id', Specimen::$fields));
            $this->assertTrue(in_array('name', Specimen::$fields));
            $this->assertTrue(in_array('gps_x', Specimen::$fields));
            $this->assertTrue(in_array('gps_y', Specimen::$fields));
            $this->assertTrue(in_array('notes', Specimen::$fields));
            $this->assertTrue(in_array('ordering', Specimen::$fields));
            $this->assertTrue(in_array('catalog_identifier', Specimen::$fields));

            $this->assertTrue(in_array('flag_workflow_published', Specimen::$fields));
            $this->assertTrue(in_array('flag_workflow_validated', Specimen::$fields));

            $this->assertTrue(in_array('flag_delete', Specimen::$fields));
		}

		//// static methods

		function testCmp() {
            $s1 = Specimen::getOneFromDb(['specimen_id'=>8001],$this->DB);
            $s2 = Specimen::getOneFromDb(['specimen_id'=>8002],$this->DB);

			$this->assertEqual(Specimen::cmp($s1, $s2), -1);
			$this->assertEqual(Specimen::cmp($s1, $s1), 0);
			$this->assertEqual(Specimen::cmp($s2, $s1), 1);

            $sall = Specimen::getAllFromDb([],$this->DB);

            usort($sall,'Specimen::cmp');

            $this->assertEqual(4,count($sall));

            $this->assertEqual(8001,$sall[0]->specimen_id);
            $this->assertEqual(8003,$sall[1]->specimen_id);
            $this->assertEqual(8002,$sall[2]->specimen_id);
            $this->assertEqual(8004,$sall[3]->specimen_id);
        }

        //// instance methods - object itself

        //// instance methods - related data
        function testGetUser() {
            $s = Specimen::getOneFromDb(['specimen_id'=>8001],$this->DB);

            $u = $s->getUser();

            $this->assertEqual(110,$u->user_id);
        }

        function testGetLinked() {
            $s = Specimen::getOneFromDb(['specimen_id'=>8001],$this->DB);

            $linked = $s->getLinked();

            $this->assertEqual(5001,$linked->authoritative_plant_id);
        }

        function testLoadImages() {
            $s = Specimen::getOneFromDb(['specimen_id'=>8001],$this->DB);

            $s->loadImages();

            $this->assertEqual(2,count($s->images));

            $this->assertEqual(8102,$s->images[0]->specimen_image_id);
            $this->assertEqual(8101,$s->images[1]->specimen_image_id);
        }
    }