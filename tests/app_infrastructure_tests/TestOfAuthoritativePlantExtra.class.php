<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfAuthoritativePlantExtra extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testAuthoritativePlantExtraAtributesExist() {
			$this->assertEqual(count(Authoritative_Plant_Extra::$fields), 9);

            $this->assertTrue(in_array('authoritative_plant_extra_id', Authoritative_Plant_Extra::$fields));
            $this->assertTrue(in_array('created_at', Authoritative_Plant_Extra::$fields));
            $this->assertTrue(in_array('updated_at', Authoritative_Plant_Extra::$fields));
            $this->assertTrue(in_array('authoritative_plant_id', Authoritative_Plant_Extra::$fields));
            $this->assertTrue(in_array('type', Authoritative_Plant_Extra::$fields));
            $this->assertTrue(in_array('value', Authoritative_Plant_Extra::$fields));
            $this->assertTrue(in_array('ordering', Authoritative_Plant_Extra::$fields));
            $this->assertTrue(in_array('flag_active', Authoritative_Plant_Extra::$fields));
            $this->assertTrue(in_array('flag_delete', Authoritative_Plant_Extra::$fields));
		}

		//// static methods

		function testCmp() {
            $pe1 = Authoritative_Plant_Extra::getOneFromDb(['authoritative_plant_extra_id'=>5101],$this->DB);
            $pe2 = Authoritative_Plant_Extra::getOneFromDb(['authoritative_plant_extra_id'=>5102],$this->DB);

			$this->assertEqual(Action::cmp($pe1, $pe2), -1);
			$this->assertEqual(Action::cmp($pe1, $pe1), 0);
			$this->assertEqual(Action::cmp($pe2, $pe1), 1);

            $pes = Authoritative_Plant_Extra::getAllFromDb([],$this->DB);

            usort($pes,'Authoritative_Plant_Extra::cmp');

//            util_prePrintR($pes[0]->authoritative_plant_extra_id);
//            util_prePrintR($pes[1]->authoritative_plant_extra_id);
//            util_prePrintR($pes[2]->authoritative_plant_extra_id);
//            util_prePrintR($pes[3]->authoritative_plant_extra_id);
//            util_prePrintR($pes[4]->authoritative_plant_extra_id);
//            util_prePrintR($pes[5]->authoritative_plant_extra_id);
//            util_prePrintR($pes[6]->authoritative_plant_extra_id);
//            util_prePrintR($pes[7]->authoritative_plant_extra_id);
//            util_prePrintR($pes[8]->authoritative_plant_extra_id);

            $this->assertEqual(5103,$pes[0]->authoritative_plant_extra_id);
            $this->assertEqual(5101,$pes[1]->authoritative_plant_extra_id);
            $this->assertEqual(5102,$pes[2]->authoritative_plant_extra_id);
            $this->assertEqual(5104,$pes[3]->authoritative_plant_extra_id);
            $this->assertEqual(5106,$pes[4]->authoritative_plant_extra_id);
            $this->assertEqual(5105,$pes[5]->authoritative_plant_extra_id);
            $this->assertEqual(5107,$pes[6]->authoritative_plant_extra_id);
            $this->assertEqual(5108,$pes[7]->authoritative_plant_extra_id);
            $this->assertEqual(5109,$pes[8]->authoritative_plant_extra_id);
        }

        function testCreateNewAuthoritativePlantExtra() {
            $n = Authoritative_Plant_Extra::createNewAuthoritativePlantExtraFor('common name',5001,$this->DB);

            $this->assertEqual('NEW',$n->authoritative_plant_extra_id);
            $this->assertNotEqual('',$n->created_at);
            $this->assertNotEqual('',$n->updated_at);
            $this->assertEqual(5001, $n->authoritative_plant_id);
            $this->assertEqual('common name', $n->type);
            $this->assertEqual('', $n->value);
            $this->assertEqual(0, $n->ordering);
            $this->assertEqual(false,$n->flag_active);
            $this->assertEqual(false,$n->flag_delete);
        }

        //// instance methods - object itself


        function testRenderFormForNew_COMMON_NAME() {
            $this->todo();
        }

        function testRenderFormForNew_IMAGE() {
            $this->todo();
        }

        function testRenderFormForNew_DESCRIPTION() {
            $this->todo();
        }

        //// instance methods - related data

		function testGetAuthoritativePlant() {
            $pe1 = Authoritative_Plant_Extra::getOneFromDb(['authoritative_plant_extra_id'=>5101],$this->DB);

            $ap = $pe1->getAuthoritativePlant();

            $this->assertEqual(5001,$ap->authoritative_plant_id);
        }

        //// instance methods - object itself

        function testRenderAsHtml_common_name() {
            $pe = Authoritative_Plant_Extra::getOneFromDb(['authoritative_plant_extra_id'=>5101],$this->DB);

            $canonical = "<div class=\"field-label\">common name : </div><div class=\"field-value taxonomy taxonomy-common-name\">\"AP_A common z chestnut\"</div>";
            $rendered = $pe->renderAsHtml();

            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsHtml_local_image() {
            $pe = Authoritative_Plant_Extra::getOneFromDb(['authoritative_plant_extra_id'=>5105],$this->DB);

            $canonical = '<img class="plant-image" src="'.APP_ROOT_PATH.'/image_data/authoritative/testing/castanea_dentata.jpg"/>';
            $rendered = $pe->renderAsHtml();

            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsHtml_external_image() {
            $pe = Authoritative_Plant_Extra::getOneFromDb(['authoritative_plant_extra_id'=>5106],$this->DB);

            $canonical = '<img class="plant-image external-reference" src="https://farm6.staticflickr.com/5556/14761853313_17d5a31479_z.jpg"/>';
            $rendered = $pe->renderAsHtml();

            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsHtml_description() {
            $pe = Authoritative_Plant_Extra::getOneFromDb(['authoritative_plant_extra_id'=>5104],$this->DB);

            $canonical = '<div class="plant-description">description of american chestnut</div>';
            $rendered = $pe->renderAsHtml();

            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsListItem() {
            $pe = Authoritative_Plant_Extra::getOneFromDb(['authoritative_plant_extra_id'=>5101],$this->DB);

            $canonical = '<li id="authoritative_plant_extra_5101" class="authoritative-plant-extra" data-authoritative_plant_extra_id="5101"><div class="field-label">common name : </div><div class="field-value taxonomy taxonomy-common-name">"AP_A common z chestnut"</div></li>';
            $rendered = $pe->renderAsListItem();

            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsListItemEdit_COMMON_NAME() {
            $pe = Authoritative_Plant_Extra::getOneFromDb(['authoritative_plant_extra_id'=>5101],$this->DB);
            $canonical = '';

            $canonical .= '<li id="authoritative_plant_extra_5101" class="authoritative-plant-extra-edit authoritative-plant-extra" data-authoritative_plant_extra_id="5101" data-created_at="'.$pe->created_at.'" data-updated_at="'.$pe->updated_at.'" data-authoritative_plant_id="5001" data-type="common name" data-value="AP_A common z chestnut" data-ordering="1.00000" data-flag_active="1" data-flag_delete="0">'."\n";
            $canonical .= '  '.util_orderingUpDownControls('authoritative_plant_extra_5101')."\n";
            $canonical .= '  <div class="authoritative-plant-extra embedded">'."\n";
            $canonical .= '    <div id="form-edit-authoritative-plant-extra-5101" class="form-edit-authoritative-plant-extra" data-authoritative_plant_extra_id="5101">'."\n";
            $canonical .= '      <div class="field-label">common name : </div><div class="field-value"><input type="text" name="authoritative_plant_extra-common_name_5101" id="authoritative_plant_extra-common_name_5101" value="AP_A common z chestnut"/></div>'."\n";
            $canonical .= '    </div>'."\n";
            $canonical .= '    <button class="btn btn-danger button-mark-authoritative-plant-extra-for-delete" title="Mark this for removal - the actual removal occurs on update" data-do-mark-title="Mark this for removal - the actual removal occurs on update" data-remove-mark-title="Undo the mark for removal" data-for_dom_id="authoritative_plant_extra_5101" data-authoritative_plant_extra_id="5101"><i class="icon-remove-sign icon-white"></i></button>'."\n";
            $canonical .= '  </div>'."\n";
            $canonical .= '</li>';

            $rendered = $pe->renderAsListItemEdit();

//            echo "<pre>\n".htmlentities($canonical)."\n------------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsListItemEdit_IMAGE() {
            $pe = Authoritative_Plant_Extra::getOneFromDb(['authoritative_plant_extra_id'=>5105],$this->DB);
            $canonical = '';

            $canonical .= '<li id="authoritative_plant_extra_5105" class="authoritative-plant-extra-edit authoritative-plant-extra" data-authoritative_plant_extra_id="5105" data-created_at="'.$pe->created_at.'" data-updated_at="'.$pe->updated_at.'" data-authoritative_plant_id="5001" data-type="image" data-value="testing/castanea_dentata.jpg" data-ordering="2.00000" data-flag_active="1" data-flag_delete="0">'."\n";
            $canonical .= '  '.util_orderingUpDownControls('authoritative_plant_extra_5105')."\n";
            $canonical .= '  <div class="authoritative-plant-extra embedded">'."\n";
            $canonical .= '    <div id="form-edit-authoritative-plant-extra-5105" class="form-edit-authoritative-plant-extra" data-authoritative_plant_extra_id="5105">'."\n";
            $canonical .= '      <img class="plant-image" src="/digitalfieldnotebooks/image_data/authoritative/testing/castanea_dentata.jpg"/>'."\n";
            $canonical .= '    </div>'."\n";
            $canonical .= '    <button class="btn btn-danger button-mark-authoritative-plant-extra-for-delete" title="Mark this for removal - the actual removal occurs on update" data-do-mark-title="Mark this for removal - the actual removal occurs on update" data-remove-mark-title="Undo the mark for removal" data-for_dom_id="authoritative_plant_extra_5105" data-authoritative_plant_extra_id="5105"><i class="icon-remove-sign icon-white"></i></button>'."\n";
            $canonical .= '  </div>'."\n";
            $canonical .= '</li>';

            $rendered = $pe->renderAsListItemEdit();

//            echo "<pre>\n".htmlentities($canonical)."\n------------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsListItemEdit_DESCRIPTION() {
            $pe = Authoritative_Plant_Extra::getOneFromDb(['authoritative_plant_extra_id'=>5104],$this->DB);
            $canonical = '';

            $canonical .= '<li id="authoritative_plant_extra_5104" class="authoritative-plant-extra-edit authoritative-plant-extra" data-authoritative_plant_extra_id="5104" data-created_at="'.$pe->created_at.'" data-updated_at="'.$pe->updated_at.'" data-authoritative_plant_id="5001" data-type="description" data-value="description of american chestnut" data-ordering="1.00000" data-flag_active="1" data-flag_delete="0">'."\n";
            $canonical .= '  '.util_orderingUpDownControls('authoritative_plant_extra_5104')."\n";
            $canonical .= '  <div class="authoritative-plant-extra embedded">'."\n";
            $canonical .= '    <div id="form-edit-authoritative-plant-extra-5104" class="form-edit-authoritative-plant-extra" data-authoritative_plant_extra_id="5104">'."\n";
            $canonical .= '      <div class="field-label">description : </div><div class="field-value"><input type="text" name="authoritative_plant_extra-description_5104" id="authoritative_plant_extra-description_5104" value="description of american chestnut"/></div>'."\n";
            $canonical .= '    </div>'."\n";
            $canonical .= '    <button class="btn btn-danger button-mark-authoritative-plant-extra-for-delete" title="Mark this for removal - the actual removal occurs on update" data-do-mark-title="Mark this for removal - the actual removal occurs on update" data-remove-mark-title="Undo the mark for removal" data-for_dom_id="authoritative_plant_extra_5104" data-authoritative_plant_extra_id="5104"><i class="icon-remove-sign icon-white"></i></button>'."\n";
            $canonical .= '  </div>'."\n";
            $canonical .= '</li>';

            $rendered = $pe->renderAsListItemEdit();

//            echo "<pre>\n".htmlentities($canonical)."\n------------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
        }

        function testToDo() {
//            $this->todo('render as list item edit');
//            $this->todo('factory to create new');
        }
    }