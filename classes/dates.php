<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Contains the class for fetching the important dates in mod_openbook for a given module instance and a user.
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace mod_openbook;

use core\activity_dates;

/**
 * Class for fetching the important dates in mod_openbook for a given module instance and a user.
 *
 * @copyright  2025 Luca Bösch <luca.boesch@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dates extends activity_dates {
    /**
     * Returns a list of important dates in mod_openbook.
     *
     * @return array
     */
    protected function get_dates(): array {
        global $CFG, $USER;
        require_once($CFG->dirroot . '/mod/openbook/locallib.php');

        $course = get_course($this->cm->course);
        $context = \context_module::instance($this->cm->id);

        $openbook = new \openbook($this->cm, $course, $context);
        $instance = $openbook->get_instance();

        $dates = [];

        $override = $openbook->override_get_currentuserorgroup();

        if ($override && $override->submissionoverride) {
            $instance->duedate = $override->duedate;
            $instance->allowsubmissionsfromdate = $override->allowsubmissionsfromdate;
        }

        if ($override && $override->securewindowoverride) {
            if ($override->securewindowfromdate > 0) {
                $instance->securewindowfromdate = $override->securewindowfromdate;
            }
            if ($override->securewindowtodate > 0) {
                $instance->securewindowtodate = $override->securewindowtodate;
            }
        }

        if ($instance->allowsubmissionsfromdate) {
            $dates[] = [
                'dataid' => 'timeopen',
                'label' => get_string('allowsubmissionsfromdate', 'openbook') . ':',
                'timestamp' => $instance->allowsubmissionsfromdate,
            ];
        }
        if ($instance->duedate) {
            $dates[] = [
                'dataid' => 'timeclose',
                'label' => get_string('duedate_upload', 'openbook') . ':',
                'timestamp' => $instance->duedate,
            ];
        }

        $extensionduedate = $openbook->user_extensionduedate($USER->id);

        if ($extensionduedate) {
            $dates[] = [
                'label' => get_string('extensionto', 'openbook') . ':',
                'timestamp' => $extensionduedate,
            ];
        }

        if ($instance->obtainstudentapproval) {
            if ($override && $override->approvaloverride) {
                $instance->approvalfromdate = $override->approvalfromdate;
                $instance->approvaltodate = $override->approvaltodate;
            }
            if ($instance->approvalfromdate) {
                $dates[] = [
                    'dataid' => 'approvalopen',
                    'label' => get_string('approvalfromdate', 'openbook') . ':',
                    'timestamp' => $instance->approvalfromdate,
                ];
            }
            if ($instance->approvaltodate) {
                $dates[] = [
                    'dataid' => 'approvalclose',
                    'label' => get_string('approvaltodate', 'openbook') . ':',
                    'timestamp' => $instance->approvaltodate,
                ];
            }
        }

        if ($instance->securewindowfromdate) {
            $dates[] = [
                'dataid' => 'securewindowstart',
                'label' => get_string('securewindowfromdate', 'openbook') . ':',
                'timestamp' => $instance->securewindowfromdate,
            ];
        }

        if ($instance->securewindowtodate) {
            $dates[] = [
                'dataid' => 'securewindowend',
                'label' => get_string('securewindowtodate', 'openbook') . ':',
                'timestamp' => $instance->securewindowtodate,
            ];
        }
        return $dates;
    }
}
