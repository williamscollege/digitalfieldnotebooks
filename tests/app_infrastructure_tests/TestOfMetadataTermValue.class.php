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

        ///////////////////////////////////////////////////////////////////
		//// static methods

		function testCmp() {
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

        function testCreateNewMetadataTermValue() {
            $this->todo();
        }

        function testRenderFormInteriorForNewMetadataTermValue() {
            $this->todo();
        }

        ///////////////////////////////////////////////////////////////////
        //// instance methods - related data

        function testGetMetadataTermSet() {
            $mdtv1 = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id'=>6201],$this->DB);

            $s = $mdtv1->getMetadataTermSet();

            $this->assertEqual(6101,$s->metadata_term_set_id);
        }

        function testLoadReferences() {
            $mdtv = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id' => 6210],$this->DB);
            $this->assertEqual(0,count($mdtv->references));

            $mdtv->loadReferences();

            $this->assertEqual(2,count($mdtv->references));

            $this->assertEqual(6305,$mdtv->references[0]->metadata_reference_id);
            $this->assertEqual(6304,$mdtv->references[1]->metadata_reference_id);
        }

        //// instance methods - object itself

        function testRenderAsHtml_no_references() {
            $mdtv = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id' => 6205],$this->DB);
            $canonical = '<span class="term_value" title="up to the the length of the back of the hand when a fist is made">6-12 cm</span>';

            $rendered = $mdtv->renderAsHtml();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsHtml_with_references() {
            $mdtv = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id' => 6210],$this->DB);
            $mdtv->loadReferences();

            $canonical = '<span class="term_value" title="teeth outward pointing - 1 level / degree of teeth">dentate</span>';
            $canonical .= '<ul class="metadata-references">';
            foreach ($mdtv->references as $r) {
                $canonical .= '<li>'.$r->renderAsViewEmbed().'</li>';
            }
            $canonical .= '</ul>';
            $rendered = $mdtv->renderAsHtml();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }


        function testRenderAsListItem_no_references() {
            $mdtv = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id' => 6205],$this->DB);

            $mdtv->loadReferences();

            // 'metadata_term_set_id', 'name', 'ordering', 'description', 'flag_delete'
            $canonical = '<li data-metadata_term_value_id="6205" data-created_at="'.$mdtv->created_at.'" data-updated_at="'.$mdtv->updated_at.'" data-metadata_term_set_id="6101" data-name="6-12 cm" data-ordering="5.00000" data-description="up to the the length of the back of the hand when a fist is made" data-flag_delete="0">';
            $canonical .= '<span class="term_value" title="up to the the length of the back of the hand when a fist is made">6-12 cm</span>';
            $canonical .= '</li>';

            $rendered = $mdtv->renderAsListItem();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsListItem_with_references() {
            $mdtv = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id' => 6210],$this->DB);

            $mdtv->loadReferences();

            // 'metadata_term_set_id', 'name', 'ordering', 'description', 'flag_delete'
            $canonical = '<li data-metadata_term_value_id="6210" data-created_at="'.$mdtv->created_at.'" data-updated_at="'.$mdtv->updated_at.'" data-metadata_term_set_id="6103" data-name="dentate" data-ordering="1.00000" data-description="teeth outward pointing - 1 level / degree of teeth" data-flag_delete="0">';
            $canonical .= '<span class="term_value" title="teeth outward pointing - 1 level / degree of teeth">dentate</span>';
            $canonical .= '<ul class="metadata-references">';
            foreach ($mdtv->references as $r) {
                $canonical .= '<li>'.$r->renderAsViewEmbed().'</li>';
            }
            $canonical .= '</ul>';
            $canonical .= '</li>';

            $rendered = $mdtv->renderAsListItem();

//            echo "<pre>\n".htmlentities($canonical)."\n-------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsViewEmbed() {
            $mdtv = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id' => 6210],$this->DB);

            $mdtv->loadReferences();

            // 'metadata_term_set_id', 'name', 'ordering', 'description', 'flag_delete'
            $canonical = '<div id="rendered_metadata_term_value_6210" class="rendered-metadata-term-value embedded" data-metadata_term_value_id="6210" data-created_at="'.$mdtv->created_at.'" data-updated_at="'.$mdtv->updated_at.'" data-metadata_term_set_id="6103" data-name="dentate" data-ordering="1.00000" data-description="teeth outward pointing - 1 level / degree of teeth" data-flag_delete="0">';
            $canonical .= $mdtv->renderAsHtml();
            $canonical .= '</div>';

            $rendered = $mdtv->renderAsViewEmbed();

//            echo "<pre>\n".htmlentities($canonical)."\n-------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsOption() {
            // $flag_selected
//            $this->todo();

            $mdtv = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id' => 6210],$this->DB);

            $mdtv->loadReferences();

            //--------------------------

            $canonical_not_selected = '<option data-metadata_term_value_id="'.$mdtv->metadata_term_value_id.'" data-description="'.htmlentities($mdtv->description).'" data-ARRAY_metadata_reference_ids="'.implode(',',Db_Linked::arrayOfAttrValues($mdtv->references,'metadata_reference_id')).'" title="'.htmlentities($mdtv->description).'" value="'.$mdtv->metadata_term_value_id.'">'.htmlentities($mdtv->name).'</option>';

            $rendered = $mdtv->renderAsOption();

//            echo "<pre>\n".htmlentities($canonical_not_selected)."\n-------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical_not_selected,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);

            //--------------------------

            $canonical_selected = '<option data-metadata_term_value_id="'.$mdtv->metadata_term_value_id.'" data-description="'.htmlentities($mdtv->description).'" data-ARRAY_metadata_reference_ids="'.implode(',',Db_Linked::arrayOfAttrValues($mdtv->references,'metadata_reference_id')).'" title="'.htmlentities($mdtv->description).'" value="'.$mdtv->metadata_term_value_id.'" selected="selected">'.htmlentities($mdtv->name).'</option>';

            $rendered = $mdtv->renderAsOption(true);

//            echo "<pre>\n".htmlentities($canonical_selected)."\n-------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical_selected,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);

        }

        function testRenderAsEditEmbed() {
            $this->todo();
        }

        function testRenderAsEditEmbed_NEW() {
            $this->todo();
        }
    }