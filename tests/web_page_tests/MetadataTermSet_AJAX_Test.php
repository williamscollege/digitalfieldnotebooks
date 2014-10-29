<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class MetadataTermSet_AJAX_Test extends WMSWebTestCase {

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

    function testGetValuesAsOptions() {
        $this->todo();
//        $this->doLoginBasic();
//
//        global $DB;
//        $DB = $this->DB;
//
//        $this->get('http://localhost/digitalfieldnotebooks/ajax_actions/metadata_structure.php?action=value_options&metadata_structure_id=6002');
//
//        $mdts = Metadata_Term_Set::getOneFromDb(['metadata_term_set_id'=>6101],$DB);
//
//        $expected = '  <option value="-1">'.util_lang('prompt_select').'</option>'."\n" . $mdts->renderValuesAsOptions();
//
//        $this->assertNoPattern('/error/i');
//
//        $results = json_decode($this->getBrowser()->getContent());
//        $this->assertEqual('success',$results->status);
//        $this->assertEqual($expected,$results->html_output);
    }

    function testToDo() {
//        $this->todo('fetch a new page field form interior / field set');
    }
}