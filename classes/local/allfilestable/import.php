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
 * Contains class for files table listing all files in import mode
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_privatestudentfolder\local\allfilestable;

defined('MOODLE_INTERNAL') || die();

/**
 * Table showing all imported files
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class import extends base {
    /**
     * constructor
     *
     * @param string $uniqueid  A string identifying this table.Used as a key in session vars.
     *                          It gets set automatically with the helper methods!
     * @param \privatestudentfolder $privatestudentfolder privatestudentfolder object
     * @param string $filter
     */
    public function __construct($uniqueid, \privatestudentfolder $privatestudentfolder, $filter) {
        global $PAGE;

        parent::__construct($uniqueid, $privatestudentfolder, $filter);

        $params = new \stdClass();
        $cm = get_coursemodule_from_instance('privatestudentfolder', $privatestudentfolder->get_instance()->id);
        $params->cmid = $cm->id;
        $PAGE->requires->js_call_amd('mod_privatestudentfolder/onlinetextpreview', 'initializer', [$params]);
    }

    /**
     * Return all columns, column-headers and helpicons for this table
     *
     * @return array Array with column names, column headers and help icons
     */
    public function get_columns() {
        list($columns, $headers, $helpicons) = parent::get_columns();

        if (has_capability('mod/privatestudentfolder:approve', $this->context) && $this->allfilespage) {
            if ($this->obtainstudentapproval) {
                $columns[] = 'studentapproval';
                $headers[] = get_string('studentapproval', 'privatestudentfolder');
                $helpicons[] = new \help_icon('studentapproval', 'privatestudentfolder');
            }
            /*
            $columns[] = 'teacherapproval';
            if ($this->privatestudentfolder->get_instance()->obtainstudentapproval) {
                $headers[] = get_string('obtainstudentapproval', 'privatestudentfolder');
            } else {
                $headers[] = get_string('teacherapproval', 'privatestudentfolder');
            }
            $helpicons[] = new \help_icon('teacherapproval', 'privatestudentfolder');

            $columns[] = 'visibleforstudents';
            $headers[] = get_string('visibleforstudents', 'privatestudentfolder');
            $helpicons[] = null;*/
            $columns[] = 'privatestudentfolderstatus';
            $headers[] = get_string('privatestudentfolderstatus', 'privatestudentfolder');
            $helpicons[] = new \help_icon('privatestudentfolderstatus', 'privatestudentfolder');
        }

        return [$columns, $headers, $helpicons];
    }
}
