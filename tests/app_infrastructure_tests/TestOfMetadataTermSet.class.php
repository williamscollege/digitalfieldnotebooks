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

        function testCreateNewMetadataTermSet() {
            $this->todo();
        }

        function testRenderAllAsSelectControl() {
            $this->todo();
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

        function testLoadStructures() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);
            $this->assertEqual(0,count($mdts->structures));

            $mdts->loadStructures();

            $this->assertEqual(1,count($mdts->structures));

            $this->assertEqual(6002,$mdts->structures[0]->metadata_structure_id);
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

        function testRenderAsHtml_references() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);
            $mdts->loadReferences();

            $canonical = '<ul class="metadata-references">';
            foreach ($mdts->references as $r) {
                $canonical .= '<li>'.$r->renderAsViewEmbed().'</li>';
            }
            $canonical .= '</ul>';

            $rendered = $mdts->renderAsHtml_references();

//            echo "<pre>\n".htmlentities($canonical)."\n--------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsHtml_term_values() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);
            $mdts->loadTermValues();

            $canonical = '<h5>'.util_lang('metadata_values','properize').'</h5>'."\n".
                '<ul class="metadata-term-values">';
            foreach ($mdts->term_values as $tv) {
                $canonical .= $tv->renderAsListItem();
            }
            $canonical .= '</ul>';

            $rendered = $mdts->renderAsHtml_term_values();

//            echo "<pre>\n".htmlentities($canonical)."\n--------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsHtml_structures() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);
            $mdts->loadStructures();

            $canonical = '<h5>'.util_lang('used_by').'</h5>'."\n";
            $canonical .= '<div class="metadata-term-set-uses">';
            $canonical .= '<ul class="metadata-structures">';
            foreach ($mdts->structures as $s) {
                $canonical .= '<li>'.$s->renderAsLink().'</li>';
            }
            $canonical .= '</ul></div>';

            $rendered = $mdts->renderAsHtml_structures();

//            echo "<pre>\n".htmlentities($canonical)."\n--------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsHtml() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);
            $mdts->loadReferences();
            $mdts->loadTermValues();
            $mdts->loadStructures();

            $canonical = '<div class="metadata-term-set-header"><h4>Value Set : <a href="'.APP_ROOT_PATH.'/app_code/metadata_term_set.php?action=view&metadata_term_set_id=6101" class="metadata_term_set_name_link" data-metadata_term_set_id="6101">small lengths</a></h4>';
            $canonical .= $mdts->renderAsHtml_references();
            $canonical .= '</div>';
            $canonical .= $mdts->renderAsHtml_term_values();
            $canonical .= $mdts->renderAsHtml_structures();


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
            $canonical .= $mdts->renderAsViewEmbed();
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
            $canonical = '<div id="rendered_metadata_term_set_6101" class="rendered-metadata-term-set embedded" data-metadata_term_set_id="6101" data-created_at="'.$mdts->created_at.'" data-updated_at="'.$mdts->updated_at.'" data-name="small lengths" data-ordering="1.00000" data-description="lengths ranging from 3 mm to 30 cm" data-flag_delete="0">';
            $canonical .= $mdts->renderAsHtml();
            $canonical .= '</div>';

            $rendered = $mdts->renderAsViewEmbed();

//            echo "<pre>\n".htmlentities($canonical)."\n-------\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }


        function testRenderAsView() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);

            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id' => 6002],$this->DB);

            $canonical = '<div class="view-metadata-term-set">'."\n";
            $canonical .= '<div class="view-metadata-term-set-header"><a href="'.APP_ROOT_PATH.'/app_code/metadata_term_set.php?action=list">'.util_lang('all_metadata_term_sets').'</a> &gt; <h3>small lengths</h3></div>';
            $canonical .= $mdts->renderAsHtml_references();
            $canonical .= $mdts->renderAsHtml_term_values();
//            $canonical .= '<div class="metadata-term-set-uses">';
            $canonical .= $mdts->renderAsHtml_structures();
            $canonical .= '</div>'."\n";
            $canonical .= '</div>'."\n";

            $rendered = $mdts->renderAsView();

//                echo "<pre>\n".htmlentities($canonical)."\n-------\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);

//            $this->assertPattern('/'.htmlentities($mds->name).'/',$rendered);
        }

        function testRenderAsSelectControl() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);
            $mdtvs = Metadata_Term_Value::getAllFromDb(['metadata_term_set_id' => 6101],$this->DB);
            usort($mdtvs,'Metadata_Term_Value::cmp');

            $selected_id = 6203;

            $canonical = '<select name="namefoo" id="idfoo" class="metadata_term_value_select_control">'."\n";
            $canonical .= '  <option value="-1">-- '.util_lang('nothing_from_the_list').' --</option>'."\n";
            foreach ($mdtvs as $v) {
                $canonical .= '  '.$v->renderAsOption($v->metadata_term_value_id == $selected_id)."\n";
            }
            $canonical .= '</select>';

            $rendered = $mdts->renderAsSelectControl('namefoo','6203','idfoo');

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);

//                echo "<pre>\n".htmlentities($canonical)."\n-------\n".htmlentities($rendered)."\n</pre>";
        }

        function testRenderAsEdit() {
            $this->todo();
        }


    function testRenderAsEditEmbed_NEW() {
        $this->todo();
    }

}