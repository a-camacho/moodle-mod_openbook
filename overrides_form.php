<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Settings form for overrides in the openbook module.
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Class for Openbook resource folder overrides
 */
class openbook_overrides_form extends moodleform {
    /** @var object $openbook */
    private $openbook;

    /**
     * Defines the override form
     */
    public function definition() {
        global $DB;
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'overrideid');
        $mform->setType('overrideid', PARAM_INT);

        $this->openbook = $this->_customdata['openbook'];
        $mode = $this->openbook->get_mode();
        if ($mode == OPENBOOK_MODE_ASSIGN_TEAMSUBMISSION) {
            $groupids = $this->openbook->get_groups();
            $groups = $DB->get_records_list('groups', 'id', $groupids);

            $mform->addElement('hidden', 'userid');
            $mform->setType('userid', PARAM_INT);
            $mform->setDefault('userid', 0);
            $groupsclean = [];
            foreach ($groups as $group) {
                $groupsclean[$group->id] = $group->name;
            }
            $options = [
                'multiple' => false,
                'noselectionstring' => get_string('override:group:choose', 'openbook'),

            ];
            $mform->addElement('autocomplete', 'groupid', get_string('group'), $groupsclean, $options);
            $mform->addRule('groupid', null, 'required', null, 'client');
        } else {
            $userids = $this->openbook->get_users([], true);
            $users = $DB->get_records_list('user', 'id', $userids);

            $mform->addElement('hidden', 'groupid');
            $mform->setType('groupid', PARAM_INT);
            $mform->setDefault('groupid', 0);

            $usersclean = [];
            foreach ($users as $user) {
                if ($user->deleted == 1 || $user->suspended == 1) {
                    continue;
                }
                $usersclean[$user->id] = fullname($user);
            }
            $options = [
                'multiple' => false,
                'noselectionstring' => get_string('override:user:choose', 'openbook'),
            ];
            $mform->addElement('autocomplete', 'userid', get_string('user'), $usersclean, $options);
            $mform->addRule('userid', null, 'required', null, 'client');
        }

        $itemsadded = false;
        if ($mode == OPENBOOK_MODE_FILEUPLOAD) {
            $mform->addElement('header', 'submissionsettings', get_string('submissionsettings', 'openbook'));
            $mform->setExpanded('submissionsettings');

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
            $itemsadded = true;
        }

        if ($this->openbook->get_instance()->obtainstudentapproval == 1) {
            $mform->addElement('header', 'approvalsettings', get_string('approvalsettings', 'openbook'));
            $mform->setExpanded('approvalsettings', true);

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
            $itemsadded = true;
        }

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
        $itemsadded = true;

        if (!$itemsadded) {
            $mform->addElement(
                'html',
                '<div class="alert alert-info">' . get_string('override:nothingtochange', 'mod_openbook') . '</div>'
            );
        }
        $this->add_action_buttons(true);
    }
}
