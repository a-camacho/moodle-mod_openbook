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
 * Displays a single mod_privatestudentfolder instance
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/privatestudentfolder/locallib.php');

$id = required_param('id', PARAM_INT); // Course Module ID.

$url = new moodle_url('/mod/privatestudentfolder/overrides.php', ['id' => $id]);
$cm = get_coursemodule_from_id('privatestudentfolder', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_login($course, true, $cm);
$PAGE->set_url($url);

$context = context_module::instance($cm->id);

require_capability('mod/privatestudentfolder:manageoverrides', $context);

$privatestudentfolder = new privatestudentfolder($cm, $course, $context);

$pagetitle = strip_tags($course->shortname . ': ' . format_string($privatestudentfolder->get_instance()->name));

// Print the page header.
$PAGE->set_pagelayout('admin');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);
$PAGE->add_body_class('limitedwidth');
$activityheader = $PAGE->activityheader;
$activityheader->set_attrs([
    'description' => '',
    'hidecompletion' => true,
    'title' => $activityheader->is_title_allowed() ? format_string(
        $privatestudentfolder->get_instance()->name,
        true,
        ['context' => $context]
    ) : "",
]);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('overrides', 'mod_assign'), 2);

$privatestudentfolderinstance = $privatestudentfolder->get_instance();
$templatecontext = $privatestudentfolder->overrides_export_for_template();

$mode = $privatestudentfolder->get_mode();

echo $OUTPUT->render_from_template('mod_privatestudentfolder/overrides', $templatecontext);

echo $OUTPUT->footer();
