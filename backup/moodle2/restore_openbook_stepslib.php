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
 * backup/moodle2/restore_openbook_stepslib.php
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class performing all restore structure steps for mod_openbook
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_openbook_activity_structure_step extends restore_activity_structure_step {
    /**
     * Define the structure of the restore workflow.
     *
     * @return restore_path_element $structure
     */
    protected function define_structure() {

        $paths = [];
        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $paths[] = new restore_path_element('openbook', '/activity/openbook');
        if ($userinfo) {
            $files = new restore_path_element(
                'openbook_file',
                '/activity/openbook/files/file'
            );
            $paths[] = $files;

            $extduedates = new restore_path_element(
                'openbook_extduedates',
                '/activity/openbook/extduedates/extduedate'
            );

            $paths[] = $extduedates;

            $overrides = new restore_path_element(
                'openbook_overrides',
                '/activity/openbook/overrides/override'
            );
            $paths[] = $overrides;
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process an assign restore.
     *
     * @param object $data The data in object form
     * @return void
     */
    protected function process_openbook($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();

        $data->allowsubmissionsfromdate = $this->apply_date_offset($data->allowsubmissionsfromdate);
        $data->duedate = $this->apply_date_offset($data->duedate);

        if (!isset($data->cutoffdate)) {
            $data->cutoffdate = 0;
        } else {
            $data->cutoffdate = $this->apply_date_offset($data->cutoffdate);
        }

        if ($data->approvalfromdate != 0) {
            $data->approvalfromdate = $this->apply_date_offset($data->approvalfromdate);
        }
        if ($data->approvaltodate != 0) {
            $data->approvaltodate = $this->apply_date_offset($data->approvaltodate);
        }

        // Delete importfrom after restore.
        $data->importfrom = -1;

        $newitemid = $DB->insert_record('openbook', $data);

        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process a submission restore
     *
     * @param object $data The data in object form
     * @return void
     */
    protected function process_openbook_file($data) {
        global $DB;

        $data = (object)$data;

        $data->openbook = $this->get_new_parentid('openbook');

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        if ($data->userid > 0) {
            $data->userid = $this->get_mappingid('user', $data->userid);
        }

        $DB->insert_record('openbook_file', $data);

        // Note - the old contextid is required in order to be able to restore files stored in
        // sub plugin file areas attached to the submissionid.
    }

    /**
     * Process a user_flags restore
     *
     * @param object $data The data in object form
     * @return void
     */
    protected function process_openbook_extduedates($data) {
        global $DB;

        $data = (object)$data;

        $data->openbook = $this->get_new_parentid('openbook');

        $data->userid = $this->get_mappingid('user', $data->userid);
        if (!empty($data->extensionduedate)) {
            $data->extensionduedate = $this->apply_date_offset($data->extensionduedate);
        } else {
            $data->extensionduedate = 0;
        }
        // Flags mailed and locked need no translation on restore.

        $DB->insert_record('openbook_extduedates', $data);
    }

    /**
     * Process a user_flags restore
     *
     * @param object $data The data in object form
     * @return void
     */
    protected function process_openbook_overrides($data) {
        global $DB;

        $data = (object)$data;

        $data->openbook = $this->get_new_parentid('openbook');

        if ($data->userid != 0) {
            $data->userid = $this->get_mappingid('user', $data->userid);
        }
        if ($data->groupid != 0) {
            $data->groupid = $this->get_mappingid('group', $data->groupid);
        }
        if ($data->allowsubmissionsfromdate) {
            $data->allowsubmissionsfromdate = $this->apply_date_offset($data->allowsubmissionsfromdate);
        }
        if ($data->duedate) {
            $data->duedate = $this->apply_date_offset($data->duedate);
        }
        if ($data->approvalfromdate) {
            $data->approvalfromdate = $this->apply_date_offset($data->approvalfromdate);
        }
        if ($data->approvaltodate) {
            $data->approvaltodate = $this->apply_date_offset($data->approvaltodate);
        }
        // Flags mailed and locked need no translation on restore.

        $DB->insert_record('openbook_overrides', $data);
    }

    /**
     * Once the database tables have been fully restored, restore the files
     *
     * @return void
     */
    protected function after_execute() {
        $this->add_related_files('mod_openbook', 'attachment', null);
    }

    /**
     * Proceses to execute after the restoration, handles links to restored files
     *
     */
    protected function after_restore() {
        global $DB;

        // Get set new fileids after restoring.

        $pubid = $this->get_new_parentid('openbook');

        $coursemodule = get_coursemodule_from_instance('openbook', $pubid);

        $context = context_module::instance($coursemodule->id);

        $contextid = $context->id;

        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'mod_openbook', 'attachment');

        foreach ($files as $file) {
            $contingencies = [
                    'openbook' => $pubid,
                    // We need to look for the new user ID if there is one!
                    'userid' => $this->get_mappingid('user', $file->get_itemid(), $file->get_itemid()),
                    'filename' => $file->get_filename(),
            ];
            $DB->set_field('openbook_file', 'fileid', $file->get_id(), $contingencies);
        }

        // Now we correct the itemids of the files!
        $rs = $DB->get_recordset('openbook_file', ['openbook' => $pubid]);
        foreach ($rs as $record) {
            $file = $fs->get_file_by_id($record->fileid);
            if ($file->get_itemid() != $record->userid) {
                $dataobject = (object)['id' => $record->fileid, 'itemid' => $record->userid];
                $DB->update_record('files', $dataobject);
            }
        }
        $rs->close();

        // And we correct the directories!
        $rs = $DB->get_recordset('files', [
                'contextid' => $contextid,
                'component' => 'mod_openbook',
                'filename' => '.']);
        foreach ($rs as $record) {
            $record->itemid = $this->get_mappingid('user', $record->itemid, $record->itemid); // We may need to update user ID!
            $DB->update_record('files', $record);
        }
        $rs->close();
    }
}
