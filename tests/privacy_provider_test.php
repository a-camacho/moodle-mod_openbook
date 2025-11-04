<?php
// This file is part of mod_openbook for Moodle - http://moodle.org/
//
// It is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit Tests for mod/openbook's privacy providers!
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.PHPUnit.TestCaseNames.UnexpectedLevel2NS

namespace mod_openbook\local\tests;

use mod_openbook\privacy\provider;
use context_module;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/openbook/locallib.php');

/**
 * Unit Tests for mod/openbook's privacy providers! TODO: finish these unit tests here!
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class privacy_provider_test extends base {
    /** @var stdClass */
    private $course1;
    /** @var stdClass */
    private $course2;
    /** @var stdClass */
    private $group11;
    /** @var stdClass */
    private $group12;
    /** @var stdClass */
    private $group21;
    /** @var stdClass */
    private $group22;
    /** @var stdClass */
    private $user1;
    /** @var stdClass */
    private $user2;
    /** @var stdClass */
    private $user3;
    /** @var stdClass */
    private $teacher1;
    /** @var openbook */
    private $openbookupload;
    /** @var openbook */
    private $openbookupload2;

    /**
     * Set up the common parts of the tests!
     *
     * The base test class already contains a setUp-method setting up a course including users and groups.
     *
     * @throws \coding_exception
     */
    protected function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();

        $this->course1 = self::getDataGenerator()->create_course();
        $this->course2 = self::getDataGenerator()->create_course();
        $this->group11 = self::getDataGenerator()->create_group((object)['courseid' => $this->course1->id]);
        $this->group12 = self::getDataGenerator()->create_group((object)['courseid' => $this->course1->id]);
        $this->group21 = self::getDataGenerator()->create_group((object)['courseid' => $this->course2->id]);
        $this->group22 = self::getDataGenerator()->create_group((object)['courseid' => $this->course2->id]);

        $this->user1 = $this->students[0];
        self::getDataGenerator()->enrol_user($this->user1->id, $this->course1->id, 'student');
        self::getDataGenerator()->enrol_user($this->user1->id, $this->course2->id, 'student');
        $this->user2 = $this->students[1];
        self::getDataGenerator()->enrol_user($this->user2->id, $this->course1->id, 'student');
        self::getDataGenerator()->enrol_user($this->user2->id, $this->course2->id, 'student');
        $this->user3 = $this->students[2];
        self::getDataGenerator()->enrol_user($this->user3->id, $this->course1->id, 'student');
        self::getDataGenerator()->enrol_user($this->user3->id, $this->course2->id, 'student');
        // Need a second user as teacher.
        $this->teacher1 = $this->editingteachers[0];
        self::getDataGenerator()->enrol_user($this->teacher1->id, $this->course1->id, 'editingteacher');
        self::getDataGenerator()->enrol_user($this->teacher1->id, $this->course2->id, 'editingteacher');

        // Prepare groups!
        self::getDataGenerator()->create_group_member((object)['userid' => $this->user1->id, 'groupid' => $this->group11->id]);
        self::getDataGenerator()->create_group_member((object)['userid' => $this->user3->id, 'groupid' => $this->group11->id]);
        self::getDataGenerator()->create_group_member((object)['userid' => $this->user1->id, 'groupid' => $this->group21->id]);
        self::getDataGenerator()->create_group_member((object)['userid' => $this->user3->id, 'groupid' => $this->group21->id]);

        self::getDataGenerator()->create_group_member((object)['userid' => $this->user2->id, 'groupid' => $this->group12->id]);
        self::getDataGenerator()->create_group_member((object)['userid' => $this->user2->id, 'groupid' => $this->group22->id]);

        // Create multiple openbook instances.
        // Openbook resource folder with uploads.
        $this->openbookupload = $this->create_instance([
                'name' => 'Openbook Upload 1',
                'course' => $this->course1,
        ]);
        $this->openbookupload2 = $this->create_instance([
                'name' => 'Openbook Upload 2',
                'course' => $this->course1,
        ]);
    }

    /**
     * Test that getting the contexts for a user works.
     *
     * @covers \mod_openbook\privacy\provider::get_contexts_for_userid
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_get_contexts_for_userid(): void {
        // The user will be in these contexts.
        $usercontextids = [
            $this->openbookupload->get_context()->id,
        ];

        // User 1 uploads in openbookupload1!
        $this->create_upload(
            $this->user1->id,
            $this->openbookupload->get_instance()->id,
            'upload-no-1.txt',
            'This is the first upload here!'
        );
        // User 3 also uploads in general openbook!
        $this->create_upload(
            $this->user3->id,
            $this->openbookupload2->get_instance()->id,
            'upload-no-2.txt',
            'This is another upload in another openbook'
        );

        // Then we check, if user 1 appears only in pubimport1, pubupload1 and pubteamimport1!
        $contextlist = provider::get_contexts_for_userid($this->user1->id);

        $this->assertEquals(count($usercontextids), count($contextlist->get_contextids()));
        // There should be no difference between the contexts.
        $this->assertEmpty(array_diff($usercontextids, $contextlist->get_contextids()));

        $contextlist = provider::get_contexts_for_userid($this->user1->id);
        $this->assertEquals(count($usercontextids), count($contextlist->get_contextids()));
        // There should be no difference between the contexts.
        $this->assertEmpty(array_diff($usercontextids, $contextlist->get_contextids()));

        // phpcs:disable moodle.Commenting.TodoComment
        // TODO: test for group approvals and extended due dates!
    }

    /**
     * Test returning a list of user IDs related to a context.
     *
     * @covers \mod_openbook\privacy\provider::get_users_in_context
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_get_users_in_context(): void {
        // User 1 uploads in openbookupload1!
        $this->create_upload(
            $this->user1->id,
            $this->openbookupload->get_instance()->id,
            'upload-no-1.txt',
            'This is the first upload here!'
        );

        $uploadcm = get_coursemodule_from_instance('openbook', $this->openbookupload->get_instance()->id);
        $uploadctx = context_module::instance($uploadcm->id);
        $userlist = new \core_privacy\local\request\userlist($uploadctx, 'openbook');
        provider::get_users_in_context($userlist);
        $userids = $userlist->get_userids();
        self::assertTrue(in_array($this->user1->id, $userids));
        self::assertFalse(in_array($this->user2->id, $userids));
        self::assertFalse(in_array($this->user3->id, $userids));

        $upload2cm = get_coursemodule_from_instance('openbook', $this->openbookupload2->get_instance()->id);
        $upload2ctx = context_module::instance($upload2cm->id);
        $userlist2 = new \core_privacy\local\request\userlist($upload2ctx, 'openbook');
        provider::get_users_in_context($userlist2);
        $userids2 = $userlist2->get_userids();
        self::assertFalse(in_array($this->user1->id, $userids2));
        self::assertFalse(in_array($this->user2->id, $userids2));
        self::assertFalse(in_array($this->user3->id, $userids2));

        // phpcs:disable moodle.Commenting.TodoComment
        // TODO: check for extended due dates and groupapprovals!
    }

    /**
     * Test that a student with multiple submissions and grades is returned with the correct data.
     *
     * @covers \mod_openbook\privacy\provider::export_user_data_student
     * @return void
     */
    public function test_export_user_data_student(): void {
        // Stop here and mark this test as incomplete.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Tests the data returned for a teacher.
     *
     * @covers \mod_openbook\privacy\provider::export_user_data_teacher
     * @return void
     */
    public function test_export_user_data_teacher(): void {
        // Stop here and mark this test as incomplete.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * A test for deleting all user data for a given context.
     *
     * @covers \mod_openbook\privacy\provider::delete_data_for_all_users_in_context
     * @return void
     */
    public function test_delete_data_for_all_users_in_context(): void {
        // Stop here and mark this test as incomplete.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * A test for deleting all user data for one user.
     *
     * @covers \mod_openbook\privacy\provider::delete_data_for_user
     * @return void
     */
    public function test_delete_data_for_user(): void {
        // Stop here and mark this test as incomplete.
        self::markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * A test for deleting all user data for a bunch of users.
     *
     * @covers \mod_openbook\privacy\provider::delete_data_for_users
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_delete_data_for_users(): void {
        global $DB;

        // User 1 uploads in openbookupload1!
        $this->create_upload(
            $this->user1->id,
            $this->openbookupload->get_instance()->id,
            'upload-no-1.txt',
            'This is the first upload here!'
        );
        $this->create_upload(
            $this->user2->id,
            $this->openbookupload->get_instance()->id,
            'upload-no-2.txt',
            'This is the second upload here!'
        );

        // Test for the data to be in place!
        self::assertEquals(
            2,
            $DB->count_records(
                'openbook_file',
                ['openbook' => $this->openbookupload->get_instance()->id]
            )
        );

        $userlist = new \core_privacy\local\request\approved_userlist(
            $this->openbookupload->get_context(),
            'openbook',
            [$this->user1->id]
        );
        provider::delete_data_for_users($userlist);
        self::assertEquals(
            1,
            $DB->count_records(
                'openbook_file',
                ['openbook' => $this->openbookupload->get_instance()->id]
            )
        );
        provider::delete_data_for_users($userlist);
        $userlist = new \core_privacy\local\request\approved_userlist(
            $this->openbookupload->get_context(),
            'openbook',
            [$this->user1->id, $this->user2->id, $this->user3->id]
        );
        provider::delete_data_for_users($userlist);
        self::assertEquals(
            0,
            $DB->count_records(
                'openbook_file',
                ['openbook' => $this->openbookupload->get_instance()->id],
            )
        );
    }
}
