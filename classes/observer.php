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
 * observer.php
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_privatestudentfolder;

use core\notification;
use mod_assign\event\assessable_submitted;
use mod_assign\event\base;
use privatestudentfolder;
use stdClass;

/**
 * mod_grouptool\observer handles events due to changes in moodle core which affect grouptool
 */
class observer {
    /**
     * Event triggered when a course module is created
     *
     * @param \core\event\base $event
     */
    public static function course_module_created(\core\event\base $event) {
        global $DB;
        $eventdata = $event->get_data();
        if (
            isset($eventdata['other']) &&
            isset($eventdata['other']['modulename']) &&
            $eventdata['other']['modulename'] == 'privatestudentfolder'
        ) {
            $cm = get_coursemodule_from_instance('privatestudentfolder', $eventdata['other']['instanceid'], 0, false, MUST_EXIST);
            $privatestudentfolder = new privatestudentfolder($cm);
            if ($privatestudentfolder->get_instance()->mode == PRIVATESTUDENTFOLDER_MODE_IMPORT) {
                $privatestudentfolder->importfiles();
            }
            privatestudentfolder::send_all_pending_notifications();
        }
    }

    /**
     * \mod_assign\event\assessable_submitted
     *
     * @param \mod_assign\event\assessable_submitted $e Event object containing useful data
     * @return bool true if success
     */
    public static function import_assessable(assessable_submitted $e) {
        global $DB, $CFG, $OUTPUT;

        // Keep other page calls slimmed down!
        require_once($CFG->dirroot . '/mod/privatestudentfolder/locallib.php');

        // We have the submission ID, so first we fetch the corresponding submission, assign, etc.!
        $assign = $e->get_assign();
        $assignid = $assign->get_course_module()->instance;
        $submission = $DB->get_record($e->objecttable, ['id' => $e->objectid]);

        if (!empty($assign->get_instance()->teamsubmission) && !empty($submission->userid)) {
            /* If the userid is set, we can skip here... the files and texts are in the submission with groupid set
               or groupid 0 for users without group! */
            return true;
        }

        $assignmoduleid = $DB->get_field('modules', 'id', ['name' => 'assign']);
        $assigncm = $DB->get_record('course_modules', [
                'course' => $assign->get_course()->id,
                'module' => $assignmoduleid,
                'instance' => $assignid,
        ]);
        $assigncontext = \context_module::instance($assigncm->id);

        $sql = "SELECT pub.*
                  FROM {privatestudentfolder} pub
                 WHERE (pub.mode = ?) AND (pub.importfrom = ?)";
        $params = [\PRIVATESTUDENTFOLDER_MODE_IMPORT, $assignid];
        if (!$privatestudentfolders = $DB->get_records_sql($sql, $params)) {
            return true;
        }

        foreach ($privatestudentfolders as $pub) {
            $cm = get_coursemodule_from_instance('privatestudentfolder', $pub->id);
            if (!$cm) {
                continue;
            }
            $privatestudentfolder = new privatestudentfolder($cm);
            $privatestudentfolder->importfiles();
        }

        privatestudentfolder::send_all_pending_notifications();
        return true;
    }
}
