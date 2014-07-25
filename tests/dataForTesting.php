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

    function createTestData_Authoritative_Plants($dbConn) {
        // 5000 series ids
        # 'authoritative_plant_id', 'created_at', 'updated_at', 'class', 'order', 'family', 'genus', 'species', 'variety', 'catalog_identifier', 'flag_delete'
        $addTestSql  = "INSERT INTO " . Authoritative_Plant::$dbTable . " VALUES
            (5001,NOW(),NOW(), 'AP_A_class', 'AP_A_order', 'AP_A_family', 'AP_A_genus', 'AP_A_species', 'AP_A_variety', 'AP_1_CI', 0),
            (5002,NOW(),NOW(), 'AP_A_class', 'AP_A_order', 'AP_A_family', 'AP_A_genus', 'AP_A_species', 'AP_B_variety', 'AP_2_CI', 0),
            (5003,NOW(),NOW(), 'AP_A_class', 'AP_A_order', 'AP_A_family', 'AP_A_genus', 'AP_B_species', 'AP_B_variety', 'AP_3_CI', 0),
            (5004,NOW(),NOW(), 'AP_A_class', 'AP_A_order', 'AP_A_family', 'AP_B_genus', 'AP_B_species', 'AP_B_variety', 'AP_4_CI', 0),
            (5005,NOW(),NOW(), 'AP_A_class', 'AP_A_order', 'AP_B_family', 'AP_B_genus', 'AP_B_species', 'AP_B_variety', 'AP_5_CI', 0),
            (5006,NOW(),NOW(), 'AP_A_class', 'AP_B_order', 'AP_B_family', 'AP_B_genus', 'AP_B_species', 'AP_B_variety', 'AP_6_CI', 0),
            (5007,NOW(),NOW(), 'AP_B_class', 'AP_B_order', 'AP_B_family', 'AP_B_genus', 'AP_B_species', 'AP_B_variety', 'AP_7_CI', 0),
            (5008,NOW(),NOW(), 'AP_C_class', 'AP_C_order', 'AP_C_family', 'AP_C_genus', 'AP_C_species', 'AP_C_variety', 'AP_8_CI', 0)
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
                (5105,NOW(),NOW(), 5001, 'image', 'authoritative/testing/castanea_dentata.jpg', 2, 0),
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
            (6002,NOW(),NOW(), 6001, 'flower size', 1, 'the size of the flower in its largest dimension', '', 6101, 0),
            (6003,NOW(),NOW(), 6001, 'flower primary color', 2, 'the primary / dominant color of the flower', '', 6102, 0),
            (6004,NOW(),NOW(), 0, 'leaf', .5, 'info about the individual leaves of the plant', 'details', 0, 0)
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
            (6103,NOW(),NOW(), 'margin styles', 3, 'the shape / pattern of an edge', 0),
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
            (6209,NOW(),NOW(), 6102, 'red', 3, '', 0),
            (6210,NOW(),NOW(), 6102, 'green', 2, '', 0),
            (6211,NOW(),NOW(), 6102, 'blue', 1, '', 0),
            (6212,NOW(),NOW(), 6103, 'serrate', 1, 'teeth forward pointing - 1 level / degree of teeth', 0),
            (6213,NOW(),NOW(), 6103, 'dentate', 2, 'teeth outward pointing - 1 level / degree of teeth', 0)
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
            (6302,NOW(),NOW(), 'set', 6101, 'text', 'testing/small_sizes.txt', 'description of the small sizes', 1, 0),
            (6303,NOW(),NOW(), 'value', 6209, 'image', 'testing/red.jpg', 'image of the color red', 1, 0),
            (6304,NOW(),NOW(), 'value', 6213, 'image', 'http://cf.ydcdn.net/1.0.1.20/images/main/dentate.jpg', 'picture of dentate', 1, 0),
            (6305,NOW(),NOW(), 'value', 6212, 'link', 'http://dictionary.reference.com/browse/serrate', 'definition of serrate', 1, 0)
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
        $addTestNotebookSql  = "INSERT INTO " . Notebook::$dbTable . " VALUES
            (1001,NOW(),NOW(),101,'testnotebook1','this is testnotebook1, owned by user 101', 0, 0, 0),
            (1002,NOW(),NOW(),101,'testnotebook2','this is testnotebook2, owned by user 101', 0, 0, 0),
            (1003,NOW(),NOW(),102,'testnotebook3','this is testnotebook3, owned by user 102', 0, 0, 0),
            (1004,NOW(),NOW(),111,'testnotebook4','this is testnotebook4, owned by user 111', 0, 0, 0)
        ";
        $addTestNotebookStmt = $dbConn->prepare($addTestNotebookSql);
        $addTestNotebookStmt->execute();
        if ($addTestNotebookStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test Notebooks data to the DB\n";
            print_r($addTestNotebookStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

    function createTestData_Users($dbConn) {
        // 1100 series ids
        # user: user_id, created_at, updated_at, username, screen_name, flag_is_system_admin, flag_is_banned, flag_delete
        $addTestUserSql  = "INSERT INTO " . User::$dbTable . " VALUES
            (101,NOW(),NOW(),'" . Auth_Base::$TEST_USERNAME . "','" . Auth_Base::$TEST_LNAME . ", " . Auth_Base::$TEST_FNAME . "',0,0,0),
            (102,NOW(),NOW(),'testUser2','tu2L, tu2F',0,0,0),
            (103,NOW(),NOW(),'testUser3','tu3L, tu3F',0,0,0),
            (104,NOW(),NOW(),'testUser4','tu4L, tu4F',0,0,0),
            (105,NOW(),NOW(),'testUser5','tu5L, tu5F',0,0,0),
            (107,NOW(),NOW(),'testUser6','tu6L, tu6F',1,0,0),
            (108,NOW(),NOW(),'testUser7','tu7L, tu7F',0,1,0),
            (109,NOW(),NOW(),'testUser8','tu8L, tu8F',0,0,1),
            (110,NOW(),NOW(),'testUser9','tu9L, tu9F',0,0,0),
            (111,NOW(),NOW(),'testUser10','tu10L, tu10F',0,0,0)
        ";
        $addTestUserStmt = $dbConn->prepare($addTestUserSql);
        $addTestUserStmt->execute();
        if ($addTestUserStmt->errorInfo()[0] != '0000') {
            echo "<pre>error adding test Users data to the DB\n";
            print_r($addTestUserStmt->errorInfo());
            debug_print_backtrace();
            exit;
        }
    }

    function makeAuthedTestUserAdmin($dbConn) {
        $u1                       = User::getOneFromDb(['username' => TESTINGUSER], $dbConn);
        $u1->flag_is_system_admin = TRUE;
        $u1->updateDb();
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
        _removeTestDataFromTable($dbConn, Role_Action_Target::$dbTable);
    }
    function removeTestData_Specimens($dbConn) {
        _removeTestDataFromTable($dbConn, Specimen::$dbTable);
    }
    function removeTestData_Specimen_Images($dbConn) {
        _removeTestDataFromTable($dbConn, Specimen_Image::$dbTable);
    }
    function removeTestData_Users($dbConn) {
        _removeTestDataFromTable($dbConn, User::$dbTable);
    }
    function removeTestData_User_Roles($dbConn) {
        _removeTestDataFromTable($dbConn, User_Role::$dbTable);
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