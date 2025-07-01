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
 * Displays a single mod_privatestudentfolder instance
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/privatestudentfolder/locallib.php');
require_once($CFG->dirroot . '/mod/privatestudentfolder/mod_privatestudentfolder_files_form.php');
require_once($CFG->dirroot . '/mod/privatestudentfolder/mod_privatestudentfolder_allfiles_form.php');

$id = required_param('id', PARAM_INT); // Course Module ID.
$allfilespage = optional_param('allfilespage', 0, PARAM_BOOL);

$url = new moodle_url('/mod/privatestudentfolder/view.php', ['id' => $id, 'allfilespage' => $allfilespage]);
$cm = get_coursemodule_from_id('privatestudentfolder', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_login($course, true, $cm);
$PAGE->set_url($url);

$context = context_module::instance($cm->id);

require_capability('mod/privatestudentfolder:view', $context);

if ($allfilespage) {
    require_capability('mod/privatestudentfolder:approve', $context);
}

$privatestudentfolder = new privatestudentfolder($cm, $course, $context);

$privatestudentfolder->set_allfilespage($allfilespage);

$event = \mod_privatestudentfolder\event\course_module_viewed::create([
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
]);
$event->add_record_snapshot('course', $PAGE->course);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$pagetitle = strip_tags($course->shortname . ': ' . format_string($privatestudentfolder->get_instance()->name));
$action = optional_param('action', 'view', PARAM_ALPHA);
$savevisibility = optional_param('savevisibility', false, PARAM_RAW);

$download = optional_param('download', 0, PARAM_INT);
if ($download > 0) {
    $privatestudentfolder->download_file($download);
}

if ($savevisibility) {
    require_capability('mod/privatestudentfolder:approve', $context);
    require_sesskey();

    $files = optional_param_array('files', [], PARAM_INT);
    $params = [];

    $params['pubid'] = $privatestudentfolder->get_instance()->id;
    $privatestudentfolder->update_files_teacherapproval($files);
    privatestudentfolder::send_all_pending_notifications();
    redirect($url);

} else if ($action == 'zip') {
    $privatestudentfolder->download_zip(true);
} else if ($action == 'zipusers') {
    $users = optional_param_array('selecteduser', false, PARAM_INT);
    if (!$users) {
        // No users selected.
        header('Location: view.php?id=' . $id);
        die();
    }
    $users = array_keys($users);
    $privatestudentfolder->download_zip($users);

} else if ($action == 'import') {
    require_capability('mod/privatestudentfolder:approve', $context);
    require_sesskey();

    if (!isset($_POST['confirm'])) {
        $message = get_string('updatefileswarning', 'privatestudentfolder');

        echo $OUTPUT->header();
        echo $OUTPUT->heading(format_string($privatestudentfolder->get_instance()->name), 1);
        echo $OUTPUT->confirm(
            $message,
            'view.php?id=' . $id . '&action=import&confirm=1&sesskey=' . sesskey(),
            'view.php?id=' . $id
        );
        echo $OUTPUT->footer();
        exit;
    }

    $privatestudentfolder->importfiles();
    privatestudentfolder::send_all_pending_notifications();
} else if ($action == 'grantextension') {
    require_capability('mod/privatestudentfolder:grantextension', $context);
    require_sesskey();

    $users = optional_param_array('selecteduser', [], PARAM_INT);
    $users = array_keys($users);

    if (count($users) > 0) {
        $url = new moodle_url('/mod/privatestudentfolder/grantextension.php', ['id' => $cm->id]);
        foreach ($users as $idx => $u) {
            $url->param('userids[' . $idx . ']', $u);
        }

        redirect($url);
        die();
    }
} else if ($action == 'approveusers' || $action == 'rejectusers' || $action == 'resetstudentapproval') {
    require_capability('mod/privatestudentfolder:approve', $context);
    require_sesskey();

    $userorgroupids = optional_param_array('selecteduser', [], PARAM_INT);
    $userorgroupids = array_keys($userorgroupids);
    if (count($userorgroupids) > 0) {
        $privatestudentfolder->update_users_or_groups_teacherapproval($userorgroupids, $action);
        privatestudentfolder::send_all_pending_notifications();
        redirect($url);
    }
}

$submissionid = $USER->id;

$filesform = new mod_privatestudentfolder_files_form(null,
    ['privatestudentfolder' => $privatestudentfolder, 'sid' => $submissionid, 'filearea' => 'attachment']);

if ($data = $filesform->get_data()) {
    $datasubmitted = $filesform->get_submitted_data();

    if (isset($datasubmitted->gotoupload)) {
        redirect(new moodle_url('/mod/privatestudentfolder/upload.php',
            ['id' => $privatestudentfolder->get_instance()->id, 'cmid' => $cm->id]));
    }
    if ($privatestudentfolder->is_approval_open()) {
        $studentapproval = optional_param_array('studentapproval', [], PARAM_INT);

        $conditions = [];
        $conditions['privatestudentfolder'] = $privatestudentfolder->get_instance()->id;
        $conditions['userid'] = $USER->id;

        $pubfileids = $DB->get_records_menu(
            'privatestudentfolder_file',
            [
                'privatestudentfolder' => $privatestudentfolder->get_instance()->id
            ],
            'id ASC', 'fileid, id');

        // Update records.
        foreach ($studentapproval as $idx => $approval) {
            $conditions['fileid'] = $idx;

            if ($approval != 1 && $approval != 2) {
                continue;
            }
            $dataforlog = new stdClass();
            $dataforlog->approval = $approval == 1
                ? get_string('approved', 'privatestudentfolder')
                : get_string('rejected', 'privatestudentfolder');
            $stats = null;

            if ($privatestudentfolder->get_mode() == PRIVATESTUDENTFOLDER_MODE_ASSIGN_TEAMSUBMISSION) {
                /* We have to deal with group approval! The method sets group approval for the specified user
                 * and returns current cumulated group approval (and it also sets it in privatestudentfolder_file table)! */
                $stats = $privatestudentfolder->set_group_approval($approval, $pubfileids[$idx], $USER->id);
            } else {
                $DB->set_field('privatestudentfolder_file', 'studentapproval', $approval, $conditions);
            }
            if (is_array($stats)) {
                $dataforlog->approval = get_string('datalogapprovalstudent', 'privatestudentfolder', [
                    'approving' => $stats['approving'],
                    'needed' => $stats['needed'],
                    'approval' => $dataforlog->approval
                ]);
            }
            $dataforlog->privatestudentfolder = $conditions['privatestudentfolder'];
            $dataforlog->userid = $USER->id;
            $dataforlog->reluser = $USER->id;
            $dataforlog->fileid = $idx;

            \mod_privatestudentfolder\event\privatestudentfolder_approval_changed::approval_changed($cm, $dataforlog)->trigger();
            if ($privatestudentfolder->get_instance()->notifystatuschange != 0) {
                $pubfile = $DB->get_record('privatestudentfolder_file', ['id' => $pubfileids[$idx]]);
                $newstatus = $approval == 2 ? 'not' : ''; // Used for string identifier..
                privatestudentfolder::send_notification_statuschange(
                    $cm,
                    $USER,
                    $newstatus,
                    $pubfile,
                    $cm->id,
                    $privatestudentfolder
                );
            }
        }
        privatestudentfolder::send_all_pending_notifications();
        redirect($url);
    }
}

$filesform = new mod_privatestudentfolder_files_form(null,
    ['privatestudentfolder' => $privatestudentfolder, 'sid' => $submissionid, 'filearea' => 'attachment']);

// Print the page header.
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);
if (!$allfilespage) {
    $PAGE->add_body_class('limitedwidth');
} else {
    $PAGE->add_body_class('allfilespage');
}
echo $OUTPUT->header();

$allfilesform = $privatestudentfolder->display_allfilesform();

$privatestudentfolderinstance = $privatestudentfolder->get_instance();
$privatestudentfoldermode = $privatestudentfolder->get_mode();
$templatecontext = new stdClass;
$templatecontext->obtainstudentapprovaltitle = get_string('obtainstudentapproval', 'privatestudentfolder');
$templatecontext->obtainteacherapproval = $privatestudentfolderinstance->obtainteacherapproval == 1
    ? get_string('obtainteacherapproval_yes', 'privatestudentfolder')
    : get_string('obtainteacherapproval_no', 'privatestudentfolder');

if ($privatestudentfoldermode == PRIVATESTUDENTFOLDER_MODE_FILEUPLOAD) {
    $templatecontext->mode = get_string('modeupload', 'privatestudentfolder');
    $templatecontext->obtainstudentapproval = $privatestudentfolderinstance->obtainstudentapproval == 1
        ? get_string('obtainstudentapproval_yes', 'privatestudentfolder')
        : get_string('obtainstudentapproval_no', 'privatestudentfolder');
} else {
    $templatecontext->mode = get_string('modeimport', 'privatestudentfolder');
    if ($privatestudentfoldermode == PRIVATESTUDENTFOLDER_MODE_ASSIGN_TEAMSUBMISSION) {
        $templatecontext->obtainstudentapprovaltitle = get_string('obtaingroupapproval', 'privatestudentfolder');
        if ($privatestudentfolderinstance->obtainstudentapproval == 0) {
            $templatecontext->obtainstudentapproval = get_string('obtainstudentapproval_no', 'privatestudentfolder');
        } else {
            $templatecontext->obtainstudentapproval =
                $privatestudentfolderinstance->groupapproval == PRIVATESTUDENTFOLDER_APPROVAL_ALL
                    ? get_string('obtaingroupapproval_all', 'privatestudentfolder')
                    : get_string('obtaingroupapproval_single', 'privatestudentfolder');
        }
    } else {
        $templatecontext->obtainstudentapproval = $privatestudentfolderinstance->obtainstudentapproval == 1
            ? get_string('obtainstudentapproval_yes', 'privatestudentfolder')
            : get_string('obtainstudentapproval_no', 'privatestudentfolder');
    }
}

if ($privatestudentfolderinstance->duedate > 0) {
    $timeremainingdiff = $privatestudentfolderinstance->duedate - time();
    if ($timeremainingdiff > 0) {
        $templatecontext->timeremaining = format_time($privatestudentfolderinstance->duedate - time());
    } else {
        $templatecontext->timeremaining = get_string('overdue', 'privatestudentfolder');
    }
}
$templatecontext->isteacher = false;
if (has_capability('mod/privatestudentfolder:approve', $context)) {
    $templatecontext->isteacher = true;
    $templatecontext->studentcount = count($privatestudentfolder->get_users([], true));
    $allfilestable = $privatestudentfolder->get_allfilestable(PRIVATESTUDENTFOLDER_FILTER_ALLFILES, true);
    $templatecontext->allfilescount = $allfilestable->get_count();
    $templatecontext->allfiles_url = (new moodle_url('/mod/privatestudentfolder/view.php',
        ['id' => $cm->id, 'filter' => PRIVATESTUDENTFOLDER_FILTER_ALLFILES, 'allfilespage' => 1]))->out(false);
    $templatecontext->allfiles_empty = $templatecontext->allfilescount == 0;
    $templatecontext->assign = $privatestudentfolder->get_importlink();
    if ($privatestudentfolderinstance->obtainteacherapproval == 1) {
        $templatecontext->viewall_approvalneeded_url = (new moodle_url('/mod/privatestudentfolder/view.php',
            ['id' => $cm->id, 'filter' => PRIVATESTUDENTFOLDER_FILTER_APPROVALREQUIRED, 'allfilespage' => 1]))->out(false);
        $templatecontext->showapprovalrequired = true;
        $notapprovedtable = $privatestudentfolder->get_allfilestable(PRIVATESTUDENTFOLDER_FILTER_APPROVALREQUIRED, true);
        $templatecontext->approvalrequiredcount = $notapprovedtable->get_count();
    }
}

/* Set mode for "filesarepersonal" */
$templatecontext->filesarepersonal = $privatestudentfolderinstance->filesarepersonal == 1
                                        ? get_string('filesarepersonal_yes', 'privatestudentfolder')
                                        : get_string('filesarepersonal_no', 'privatestudentfolder');

$mode = $privatestudentfolder->get_mode();
$templatecontext->myfilestitle = $mode == PRIVATESTUDENTFOLDER_MODE_ASSIGN_TEAMSUBMISSION
                                        ? get_string('mygroupfiles', 'privatestudentfolder')
                                        : get_string('myfiles', 'privatestudentfolder');

/* Get restricted files table (only documents that have been aproved) */
$filestable = $privatestudentfolder->get_filestable();

$filestable->init();

$templatecontext->myfiles = $filestable->data;
$templatecontext->hasmyfiles = count($templatecontext->myfiles) > 0;
$templatecontext->myfilesform = $filesform->render();
if (!$allfilespage) {
    echo $OUTPUT->render_from_template('mod_privatestudentfolder/overview', $templatecontext);
}

if ( has_capability('mod/privatestudentfolder:approve', $context) || $privatestudentfolderinstance->filesarepersonal == 0 ) {
    echo $allfilesform;
} else {
    /* TODO: Make sure all files are not avalaible, no just hidden */
    echo 'All files table not showing because files are personal.';
}

echo $OUTPUT->footer();
