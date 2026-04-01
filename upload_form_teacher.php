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
 * File containing upload form class.
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/formslib.php'); // Putting this is as a safety as i got a class not found error.

/**
 * Form to upload files for mod_openbook
 */
class mod_openbook_upload_form_teacher extends moodleform {
    /**
     * Definition of file upload format
     */
    public function definition() {
        $mform = $this->_form;

        $currententry = $this->_customdata['current'];
        $openbook = $this->_customdata['openbook'];

        $attachmentoptions = $this->_customdata['attachmentoptions'];

        if ($openbook->get_instance()->obtainteacherapproval) {
            $text = get_string('published_aftercheck', 'openbook');
        } else {
            $text = get_string('published_immediately', 'openbook');
        }

        $headerstring = get_string('teacher_files', 'openbook');
        $mform->addElement('header', 'myfiles', $headerstring);

        $mform->addElement(
            'filemanager',
            'commonteacherfiles_filemanager',
            $headerstring,
            null,
            $attachmentoptions,
        );

        // Hidden params.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        // Buttons.
        $this->add_action_buttons(true, get_string('save_changes', 'openbook'));
        $this->set_data($currententry);
    }
}
