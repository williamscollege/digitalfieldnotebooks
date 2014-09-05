<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfMetadataTermSet extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testMetadataTermSetAtributesExist() {
			$this->assertEqual(count(Metadata_Term_Set::$fields), 7);

            $this->assertTrue(in_array('metadata_term_set_id', Metadata_Term_Set::$fields));
            $this->assertTrue(in_array('created_at', Metadata_Term_Set::$fields));
            $this->assertTrue(in_array('updated_at', Metadata_Term_Set::$fields));
            $this->assertTrue(in_array('name', Metadata_Term_Set::$fields));
            $this->assertTrue(in_array('ordering', Metadata_Term_Set::$fields));
            $this->assertTrue(in_array('description', Metadata_Term_Set::$fields));
            $this->assertTrue(in_array('flag_delete', Metadata_Term_Set::$fields));
		}

		//// static methods

		function testCmp() {
            $n1 = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);
            $n2 = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6102],$this->DB);
            $n3 = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6103],$this->DB);

			$this->assertEqual(Metadata_Term_Set::cmp($n1, $n2), -1);
			$this->assertEqual(Metadata_Term_Set::cmp($n1, $n1), 0);
			$this->assertEqual(Metadata_Term_Set::cmp($n2, $n1), 1);

            $this->assertEqual(Metadata_Term_Set::cmp($n3, $n1), -1);
        }

        //// instance methods - related data

        function testGetMetadataStructures() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);

            $structures = $mdts->getMetadataStructures();

            $this->assertEqual(1,count($structures));
            $this->assertEqual(6002,$structures[0]->metadata_structure_id);
        }

        function testLoadTermValues() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);
            $this->assertEqual(0,count($mdts->term_values));

            $mdts->loadTermValues();

            $this->assertEqual(8,count($mdts->term_values));

            $this->assertEqual(6201,$mdts->term_values[0]->metadata_term_value_id);
            $this->assertEqual(6202,$mdts->term_values[1]->metadata_term_value_id);
            $this->assertEqual(6203,$mdts->term_values[2]->metadata_term_value_id);
            $this->assertEqual(6204,$mdts->term_values[3]->metadata_term_value_id);
            $this->assertEqual(6205,$mdts->term_values[4]->metadata_term_value_id);
            $this->assertEqual(6206,$mdts->term_values[5]->metadata_term_value_id);
            $this->assertEqual(6207,$mdts->term_values[6]->metadata_term_value_id);
            $this->assertEqual(6208,$mdts->term_values[7]->metadata_term_value_id);
        }

        function testLoadReferences() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);
            $this->assertEqual(0,count($mdts->references));

            $mdts->loadReferences();

            $this->assertEqual(1,count($mdts->references));

            $this->assertEqual(6302,$mdts->references[0]->metadata_reference_id);
        }

        //// instance methods - object itself

//        function testRenderAsLink() {
//            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);
//            $canonical = '<div class="metadata-term-set-header">small lengths';
//
//            $rendered = $mdts->renderAsLink();
////            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
//
//            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
//            $this->assertEqual($canonical,$rendered);
//        }

        function testRenderAsHtml() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);
            $mdts->loadReferences();
            $mdts->loadTermValues();

            $canonical = '<div class="metadata-term-set-header"><a class="metadata_term_set_name_link" data-metadata_term_set_id="6101">small lengths</a>';
            $canonical .= '<ul class="metadata-references">';
            foreach ($mdts->references as $r) {
                $canonical .= '<li>'.$r->renderAsViewEmbed().'</li>';
            }
            $canonical .= '</ul></div>';
            $canonical .= '<ul class="metadata-term-values">';
            foreach ($mdts->term_values as $tv) {
                $canonical .= $tv->renderAsListItem();
            }
            $canonical .= '</ul>';

            $rendered = $mdts->renderAsHtml();

//            echo "<pre>\n".htmlentities($canonical)."\n--------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsListItem() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);
            $mdts->loadReferences();
            $mdts->loadTermValues();

            // 'name', 'ordering', 'description', 'flag_delete'
            $canonical = '<li data-metadata_term_set_id="6101" data-created_at="'.$mdts->created_at.'" data-updated_at="'.$mdts->updated_at.'" data-name="small lengths" data-ordering="1.00000" data-description="lengths ranging from 3 mm to 30 cm" data-flag_delete="0">';
            $canonical .= $mdts->renderAsHtml();
            $canonical .= '</li>';

            $rendered = $mdts->renderAsListItem();

//            echo "<pre>\n".htmlentities($canonical)."\n-------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsViewEmbed() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);
            $mdts->loadReferences();
            $mdts->loadTermValues();

            // 'name', 'ordering', 'description', 'flag_delete'
            $canonical = '<div id="rendered_metadata_term_set_6101" class="rendered-metadata-term-set" data-metadata_term_set_id="6101" data-created_at="'.$mdts->created_at.'" data-updated_at="'.$mdts->updated_at.'" data-name="small lengths" data-ordering="1.00000" data-description="lengths ranging from 3 mm to 30 cm" data-flag_delete="0">';
            $canonical .= $mdts->renderAsHtml();
            $canonical .= '</div>';

            $rendered = $mdts->renderAsViewEmbed();

//            echo "<pre>\n".htmlentities($canonical)."\n-------\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }


        function testRenderAsView() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);

            $canonical = '<div class="metadata-term-set-header"><a href="/digitalfieldnotebooks/app_code/metadata_term_set.php?action=list">all metadata value sets</a> &gt; small lengths</div>';
            $canonical .= $mdts->renderAsHtml_references();
            $canonical .= $mdts->renderAsHtml_term_values();

            $rendered = $mdts->renderAsView();

//                echo "<pre>\n".htmlentities($canonical)."\n-------\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }
    }