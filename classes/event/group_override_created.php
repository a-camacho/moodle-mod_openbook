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
 * The mod_openbook group override created event.
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_openbook\event;

/**
 * The mod_openbook group override created event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int openbook: the id of the openbook.
 *      - int groupid: the id of the group.
 * }
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group_override_created extends \core\event\base {
    /**
     * Init method.
     */
    protected function init() {
        $this->data['objecttable'] = 'openbook_overrides';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventoverridecreated', 'mod_openbook');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' created the override with
                id '$this->objectid' for the openbook with " .
                "course module id '$this->contextinstanceid' for the group
                with id '{$this->other['groupid']}'.";
    }

    /**
     * Returns relevant URL.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/openbook/overrideedit.php', ['id' => $this->objectid]);
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['openbook'])) {
            throw new \coding_exception('The \'openbook\' value must be set in other.');
        }

        if (!isset($this->other['groupid'])) {
            throw new \coding_exception('The \'groupid\' value must be set in other.');
        }
    }

    /**
     * Get objectid mapping
     */
    public static function get_objectid_mapping() {
        return ['db' => 'openbook_overrides', 'restore' => 'openbook_override'];
    }

    /**
     * Get other mapping
     */
    public static function get_other_mapping() {
        $othermapped = [];
        $othermapped['openbook'] = ['db' => 'openbook', 'restore' => 'openbook'];
        $othermapped['groupid'] = ['db' => 'groups', 'restore' => 'group'];

        return $othermapped;
    }
}
