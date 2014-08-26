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

        //// instance methods - object itself

        function testRenderAsShortText() {
            $ap = Authoritative_Plant::getOneFromDb(['authoritative_plant_id'=>5001],$this->DB);

            $canonical = "Ap_a_genus ap_a_species 'AP_A_variety' (\"AP_A common y achestnut\") [AP_1_CI]";
            $rendered = $ap->renderAsShortText();

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
                'data-class="AP_A_class" data-order="AP_A_order" data-family="AP_A_family" data-genus="AP_A_genus" data-species="AP_A_species" data-variety="AP_A_variety" data-catalog_identifier="AP_1_CI" data-flag_delete="0"><a href="/app_code/authoritative_plant.php?authoritative_plant_id=5001">'.htmlentities($ap->renderAsShortText()).'</a></li>';

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
                'data-class="AP_A_class" data-order="AP_A_order" data-family="AP_A_family" data-genus="AP_A_genus" data-species="AP_A_species" data-variety="AP_A_variety" data-catalog_identifier="AP_1_CI" data-flag_delete="0" data-can-edit="1"><a href="/app_code/authoritative_plant.php?authoritative_plant_id=5001">'.htmlentities($ap->renderAsShortText()).'</a></li>';
            $rendered = $ap->renderAsListItem();
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);

            $rat->doDelete();
            unset($USER);
        }

        function testRenderAsViewEmbed() {
            $this->todo();
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

    }