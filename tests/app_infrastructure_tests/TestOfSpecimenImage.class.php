<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfSpecimenImage extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testSpecimenImageAtributesExist() {
			$this->assertEqual(count(Specimen_Image::$fields), 10);

            $this->assertTrue(in_array('specimen_image_id', Specimen_Image::$fields));
            $this->assertTrue(in_array('created_at', Specimen_Image::$fields));
            $this->assertTrue(in_array('updated_at', Specimen_Image::$fields));
            $this->assertTrue(in_array('specimen_id', Specimen_Image::$fields));
            $this->assertTrue(in_array('user_id', Specimen_Image::$fields));

            $this->assertTrue(in_array('image_reference', Specimen_Image::$fields));
            $this->assertTrue(in_array('ordering', Specimen_Image::$fields));

            $this->assertTrue(in_array('flag_workflow_published', Specimen_Image::$fields));
            $this->assertTrue(in_array('flag_workflow_validated', Specimen_Image::$fields));
            $this->assertTrue(in_array('flag_delete', Specimen_Image::$fields));
		}

		//// static methods

		function testCmp() {
            $si1 = Specimen_Image::getOneFromDb(['specimen_image_id'=>8101],$this->DB);
            $si2 = Specimen_Image::getOneFromDb(['specimen_image_id'=>8102],$this->DB);
            $si3 = Specimen_Image::getOneFromDb(['specimen_image_id'=>8103],$this->DB);
            $si4 = Specimen_Image::getOneFromDb(['specimen_image_id'=>8104],$this->DB);

            $this->assertEqual(Specimen_Image::cmp($si1, $si1), 0); // self-equal

            $this->assertEqual(Specimen_Image::cmp($si1, $si2), 1); // by ordering
            $this->assertEqual(Specimen_Image::cmp($si2, $si1), -1);

            $this->assertEqual(Specimen_Image::cmp($si1, $si3), -1); // by specimen
            $this->assertEqual(Specimen_Image::cmp($si3, $si1), 1);

            $this->assertEqual(Specimen_Image::cmp($si2, $si3), -1); // by specimen
            $this->assertEqual(Specimen_Image::cmp($si3, $si2), 1);

            $this->assertEqual(Specimen_Image::cmp($si3, $si4), 1); // by image_reference
            $this->assertEqual(Specimen_Image::cmp($si4, $si3), -1);
        }

        //// instance methods - object itself

        //// instance methods - related data
        function testGetSpecimen() {
            $si = Specimen_Image::getOneFromDb(['specimen_image_id'=>8101],$this->DB);

            $s = $si->getSpecimen();

            $this->assertEqual(8001,$s->specimen_id);
        }

        function testRenderAsHtml_local() {
            $si = Specimen_Image::getOneFromDb(['specimen_image_id'=>8101],$this->DB);

            $canonical = '<img id="specimen_image_8101" class="plant-image" src="'.APP_ROOT_PATH.'/image_data/specimen/testing/cnh_castanea_dentata.jpg" />';
            $rendered = $si->renderAsHtml();

//            echo "<pre>\n".htmlentities($canonical)."\n------------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsHtml_external() {
            $si = Specimen_Image::getOneFromDb(['specimen_image_id'=>8102],$this->DB);

            $canonical = '<img id="specimen_image_8102" class="plant-image external-reference" src="https://farm6.staticflickr.com/5556/14761853313_17d5a31479_z.jpg" />';
            $rendered = $si->renderAsHtml();

//            echo "<pre>\n".htmlentities($canonical)."\n------------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsListItem() {
            $si = Specimen_Image::getOneFromDb(['specimen_image_id'=>8101],$this->DB);

            $canonical = '<li class="specimen-image" data-specimen_image_id="8101" data-created_at="'.$si->created_at.'" data-updated_at="'.$si->updated_at.'" data-specimen_id="8001" data-user_id="110" data-image_reference="testing/cnh_castanea_dentata.jpg" data-ordering="1.00000" data-flag_workflow_published="1" data-flag_workflow_validated="1" data-flag_delete="0">'.
                $si->renderAsHtml().'</li>';

            $rendered = $si->renderAsListItem();

//            echo "<pre>\n".htmlentities($canonical)."\n------------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsListItemEdit() {
            $si = Specimen_Image::getOneFromDb(['specimen_image_id'=>8103],$this->DB);
            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);
            $canonical = '<li id="specimen-image-8103" class="specimen-image" data-specimen_image_id="8103" data-created_at="'.$si->created_at.'" data-updated_at="'.$si->updated_at.'" data-specimen_id="8002" data-user_id="101" data-image_reference="testing/USER101_8103_cnh_castanea_dentata.jpg" data-ordering="0.75000" data-flag_workflow_published="0" data-flag_workflow_validated="1" data-flag_delete="0">';
            $canonical .= '<button type="button" class="btn btn-danger button-delete-specimen-image" title="'.util_lang('prompt_confirm_delete','ucfirst').'" data-specimen_image_id="'.$si->specimen_image_id.'" data-dom_id="specimen-image-8103"><i class="icon-remove icon-white"></i></button><br/>';
            $canonical .= $si->renderAsHtml();
            $canonical .= '<div class="controls">';
            $canonical .= util_orderingLeftRightControls('specimen-image-8103');
            $canonical .= '<input type="hidden" name="new_ordering-specimen-image-8103" id="new_ordering-specimen-image-8103" value="'.$si->ordering.'"/>';
            // publish, verify, reordering handle
            $canonical .= '<div class="control-workflows"><span class="control-publish"><input id="flag_workflow_published_8103-control" type="checkbox" name="flag_workflow_published" value="1" /> publish</span>, <span class="control-verify">verified</span></div>';
            $canonical .= '</div>';
            $canonical .= '</li>';

            $rendered = $si->renderAsListItemEdit();

//            echo "<pre>\n".htmlentities($canonical)."\n------------------\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testDoDelete() {
            // record is deleted
            // file is moved to one w/ same name but .DEL extension added
            $si = Specimen_Image::getOneFromDb(['specimen_image_id'=>8101],$this->DB);
            $origFile = $_SERVER['DOCUMENT_ROOT'].APP_ROOT_PATH.'/image_data/specimen/'.$si->image_reference;
            $this->assertTrue(file_exists($origFile));

            $si->doDelete();

            $siDel = Specimen_Image::getOneFromDb(['specimen_image_id'=>8101],$this->DB);
            $this->assertFalse($siDel->matchesDb);

            $this->assertFalse(file_exists($origFile));
            $this->assertTrue(file_exists($origFile.'.DEL'));

            // now clean up after this test by putting the file back
            if (file_exists($origFile.'.DEL')) {
                rename($origFile.'.DEL',$origFile);
                $this->assertTrue(file_exists($origFile));
            }
        }
    }