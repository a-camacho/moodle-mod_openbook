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
 * Handles file uploads by students!
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/privatestudentfolder/locallib.php');
require_once($CFG->dirroot . '/mod/privatestudentfolder/upload_form.php');

$cmid = required_param('cmid', PARAM_INT); // Course Module ID.
$id = optional_param('id', 0, PARAM_INT); // EntryID.

if (!$cm = get_coursemodule_from_id('privatestudentfolder', $cmid)) {
    throw new \moodle_exception('invalidcoursemodule');
}

if (!$course = $DB->get_record('course', ['id' => $cm->course])) {
    throw new \moodle_exception('coursemisconf');
}

require_login($course, false, $cm);

$context = context_module::instance($cm->id);

require_capability('mod/privatestudentfolder:upload', $context);

$privatestudentfolder = new privatestudentfolder($cm, $course, $context);

$url = new moodle_url('/mod/privatestudentfolder/upload.php', ['cmid' => $cm->id]);
if (!empty($id)) {
    $url->param('id', $id);
}
$PAGE->set_url($url);

if (!$privatestudentfolder->is_open()) {
    redirect(
        new moodle_url(
            '/mod/privatestudentfolder/view.php',
            ['id' => $cm->id],
        ),
        get_string('uploadnotopen', 'mod_privatestudentfolder'),
    );
}

$entry = new stdClass();
$entry->id = $USER->id;

$entry->definition = '';          // Updated later.
$entry->definitionformat = FORMAT_HTML; // Updated later.

$maxfiles = $privatestudentfolder->get_instance()->maxfiles;
$maxbytes = $privatestudentfolder->get_instance()->maxbytes;

$acceptedfiletypes = $privatestudentfolder->get_accepted_types();

$definitionoptions = [
        'trusttext' => true,
        'subdirs' => false,
        'maxfiles' => $maxfiles,
        'maxbytes' => $maxbytes,
        'context' => $context,
        'accepted_types' => $acceptedfiletypes,
];
$attachmentoptions = [
        'subdirs' => false,
        'maxfiles' => $maxfiles,
        'maxbytes' => $maxbytes,
        'accepted_types' => $acceptedfiletypes,
];

$entry = file_prepare_standard_editor(
    $entry,
    'definition',
    $definitionoptions,
    $context,
    'mod_privatestudentfolder',
    'entry',
    $entry->id,
);
$entry = file_prepare_standard_filemanager(
    $entry,
    'attachment',
    $attachmentoptions,
    $context,
    'mod_privatestudentfolder',
    'attachment',
    $entry->id
);

$entry->cmid = $cm->id;

// Create a new form object (found in lib.php).
$mform = new mod_privatestudentfolder_upload_form(null, [
        'current' => $entry,
        'cm' => $cm,
        'privatestudentfolder' => $privatestudentfolder,
        'definitionoptions' => $definitionoptions,
        'attachmentoptions' => $attachmentoptions,
]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/privatestudentfolder/view.php', ['id' => $cm->id]));
} else if ($data = $mform->get_data()) {
    // Store updated set of files.

    // Save and relink embedded images and save attachments.
    $entry = file_postupdate_standard_editor(
        $entry,
        'definition',
        $definitionoptions,
        $context,
        'mod_privatestudentfolder',
        'entry',
        $entry->id
    );
    $entry = file_postupdate_standard_filemanager(
        $entry,
        'attachment',
        $attachmentoptions,
        $context,
        'mod_privatestudentfolder',
        'attachment',
        $entry->id
    );

    $filearea = 'attachment';
    $sid = $USER->id;
    $fs = get_file_storage();

    $files = $fs->get_area_files($context->id, 'mod_privatestudentfolder', $filearea, $sid, 'timemodified', false);

    $values = [];
    foreach ($files as $file) {
        $values[] = $file->get_id();
    }

    $filescount = count($values);
    $rows = $DB->get_records('privatestudentfolder_file', ['privatestudentfolder' => $privatestudentfolder->get_instance()->id,
        'userid' => $USER->id]);

    // Find new files and store in db.
    foreach ($files as $file) {
        $found = false;

        foreach ($rows as $row) {
            if ($row->fileid == $file->get_id()) {
                $found = true;
            }
        }

        if (!$found) {
            $dataobject = new stdClass();
            $dataobject->privatestudentfolder = $privatestudentfolder->get_instance()->id;
            $dataobject->userid = $USER->id;
            $dataobject->timecreated = $file->get_timecreated();
            $dataobject->fileid = $file->get_id();
            $dataobject->studentapproval = 0;
            $dataobject->teacherapproval = 0;
            $dataobject->filename = $file->get_filename();
            $dataobject->type = PRIVATESTUDENTFOLDER_MODE_UPLOAD;

            $dataobject->id = $DB->insert_record('privatestudentfolder_file', $dataobject);

            if ($privatestudentfolder->get_instance()->notifyfilechange != 0) {
                privatestudentfolder::send_notification_filechange($cm, $dataobject, null, $privatestudentfolder);
            }

            \mod_privatestudentfolder\event\privatestudentfolder_file_uploaded::create_from_object($cm, $dataobject)->trigger();
        }
    }

    // Find deleted files and update db.
    foreach ($rows as $idx => $row) {
        $found = false;
        foreach ($files as $file) {
            if ($file->get_id() == $row->fileid) {
                $found = true;
                continue;
            }
        }

        if (!$found) {
            $dataobject = $DB->get_record('privatestudentfolder_file', ['id' => $row->id]);
            \mod_privatestudentfolder\event\privatestudentfolder_file_deleted::create_from_object($cm, $dataobject)->trigger();
            $DB->delete_records('privatestudentfolder_file', ['id' => $row->id]);
        }
    }

    // Update competion status - if filescount == 0 => activity not completed, else => activity completed.

    $completion = new completion_info($course);
    if ($completion->is_enabled($cm) && $privatestudentfolder->get_instance()->completionupload) {
        if ($filescount == 0) {
            $completion->update_state($cm, COMPLETION_INCOMPLETE, $USER->id);
        } else {
            $completion->update_state($cm, COMPLETION_COMPLETE, $USER->id);
        }
    }
    privatestudentfolder::send_all_pending_notifications();
    redirect(new moodle_url('/mod/privatestudentfolder/view.php', ['id' => $cm->id]));
}

// Load existing files into draft area.

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
