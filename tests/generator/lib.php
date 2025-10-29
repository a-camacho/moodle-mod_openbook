<?php
// This file is part of mod_privatestudentfolder for Moodle - http://moodle.org/
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
 * Generator file for mod_privatestudentfolder's PHPUnit tests
 *
 * @package       mod_privatestudentfolder
 * @category      test
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * privatestudentfolder module data generator class
 *
 * @package       mod_privatestudentfolder
 * @category      test
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_privatestudentfolder_generator extends testing_module_generator {
    /**
     * Generator method creating a mod_privatestudentfolder instance.
     *
     * @param stdClass|array|null $record Optional named array or stdClass containing instance settings.
     * @param array|null $options Optional general options for course module. Can be merged into $record.
     * @return stdClass Record from module-defined table with additional field cmid (corresponding id in course_modules table).
     */
    public function create_instance($record = null, ?array $options = null) {
        $record = (object)(array)$record;

        $timecreated = time();

        $defaultsettings = [
                'name' => 'privatestudentfolder',
                'intro' => 'Introtext',
                'introformat' => 1,
                'alwaysshowdescription' => 1,
                'timecreated' => $timecreated,
                'timemodified' => $timecreated,
                'duedate' => $timecreated + 604800, // 1 week later!
                'allowsubmissionsfromdate' => $timecreated,
                'cutoffdate' => 0,
                'mode' => 0, // Equals PRIVATESTUDENTFOLDER_MODE_UPLOAD!
                'importfrom' => -1,
                'autoimport' => 1,
                'obtainstudentapproval' => 1,
                'groupapproval' => 0, // Equals PRIVATESTUDENTFOLDER_APPROVAL_ALL!
                'maxfiles' => 5,
                'maxbytes' => 2,
                'allowedfiletypes' => '',
                'filesarepersonal' => 1,
                'openpdffilesinpdfjs' => 1,
                'obtainteacherapproval' => 1,
                'groupmode' => SEPARATEGROUPS,
        ];

        foreach ($defaultsettings as $name => $value) {
            if (!isset($record->{$name})) {
                $record->{$name} = $value;
            }
        }

        return parent::create_instance($record, (array)$options);
    }
}
