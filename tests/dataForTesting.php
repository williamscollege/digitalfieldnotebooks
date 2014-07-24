<?php
	require_once dirname(__FILE__) . '/../classes/auth_base.class.php';
	require_once dirname(__FILE__) . '/../classes/auth_LDAP.class.php';

//	require_once dirname(__FILE__) . '/../classes/role.class.php';
require_once dirname(__FILE__) . '/../classes/user.class.php';
require_once dirname(__FILE__) . '/../classes/notebook.class.php';

	/*
	This file contains a series of methods for creating known test data in a target database
	*/


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

//function createTestData_Permissions($dbConn) {
//    # Permission[user|inst_group]: permission_id, entity_id, entity_type, role_id, eq_group_id, flag_delete
//    # NOTE: no one has access to eqg8
//    $addTestPermissionSql  = "INSERT INTO " . Permission::$dbTable . " VALUES
//        (701,1101,'user',     2,202,0), # user1 user access eqg2
//        (702,1101,'user',     2,201,0), # user1 user access eqg1 (flipped to test ordering functions)
//        (703,1101,'user',     1,203,0), # user1 manager access eqg3
//        (704,1101,'user',     2,204,1), # user1 deleted access eqg4
//        (705,1101,'user',     2,205,0), # user1 user access deleted eqg5
//        (706,1101,'user',     2,207,0), # user1 user access to eqg 7
//        (707,501,'inst_group',1,201,0), # ig1 has manager access to eqg1 (overrides user1 eqg1 user access)
//        (708,501,'inst_group',2,202,0), # ig1 has user access to eqg2 (dual user access on user1 eqg2)
//        (709,501,'inst_group',2,203,0), # ig1 has user access to eqg3 (overridden by user1 eqg2 manager access)
//        (710,501,'inst_group',2,206,0), # ig1 has user access to eqg6 (gives user1 indirect user access)
//        (711,502,'inst_group',2,201,0), # ig2 has user access to eqg1
//        (712,502,'inst_group',2,204,0), # ig2 has user access to eqg4
//        (713,502,'inst_group',2,207,1), # ig2 has deleted access to eqg7
//        (714,1103,'user',     2,201,0), # deleted user3
//        (715,504,'inst_group',2,206,0), # deleted instgroup4
//        (716,1102,'user',     2,207,0), # user2 user access to eqg 7
//        (717,1102,'user',     1,202,0), # user2 manager access to eqg 2
//        (718,1102,'user',     2,201,0), # user2 user access to eqg 1
//        (719,1106,'user',     1,201,0)  # user6 manager access to eqg 1
//    ";
//    $addTestPermissionStmt = $dbConn->prepare($addTestPermissionSql);
//    $addTestPermissionStmt->execute();
//    if ($addTestPermissionStmt->errorInfo()[0] != '0000') {
//        echo "<pre>error adding test Permissions data to the DB\n";
//        print_r($addTestPermissionStmt->errorInfo());
//        debug_print_backtrace();
//        exit;
//    }
//}

	function createAllTestData($dbConn) {
		createTestData_Users($dbConn);
        createTestData_Notebooks($dbConn);
//		createTestData_Permissions($dbConn);
	}

	//------------

	function _removeTestDataFromTable($dbConn, $tableName) {
		$sql = "DELETE FROM $tableName";
		//echo "<pre>" . $sql . "\n</pre>";
		$stmt = $dbConn->prepare($sql);
		$stmt->execute();
	}

//	function removeTestData_Permissions($dbConn) {
//		_removeTestDataFromTable($dbConn, Permission::$dbTable);
//	}
	function removeTestData_Users($dbConn) {
		_removeTestDataFromTable($dbConn, User::$dbTable);
	}
    function removeTestData_Notebooks($dbConn) {
        _removeTestDataFromTable($dbConn, Notebook::$dbTable);
    }

	function removeAllTestData($dbConn) {
        removeTestData_Users($dbConn);
        removeTestData_Notebooks($dbConn);
//		removeTestData_Permissions($dbConn);
	}