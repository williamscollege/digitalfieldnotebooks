<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfMetadataReference extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testMetadataReferenceAtributesExist() {
			$this->assertEqual(count(Metadata_Reference::$fields), 10);

            $this->assertTrue(in_array('metadata_reference_id', Metadata_Reference::$fields));
            $this->assertTrue(in_array('created_at', Metadata_Reference::$fields));
            $this->assertTrue(in_array('updated_at', Metadata_Reference::$fields));
            $this->assertTrue(in_array('metadata_type', Metadata_Reference::$fields));
            $this->assertTrue(in_array('metadata_id', Metadata_Reference::$fields));
            $this->assertTrue(in_array('type', Metadata_Reference::$fields));
            $this->assertTrue(in_array('external_reference', Metadata_Reference::$fields));
            $this->assertTrue(in_array('description', Metadata_Reference::$fields));
            $this->assertTrue(in_array('ordering', Metadata_Reference::$fields));
            $this->assertTrue(in_array('flag_delete', Metadata_Reference::$fields));
		}

		//// static methods

		function testCmp() {
            $mdr1 = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6301],$this->DB);
            $mdr2 = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6302],$this->DB);

			$this->assertEqual(-1,Metadata_Reference::cmp($mdr1, $mdr2));
			$this->assertEqual(0, Metadata_Reference::cmp($mdr1, $mdr1));
			$this->assertEqual(1, Metadata_Reference::cmp($mdr2, $mdr1));

            $mdrs = Metadata_Reference::getAllFromDb([],$this->DB);

            usort($mdrs,'Metadata_Reference::cmp');

            $this->assertEqual(6301,$mdrs[0]->metadata_reference_id);
            $this->assertEqual(6302,$mdrs[1]->metadata_reference_id);
            $this->assertEqual(6303,$mdrs[2]->metadata_reference_id);
            $this->assertEqual(6307,$mdrs[3]->metadata_reference_id);
            $this->assertEqual(6306,$mdrs[4]->metadata_reference_id);
            $this->assertEqual(6305,$mdrs[5]->metadata_reference_id);
            $this->assertEqual(6304,$mdrs[6]->metadata_reference_id);
        }

        //// instance methods - object itself

        //// instance methods - related data

        function testGetReferrent() {
            $mdr1 = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6301],$this->DB);
            $mdr2 = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6302],$this->DB);
            $mdr3 = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6303],$this->DB);
            $mdrX = new Metadata_Reference(['metadata_reference_id' => 10001, 'DB' => $this->DB]);

            $r1 = $mdr1->getReferrent();
            $r2 = $mdr2->getReferrent();
            $r3 = $mdr3->getReferrent();
            $rX = $mdrX->getReferrent();

            $this->assertEqual(6001,$r1->metadata_structure_id);
            $this->assertEqual(6101,$r2->metadata_term_set_id);
            $this->assertEqual(6209,$r3->metadata_term_value_id);
            $this->assertEqual('UNKNOWN METADATA_TYPE: //',$rX);
        }

    }