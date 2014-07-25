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
    # user: user_id, created_at, updated_at, username, screen_name, flag_is_system_admin, flag_is_banned, flag_delete
    $addTestSql  = "INSERT INTO " . User::$dbTable . " VALUES
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

    function createTestData_Notebooks($dbConn) {
        $addTestNotebookSql  = "INSERT INTO " . Notebook::$dbTable . " VALUES
            (1001,NOW(),NOW(),101,'testnotebook1','this is testnotebook1, owned by user 101', 0),
            (1002,NOW(),NOW(),101,'testnotebook2','this is testnotebook2, owned by user 101', 0),
            (1003,NOW(),NOW(),102,'testnotebook3','this is testnotebook3, owned by user 102', 0),
            (1004,NOW(),NOW(),111,'testnotebook4','this is testnotebook4, owned by user 111', 0)
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
        createTestData_Metadata_References($dbConn);
        createTestData_Metadata_Structures($dbConn);
        createTestData_Metadata_Term_Sets($dbConn);
        createTestData_Metadata_Term_Values($dbConn);
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