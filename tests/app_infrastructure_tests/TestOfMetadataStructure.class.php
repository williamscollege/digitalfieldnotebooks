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


        function testRenderControlSelectAllMetadataStructures() {
            global $DB;
            $DB = $this->DB;

            // no default selected
            $canonical = '<select name="ABC123_metadata_structure_id" id="ABC123_metadata_structure_id" class="metadata_structure_selector">'."\n";
            $canonical .= '<option value="-1">'.util_lang('prompt_select').'</option>'."\n";
            $canonical .= '<option value="6004" title="info about the individual leaves of the plant" data-details="details">leaf</option>'."\n";
            $canonical .= '<option value="6001" title="info about the flower" data-details="">flower</option>'."\n";
            $canonical .= '<option value="6002" title="the size of the flower in its largest dimension" data-details="some details">flower - flower size</option>'."\n";
            $canonical .= '<option value="6003" title="the primary / dominant color of the flower" data-details="">flower - flower primary color</option>'."\n";
            $canonical .= '</select>';

            $rendered = Metadata_Structure::renderControlSelectAllMetadataStructures('ABC123_metadata_structure_id');

//            echo "<pre>\n".htmlentities($canonical)."\n---------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);


            // default selected
            $canonical = '<select name="ABC123_metadata_structure_id" id="ABC123_metadata_structure_id" class="metadata_structure_selector">'."\n";
            $canonical .= '<option value="-1">'.util_lang('prompt_select').'</option>'."\n";
            $canonical .= '<option value="6004" title="info about the individual leaves of the plant" data-details="details">leaf</option>'."\n";
            $canonical .= '<option value="6001" title="info about the flower" data-details="">flower</option>'."\n";
            $canonical .= '<option value="6002" title="the size of the flower in its largest dimension" data-details="some details" selected="selected">flower - flower size</option>'."\n";
            $canonical .= '<option value="6003" title="the primary / dominant color of the flower" data-details="">flower - flower primary color</option>'."\n";
            $canonical .= '</select>';

            $rendered = Metadata_Structure::renderControlSelectAllMetadataStructures('ABC123_metadata_structure_id',6002);

//            echo "<pre>\n".htmlentities($canonical)."\n---------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);

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

        function testRenderAsFullName() {
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);

            $canonical = 'flower - flower size';
            $rendered = $mds->renderAsFullName();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsLink() {
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);

            $canonical = '<a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=view&metadata_structure_id='.$mds->metadata_structure_id.'">'.htmlentities($mds->name).'</a>';
            $rendered = $mds->renderAsLink('view');

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsButtonEdit() {
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);

            $canonical = '<a id="btn-edit" href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=edit&metadata_structure_id=6001" class="edit_link btn" >'.util_lang('edit').'</a>';
            $rendered = $mds->renderAsButtonEdit();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsOption() {
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);
            $display_prefix = '-- ';
            $canonical = '<option value="'.$mds->metadata_structure_id.'" title="'.htmlentities($mds->description).'" data-details="'.htmlentities($mds->details).'">'.$display_prefix.htmlentities($mds->name).'</option>';
            $rendered = $mds->renderAsOption($display_prefix);

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
        }


//        function testRenderAsHtml() {
//            $this->todo();
//            return;
//
//            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);
//
//            global $USER;
//            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);
//
//            $canonical = '';
//
//            $rendered = $mds->renderAsHtml();
//
////            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
//
//            $this->assertEqual($canonical,$rendered);
//            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
//        }

        function testRenderAsListItem() {
//            $this->todo();
//            return;

            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);

            $canonical = '<li data-metadata_structure_id="6001" data-created_at="'.$mds->created_at.'" data-updated_at="'.$mds->updated_at.'" data-parent_metadata_structure_id="0" data-name="flower" data-ordering="1.00000" data-description="info about the flower" data-details="" data-metadata_term_set_id="0" data-flag_delete="0"><a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=view&metadata_structure_id=6001">flower</a></li>';

            $rendered = $mds->renderAsListItem();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsListTree_leaf() {
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);

            $canonical = $mds->renderAsListItem();

            $rendered = $mds->renderAsListTree();

//            echo "<pre>\n".htmlentities($canonical)."\n---------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsListTree_branch() {
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);
            $mds_c1 = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);
            $mds_c2 = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6003],$this->DB);

            $canonical = $mds->renderAsListItem_Lead();
            $canonical .= $mds->renderAsLink();
            $canonical .= '<ul class="metadata-structure-tree">'."\n";
            $canonical .= $mds_c1->renderAsListItem();
            $canonical .= $mds_c2->renderAsListItem();
            $canonical .= '</ul>';
            $canonical .= '</li>';

            $rendered = $mds->renderAsListTree();

//            echo "<pre>\n".htmlentities($canonical)."\n---------\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsOptionTree() {
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);

            $canonical = '<option value="6001" title="info about the flower" data-details="">flower</option>'."\n";
            $canonical .= '<option value="6002" title="the size of the flower in its largest dimension" data-details="some details">flower - flower size</option>'."\n";
            $canonical .= '<option value="6003" title="the primary / dominant color of the flower" data-details="">flower - flower primary color</option>'."\n";

            $rendered = $mds->renderAsOptionTree();

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);

//            echo "<pre>\n".htmlentities($canonical)."\n---------\n".htmlentities($rendered)."\n</pre>";
        }

        function testRenderAsView_parent() {
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);
            $mds->loadTermSetAndValues();
            $mds->loadReferences();

            /*
<ul class="metadata-references"><li><div id="rendered_metadata_reference_6301" class="rendered_metadata_reference rendered_metadata_reference_text"><div class="text_data" title="description of what a flower is">This is a flower.</div></div></li></ul></div>
<h4>further breakdown:</h4>
<ul class="metadata-structure-tree">
<li data-metadata_structure_id="6002" data-created_at="2014-10-28 13:42:54" data-updated_at="2014-10-28 13:42:54" data-parent_metadata_structure_id="6001" data-name="flower size" data-ordering="0.50000" data-description="the size of the flower in its largest dimension" data-details="some details" data-metadata_term_set_id="6101" data-flag_delete="0"><a href="/digitalfieldnotebooks/app_code/metadata_structure.php?action=view&metadata_structure_id=6002">flower - flower size</a></li><li data-metadata_structure_id="6003" data-created_at="2014-10-28 13:42:54" data-updated_at="2014-10-28 13:42:54" data-parent_metadata_structure_id="6001" data-name="flower primary color" data-ordering="0.75000" data-description="the primary / dominant color of the flower" data-details="" data-metadata_term_set_id="6102" data-flag_delete="0"><a href="/digitalfieldnotebooks/app_code/metadata_structure.php?action=view&metadata_structure_id=6003">flower - flower primary color</a></li></ul></div>
             */

            $canonical = '<div id="rendered_metadata_structure_6001" class="view-rendered_metadata_structure" '.$mds->fieldsAsDataAttribs().'>
  <div class="metadata_lineage"><a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=list">metadata</a> &gt;</div>
  <div class="metadata-structure-header"><h3>flower</h3>';

            $canonical .= '  <div class="description">info about the flower</div>'."\n";
            $canonical .= '<ul class="metadata-references">';
            foreach ($mds->references as $r) {
                $canonical .= '<li>'.$r->renderAsViewEmbed().'</li>';
            }
            $canonical .= '</ul></div>'."\n";

            $canonical .= '<h4>further breakdown:</h4>'."\n";
            $canonical .= '<ul class="metadata-structure-tree">'."\n";
            $children = $mds->getChildren();

//            util_prePrintR($children);

            foreach ($children as $mds_child) {
                $canonical .= $mds_child->renderAsListTree();
            }
            $canonical .= '</ul>';

            $canonical .= '</div>';

            $rendered = $mds->renderAsView();

//            echo "<pre>\n".htmlentities($canonical)."\n---------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsView_child() {
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);
            $mds->loadTermSetAndValues();
            $mds->loadReferences();

            $mds_parent = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);

            $canonical =
   '<div id="rendered_metadata_structure_6002" class="view-rendered_metadata_structure" '.$mds->fieldsAsDataAttribs().'>
  <div class="metadata_lineage"><a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=list">metadata</a> &gt; '.$mds_parent->renderAsLink().' &gt;</div>
  <div class="metadata-structure-header"><h3>flower size</h3>';
            $canonical .= '  <div class="description">the size of the flower in its largest dimension</div>'."\n";
            $canonical .= '<ul class="metadata-references">';
            foreach ($mds->references as $r) {
                $canonical .= '<li>'.$r->renderAsViewEmbed().'</li>';
            }
            $canonical .= '</ul></div>
  <div class="details">some details</div>
  ';
            $canonical .= $mds->term_set->renderAsViewEmbed();
            $canonical .= '</div>';

            $rendered = $mds->renderAsView();

//            echo "<pre>\n".htmlentities($canonical)."\n---------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }

    }