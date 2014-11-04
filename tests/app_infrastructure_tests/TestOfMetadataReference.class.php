<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfMetadataReference extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testMetadataReferenceAtributesExist() {
			$this->assertEqual(count(Metadata_Reference::$fields), 11);

            $this->assertTrue(in_array('metadata_reference_id', Metadata_Reference::$fields));
            $this->assertTrue(in_array('created_at', Metadata_Reference::$fields));
            $this->assertTrue(in_array('updated_at', Metadata_Reference::$fields));
            $this->assertTrue(in_array('metadata_type', Metadata_Reference::$fields));
            $this->assertTrue(in_array('metadata_id', Metadata_Reference::$fields));
            $this->assertTrue(in_array('type', Metadata_Reference::$fields));
            $this->assertTrue(in_array('external_reference', Metadata_Reference::$fields));
            $this->assertTrue(in_array('description', Metadata_Reference::$fields));
            $this->assertTrue(in_array('ordering', Metadata_Reference::$fields));
            $this->assertTrue(in_array('flag_active', Metadata_Reference::$fields));
            $this->assertTrue(in_array('flag_delete', Metadata_Reference::$fields));
		}

		//// static methods

		function testCmp() {
            $mdr1 = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6301],$this->DB);
            $mdr2 = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6302],$this->DB);

			$this->assertEqual(-1,Metadata_Reference::cmp($mdr1, $mdr2));
			$this->assertEqual(0, Metadata_Reference::cmp($mdr1, $mdr1));
			$this->assertEqual(1, Metadata_Reference::cmp($mdr2, $mdr1));

            $mdrs = Metadata_Reference::getAllFromDb([],$this->DB);

            usort($mdrs,'Metadata_Reference::cmp');

//            util_prePrintR($mdrs);

            $this->assertEqual(6301,$mdrs[0]->metadata_reference_id);
            $this->assertEqual(6302,$mdrs[1]->metadata_reference_id);
            $this->assertEqual(6307,$mdrs[2]->metadata_reference_id);
            $this->assertEqual(6306,$mdrs[3]->metadata_reference_id);
            $this->assertEqual(6305,$mdrs[4]->metadata_reference_id);
            $this->assertEqual(6304,$mdrs[5]->metadata_reference_id);
            $this->assertEqual(6303,$mdrs[6]->metadata_reference_id);
        }


        function testCreateNewMetadataReference() {
            $this->todo();
        }

        function testRenderReferencesArrayAsListsView() {
            $ref_list = [
                Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6302],$this->DB), // text
                Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6305],$this->DB), // image
                Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6304],$this->DB), // link
                Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6303],$this->DB), // image
                Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6306],$this->DB) // link
                ];

            $canonical = '';
            $canonical .= '<div class="metadata-references">'."\n";
            $canonical .= '  <ul class="metadata-references metadata-references-images">'."\n";
            $canonical .= '    <li>'.$ref_list[1]->renderAsViewEmbed().'</li>'."\n";
            $canonical .= '    <li>'.$ref_list[3]->renderAsViewEmbed().'</li>'."\n";
            $canonical .= '  </ul>'."\n";
            $canonical .= '  <ul class="metadata-references metadata-references-links">'."\n";
            $canonical .= '    <li>'.$ref_list[2]->renderAsViewEmbed().'</li>'."\n";
            $canonical .= '    <li>'.$ref_list[4]->renderAsViewEmbed().'</li>'."\n";
            $canonical .= '  </ul>'."\n";
            $canonical .= '  <ul class="metadata-references metadata-references-texts">'."\n";
            $canonical .= '    <li>'.$ref_list[0]->renderAsViewEmbed().'</li>'."\n";
            $canonical .= '  </ul>'."\n";
            $canonical .= '</div>'."\n";

            $rendered = Metadata_Reference::renderReferencesArrayAsListsView($ref_list);

//            echo "<pre>\n".htmlentities($canonical)."\n-----\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderReferencesArrayAsListsEdit() {
            $ref_list = [
                Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6302],$this->DB),
                Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6305],$this->DB),
                Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6304],$this->DB),
                Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6303],$this->DB),
                Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6306],$this->DB)
            ];
            $canonical = '';

            $canonical .= '<div class="metadata-references edit-metadata-references">'."\n";
            $canonical .= '  <ul class="metadata-references metadata-references-images edit-metadata-references-images">'."\n";
            $canonical .= '    <li>'.$ref_list[1]->renderAsEditEmbed().'</li>'."\n";
            $canonical .= '    <li>'.$ref_list[3]->renderAsEditEmbed().'</li>'."\n";
            $canonical .= '  </ul>'."\n";
            $canonical .= '  <ul class="metadata-references metadata-references-links edit-metadata-references-links">'."\n";
            $canonical .= '    <li>'.$ref_list[2]->renderAsEditEmbed().'</li>'."\n";
            $canonical .= '    <li>'.$ref_list[4]->renderAsEditEmbed().'</li>'."\n";
            $canonical .= '  </ul>'."\n";
            $canonical .= '  <ul class="metadata-references metadata-references-texts edit-metadata-references-texts">'."\n";
            $canonical .= '    <li>'.$ref_list[0]->renderAsEditEmbed().'</li>'."\n";
            $canonical .= '  </ul>'."\n";
            $canonical .= '</div>'."\n";

            $rendered = Metadata_Reference::renderReferencesArrayAsListsEdit($ref_list);

//            echo "<pre>\n".htmlentities($canonical)."\n-----\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);        }

        //// instance methods - related data

        function testGetReferrent() {
            $mdr1 = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6301],$this->DB);
            $mdr2 = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6302],$this->DB);
            $mdr3 = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6303],$this->DB);
            $mdrX = new Metadata_Reference(['metadata_reference_id' => 10001, 'DB' => $this->DB]);

            $r1 = $mdr1->getReferrent();
            $r2 = $mdr2->getReferrent();
            $r3 = $mdr3->getReferrent();
            $rX = $mdrX->getReferrent();

            $this->assertEqual(6001,$r1->metadata_structure_id);
            $this->assertEqual(6101,$r2->metadata_term_set_id);
            $this->assertEqual(6211,$r3->metadata_term_value_id);
            $this->assertEqual('UNKNOWN METADATA_TYPE: //',$rX);
        }

        //// instance methods - object itself

        function testRenderAsHtml_text() {
            $mdr = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6302],$this->DB);

            $text = "< 3 mm : less than the thickness of 3 pennies
3 mm - 1cm : smaller than the smallest thickness of your pinkie finger
1-3 cm : up to the largest thickness of your thumb
3-6 cm : up to the thicness of 3 fingers
6-12 cm : up to the thicness of 2 fists side face to face
12-20 cm : up to the thicness of 2 fists pinkie to pinkie (palms up)
20-30 cm : up to the length of your forearm
> 30 cm : bigger than that";
//            $text = preg_replace('/\\r/',"",$text);

            $canonical = '<div class="text_data" title="description of the small sizes">'.htmlentities($text).'</div>';
            $rendered = $mdr->renderAsHtml();
//            echo "<pre>\n".htmlentities($canonical)."\n-----\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsViewEmbed_text() {
            $mdr = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6302],$this->DB);
            $canonical = '<div id="rendered_metadata_reference_6302" class="embedded rendered_metadata_reference rendered_metadata_reference_text">'.$mdr->renderAsHtml().'</div>';
            $rendered = $mdr->renderAsViewEmbed();
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

//        function testRenderAsListItem_text() {
//            $mdr = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6301],$this->DB);
//
//            global $USER;
//            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);
//
//            $canonical = '<li data-metadata_reference_id="6301" data-created_at="'.$mdr->created_at.'" data-updated_at="'.$mdr->updated_at.'" data-flag_delete="0">'.
//                ''.
//                '</li>';
//
//            $rendered = $mdr->renderAsListItem();
//
////            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
//
//            $this->assertEqual($canonical,$rendered);
//            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
//        }

        //---------

        function testRenderAsHtml_image() {
            $mdr = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6303],$this->DB);
            $canonical = '<img class="metadata-reference-image" src="'.APP_ROOT_PATH.'/image_data/reference/testing/red.jpg" alt="image of the color red"/>';
            $rendered = $mdr->renderAsHtml();
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);

            $mdr = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6305],$this->DB);
            $canonical = '<img class="metadata-reference-image external-reference" src="http://cf.ydcdn.net/1.0.1.20/images/main/dentate.jpg" alt="picture of dentate"/>';
            $rendered = $mdr->renderAsHtml();
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsViewEmbed_image() {
            $mdr = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6303],$this->DB);
            $canonical = '<div id="rendered_metadata_reference_6303" class="embedded rendered_metadata_reference rendered_metadata_reference_image">'.$mdr->renderAsHtml().'</div>';
            $rendered = $mdr->renderAsViewEmbed();
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);

            $mdr = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6305],$this->DB);
            $canonical = '<div id="rendered_metadata_reference_6305" class="embedded rendered_metadata_reference rendered_metadata_reference_image">'.$mdr->renderAsHtml().'</div>';
            $rendered = $mdr->renderAsViewEmbed();
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

//        function testRenderAsListItem_image() {
//            $mdr = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6303],$this->DB);
//
//            global $USER;
//            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);
//
//            $canonical = '<li data-metadata_reference_id="6303" data-created_at="'.$mdr->created_at.'" data-updated_at="'.$mdr->updated_at.'" data-flag_delete="0">'.
//                ''.
//                '</li>';
//
//            $rendered = $mdr->renderAsListItem();
//
////            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
//
//            $this->assertEqual($canonical,$rendered);
//            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
//        }

        //---------

        function testRenderAsHtml_link() {
            $mdr = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6304],$this->DB);
            $canonical = '<a href="http://dictionary.reference.com/browse/dentate" title="definition of dentate">definition of dentate</a>';
            $rendered = $mdr->renderAsHtml();
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsViewEmbed_link() {
            $mdr = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6304],$this->DB);

            global $USER;
            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);

            $canonical = '<div id="rendered_metadata_reference_6304" class="embedded rendered_metadata_reference rendered_metadata_reference_link">'.$mdr->renderAsHtml().'</div>';

            $rendered = $mdr->renderAsViewEmbed();

//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";

            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

//        function testRenderAsListItem_link() {
//            $mdr = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6304],$this->DB);
//
//            global $USER;
//            $USER = User::getOneFromDb(['username'=>TESTINGUSER], $this->DB);
//
//            $canonical = '<li data-metadata_reference_id="6304" data-created_at="'.$mdr->created_at.'" data-updated_at="'.$mdr->updated_at.'" data-flag_delete="0">'.
//                ''.
//                '</li>';
//
//            $rendered = $mdr->renderAsListItem();
//
////            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
//
//            $this->assertEqual($canonical,$rendered);
//            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
//        }

        //---------

        function testRenderAsEditEmbed_text() {
            $this->todo();
        }

        function testRenderAsEditEmbed_image() {
            $this->todo();
            return;

            $mdr = Metadata_Reference::getOneFromDb(['metadata_reference_id'=>6303],$this->DB);
            $canonical = '<div id="rendered_metadata_reference_6303" class="embedded rendered_metadata_reference rendered_metadata_reference_image">'.$mdr->renderAsHtml().'</div>';
            $rendered = $mdr->renderAsEditEmbed();
//            echo "<pre>\n".htmlentities($canonical)."\n".htmlentities($rendered)."\n</pre>";
            $this->assertEqual($canonical,$rendered);
            $this->assertNoPattern('/IMPLEMENTED/',$rendered);
        }

        function testRenderAsEditEmbed_link() {
            $this->todo();
        }
    }