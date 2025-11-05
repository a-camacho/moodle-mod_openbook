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
 * Contains interface and callback methods for mod_openbook
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/locallib.php');

/**
 * Adds a new openbook instance
 *
 * @param stdClass $openbook data (from mod_openbook_mod_form)
 * @return int openbook ID
 */
function openbook_add_instance($openbook) {
    global $DB, $OUTPUT;

    $cmid = $openbook->coursemodule;
    $courseid = $openbook->course;

    $id = 0;
    try {
        $id = $DB->insert_record('openbook', $openbook);
    } catch (Exception $e) {
        echo $OUTPUT->notification($e->getMessage(), 'error');
    }

    $DB->set_field('course_modules', 'instance', $id, ['id' => $cmid]);

    $record = $DB->get_record('openbook', ['id' => $id]);

    $record->course = $courseid;
    $record->cmid = $cmid;

    $course = $DB->get_record('course', ['id' => $record->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_id('openbook', $cmid, 0, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
    $instance = new openbook($cm, $course, $context);

    $instance->update_calendar_event();

    return $record->id;
}

/**
 * Return the list if Moodle features this module supports
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function openbook_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_IDNUMBER:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_CONTENT;
        case FEATURE_MOD_OTHERPURPOSE:
            return MOD_PURPOSE_COLLABORATION;
        default:
            return null;
    }
}

/**
 * updates an existing openbook instance
 *
 * @param stdClass $openbook from mod_openbook_mod_form
 * @return bool true
 */
function openbook_update_instance($openbook) {
    global $DB;

    if ($openbook->filesarepersonal == 1) {
        $openbook->obtainstudentapproval = "0";
    }

    $openbook->id = $openbook->instance;

    $openbook->timemodified = time();

    $DB->update_record('openbook', $openbook);

    $course = $DB->get_record('course', ['id' => $openbook->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('openbook', $openbook->id, 0, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
    $instance = new openbook($cm, $course, $context);

    $approvalreseted = false;
    // If the files are personal, reset the student approval.
    if ($openbook->filesarepersonal == 1) {
        $approvalreseted = $instance->reset_all_studentapproval();
    }

    $instance->update_calendar_event();

    if ($instance->get_instance()->mode == OPENBOOK_MODE_IMPORT || $approvalreseted) {
        // Fetch all files right now!
        $instance->importfiles();
        openbook::send_all_pending_notifications();
    }

    return true;
}

/**
 * complete deletes an openbook instance
 *
 * @param int $id
 * @return bool
 */
function openbook_delete_instance($id) {
    global $DB;

    if (!$openbook = $DB->get_record('openbook', ['id' => $id])) {
        return false;
    }

    $DB->delete_records('openbook_extduedates', ['openbook' => $openbook->id]);

    $fs = get_file_storage();

    $fs->delete_area_files($openbook->id, 'mod_openbook', 'attachment');

    $DB->delete_records('openbook_file', ['openbook' => $openbook->id]);

    $DB->delete_records('event', ['modulename' => 'openbook', 'instance' => $openbook->id]);

    $tableuniqueid = \mod_openbook\local\allfilestable\base::get_table_uniqueid($id);
    $DB->delete_records('user_preferences', ['name' => $tableuniqueid]);
    $filteruserpreference = 'mod-openbook-perpage-' . $id;
    $DB->delete_records('user_preferences', ['name' => $filteruserpreference]);

    $result = true;
    if (!$DB->delete_records('openbook', ['id' => $openbook->id])) {
        $result = false;
    }

    return $result;
}

/**
 * Returns info object about the course module
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return bool|cached_cm_info An object on information that the courses will know about (most noticeably, an icon) or false.
 */
function openbook_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = ['id' => $coursemodule->instance];
    $fields = 'id, name, alwaysshowdescription, allowsubmissionsfromdate, intro, introformat, completionupload';
    if (!$openbook = $DB->get_record('openbook', $dbparams, $fields)) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $openbook->name;
    if ($coursemodule->showdescription) {
        if ($openbook->alwaysshowdescription || time() > $openbook->allowsubmissionsfromdate) {
            // Convert intro to html. Do not filter cached version, filters run at display time.
            $result->content = format_module_intro('openbook', $openbook, $coursemodule->id, false);
        }
    }

    // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $result->customdata['customcompletionrules']['completionupload'] = $openbook->completionupload;
    }

    return $result;
}

/**
 * Defines which elements mod_openbook needs to add to reset form
 *
 * @param MoodleQuickForm $mform The reset course form to extend
 */
function openbook_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'openbookheader', get_string('modulenameplural', 'openbook'));
    $mform->addElement('checkbox', 'reset_openbook_userdata', get_string('reset_userdata', 'openbook'));
}

/**
 * Reset the userdata in openbook module
 *
 * @param object $data settings object which userdata to reset
 * @return array[] array of associative arrays giving feedback what has been successfully reset
 */
function openbook_reset_userdata($data) {
    global $DB;

    if (!$DB->count_records('openbook', ['course' => $data->courseid])) {
        return [];
    }

    $componentstr = get_string('modulenameplural', 'openbook');
    $status = [];

    if (isset($data->reset_openbook_userdata)) {
        $openbooks = $DB->get_records('openbook', ['course' => $data->courseid]);

        foreach ($openbooks as $openbook) {
            $DB->delete_records('openbook_extduedates', ['openbook' => $openbook->id]);

            $filerecords = $DB->get_records('openbook_file', ['openbook' => $openbook->id]);

            $fs = get_file_storage();
            foreach ($filerecords as $filerecord) {
                if ($file = $fs->get_file_by_id($filerecord->fileid)) {
                    $file->delete();
                }
            }

            $DB->delete_records('openbook_file', ['openbook' => $openbook->id]);

            $status[] = [
                    'component' => $componentstr,
                    'item' => $openbook->name,
                    'error' => false,
            ];
        }
    }

    return $status;
}

/**
 * extend an assigment navigation settings
 *
 * @param settings_navigation $settings
 * @param navigation_node $navref
 * @return void
 */
function openbook_extend_settings_navigation(settings_navigation $settings, navigation_node $navref) {
    global $DB, $CFG;

    require_once($CFG->dirroot . '/mod/openbook/locallib.php');

    // We want to add these new nodes after the Edit settings node, and before the
    // Locally assigned roles node. Of course, both of those are controlled by capabilities.
    $keys = $navref->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false && array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    $cm = $settings->get_page()->cm;
    if (!$cm) {
        return;
    }

    $context = $cm->context;
    $course = $settings->get_page()->course;

    if (!$course) {
        return;
    }

    if (has_capability('mod/openbook:addinstance', $settings->get_page()->cm->context)) {
        $url = new moodle_url('/mod/openbook/view.php', ['id' => $settings->get_page()->cm->id, 'allfilespage' => '1']);

        $node = navigation_node::create(
            get_string('allfiles', 'openbook'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'mod_openbook_allfiles'
        );
        $navref->add_node($node, $beforekey);
    }

    if (has_capability('mod/openbook:manageoverrides', $settings->get_page()->cm->context)) {
        $openbook = new openbook($cm, $course, $context);
        $mode = $openbook->get_mode();
        if ($mode != OPENBOOK_MODE_ASSIGN_TEAMSUBMISSION || true) {
            $url = new moodle_url('/mod/openbook/overrides.php', ['id' => $settings->get_page()->cm->id]);

            $node = navigation_node::create(
                get_string('overrides', 'assign'),
                $url,
                navigation_node::TYPE_SETTING,
                null,
                'mod_openbook_useroverrides'
            );
            $navref->add_node($node, $beforekey);
        }
    }
}

/**
 * Serves resource files.
 *
 * @param mixed $course course or id of the course
 * @param mixed $cm course module or id of the course module
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function mod_openbook_pluginfile(
    $course,
    $cm,
    context $context,
    $filearea,
    $args,
    $forcedownload,
    array $options = []
) {
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, false, $cm);
    if (!has_capability('mod/openbook:view', $context)) {
        return false;
    }

    if ($filearea !== 'attachment') {
        return false;
    }

    $itemid = (int)array_shift($args);

    $relativepath = implode('/', $args);

    $fullpath = "/{$context->id}/mod_openbook/$filearea/$itemid/$relativepath";
    $fs = get_file_storage();
    $file = $fs->get_file_by_hash(sha1($fullpath));

    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, true, $options);

    // Wont be reached!
    return false;
}

/**
 * Callback for block_myoverview which will decide whether it will be shown in the overview
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 */
function mod_openbook_core_calendar_provide_event_action(
    calendar_event $event,
    \core_calendar\action_factory $factory
) {
    global $CFG, $USER, $DB;
    require_once($CFG->dirroot . '/mod/openbook/locallib.php');

    // Get the instance of the openbook with the way recommended by the docs.
    $courseinstance = get_fast_modinfo($event->courseid)->instances['openbook'][$event->instance];
    $instance = new openbook($courseinstance);

    // Only show this instance if it's open.
    if ($instance->is_open()) {
        // Also don't show this instance when the user already uploaded one or more files.
        $files = $DB->count_records('openbook_file', ['openbook' => $event->instance,
            'userid' => $USER->id]);

        if ($files >= 1) {
            return null;
        }

        return $factory->create_instance(
            get_string('add_uploads', 'openbook'), // Name of the action button.
            new \moodle_url(
                '/mod/openbook/view.php',
                ['id' => $courseinstance->id],
            ), // URL of the instance.
            1, // Count of necessary actions.
            true, // Whether the user can take action on this folder.
        );
    }
}

/**
 * Callback which returns human-readable strings describing the active completion custom rules for the module instance.
 *
 * @param cm_info|stdClass $cm object with fields ->completion and ->customdata['customcompletionrules']
 * @return array $descriptions the array of descriptions for the custom rules.
 */
function mod_openbook_get_completion_active_rule_descriptions($cm) {
    // Values will be present in cm_info, and we assume these are up to date.
    if (
        empty($cm->customdata['customcompletionrules'])
        || $cm->completion != COMPLETION_TRACKING_AUTOMATIC
    ) {
        return [];
    }

    $descriptions = [];
    foreach ($cm->customdata['customcompletionrules'] as $key => $val) {
        switch ($key) {
            case 'completionupload':
                if (!empty($val)) {
                    $descriptions[] = get_string('completionupload', 'openbook');
                }
                break;
            default:
                break;
        }
    }
    return $descriptions;
}
