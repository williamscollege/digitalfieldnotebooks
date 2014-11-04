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
			$this->assertEqual(count(Metadata_Term_Value::$fields), 9);

            $this->assertTrue(in_array('metadata_term_value_id', Metadata_Term_Value::$fields));
            $this->assertTrue(in_array('created_at', Metadata_Term_Value::$fields));
            $this->assertTrue(in_array('updated_at', Metadata_Term_Value::$fields));
            $this->assertTrue(in_array('metadata_term_set_id', Metadata_Term_Value::$fields));
            $this->assertTrue(in_array('name', Metadata_Term_Value::$fields));
            $this->assertTrue(in_array('ordering', Metadata_Term_Value::$fields));
            $this->assertTrue(in_array('description', Metadata_Term_Value::$fields));
            $this->assertTrue(in_array('flag_active', Metadata_Term_Value::$fields));
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
            $new = Metadata_Term_Value::createNewMetadataTermValue(6101,$this->DB);

            // 'metadata_term_set_id', 'created_at', 'updated_at', 'name', 'ordering', 'description', 'flag_delete'

            $this->assertEqual('NEW',$new->metadata_term_value_id);
            $this->assertNotEqual('',$new->created_at);
            $this->assertNotEqual('',$new->updated_at);
            $this->assertEqual(6101,$new->metadata_term_set_id);
            $this->assertEqual(util_lang('new_metadata_term_value_name'), $new->name);
            $this->assertEqual('0',$new->ordering);
            $this->assertEqual(util_lang('new_metadata_term_value_description'),$new->description);
//            $this->assertEqual('',$new->flag_workflow_published);
//            $this->assertEqual('',$new->flag_workflow_validated);
            $this->assertEqual('',$new->flag_delete);
        }

        function testRenderFormInteriorForNewMetadataTermValue() {
            global $DB;
            $DB = $this->DB;

            $canonical = '';
            $canonical .= '<div class="new-metadata-term-value edit-metadata-term-value">';
            $canonical .= '  <div class="edit-metadata-term-value-name">';
            $canonical .= '<input type="text" name="metadata-term-value-name-ABC" value="'.htmlentities(util_lang('new_metadata_term_value_name')).'"/>';
            $canonical .= '</div>'."\n";
            $canonical .= '  <div class="edit-metadata-term-value-description">';
            $canonical .= '<input type="text" name="metadata-term-value-description-ABC" value="'.htmlentities(util_lang('new_metadata_term_value_description')).'"/>';
            $canonical .= '</div>'."\n";
            $canonical .= '</div>';

            $rendered = Metadata_Term_Value::renderFormInteriorForNewMetadataTermValue('ABC');

//            echo "<pre>\n".htmlentities($canonical)."\n------------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
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
            $canonical = '<span class="term_value" title="up to the the length of the back of the hand when a fist is made">6-12 cm</span><div class="metadata-references"><div class="metadata-references">
  <ul class="metadata-references metadata-references-images">
  </ul>
  <ul class="metadata-references metadata-references-links">
  </ul>
  <ul class="metadata-references metadata-references-texts">
  </ul>
</div>
</div>';

            $rendered = $mdtv->renderAsHtml();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsHtml_with_references() {
            $mdtv = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id' => 6210],$this->DB);
            $mdtv->loadReferences();

            $canonical = '<span class="term_value" title="teeth outward pointing - 1 level / degree of teeth">dentate</span>';
            $canonical .= '<div class="metadata-references">';
            $canonical .= Metadata_Reference::renderReferencesArrayAsListsView($mdtv->references);
            $canonical .= '</div>';

            $rendered = $mdtv->renderAsHtml();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }


        function testRenderAsListItem_no_references() {
            $mdtv = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id' => 6205],$this->DB);

            $mdtv->loadReferences();

            // 'metadata_term_set_id', 'name', 'ordering', 'description', 'flag_delete'
            $canonical = '<li data-metadata_term_value_id="6205" data-created_at="'.$mdtv->created_at.'" data-updated_at="'.$mdtv->updated_at.'" data-metadata_term_set_id="6101" data-name="6-12 cm" data-ordering="5.00000" data-description="up to the the length of the back of the hand when a fist is made" data-flag_active="1" data-flag_delete="0">';
            $canonical .= '<span class="term_value" title="up to the the length of the back of the hand when a fist is made">6-12 cm</span>';
            $canonical .= '<div class="metadata-references">';
            $canonical .= Metadata_Reference::renderReferencesArrayAsListsView($mdtv->references);
            $canonical .= '</div>';
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
            $canonical = '<li data-metadata_term_value_id="6210" data-created_at="'.$mdtv->created_at.'" data-updated_at="'.$mdtv->updated_at.'" data-metadata_term_set_id="6103" data-name="dentate" data-ordering="1.00000" data-description="teeth outward pointing - 1 level / degree of teeth" data-flag_active="1" data-flag_delete="0">';
            $canonical .= '<span class="term_value" title="teeth outward pointing - 1 level / degree of teeth">dentate</span>';
            $canonical .= '<div class="metadata-references">';
            $canonical .= Metadata_Reference::renderReferencesArrayAsListsView($mdtv->references);
            $canonical .= '</div>';
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
            $canonical = '<div id="rendered_metadata_term_value_6210" class="rendered-metadata-term-value embedded" data-metadata_term_value_id="6210" data-created_at="'.$mdtv->created_at.'" data-updated_at="'.$mdtv->updated_at.'" data-metadata_term_set_id="6103" data-name="dentate" data-ordering="1.00000" data-description="teeth outward pointing - 1 level / degree of teeth" data-flag_active="1" data-flag_delete="0">';
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

        function testRenderAsListItemEdit() {
            $mdtv = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id' => 6210],$this->DB);
            $canonical = '';

            $canonical .= '<li id="list-item-metadata-term-value-6210" '.$mdtv->fieldsAsDataAttribs().'>';
            $canonical .= util_orderingUpDownControls('list-item-metadata-term-value-6210').' ';
            $canonical .= $mdtv->renderAsEdit();
            $canonical .= '<input type="hidden" name="original_ordering-list-item-metadata-term-value-6210" id="original_ordering-list-item-metadata-term-value-6210" value="'.$mdtv->ordering.'"/>';
            $canonical .= '<input type="hidden" name="new_ordering-list-item-metadata-term-value-6210" id="new_ordering-list-item-metadata-term-value-6210" value="'.$mdtv->ordering.'"/>';
            $canonical .= '</li>';

            $rendered = $mdtv->renderAsListItemEdit();

//            echo "<pre>\n".htmlentities($canonical)."\n-------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsEdit() {
            $mdtv = Metadata_Term_Value::getOneFromDb(['metadata_term_value_id' => 6210],$this->DB);
            $mdr1 = Metadata_Reference::getOneFromDb(['metadata_reference_id' => 6305],$this->DB);
            $mdr2 = Metadata_Reference::getOneFromDb(['metadata_reference_id' => 6304],$this->DB);
            $canonical = '';

            $canonical .= '<div id="edit-metadata-term-value-6210" class="edit-metadata-term-value" '.$mdtv->fieldsAsDataAttribs().'>'."\n";
            $canonical .= '  <div class="edit-metadata-term-value-name">';
            $canonical .= '<input type="text" name="metadata-term-value-name-6210" value="'.htmlentities($mdtv->name).'"/>';
            $canonical .= '</div>'."\n";
            $canonical .= '  <div class="edit-metadata-term-value-description">';
            $canonical .= '<input type="text" name="metadata-term-value-description-6210" value="'.htmlentities($mdtv->description).'"/>';
            $canonical .= '</div>'."\n";
            $canonical .= '  <div class="edit-metadata-term-value-references">'."\n";
            $canonical .= '    <ul class="add-metadata-references">'."\n";
            $canonical .= '      <li><a href="#" id="add_new_metadata_reference_button-for_metadata_term_value_6210" class="btn" data-for_metadata_term_value="6210">'.util_lang('add_metadata_reference').'</a></li>'."\n";
            $canonical .= '    </ul>'."\n";

            $canonical .= $mdtv->renderAsReferencesListEdit();

//            $canonical .= '<ul class="metadata-references">'."\n";
//            $canonical .= '<li><a href="#" id="add_new_metadata_reference_button-for_metadata_term_value_6210" class="btn" data-for_metadata_term_value="6210">'.util_lang('add_metadata_reference').'</a></li>'."\n";
//            $canonical .= '<li>'.$mdr1->renderAsEditEmbed().'</li>'."\n";
//            $canonical .= '<li>'.$mdr2->renderAsEditEmbed().'</li>'."\n";
//            $canonical .= '</ul>'."\n";
            $canonical .= '  </div>'."\n";
            $canonical .= '</div>'."\n";

            $rendered = $mdtv->renderAsEdit();

//            echo "<pre>\n".htmlentities($canonical)."\n-------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }
    }