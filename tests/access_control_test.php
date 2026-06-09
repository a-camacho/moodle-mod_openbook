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
 * Access control tests for mod_openbook: has_filepermission, download_file, update_files_teacherapproval.
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @copyright     2026 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.PHPUnit.TestCaseNames.UnexpectedLevel2NS

namespace mod_openbook\local\tests;

use moodle_exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/openbook/locallib.php');

/**
 * Tests that broken access-control in the file download path is closed.
 *
 * @package   mod_openbook
 * @author    University of Geneva, E-Learning Team
 * @copyright 2026 University of Geneva {@link http://www.unige.ch}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class access_control_test extends base {
    /**
     * Owner can be detected through has_filepermission() regardless of approval state.
     *
     * @covers \openbook::has_filepermission
     */
    public function test_owner_can_view_own_file_when_unapproved(): void {
        $openbook = $this->create_instance([
            'filesarepersonal' => 1,
            'obtainteacherapproval' => 1,
            'obtainstudentapproval' => 0,
        ]);
        $owner = $this->students[0];

        $this->create_upload($owner->id, $openbook->get_instance()->id, 'owned.txt', 'hello');
        $fileid = $this->get_stored_fileid_for($openbook->get_instance()->id, $owner->id, 'owned.txt');

        self::assertTrue($openbook->has_filepermission($fileid, $owner->id));
    }

    /**
     * filesarepersonal=1 must block any non-owner, even if approvals are granted.
     *
     * @covers \openbook::has_filepermission
     */
    public function test_non_owner_blocked_when_files_are_personal(): void {
        $openbook = $this->create_instance([
            'filesarepersonal' => 1,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
        ]);
        $owner = $this->students[0];
        $other = $this->students[1];

        $this->create_upload($owner->id, $openbook->get_instance()->id, 'private.txt', 'hidden');
        $fileid = $this->get_stored_fileid_for($openbook->get_instance()->id, $owner->id, 'private.txt');

        self::assertFalse($openbook->has_filepermission($fileid, $other->id));
        // Default $userid=0 path must also deny, since the file would only be visible to the owner.
        self::assertFalse($openbook->has_filepermission($fileid));
    }

    /**
     * Non-owner cannot view another user's file when teacher approval is required but not yet granted.
     *
     * @covers \openbook::has_filepermission
     */
    public function test_non_owner_blocked_when_teacher_approval_pending(): void {
        $openbook = $this->create_instance([
            'filesarepersonal' => 0,
            'obtainteacherapproval' => 1,
            'obtainstudentapproval' => 0,
        ]);
        $owner = $this->students[0];
        $other = $this->students[1];

        $this->create_upload($owner->id, $openbook->get_instance()->id, 'pending.txt', 'pending');
        $fileid = $this->get_stored_fileid_for($openbook->get_instance()->id, $owner->id, 'pending.txt');

        self::assertFalse($openbook->has_filepermission($fileid, $other->id));
    }

    /**
     * Non-owner CAN view another user's file once it is approved and the instance is not personal.
     *
     * @covers \openbook::has_filepermission
     */
    public function test_non_owner_allowed_when_fully_approved_and_not_personal(): void {
        global $DB;

        $openbook = $this->create_instance([
            'filesarepersonal' => 0,
            'obtainteacherapproval' => 1,
            'obtainstudentapproval' => 0,
        ]);
        $owner = $this->students[0];
        $other = $this->students[1];

        $this->create_upload($owner->id, $openbook->get_instance()->id, 'approved.txt', 'shared');
        $fileid = $this->get_stored_fileid_for($openbook->get_instance()->id, $owner->id, 'approved.txt');
        $scope = ['openbook' => $openbook->get_instance()->id, 'fileid' => $fileid];
        $DB->set_field('openbook_file', 'teacherapproval', 1, $scope);

        self::assertTrue($openbook->has_filepermission($fileid, $other->id));
        // And the visibility-only path (userid=0) also succeeds.
        self::assertTrue($openbook->has_filepermission($fileid));
    }

    /**
     * download_file() must refuse a fileid that does not belong to this instance.
     *
     * @covers \openbook::download_file
     */
    public function test_download_file_rejects_cross_instance_fileid(): void {
        $openbooka = $this->create_instance([
            'filesarepersonal' => 0,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
        ]);
        $openbookb = $this->create_instance([
            'filesarepersonal' => 0,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
        ]);

        $student = $this->students[0];
        $this->create_upload($student->id, $openbookb->get_instance()->id, 'in-b.txt', 'B-content');
        $bfileid = $this->get_stored_fileid_for($openbookb->get_instance()->id, $student->id, 'in-b.txt');

        // Student tries to fetch B's file via instance A's download_file().
        self::setUser($student);
        $this->expectException(moodle_exception::class);
        $openbooka->download_file($bfileid);
    }

    /**
     * Even a teacher with mod/openbook:approve in instance A must NOT be able to download
     * a file belonging to instance B via instance A's download_file().
     *
     * @covers \openbook::download_file
     */
    public function test_download_file_rejects_cross_instance_for_teacher(): void {
        $openbooka = $this->create_instance([
            'filesarepersonal' => 0,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
        ]);
        $openbookb = $this->create_instance([
            'filesarepersonal' => 0,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
        ]);

        $student = $this->students[0];
        $this->create_upload($student->id, $openbookb->get_instance()->id, 'in-b.txt', 'B-content');
        $bfileid = $this->get_stored_fileid_for($openbookb->get_instance()->id, $student->id, 'in-b.txt');

        self::setUser($this->editingteachers[0]);
        $this->expectException(moodle_exception::class);
        $openbooka->download_file($bfileid);
    }

    /**
     * Approving fileids in instance A must not flip approval flags of files in instance B.
     *
     * @covers \openbook::update_files_teacherapproval
     */
    public function test_update_files_teacherapproval_is_instance_scoped(): void {
        global $DB;

        $openbooka = $this->create_instance([
            'filesarepersonal' => 0,
            'obtainteacherapproval' => 1,
            'obtainstudentapproval' => 0,
        ]);
        $openbookb = $this->create_instance([
            'filesarepersonal' => 0,
            'obtainteacherapproval' => 1,
            'obtainstudentapproval' => 0,
        ]);

        $studenta = $this->students[0];
        $studentb = $this->students[1];
        $this->create_upload($studenta->id, $openbooka->get_instance()->id, 'a.txt', 'A');
        $this->create_upload($studentb->id, $openbookb->get_instance()->id, 'b.txt', 'B');

        $afileid = $this->get_stored_fileid_for($openbooka->get_instance()->id, $studenta->id, 'a.txt');
        $bfileid = $this->get_stored_fileid_for($openbookb->get_instance()->id, $studentb->id, 'b.txt');

        $bscope = ['openbook' => $openbookb->get_instance()->id, 'fileid' => $bfileid];
        $beforeb = (int)$DB->get_field('openbook_file', 'teacherapproval', $bscope);

        self::setUser($this->editingteachers[0]);
        // Teacher in instance A submits a payload mixing A's own file with B's file id.
        $openbooka->update_files_teacherapproval([$afileid => '1', $bfileid => '1']);

        $ascope = ['openbook' => $openbooka->get_instance()->id, 'fileid' => $afileid];
        $aftera = (int)$DB->get_field('openbook_file', 'teacherapproval', $ascope);
        $afterb = (int)$DB->get_field('openbook_file', 'teacherapproval', $bscope);

        self::assertSame(1, $aftera, 'A\'s own file was approved as expected.');
        self::assertSame($beforeb, $afterb, 'B\'s file must NOT be touched by A\'s approval form.');
    }

    /**
     * Resolve the stored_file id for a previously-created upload.
     *
     * create_upload() returns the inserted openbook_file row id, but the tests need
     * the {files} record id (which the rest of the API uses as the fileid).
     *
     * @param int $openbookid
     * @param int $userid
     * @param string $filename
     * @return int
     */
    private function get_stored_fileid_for(int $openbookid, int $userid, string $filename): int {
        global $DB;
        return (int)$DB->get_field('openbook_file', 'fileid', [
            'openbook' => $openbookid,
            'userid' => $userid,
            'filename' => $filename,
        ]);
    }
}
