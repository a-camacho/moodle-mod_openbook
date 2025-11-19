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
 * Displays a single mod_openbook instance
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/openbook/locallib.php');
require_once($CFG->dirroot . '/mod/openbook/mod_openbook_files_form.php');
require_once($CFG->dirroot . '/mod/openbook/mod_openbook_allfiles_form.php');

$id = required_param('id', PARAM_INT); // Course Module ID.
$allfilespage = optional_param('allfilespage', 0, PARAM_BOOL);

$url = new moodle_url('/mod/openbook/view.php', ['id' => $id, 'allfilespage' => $allfilespage]);
$cm = get_coursemodule_from_id('openbook', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_login($course, true, $cm);
$PAGE->set_url($url);

// Load the CSS of the plugin.
$PAGE->requires->css(new \moodle_url($CFG->wwwroot . '/mod/openbook/styles.css'));

$context = context_module::instance($cm->id);

require_capability('mod/openbook:view', $context);

if ($allfilespage) {
    require_capability('mod/openbook:approve', $context);
}

$openbook = new openbook($cm, $course, $context);

$openbook->set_allfilespage($allfilespage);

$event = \mod_openbook\event\course_module_viewed::create([
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
]);
$event->add_record_snapshot('course', $PAGE->course);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$pagetitle = strip_tags($course->shortname . ': ' . format_string($openbook->get_instance()->name));
$action = optional_param('action', 'view', PARAM_ALPHA);
$savevisibility = optional_param('savevisibility', false, PARAM_RAW);

$download = optional_param('download', 0, PARAM_INT);
if ($download > 0) {
    $openbook->download_file($download);
}

if ($savevisibility) {
    require_capability('mod/openbook:approve', $context);
    require_sesskey();

    $files = optional_param_array('files', [], PARAM_INT);
    $params = [];

    $params['pubid'] = $openbook->get_instance()->id;
    $openbook->update_files_teacherapproval($files);
    openbook::send_all_pending_notifications();
    redirect($url);
} else if ($action == 'zip') {
    $openbook->download_zip(true);
} else if ($action == 'zipusers') {
    $users = optional_param_array('selecteduser', false, PARAM_INT);
    if (!$users) {
        // No users selected.
        header('Location: view.php?id=' . $id);
        die();
    }
    $users = array_keys($users);
    $openbook->download_zip($users);
} else if ($action == 'grantextension') {
    require_capability('mod/openbook:grantextension', $context);
    require_sesskey();

    $users = optional_param_array('selecteduser', [], PARAM_INT);
    $users = array_keys($users);

    if (count($users) > 0) {
        $url = new moodle_url('/mod/openbook/grantextension.php', ['id' => $cm->id]);
        foreach ($users as $idx => $u) {
            $url->param('userids[' . $idx . ']', $u);
        }

        redirect($url);
        die();
    }
} else if ($action == 'approveusers' || $action == 'rejectusers' || $action == 'resetstudentapproval') {
    require_capability('mod/openbook:approve', $context);
    require_sesskey();

    $userorgroupids = optional_param_array('selecteduser', [], PARAM_INT);
    $userorgroupids = array_keys($userorgroupids);
    if (count($userorgroupids) > 0) {
        $openbook->update_users_or_groups_teacherapproval($userorgroupids, $action);
        openbook::send_all_pending_notifications();
        redirect($url);
    }
}

$submissionid = $USER->id;

$filesform = new mod_openbook_files_form(
    null,
    ['openbook' => $openbook, 'sid' => $submissionid, 'filearea' => 'attachment']
);

if ($data = $filesform->get_data()) {
    $datasubmitted = $filesform->get_submitted_data();

    if (isset($datasubmitted->gotoupload)) {
        redirect(new moodle_url(
            '/mod/openbook/upload.php',
            ['id' => $openbook->get_instance()->id, 'cmid' => $cm->id]
        ));
    }
    if ($openbook->is_approval_open()) {
        $studentapproval = optional_param_array('studentapproval', [], PARAM_INT);

        $conditions = [];
        $conditions['openbook'] = $openbook->get_instance()->id;
        $conditions['userid'] = $USER->id;

        $pubfileids = $DB->get_records_menu(
            'openbook_file',
            [
                'openbook' => $openbook->get_instance()->id,
            ],
            'id ASC',
            'fileid, id'
        );

        // Update records.
        foreach ($studentapproval as $idx => $approval) {
            $conditions['fileid'] = $idx;

            if ($approval != 1 && $approval != 2) {
                continue;
            }
            $dataforlog = new stdClass();
            $dataforlog->approval = $approval == 1
                ? get_string('approved', 'openbook')
                : get_string('rejected', 'openbook');
            $stats = null;

            $DB->set_field('openbook_file', 'studentapproval', $approval, $conditions);
            if (is_array($stats)) {
                $dataforlog->approval = get_string('datalogapprovalstudent', 'openbook', [
                    'approving' => $stats['approving'],
                    'needed' => $stats['needed'],
                    'approval' => $dataforlog->approval,
                ]);
            }
            $dataforlog->openbook = $conditions['openbook'];
            $dataforlog->userid = $USER->id;
            $dataforlog->reluser = $USER->id;
            $dataforlog->fileid = $idx;

            \mod_openbook\event\openbook_approval_changed::approval_changed($cm, $dataforlog)->trigger();
            if ($openbook->get_instance()->notifystatuschange != 0) {
                $pubfile = $DB->get_record('openbook_file', ['id' => $pubfileids[$idx]]);
                $newstatus = $approval == 2 ? 'not' : ''; // Used for string identifier..
                openbook::send_notification_statuschange(
                    $cm,
                    $USER,
                    $newstatus,
                    $pubfile,
                    $cm->id,
                    $openbook
                );
            }
        }
        openbook::send_all_pending_notifications();
        redirect($url);
    }
}

$filesform = new mod_openbook_files_form(
    null,
    ['openbook' => $openbook, 'sid' => $submissionid, 'filearea' => 'attachment']
);

// Print the page header.
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);
if (!$allfilespage) {
    $PAGE->add_body_class('limitedwidth');
} else {
    $PAGE->add_body_class('allfilespage');
}

// For teacher and manager/admins do not go into secure window layout.
if (!has_capability('moodle/course:update', context_course::instance($course->id))) {
    if ($openbook->is_securewindow_enforced()) {
        $PAGE->set_pagelayout('secure');
    }
}
echo $OUTPUT->header();

$allfilesform = $openbook->display_allfilesform();

$openbookinstance = $openbook->get_instance();
$templatecontext = new stdClass();
$templatecontext->obtainstudentapprovaltitle = get_string('obtainstudentapproval', 'openbook');
$templatecontext->obtainteacherapproval = $openbookinstance->obtainteacherapproval == 1
    ? get_string('obtainteacherapproval_yes', 'openbook')
    : get_string('obtainteacherapproval_no', 'openbook');

$templatecontext->obtainstudentapproval = $openbookinstance->obtainstudentapproval == 1
    ? get_string('obtainstudentapproval_yes', 'openbook')
    : get_string('obtainstudentapproval_no', 'openbook');

if ($openbookinstance->duedate > 0) {
    $timeremainingdiff = $openbookinstance->duedate - time();
    if ($timeremainingdiff > 0) {
        $templatecontext->timeremaining = format_time($openbookinstance->duedate - time());
    } else {
        $templatecontext->timeremaining = get_string('overdue', 'openbook');
    }
}
$templatecontext->isteacher = false;
if (has_capability('mod/openbook:approve', $context)) {
    $templatecontext->isteacher = true;
    // TODO : Count only students ?
    $templatecontext->studentcount = count($openbook->get_users([], true));
    $allfilestable = $openbook->get_allfilestable(OPENBOOK_FILTER_ALLFILES, true);
    $templatecontext->allfilescount = $allfilestable->get_count();
    $templatecontext->allfiles_url = (new moodle_url(
        '/mod/openbook/view.php',
        ['id' => $cm->id, 'filter' => OPENBOOK_FILTER_ALLFILES, 'allfilespage' => 1]
    ))->out(false);
    $templatecontext->allfiles_empty = $templatecontext->allfilescount == 0;
    if ($openbookinstance->obtainteacherapproval == 1) {
        $templatecontext->viewall_approvalneeded_url = (new moodle_url(
            '/mod/openbook/view.php',
            ['id' => $cm->id, 'filter' => OPENBOOK_FILTER_APPROVALREQUIRED, 'allfilespage' => 1]
        ))->out(false);
        $templatecontext->showapprovalrequired = true;
        $notapprovedtable = $openbook->get_allfilestable(OPENBOOK_FILTER_APPROVALREQUIRED, true);
        $templatecontext->approvalrequiredcount = $notapprovedtable->get_count();
    }
    $teacherfilestable = $openbook->get_allfilestable(OPENBOOK_FILTER_APPROVALREQUIRED, true);
    $templatecontext->teacherfilescount = $teacherfilestable->get_count();
    if ($templatecontext->teacherfilescount > 0) {
        $templatecontext->showteacherfiles = true;
    } else {
        $templatecontext->showteacherfiles = false;
    }
}

/* Set mode for "filesarepersonal" */

$templatecontext->filesarepersonal = $openbookinstance->filesarepersonal == 1
                                                ? get_string('filesarepersonal_yes', 'openbook')
                                                : get_string('filesarepersonal_no', 'openbook');

$templatecontext->myfilestitle = get_string('myfiles', 'openbook');

if (has_capability('moodle/course:update', context_course::instance($course->id))) {
    $templatecontext->myfilestitle = get_string('teacherfiles', 'openbook');
}

/* Show "own files" table */

$filestable = $openbook->get_filestable();

$filestable->init();

$templatecontext->myfiles = $filestable->data;
$templatecontext->hasmyfiles = count($templatecontext->myfiles) > 0;
$templatecontext->myfilesform = $filesform->render();

if (!$allfilespage) {
    echo $OUTPUT->render_from_template('mod_openbook/overview', $templatecontext);
}

/* Show "teacher files" table */

if (!$allfilespage) {

    $teacherfilesform = $openbook->display_teacherfilesform();
    // echo $teacherfilesform;

    $data = [
        'uniqid'  => 'id_teacherfilescontainer',
        'title'   => get_string('teacher_files', 'openbook'),
        'content' => $teacherfilesform,
        'open'    => true
    ];

    echo $OUTPUT->render_from_template('mod_openbook/collapsible', $data);

}

/* Show "files shared by students" table */

$content_html = '';

if (has_capability('mod/openbook:approve', $context) || $openbookinstance->filesarepersonal == 0) {
    $content_html = $allfilesform;
} else {
    /* TODO: Make sure all files are not available, no just hidden */
    $content_html = get_string('allfilesnotshowing', 'openbook');
}

$container_data = [
    'uniqid'  => 'id_allfilescontainer',
    'title'   => get_string('publicfiles', 'openbook'),
    'content' => $content_html,
    'open'    => true
];

echo $OUTPUT->render_from_template('mod_openbook/collapsible', $container_data);

echo $OUTPUT->footer();
