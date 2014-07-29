<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfMetadataStructure extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testMetadataStructureAtributesExist() {
			$this->assertEqual(count(Metadata_Structure::$fields), 10);

            $this->assertTrue(in_array('metadata_structure_id', Metadata_Structure::$fields));
            $this->assertTrue(in_array('created_at', Metadata_Structure::$fields));
            $this->assertTrue(in_array('updated_at', Metadata_Structure::$fields));
            $this->assertTrue(in_array('parent_metadata_structure_id', Metadata_Structure::$fields));
            $this->assertTrue(in_array('name', Metadata_Structure::$fields));
            $this->assertTrue(in_array('ordering', Metadata_Structure::$fields));
            $this->assertTrue(in_array('description', Metadata_Structure::$fields));
            $this->assertTrue(in_array('details', Metadata_Structure::$fields));
            $this->assertTrue(in_array('metadata_term_set_id', Metadata_Structure::$fields));
            $this->assertTrue(in_array('flag_delete', Metadata_Structure::$fields));
		}

		//// static methods

		function testCmp() {
            $mds1 = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);
            $mds2 = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6004],$this->DB);
            $this->assertEqual(-1,Metadata_Structure::cmp($mds2,$mds1));
            $this->assertEqual(1,Metadata_Structure::cmp($mds1,$mds2));
            $this->assertEqual(0,Metadata_Structure::cmp($mds2,$mds2));

            $mds3 = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);
            $this->assertEqual(-1,Metadata_Structure::cmp($mds1,$mds3));
            $this->assertEqual(1,Metadata_Structure::cmp($mds3,$mds1));


            $mds = Metadata_Structure::getAllFromDb(['parent_metadata_structure_id'=>0],$this->DB);

            usort($mds,'Metadata_Structure::cmp');

            $this->assertEqual('leaf',$mds[0]->name);
            $this->assertEqual('flower',$mds[1]->name);
        }

        //// instance methods - object itself

        //// instance methods - related data

        function testGetParent() {
            $mdsP = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);

            $p = $mdsP->getParent();
            $this->assertFalse($p);

            $mdsC = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);

            $p = $mdsC->getParent();
            $this->assertEqual($p->metadata_structure_id,$mdsP->metadata_structure_id);
        }

        function testGetRoot() {
            $mdsP = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);

            $p = $mdsP->getRoot();
            $this->assertEqual($p->metadata_structure_id,$mdsP->metadata_structure_id);

            $mdsC = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);

            $p = $mdsP->getRoot();
            $this->assertEqual($p->metadata_structure_id,$mdsP->metadata_structure_id);
        }

        function testGetLineage() {
            $mds1 = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);
            $mds2 = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);

            $lin1 = $mds1->getLineage();
            $this->assertEqual(1,count($lin1));
            $this->assertEqual(6001,$lin1[0]->metadata_structure_id);

            $lin2 = $mds2->getLineage();
            $this->assertEqual(2,count($lin2));
            $this->assertEqual(6001,$lin2[0]->metadata_structure_id);
            $this->assertEqual(6002,$lin2[1]->metadata_structure_id);
        }

        function testGetChildren() {
            $this->fail('TODO: test for getChildren');
        }

        function testLoadReferences() {
            $this->fail('TODO: test for loadReferences');
        }
    }