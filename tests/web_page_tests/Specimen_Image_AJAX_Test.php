<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class Specimen_Image_AJAX_Test extends WMSWebTestCase {

    function setUp() {
        createAllTestData($this->DB);
        global $CUR_LANG_SET;
        $CUR_LANG_SET = 'en';
    }

    function tearDown() {
        removeAllTestData($this->DB);
    }

    function doLoginBasic() {
        $this->get('http://localhost/digitalfieldnotebooks/');
        $this->assertCookie('PHPSESSID');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);

        $this->click('Sign in');

        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');
    }

    function doLoginAdmin() {
        makeAuthedTestUserAdmin($this->DB);
        $this->doLoginBasic();
    }

    //-----------------------------------------------------------------------------------------------------------------

    function testDeleteImage_USER() {
        // pre-condition check
        $delImgPre = Specimen_Image::getOneFromDb(['specimen_image_id'=>8103],$this->DB);
        $this->assertTrue($delImgPre->matchesDb);

        $imageOrigName = $_SERVER['DOCUMENT_ROOT'].APP_ROOT_PATH.'/image_data/specimen/'.$delImgPre->image_reference;
        $this->assertTrue(file_exists($imageOrigName));

        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/ajax_actions/specimen_image.php?action=delete&specimen_image_id=8103');
        $this->checkBasicAsserts();

        // script status should be success
        $results = json_decode($this->getBrowser()->getContent());
//        util_prePrintR($results);
        $this->assertEqual('success',$results->status);
//        $this->assertEqual($expected,$results->html_output);
        $this->assertNoPattern('/IMPLEMENTED/');

        // record should be gone
        $delImgPost = Specimen_Image::getOneFromDb(['specimen_image_id'=>8103],$this->DB);
        $this->assertFalse($delImgPost->matchesDb);

        // image file should be marked for / as deleted by having the .DEL extension added
        $this->assertFalse(file_exists($imageOrigName));
        $this->assertTrue(file_exists($imageOrigName.'.DEL'));

        // now clean up the deleted image file and verify that the orig is back in place
        if (file_exists($imageOrigName.'.DEL')) {
            $this->assertTrue(rename($imageOrigName.'.DEL',$imageOrigName));
            $this->assertTrue(file_exists($imageOrigName));
        }
    }

    function testDeleteImage_ADMIN() {

        // pre-condition check
        $delImgPre = Specimen_Image::getOneFromDb(['specimen_image_id'=>8101],$this->DB);
        $this->assertTrue($delImgPre->matchesDb);

        $imageOrigName = $_SERVER['DOCUMENT_ROOT'].APP_ROOT_PATH.'/image_data/specimen/'.$delImgPre->image_reference;
        $this->assertTrue(file_exists($imageOrigName));

        $this->doLoginAdmin();

        $this->get('http://localhost/digitalfieldnotebooks/ajax_actions/specimen_image.php?action=delete&specimen_image_id=8101');
        $this->checkBasicAsserts();

        // script status should be success
        $results = json_decode($this->getBrowser()->getContent());
//        util_prePrintR($results);
        $this->assertEqual('success',$results->status);
//        $this->assertEqual($expected,$results->html_output);
        $this->assertNoPattern('/IMPLEMENTED/');

        // record should be gone
        $delImgPost = Specimen_Image::getOneFromDb(['specimen_image_id'=>8101],$this->DB);
        $this->assertFalse($delImgPost->matchesDb);

        // image file should be marked for / as deleted by having the .DEL extension added
        $this->assertFalse(file_exists($imageOrigName));
        $this->assertTrue(file_exists($imageOrigName.'.DEL'));

        // now clean up the deleted image file and verify that the orig is back in place
        if (file_exists($imageOrigName.'.DEL')) {
            $this->assertTrue(rename($imageOrigName.'.DEL',$imageOrigName));
            $this->assertTrue(file_exists($imageOrigName));
        }
    }

    function testDeleteImage_BLOCKED() {

        // pre-condition check
        $delImgPre = Specimen_Image::getOneFromDb(['specimen_image_id'=>8101],$this->DB);
        $this->assertTrue($delImgPre->matchesDb);

        $imageOrigName = $_SERVER['DOCUMENT_ROOT'].APP_ROOT_PATH.'/image_data/specimen/'.$delImgPre->image_reference;
        $this->assertTrue(file_exists($imageOrigName));

        $this->doLoginBasic();

        $this->get('http://localhost/digitalfieldnotebooks/ajax_actions/specimen_image.php?action=delete&specimen_image_id=8101');
        $this->checkBasicAsserts();

        // script status should be success
        $results = json_decode($this->getBrowser()->getContent());
//        util_prePrintR($results);
        $this->assertEqual('failure',$results->status);
        $this->assertEqual(util_lang('no_permission'),$results->note);
//        $this->assertEqual($expected,$results->html_output);
        $this->assertNoPattern('/IMPLEMENTED/');

        $delImgPost = Specimen_Image::getOneFromDb(['specimen_image_id'=>8101],$this->DB);
        $this->assertTrue($delImgPost->matchesDb);
        $this->assertTrue(file_exists($imageOrigName));

    }

    function testImageReordering() {
        $this->doLoginAdmin();

        $this->get('http://localhost/digitalfieldnotebooks/ajax_actions/specimen_image.php?action=reorder&for_specimen=8002&ordering_8104=70&ordering_8103=50');
        $this->checkBasicAsserts();

        // script status should be success
        $results = json_decode($this->getBrowser()->getContent());
//        util_prePrintR($results);
        $this->assertEqual('success',$results->status);

        $s03 = Specimen_Image::getOneFromDb(['specimen_image_id'=>8103],$this->DB);
        $s04 = Specimen_Image::getOneFromDb(['specimen_image_id'=>8104],$this->DB);

        $this->assertEqual(50,$s03->ordering);
        $this->assertEqual(70,$s04->ordering);
    }

    function testToDo() {
// NOTE: image upload too messy to test at the moment - it works for now, and if it breaks later then we'll add a test for it then
//        $this->todo('image delete test');
    }
}