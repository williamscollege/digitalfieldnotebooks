<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfAuthoritativePlant extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testAuthoritativePlantAtributesExist() {
			$this->assertEqual(count(Authoritative_Plant::$fields), 11);

            $this->assertTrue(in_array('authoritative_plant_id', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('created_at', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('updated_at', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('class', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('order', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('family', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('genus', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('species', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('variety', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('catalog_identifier', Authoritative_Plant::$fields));
            $this->assertTrue(in_array('flag_delete', Action::$fields));
		}

		//// static methods

		function testCmp() {
            $p1 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);
            $p2 = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5002],$this->DB);

            $this->assertEqual(Authoritative_Plant::cmp($p1, $p2), -1);
            $this->assertEqual(Authoritative_Plant::cmp($p1, $p1), 0);
            $this->assertEqual(Authoritative_Plant::cmp($p2, $p1), 1);

            $ps = Authoritative_Plant::getAllFromDb([],$this->DB);

            usort($ps,'Authoritative_Plant::cmp');

            $this->assertEqual('AP_1_CI',$ps[0]->catalog_identifier);
            $this->assertEqual('AP_2_CI',$ps[1]->catalog_identifier);
            $this->assertEqual('AP_3_CI',$ps[2]->catalog_identifier);
            $this->assertEqual('AP_4_CI',$ps[3]->catalog_identifier);
            $this->assertEqual('AP_5_CI',$ps[4]->catalog_identifier);
            $this->assertEqual('AP_6_CI',$ps[5]->catalog_identifier);
            $this->assertEqual('AP_7_CI',$ps[6]->catalog_identifier);
            $this->assertEqual('AP_8_CI',$ps[7]->catalog_identifier);
        }

        function testRenderControlSelectAllAuthoritativePlants() {
            $aps = Authoritative_Plant::getAllFromDb(['flag_delete'=>false],$this->DB);
            usort($aps,'Authoritative_Plant::cmp');

            $canonical_base = '<select name="authoritative_plant_id" id="authoritative-plant-id">'."\n";

            global $DB;
            $DB = $this->DB;

            //-------
            // no default select

            $canonical = $canonical_base;
            foreach ($aps as $ap) {
                $canonical .= '  '.$ap->renderAsOption()."\n";
            }
            $canonical .= '</select>';
            $rendered = Authoritative_Plant::renderControlSelectAllAuthoritativePlants();

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);

            //-------
            // default select using an id number

            $canonical = $canonical_base;
            foreach ($aps as $ap) {
                $canonical .= '  '.$ap->renderAsOption($ap->authoritative_plant_id == 5005)."\n";
            }
            $canonical .= '</select>';
            $rendered = Authoritative_Plant::renderControlSelectAllAuthoritativePlants(5005);

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);

            //-------------
            // default select using an object

            $default_ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5003],$this->DB);
            $canonical = $canonical_base;
            foreach ($aps as $ap) {
                $canonical .= '  '.$ap->renderAsOption($ap->authoritative_plant_id == $default_ap->authoritative_plant_id)."\n";
            }
            $canonical .= '</select>';
            $rendered = Authoritative_Plant::renderControlSelectAllAuthoritativePlants($default_ap);

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        //// instance methods - object itself

        function testRenderAsShortText() {
            $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);

            $canonical = "Ap_a_genus ap_a_species 'AP_A_variety' (\"AP_A common y achestnut\") [AP_1_CI]";
            $rendered = $ap->renderAsShortText();

            $this->assertEqual($canonical,$rendered);
        }


        function testRenderAsLink() {
            $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);

            $canonical = '<a href="'.APP_ROOT_PATH.'/app_code/authoritative_plant.php?action=view&authoritative_plant_id=5001">'.htmlentities("Ap_a_genus ap_a_species 'AP_A_variety' (\"AP_A common y achestnut\") [AP_1_CI]").'</a>';
            $rendered = $ap->renderAsLink();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsListItem_General() {
            $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id' => 5001], $this->DB);

            global $USER;

            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            # 'authoritative_plant_id', 'created_at', 'updated_at', 'class', 'order', 'family', 'genus', 'species', 'variety', 'catalog_identifier', 'flag_delete'
   //         $addTestSql  = "INSERT INTO " . Authoritative_Plant::$dbTable . " VALUES
     //       (5001,NOW(),NOW(), 'AP_A_class', 'AP_A_order', 'AP_A_family', 'AP_A_genus', 'AP_A_species', 'AP_A_variety', 'AP_1_CI', 0),

            $canonical = '<li data-authoritative_plant_id="5001" data-created_at="'.$ap->created_at.'" data-updated_at="'.$ap->updated_at.'" '.
                'data-class="AP_A_class" data-order="AP_A_order" data-family="AP_A_family" data-genus="AP_A_genus" data-species="AP_A_species" data-variety="AP_A_variety" data-catalog_identifier="AP_1_CI" data-flag_delete="0"><a href="/digitalfieldnotebooks/app_code/authoritative_plant.php?action=view&authoritative_plant_id=5001">'.htmlentities($ap->renderAsShortText()).'</a></li>';

            $rendered = $ap->renderAsListItem();
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);

            unset($USER);
        }

        function testRenderAsListItem_Editable() {
            $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id' => 5001], $this->DB);

            global $USER;

            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            //$this->todo('make user able to edit the authoritative plant');

            $rat = new Role_Action_Target(['role_action_target_link_id'=>500,'last_user_id'=>0,'role_id'=>3,'action_id'=>2,'target_type'=>'global_plant','target_id'=>0,'DB'=>$this->DB]);
            $rat->updateDb();
            $this->assertTrue($rat->matchesDb);

            $canonical = '<li data-authoritative_plant_id="5001" data-created_at="'.$ap->created_at.'" data-updated_at="'.$ap->updated_at.'" '.
                'data-class="AP_A_class" data-order="AP_A_order" data-family="AP_A_family" data-genus="AP_A_genus" data-species="AP_A_species" data-variety="AP_A_variety" data-catalog_identifier="AP_1_CI" data-flag_delete="0" data-can-edit="1"><a href="/digitalfieldnotebooks/app_code/authoritative_plant.php?action=view&authoritative_plant_id=5001">'.htmlentities($ap->renderAsShortText()).'</a></li>';
            $rendered = $ap->renderAsListItem();
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);

            $rat->doDelete();
            unset($USER);
        }

        function testRenderAsViewEmbed() {
            $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id' => 5001], $this->DB);

            $ap->cacheExtras();
            $ap->cacheSpecimens();

            $canonical = '<div id="authoritative_plant_embed_5001" class="authoritative-plant embedded" data-authoritative_plant_id="5001">
  <h3>'.$ap->renderAsLink().'</h3>
  <div class="canonical_image"><img class="plant-image external-reference" src="https://farm6.staticflickr.com/5556/14761853313_17d5a31479_z.jpg"/></div>
  <ul class="base-info">
    <li><div class="field-label">'.util_lang('class').' : </div><div class="field-value taxonomy taxonomy-class">'.htmlentities($ap->class).'</div></li>
    <li><div class="field-label">'.util_lang('order').' : </div><div class="field-value taxonomy taxonomy-order">'.htmlentities($ap->order).'</div></li>
    <li><div class="field-label">'.util_lang('family').' : </div><div class="field-value taxonomy taxonomy-family">'.htmlentities($ap->family).'</div></li>
    <li><div class="field-label">'.util_lang('genus').' : </div><div class="field-value taxonomy taxonomy-genus">'.htmlentities($ap->genus).'</div></li>
    <li><div class="field-label">'.util_lang('species').' : </div><div class="field-value taxonomy taxonomy-species">'.htmlentities($ap->species).'</div></li>
    <li><div class="field-label">'.util_lang('variety').' : </div><div class="field-value taxonomy taxonomy-variety">\''.htmlentities($ap->variety).'\'</div></li>
    <li><div class="field-label">'.util_lang('catalog_identifier').' : </div><div class="field-value">'.htmlentities($ap->catalog_identifier).'</div></li>
  </ul><br/>
  <a class="show-hide-control" href="#" data-for_elt_id="authoritative_plant-details_5001">'.util_lang('show_hide').' '.util_lang('extra_info').'</a>
  <div class="details-info" id="authoritative_plant-details_5001">
  <ul class="extra-info" id="authoritative_plant-extra_info_5001">
';
            foreach ($ap->extras as $extra) {
                $canonical .='    '.$extra->renderAsListItem()."\n";
            }
            $canonical .= '  </ul>
  <h4>'.util_lang('specimens','properize').'</h4>
  <ul class="specimens">
';
            foreach ($ap->specimens as $specimen) {
                $canonical .= '    <li>'.$specimen->renderAsViewEmbed()."</li>\n";
            }
            $canonical .= '  </ul>
</div>
</div>';

            $rendered = $ap->renderAsViewEmbed();

//                echo "<pre>\n".htmlentities($canonical)."\n------------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsView() {
            $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id' => 5001], $this->DB);

            $ap->cacheExtras();
            $ap->cacheNotebookPages();
            $ap->cacheSpecimens();

            global $USER,$ACTIONS;

            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $canonical =
                '<div id="authoritative_plant_view_5001" class="authoritative-plant" data-authoritative_plant_id="5001">
  <h3><a href="'.APP_ROOT_PATH.'/app_code/authoritative_plant.php?action=list">'.util_lang('authoritative_plant','properize').'</a>: '.$ap->renderAsShortText().'</h3>
  <ul class="base-info">
    <li><span class="field-label">'.util_lang('class').'</span> : <span class="field-value taxonomy taxonomy-class">'.htmlentities($ap->class).'</span></li>
    <li><span class="field-label">'.util_lang('order').'</span> : <span class="field-value taxonomy taxonomy-order">'.htmlentities($ap->order).'</span></li>
    <li><span class="field-label">'.util_lang('family').'</span> : <span class="field-value taxonomy taxonomy-family">'.htmlentities($ap->family).'</span></li>
    <li><span class="field-label">'.util_lang('genus').'</span> : <span class="field-value taxonomy taxonomy-genus">'.htmlentities($ap->genus).'</span></li>
    <li><span class="field-label">'.util_lang('species').'</span> : <span class="field-value taxonomy taxonomy-species">'.htmlentities($ap->species).'</span></li>
    <li><span class="field-label">'.util_lang('variety').'</span> : <span class="field-value taxonomy taxonomy-variety">\''.htmlentities($ap->variety).'\'</span></li>
    <li><span class="field-label">'.util_lang('catalog_identifier').'</span> : <span class="field-value">'.htmlentities($ap->catalog_identifier).'</span></li>
  </ul>
  <h4>'.util_lang('details','properize').'</h4>
  <ul class="extra-info">
';
            foreach ($ap->extras as $extra) {
                $canonical .='    '.$extra->renderAsListItem()."\n";
            }
            $canonical .='  </ul>
  <h4>'.util_lang('notebook_pages','properize').'</h4>
  <ul class="notebook-pages">
';
            foreach ($ap->notebook_pages as $np) {
                if ($USER->canActOnTarget($ACTIONS['view'],$np)) {
                    $canonical .='    '.$np->renderAsListItemForNotebook()."\n";
                }
            }

            $canonical .= '  </ul>
  <h4>'.util_lang('specimens','properize').'</h4>
  <ul class="specimens">
';
            foreach ($ap->specimens as $specimen) {
                $canonical .= '    <li>'.$specimen->renderAsViewEmbed()."</li>\n";
            }

            $canonical .='  </ul>
</div>';



            $rendered = $ap->renderAsView();

//            echo "<pre>\n".htmlentities($canonical)."\n------------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
        }

        function testRenderAsOption() {
            $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id' => 5001],$this->DB);

            $canonical ='<option data-authoritative_plant_id="'.$ap->authoritative_plant_id.'" value="'.$ap->authoritative_plant_id.'">'.$ap->renderAsShortText().'</option>';

            $rendered = $ap->renderAsOption(false);

//            echo "<pre>\n".htmlentities($canonical)."\n------------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);

            //---------------------------------

            $canonical ='<option data-authoritative_plant_id="'.$ap->authoritative_plant_id.'" value="'.$ap->authoritative_plant_id.'" selected="selected">'.$ap->renderAsShortText().'</option>';

            $rendered = $ap->renderAsOption(true);

//            echo "<pre>\n".htmlentities($canonical)."\n------------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        //// instance methods - related data

        function testLoadExtras() {
            $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id' => 5001],$this->DB);
            $this->assertEqual(0,count($ap->extras));

            $ap->loadExtras();

            $this->assertEqual(6,count($ap->extras));

            $this->assertEqual(5103,$ap->extras[0]->authoritative_plant_extra_id);
            $this->assertEqual(5101,$ap->extras[1]->authoritative_plant_extra_id);
            $this->assertEqual(5102,$ap->extras[2]->authoritative_plant_extra_id);
            $this->assertEqual(5106,$ap->extras[3]->authoritative_plant_extra_id);
            $this->assertEqual(5105,$ap->extras[4]->authoritative_plant_extra_id);
            $this->assertEqual(5104,$ap->extras[5]->authoritative_plant_extra_id);
        }

        function testLoadNotebookPages() {
            $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id' => 5001],$this->DB);
            $this->assertEqual(0,count($ap->notebook_pages));

            $ap->loadNotebookPages();

            $this->assertEqual(3,count($ap->notebook_pages));
            $this->assertEqual(1101,$ap->notebook_pages[0]->notebook_page_id);
            $this->assertEqual(1103,$ap->notebook_pages[1]->notebook_page_id);
            $this->assertEqual(1104,$ap->notebook_pages[2]->notebook_page_id);
        }

        function testLoadSpecimens() {
            $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id' => 5001],$this->DB);
            $this->assertEqual(0,count($ap->specimens));

            $ap->loadSpecimens();

            $this->assertEqual(1,count($ap->specimens));
            $this->assertEqual(8001,$ap->specimens[0]->specimen_id);
        }
    }