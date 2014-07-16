<?php
	require_once dirname(__FILE__) . '/../classes/auth_base.class.php';
	require_once dirname(__FILE__) . '/../classes/auth_LDAP.class.php';

	require_once dirname(__FILE__) . '/../classes/permission.class.php';
	require_once dirname(__FILE__) . '/../classes/role.class.php';
	require_once dirname(__FILE__) . '/../classes/user.class.php';

	/*
	This file contains a series of methods for creating known test data in a target database
	*/


	function createTestData_Permissions($dbConn) {
		# Permission[user|inst_group]: permission_id, entity_id, entity_type, role_id, eq_group_id, flag_delete
		# NOTE: no one has access to eqg8
		$addTestPermissionSql  = "INSERT INTO " . Permission::$dbTable . " VALUES
        (701,1101,'user',     2,202,0), # user1 user access eqg2
        (702,1101,'user',     2,201,0), # user1 user access eqg1 (flipped to test ordering functions)
        (703,1101,'user',     1,203,0), # user1 manager access eqg3
        (704,1101,'user',     2,204,1), # user1 deleted access eqg4
        (705,1101,'user',     2,205,0), # user1 user access deleted eqg5
        (706,1101,'user',     2,207,0), # user1 user access to eqg 7
        (707,501,'inst_group',1,201,0), # ig1 has manager access to eqg1 (overrides user1 eqg1 user access)
        (708,501,'inst_group',2,202,0), # ig1 has user access to eqg2 (dual user access on user1 eqg2)
        (709,501,'inst_group',2,203,0), # ig1 has user access to eqg3 (overridden by user1 eqg2 manager access)
        (710,501,'inst_group',2,206,0), # ig1 has user access to eqg6 (gives user1 indirect user access)
        (711,502,'inst_group',2,201,0), # ig2 has user access to eqg1
        (712,502,'inst_group',2,204,0), # ig2 has user access to eqg4
        (713,502,'inst_group',2,207,1), # ig2 has deleted access to eqg7
        (714,1103,'user',     2,201,0), # deleted user3
        (715,504,'inst_group',2,206,0), # deleted instgroup4
        (716,1102,'user',     2,207,0), # user2 user access to eqg 7
        (717,1102,'user',     1,202,0), # user2 manager access to eqg 2
        (718,1102,'user',     2,201,0), # user2 user access to eqg 1
        (719,1106,'user',     1,201,0)  # user6 manager access to eqg 1
    ";
		$addTestPermissionStmt = $dbConn->prepare($addTestPermissionSql);
		$addTestPermissionStmt->execute();
		if ($addTestPermissionStmt->errorInfo()[0] != '0000') {
			echo "<pre>error adding test Permissions data to the DB\n";
			print_r($addTestPermissionStmt->errorInfo());
			debug_print_backtrace();
			exit;
		}
	}

	function createTestData_Users($dbConn) {
		// 1100 series ids
		# user: user_id, username, fname, lname, sortname, email, advisor, notes, flag_is_system_admin, flag_is_banned, flag_delete
		$addTestUserSql  = "INSERT INTO " . User::$dbTable . " VALUES
        (1101,'" . Auth_Base::$TEST_USERNAME . "','" . Auth_Base::$TEST_FNAME . "','" . Auth_Base::$TEST_LNAME . "','" . Auth_Base::$TEST_SORTNAME . "','" . Auth_Base::$TEST_EMAIL . "','David Keiser-Clark','some important notes',0,0,0),
        (1102,'testUser2','tu2F','tu2L','tu2L, tu2F','tu2@inst.edu','tu2Advisor','tu2 notes',0,0,0),
        (1103,'testUser3deleted','tu3F','tu3L','tu3L, tu3F','tu3@inst.edu','tu3Advisor','tu3 notes',0,0,1),
        (1104,'testUser4banned','tu4F','tu4L','tu4L, tu4F','tu4@inst.edu','tu4Advisor','tu4 notes',0,1,0),
        (1105,'testUser5SystemAdmin','tu5F','tu5L','tu5L, tu5F','tu5@inst.edu','tu5Advisor','tu5 notes',1,0,0),
        (1106,'testUser6','tu6F','tu6L','tu6L, tu6F','tu6@inst.edu','tu6Advisor','tu6 notes',0,0,0),
        (1107,'testUser7','tu7F','tu7L','tu7L, tu7F','tu7@inst.edu','tu7Advisor','tu7 notes',0,0,0)
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

	function createAllTestData($dbConn) {
		createTestData_Permissions($dbConn);
		createTestData_Users($dbConn);
	}

	//------------

	function _removeTestDataFromTable($dbConn, $tableName) {
		$sql = "DELETE FROM $tableName";
		//echo "<pre>" . $sql . "\n</pre>";
		$stmt = $dbConn->prepare($sql);
		$stmt->execute();
	}

	function removeTestData_Permissions($dbConn) {
		_removeTestDataFromTable($dbConn, Permission::$dbTable);
	}
	function removeTestData_Users($dbConn) {
		_removeTestDataFromTable($dbConn, User::$dbTable);
	}


	function removeAllTestData($dbConn) {
		removeTestData_Permissions($dbConn);
		removeTestData_Users($dbConn);
	}