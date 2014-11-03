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
            $new = Metadata_Term_Set::createNewMetadataTermSet($this->DB);

            // 'metadata_term_set_id', 'created_at', 'updated_at', 'name', 'ordering', 'description', 'flag_delete'

            $this->assertEqual('NEW',$new->metadata_term_set_id);
            $this->assertNotEqual('',$new->created_at);
            $this->assertNotEqual('',$new->updated_at);
            $this->assertEqual(util_lang('new_metadata_term_set_name'), $new->name);
            $this->assertEqual('0',$new->ordering);
            $this->assertEqual(util_lang('new_metadata_term_set_description'),$new->description);
//            $this->assertEqual('',$new->flag_workflow_published);
//            $this->assertEqual('',$new->flag_workflow_validated);
            $this->assertEqual('',$new->flag_delete);
        }

        function testRenderAllAsSelectControl() {
            global $DB;
            $DB = $this->DB;

            $canonical = '<select name="metadata_term_set_id" id="metadata_term_set_id" class="metadata_term_set_selector">
<option value="-1">'.util_lang('prompt_select').'</option>
<option value="6103" title="the shape / pattern of an edge">margin styles</option>
<option value="6101" title="lengths ranging from 3 mm to 30 cm">small lengths</option>
<option value="6102" title="basic colors">colors</option>
<option value="6104" title="general kinds of places plants live (no terms)">habitats</option>
</select>';

            $rendered = Metadata_Term_Set::renderAllAsSelectControl();

//            echo "<pre>\n".htmlentities($canonical)."\n--------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
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

            $canonical = Metadata_Reference::renderReferencesArrayAsListsView($mdts->references);

//            $list_items_image = '';
//            $list_items_link = '';
//            $list_items_text = '';
//            foreach ($mdts->references as $r) {
//                if ($r->type == 'image') {
//                    $list_items_image .= '<li>'.$r->renderAsViewEmbed().'</li>'."\n";
//                } elseif ($r->type == 'link') {
//                    $list_items_link .= '<li>'.$r->renderAsViewEmbed().'</li>'."\n";
//                } elseif ($r->type == 'text') {
//                    $list_items_text .= '<li>'.$r->renderAsViewEmbed().'</li>'."\n";
//                }
//            }
//
//            $canonical .= '<ul class="metadata-references edit-metadata-references metadata-references-images">."\n"';
//            $canonical .= $list_items_image;
//            $canonical .= '</ul>'."\n";
//            $canonical .= '<ul class="metadata-references edit-metadata-references metadata-references-links">'."\n";
//            $canonical .= $list_items_link;
//            $canonical .= '</ul>'."\n";
//            $canonical .= '<ul class="metadata-references edit-metadata-references metadata-references-texts">'."\n";
//            $canonical .= $list_items_text;
//            $canonical .= '</ul>'."\n";

            $rendered = $mdts->renderAsHtml_references();

//            echo "<pre>\n".htmlentities($canonical)."\n--------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsEdit_references() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);
            $mdts->loadReferences();

            $canonical = Metadata_Reference::renderReferencesArrayAsListsEdit($mdts->references);

//            $canonical = '';
//
//            $list_items_image = '';
//            $list_items_link = '';
//            $list_items_text = '';
//            foreach ($mdts->references as $r) {
//                if ($r->type == 'image') {
//                    $list_items_image .= '<li>'.$r->renderAsEditEmbed().'</li>'."\n";
//                } elseif ($r->type == 'link') {
//                    $list_items_link .= '<li>'.$r->renderAsEditEmbed().'</li>'."\n";
//                } elseif ($r->type == 'text') {
//                    $list_items_text .= '<li>'.$r->renderAsEditEmbed().'</li>'."\n";
//                }
//            }
//
//            $canonical .= '<ul class="metadata-references edit-metadata-references metadata-references-images">."\n"';
//            $canonical .= $list_items_image;
//            $canonical .= '</ul>'."\n";
//            $canonical .= '<ul class="metadata-references edit-metadata-references metadata-references-links">'."\n";
//            $canonical .= $list_items_link;
//            $canonical .= '</ul>'."\n";
//            $canonical .= '<ul class="metadata-references edit-metadata-references metadata-references-texts">'."\n";
//            $canonical .= $list_items_text;
//            $canonical .= '</ul>'."\n";

            $rendered = $mdts->renderAsEdit_references();

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

        function testRenderAsEdit_term_values() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);
            $mdts->loadTermValues();
            $canonical = '';

            $canonical .= '<h5>'.util_lang('metadata_values','properize').'</h5>'."\n";
            $canonical .= '<ul class="metadata-term-values">'."\n";
//            $canonical .= '  <li></li>'."\n";
            $canonical .= '    <li><a href="#" id="add_new_metadata_term_value_button" class="btn">'.util_lang('add_metadata_term_value').'</a></li>'."\n";

            foreach ($mdts->term_values as $tv) {
                $canonical .= $tv->renderAsListItemEdit();
            }
            $canonical .= '</ul>';

            $rendered = $mdts->renderAsEdit_term_values();

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

        function testRenderAsOption() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);


            //---- base

            $canonical = '<option value="'.$mdts->metadata_term_set_id.'" title="'.htmlentities($mdts->description).'">'.htmlentities($mdts->name).'</option>';

            $rendered = $mdts->renderAsOption();

//                echo "<pre>\n".htmlentities($canonical)."\n-------\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);

            //---- selected

            $canonical = '<option value="'.$mdts->metadata_term_set_id.'" title="'.htmlentities($mdts->description).'" selected="selected">'.htmlentities($mdts->name).'</option>';

            $rendered = $mdts->renderAsOption('',$mdts->metadata_term_set_id);

//                echo "<pre>\n".htmlentities($canonical)."\n-------\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);

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

            //                echo "<pre>\n".htmlentities($canonical)."\n-------\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsEdit() {
            $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id' => 6101],$this->DB);

            $canonical = '';

            $canonical .= '<form id="form-edit-metadata-term-set-base-data" action="'.APP_ROOT_PATH.'/app_code/metadata_term_set.php">'."\n";
            $canonical .= '  <input type="hidden" name="action" value="update"/>'."\n";
            $canonical .= '  <input type="hidden" id="metadata_term_set_id" name="metadata_term_set_id" value="'.$mdts->metadata_term_set_id.'"/>'."\n";

            $canonical .= '  <div id="actions">'."\n";
            $canonical .= '    <button id="edit-submit-control" class="btn btn-success" type="submit" name="edit-submit-control" value="update"><i class="icon-ok-sign icon-white"></i> '.util_lang('update','properize').'</button>'."\n";
            $canonical .= '    <a id="edit-cancel-control" class="btn" href="'.APP_ROOT_PATH.'/app_code/metadata_term_set.php?action=view&metadata_term_set_id='.$mdts->metadata_term_set_id.'"><i class="icon-remove"></i> '.util_lang('cancel','properize').'</a>'."\n";
            $canonical .= '    <a id="edit-delete-metadata-term-set-control" class="btn btn-danger" href="'.APP_ROOT_PATH.'/app_code/metadata_term_set.php?action=delete&metadata_term_set_id='.$mdts->metadata_term_set_id.'"><i class="icon-trash icon-white"></i> '.util_lang('delete','properize').'</a>'."\n";
            $canonical .= '  </div>'."\n";

            $canonical .= '  <div class="edit-metadata-term-set" '.$mdts->fieldsAsDataAttribs().'>'."\n";
            $canonical .= '    <div class="edit-metadata-term-set-header">';
            $canonical .= '<a href="'.APP_ROOT_PATH.'/app_code/metadata_term_set.php?action=list">'.util_lang('all_metadata_term_sets').'</a> &gt;';
            $canonical .= '<h3><input class="object-name-control" id="mdts-name" name="name" type="text" value="'.htmlentities($mdts->name).'"/></h3>';
            $canonical .= '</div>';
            $canonical .= '    <div class="description-controls"><input title="'.util_lang('title_description').'" class="description-control" type="text" name="description" value="'.htmlentities($mdts->description).'"/></div>'."\n";
            $canonical .= $mdts->renderAsEdit_references();
            $canonical .= $mdts->renderAsEdit_term_values();
            $canonical .= $mdts->renderAsHtml_structures();
            $canonical .= '  </div>'."\n";
            $canonical .= '</form>'."\n";

            $rendered = $mdts->renderAsEdit();

//            echo "<pre>\n".htmlentities($canonical)."\n-------\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }


        function testRenderAsEditEmbed_NEW() {
            $this->todo();
        }

}