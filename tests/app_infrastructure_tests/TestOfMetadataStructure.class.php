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
			$this->assertEqual(count(Metadata_Structure::$fields), 11);

            $this->assertTrue(in_array('metadata_structure_id', Metadata_Structure::$fields));
            $this->assertTrue(in_array('created_at', Metadata_Structure::$fields));
            $this->assertTrue(in_array('updated_at', Metadata_Structure::$fields));
            $this->assertTrue(in_array('parent_metadata_structure_id', Metadata_Structure::$fields));
            $this->assertTrue(in_array('name', Metadata_Structure::$fields));
            $this->assertTrue(in_array('ordering', Metadata_Structure::$fields));
            $this->assertTrue(in_array('description', Metadata_Structure::$fields));
            $this->assertTrue(in_array('details', Metadata_Structure::$fields));
            $this->assertTrue(in_array('metadata_term_set_id', Metadata_Structure::$fields));
            $this->assertTrue(in_array('flag_active', Metadata_Structure::$fields));
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

            // alternate initial item text
            $canonical = '<select name="ABC123_metadata_structure_id" id="ABC123_metadata_structure_id" class="metadata_structure_selector">'."\n";
            $canonical .= '<option value="-1">abc123blarg</option>'."\n";
            $canonical .= '<option value="6004" title="info about the individual leaves of the plant" data-details="details">leaf</option>'."\n";
            $canonical .= '<option value="6001" title="info about the flower" data-details="">flower</option>'."\n";
            $canonical .= '<option value="6002" title="the size of the flower in its largest dimension" data-details="some details" selected="selected">flower - flower size</option>'."\n";
            $canonical .= '<option value="6003" title="the primary / dominant color of the flower" data-details="">flower - flower primary color</option>'."\n";
            $canonical .= '</select>';

            $rendered = Metadata_Structure::renderControlSelectAllMetadataStructures('ABC123_metadata_structure_id',6002,'abc123blarg');

//            echo "<pre>\n".htmlentities($canonical)."\n---------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }

        function testCreateNewMetadataStructure() {
            $this->todo();
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

        function testRenderAsLink_NO_TERM_SET() {
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);
            $mds->loadTermSetAndValues();

            $canonical = '<a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=view&metadata_structure_id='.$mds->metadata_structure_id.'">'.$mds->renderAsFullName().' <i class="icon-ok-circle"></i></a>';
            $rendered = $mds->renderAsLink('view');

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsLink_WITH_TERM_SET() {
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6003],$this->DB);
            $mds->loadTermSetAndValues();

            $canonical = '<a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=view&metadata_structure_id='.$mds->metadata_structure_id.'">'.$mds->renderAsFullName().' ('.htmlentities($mds->term_set->name).') <i class="icon-ok-circle"></i></a>';
            $rendered = $mds->renderAsLink('view');

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsButtonEdit() {
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);

            $canonical = '<a id="metadata_structure-btn-edit-6001" href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=edit&metadata_structure_id=6001" title="'.util_lang('edit').'" class="edit_link btn" >'.util_lang('edit').'</a>';
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

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }


        function testRenderAsListItem() {
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);

            $canonical = '<li data-metadata_structure_id="6001" data-created_at="'.$mds->created_at.'" data-updated_at="'.$mds->updated_at.'" data-parent_metadata_structure_id="0" data-name="flower" data-ordering="1.00000" data-description="info about the flower" data-details="" data-metadata_term_set_id="0" data-flag_active="1" data-flag_delete="0"><a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=view&metadata_structure_id=6001">flower <i class="icon-ok-circle"></i></a></li>';

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
            $canonical .= '</li>'."\n";

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
  <div class="metadata-structure-header">'."\n";
            $canonical .= '    <h3>flower</h3>'."\n";
            $canonical .= '    <div class="active_state_info"><i class="icon-ok-circle"></i> '.util_lang('active_true').'</div>'."\n";
            $canonical .= '    <div class="description">info about the flower</div>'."\n";
            $canonical .= Metadata_Reference::renderReferencesArrayAsListsView($mds->references);
            $canonical .= '  </div>'."\n";

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
  <div class="metadata-structure-header">
    <h3>flower size</h3>'."\n";
            $canonical .= '    <div class="active_state_info"><i class="icon-ok-circle"></i> '.util_lang('active_true').'</div>'."\n";
            $canonical .= '    <div class="description">the size of the flower in its largest dimension</div>'."\n";
            $canonical .= '    <div class="details">some details</div>'."\n";
            $canonical .= Metadata_Reference::renderReferencesArrayAsListsView($mds->references);
//            $canonical .= '<ul class="metadata-references">';
//            foreach ($mds->references as $r) {
//                $canonical .= '<li>'.$r->renderAsViewEmbed().'</li>';
//            }
//            $canonical .= '</ul>'."\n";

            $canonical .= '  </div>'."\n";
            $canonical .= '  '.$mds->term_set->renderAsViewEmbed();
            $canonical .= '</div>';

            $rendered = $mds->renderAsView();

//            echo "<pre>\n".htmlentities($canonical)."\n---------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }


//if ($USER->canActOnTarget($ACTIONS['create'],$all_metadata_structures[0])) {
//echo '<li><a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=create&parent_metadata_structure_id=0" id="btn-add-metadata_structure-ROOT" class="creation_link btn" title="'.htmlentities(util_lang('add_metadata_structure')).'">'.htmlentities(util_lang('add_metadata_structure')).'</a></li>'."\n";
//}

        function testRenderAsEdit() {
            $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6001],$this->DB);
            $mds->loadTermSetAndValues();
            $mds->loadReferences();


            // name, description, details, term set ('none' is OK) - fields present
            // add/remove child structures - add button present, remove buttons present
            // re-order child structures - ordering handles and data fields present
            // ??? references ?
            $canonical = '';
            $canonical .= '<form id="form-edit-metadata-structure-base-data" action="/digitalfieldnotebooks/app_code/metadata_structure.php">'."\n";
            $canonical .= '  <input type="hidden" name="action" value="update"/>'."\n";
            $canonical .= '  <input type="hidden" id="metadata_structure_id" name="metadata_structure_id" value="'.$mds->metadata_structure_id.'"/>'."\n";

            $canonical .= '  <div id="actions"><button id="edit-submit-control" class="btn btn-success" type="submit" name="edit-submit-control" value="update"><i class="icon-ok-sign icon-white"></i> Update</button>'."\n";
            $canonical .= '  <a id="edit-cancel-control" class="btn" href="/digitalfieldnotebooks/app_code/metadata_structure.php?action=view&metadata_structure_id=6001"><i class="icon-remove"></i> Cancel</a>  <a id="edit-delete-metadata-structure-control" class="btn btn-danger" href="/digitalfieldnotebooks/app_code/metadata_structure.php?action=delete&metadata_structure_id=6001"><i class="icon-trash icon-white"></i> Delete</a>  </div>'."\n";

            $canonical .= '<div id="edit-rendered_metadata_structure_6001" class="edit-rendered_metadata_structure" '.$mds->fieldsAsDataAttribs().'>'."\n";
            $canonical .= '  <div class="metadata_lineage"><a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=list">metadata</a> &gt;</div>'."\n";

            $canonical .= '  <div class="metadata-parent-controls">'.util_lang('label_metadata_structure_change_parent').': '.Metadata_Structure::renderControlSelectAllMetadataStructures('parent_metadata_structure_id',$mds->parent_metadata_structure_id,util_lang('metadata_root_level')).'</div>'."\n";

            $canonical .= '  <div class="metadata-structure-header">'."\n";
            $canonical .= '    <h3><input id="" class="object-name-control" type="text" name="name" value="flower"/></h3>'."\n";
            $canonical .= '    <div class="active-state-controls"><input type="checkbox" name="flag_active" value="1" checked="checked"/> '.util_lang('active').'</div>'."\n";
            $canonical .= '    <div class="description-controls"><input title="brief description/summary" class="description-control" type="text" name="description" value="info about the flower"/></div>'."\n";
            $canonical .= '    <div class="details-controls"><textarea title="additional information/details - no size limit" class="details-control" name="details"></textarea></div>'."\n";
            $canonical .= '    <h4>references</h4>'."\n";
            $canonical .= Metadata_Reference::renderReferencesArrayAsListsEdit($mds->references);
            $canonical .= '  </div>'."\n";

            $canonical .= '  <div class="metadata-term-set-controls"><h4>'.util_lang('metadata_term_set')."</h4>\n".Metadata_Term_Set::renderAllAsSelectControl('',$mds->term_set ? $mds->term_set->metadata_term_set_id : 0)."</div>\n";


            $canonical .= '  <h4>further breakdown:</h4>'."\n";
            $canonical .= '  <ul class="metadata-structure-tree">'."\n";
            $canonical .= '    <li><a href="'.APP_ROOT_PATH.'/app_code/metadata_structure.php?action=create&parent_metadata_structure_id='.$mds->metadata_structure_id.'" id="btn-add-metadata-structure" title="'.htmlentities(util_lang('add_metadata_structure')).'" class="creation_link btn">'.htmlentities(util_lang('add_metadata_structure')).'</a></li>'."\n";
            $children = $mds->getChildren();
            foreach ($children as $mds_child) {
                $canonical .= '    '.$mds_child->renderAsListTreeEditable();
            }
            $canonical .= '  </ul>';
            $canonical .= '</div>';


            $rendered = $mds->renderAsEdit();


//            echo "<pre>\n".htmlentities($canonical)."\n---------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
            $this->assertEqual($canonical,$rendered);
        }
}