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
 * Instance settings form.
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
 * Form for creating and editing mod_privatestudentfolder instances
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_privatestudentfolder_mod_form extends moodleform_mod {

    /** @var object $_teamassigns */
    private $_teamassigns;

    /** @var object $_notteamassigns */
    private $_notteamassigns;

    /**
     * Define this form - called by the parent constructor
     */
    public function definition() {
        global $DB, $CFG, $COURSE, $PAGE;

        $mform = $this->_form;
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name.
        $mform->addElement('text', 'name', get_string('name', 'privatestudentfolder'), ['size' => '64']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        // Adding the standard "intro" and "introformat" fields!
        $this->standard_intro_elements();

        // Private Student Folder specific elements.
        $mform->addElement('header', 'submissionsettings', get_string('submissionsettings', 'privatestudentfolder'));
        $mform->setExpanded('submissionsettings');

        if (isset($this->current->id) && $this->current->id != "") {
            $filecount = $DB->count_records('privatestudentfolder_file', ['privatestudentfolder' => $this->current->id]);
        } else {
            $filecount = 0;
        }

        $disabled = [];
        if ($filecount > 0) {
            $disabled['disabled'] = 'disabled';
        }

        $modearray = [];
        $modearray[] =& $mform->createElement('radio', 'mode', '', get_string('modeupload', 'privatestudentfolder'),
                PRIVATESTUDENTFOLDER_MODE_UPLOAD, $disabled);
        $modearray[] =& $mform->createElement('radio', 'mode', '', get_string('modeimport', 'privatestudentfolder'),
                PRIVATESTUDENTFOLDER_MODE_IMPORT, $disabled);
        $mform->addGroup($modearray, 'modegrp', get_string('mode', 'privatestudentfolder'), [' '], false);
        $mform->addHelpButton('modegrp', 'mode', 'privatestudentfolder');
        if ($filecount === 0) {
            $mform->addRule('modegrp', null, 'required', null, 'client');
        }

        // Private Student Folder mode import specific elements.
        $choices = [];
        $choices[-1] = get_string('choose', 'privatestudentfolder');
        $assigninstances = $DB->get_records('assign', ['course' => $COURSE->id], 'name ASC');
        $module = $DB->get_record('modules', ['name' => 'assign']);
        $select = $mform->createElement('select', 'importfrom', get_string('assignment', 'privatestudentfolder'), $choices, $disabled);
        $notteamassigns = [-1];
        $teamassigns = [];
        foreach ($assigninstances as $assigninstance) {
            $cm = $DB->get_record('course_modules', ['module' => $module->id, 'instance' => $assigninstance->id]);
            if ($cm->deletioninprogress == 1) {
                continue;
            }
            if (!$assigninstance->teamsubmission) {
                $notteamassigns[] = $assigninstance->id;
            } else {
                $teamassigns[] = $assigninstance->id;
            }
            $attributes = ['data-teamsubmission' => $assigninstance->teamsubmission];
            $select->addOption($assigninstance->name, $assigninstance->id, $attributes);
        }
        $this->_teamassigns = $teamassigns;
        $this->_notteamassigns = $notteamassigns;
        $mform->addElement($select);
        $mform->addHelpButton('importfrom', 'assignment', 'privatestudentfolder');
        $mform->hideIf('importfrom', 'mode', 'neq', PRIVATESTUDENTFOLDER_MODE_IMPORT);
        $mform->addElement('html', '<span id="teamassignids" data-assignids="' . implode(',', $teamassigns) . '"></span>');

        // Private Student Folder mode upload specific elements.
        $maxfiles = [];
        for ($i = 1; $i <= 100 || $i <= get_config('privatestudentfolder', 'maxfiles'); $i++) {
            $maxfiles[$i] = $i;
        }

        $mform->addElement('select', 'maxfiles', get_string('maxfiles', 'privatestudentfolder'), $maxfiles);
        $mform->setDefault('maxfiles', get_config('privatestudentfolder', 'maxfiles'));
        $mform->addHelpButton('maxfiles', 'maxfiles', 'privatestudentfolder');
        $mform->hideIf('maxfiles', 'mode', 'neq', PRIVATESTUDENTFOLDER_MODE_UPLOAD);

        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes);
        $choices[0] = get_string('courseuploadlimit', 'privatestudentfolder') . ' (' . display_size($COURSE->maxbytes) . ')';
        $mform->addElement('select', 'maxbytes', get_string('maxbytes', 'privatestudentfolder'), $choices);
        $mform->setDefault('maxbytes', get_config('privatestudentfolder', 'maxbytes'));
        $mform->addHelpButton('maxbytes', 'maxbytes', 'privatestudentfolder');
        $mform->hideIf('maxbytes', 'mode', 'neq', PRIVATESTUDENTFOLDER_MODE_UPLOAD);

        $mform->addElement('filetypes', 'allowedfiletypes', get_string('allowedfiletypes', 'privatestudentfolder'));
        $mform->addHelpButton('allowedfiletypes', 'allowedfiletypes', 'privatestudentfolder');
        $mform->hideIf('allowedfiletypes', 'mode', 'neq', PRIVATESTUDENTFOLDER_MODE_UPLOAD);

        $name = get_string('allowsubmissionsfromdate', 'privatestudentfolder');
        $options = ['optional' => true];
        $mform->addElement('date_time_selector', 'allowsubmissionsfromdate', $name, $options);
        $mform->addHelpButton('allowsubmissionsfromdate', 'allowsubmissionsfromdate', 'privatestudentfolder');
        $mform->setDefault('allowsubmissionsfromdate', time());
        $mform->hideIf('allowsubmissionsfromdate', 'mode', 'neq', PRIVATESTUDENTFOLDER_MODE_UPLOAD);

        $name = get_string('duedate', 'privatestudentfolder');
        $mform->addElement('date_time_selector', 'duedate', $name, ['optional' => true]);
        $mform->addHelpButton('duedate', 'duedate', 'privatestudentfolder');
        $mform->setDefault('duedate', time() + 7 * 24 * 3600);
        $mform->hideIf('duedate', 'mode', 'neq', PRIVATESTUDENTFOLDER_MODE_UPLOAD);

        $mform->addElement('hidden', 'cutoffdate', false);
        $mform->setType('cutoffdate', PARAM_BOOL);

        // Approval settings start.
        $mform->addElement('header', 'approvalsettings', get_string('approvalsettings', 'privatestudentfolder'));
        $mform->setExpanded('approvalsettings', true);

        // Files are personal
        $attributes = [];
        $options = [
            '0' => get_string('filesarepersonal_no', 'privatestudentfolder'),
            '1' => get_string('filesarepersonal_yes', 'privatestudentfolder'),
        ];

        $mform->addElement('select', 'filesarepersonal',
            get_string('filesarepersonal', 'privatestudentfolder'), $options, $attributes);
        $mform->setDefault('filesarepersonal', get_config('privatestudentfolder', 'filesarepersonal'));
        $mform->addHelpButton('filesarepersonal', 'filesarepersonal', 'privatestudentfolder');

        // Teacher approval.
        $attributes = [];
        $options = [
            '0' => get_string('obtainapproval_automatic', 'privatestudentfolder'),
            '1' => get_string('obtainapproval_required', 'privatestudentfolder'),
        ];

        $mform->addElement('select', 'obtainteacherapproval',
            get_string('obtainteacherapproval', 'privatestudentfolder'), $options, $attributes);
        $mform->setDefault('obtainteacherapproval', get_config('privatestudentfolder', 'obtainteacherapproval'));
        $mform->addHelpButton('obtainteacherapproval', 'obtainteacherapproval', 'privatestudentfolder');

        // Student approval.
        $attributes = [];
        $options = [
            '0' => get_string('obtainapproval_automatic', 'privatestudentfolder'),
            '1' => get_string('obtainapproval_required', 'privatestudentfolder'),
        ];

        $mform->addElement('select', 'obtainstudentapproval', get_string('obtainstudentapproval', 'privatestudentfolder'), $options, $attributes);
        $mform->setDefault('obtainstudentapproval', get_config('privatestudentfolder', 'obtainstudentapproval'));
        $mform->addHelpButton('obtainstudentapproval', 'obtainstudentapproval', 'privatestudentfolder');
        // $mform->hideIf('obtainstudentapproval', 'importfrom', 'in', $teamassigns);

        // Group approval.
        $attributes = [];
        $options = [
            PRIVATESTUDENTFOLDER_APPROVAL_GROUPAUTOMATIC => get_string('obtainapproval_automatic', 'privatestudentfolder'),
            PRIVATESTUDENTFOLDER_APPROVAL_SINGLE => get_string('obtaingroupapproval_single', 'privatestudentfolder'),
            PRIVATESTUDENTFOLDER_APPROVAL_ALL => get_string('obtaingroupapproval_all', 'privatestudentfolder'),
        ];

        $mform->addElement('select', 'obtaingroupapproval', get_string('obtaingroupapproval', 'privatestudentfolder'), $options, $attributes);
        $mform->setDefault('obtaingroupapproval',  get_config('privatestudentfolder', 'obtaingroupapproval'));
        $mform->addHelpButton('obtaingroupapproval', 'obtaingroupapproval', 'privatestudentfolder');
        // $mform->hideIf('obtaingroupapproval', 'importfrom', 'in', $notteamassigns);

        /*foreach ($notteamassigns as $cur) {
            $mform->hideIf('obtaingroupapproval', 'importfrom', 'eq', $cur);
        }*/

        // $mform->hideIf('obtainstudentapproval', 'mode', 'neq', PRIVATESTUDENTFOLDER_MODE_IMPORT);

        /* $radioarray = [];
        $radioarray[] = $mform->createElement('radio', 'groupapproval', '', get_string('obtaingroupapproval_all', 'privatestudentfolder'),
            PRIVATESTUDENTFOLDER_APPROVAL_ALL, $attributes);
        $radioarray[] = $mform->createElement('radio', 'groupapproval', '', get_string('obtaingroupapproval_single', 'privatestudentfolder'),
            PRIVATESTUDENTFOLDER_APPROVAL_SINGLE, $attributes);
        $mform->addGroup($radioarray, 'groupapprovalarray', get_string('obtaingroupapproval', 'privatestudentfolder'),
            [html_writer::empty_tag('br')], false);
        $mform->addHelpButton('groupapprovalarray', 'obtaingroupapproval', 'privatestudentfolder');
        $mform->setDefault('groupapproval', PRIVATESTUDENTFOLDER_APPROVAL_ALL);
        $mform->hideIf('groupapprovalarray', 'mode', 'neq', PRIVATESTUDENTFOLDER_MODE_IMPORT);
        $mform->hideIf('groupapprovalarray', 'obtainstudentapproval', 'eq', 0);
        foreach ($notteamassigns as $cur) {
            $mform->hideIf('groupapprovalarray', 'importfrom', 'eq', $cur);
        }

        //  $mform->hideIf('obtainteacherapproval', 'mode', 'neq', PRIVATESTUDENTFOLDER_MODE_UPLOAD);

        /*   $infostrings = [
            'notice_obtainapproval_import_both',
            'notice_obtainapproval_import_studentonly',
            'notice_obtainapproval_upload_teacher',
            'notice_obtainapproval_upload_automatic',
        ];
        foreach ($infostrings as $infostring) {
            $infohtml = html_writer::start_tag('div', ['class' => 'alert alert-info']);
            $infohtml .= get_string($infostring, 'privatestudentfolder');
            $infohtml .= html_writer::end_tag('div');
            $infogroupimport =& $mform->createElement('static', $infostring, '', $infohtml);
            $mform->addGroup([$infogroupimport], $infostring . '_group', '', ' ', false);
        }
        $mform->hideIf('notice_obtainapproval_import_both_group', 'mode', 'neq', PRIVATESTUDENTFOLDER_MODE_IMPORT);
        $mform->hideIf('notice_obtainapproval_import_both_group', 'obtainstudentapproval', 'neq', '1');
        $mform->hideIf('notice_obtainapproval_import_studentonly_group', 'mode', 'neq', PRIVATESTUDENTFOLDER_MODE_IMPORT);
        $mform->hideIf('notice_obtainapproval_import_studentonly_group', 'obtainstudentapproval', 'neq', '0');

        $mform->hideIf('notice_obtainapproval_upload_teacher_group', 'obtainteacherapproval', 'neq', '0');
        $mform->hideIf('notice_obtainapproval_upload_teacher_group', 'mode', 'neq', PRIVATESTUDENTFOLDER_MODE_UPLOAD);
        $mform->hideIf('notice_obtainapproval_upload_automatic_group', 'obtainteacherapproval', 'neq', '1');
        $mform->hideIf('notice_obtainapproval_upload_automatic_group', 'mode', 'neq', PRIVATESTUDENTFOLDER_MODE_UPLOAD);*/

        $mform->addElement('date_time_selector', 'approvalfromdate', get_string('approvalfromdate', 'privatestudentfolder'), ['optional' => true]);
        $mform->addHelpButton('approvalfromdate', 'approvalfromdate', 'privatestudentfolder');
        $mform->setDefault('approvalfromdate', time());

        $mform->addElement('date_time_selector', 'approvaltodate', get_string('approvaltodate', 'privatestudentfolder'), ['optional' => true]);
        $mform->addHelpButton('approvaltodate', 'approvaltodate', 'privatestudentfolder');
        $mform->setDefault('approvaltodate', time() + 7 * 24 * 3600);
        // Approval code end.

        $mform->addElement('hidden', 'alwaysshowdescription', true);
        $mform->setType('alwaysshowdescription', PARAM_BOOL);

        // Apply availability restrictions.
        $mform->addElement('select', 'availabilityrestriction', get_string('availabilityrestriction', 'privatestudentfolder'),
                [get_string('no'), get_string('yes')]);
        $mform->setDefault('availabilityrestriction', get_config('privatestudentfolder', 'availabilityrestriction'));
        $mform->addHelpButton('availabilityrestriction', 'availabilityrestriction', 'privatestudentfolder');

        $mform->addElement('header', 'notifications', get_string('notifications', 'privatestudentfolder'));

        $options = [
            PRIVATESTUDENTFOLDER_NOTIFY_NONE => get_string('notify:setting:0', 'privatestudentfolder'),
            PRIVATESTUDENTFOLDER_NOTIFY_TEACHER => get_string('notify:setting:1', 'privatestudentfolder'),
            PRIVATESTUDENTFOLDER_NOTIFY_STUDENT => get_string('notify:setting:2', 'privatestudentfolder'),
            PRIVATESTUDENTFOLDER_NOTIFY_ALL => get_string('notify:setting:3', 'privatestudentfolder'),
        ];

        $mform->addElement('select', 'notifyfilechange', get_string('notify:filechange', 'privatestudentfolder'), $options);
        $mform->addHelpButton('notifyfilechange', 'notify:filechange', 'privatestudentfolder');
        $mform->setDefault('notifyfilechange', get_config('privatestudentfolder', 'notifyfilechange'));

        $mform->addElement('select', 'notifystatuschange', get_string('notify:statuschange', 'privatestudentfolder'), $options);
        $mform->addHelpButton('notifystatuschange', 'notify:statuschange', 'privatestudentfolder');
        $mform->setDefault('notifystatuschange', get_config('privatestudentfolder', 'notifystatuschange'));

        /*
        $name = get_string('notifyteacher', 'privatestudentfolder');
        $mform->addElement('selectyesno', 'notifyteacher', $name);
        $mform->addHelpButton('notifyteacher', 'notifyteacher', 'privatestudentfolder');
        $mform->setDefault('notifyteacher', 0);

        $name = get_string('notifystudents', 'privatestudentfolder');
        $mform->addElement('selectyesno', 'notifystudents', $name);
        $mform->addHelpButton('notifystudents', 'notifystudents', 'privatestudentfolder');
        $mform->setDefault('notifystudents', 0);*/
        // Standard coursemodule elements.
        $this->standard_coursemodule_elements();

        // Buttons.
        $this->add_action_buttons();
        $PAGE->requires->js_call_amd('mod_privatestudentfolder/modform');
    }

    /**
     * Add any custom completion rules to the form.
     *
     * @return array Contains the names of the added form elements
     */
    public function add_completion_rules() {
        $mform =& $this->_form;

        $suffix = $this->get_suffix();
        $completionuploadlabel = 'completionupload' . $suffix;

        $mform->addElement('advcheckbox', $completionuploadlabel, '', get_string('completionupload', 'privatestudentfolder'));
        // Enable this completion rule by default.
        $mform->setDefault($completionuploadlabel, 1);
        $mform->hideIf($completionuploadlabel, 'mode', 'neq', PRIVATESTUDENTFOLDER_MODE_UPLOAD);
        return [$completionuploadlabel];
    }

    /**
     * Check if completion rule is enabled.
     *
     * @param object $data
     * @return bool
     */
    public function completion_rule_enabled($data) {
        $suffix = $this->get_suffix();
        $completionuploadlabel = 'completionupload' . $suffix;
        if ($data['mode'] == PRIVATESTUDENTFOLDER_MODE_UPLOAD && !empty($data[$completionuploadlabel])) {
            return true;
        }
        return false;
    }

    /**
     * Function that post processes data
     *
     * @param object $data
     */
    public function data_postprocessing($data) {
        global $DB;
        parent::data_postprocessing($data);
        $suffix = $this->get_suffix();
        $completionuploadlabel = 'completionupload' . $suffix;
        if (!isset($data->mode) || $data->mode != PRIVATESTUDENTFOLDER_MODE_UPLOAD) {
            $data->{$completionuploadlabel} = 0;
        }

        $data->groupapproval = 0;
        if ($data->mode == PRIVATESTUDENTFOLDER_MODE_IMPORT && $data->importfrom != -1) {
            $assigninstance = $DB->get_record('assign', ['id' => $data->importfrom], '*', MUST_EXIST);
            if ($assigninstance->teamsubmission) {
                if ($data->obtaingroupapproval == PRIVATESTUDENTFOLDER_APPROVAL_GROUPAUTOMATIC) {
                    $data->groupapproval = 0;
                    $data->obtainstudentapproval = 0;
                } else {
                    $data->obtainstudentapproval = 1;
                    $data->groupapproval = $data->obtaingroupapproval;
                }
            }
        }

    }

    /**
     * Function that pre-processes data
     *
     * @param object $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        global $DB;
        parent::data_preprocessing($defaultvalues); // TODO: Change the autogenerated stub

        if (isset($defaultvalues['mode']) && $defaultvalues['mode'] == PRIVATESTUDENTFOLDER_MODE_IMPORT) {
            $assign = $DB->get_record('assign', ['id' => $defaultvalues['importfrom']]);
            if ($assign && $assign->teamsubmission) {
                if ($defaultvalues['obtainstudentapproval'] == 0) {
                    $defaultvalues['obtaingroupapproval'] = PRIVATESTUDENTFOLDER_APPROVAL_GROUPAUTOMATIC;
                } else {
                    $defaultvalues['obtaingroupapproval'] = $defaultvalues['groupapproval'];
                }
            }
        }
    }

    /**
     * Perform minimal validation on the settings form
     *
     * @param array $data
     * @param array $files
     * @return string[] errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['allowsubmissionsfromdate'] && $data['duedate']) {
            if ($data['allowsubmissionsfromdate'] > $data['duedate']) {
                $errors['duedate'] = get_string('duedatevalidation', 'privatestudentfolder');
            }
        }
        if ($data['duedate'] && $data['cutoffdate']) {
            if ($data['duedate'] > $data['cutoffdate']) {
                $errors['cutoffdate'] = get_string('cutoffdatevalidation', 'privatestudentfolder');
            }
        }
        if ($data['allowsubmissionsfromdate'] && $data['cutoffdate']) {
            if ($data['allowsubmissionsfromdate'] > $data['cutoffdate']) {
                $errors['cutoffdate'] = get_string('cutoffdatefromdatevalidation', 'privatestudentfolder');
            }
        }

        if ($data['approvalfromdate'] && $data['approvaltodate']) {
            $studentapprovalrequired = $data['obtainstudentapproval'] == 1;
            if ($data['mode'] == PRIVATESTUDENTFOLDER_MODE_IMPORT && in_array($data['importfrom'], $this->_teamassigns)) {
                $studentapprovalrequired = $data['obtaingroupapproval'] != -1;
            }
            if ($studentapprovalrequired && $data['approvalfromdate'] > $data['approvaltodate']) {
                $errors['approvaltodate'] = get_string('approvaltodatevalidation', 'privatestudentfolder');
            }
        }

        if ($data['mode'] == PRIVATESTUDENTFOLDER_MODE_IMPORT) {
            if ($data['importfrom'] == '0' || $data['importfrom'] == '-1') {
                $errors['importfrom'] = get_string('importfrom_err', 'privatestudentfolder');
            }
        }

        return $errors;
    }
}
