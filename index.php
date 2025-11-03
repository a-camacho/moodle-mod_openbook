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
 * Displays a list of all mod_openbook instances in course
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/openbook/locallib.php');

$id = required_param('id', PARAM_INT);   // We need a course!

if (!$course = $DB->get_record('course', ['id' => $id])) {
    throw new \moodle_exception('coursemisconf');
}

require_course_login($course);
$PAGE->set_pagelayout('incourse');

$event = \mod_openbook\event\course_module_instance_list_viewed::create([
        'context' => context_course::instance($course->id),
]);
$event->trigger();

$strmodulenameplural = get_string('modulenameplural', 'openbook');
$strmodulname = get_string('modulename', 'openbook');
$strsectionname = get_string('sectionname', 'format_' . $course->format);
$strname = get_string('name');
$strdesc = get_string('description');

$PAGE->set_url('/mod/openbook/index.php', ['id' => $course->id]);
$PAGE->navbar->add($strmodulenameplural);
$PAGE->set_title($strmodulenameplural);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading($strmodulname);

if (!$cms = get_coursemodules_in_course('openbook', $course->id, 'cm.idnumber')) {
    notice(get_string('noopenbooksincourse', 'openbook'), '../../course/view.php?id=' . $course->id);
    die;
}

$usesections = course_format_uses_sections($course->format);
if ($usesections) {
    $sections = get_fast_modinfo($course->id)->get_section_info_all();
} else {
    $sections = [];
}

$timenow = time();

$table = new html_table();

if ($usesections) {
    $table->head = [$strsectionname, $strname, $strdesc];
} else {
    $table->head = [$strname, $strdesc];
}

$currentsection = '';

$modinfo = get_fast_modinfo($course);
foreach ($modinfo->instances['openbook'] as $cm) {
    if (!$cm->uservisible) {
        continue;
    }

    // Show dimmed if the mod is hidden!
    $class = $cm->visible ? '' : 'dimmed';

    $link = html_writer::tag('a', format_string($cm->name), ['href' => 'view.php?id=' . $cm->id, 'class' => $class]);

    $printsection = '';
    if ($usesections) {
        if ($cm->sectionnum !== $currentsection) {
            if ($cm->sectionnum) {
                $printsection = get_section_name($course, $sections[$cm->sectionnum]);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $cm->sectionnum;
        }
    }

    $openbook = new openbook($cm, $course);
    $desc = $openbook->get_instance()->intro;

    if ($usesections) {
        $table->data[] = [$printsection, $link, $desc];
    } else {
        $table->data[] = [$link, $desc];
    }
}

echo html_writer::empty_tag('br');

echo html_writer::table($table);

echo $OUTPUT->footer();
