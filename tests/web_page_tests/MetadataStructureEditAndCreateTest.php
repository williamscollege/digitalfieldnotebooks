<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class MetadataStructureEditAndCreateTest extends WMSWebTestCase {

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

    function goToMetadataStructureView($target_id) {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=view&metadata_structure_id='.$target_id);
    }

    function goToMetadataStructureEdit($target_id) {
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=edit&metadata_structure_id='.$target_id);
    }

    function checkBasicAsserts() {
        $this->assertNoText('IMPLEMENTED');
        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/fatal error/i');
    }

    //-----------------------------------------------------------------------------------------------------------------

    function testEditButtonExists() {
        $this->doLoginAdmin();
        $this->goToMetadataStructureView(6002);

        $this->checkBasicAsserts();
        $this->assertEltByIdHasAttrOfValue('metadata_structure-btn-edit-6002','class','edit_link btn');
    }

    function testCreateButtonExists_MAIN_LIST() {
        $this->doLoginAdmin();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=list');

//        $this->showContent();

        $this->checkBasicAsserts();
        $this->assertEltByIdHasAttrOfValue('btn-add-metadata_structure-ROOT','title',util_lang('add_metadata_structure'));
    }

    function testCreateButtonExists_EXISTING_STRUCTURE() {
        $this->doLoginAdmin();

        $this->goToMetadataStructureEdit(6002);

//        $this->showContent();

        $this->checkBasicAsserts();
        $this->assertEltByIdHasAttrOfValue('btn-add-metadata-structure','title',util_lang('add_metadata_structure'));
    }

    function testCreateButtonAction() {
        $this->doLoginAdmin();
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=create&parent_metadata_structure_id=6001');

//        $this->showContent();

        $this->checkBasicAsserts();
        $this->assertPattern('/'.util_lang('new_metadata_structure_description').'/');
    }

    function testEditButtonAction() { // make sure it exists and that clicking on it goes to the edit page
        $this->doLoginAdmin();
        $this->goToMetadataStructureEdit(6002);

        $edit_content = $this->getBrowser()->getContent();

        $this->goToMetadataStructureView(6002);

        $this->checkBasicAsserts();
        $this->assertEltByIdHasAttrOfValue('metadata_structure-btn-edit-6002','title',util_lang('edit'));

        $this->click(util_lang('edit'));
        $clicked_content = $this->getBrowser()->getContent();

        $this->checkBasicAsserts();
        $this->assertEqual($edit_content,$clicked_content);
    }

    function testBaseDataUpdate() {
        $this->doLoginAdmin();
        $this->goToMetadataStructureEdit(6002);

//      NOTE: the identifier to use for setField is the value of the name attribute of the field
        $this->setField('name','mds new');
//        NOTE: the identifier to use for form buttons is the value of the value attribute of the button, or the interior html of a button element
        $this->click('<i class="icon-ok-sign icon-white"></i> '.util_lang('update','properize'));

        $this->checkBasicAsserts();
        $this->assertText('mds new');

        $mds = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);
        $this->assertEqual($mds->name,'mds new');
    }

    function testBaseDataUpdate_NEW() {
        $this->doLoginAdmin();
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=create&parent_metadata_structure_id=6001');
        $this->checkBasicAsserts();

        $this->setField('name','brand new metadata structure');
        $this->click('<i class="icon-ok-sign icon-white"></i> '.util_lang('save','properize'));

        $this->checkBasicAsserts();
        $this->assertText('brand new metadata structure');

        $mds = Metadata_Structure::getOneFromDb(['name'=>'brand new metadata structure'],$this->DB);
        $this->assertTrue($mds->matchesDb);
    }

    function testSubStructureReOrdering() {
        $mds_initial = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);

        $this->doLoginAdmin();

        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=update&metadata_structure_id=6001&new_ordering-item-metadata_structure_6002=25');

        $this->checkBasicAsserts();

//        $this->showContent();

        $mds_revised_order = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);

        $this->assertNotEqual($mds_initial->ordering,$mds_revised_order->ordering);
        $this->assertEqual(25,$mds_revised_order->ordering);
    }

    function testDeleteStructure() {
        $mds_orig = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);

        $this->doLoginAdmin();
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=delete&metadata_structure_id='.$mds_orig->metadata_structure_id);

//        $this->showContent();

        $this->checkBasicAsserts();

        $mds_del = Metadata_Structure::getOneFromDb(['metadata_structure_id'=>6002],$this->DB);
        $this->assertFalse($mds_del->matchesDb);
    }

    function testToDo() {
//        $this->todo('');
    }

}