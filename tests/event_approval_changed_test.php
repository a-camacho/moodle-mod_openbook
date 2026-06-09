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
 * Tests for the openbook_approval_changed log event.
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @copyright     2026 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.PHPUnit.TestCaseNames.UnexpectedLevel2NS

namespace mod_openbook\local\tests;

use mod_openbook\event\openbook_approval_changed;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/openbook/locallib.php');

/**
 * Ensures openbook_approval_changed::get_description() never fatals the log report.
 *
 * @package   mod_openbook
 * @author    University of Geneva, E-Learning Team
 * @copyright 2026 University of Geneva {@link http://www.unige.ch}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class event_approval_changed_test extends base {
    /**
     * Build the event for a given approval payload.
     *
     * @param array $extra extra key/values to add to the event's "other" payload
     * @return openbook_approval_changed
     */
    private function make_event(array $extra): openbook_approval_changed {
        $openbook = $this->create_instance();
        $cm = get_coursemodule_from_instance('openbook', $openbook->get_instance()->id);

        $do = new stdClass();
        $do->openbook = $openbook->get_instance()->id;
        $do->userid = $this->teachers[0]->id;
        $do->reluser = $this->students[0]->id;
        $do->fileid = 123;
        foreach ($extra as $key => $value) {
            $do->$key = $value;
        }

        return openbook_approval_changed::approval_changed($cm, $do);
    }

    /**
     * Teacher-approval events historically stored no 'approval' key; get_description()
     * must still return a string instead of raising "Undefined array key 'approval'".
     *
     * @covers \mod_openbook\event\openbook_approval_changed::get_description
     */
    public function test_get_description_without_approval_key(): void {
        $event = $this->make_event([]);

        $description = $event->get_description();

        self::assertIsString($description);
        self::assertStringContainsString("file with id '123'", $description);
    }

    /**
     * When the status is present it is included in the description.
     *
     * @covers \mod_openbook\event\openbook_approval_changed::get_description
     */
    public function test_get_description_with_approval_key(): void {
        $event = $this->make_event(['approval' => 'approved']);

        self::assertStringContainsString("changed to 'approved'", $event->get_description());
    }
}
