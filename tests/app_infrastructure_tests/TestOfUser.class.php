<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';
	require_once dirname(__FILE__) . '/../../classes/auth_base.class.php';

	Mock::generate('Auth_Base');

	class TestOfUser extends WMSUnitTestCaseDB {

		public $auth;

		function setUp() {
			createAllTestData($this->DB);

			$this->auth              = new MockAuth_Base();
			$this->auth->username    = Auth_Base::$TEST_USERNAME;
			$this->auth->email       = Auth_Base::$TEST_EMAIL;
			$this->auth->fname       = Auth_Base::$TEST_FNAME;
			$this->auth->lname       = Auth_Base::$TEST_LNAME;
			$this->auth->sortname    = Auth_Base::$TEST_SORTNAME;
			$this->auth->inst_groups = array_slice(Auth_Base::$TEST_INST_GROUPS, 0);
			$this->auth->msg         = '';
			$this->auth->debug       = '';
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		function testUserAtributesExist() {
			$this->assertEqual(count(User::$fields), 8);

			$this->assertTrue(in_array('user_id', User::$fields));
            $this->assertTrue(in_array('created_at', User::$fields));
            $this->assertTrue(in_array('updated_at', User::$fields));
			$this->assertTrue(in_array('username', User::$fields));
            $this->assertTrue(in_array('screen_name', User::$fields));
            $this->assertTrue(in_array('flag_is_system_admin', User::$fields));
			$this->assertTrue(in_array('flag_is_banned', User::$fields));
			$this->assertTrue(in_array('flag_delete', User::$fields));
		}

		//// static methods

		function testCmp() {
            $u1 = new User(['user_id' => 50, 'screen_name' => 'jones, fred', 'DB' => $this->DB]);
            $u2 = new User(['user_id' => 50, 'screen_name' => 'albertson, fred', 'DB' => $this->DB]);
            $u3 = new User(['user_id' => 50, 'screen_name' => 'ji, al', 'DB' => $this->DB]);
            $u4 = new User(['user_id' => 50, 'screen_name' => 'ji, bab', 'DB' => $this->DB]);

			$this->assertEqual(User::cmp($u1, $u2), 1);
			$this->assertEqual(User::cmp($u1, $u1), 0);
			$this->assertEqual(User::cmp($u2, $u1), -1);

			$this->assertEqual(User::cmp($u3, $u4), -1);
		}


		//// DB interaction tests

		function testUserDBInsert() {
			$u = new User(['user_id' => 50, 'username' => 'fjones', 'screen_name' => 'jones, fred', 'DB' => $this->DB]);

			$u->updateDb();

			$u2 = User::getOneFromDb(['user_id' => 50], $this->DB);

			$this->assertTrue($u2->matchesDb);
			$this->assertEqual($u2->username, 'fjones');
		}

		function testUserRetrievedFromDb() {
			$u = new User(['user_id' => 101, 'DB' => $this->DB]);
			$this->assertNull($u->username);

			$u->refreshFromDb();
			$this->assertEqual($u->username, Auth_Base::$TEST_USERNAME);
		}

        //// instance methods - object itself

        function testUserRenderMinimal() {
            $u = User::getOneFromDb(['user_id' => 101], $this->DB);

            $info = '<div class="rendered-object user-render user-render-minimal user-render-101" data-for-user="101">'.Auth_Base::$TEST_LNAME.', '.Auth_Base::$TEST_FNAME.'</div>';

//            util_prePrintR($info);
//            util_prePrintR($u->render());
//
//            exit;

            $this->assertEqual($u->renderMinimal(),$info);
        }

        function testUserRender() {
            $u = User::getOneFromDb(['user_id' => 101], $this->DB);

            $info = '<div class="rendered-object user-render user-render-minimal user-render-101" data-for-user="101">'.Auth_Base::$TEST_LNAME.', '.Auth_Base::$TEST_FNAME.'</div>';

            $this->assertEqual($u->render(),$info);
        }

        function testUserRenderRich() {
            $u = User::getOneFromDb(['user_id' => 101], $this->DB);

            $info = '<div class="rendered-object user-render user-render-minimal user-render-101" data-for-user="101">'.Auth_Base::$TEST_LNAME.', '.Auth_Base::$TEST_FNAME.'</div>';

            $rendered = $u->renderRich();

            $this->assertEqual($rendered,$info);
        }

        //// instance methods - related data

        function testGetAccessibleNotebooksBasic() {
            $u = User::getOneFromDb(['user_id' => 101], $this->DB);
            $notebooks = $u->getAccessibleNotebooks();

            $this->assertEqual(2,count($notebooks),'number of notebooks mismatch');
            $this->assertEqual(1001,$notebooks[0]->notebook_id,'notebook id mismatch');
            $this->assertEqual(1002,$notebooks[1]->notebook_id,'notebook id mismatch');
        }

        function testGetAccessibleNotebooksSystemAdmin() {
            makeAuthedTestUserAdmin($this->DB);
            $u = User::getOneFromDb(['user_id' => 101], $this->DB);
            $notebooks = $u->getAccessibleNotebooks();

            $this->assertEqual(4,count($notebooks),'number of notebooks mismatch: 3 vs '.count($notebooks));
            $this->assertEqual(1001,$notebooks[0]->notebook_id,'notebook id mismatch');
            $this->assertEqual(1002,$notebooks[1]->notebook_id,'notebook id mismatch');
            $this->assertEqual(1003,$notebooks[2]->notebook_id,'notebook id mismatch');
            $this->assertEqual(102,$notebooks[2]->user_id,'notebook user id mismatch');
        }

        function testGetAccessibleNotebooksManager() {
            $this->fail("TODO: create test for testGetAccessibleNotebooksManager");
        }

        function testGetAccessibleNotebooksFieldUser() {
            $this->fail("TODO: create test for testGetAccessibleNotebooksFieldUser");
        }

        //// auth-related tests

		function testUserUpdatesBaseDbWhenValidAuthDataIsDifferent() {
			$u = User::getOneFromDb(['user_id' => 101], $this->DB);
			$this->assertEqual($u->username, Auth_Base::$TEST_USERNAME);
			$this->assertTrue($u->matchesDb);

            $this->auth->lname       = 'Newlastname';
            $this->auth->screen_name       = $this->auth->lname.", ".$this->auth->fname;

			$u->updateDbFromAuth($this->auth);

			$this->assertEqual($u->screen_name, $this->auth->screen_name);
			$this->assertTrue($u->matchesDb);

			$u2 = User::getOneFromDb(['user_id' => 101], $this->DB);
			$this->assertEqual($u2->username, Auth_Base::$TEST_USERNAME);
			$this->assertEqual($u2->screen_name, $this->auth->screen_name);
		}

		function testUserUpdatesBaseDbWhenAuthDataIsInvalid() {
			$u                 = User::getOneFromDb(['user_id' => 101], $this->DB);
			$this->auth->fname = '';

			$status = $u->updateDbFromAuth($this->auth);

			// should let caller/program know there's a problem
			$this->assertFalse($status);
		}

		function testNewUserBaseRecordCreatedWhenAuthDataIsForNewUser() {
			$u                 = User::getOneFromDb(['user_id' => 101], $this->DB);
			$this->auth->fname = '';

			$status = $u->updateDbFromAuth($this->auth);

			// should let caller/program know there's a problem
			$this->assertFalse($status);
		}

    }