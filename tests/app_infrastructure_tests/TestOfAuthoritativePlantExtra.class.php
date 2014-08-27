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
			$this->assertEqual(count(Authoritative_Plant_Extra::$fields), 8);

            $this->assertTrue(in_array('authoritative_plant_extra_id', Authoritative_Plant_Extra::$fields));
            $this->assertTrue(in_array('created_at', Authoritative_Plant_Extra::$fields));
            $this->assertTrue(in_array('updated_at', Authoritative_Plant_Extra::$fields));
            $this->assertTrue(in_array('authoritative_plant_id', Authoritative_Plant_Extra::$fields));
            $this->assertTrue(in_array('type', Authoritative_Plant_Extra::$fields));
            $this->assertTrue(in_array('value', Authoritative_Plant_Extra::$fields));
            $this->assertTrue(in_array('ordering', Authoritative_Plant_Extra::$fields));
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

            $this->assertEqual(5103,$pes[0]->authoritative_plant_extra_id);
            $this->assertEqual(5101,$pes[1]->authoritative_plant_extra_id);
            $this->assertEqual(5102,$pes[2]->authoritative_plant_extra_id);
            $this->assertEqual(5106,$pes[3]->authoritative_plant_extra_id);
            $this->assertEqual(5105,$pes[4]->authoritative_plant_extra_id);
            $this->assertEqual(5104,$pes[5]->authoritative_plant_extra_id);
            $this->assertEqual(5107,$pes[6]->authoritative_plant_extra_id);
            $this->assertEqual(5108,$pes[7]->authoritative_plant_extra_id);
            $this->assertEqual(5109,$pes[8]->authoritative_plant_extra_id);
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

            $canonical = "<span class=\"field-value taxonomy taxonomy-common-name\">\"AP_A common z chestnut\"</span>";
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

            $canonical = '<img class="plant-image external-reference" src="https://www.flickr.com/photos/plussed/14761853313"/>';
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

            $canonical = "<li class=\"authoritative-plant-extra\"><span class=\"field-value taxonomy taxonomy-common-name\">\"AP_A common z chestnut\"</span></li>";
            $rendered = $pe->renderAsListItem();

            $this->assertEqual($canonical,$rendered);
        }
    }