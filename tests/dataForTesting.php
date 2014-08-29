<?php
	require_once dirname(__FILE__) . '/../classes/auth_base.class.php';
	require_once dirname(__FILE__) . '/../classes/auth_LDAP.class.php';

    require_once dirname(__FILE__) . '/../classes/action.class.php';
    require_once dirname(__FILE__) . '/../classes/authoritative_plant.class.php';
    require_once dirname(__FILE__) . '/../classes/authoritative_plant_extra.class.php';
    require_once dirname(__FILE__) . '/../classes/metadata_reference.class.php';
    require_once dirname(__FILE__) . '/../classes/metadata_structure.class.php';
    require_once dirname(__FILE__) . '/../classes/metadata_term_set.class.php';
    require_once dirname(__FILE__) . '/../classes/metadata_term_value.class.php';
    require_once dirname(__FILE__) . '/../classes/notebook.class.php';
    require_once dirname(__FILE__) . '/../classes/notebook_page.class.php';
    require_once dirname(__FILE__) . '/../classes/notebook_page_field.class.php';
    require_once dirname(__FILE__) . '/../classes/role.class.php';
    require_once dirname(__FILE__) . '/../classes/role_action_target.class.php';
    require_once dirname(__FILE__) . '/../classes/specimen.class.php';
    require_once dirname(__FILE__) . '/../classes/specimen_image.class.php';
    require_once dirname(__FILE__) . '/../classes/user.class.php';
    require_once dirname(__FILE__) . '/../classes/user_role.class.php';

	/*
	This file contains a series of methods for creating known test data in a target database
	*/
// NOTE !!!!!!!!!!!!!!!!!!!!
// Actions and Roles are pre-populated and fixed - there is no creation nor removal of test data for those tables

/*
function createTestData_XXXX($dbConn) {
    // 1100 series ids
    # XXXX: user_id, created_at, updated_at, username, screen_name, flag_is_system_admin, flag_is_banned, flag_delete
    $addTestSql  = "INSERT INTO " . XXXX::$dbTable . " VALUES
        (1,NOW(),NOW())
    ";
    $addTestStmt = $dbConn->prepare($addTestSql);
    $addTestStmt->execute();
    if ($addTestStmt->errorInfo()[0] != '0000') {
        echo "<pre>error adding test XXXX data to the DB\n";
        print_r($addTestStmt->errorInfo());
        debug_print_backtrace();
        exit;
    }
}
*/
    $ACTIONS = array();

    function createTestData_Authoritative_Plants($dbConn) {
        // 5000 series ids
        # 'authoritative_plant_id', 'created_at', 'updated_at', 'class', 'order', 'family', 'genus', 'species', 'variety', 'catalog_identifier', 'flag_delete'
        $addTestSql  = "INSERT INTO " . Authoritative_Plant::$dbTable . " VALUES
            (5001,NOW(),NOW(), 'AP_A_class', 'AP_A_order', 'AP_A_family', 'AP_A_genus', 'AP_A_species', 'AP_A_variety', 'AP_1_CI', 0),
            (5002,NOW(),NOW(), 'AP_A_class', 'AP_A_order', 'AP_A_family', 'AP_A_genus', 'AP_B_species', 'AP_B_variety', 'AP_3_CI', 0),
            (5003,NOW(),NOW(), 'AP_A_class', 'AP_A_order', 'AP_A_family', 'AP_A_genus', 'AP_A_species', 'AP_B_variety', 'AP_2_CI', 0),
            (5004,NOW(),NOW(), 'AP_C_class', 'AP_C_order', 'AP_C_family', 'AP_C_genus', 'AP_C_species', 'AP_C_variety', 'AP_8_CI', 0),
            (5005,NOW(),NOW(), 'AP_A_class', 'AP_A_order', 'AP_A_family', 'AP_B_genus', 'AP_B_species', 'AP_B_variety', 'AP_4_CI', 0),
            (5006,NOW(),NOW(), 'AP_A_class', 'AP_B_order', 'AP_B_family', 'AP_B_genus', 'AP_B_species', 'AP_B_variety', 'AP_6_CI', 0),
            (5007,NOW(),NOW(), 'AP_B_class', 'AP_B_order', 'AP_B_family', 'AP_B_genus', 'AP_B_species', 'AP_B_variety', 'AP_7_CI', 0),
            (5008,NOW(),NOW(), 'AP_A_class', 'AP_A_order', 'AP_B_family', 'AP_B_genus', 'AP_B_species', 'AP_B_variety', 'AP_5_CI', 0)
        ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test Authoritative_Plant data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

    function createTestData_Authoritative_Plant_Extras($dbConn) {
        // 5100 series ids
        # 'authoritative_plant_extra_id', 'created_at', 'updated_at', 'authoritative_plant_id', 'type', 'value', 'ordering', 'flag_delete'
        # VALID_TYPES = ['common name', 'description', 'image'];
        $addTestSql  = "INSERT INTO " . Authoritative_Plant_Extra::$dbTable . " VALUES
                (5101,NOW(),NOW(), 5001, 'common name', 'AP_A common z chestnut', 1, 0),
                (5102,NOW(),NOW(), 5001, 'common name', 'AP_A common a american chestnut', 2, 0),
                (5103,NOW(),NOW(), 5001, 'common name', 'AP_A common y achestnut', 1, 0),
                (5104,NOW(),NOW(), 5001, 'description', 'description of american chestnut', 1, 0),
                (5105,NOW(),NOW(), 5001, 'image', 'testing/castanea_dentata.jpg', 2, 0),
                (5106,NOW(),NOW(), 5001, 'image', 'https://www.flickr.com/photos/plussed/14761853313', 1, 0),
                (5107,NOW(),NOW(), 5008, 'common name', 'AP_C common beebalm', 5, 0),
                (5108,NOW(),NOW(), 5008, 'image', 'https://www.flickr.com/photos/plussed/213953635', 1, 0),
                (5109,NOW(),NOW(), 5008, 'image', 'https://www.flickr.com/photos/plussed/213954024', 1, 0)
            ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test createTestData_Authoritative_Plant_Extra data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

    function createTestData_Metadata_Structures($dbConn) {
        // 6000 series ids
        # Metadata_Structure: 'metadata_structure_id', 'created_at', 'updated_at', 'parent_metadata_structure_id', 'name', 'ordering', 'description', 'details', 'metadata_term_set_id', 'flag_delete'
        $addTestSql  = "INSERT INTO " . Metadata_Structure::$dbTable . " VALUES
            (6001,NOW(),NOW(), 0, 'flower', 1, 'info about the flower', '', 0, 0),
            (6002,NOW(),NOW(), 6001, 'flower size', 0.5, 'the size of the flower in its largest dimension', '', 6101, 0),
            (6003,NOW(),NOW(), 6001, 'flower primary color', .75, 'the primary / dominant color of the flower', '', 6102, 0),
            (6004,NOW(),NOW(), 0, 'leaf', 0.5, 'info about the individual leaves of the plant', 'details', 0, 0)
        ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test Metadata_Structure data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

    function createTestData_Metadata_Term_Sets($dbConn) {
        // 6100 series ids
        # Metadata_Term_Set: 'metadata_term_set_id', 'created_at', 'updated_at', 'name', 'ordering', 'description', 'flag_delete'
        $addTestSql  = "INSERT INTO " . Metadata_Term_Set::$dbTable . " VALUES
            (6101,NOW(),NOW(), 'small lengths', 1, 'lengths ranging from 3 mm to 30 cm', 0),
            (6102,NOW(),NOW(), 'colors', 2, 'basic colors', 0),
            (6103,NOW(),NOW(), 'margin styles', 1, 'the shape / pattern of an edge', 0),
            (6104,NOW(),NOW(), 'habitats', 4, 'general kinds of places plants live (no terms)', 0)
        ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test Metadata_Term_Set data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

    function createTestData_Metadata_Term_Values($dbConn) {
        // 6200 series ids
        # Metadata_Term_Value: 'metadata_term_value_id', 'created_at', 'updated_at', 'metadata_term_set_id', 'name', 'ordering', 'description', 'flag_delete'
        $addTestSql  = "INSERT INTO " . Metadata_Term_Value::$dbTable . " VALUES
            (6201,NOW(),NOW(), 6101, '< 3 mm', 1, 'less than the thickness of 3 pennies', 0),
            (6202,NOW(),NOW(), 6101, '3 mm - 1cm', 2, 'smaller than the smallest thickness of your pinkie finger', 0),
            (6203,NOW(),NOW(), 6101, '1-3 cm', 3, 'up to the largest thickness of your thumb', 0),
            (6204,NOW(),NOW(), 6101, '3-6 cm', 4, 'up to the thicness of 3 fingers', 0),
            (6205,NOW(),NOW(), 6101, '6-12 cm', 5, 'up to the thicness of 2 fists side face to face', 0),
            (6206,NOW(),NOW(), 6101, '12-20 cm', 6, 'up to the thicness of 2 fists pinkie to pinkie (palms up)', 0),
            (6207,NOW(),NOW(), 6101, '20-30 cm', 7, 'up to the length of your forearm', 0),
            (6208,NOW(),NOW(), 6101, '> 30 cm', 8, 'bigger than that', 0),
            (6209,NOW(),NOW(), 6103, 'serrate', 1, 'teeth forward pointing - 1 level / degree of teeth', 0),
            (6210,NOW(),NOW(), 6103, 'dentate', 1, 'teeth outward pointing - 1 level / degree of teeth', 0),
            (6211,NOW(),NOW(), 6102, 'red', 3, '', 0),
            (6212,NOW(),NOW(), 6102, 'green', 1, '', 0),
            (6213,NOW(),NOW(), 6102, 'blue', 2, '', 0)
        ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test Metadata_Term_Value data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

    function createTestData_Metadata_References($dbConn) {
        // 6300 series ids
        # Metadata_Reference: 'metadata_reference_id', 'created_at', 'updated_at', 'metadata_type', 'metadata_id', 'type', 'external_reference', 'description', 'ordering', 'flag_delete'
        # VALID_METADATA_TYPES = ['structure', 'set', 'value'];
        # VALID_TYPES = ['text', 'image', 'link'];
        $addTestSql  = "INSERT INTO " . Metadata_Reference::$dbTable . " VALUES
            (6301,NOW(),NOW(), 'structure', 6001, 'text', 'testing/flower_descr.txt', 'description of what a flower is', 1, 0),
            (6302,NOW(),NOW(), 'term_set', 6101, 'text', 'testing/small_sizes.txt', 'description of the small sizes', 1, 0),
            (6303,NOW(),NOW(), 'term_value', 6209, 'image', 'testing/red.jpg', 'image of the color red', 1, 0),
            (6304,NOW(),NOW(), 'term_value', 6213, 'link', 'http://dictionary.reference.com/browse/dentate', 'definition of dentate', 1, 0),
            (6305,NOW(),NOW(), 'term_value', 6213, 'image', 'http://cf.ydcdn.net/1.0.1.20/images/main/dentate.jpg', 'picture of dentate', 1, 0),
            (6306,NOW(),NOW(), 'term_value', 6212, 'link', 'http://dictionary.reference.com/browse/serrate', 'definition of serrate', 1, 0),
            (6307,NOW(),NOW(), 'term_value', 6212, 'link', 'http://dictionary.reference.com/browse/serrate', 'another definition of serrate', .5, 0)
        ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test Metadata_Reference data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

    function createTestData_Notebooks($dbConn) {
        // 1000 series ids
        // Notebook: 'notebook_id', 'created_at', 'updated_at', 'user_id', 'name', 'notes', 'flag_workflow_published', 'flag_workflow_validated', 'flag_delete'
        $addTestSql  = "INSERT INTO " . Notebook::$dbTable . " VALUES
            (1001,NOW(),NOW(),101,'testnotebook1','this is testnotebook1, owned by user 101', 0, 0, 0),
            (1002,NOW(),NOW(),101,'testnotebook2','this is testnotebook2, owned by user 101', 0, 0, 0),
            (1003,NOW(),NOW(),102,'testnotebook3','this is testnotebook3, owned by user 102', 0, 0, 0),
            (1004,NOW(),NOW(),110,'testnotebook4','this is generally viewable testnotebook4, owned by user 110', 1, 1, 0)
        ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test Notebooks data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

    function createTestData_Notebook_Pages($dbConn) {
        // 1100 series ids
        // Notebook_Page: 'notebook_page_id', 'created_at', 'updated_at', 'notebook_id', 'authoritative_plant_id', 'notes', 'flag_workflow_published', 'flag_workflow_validated', 'flag_delete'
        $addTestSql  = "INSERT INTO " . Notebook_Page::$dbTable . " VALUES
                (1101,NOW(),NOW(), 1001, 5001, 'testing notebook page the first in testnotebook1, owned by user 101', 0, 0, 0),
                (1102,NOW(),NOW(), 1001, 5008, 'second testing notebook page in testnotebook1, owned by user 101', 0, 0, 0),
                (1103,NOW(),NOW(), 1002, 5001, 'first page of testnotebook2, owned by user 101', 0, 0, 0),
                (1104,NOW(),NOW(), 1004, 5001, 'first page of testnotebook4, owned by user 110', 0, 0, 0)
            ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test Notebook_Pages data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

    function createTestData_Notebook_Page_Fields($dbConn) {
        // 1200 series ids
        // Notebook_Page_Field: 'notebook_page_field_id', 'created_at', 'updated_at', 'notebook_page_id', 'label_metadata_structure_id', 'value_metadata_term_value_id', 'value_open', 'flag_delete'
        $addTestSql  = "INSERT INTO " . Notebook_Page_Field::$dbTable . " VALUES
                    (1201,NOW(),NOW(), 1101, 6002, 6202, '', 0),
                    (1202,NOW(),NOW(), 1101, 6002, 6203, '', 0),
                    (1203,NOW(),NOW(), 1101, 6003, 6211, '', 0),
                    (1204,NOW(),NOW(), 1101, 6004, 0, 'wavy-ish', 0),
                    (1205,NOW(),NOW(), 1104, 6002, 6205, 'rare', 0)
                ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test Notebook_Page_Fields data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

    function createTestData_Role_Action_Targets($dbConn) {
        // 200 series ids
        // Role_Action_Target: 'role_action_target_link_id', 'created_at', 'updated_at', 'last_user_id', 'role_id', 'action_id', 'target_type', 'target_id', 'flag_delete'
        // VALID_TARGET_TYPES = ['global_notebook', 'global_metadata', 'global_plants', 'global_specimens', 'notebook', 'metadata', 'plant', 'specimen'];
        $addTestSql  = "INSERT INTO " . Role_Action_Target::$dbTable . " VALUES
                        (201,NOW(),NOW(), 110, 2, 1, 'global_notebook', 0, 0),
                        (202,NOW(),NOW(), 110, 2, 2, 'global_notebook', 0, 0),
                        (203,NOW(),NOW(), 110, 2, 2, 'global_metadata', 0, 0),
                        (204,NOW(),NOW(), 110, 2, 2, 'global_plant', 0, 0),
                        (205,NOW(),NOW(), 110, 2, 2, 'global_specimen', 0, 0),
                        (206,NOW(),NOW(), 110, 2, 1, 'global_metadata', 0, 0),
                        (207,NOW(),NOW(), 110, 3, 1, 'global_metadata', 0, 0),
                        (208,NOW(),NOW(), 110, 4, 1, 'global_metadata', 0, 0),
                        (209,NOW(),NOW(), 110, 2, 1, 'global_plant', 0, 0),
                        (210,NOW(),NOW(), 110, 3, 1, 'global_plant', 0, 0),
                        (211,NOW(),NOW(), 110, 4, 1, 'global_plant', 0, 0),
                        (212,NOW(),NOW(), 110, 3, 1, 'notebook', 1004, 0),
                        (213,NOW(),NOW(), 110, 4, 1, 'notebook', 1004, 0),
                        (214,NOW(),NOW(), 110, 2, 3, 'global_metadata', 0, 0),
                        (215,NOW(),NOW(), 110, 2, 3, 'global_notebook', 0, 0),
                        (216,NOW(),NOW(), 110, 2, 3, 'global_specimen', 0, 0),
                        (217,NOW(),NOW(), 110, 2, 4, 'global_metadata', 0, 0),
                        (218,NOW(),NOW(), 110, 2, 4, 'global_notebook', 0, 0),
                        (219,NOW(),NOW(), 110, 2, 4, 'global_specimen', 0, 0),
                        (220,NOW(),NOW(), 110, 3, 4, 'global_notebook', 0, 0),
                        (221,NOW(),NOW(), 110, 3, 4, 'global_specimen', 0, 0)
                    ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test Role_Action_Targets data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }


    function createTestData_Specimens($dbConn) {
        // 8000 series ids
        # Specimen: 'specimen_id', 'created_at', 'updated_at', 'user_id', 'link_to_type', 'link_to_id', 'name', 'gps_longitude', 'gps_latitude', 'notes', 'ordering', 'catalog_identifier', 'flag_workflow_published', 'flag_workflow_validated', 'flag_delete'
        # VALID_LINK_TO_TYPES =  ['authoritative_plant', 'notebook_page'];
        $addTestSql  = "INSERT INTO " . Specimen::$dbTable . " VALUES
            (8001,NOW(),NOW(), 110, 'authoritative_plant', 5001, 'sci quad authoritative', -73.2054918, 42.7118454, 'notes on authoritative specimen', 1, '1a', 1, 1, 0),
            (8002,NOW(),NOW(), 101, 'notebook_page', 1101, 'sci quad 1 notebook page 1', -73.2054918, 42.7118454, 'notes 1.1 on notebook specimen', 2.5, '1n1.1', 0, 1, 0),
            (8003,NOW(),NOW(), 101, 'notebook_page', 1101, 'sci quad 2 notebook page 1', -73.2054919, 42.7118455, 'notes 1.2 on notebook specimen', 2, '1n1.2', 0, 0, 0),
            (8004,NOW(),NOW(), 101, 'notebook_page', 1102, 'sci quad 1 notebook page 2', -73.2054918, 42.7118454, 'notes 2.1 on notebook specimen', 1, '2n1.1', 0, 0, 0)
        ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test Specimen data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

    function createTestData_Specimen_Images($dbConn) {
        // 8100 series ids
        # Specimen_Image: 'specimen_image_id', 'created_at', 'updated_at', 'specimen_id', 'user_id', 'image_reference', 'ordering', 'flag_workflow_published', 'flag_workflow_validated', 'flag_delete'
        $addTestSql  = "INSERT INTO " . Specimen_Image::$dbTable . " VALUES
            (8101,NOW(),NOW(), 8001, 110, 'testing/cnh_castanea_dentata.jpg', 1, 1, 1, 0),
            (8102,NOW(),NOW(), 8001, 110, 'https://www.flickr.com/photos/plussed/14761853313', .5, 1, 1, 0),
            (8103,NOW(),NOW(), 8002, 101, 'testing/USER101_8103_cnh_castanea_dentata.jpg', .75, 0, 1, 0),
            (8104,NOW(),NOW(), 8002, 101, 'testing/USER101_8103_a_nonexistent_file.jpg', .75, 0, 1, 0)
        ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test Specimen_Image data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

    function createTestData_Users($dbConn) {
        // 100 series ids
        # user: user_id, created_at, updated_at, username, screen_name, flag_is_system_admin, flag_is_banned, flag_delete
        // 101-104 are field user
        // 105 is assistant
        // 106-108 are field user
        // 109 has no roles (implicit 'public' role)
        // 110 is manager
        // 111 is field user
        $addTestSql  = "INSERT INTO " . User::$dbTable . " VALUES
            (101,NOW(),NOW(),'" . Auth_Base::$TEST_USERNAME . "','" . Auth_Base::$TEST_LNAME . ", " . Auth_Base::$TEST_FNAME . "',0,0,0),
            (102,NOW(),NOW(),'testUser2','tu2L, tu2F',0,0,0),
            (103,NOW(),NOW(),'testUser3','tu3L, tu3F',0,0,0),
            (104,NOW(),NOW(),'testUser4','tu4L, tu4F',0,0,0),
            (105,NOW(),NOW(),'testUser5','tu5L, tu5F',0,0,0),
            (106,NOW(),NOW(),'testUser6','tu6L, tu6F',1,0,0),
            (107,NOW(),NOW(),'testUser7','tu7L, tu7F',0,1,0),
            (108,NOW(),NOW(),'testUser8','tu8L, tu8F',0,0,1),
            (110,NOW(),NOW(),'testUser9','tu9L, tu9F',0,0,0),
            (109,NOW(),NOW(),'testUser10','tu10L, tu10F',0,0,0)
        ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test Users data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

    function makeAuthedTestUserAdmin($dbConn) {
        $u1                       = User::getOneFromDb(['username' => TESTINGUSER], $dbConn);
        $u1->flag_is_system_admin = TRUE;
        $u1->updateDb();
    }

    function createTestData_User_Roles($dbConn) {
        // 300 series ids
        # User_Role: 'user_role_link_id', 'created_at', 'updated_at', 'last_user_id', 'user_id', 'role_id'
        $addTestSql  = "INSERT INTO " . User_Role::$dbTable . " VALUES
            (301,NOW(),NOW(),110,101,3),
            (302,NOW(),NOW(),110,102,3),
            (303,NOW(),NOW(),110,103,3),
            (304,NOW(),NOW(),110,104,3),
            (305,NOW(),NOW(),110,105,2),
            (306,NOW(),NOW(),110,106,3),
            (307,NOW(),NOW(),110,107,3),
            (308,NOW(),NOW(),110,110,1),
            (309,NOW(),NOW(),110,108,3)
        ";
        $addTestStmt = $dbConn->prepare($addTestSql);
        $addTestStmt->execute();
        if ($addTestStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test User_Role data to the DB\n";
            print_r($addTestStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

//--------------------------------------------------------------------------------------------------------------

	function createAllTestData($dbConn) {
        createTestData_Authoritative_Plants($dbConn);
        createTestData_Authoritative_Plant_Extras($dbConn);
        createTestData_Metadata_Structures($dbConn);
        createTestData_Metadata_Term_Sets($dbConn);
        createTestData_Metadata_Term_Values($dbConn);
        createTestData_Metadata_References($dbConn);
        createTestData_Notebooks($dbConn);
        createTestData_Notebook_Pages($dbConn);
        createTestData_Notebook_Page_Fields($dbConn);
        createTestData_Role_Action_Targets($dbConn);
        createTestData_Specimens($dbConn);
        createTestData_Specimen_Images($dbConn);
        createTestData_Users($dbConn);
        createTestData_User_Roles($dbConn);

        $all_actions = Action::getAllFromDb([],$dbConn);
        global $ACTIONS;
        foreach ($all_actions as $a) {
            $ACTIONS[$a->name] = $a;
        }
	}

//--------------------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------------------

	function _removeTestDataFromTable($dbConn, $tableName) {
		$sql = "DELETE FROM $tableName";
		//echo "<pre>" . $sql . "\n</pre>";
		$stmt = $dbConn->prepare($sql);
		$stmt->execute();
	}

    function removeTestData_Authoritative_Plants($dbConn) {
        _removeTestDataFromTable($dbConn, Authoritative_Plant::$dbTable);
    }
    function removeTestData_Authoritative_Plant_Extras($dbConn) {
     _removeTestDataFromTable($dbConn, Authoritative_Plant_Extra::$dbTable);
    }
    function removeTestData_Metadata_References($dbConn) {
        _removeTestDataFromTable($dbConn, Metadata_Reference::$dbTable);
    }
    function removeTestData_Metadata_Structures($dbConn) {
        _removeTestDataFromTable($dbConn, Metadata_Structure::$dbTable);
    }
    function removeTestData_Metadata_Term_Sets($dbConn) {
        _removeTestDataFromTable($dbConn, Metadata_Term_Set::$dbTable);
    }
    function removeTestData_Metadata_Term_Values($dbConn) {
        _removeTestDataFromTable($dbConn, Metadata_Term_Value::$dbTable);
    }
    function removeTestData_Notebooks($dbConn) {
        _removeTestDataFromTable($dbConn, Notebook::$dbTable);
    }
    function removeTestData_Notebook_Pages($dbConn) {
        _removeTestDataFromTable($dbConn, Notebook_Page::$dbTable);
    }
    function removeTestData_Notebook_Page_Fields($dbConn) {
        _removeTestDataFromTable($dbConn, Notebook_Page_Field::$dbTable);
    }
    function removeTestData_Role_Action_Targets($dbConn) {
        $sql = "DELETE FROM ".Role_Action_Target::$dbTable." WHERE ".Role_Action_Target::$primaryKeyField." > 100";
        //echo "<pre>" . $sql . "\n</pre>";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute();
    }
    function removeTestData_Specimens($dbConn) {
        _removeTestDataFromTable($dbConn, Specimen::$dbTable);
    }
    function removeTestData_Specimen_Images($dbConn) {
        _removeTestDataFromTable($dbConn, Specimen_Image::$dbTable);
    }
    function removeTestData_Users($dbConn) {
        $sql = "DELETE FROM ".User::$dbTable." WHERE ".User::$primaryKeyField." > 1";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute();
    }
    function removeTestData_User_Roles($dbConn) {
        $sql = "DELETE FROM ".User_Role::$dbTable." WHERE ".User_Role::$primaryKeyField." > 1";
        $stmt = $dbConn->prepare($sql);
        $stmt->execute();
    }

//--------------------------------------------------------------------------------------------------------------

	function removeAllTestData($dbConn) {
        removeTestData_Authoritative_Plants($dbConn);
        removeTestData_Authoritative_Plant_Extras($dbConn);
        removeTestData_Metadata_References($dbConn);
        removeTestData_Metadata_Structures($dbConn);
        removeTestData_Metadata_Term_Sets($dbConn);
        removeTestData_Metadata_Term_Values($dbConn);
        removeTestData_Notebooks($dbConn);
        removeTestData_Notebook_Pages($dbConn);
        removeTestData_Notebook_Page_Fields($dbConn);
        removeTestData_Role_Action_Targets($dbConn);
        removeTestData_Specimens($dbConn);
        removeTestData_Specimen_Images($dbConn);
        removeTestData_Users($dbConn);
        removeTestData_User_Roles($dbConn);

	}