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
 * Unit tests for mod_openbook's allfilestable classes.
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.PHPUnit.TestCaseNames.UnexpectedLevel2NS

namespace mod_openbook\local\tests;

use Exception;
use mod_assign_generator;
use coding_exception;

defined('MOODLE_INTERNAL') || die();

// Make sure the code being tested is accessible.
global $CFG;
require_once($CFG->dirroot . '/mod/openbook/locallib.php'); // Include the code to test!

/**
 * This class contains the test cases for the formular validation.
 *
 * @package   mod_openbook
 * @author    Philipp Hager
 * @copyright 2017 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class allfilestable_test extends base {
    /**
     * Tests the basic creation of a openbook instance with standardized settings!
     *
     * @covers \openbook::__construct
     * @return void
     */
    public function test_create_instance(): void {
        self::assertNotEmpty($this->create_instance());
    }

    /**
     * Tests if we can create an allfilestable without uploaded files
     *
     * @covers \openbook::get_allfilestable_upload
     * @return void
     * @throws Exception
     */
    public function test_allfilestable_upload(): void {
        // Setup fixture!
        $openbook = $this->create_instance([
            'filesarepersonal' => 1,
            'openpdffilesinpdfjs' => 1,
            'obtainteacherapproval' => 0,
            'obtainstudentapproval' => 0,
        ]);

        // Exercise SUT!
        $output = $openbook->display_allfilesform();
        self::assertFalse(strpos($output, "Nothing to display"));

        // Teardown fixture!
        $openbook = null;
    }
}
