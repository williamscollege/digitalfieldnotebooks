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

            $this->assertEqual(-1,Metadata_Structure::cmp($mds2,$mds3));
            $this->assertEqual(1,Metadata_Structure::cmp($mds3,$mds2));

            $mds = Metadata_Structure::getAllFromDb(['parent_metadata_structure_id'=>0],$this->DB);

            usort($mds,'Metadata_Structure::cmp');

            $this->assertEqual('leaf',$mds[0]->name);
            $this->assertEqual('flower',$mds[1]->name);
        }

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
            $mds1 = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);

            $c = $mds1->getChildren();

            $this->assertEqual(2,count($c));
            $this->assertEqual(6002,$c[0]->metadata_structure_id);
            $this->assertEqual(6003,$c[1]->metadata_structure_id);
        }

        function testLoadReferences() {
            $mds1 = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);
            $this->assertEqual(0,count($mds1->references));

            $mds1->loadReferences();

            $this->assertEqual(1,count($mds1->references));
            $this->assertEqual(6301,$mds1->references[0]->metadata_reference_id);
        }

        function testLoadTermSetAndValues() {
            // no term set (parent structure)
            $mds0 = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);
            $this->assertEqual('',$mds0->term_set);

            $mds0->loadTermSetAndValues();

            $this->assertEqual('',$mds0->term_set);

            // has term set (leaf structure)
            $mds1 = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);
            $this->assertEqual('',$mds1->term_set);

            $mds1->loadTermSetAndValues();

            $this->assertEqual(6101,$mds1->term_set->metadata_term_set_id);
            $this->assertEqual(8,count($mds1->term_set->term_values));
            $this->assertEqual(6201,$mds1->term_set->term_values[0]->metadata_term_value_id);
            $this->assertEqual(6202,$mds1->term_set->term_values[1]->metadata_term_value_id);
            $this->assertEqual(6203,$mds1->term_set->term_values[2]->metadata_term_value_id);
            $this->assertEqual(6204,$mds1->term_set->term_values[3]->metadata_term_value_id);
            $this->assertEqual(6205,$mds1->term_set->term_values[4]->metadata_term_value_id);
            $this->assertEqual(6206,$mds1->term_set->term_values[5]->metadata_term_value_id);
            $this->assertEqual(6207,$mds1->term_set->term_values[6]->metadata_term_value_id);
            $this->assertEqual(6208,$mds1->term_set->term_values[7]->metadata_term_value_id);
        }

        //// instance methods - object itself

        function testRenderAsHtml() {
            $this->todo();
            return;

            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $canonical = '';

            $rendered = $mds->renderAsHtml();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsListItem() {
            $this->todo();
            return;

            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $canonical = '';

            $rendered = $mds->renderAsListItem();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsView_parent() {
            $this->todo();
            return;

            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $canonical = '';

            $rendered = $mds->renderAsView();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsView_child() {
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

//            $mds->

            $canonical = '<div id="rendered_metadata_structure_6002" class="rendered_metadata_structure">'.$mds->fieldsAsDataAttribs().'>
  <div class="metadata_lineage"></div>
  <h3>flower size</h3>
  <div class="description"></div>
  <div class="details"></div>
  <h5>small lengths</h5>
  <ul>
';

            $mds->loadTermSetAndValues();
            foreach ($mds->term_set->term_values as $tv) {
                $canonical .= '    '.$tv->renderAsListItem()."\n";
            }

            $canonical .= '  </ul>
</div>';

            $rendered = $mds->renderAsView();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsViewEmbed() {
            $this->todo();
            return;

            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $canonical = '';

            $rendered = $mds->renderAsViewEmbed();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

    }