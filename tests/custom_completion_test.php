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
 * Unit tests for mod_openbook's completion classes.
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_openbook;

use core_completion\activity_custom_completion;
use mod_openbook\completion\custom_completion;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/openbook/locallib.php');

/**
 * This class contains the test cases for the custom completion rules.
 *
 * @package    mod_openbook
 * @copyright  2025 University of Geneva {@link http://www.unige.ch}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class custom_completion_test extends \advanced_testcase {
    /**
     * Tests that the 'completionupload' rule returns COMPLETE when a file is uploaded.
     *
     * @covers \mod_openbook\completion\custom_completion::get_state
     * @return void
     */
    public function test_completion_upload_rule_success(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        $params = [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionview' => 1,
            'completionupload' => 1,
        ];
        $openbook = $this->getDataGenerator()->create_module('openbook', $params);

        \rebuild_course_cache($course->id, true);
        $modinfo = \get_fast_modinfo($course);
        $cm = $modinfo->get_cm($openbook->cmid);

        $customcompletion = new custom_completion($cm, $student->id);

        $this->assertEquals(COMPLETION_INCOMPLETE, $customcompletion->get_state('completionupload'));

        $record = (object) [
            'openbook' => $openbook->id,
            'userid' => $student->id,
            'fileid' => 12345,
            'filename' => 'test_file.pdf',
            'timecreated' => time(),
            'timemodified' => time(),
        ];
        $DB->insert_record('openbook_file', $record);

        $this->assertEquals(COMPLETION_COMPLETE, $customcompletion->get_state('completionupload'));
    }
}
