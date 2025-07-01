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
 * Contains form class for approving privatestudentfolder files
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/privatestudentfolder/locallib.php');

/**
 * Form for displaying and changing approval for privatestudentfolder files
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_privatestudentfolder_files_form extends moodleform {
    /**
     * Form definition method_exists
     */
    public function definition() {
        global $DB, $PAGE, $OUTPUT, $USER;

        $privatestudentfolder = &$this->_customdata['privatestudentfolder'];

        $mform = $this->_form;

        $mode = $privatestudentfolder->get_mode();

        $privatestudentfolderinstance = $privatestudentfolder->get_instance();

        $noticestudentstringid = '';
        $noticeteacherid = '';
        $noticemode = '';

        if ($mode == PRIVATESTUDENTFOLDER_MODE_FILEUPLOAD) {
            $noticemode = 'upload';
        } else {
            $noticemode = 'import';
        }

        /* Check if files are personal */
        if ($privatestudentfolderinstance->filesarepersonal) {

            if ($privatestudentfolderinstance->obtainteacherapproval) {
                $noticeteacherid = 'teacherrequired';
            } else {
                $noticeteacherid = 'teachernotrequired';
            }

            $noticestudentstringid = 'filesarepersonal';

        } else {

            if ($privatestudentfolderinstance->obtainstudentapproval) {
                if ($mode == PRIVATESTUDENTFOLDER_MODE_ASSIGN_TEAMSUBMISSION) {
                    if ($privatestudentfolderinstance->groupapproval == PRIVATESTUDENTFOLDER_APPROVAL_ALL) {
                        $noticestudentstringid = 'all';
                    } else {
                        $noticestudentstringid = 'one';
                    }
                    $noticemode = 'group';
                } else {
                    $noticestudentstringid = 'studentrequired';
                }
            } else {
                $noticestudentstringid = 'studentnotrequired';
            }

            if ($privatestudentfolderinstance->obtainteacherapproval) {
                $noticeteacherid = 'teacherrequired';
            } else {
                $noticeteacherid = 'teachernotrequired';
            }

        }

        $stringid = 'notice_' . $noticemode . '_' . $noticestudentstringid . '_' . $noticeteacherid;

        if ($mode == PRIVATESTUDENTFOLDER_MODE_ASSIGN_TEAMSUBMISSION) {
            $headertext = get_string('mygroupfiles', 'privatestudentfolder');
        } else {
            $headertext = get_string('myfiles', 'privatestudentfolder');
        }
        $notice = get_string($stringid, 'privatestudentfolder');

        if ($mode == PRIVATESTUDENTFOLDER_MODE_ASSIGN_TEAMSUBMISSION) {
            $notice = get_string('notice_files_imported_group', 'privatestudentfolder') . ' ' . $notice;
        } else if ($mode == PRIVATESTUDENTFOLDER_MODE_ASSIGN_IMPORT) {
            $notice = get_string('notice_files_imported', 'privatestudentfolder') . ' ' . $notice;
        }

        if ($mode != PRIVATESTUDENTFOLDER_MODE_FILEUPLOAD) {
            $notice .= '<br />' . get_string('notice_changes_possible_in_original', 'privatestudentfolder');
        }

        $table = $privatestudentfolder->get_filestable();

        $mform->addElement('header', 'myfiles', $headertext);
        $mform->setExpanded('myfiles');

        $PAGE->requires->js_call_amd('mod_privatestudentfolder/filesform', 'initializer', []);
        $PAGE->requires->js_call_amd('mod_privatestudentfolder/alignrows', 'initializer', []);

        $noticehtml = html_writer::start_tag('div', ['class' => 'alert alert-info']);
        $noticehtml .= get_string('notice', 'privatestudentfolder') . ' ' . $notice;
        $noticehtml .= html_writer::end_tag('div');

        $mform->addElement('html', $noticehtml);

        // Now we do all the table work and return 0 if there's no files to show!
        $table->init();

        $mode = $privatestudentfolder->get_mode();
        $timeremaining = false;
        $privatestudentfolderinstance = $privatestudentfolder->get_instance();

        $extensionduedate = $privatestudentfolder->user_extensionduedate($USER->id);
        $override = $privatestudentfolder->override_get_currentuserorgroup();
        if ($override && $override->approvalfromdate) {
            $approvalfromdate = $override->approvalfromdate > 0 ? userdate($override->approvalfromdate) : false;
            $approvaltodate = $override->approvaltodate > 0 ? userdate($override->approvaltodate) : false;
        } else {
            $approvalfromdate = $privatestudentfolderinstance->approvalfromdate > 0 ? userdate($privatestudentfolderinstance->approvalfromdate) : false;
            $approvaltodate = $privatestudentfolderinstance->approvaltodate > 0 ? userdate($privatestudentfolderinstance->approvaltodate) : false;
        }

        if ($privatestudentfolderinstance->duedate > 0 || ($override && $override->submissionoverride && $override->duedate > 0)) {
            if ($override && $override->submissionoverride && $override->duedate > 0) {
                $timeremainingdiff = $override->duedate - time();
            } else {
                $timeremainingdiff = $privatestudentfolderinstance->duedate - time();
            }
            if ($timeremainingdiff > 0) {
                $timeremaining = format_time($privatestudentfolderinstance->duedate - time());
            } else {
                $timeremaining = get_string('overdue', 'privatestudentfolder');
            }
        }

        $extensionduedate = $extensionduedate > 0 ? userdate($extensionduedate) : false;
        if (!$privatestudentfolderinstance->obtainstudentapproval) {
            $approvalfromdate = false;
            $approvaltodate = false;
        }
        $tablecontext = [
            'myfiles' => $table->data,
            'hasmyfiles' => !empty($table->data),
            'timeremaining' => $timeremaining,
            'lastmodified' => userdate($table->lastmodified),
            'approvalfromdate' => $approvalfromdate,
            'approvaltodate' => $approvaltodate,
            'extensionduedate' => $extensionduedate,
            'assign' => $privatestudentfolder->get_importlink(),
            'myfilestitle' => $mode == PRIVATESTUDENTFOLDER_MODE_ASSIGN_TEAMSUBMISSION
                                ? get_string('mygroupfiles', 'privatestudentfolder')
                                : get_string('myfiles', 'privatestudentfolder'),
        ];
        $myfilestable = $OUTPUT->render_from_template('mod_privatestudentfolder/myfiles', $tablecontext);
        $myfilestable = '<table class="table table-striped w-100">' . $myfilestable . '</table>';
        $mform->addElement('html', $myfilestable);

        // Display submit buttons if necessary.
        if ($privatestudentfolderinstance->obtainstudentapproval) {
            if (!empty($table) && $table->changepossible()) {
                $buttonarray = [];

                $onclick = 'return confirm("' . get_string('savestudentapprovalwarning', 'privatestudentfolder') . '")';

                $buttonarray[] = &$mform->createElement('submit', 'submitbutton',
                    get_string('savechanges'), ['onClick' => $onclick]);
                $buttonarray[] = &$mform->createElement('reset', 'resetbutton', get_string('revert'),
                    ['class' => 'btn btn-secondary']);

                $mform->addGroup($buttonarray, 'submitgrp', '', [' '], false);
            } else {
                $mform->addElement('static', 'approvaltimeover', '', get_string('approval_timeover', 'privatestudentfolder'));
            }
        }

        if ($privatestudentfolder->get_instance()->mode == PRIVATESTUDENTFOLDER_MODE_UPLOAD
            && has_capability('mod/privatestudentfolder:upload', $privatestudentfolder->get_context())) {
            if ($privatestudentfolder->is_open()) {
                $buttonarray = [];

                if (empty($table)) { // This means, there are no files shown!
                    $label = get_string('add_uploads', 'privatestudentfolder');
                } else {
                    $label = get_string('edit_uploads', 'privatestudentfolder');
                }

                $buttonarray[] = &$mform->createElement('submit', 'gotoupload', $label);
                $mform->addGroup($buttonarray, 'uploadgrp', '', [' '], false);
            } else if (has_capability('mod/privatestudentfolder:upload', $privatestudentfolder->get_context())) {
                $mform->addElement('static', 'edittimeover', '', get_string('edit_timeover', 'privatestudentfolder'));
            }
        }

        $mform->addElement('hidden', 'id', $privatestudentfolder->get_coursemodule()->id);
        $mform->setType('id', PARAM_INT);

        $mform->disable_form_change_checker();
    }
}
