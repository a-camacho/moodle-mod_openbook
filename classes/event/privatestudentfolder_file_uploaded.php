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
 * Contains event class for a single mod_privatestudentfolder being viewed
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_privatestudentfolder\event;
use phpDocumentor\Reflection\Types\Object_;

/**
 * A file was uploaded for this event
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class privatestudentfolder_file_uploaded extends \core\event\base {
    /**
     * Init event objecttable
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['objecttable'] = 'privatestudentfolder_file';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Logs that a file was uploaded
     * @param \stdClass $cm
     * @param object $dobj
     * @return \core\event\base
     * @throws \coding_exception
     */
    public static function create_from_object(\stdClass $cm, $dobj) {
        // Trigger overview event.
        $event = self::create([
            'objectid'      => $dobj->id,
            'context'       => \context_module::instance($cm->id),
            'relateduserid' => $dobj->userid,
            'other'         => (array)$dobj,
        ]);
        return $event;
    }
    // You might need to override get_url() and get_legacy_log_data() if view mode needs to be stored as well.
    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '" . $this->data['other']['userid'] . "' uploaded a new file with id '" .
            $this->data['other']['id'] . "' to privatestudentfolder with id '" . $this->data['other']['privatestudentfolder'] . "'";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventprivatestudentfolderfileuploaded', 'privatestudentfolder');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        $moduleid = get_coursemodule_from_instance('privatestudentfolder', $this->data['other']['privatestudentfolder'])->id;
        return new \moodle_url("/mod/privatestudentfolder/view.php", ['id'  => $moduleid]);
    }
}
