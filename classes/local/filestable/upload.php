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
 * Contains class for files table listing files uploaded by oneself (and options for approving them)
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_openbook\local\filestable;

/**
 * Table showing my uploaded files
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class upload extends base {
    /**
     * Add a single file to the table
     *
     * @param \stored_file $file Stored file instance
     * @return string[] Array of table cell contents
     */
    public function add_file2(\stored_file $file) {
        global $OUTPUT;
        // The common columns!
        $data = parent::add_file($file);

        $templatecontext = new \stdClass();
        // Now add the specific data to the table!
        $teacherapproval = $this->openbook->teacher_approval($file);
        // If it is a common teacher file, add data to the table!
        $commonteacherfile = $this->openbook->commonteacherfile($file);

        if ($this->openbook->get_instance()->obtainteacherapproval) {
            // Teacher has to approve: show all status.
            if (is_null($teacherapproval) || $teacherapproval == 0) {
                $templatecontext->icon = $this->questionmark;
                $templatecontext->hint = get_string('hidden', 'openbook') .
                    ' (' . get_string('teacher_pending', 'openbook') . ')';
            } else if ($teacherapproval == 1 || $commonteacherfile == 1) {
                // Here, show also the common teacher files.
                $templatecontext->icon = $this->valid;
                $templatecontext->hint = get_string('visible', 'openbook');
            } else if ($teacherapproval == 3) {
                $templatecontext->icon = $this->questionmark;
                $templatecontext->hint = get_string('hidden', 'openbook') .
                    ' (' . get_string('teacher_pending', 'openbook') . ')';
            } else {
                $templatecontext->icon = $this->invalid;
                $templatecontext->hint = get_string('hidden', 'openbook') .
                    ' (' . get_string('teacher_rejected', 'openbook') . ')';
            }
        } else {
            // Teacher doesn't have to approve: only show when rejected.
            if ((is_null($teacherapproval) || $teacherapproval == 0) || $commonteacherfile == 1) {
                // Here, show also the common teacher files.
                $templatecontext->icon = $this->valid;
                $templatecontext->hint = get_string('visible', 'openbook');
            } else if ($teacherapproval == 1) {
                $templatecontext->icon = $this->valid;
                $templatecontext->hint = get_string('visible', 'openbook');
            } else {
                $templatecontext->icon = $this->invalid;
                $templatecontext->hint = get_string('hidden', 'openbook') . ' (' .
                    get_string('teacher_rejected', 'openbook') . ')';
            }
        }
        $data[] = $OUTPUT->render_from_template('mod_openbook/approval_icon', $templatecontext);

        return $data;
    }
}
