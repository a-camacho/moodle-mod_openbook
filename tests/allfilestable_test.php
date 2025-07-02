<?php
// This file is part of mod_privatestudentfolder for Moodle - http://moodle.org/
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
 * Unit tests for mod_privatestudentfolder's allfilestable classes.
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_privatestudentfolder\local\tests;

use Exception;
use mod_assign_generator;
use coding_exception;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page!
}

// Make sure the code being tested is accessible.
global $CFG;
require_once($CFG->dirroot . '/mod/privatestudentfolder/locallib.php'); // Include the code to test!

/**
 * This class contains the test cases for the formular validation.
 *
 * @package   mod_privatestudentfolder
 * @author    Philipp Hager
 * @copyright 2017 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class allfilestable_test extends base {
    /*
     * The base test class already contains a setUp-method setting up a course including users and groups.
     */

    /**
     * Tests the basic creation of a privatestudentfolder instance with standardized settings!
     */
    public function test_create_instance() {
        self::assertNotEmpty($this->create_instance());
    }

    /**
     * Tests if we can create an allfilestable without uploaded files
     *
     * @throws Exception
     */
    public function test_allfilestable_upload() {
        // Setup fixture!
        $privatestudentfolder = $this->create_instance([
            'mode' => PRIVATESTUDENTFOLDER_MODE_UPLOAD,
            'filesarepersonal' => 1,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
        ]);

        // Exercise SUT!
        $output = $privatestudentfolder->display_allfilesform();
        self::assertFalse(strpos($output, "Nothing to display"));

        // Teardown fixture!
        $privatestudentfolder = null;
    }

    /**
     * Tests if we can create an allfilestable without imported files
     *
     * @throws coding_exception
     */
    public function test_allfilestable_import() {
        // Setup fixture!
        /** @var mod_assign_generator $generator */
        $generator = self::getDataGenerator()->get_plugin_generator('mod_assign');
        $params['course'] = $this->course->id;
        $assign = $generator->create_instance($params);
        $privatestudentfolder = $this->create_instance([
            'mode' => PRIVATESTUDENTFOLDER_MODE_IMPORT,
            'importfrom' => $assign->id,
            'filesarepersonal' => 1,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
        ]);

        // Exercise SUT!
        $output = $privatestudentfolder->display_allfilesform();
        self::assertFalse(strpos($output, "Nothing to display"));

        // Teardown fixture!
        $privatestudentfolder = null;
    }

    /**
     * Tests if we can create an allfilestable without imported group-files
     *
     * @throws coding_exception
     */
    public function test_allfilestable_group() {
        // TODO : Setup fixture!

        $this->resetAfterTest();
        $this->setAdminUser();
        // Create course and enrols.
        $course = $this->getDataGenerator()->create_course();
        $users = [
            'student1' => $this->getDataGenerator()->create_and_enrol($course, 'student'),
            'student2' => $this->getDataGenerator()->create_and_enrol($course, 'student'),
            'student3' => $this->getDataGenerator()->create_and_enrol($course, 'student'),
            'student4' => $this->getDataGenerator()->create_and_enrol($course, 'student'),
            'student5' => $this->getDataGenerator()->create_and_enrol($course, 'student'),
        ];
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $this->course = $course;

        // Generate groups.
        $groups = [];
        $groupmembers = [
            'group1' => ['student1', 'student2'],
            'group2' => ['student3', 'student4'],
            'group3' => ['student5'],
        ];
        foreach ($groupmembers as $groupname => $groupusers) {
            $group = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => $groupname]);
            foreach ($groupusers as $user) {
                groups_add_member($group, $users[$user]);
            }
            $groups[$groupname] = $group;
        }

        $params = [
            'course' => $course,
            'assignsubmission_file_enabled' => 1,
            'assignsubmission_file_maxfiles' => 12,
            'assignsubmission_file_maxsizebytes' => 1024 * 1024,
            'teamsubmission' => 1,
            'preventsubmissionnotingroup' => false,
            'requireallteammemberssubmit' => false,
            'groupmode' => 1,
        ];

        $assign = $this->getDataGenerator()->create_module('assign', $params);
        $cm = get_coursemodule_from_id('assign', $assign->cmid, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $files = [
            "mod/assign/tests/fixtures/submissionsample01.txt",
            "mod/assign/tests/fixtures/submissionsample02.txt",
        ];
        $generator = self::getDataGenerator()->get_plugin_generator('mod_assign');

        $this->setAdminUser();
        foreach ($users as $key => $user) {
            $generator->create_submission([
                'userid' => $user->id,
                'cmid' => $cm->id,
                'file' => implode(',', $files),
            ]);
        }

        $this->setAdminUser();
        $privatestudentfolder = $this->create_instance([
            'mode' => PRIVATESTUDENTFOLDER_MODE_IMPORT,
            'importfrom' => $assign->id,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
            'allowsubmissionsfromdate' => 0,
            'duedate' => 0,
            'groupmode' => NOGROUPS,
        ]);

        $privatestudentfolder->importfiles();
        $privatestudentfolder->set_allfilespage(true);
        $allfilestable = $privatestudentfolder->get_allfilestable(PRIVATESTUDENTFOLDER_FILTER_NOFILTER);
        ob_start();
        $allfilestable->out(10, true); // Print the whole table.
        $tableoutput = ob_get_contents();
        ob_end_clean();
        $norowsfound = $allfilestable->get_count() == 0;
        $nofilesfound = $allfilestable->get_totalfilescount() == 0;
        self::assertFalse($norowsfound);
        self::assertFalse($nofilesfound);

        // Teardown fixture!
        $privatestudentfolder = null;
    }
}
