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
 * Contains class for files table listing all files in import mode
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_openbook\local\allfilestable;

/**
 * Table showing all imported files
 *
 * @package       mod_openbook
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
     * @param \openbook $openbook openbook object
     * @param string $filter
     */
    public function __construct($uniqueid, \openbook $openbook, $filter) {
        global $PAGE;

        parent::__construct($uniqueid, $openbook, $filter);

        $params = new \stdClass();
        $cm = get_coursemodule_from_instance('openbook', $openbook->get_instance()->id);
        $params->cmid = $cm->id;
        $PAGE->requires->js_call_amd('mod_openbook/onlinetextpreview', 'initializer', [$params]);
    }

    /**
     * Return all columns, column-headers and helpicons for this table
     *
     * @return array Array with column names, column headers and help icons
     */
    public function get_columns() {
        [$columns, $headers, $helpicons] = parent::get_columns();

        if (has_capability('mod/openbook:approve', $this->context) && $this->allfilespage) {
            if ($this->obtainstudentapproval) {
                $columns[] = 'studentapproval';
                $headers[] = get_string('studentapproval', 'openbook');
                $helpicons[] = new \help_icon('studentapproval', 'openbook');
            }
            $columns[] = 'openbookstatus';
            $headers[] = get_string('openbookstatus', 'openbook');
            $helpicons[] = new \help_icon('openbookstatus', 'openbook');
        }

        return [$columns, $headers, $helpicons];
    }
}
