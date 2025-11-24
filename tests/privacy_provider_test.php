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
use core_privacy\local\request\writer;
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
    private $openbook;
    /** @var openbook */
    private $openbook2;
    /** @var \context context_module of the openbook activity. */
    protected $context;

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
        $this->openbook = $this->create_instance([
                'name' => 'Openbook 1',
                'course' => $this->course1,
        ]);
        $this->openbook2 = $this->create_instance([
                'name' => 'Openbook 2',
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
            $this->openbook->get_context()->id,
        ];

        // User 1 uploads in openbook!
        $this->create_upload(
            $this->user1->id,
            $this->openbook->get_instance()->id,
            'upload-no-1.txt',
            'This is the first upload here!'
        );
        // User 3 also uploads in openbook2!
        $this->create_upload(
            $this->user3->id,
            $this->openbook2->get_instance()->id,
            'upload-no-2.txt',
            'This is another upload in another openbook'
        );

        // Then we check, if user 1 appears only in openbook but not in openbook2!
        $contextlist = provider::get_contexts_for_userid($this->user1->id);

        $this->assertEquals(count($usercontextids), count($contextlist->get_contextids()));
        // There should be no difference between the contexts.
        $this->assertEmpty(array_diff($usercontextids, $contextlist->get_contextids()));

        $contextlist = provider::get_contexts_for_userid($this->user1->id);
        $this->assertEquals(count($usercontextids), count($contextlist->get_contextids()));
        // There should be no difference between the contexts.
        $this->assertEmpty(array_diff($usercontextids, $contextlist->get_contextids()));
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
        // User 1 uploads in openbook!
        $this->create_upload(
            $this->user1->id,
            $this->openbook->get_instance()->id,
            'upload-no-1.txt',
            'This is the first upload here!'
        );

        $uploadcm = get_coursemodule_from_instance('openbook', $this->openbook->get_instance()->id);
        $uploadctx = context_module::instance($uploadcm->id);
        $userlist = new \core_privacy\local\request\userlist($uploadctx, 'openbook');
        provider::get_users_in_context($userlist);
        $userids = $userlist->get_userids();
        self::assertTrue(in_array($this->user1->id, $userids));
        self::assertFalse(in_array($this->user2->id, $userids));
        self::assertFalse(in_array($this->user3->id, $userids));

        $upload2cm = get_coursemodule_from_instance('openbook', $this->openbook2->get_instance()->id);
        $upload2ctx = context_module::instance($upload2cm->id);
        $userlist2 = new \core_privacy\local\request\userlist($upload2ctx, 'openbook');
        provider::get_users_in_context($userlist2);
        $userids2 = $userlist2->get_userids();
        self::assertFalse(in_array($this->user1->id, $userids2));
        self::assertFalse(in_array($this->user2->id, $userids2));
        self::assertFalse(in_array($this->user3->id, $userids2));
    }


    /**
     * The export function should handle an empty contextlist properly.
     *
     * @covers \mod_openbook\privacy\provider::export_user_data
     * @return void
     */
    public function test_export_user_data_no_data(): void {
        global $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $approvedlist = new \core_privacy\tests\request\approved_contextlist(
            \core_user::get_user($USER->id),
            'mod_openbook',
            []
        );

        provider::export_user_data($approvedlist);
        $this->assertDebuggingNotCalled();

        // No data should have been exported.
        $writer = \core_privacy\local\request\writer::with_context(\context_system::instance());
        $this->assertFalse($writer->has_any_data_in_any_context());
    }

    /**
     * Test that a student with multiple submissions and grades is returned with the correct data.
     *
     * @covers \mod_openbook\privacy\provider::export_user_data_student
     * @return void
     */
    public function test_export_user_data_student(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $coursecontext = \context_course::instance($course->id);

        $user = $this->getDataGenerator()->create_user();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $openbook = $this->create_instance([
            'name' => 'Openbook Privacy Test',
            'course' => $course,
            'filesarepersonal' => 1,
            'openpdffilesinpdfjs' => 1,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
        ]);

        $context = $openbook->get_context();

        // Validate exported data for user1 (without any file uploaded).
        $this->setUser($user);
        $writer = writer::with_context($context);

        $data = provider::get_contexts_for_userid($user->id);
        $this->assertEmpty($data);

        // Create two files for a student.
        $this->create_upload(
            $user->id,
            $openbook->get_instance()->id,
            'upload-no-1.txt',
            'This is the first upload here!'
        );
        $this->create_upload(
            $user->id,
            $openbook->get_instance()->id,
            'upload-no-2.txt',
            'This is the second upload here!'
        );

        $this->setUser($teacher);

        $overridedata = new \stdClass();
        $overridedata->openbook = $openbook->get_instance()->id;
        $overridedata->userid = $user->id;
        $overridedata->duedate = time();
        $overridedata->allowsubmissionsfromdate = time() - 3600;
        $overridedata->approvalfromdate = time() + 3600;
        $overridedata->approvaltodate = time() + 7200;
        $overridedata->securewindowfromdate = time() + 10800;
        $overridedata->securewindowtodate = time() + 14400;
        $DB->insert_record('openbook_overrides', $overridedata);

        /** @var \core_privacy\tests\request\content_writer $writer */
        $writer = writer::with_context($context);
        $this->assertFalse($writer->has_any_data());

        // The student should have some text submitted.
        // Add the course context as well to make sure there is no error.
        $approvedlist = new \core_privacy\tests\request\approved_contextlist(
            $user,
            'mod_openbook',
            [$context->id, $coursecontext->id]
        );

        provider::export_user_data($approvedlist);

        // Check that we have general details about the openbook.
        $this->assertEquals('Openbook Privacy Test', $writer->get_data()->name);

        // Check override data was exported correctly.
        $writer = writer::with_context($context);
        $overrideexport = $writer->get_data(['Overrides']);
        $label = get_string('privacy:metadata:userextensionallowsubmissionsfromdate', 'mod_openbook');
        $this->assertEquals(
            \core_privacy\local\request\transform::datetime($overridedata->allowsubmissionsfromdate),
            $overrideexport->allowsubmissionsfromdate->{$label}
        );
        $label = get_string('privacy:metadata:userextensiontodate', 'mod_openbook');
        $this->assertEquals(
            \core_privacy\local\request\transform::datetime($overridedata->duedate),
            $overrideexport->todate->{$label}
        );
        $label = get_string('privacy:metadata:userextensionapprovalfromdate', 'mod_openbook');
        $this->assertEquals(
            \core_privacy\local\request\transform::datetime($overridedata->approvalfromdate),
            $overrideexport->approvalfromdate->{$label}
        );
        $label = get_string('privacy:metadata:userextensionapprovaltodate', 'mod_openbook');
        $this->assertEquals(
            \core_privacy\local\request\transform::datetime($overridedata->approvaltodate),
            $overrideexport->approvaltodate->{$label}
        );
        $label = get_string('privacy:metadata:userextensionsecurewindowfromdate', 'mod_openbook');
        $this->assertEquals(
            \core_privacy\local\request\transform::datetime($overridedata->securewindowfromdate),
            $overrideexport->securewindowfromdate->{$label}
        );
        $label = get_string('privacy:metadata:userextensionsecurewindowtodate', 'mod_openbook');
        $this->assertEquals(
            \core_privacy\local\request\transform::datetime($overridedata->securewindowtodate),
            $overrideexport->securewindowtodate->{$label}
        );
    }

    /**
     * Tests the data returned for a teacher.
     *
     * @covers \mod_openbook\privacy\provider::export_user_data_teacher
     * @return void
     */
    public function test_export_user_data_teacher(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $coursecontext = \context_course::instance($course->id);

        $user = $this->getDataGenerator()->create_user();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $openbook = $this->create_instance([
            'name' => 'Openbook Privacy Test',
            'course' => $course,
            'filesarepersonal' => 1,
            'openpdffilesinpdfjs' => 1,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
        ]);

        $context = $openbook->get_context();

        // Validate exported data for teacher1 (without any attempt).
        $this->setUser($user);
        $writer = writer::with_context($context);

        $data = provider::get_contexts_for_userid($user->id);
        $this->assertEmpty($data);

        // Create two files for a teacher.
        $this->create_teacher_file(
            $user->id,
            $openbook->get_instance()->id,
            'upload-no-1.txt',
            'This is the first upload here!'
        );
        $this->create_teacher_file(
            $user->id,
            $openbook->get_instance()->id,
            'upload-no-2.txt',
            'This is the second upload here!'
        );

        // The teacher should have some text submitted.
        // Add the course context as well to make sure there is no error.
        $approvedlist = new \core_privacy\tests\request\approved_contextlist(
            $user,
            'mod_openbook',
            [$context->id, $coursecontext->id]
        );

        provider::export_user_data($approvedlist);

        // Check that we have general details about the openbook.
        $this->assertEquals('Openbook Privacy Test', $writer->get_data()->name);

        /** @var \core_privacy\tests\request\content_writer $writer */
        $writer = writer::with_context($context);
        $this->assertTrue($writer->has_any_data());

        // The teacher should have some text submitted.
        $approvedlist = new \core_privacy\tests\request\approved_contextlist(
            $teacher,
            'mod_openbook',
            [$context->id, $coursecontext->id]
        );
        provider::export_user_data($approvedlist);
    }

    /**
     * A test for deleting all user data for a given context.
     *
     * @covers \mod_openbook\privacy\provider::delete_data_for_all_users_in_context
     * @return void
     */
    public function test_delete_data_for_all_users_in_context(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user1->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $openbook = $this->create_instance([
            'name' => 'Openbook Privacy Test',
            'course' => $course,
            'filesarepersonal' => 1,
            'openpdffilesinpdfjs' => 1,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
        ]);

        $context = $openbook->get_context();

        $this->setUser($teacher);

        // Override data for two users.
        $overridedata = new \stdClass();
        $overridedata->openbook = $openbook->get_instance()->id;
        $overridedata->userid = $user1->id;
        $overridedata->duedate = time();
        $overridedata->allowsubmissionsfromdate = time() - 3600;
        $overridedata->approvalfromdate = time() + 3600;
        $overridedata->approvaltodate = time() + 7200;
        $overridedata->securewindowfromdate = time() + 10800;
        $overridedata->securewindowtodate = time() + 14400;
        $DB->insert_record('openbook_overrides', $overridedata);

        $overridedata = new \stdClass();
        $overridedata->openbook = $openbook->get_instance()->id;
        $overridedata->userid = $user2->id;
        $overridedata->duedate = time();
        $overridedata->allowsubmissionsfromdate = time() - 3600;
        $overridedata->approvalfromdate = time() + 3600;
        $overridedata->approvaltodate = time() + 7200;
        $overridedata->securewindowfromdate = time() + 10800;
        $overridedata->securewindowtodate = time() + 14400;
        $DB->insert_record('openbook_overrides', $overridedata);

        // Delete all user data for this Openbook resources folder.
        provider::delete_data_for_all_users_in_context($context);

        // Check all relevant tables.
        $records = $DB->get_records('openbook_file');
        $this->assertEmpty($records);

        // Check that overrides and the calendar events are deleted.
        $records = $DB->get_records('openbook_overrides');
        $this->assertEmpty($records);
        $records = $DB->get_records('event', ['modulename' => 'openbook', 'instance' => $openbook->get_instance()->id]);
        $this->assertEmpty($records);
    }

    /**
     * A test for deleting all user data for one user.
     *
     * @covers \mod_openbook\privacy\provider::delete_data_for_user
     * @return void
     */
    public function test_delete_data_for_user(): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        $coursecontext = \context_course::instance($course->id);

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user1->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $openbook = $this->create_instance([
            'name' => 'Openbook Privacy Test',
            'course' => $course,
            'filesarepersonal' => 1,
            'openpdffilesinpdfjs' => 1,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
        ]);

        $context = $openbook->get_context();

        $this->setUser($teacher);

        // Override data for two users.
        $overridedata = new \stdClass();
        $overridedata->openbook = $openbook->get_instance()->id;
        $overridedata->userid = $user1->id;
        $overridedata->duedate = time();
        $overridedata->allowsubmissionsfromdate = time() - 3600;
        $overridedata->approvalfromdate = time() + 3600;
        $overridedata->approvaltodate = time() + 7200;
        $overridedata->securewindowfromdate = time() + 10800;
        $overridedata->securewindowtodate = time() + 14400;
        $DB->insert_record('openbook_overrides', $overridedata);

        $overridedata = new \stdClass();
        $overridedata->openbook = $openbook->get_instance()->id;
        $overridedata->userid = $user2->id;
        $overridedata->duedate = time();
        $overridedata->allowsubmissionsfromdate = time() - 3600;
        $overridedata->approvalfromdate = time() + 3600;
        $overridedata->approvaltodate = time() + 7200;
        $overridedata->securewindowfromdate = time() + 10800;
        $overridedata->securewindowtodate = time() + 14400;
        $DB->insert_record('openbook_overrides', $overridedata);

        // Create two files for a student.
        $this->create_upload(
            $user1->id,
            $openbook->get_instance()->id,
            'upload-no-1.txt',
            'This is the first upload here!'
        );
        $this->create_upload(
            $user2->id,
            $openbook->get_instance()->id,
            'upload-no-2.txt',
            'This is the second upload here!'
        );

        // Delete user 2's data.
        $approvedlist = new \core_privacy\tests\request\approved_contextlist(
            $user2,
            'mod_openbook',
            [$context->id, $coursecontext->id]
        );
        provider::delete_data_for_user($approvedlist);

        // Check all relevant tables.
        $records = $DB->get_records('openbook_file');
        foreach ($records as $record) {
            $this->assertEquals($user1->id, $record->userid);
            $this->assertNotEquals($user2->id, $record->userid);
        }
        $records = $DB->get_records('openbook_overrides');
        foreach ($records as $record) {
            $this->assertEquals($user1->id, $record->userid);
            $this->assertNotEquals($user2->id, $record->userid);
        }
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
            $this->openbook->get_instance()->id,
            'upload-no-1.txt',
            'This is the first upload here!'
        );
        $this->create_upload(
            $this->user2->id,
            $this->openbook->get_instance()->id,
            'upload-no-2.txt',
            'This is the second upload here!'
        );

        // Test for the data to be in place!
        self::assertEquals(
            2,
            $DB->count_records(
                'openbook_file',
                ['openbook' => $this->openbook->get_instance()->id]
            )
        );

        $userlist = new \core_privacy\local\request\approved_userlist(
            $this->openbook->get_context(),
            'openbook',
            [$this->user1->id]
        );
        provider::delete_data_for_users($userlist);
        self::assertEquals(
            1,
            $DB->count_records(
                'openbook_file',
                ['openbook' => $this->openbook->get_instance()->id]
            )
        );
        provider::delete_data_for_users($userlist);
        $userlist = new \core_privacy\local\request\approved_userlist(
            $this->openbook->get_context(),
            'openbook',
            [$this->user1->id, $this->user2->id, $this->user3->id]
        );
        provider::delete_data_for_users($userlist);
        self::assertEquals(
            0,
            $DB->count_records(
                'openbook_file',
                ['openbook' => $this->openbook->get_instance()->id],
            )
        );
    }
}
