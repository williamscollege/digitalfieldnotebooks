<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class PublicPagesAccessTest extends WMSWebTestCase {

    function setUp() {
        createAllTestData($this->DB);
        global $CUR_LANG_SET;
        $CUR_LANG_SET = 'en';
    }

    function tearDown() {
        removeAllTestData($this->DB);
    }

    function doAsserts() {
        $this->assertResponse(200);
        $this->assertNoPattern('/IMPLEMENTED/i');
        $this->assertNoPattern('/warning/i');
        $this->assertNoPattern('/error/i');
    }

    function testIndexPage() {
        $this->get('http://localhost/digitalfieldnotebooks/');
        $this->doAsserts();
    }

    function testNotebookList() {
        $this->todo();
        $this->get('http://localhost/digitalfieldnotebooks/app_code/notebook.php?action=list');
        $this->doAsserts();
    }

    function testNotebookSpecific() {
        $this->todo();
        $this->get('http://localhost/digitalfieldnotebooks/notebook.php?notebook_id=1004');
        $this->doAsserts();
    }

    function testNotebookPageSpecific() {
        $this->todo();
        $this->get('http://localhost/digitalfieldnotebooks/notebook_page.php?notebook_page_id=1104');
        $this->doAsserts();
    }

    function testMetadataStructureList() {
        $this->todo();
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_structure.php?action=list');
        $this->doAsserts();
    }

    function testMetadataStructureSpecific() {
        $this->todo();
        $this->get('http://localhost/digitalfieldnotebooks/metadata_structure.php?metadata_structure_id=6001');
        $this->doAsserts();
    }

    function testMetadataTermSetList() {
        $this->todo();
        $this->get('http://localhost/digitalfieldnotebooks/app_code/metadata_term_set.php?action=list');
        $this->doAsserts();
    }

    function testMetadataTermSetSpecific() {
        $this->todo();
        $this->get('http://localhost/digitalfieldnotebooks/metadata_term_set.php?metadata_term_set_id=6101');
        $this->doAsserts();
    }

    function testAuthoritativePlantList() {
        $this->todo();
        $this->get('http://localhost/digitalfieldnotebooks/app_code/authoritative_plant.php?action=list');
        $this->doAsserts();
    }

    function testAuthoritativePlantSpecific() {
        $this->todo();
        $this->get('http://localhost/digitalfieldnotebooks/authoritative_plant.php?authoritative_plant_id=5001');
        $this->doAsserts();
    }

    function testSearch() {
        $this->todo();
        $this->get('http://localhost/digitalfieldnotebooks/app_code/search.php');
        $this->doAsserts();
    }
}