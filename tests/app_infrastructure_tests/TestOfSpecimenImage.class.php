<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfSpecimenImage extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testSpecimenImageAtributesExist() {
			$this->assertEqual(count(Specimen_Image::$fields), 10);

            $this->assertTrue(in_array('specimen_image_id', Specimen_Image::$fields));
            $this->assertTrue(in_array('created_at', Specimen_Image::$fields));
            $this->assertTrue(in_array('updated_at', Specimen_Image::$fields));
            $this->assertTrue(in_array('specimen_id', Specimen_Image::$fields));
            $this->assertTrue(in_array('user_id', Specimen_Image::$fields));

            $this->assertTrue(in_array('image_reference', Specimen_Image::$fields));
            $this->assertTrue(in_array('ordering', Specimen_Image::$fields));

            $this->assertTrue(in_array('flag_workflow_published', Specimen_Image::$fields));
            $this->assertTrue(in_array('flag_workflow_validated', Specimen_Image::$fields));
            $this->assertTrue(in_array('flag_delete', Specimen_Image::$fields));
		}

		//// static methods

		function testCmp() {
            $si1 = Specimen_Image::getOneFromDb(['specimen_image_id'=>8101],$this->DB);
            $si2 = Specimen_Image::getOneFromDb(['specimen_image_id'=>8102],$this->DB);
            $si3 = Specimen_Image::getOneFromDb(['specimen_image_id'=>8103],$this->DB);
            $si4 = Specimen_Image::getOneFromDb(['specimen_image_id'=>8104],$this->DB);

            $this->assertEqual(Specimen_Image::cmp($si1, $si1), 0); // self-equal

            $this->assertEqual(Specimen_Image::cmp($si1, $si2), 1); // by ordering
            $this->assertEqual(Specimen_Image::cmp($si2, $si1), -1);

            $this->assertEqual(Specimen_Image::cmp($si1, $si3), -1); // by specimen
            $this->assertEqual(Specimen_Image::cmp($si3, $si1), 1);

            $this->assertEqual(Specimen_Image::cmp($si2, $si3), -1); // by specimen
            $this->assertEqual(Specimen_Image::cmp($si3, $si2), 1);

            $this->assertEqual(Specimen_Image::cmp($si3, $si4), 1); // by image_reference
            $this->assertEqual(Specimen_Image::cmp($si4, $si3), -1);
        }

        //// instance methods - object itself

        //// instance methods - related data
        function testGetSpecimen() {
            $si = Specimen_Image::getOneFromDb(['specimen_image_id'=>8101],$this->DB);

            $s = $si->getSpecimen();

            $this->assertEqual(8001,$s->specimen_id);
        }

    }