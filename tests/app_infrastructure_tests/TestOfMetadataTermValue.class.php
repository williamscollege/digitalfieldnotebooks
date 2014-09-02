<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfMetadataTermValue extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testMetadataTermValueAtributesExist() {
			$this->assertEqual(count(Metadata_Term_Value::$fields), 8);

            $this->assertTrue(in_array('metadata_term_value_id', Metadata_Term_Value::$fields));
            $this->assertTrue(in_array('created_at', Metadata_Term_Value::$fields));
            $this->assertTrue(in_array('updated_at', Metadata_Term_Value::$fields));
            $this->assertTrue(in_array('metadata_term_set_id', Metadata_Term_Value::$fields));
            $this->assertTrue(in_array('name', Metadata_Term_Value::$fields));
            $this->assertTrue(in_array('ordering', Metadata_Term_Value::$fields));
            $this->assertTrue(in_array('description', Metadata_Term_Value::$fields));
            $this->assertTrue(in_array('flag_delete', Metadata_Term_Value::$fields));
		}

		//// static methods

		function testCmp() {
//            $n1 = new Action(['action_id' => 50, 'name' => 'nA', 'DB' => $this->DB]);
//            $n2 = new Action(['action_id' => 60, 'name' => 'nB', 'DB' => $this->DB]);
//
//			$this->assertEqual(Action::cmp($n1, $n2), -1);
//			$this->assertEqual(Action::cmp($n1, $n1), 0);
//			$this->assertEqual(Action::cmp($n2, $n1), 1);

            $mdtv1 = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id'=>6201],$this->DB);
            $mdtv2 = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id'=>6202],$this->DB);

			$this->assertEqual(Metadata_Term_Value::cmp($mdtv1, $mdtv2), -1);
			$this->assertEqual(Metadata_Term_Value::cmp($mdtv1, $mdtv1), 0);
			$this->assertEqual(Metadata_Term_Value::cmp($mdtv2, $mdtv1), 1);

            $all = Metadata_Term_Value::getAllFromDb([],$this->DB);

            usort($all,'Metadata_Term_Value::cmp');

            $this->assertEqual(6201, $all[0]->metadata_term_value_id);
            $this->assertEqual(6202, $all[1]->metadata_term_value_id);
            $this->assertEqual(6203, $all[2]->metadata_term_value_id);
            $this->assertEqual(6204, $all[3]->metadata_term_value_id);
            $this->assertEqual(6205, $all[4]->metadata_term_value_id);
            $this->assertEqual(6206, $all[5]->metadata_term_value_id);
            $this->assertEqual(6207, $all[6]->metadata_term_value_id);
            $this->assertEqual(6208, $all[7]->metadata_term_value_id);
            $this->assertEqual(6212, $all[8]->metadata_term_value_id);
            $this->assertEqual(6213, $all[9]->metadata_term_value_id);
            $this->assertEqual(6211, $all[10]->metadata_term_value_id);
            $this->assertEqual(6210, $all[11]->metadata_term_value_id);
            $this->assertEqual(6209, $all[12]->metadata_term_value_id);
        }

        //// instance methods - related data

        function testGetMetadataTermSet() {
            $mdtv1 = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id'=>6201],$this->DB);

            $s = $mdtv1->getMetadataTermSet();

            $this->assertEqual(6101,$s->metadata_term_set_id);
        }

        function testLoadReferences() {
            $mdtv = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id' => 6213],$this->DB);
            $this->assertEqual(0,count($mdtv->references));

            $mdtv->loadReferences();

            $this->assertEqual(2,count($mdtv->references));

            $this->assertEqual(6305,$mdtv->references[0]->metadata_reference_id);
            $this->assertEqual(6304,$mdtv->references[1]->metadata_reference_id);
        }

        //// instance methods - object itself

        function testRenderAsHtml() {
            $this->todo();
        }

        function testRenderAsListItem_no_references() {
            $this->todo();
        }

        function testRenderAsListItem_with_references() {
            $mdtv = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id' => 6213],$this->DB);

            $mdtv->loadReferences();

            // 'metadata_term_set_id', 'name', 'ordering', 'description', 'flag_delete'
            $canonical = '<li data-metadata_term_value_id="6213" data-created_at="'.$mdtv->created_at.'" data-updated_at="'.$mdtv->updated_at.'" data-metadata_term_set_id="6102" data-name="blue" data-ordering="2" data-description="" data-flag_delete="0">';
            $canonical .= '<span class="term_value">blue</span>';
            $canonical .= '<ul class="metadata_references">';
            foreach ($mdtv->references as $r) {
                $canonical .= '<li>'.$r->renderAsViewEmbed().'</li>';
            }
            $canonical .= '</ul>';
            $canonical .= '</li>';

            $rendered = $mdtv->renderAsListItem();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);

        }

    }