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
 * backup/moodle2/backup_openbook_stepslieb.php
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class used to design mod_openbooks data structure to back up
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_openbook_activity_structure_step extends backup_activity_structure_step {
    /**
     * Define the structure for the openbook activity
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $openbook = new backup_nested_element('openbook', ['id'], [
                'name',
                'intro',
                'introformat',
                'alwaysshowdescription',
                'allowsubmissionsfromdate',
                'duedate',
                'completionupload',
                'approvalfromdate',
                'approvaltodate',
                'obtainstudentapproval',
                'maxfiles',
                'maxbytes',
                'allowedfiletypes',
                'obtainteacherapproval',
                'filesarepersonal',
                'openpdffilesinpdfjs',
                'notifystatuschange',
                'notifyfilechange',
                'securewindowfromdate',
                'securewindowtodate',
                'showfilechangeswarning',
                'timecreated',
                'timemodified',
                'uselegacyviewer',
        ]);

        $overrides = new backup_nested_element('overrides');
        $override = new backup_nested_element('override', ['id'], [
                'openbook',
                'groupid',
                'userid',
                'allowsubmissionsfromdate',
                'duedate',
                'approvalfromdate',
                'approvaltodate',
                'securewindowfromdate',
                'securewindowtodate',
        ]);

        $files = new backup_nested_element('files');

        $file = new backup_nested_element('file', ['id'], [
                'userid',
                'timecreated',
                'fileid',
                'filename',
                'contenthash',
                'type',
                'teacherapproval',
                'studentapproval',
        ]);

        // Define sources.
        $openbook->set_source_table('openbook', ['id' => backup::VAR_ACTIVITYID]);

        if ($userinfo) {
            // Build the tree.
            $openbook->add_child($extduedates);
            $extduedates->add_child($extduedate);
            $openbook->add_child($overrides);
            $overrides->add_child($override);
            $openbook->add_child($files);
            $files->add_child($file);

            $extduedate->set_source_table('openbook_extduedates', ['openbook' => backup::VAR_PARENTID]);
            $override->set_source_table('openbook_overrides', ['openbook' => backup::VAR_PARENTID]);

            $file->set_source_table('openbook_file', ['openbook' => backup::VAR_PARENTID]);

            $file->annotate_files('mod_openbook', 'attachment', null);

            // Define id annotations.
            $extduedate->annotate_ids('user', 'userid');
            $override->annotate_ids('user', 'userid');
            $override->annotate_ids('group', 'groupid');
            $file->annotate_ids('user', 'userid');

            // Define file annotations.
            // This file area hasn't itemid.
            $openbook->annotate_files('mod_openbook', 'attachment', null);
        }

        // Return the root element (openbook), wrapped into standard activity structure.

        return $this->prepare_activity_structure($openbook);
    }
}
