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
 * Instance settings form.
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
 * Form for creating and editing mod_openbook instances
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_openbook_mod_form extends moodleform_mod {
    /** @var object $teamassigns */
    private $teamassigns;

    /** @var object $notteamassigns */
    private $notteamassigns;

    /**
     * Define this form - called by the parent constructor
     */
    public function definition() {
        global $DB, $CFG, $COURSE, $PAGE;

        $mform = $this->_form;
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name.
        $mform->addElement('text', 'name', get_string('name', 'openbook'), ['size' => '64']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        // Adding the standard "intro" and "introformat" fields!
        $this->standard_intro_elements();

        // Openbook resource folder specific elements.
        $mform->addElement('header', 'submissionsettings', get_string('submissionsettings', 'openbook'));
        $mform->setExpanded('submissionsettings');

        if (isset($this->current->id) && $this->current->id != "") {
            $filecount = $DB->count_records('openbook_file', ['openbook' => $this->current->id]);
        } else {
            $filecount = 0;
        }

        $disabled = [];
        if ($filecount > 0) {
            $disabled['disabled'] = 'disabled';
        }

        // Openbook resource folder mode upload specific elements.
        $maxfiles = [];
        for ($i = 1; $i <= 100 || $i <= get_config('openbook', 'maxfiles'); $i++) {
            $maxfiles[$i] = $i;
        }

        $mform->addElement('select', 'maxfiles', get_string('maxfiles', 'openbook'), $maxfiles);
        $mform->setDefault('maxfiles', get_config('openbook', 'maxfiles'));
        $mform->addHelpButton('maxfiles', 'maxfiles', 'openbook');
        $mform->hideIf('maxfiles', 'mode', 'neq', OPENBOOK_MODE_UPLOAD);

        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes);
        $choices[0] = get_string('courseuploadlimit', 'openbook') . ' (' . display_size($COURSE->maxbytes) . ')';
        $mform->addElement('select', 'maxbytes', get_string('maxbytes', 'openbook'), $choices);
        $mform->setDefault('maxbytes', get_config('openbook', 'maxbytes'));
        $mform->addHelpButton('maxbytes', 'maxbytes', 'openbook');
        $mform->hideIf('maxbytes', 'mode', 'neq', OPENBOOK_MODE_UPLOAD);

        $mform->addElement('filetypes', 'allowedfiletypes', get_string('allowedfiletypes', 'openbook'));
        $mform->addHelpButton('allowedfiletypes', 'allowedfiletypes', 'openbook');
        $mform->hideIf('allowedfiletypes', 'mode', 'neq', OPENBOOK_MODE_UPLOAD);

        $name = get_string('allowsubmissionsfromdate', 'openbook');
        $options = ['optional' => true];
        $mform->addElement('date_time_selector', 'allowsubmissionsfromdate', $name, $options);
        $mform->addHelpButton('allowsubmissionsfromdate', 'allowsubmissionsfromdate', 'openbook');
        $mform->setDefault('allowsubmissionsfromdate', time());
        $mform->hideIf('allowsubmissionsfromdate', 'mode', 'neq', OPENBOOK_MODE_UPLOAD);

        $name = get_string('duedate', 'openbook');
        $mform->addElement('date_time_selector', 'duedate', $name, ['optional' => true]);
        $mform->addHelpButton('duedate', 'duedate', 'openbook');
        $mform->setDefault('duedate', time() + 7 * 24 * 3600);
        $mform->hideIf('duedate', 'mode', 'neq', OPENBOOK_MODE_UPLOAD);

        $mform->addElement('hidden', 'cutoffdate', false);
        $mform->setType('cutoffdate', PARAM_BOOL);

        // Approval settings start.
        $mform->addElement('header', 'approvalsettings', get_string('approvalsettings', 'openbook'));
        $mform->setExpanded('approvalsettings', true);

        // Files are personal.
        $attributes = [];
        $options = [
            '0' => get_string('filesarepersonal_no', 'openbook'),
            '1' => get_string('filesarepersonal_yes', 'openbook'),
        ];

        $mform->addElement(
            'select',
            'filesarepersonal',
            get_string('filesarepersonal', 'openbook'),
            $options,
            $attributes
        );
        $mform->setDefault('filesarepersonal', get_config('openbook', 'filesarepersonal'));
        $mform->addHelpButton('filesarepersonal', 'filesarepersonal', 'openbook');

        // Open PDF files in PDF.js.
        $attributes = [];
        $options = [
            '0' => get_string('openpdffilesinpdfjs_no', 'openbook'),
            '1' => get_string('openpdffilesinpdfjs_yes', 'openbook'),
        ];

        $mform->addElement(
            'select',
            'openpdffilesinpdfjs',
            get_string('openpdffilesinpdfjs', 'openbook'),
            $options,
            $attributes
        );
        $mform->setDefault('openpdffilesinpdfjs', get_config('openbook', 'openpdffilesinpdfjs'));
        $mform->addHelpButton('openpdffilesinpdfjs', 'openpdffilesinpdfjs', 'openbook');

        // Use legacy PDF.js viewer.
        $attributes = [];
        $options = [
            '0' => get_string('uselegacyviewer_no', 'openbook'),
            '1' => get_string('uselegacyviewer_yes', 'openbook'),
        ];

        $mform->addElement(
            'select',
            'uselegacyviewer',
            get_string('uselegacyviewer', 'openbook'),
            $options,
            $attributes
        );
        $mform->setDefault('uselegacyviewer', get_config('openbook', 'uselegacyviewer'));
        $mform->addHelpButton('uselegacyviewer', 'uselegacyviewer', 'openbook');
        $mform->hideIf('uselegacyviewer', 'openpdffilesinpdfjs', 'eq', '0');

        // Teacher approval.
        $attributes = [];
        $options = [
            '0' => get_string('obtainapproval_automatic', 'openbook'),
            '1' => get_string('obtainapproval_required', 'openbook'),
        ];

        $mform->addElement(
            'select',
            'obtainteacherapproval',
            get_string('obtainteacherapproval', 'openbook'),
            $options,
            $attributes
        );
        $mform->setDefault('obtainteacherapproval', get_config('openbook', 'obtainteacherapproval'));
        $mform->addHelpButton('obtainteacherapproval', 'obtainteacherapproval', 'openbook');

        // Student approval.
        $attributes = [];
        $options = [
            '0' => get_string('obtainapproval_automatic', 'openbook'),
            '1' => get_string('obtainapproval_required', 'openbook'),
        ];

        $mform->addElement(
            'select',
            'obtainstudentapproval',
            get_string('obtainstudentapproval', 'openbook'),
            $options,
            $attributes
        );
        $mform->setDefault('obtainstudentapproval', get_config('openbook', 'obtainstudentapproval'));
        $mform->addHelpButton('obtainstudentapproval', 'obtainstudentapproval', 'openbook');

        $mform->hideIf('obtainstudentapproval', 'filesarepersonal', 'eq', '1');
        $mform->hideIf('approvalfromdate', 'filesarepersonal', 'eq', '1');
        $mform->hideIf('approvaltodate', 'filesarepersonal', 'eq', '1');

        $mform->addElement(
            'date_time_selector',
            'approvalfromdate',
            get_string('approvalfromdate', 'openbook'),
            ['optional' => true]
        );
        $mform->addHelpButton('approvalfromdate', 'approvalfromdate', 'openbook');
        $mform->setDefault('approvalfromdate', time());

        $mform->addElement(
            'date_time_selector',
            'approvaltodate',
            get_string('approvaltodate', 'openbook'),
            ['optional' => true]
        );
        $mform->addHelpButton('approvaltodate', 'approvaltodate', 'openbook');
        $mform->setDefault('approvaltodate', time() + 7 * 24 * 3600);
        // Approval code end.

        $mform->addElement('hidden', 'alwaysshowdescription', true);
        $mform->setType('alwaysshowdescription', PARAM_BOOL);

        // Apply availability restrictions.
        $mform->addElement(
            'select',
            'availabilityrestriction',
            get_string('availabilityrestriction', 'openbook'),
            [get_string('no'), get_string('yes')]
        );
        $mform->setDefault('availabilityrestriction', get_config('openbook', 'availabilityrestriction'));
        $mform->addHelpButton('availabilityrestriction', 'availabilityrestriction', 'openbook');

        $mform->addElement('header', 'notifications', get_string('notifications', 'openbook'));

        $options = [
            OPENBOOK_NOTIFY_NONE => get_string('notify:setting:0', 'openbook'),
            OPENBOOK_NOTIFY_TEACHER => get_string('notify:setting:1', 'openbook'),
            OPENBOOK_NOTIFY_STUDENT => get_string('notify:setting:2', 'openbook'),
            OPENBOOK_NOTIFY_ALL => get_string('notify:setting:3', 'openbook'),
        ];

        $mform->addElement('select', 'notifyfilechange', get_string('notify:filechange', 'openbook'), $options);
        $mform->addHelpButton('notifyfilechange', 'notify:filechange', 'openbook');
        $mform->setDefault('notifyfilechange', get_config('openbook', 'notifyfilechange'));

        $mform->addElement('select', 'notifystatuschange', get_string('notify:statuschange', 'openbook'), $options);
        $mform->addHelpButton('notifystatuschange', 'notify:statuschange', 'openbook');
        $mform->setDefault('notifystatuschange', get_config('openbook', 'notifystatuschange'));

        $mform->addElement('header', 'securewindowsettings', get_string('securewindowsettings', 'openbook'));
        $mform->setExpanded('securewindowsettings');

        $mform->addElement(
            'date_time_selector',
            'securewindowfromdate',
            get_string('securewindowfromdate', 'openbook'),
            ['optional' => true]
        );
        $mform->addHelpButton('securewindowfromdate', 'securewindowfromdate', 'openbook');
        $mform->setDefault('securewindowfromdate', time());

        $mform->addElement(
            'date_time_selector',
            'securewindowtodate',
            get_string('securewindowtodate', 'openbook'),
            ['optional' => true]
        );
        $mform->addHelpButton('securewindowtodate', 'securewindowtodate', 'openbook');
        $mform->setDefault('securewindowtodate', time() + 7 * 24 * 3600);

        // Standard coursemodule elements.
        $this->standard_coursemodule_elements();

        // Buttons.
        $this->add_action_buttons();
        $PAGE->requires->js_call_amd('mod_openbook/modform');
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

        $mform->addElement('advcheckbox', $completionuploadlabel, '', get_string('completionupload', 'openbook'));
        // Enable this completion rule by default.
        $mform->setDefault($completionuploadlabel, 1);
        $mform->hideIf($completionuploadlabel, 'mode', 'neq', OPENBOOK_MODE_UPLOAD);
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
        if ($data['mode'] == OPENBOOK_MODE_UPLOAD && !empty($data[$completionuploadlabel])) {
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
        if (!isset($data->mode) || $data->mode != OPENBOOK_MODE_UPLOAD) {
            $data->{$completionuploadlabel} = 0;
        }
    }

    /**
     * Function that pre-processes data
     *
     * @param object $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        global $DB;
        // phpcs:disable moodle.Commenting.TodoComment
        parent::data_preprocessing($defaultvalues); // TODO: Change the autogenerated stub.

        if (isset($defaultvalues['mode']) && $defaultvalues['mode'] == OPENBOOK_MODE_IMPORT) {
            $assign = $DB->get_record('assign', ['id' => $defaultvalues['importfrom']]);
            if ($assign && $assign->teamsubmission) {
                if ($defaultvalues['obtainstudentapproval'] == 0) {
                    $defaultvalues['obtaingroupapproval'] = OPENBOOK_APPROVAL_GROUPAUTOMATIC;
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
                $errors['duedate'] = get_string('duedatevalidation', 'openbook');
            }
        }
        if ($data['duedate'] && $data['cutoffdate']) {
            if ($data['duedate'] > $data['cutoffdate']) {
                $errors['cutoffdate'] = get_string('cutoffdatevalidation', 'openbook');
            }
        }
        if ($data['allowsubmissionsfromdate'] && $data['cutoffdate']) {
            if ($data['allowsubmissionsfromdate'] > $data['cutoffdate']) {
                $errors['cutoffdate'] = get_string('cutoffdatefromdatevalidation', 'openbook');
            }
        }

        if ($data['approvalfromdate'] && $data['approvaltodate']) {
            $studentapprovalrequired = $data['obtainstudentapproval'] == 1;
            if ($studentapprovalrequired && $data['approvalfromdate'] > $data['approvaltodate']) {
                $errors['approvaltodate'] = get_string('approvaltodatevalidation', 'openbook');
            }
        }

        if ($data['securewindowfromdate'] && $data['securewindowtodate']) {
            if ($data['securewindowfromdate'] > $data['securewindowtodate']) {
                $errors['securewindowtodate'] = get_string('securewindowtodatevalidation', 'openbook');
            }
        }

        return $errors;
    }
}
