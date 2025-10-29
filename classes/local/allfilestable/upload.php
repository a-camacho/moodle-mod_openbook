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
 * Contains class for files table listing all files in upload mode
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_privatestudentfolder\local\allfilestable;

/**
 * Table showing all uploaded files
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class upload extends base {
    /**
     * Return all columns, column-headers and helpicons for this table
     *
     * @return array Array with column names, column headers and help icons
     */
    public function get_columns() {
        [$columns, $headers, $helpicons] = parent::get_columns();

        if (has_capability('mod/privatestudentfolder:approve', $this->context) && $this->allfilespage) {
            if ($this->obtainstudentapproval) {
                $columns[] = 'studentapproval';
                $headers[] = get_string('studentapproval', 'privatestudentfolder');
                $helpicons[] = new \help_icon('studentapproval', 'privatestudentfolder');
            }

            $columns[] = 'privatestudentfolderstatus';
            $headers[] = get_string('privatestudentfolderstatus', 'privatestudentfolder');
            $helpicons[] = new \help_icon('privatestudentfolderstatus', 'privatestudentfolder');
        }

        return [$columns, $headers, $helpicons];
    }

    /**
     * Method is not needed here and has to return ''!
     *
     * @param int $itemid user ID or group ID
     * @param int $fileid file ID
     * @return string empty string
     */
    protected function add_onlinetext_preview($itemid, $fileid) {
        // This method does nothing here!
        return '';
    }
}
