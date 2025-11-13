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
 * Contains form class for approving openbook files
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/openbook/locallib.php');

/**
 * Form for displaying and changing approval for openbook files
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_openbook_files_form extends moodleform {
    /**
     * Form definition method_exists
     */
    public function definition() {
        global $DB, $PAGE, $OUTPUT, $USER;

        $openbook = &$this->_customdata['openbook'];

        $mform = $this->_form;

        $mode = $openbook->get_mode();

        $openbookinstance = $openbook->get_instance();

        $noticestudentstringid = '';
        $noticeteacherid = '';
        $noticemode = '';

        if ($mode == OPENBOOK_MODE_FILEUPLOAD) {
            $noticemode = 'upload';
        } else {
            $noticemode = 'import';
        }

        /* Check if files are personal */
        if ($openbookinstance->filesarepersonal) {
            if ($openbookinstance->obtainteacherapproval) {
                $noticeteacherid = 'teacherrequired';
            } else {
                $noticeteacherid = 'teachernotrequired';
            }

            $noticestudentstringid = 'filesarepersonal';
        } else {
            if ($openbookinstance->obtainstudentapproval) {
                $noticestudentstringid = 'studentrequired';
            } else {
                $noticestudentstringid = 'studentnotrequired';
            }

            if ($openbookinstance->obtainteacherapproval) {
                $noticeteacherid = 'teacherrequired';
            } else {
                $noticeteacherid = 'teachernotrequired';
            }
        }

        $table = $openbook->get_filestable();

        $mform->addElement('header', 'myfiles', get_string('myfiles', 'openbook'));
        $mform->setExpanded('myfiles');

        $PAGE->requires->js_call_amd('mod_openbook/filesform', 'initializer', []);
        $PAGE->requires->js_call_amd('mod_openbook/alignrows', 'initializer', []);

        // Now we do all the table work and return 0 if there's no files to show!
        $table->init();

        $mode = $openbook->get_mode();
        $timeremaining = false;
        $openbookinstance = $openbook->get_instance();

        $override = $openbook->override_get_currentuserorgroup();
        if ($override && $override->approvalfromdate) {
            $approvalfromdate = $override->approvalfromdate > 0 ? userdate($override->approvalfromdate) : false;
            $approvaltodate = $override->approvaltodate > 0 ? userdate($override->approvaltodate) : false;
        } else {
            $approvalfromdate = $openbookinstance->approvalfromdate > 0 ?
                userdate($openbookinstance->approvalfromdate) : false;
            $approvaltodate = $openbookinstance->approvaltodate > 0 ?
                userdate($openbookinstance->approvaltodate) : false;
        }

        if ($openbookinstance->duedate > 0 || ($override && $override->submissionoverride && $override->duedate > 0)) {
            if ($override && $override->submissionoverride && $override->duedate > 0) {
                $timeremainingdiff = $override->duedate - time();
            } else {
                $timeremainingdiff = $openbookinstance->duedate - time();
            }
            if ($timeremainingdiff > 0) {
                $timeremaining = format_time($openbookinstance->duedate - time());
            } else {
                $timeremaining = get_string('overdue', 'openbook');
            }
        }

        $tablecontext = [
            'myfiles' => $table->data,
            'hasmyfiles' => !empty($table->data),
            'timeremaining' => $timeremaining,
            'lastmodified' => userdate($table->lastmodified),
            'approvalfromdate' => $approvalfromdate,
            'approvaltodate' => $approvaltodate,
            'myfilestitle' => get_string('myfiles', 'openbook'),
        ];
        /* TODO : Add PDF.js link to myfiles table */
        $myfilestable = $OUTPUT->render_from_template('mod_openbook/myfiles', $tablecontext);
        $myfilestable = $myfilestable;
        $mform->addElement('html', $myfilestable);

        // Display submit buttons if necessary.
        if ($openbookinstance->obtainstudentapproval) {
            if (!empty($table) && $table->changepossible()) {
                $buttonarray = [];

                $onclick = 'return confirm("' . get_string('savestudentapprovalwarning', 'openbook') . '")';

                $buttonarray[] = &$mform->createElement(
                    'submit',
                    'submitbutton',
                    get_string('savechanges'),
                    ['onClick' => $onclick]
                );
                $buttonarray[] = &$mform->createElement(
                    'reset',
                    'resetbutton',
                    get_string('revert'),
                    ['class' => 'btn btn-secondary']
                );

                $mform->addGroup($buttonarray, 'submitgrp', '', [' '], false);
            } else {
                $noticehtml = html_writer::start_tag('div', ['class' => 'alert alert-secondary']);
                $noticehtml .= get_string('approval_timeover', 'openbook');
                $noticehtml .= html_writer::end_tag('div');

                $mform->addElement('html', $noticehtml);
            }
        }

        if (
            has_capability('mod/openbook:upload', $openbook->get_context())
        ) {
            if ($openbook->is_open()) {
                $buttonarray = [];

                if (empty($table)) { // This means, there are no files shown!
                    $label = get_string('add_uploads', 'openbook');
                } else {
                    $label = get_string('edit_uploads', 'openbook');
                }

                $buttonarray[] = &$mform->createElement('submit', 'gotoupload', $label);
                $mform->addGroup($buttonarray, 'uploadgrp', '', [' '], false);
            } else if (has_capability('mod/openbook:upload', $openbook->get_context())) {
                $mform->addElement('static', 'edittimeover', '', get_string('edit_timeover', 'openbook'));
            }
        }

        $mform->addElement('hidden', 'id', $openbook->get_coursemodule()->id);
        $mform->setType('id', PARAM_INT);

        $mform->disable_form_change_checker();
    }
}
