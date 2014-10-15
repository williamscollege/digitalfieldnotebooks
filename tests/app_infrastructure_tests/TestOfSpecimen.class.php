<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfSpecimen extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testSpecimenAtributesExist() {
			$this->assertEqual(count(Specimen::$fields), 15);

            $this->assertTrue(in_array('specimen_id', Specimen::$fields));
            $this->assertTrue(in_array('created_at', Specimen::$fields));
            $this->assertTrue(in_array('updated_at', Specimen::$fields));

            $this->assertTrue(in_array('user_id', Specimen::$fields));
            $this->assertTrue(in_array('link_to_type', Specimen::$fields));
            $this->assertTrue(in_array('link_to_id', Specimen::$fields));
            $this->assertTrue(in_array('name', Specimen::$fields));
            $this->assertTrue(in_array('gps_longitude', Specimen::$fields));
            $this->assertTrue(in_array('gps_latitude', Specimen::$fields));
            $this->assertTrue(in_array('notes', Specimen::$fields));
            $this->assertTrue(in_array('ordering', Specimen::$fields));
            $this->assertTrue(in_array('catalog_identifier', Specimen::$fields));

            $this->assertTrue(in_array('flag_workflow_published', Specimen::$fields));
            $this->assertTrue(in_array('flag_workflow_validated', Specimen::$fields));

            $this->assertTrue(in_array('flag_delete', Specimen::$fields));
		}

		//// static methods

		function testCmp() {
            $s1 = Specimen::getOneFromDb(['specimen_id'=>8001],$this->DB);
            $s2 = Specimen::getOneFromDb(['specimen_id'=>8002],$this->DB);

			$this->assertEqual(Specimen::cmp($s1, $s2), -1);
			$this->assertEqual(Specimen::cmp($s1, $s1), 0);
			$this->assertEqual(Specimen::cmp($s2, $s1), 1);

            $sall = Specimen::getAllFromDb([],$this->DB);

            usort($sall,'Specimen::cmp');

            $this->assertEqual(4,count($sall));

            $this->assertEqual(8001,$sall[0]->specimen_id);
            $this->assertEqual(8003,$sall[1]->specimen_id);
            $this->assertEqual(8002,$sall[2]->specimen_id);
            $this->assertEqual(8004,$sall[3]->specimen_id);
        }

        function testCreateNewSpecimenForNotebookPage() {
            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $s = Specimen::createNewSpecimenForNotebookPage(1101,$this->DB);

            $this->assertEqual('NEW',$s->specimen_id);
            $this->assertNotEqual('',$s->created_at);
            $this->assertNotEqual('',$s->updated_at);
            $this->assertEqual($USER->user_id,$s->user_id);
            $this->assertEqual('notebook_page',$s->link_to_type);
            $this->assertEqual(1101,$s->link_to_id);
            $this->assertEqual(util_lang('new_specimen_name'),$s->name);
            $this->assertEqual(0,$s->gps_longitude);
            $this->assertEqual(0,$s->gps_latitude);
            $this->assertEqual(util_lang('new_specimen_notes'),$s->notes);
            $this->assertEqual(0,$s->ordering);
            $this->assertEqual('',$s->catalog_identifier);
            $this->assertEqual(0,$s->flag_workflow_published);
            $this->assertEqual(0,$s->flag_workflow_validated);
            $this->assertEqual(false,$s->flag_delete);
        }

        function testCreateNewSpecimenForAuthoritativePlant() {
            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $s = Specimen::createNewSpecimenForAuthoritativePlant(5001,$this->DB);

            $this->assertEqual('NEW',$s->specimen_id);
            $this->assertNotEqual('',$s->created_at);
            $this->assertNotEqual('',$s->updated_at);
            $this->assertEqual($USER->user_id,$s->user_id);
            $this->assertEqual('authoritative_plant',$s->link_to_type);
            $this->assertEqual(5001,$s->link_to_id);
            $this->assertEqual(util_lang('new_specimen_name'),$s->name);
            $this->assertEqual(0,$s->gps_longitude);
            $this->assertEqual(0,$s->gps_latitude);
            $this->assertEqual(util_lang('new_specimen_notes'),$s->notes);
            $this->assertEqual(0,$s->ordering);
            $this->assertEqual('',$s->catalog_identifier);
            $this->assertEqual(0,$s->flag_workflow_published);
            $this->assertEqual(0,$s->flag_workflow_validated);
            $this->assertEqual(false,$s->flag_delete);
        }

        //// instance methods - object itself

        //// instance methods - related data
        function testGetUser() {
            $s = Specimen::getOneFromDb(['specimen_id'=>8001],$this->DB);

            $u = $s->getUser();

            $this->assertEqual(110,$u->user_id);
        }

        function testGetLinked() {
            $s = Specimen::getOneFromDb(['specimen_id'=>8001],$this->DB);

            $linked = $s->getLinked();

            $this->assertEqual(5001,$linked->authoritative_plant_id);
        }

        function testLoadImages() {
            $s = Specimen::getOneFromDb(['specimen_id'=>8001],$this->DB);

            $s->loadImages();

            $this->assertEqual(2,count($s->images));

            $this->assertEqual(8102,$s->images[0]->specimen_image_id);
            $this->assertEqual(8101,$s->images[1]->specimen_image_id);
        }

        function testRenderAsViewEmbed() {
            $s = Specimen::getOneFromDb(['specimen_id'=>8001],$this->DB);

            $s->cacheImages();

            /*
            # Specimen:
            'specimen_id',8001
            'created_at',
            'updated_at',
            'user_id',110
            'link_to_type',authoritative_plant
            'link_to_id',5001
            'name',sci quad authoritative
            'gps_longitude',-73.2054918
            'gps_latitude',42.7118454
            'notes',notes on authoritative specimen
            'ordering',1
            'catalog_identifier',1a
            'flag_workflow_published',1
            'flag_workflow_validated',1
            'flag_delete'0

            (8001,NOW(),NOW(), 110, 'authoritative_plant', 5001, 'sci quad authoritative', -73.2054918, 42.7118454, 'notes on authoritative specimen', 1, '1a', 1, 1, 0),

            # VALID_LINK_TO_TYPES =  ['authoritative_plant', 'notebook_page'];
            */
            $canonical =
                '<div class="specimen">
  <h3>'.htmlentities($s->name).'</h3>
  <ul class="base-info">
    <li><span class="field-label">'.util_lang('coordinates').'</span> : <span class="field-value"><a href="'.util_coordsMapLink($s->gps_longitude,$s->gps_latitude).'">'.htmlentities($s->gps_longitude).','.htmlentities($s->gps_latitude).'</a></span></li>
    <li><span class="field-label">'.util_lang('notes').'</span> : <span class="field-value">'.htmlentities($s->notes).'</span></li>
    <li><span class="field-label">'.util_lang('catalog_identifier').'</span> : <span class="field-value">'.htmlentities($s->catalog_identifier).'</span></li>
  </ul>
  <ul class="specimen-images">
';
            foreach ($s->images as $image) {
                $canonical .='    '.$image->renderAsListItem()."\n";
            }
            $canonical .='  </ul>
</div>';
            $rendered = $s->renderAsViewEmbed();

//            echo "<pre>\n".htmlentities($canonical)."\n------------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsEditEmbed() {
            $s = Specimen::getOneFromDb(['specimen_id'=>8001],$this->DB);
            $s->cacheImages();

            global $USER;
            $USER = User::getOneFromDb(['user_id'=>110], $this->DB);

            $canonical =
                '<div class="specimen">
<div id="form-edit-specimen-'.$s->specimen_id.'" class="form-edit-specimen" data-specimen_id="'.$s->specimen_id.'">
  <h3><input type="text" name="specimen-name_'.$s->specimen_id.'" id="specimen-name_'.$s->specimen_id.'" value="'.htmlentities($s->name).'"/></h3>
  <span class="published_state"><input id="specimen-workflow-publish-control_'.$s->specimen_id.'" type="checkbox" name="specimen-flag_workflow_published_'.$s->specimen_id.'" value="1" checked="checked" /> publish</span>,  <span class="verified_state"><input id="specimen-workflow-validate-control_'.$s->specimen_id.'" type="checkbox" name="specimen-flag_workflow_validated_'.$s->specimen_id.'" value="1" checked="checked" /> verify</span><br/>
  <ul class="base-info">
    <li><span class="field-label">'.util_lang('coordinates').'</span> : <input type="text" name="specimen-gps_longitude_'.$s->specimen_id.'" id="specimen-gps_longitude_'.$s->specimen_id.'" value="'.htmlentities($s->gps_longitude).'"/>, <input type="text" name="specimen-gps_latitude_'.$s->specimen_id.'" id="specimen-gps_latitude_'.$s->specimen_id.'" value="'.htmlentities($s->gps_latitude).'"/></li>
    <li><span class="field-label">'.util_lang('notes').'</span> : <textarea name="specimen-notes_'.$s->specimen_id.'" id="specimen-notes_'.$s->specimen_id.'" row="4" cols="120">'.htmlentities($s->notes).'</textarea></li>
    <li><span class="field-label">'.util_lang('catalog_identifier').'</span> : <input type="text" name="specimen-catalog_identifier_'.$s->specimen_id.'" id="specimen-catalog_identifier_'.$s->specimen_id.'" value="'.htmlentities($s->catalog_identifier).'"/></li>
  </ul>
  <ul class="specimen-images">
';
            $canonical .= '    <li><a href="#" id="specimen-control-add-image-for-'.$s->specimen_id.'" class="btn add-specimen-image-button" data-for-specimen="'.$s->specimen_id.'">'.util_lang('add_specimen_image').'</a></li>
';
            foreach ($s->images as $image) {
                $canonical .='    '.$image->renderAsListItemEdit()."\n";
            }
            $canonical .='  </ul>
</div>
</div>';
            $rendered = $s->renderAsEditEmbed();

//            echo "<pre>\n".htmlentities($canonical)."\n------------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsListItem_General() {
            $s = Specimen::getOneFromDb(['specimen_id'=>8001],$this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            /*
            # Specimen:
            'specimen_id',8001
            'created_at',
            'updated_at',
            'user_id',110
            'link_to_type',authoritative_plant
            'link_to_id',5001
            'name',sci quad authoritative
            'gps_longitude',-73.2054918
            'gps_latitude',42.7118454
            'notes',notes on authoritative specimen
            'ordering',1
            'catalog_identifier',1a
            'flag_workflow_published',1
            'flag_workflow_validated',1
            'flag_delete'0
            */
            $canonical = '<li data-specimen_id="8001" data-created_at="'.$s->created_at.'" data-updated_at="'.$s->updated_at.'" '.
                'data-user_id="110" data-link_to_type="authoritative_plant" data-link_to_id="5001" data-name="sci quad authoritative" data-gps_longitude="-73.2054918" data-gps_latitude="42.7118454" data-notes="notes on authoritative specimen" data-ordering="1.00000" data-catalog_identifier="1a" data-flag_workflow_published="1" data-flag_workflow_validated="1" data-flag_delete="0"><a href="'.APP_ROOT_PATH.'/app_code/specimen.php?specimen_id=8001">'.
                htmlentities($s->name).'</a></li>';

            $rendered = $s->renderAsListItem();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);

            unset($USER);
        }

        function testRenderAsListItem_Editable() {
            $s = Specimen::getOneFromDb(['specimen_id'=>8001],$this->DB);

            global $USER;
            $USER = User::getOneFromDb(['user_id'=>110], $this->DB);

            /*
            # Specimen:
            'specimen_id',8001
            'created_at',
            'updated_at',
            'user_id',110
            'link_to_type',authoritative_plant
            'link_to_id',5001
            'name',sci quad authoritative
            'gps_longitude',-73.2054918
            'gps_latitude',42.7118454
            'notes',notes on authoritative specimen
            'ordering',1
            'catalog_identifier',1a
            'flag_workflow_published',1
            'flag_workflow_validated',1
            'flag_delete'0
            */
            $canonical = '<li data-specimen_id="8001" data-created_at="'.$s->created_at.'" data-updated_at="'.$s->updated_at.'" '.
                'data-user_id="110" data-link_to_type="authoritative_plant" data-link_to_id="5001" data-name="sci quad authoritative" data-gps_longitude="-73.2054918" data-gps_latitude="42.7118454" data-notes="notes on authoritative specimen" data-ordering="1.00000" data-catalog_identifier="1a" data-flag_workflow_published="1" data-flag_workflow_validated="1" data-flag_delete="0" data-can-edit="1"><a href="'.APP_ROOT_PATH.'/app_code/specimen.php?specimen_id=8001">'.
                htmlentities($s->name).'</a></li>';

            $rendered = $s->renderAsListItem();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);

            unset($USER);
        }

        function testUpdateFromArray() {
            $s = Specimen::getOneFromDb(['specimen_id'=>8001],$this->DB);

            $new_vals_ar = [
                'specimen-name_8001' => 'new name 8001',
                'specimen-gps_longitude_8001' => '11.1111',
                'specimen-gps_latitude_8001' => '22.2222',
                'specimen-notes_8001' => 'new notes 8001',
                'specimen-ordering_8001' => '19.1',
                'specimen-catalog_identifier_8001' => 'ABCD',
                'specimen-flag_workflow_published_8001' => 0,
                'specimen-flag_workflow_validated_8001' => 0,
                        ];

            $this->assertEqual('sci quad authoritative',$s->name);

            $s->setFromArray($new_vals_ar);
            $s->updateDb();

            $s2 = Specimen::getOneFromDb(['specimen_id'=>8001],$this->DB);

            $this->assertEqual($new_vals_ar['specimen-name_8001'],$s->name);
            $this->assertEqual($new_vals_ar['specimen-gps_longitude_8001'],$s->gps_longitude);
            $this->assertEqual($new_vals_ar['specimen-gps_latitude_8001'],$s->gps_latitude);
            $this->assertEqual($new_vals_ar['specimen-notes_8001'],$s->notes);
            $this->assertEqual($new_vals_ar['specimen-ordering_8001'],$s->ordering);
            $this->assertEqual($new_vals_ar['specimen-catalog_identifier_8001'],$s->catalog_identifier);
            $this->assertEqual($new_vals_ar['specimen-flag_workflow_published_8001'],$s->flag_workflow_published);
            $this->assertEqual($new_vals_ar['specimen-flag_workflow_validated_8001'],$s->flag_workflow_validated);
        }

    }