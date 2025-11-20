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

// TODO: Show common teacher files independently from Teacher approval / Student approval status.
// TODO: Show common teacher files to teacher in the "Teacher files that are visible to everybody".
// TODO: Remove common teacher files from 'Decision pending' count.
// TODO: Remove common teacher files from 'Submitted' count.

/**
 * Contains much of the logic needed for mod_openbook
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('OPENBOOK_MODE_UPLOAD', 0);

define('OPENBOOK_EVENT_TYPE_DUE', 'due');

define('OPENBOOK_FILTER_NOFILTER', 'nofilter');
define('OPENBOOK_FILTER_ALLFILES', 'allfiles');
define('OPENBOOK_FILTER_APPROVED', 'approved');
define('OPENBOOK_FILTER_REJECTED', 'rejected');
define('OPENBOOK_FILTER_APPROVALREQUIRED', 'approvalrequired');
define('OPENBOOK_FILTER_TEACHERFILE', 'commonteacherfile');
define('OPENBOOK_FILTER_NOFILES', 'nofiles');

define('OPENBOOK_MODE_FILEUPLOAD', 'fileupload');

define('OPENBOOK_NOTIFY_NONE', 0);
define('OPENBOOK_NOTIFY_TEACHER', 1);
define('OPENBOOK_NOTIFY_STUDENT', 2);
define('OPENBOOK_NOTIFY_ALL', 3);
define('OPENBOOK_NOTIFY_STATUSCHANGE', 'status');
define('OPENBOOK_NOTIFY_FILECHANGE', 'file');

require_once($CFG->dirroot . '/mod/openbook/mod_openbook_allfiles_form.php');

/**
 * openbook class contains much logic used in mod_openbook
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class openbook {
    /** @var object instance */
    protected $instance;
    /** @var object context */
    protected $context;
    /** @var object course */
    protected $course;
    /** @var object coursemodule */
    protected $coursemodule;
    /** @var constant mode */
    protected $mode;
    /** @var bool allfilespage */
    protected $allfilespage = false;
    /** @var bool teamsubmission */
    protected $teamsubmission = false;
    /** @var [] pendingnotifications */
    protected static $pendingnotifications = [];

    /**
     * Constructor
     *
     * @param object $cm course module object
     * @param object $course (optional) course object
     * @param context_module $context (optional) Course Module Context
     */
    public function __construct($cm, $course = null, $context = null) {
        global $DB;

        $this->coursemodule = $cm;

        if ($course != null) {
            $this->course = $course;
        } else {
            $this->course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        }

        if ($context != null) {
            $this->context = $context;
        } else {
            $this->context = context_module::instance($cm->id);
        }

        $this->instance = $DB->get_record("openbook", ["id" => $cm->instance]);

        // phpcs:disable Squiz.PHP.CommentedOutCode
        // phpcs:disable moodle.Commenting.InlineComment
        // $this->instance->obtainteacherapproval = !$this->instance->obtainteacherapproval;
    }

    /**
     * Whether or not to show intro text right now
     *
     * @return bool
     */
    public function show_intro() {
        if (
            $this->get_instance()->alwaysshowdescription ||
                time() > $this->get_instance()->allowsubmissionsfromdate
        ) {
            return true;
        }

        return false;
    }

    /**
     * Display the intro text if available
     */
    public function display_intro() {
        global $OUTPUT;

        if ($this->show_intro()) {
            if ($this->instance->intro) {
                echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
                echo format_module_intro('openbook', $this->instance, $this->coursemodule->id);
                echo $OUTPUT->box_end();
            }
        } else {
            if ($this->alwaysshowdescription) {
                $message = get_string(
                    'allowsubmissionsfromdatesummary',
                    'openbook',
                    userdate($this->instance->allowsubmissionsfromdate)
                );
            } else {
                $message = get_string(
                    'allowsubmissionsanddescriptionfromdatesummary',
                    'openbook',
                    userdate($this->instance->allowsubmissionsfromdate)
                );
            }
            echo html_writer::div($message, '', ['id' => 'intro']);
        }
    }

    /**
     * Display dates which limit submission timespan
     */
    public function display_availability() {
        global $USER, $OUTPUT;

        // Display availability dates.
        $textsuffix = ($this->instance->mode == OPENBOOK_MODE_IMPORT) ? "_import" : "_upload";

        echo $OUTPUT->box_start('generalbox boxaligncenter', 'dates');
        echo '<table>';
        if ($this->instance->allowsubmissionsfromdate) {
            echo '<tr><td class="c0">' . get_string('allowsubmissionsfromdate' . $textsuffix, 'openbook') . ':</td>';
            echo '    <td class="c1">' . userdate($this->instance->allowsubmissionsfromdate) . '</td></tr>';
        }
        if ($this->instance->duedate) {
            echo '<tr><td class="c0">' . get_string('duedate' . $textsuffix, 'openbook') . ':</td>';
            echo '    <td class="c1">' . userdate($this->instance->duedate) . '</td></tr>';
        }

        $override = $this->override_get_currentuserorgroup();
        if ($override) {
            if ($override->submissionoverride) {
                echo '<tr><td class="c0">' . get_string('submissionoverride', 'openbook') . ':</td>';
                echo '    <td class="c1">' . $override->submissionoverride . '</td></tr>';
            }
            if ($override->approvaloverride) {
                echo '<tr><td class="c0">' . get_string('approvaloverride', 'openbook') . ':</td>';
                echo '    <td class="c1">' . $override->approvaloverride . '</td></tr>';
            }
            if ($override->securewindowoverride) {
                echo '<tr><td class="c0">' . get_string('submissionoverride', 'openbook') . ':</td>';
                echo '    <td class="c1">' . $override->securewindowoverride . '</td></tr>';
            }
        }

        echo '</table>';

        echo $OUTPUT->box_end();
    }

    /**
     * Display Link to upload form if submission date is open
     * and the user has the capability to upload files
     *
     * @return string HTML snippet with upload link (single button or plain text if not allowed)
     */
    public function display_uploadlink() {
        global $OUTPUT;

        if (has_capability('mod/openbook:upload', $this->context)) {
            if ($this->is_open()) {
                $url = new moodle_url(
                    '/mod/openbook/upload.php',
                    ['id' => $this->instance->id, 'cmid' => $this->coursemodule->id]
                );
                $label = get_string('edit_uploads', 'openbook');
                $editbutton = $OUTPUT->single_button($url, $label);

                return $editbutton;
            } else {
                return get_string('edit_timeover', 'openbook');
            }
        } else {
            return get_string('edit_notcapable', 'openbook');
        }

        return '';
    }

    /**
     * Get the secure window to date (if set)
     *
     * @param int $uid User ID to fetch overriden secure window to date for
     * @return int overridden date if set or 0
     */
    public function user_overridensecurewindowtodate($uid) {
        global $DB;

        $overriddensecurewindowtodate = $DB->get_field('openbook_overrides', 'securewindowtodate', [
            'openbook' => $this->get_instance()->id,
            'userid' => $uid,
        ]);

        if (!$overriddensecurewindowtodate) {
            return 0;
        }

        return $overriddensecurewindowtodate;
    }

    /**
     * Allfilespage setter
     *
     * @param bool $allfilespage
     */
    public function set_allfilespage($allfilespage) {
        $this->allfilespage = $allfilespage;
    }

    /**
     * Allfilespage getter
     */
    public function get_allfilespage() {
        return $this->allfilespage;
    }

    /**
     * Check if submission is currently allowed due to allowsubmissionsfromdate and duedate
     *
     * @return bool
     */
    public function is_open() {
        global $USER;

        if (!has_capability('mod/openbook:upload', $this->get_context())) {
            return false;
        }

        if (has_capability('mod/openbook:uploadcommonteacherfile', $this->get_context())) {
            return true;
        }

        $now = time();

        $from = $this->get_instance()->allowsubmissionsfromdate;
        $due = $this->get_instance()->duedate;

        $override = $this->override_get_currentuserorgroup();

        if ($override && $override->submissionoverride) {
            if ($override->allowsubmissionsfromdate > 0) {
                $from = $override->allowsubmissionsfromdate;
            }
            if ($override->duedate > 0) {
                $due = $override->duedate;
            }
        }

        if (
            ($from == 0 || $from < $now) &&
                ($due == 0 || $due > $now)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check if approval time frame is open
     */
    public function is_approval_open() {
        global $USER;

        $now = time();

        $from = $this->get_instance()->approvalfromdate;
        $to = $this->get_instance()->approvaltodate;

        $override = $this->override_get_currentuserorgroup();

        if ($override && $override->approvaloverride) {
            if ($override->approvalfromdate > 0) {
                $from = $override->approvalfromdate;
            }
            if ($override->approvaltodate > 0) {
                $to = $override->approvaltodate;
            }
        }

        if (($from == 0 || $from < $now) && ($to == 0 || $to > $now)) {
            return true;
        }
        return false;
    }

    /**
     * Check if secure window time frame is now and secure window is therefore enforced.
     * If no date is set secure window is not enforced.
     */
    public function is_securewindow_enforced() {
        global $USER;

        // For teachers secure window is never enforced.
        if (has_capability('moodle/course:update', context_course::instance($this->course->id))) {
            return false;
        }

        $now = time();

        $from = $this->get_instance()->securewindowfromdate;
        $to = $this->get_instance()->securewindowtodate;
        $overridesecurewindowtodate = $this->user_overridensecurewindowtodate($USER->id);

        if ($to != 0 && $overridesecurewindowtodate) {
            $to = $overridesecurewindowtodate;
        }

        $override = $this->override_get_currentuserorgroup();

        if ($override && $override->securewindowoverride) {
            if ($override->securewindowfromdate > 0) {
                $from = $override->securewindowfromdate;
            }
            if ($override->securewindowtodate > 0) {
                $to = $override->securewindowtodate;
            }
        }

        if ($from == 0 && $to == 0) {
            return false;
        } else if (($from == 0 || $from < $now) && ($to == 0 || $to > $now)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the string with approval timeframe information
     */
    public function is_approval_open_string() {
        $fromstr = '';
        if ($this->get_instance()->approvalfromdate > 0) {
            $fromstr = get_string('from') . ' ' . userdate($this->get_instance()->approvalfromdate);
        }
        $tostr = '';
        if ($this->get_instance()->approvaltodate > 0) {
            $tostr = get_string('until') . ' ' . userdate($this->get_instance()->approvaltodate);
        }
        return $fromstr . ' ' . $tostr;
    }

    /**
     * Instance getter
     *
     * @return object instance object
     */
    public function get_instance() {
        return $this->instance;
    }

    /**
     * Context getter
     *
     * @return \context_module context object
     */
    public function get_context() {
        return $this->context;
    }

    /**
     * Coursemodule getter
     *
     * @return object coursemodule object
     */
    public function get_coursemodule() {
        return $this->coursemodule;
    }

    /**
     * Get all groups (optionally filtered by groupingid or group-IDs in selgroups-array)
     *
     * @param int $groupingid (optional) Grouping-ID to filter groups for or 0
     * @param int[] $selgroups (optional) selected group's IDs to filter for or empty array()
     * @return int[] array of group's IDs
     */
    public function get_groups($groupingid = 0, $selgroups = []) {
        $groups = groups_get_all_groups($this->get_instance()->course, 0, $groupingid);
        $groups = array_keys($groups);

        if (is_array($selgroups) && count($selgroups) > 0) {
            $groups = array_intersect($groups, $selgroups);
        }

        foreach ($groups as $id => $groupid) {
            if (!empty($groupid)) {
                $members = groups_get_members($groupid);
            }
            if (empty($members)) {
                unset($groups[$id]);
            }
        }
        return $groups;
    }

    /**
     * Get userids to fetch files for, when displaying all submitted files or downloading them as ZIP
     *
     * @param int[] $users (optional) user ids for which the returned user ids have to filter
     * @param boolean $ignoreallfilespage (optional)
     *
     * @return int[] array of userids
     */
    public function get_users($users = [], $ignoreallfilespage = false) {
        global $DB;

        $customusers = '';

        if (is_array($users) && count($users) > 0) {
            $customusers = " and u.id IN (" . implode(', ', $users) . ") ";
        } else if ($users === false) {
            return [];
        }

        // Find out current groups mode.
        $currentgroup = groups_get_activity_group($this->get_coursemodule(), true);

        // Get all ppl that are allowed to submit assignments.
        [$esql, $params] = get_enrolled_sql($this->context, 'mod/openbook:view', $currentgroup);

        $allfilespage = $ignoreallfilespage || $this->allfilespage;

        if (
            $allfilespage && (has_capability('mod/openbook:approve', $this->context)
                || has_capability('mod/openbook:grantextension', $this->context))
        ) {
            // We can skip the approval-checks for teachers!
            $sql = 'SELECT u.id FROM {user} u ' .
                    'LEFT JOIN (' . $esql . ') eu ON eu.id=u.id ' .
                    'WHERE u.deleted = 0 AND eu.id=u.id ' . $customusers;
        } else {
            $sql = 'SELECT u.id FROM {user} u ' .
                    'LEFT JOIN (' . $esql . ') eu ON eu.id=u.id ' .
                    'LEFT JOIN {openbook_file} files ON (u.id = files.userid) ' .
                    'WHERE u.deleted = 0 AND eu.id=u.id ' . $customusers .
                    'AND files.openbook = ' . $this->get_instance()->id . ' ';

            $where = '';
            if ($this->get_instance()->obtainteacherapproval == 1) {
                // Need teacher approval.
                $where = 'files.teacherapproval = 1';
            }
                // No else{}.
                // No need for teacher approval.
                // Teacher only hasnt rejected.
                // $where = '(files.teacherapproval = 1 OR files.teacherapproval IS NULL)';
            if ($this->get_instance()->obtainstudentapproval == 1) {
                // No need to ask student and teacher has approved.
                if (mb_strlen($where) > 0) {
                    $where .= ' AND ';
                }
                $where .= 'files.studentapproval = 1';
            }   // No else {}
                // Student and teacher have approved.
                // $where = 'files.teacherapproval = 1 AND files.studentapproval = 1';

            if (mb_strlen($where) > 0) {
                $sql .= 'AND ' . $where . ' ';
            }
            $sql .= 'GROUP BY u.id';
        }

        $users = $DB->get_records_sql($sql, $params);

        if (empty($users)) {
            return [-1];
        }
        $modinfo = get_fast_modinfo($this->course->id);

        $filtered = $users;
        if (empty($filtered)) {
            return [-1];
        }
        return array_keys($filtered);
    }

    /**
     * Get mode
     */
    public function get_mode() {
        return $this->mode;
    }

    /**
     * Get table with all teacher submitted files
     *
     * @param string $filter
     * @param bool $ignoreallfilespage (optional)
     */
    public function get_teacherfilestable($filter, $ignoreallfilespage = false) {
        global $DB;
        $mode = $this->get_mode();
        $oldallfilespage = $this->allfilespage;
        if ($ignoreallfilespage) {
            $this->allfilespage = true;
        }
        $uniqueid = \mod_openbook\local\teacherfilestable\base::get_table_uniqueid($this->instance->id);
        $table = new \mod_openbook\local\teacherfilestable\upload($uniqueid . $this->coursemodule->id, $this, $filter);

        $this->allfilespage = $oldallfilespage;
        return $table;
    }

    /**
     * Get table with all files
     *
     * @param string $filter
     * @param bool $ignoreallfilespage (optional)
     */
    public function get_allfilestable($filter, $ignoreallfilespage = false) {
        global $DB;
        $mode = $this->get_mode();
        $oldallfilespage = $this->allfilespage;
        if ($ignoreallfilespage) {
            $this->allfilespage = true;
        }
        $uniqueid = \mod_openbook\local\allfilestable\base::get_table_uniqueid($this->instance->id);
        $table = new \mod_openbook\local\allfilestable\upload($uniqueid . $this->coursemodule->id, $this, $filter);
        $this->allfilespage = $oldallfilespage;
        return $table;
    }

    /**
     * Get table with files
     */
    public function get_filestable() {
        $table = new \mod_openbook\local\filestable\upload($this);
        return $table;
    }

    /**
     * Display form with table containing all teacher files
     */
    public function display_teacherfilesform() {
        global $CFG, $DB;
        $output = '';

        $cm = $this->coursemodule;
        $context = $this->context;

        $filter = optional_param('filter', OPENBOOK_FILTER_NOFILTER, PARAM_ALPHANUMEXT);

        $page = optional_param('page', 0, PARAM_INT);
        $perpage = 10;

        $formattrs = [];
        $formattrs['action'] = new moodle_url('/mod/openbook/view.php', ['allfilespage' => $this->allfilespage]);
        $formattrs['id'] = 'fastg';
        $formattrs['method'] = 'post';
        $formattrs['class'] = 'mform';

        $output .= html_writer::start_tag('form', $formattrs) .
                html_writer::empty_tag('input', [
                        'type' => 'hidden',
                        'name' => 'id',
                        'value' => $this->get_coursemodule()->id,
                ]) .
                html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'page', 'value' => $page]) .
                html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]) .
                html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'filter', 'value' => $filter]);

        $output .= html_writer::start_tag('fieldset', ['class' => 'clearfix collapsible', 'id' => 'id_allfiles']);

        // $teacherfiles = get_string('teacher_files', 'openbook');
        // $title = $teacherfiles;
        // $output .= html_writer::tag('legend', $title, ['class' => 'ftoggler h3']);

        $output .= html_writer::start_div('fcontainer clearfix mb-3');

        $f = groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/openbook/view.php?id=' . $cm->id, true);

        /* Download all file submissions button */
        $mf = new mod_openbook_allfiles_form(null, ['form' => $f]);
        $output .= $mf->render();

        $table = $this->get_teacherfilestable($filter);

        ob_start();
        $table->out($perpage, true); // Print the whole table.
        $tableoutput = ob_get_contents();
        ob_end_clean();

        $norowsfound = $table->get_count() == 0;
        $nofilesfound = $table->get_totalfilescount() == 0;

        /* Download all file submissions button */
        $link = html_writer::link(
            new moodle_url('/mod/openbook/view.php', [
                'id' => $this->coursemodule->id,
                'action' => 'zip',
                'allfilespage' => $this->allfilespage,
            ]),
            get_string('downloadall', 'openbook'),
            ['class' => 'btn btn-secondary mb-2']
        );

        if (!$norowsfound && !$nofilesfound) {
            $output .= html_writer::tag('div', $link, ['class' => 'mod-openbook-download-link']);
        }

        if ($perpage == 0) {
            $output .= '<style> nav.pagination ul.pagination li:only-child { display: none} </style>';
        }

        $output .= $tableoutput;

        if (has_capability('mod/openbook:uploadcommonteacherfile', $context)) {
            $uploadurl = new \moodle_url('/mod/openbook/upload_teacher.php', [
                'cmid' => $this->coursemodule->id,
            ]);

            $label = get_string('edit_teacher_uploads', 'openbook');

            $buttonhtml = \html_writer::link(
                $uploadurl,
                $label,
                ['class' => 'btn btn-primary']
            );

            $output .= \html_writer::div($buttonhtml, 'mt-3 mb-2');
        }

        $options = [];
        $options['zipusers'] = get_string('zipusers', 'openbook');

        if (has_capability('mod/openbook:approve', $context) && $table->totalfiles() > 0  && $this->allfilespage) {
            if ($this->get_instance()->obtainteacherapproval) {
                $options['approveusers'] = get_string('approveusers', 'openbook');
                $options['rejectusers'] = get_string('rejectusers', 'openbook');
            }

            if ($this->get_instance()->obtainstudentapproval) {
                $options['resetstudentapproval'] = get_string('resetstudentapproval', 'openbook');
            }
        }
        if (has_capability('mod/openbook:grantextension', $this->get_context()) && $this->allfilespage) {
            $options['grantextension'] = get_string('grantextension', 'openbook');
        }

        if (count($options) > 0 && !$norowsfound && !$nofilesfound) {
            $output .= html_writer::start_div('form-row');
            $marginstartclass = "ml-1";
            if ($CFG->version >= 2024041400) {
                $marginstartclass = "ms-1";
            }
            if (has_capability('mod/openbook:approve', $context) && $this->allfilespage) {
                $buttons = html_writer::empty_tag('input', [
                        'type' => 'reset',
                        'name' => 'resetvisibility',
                        'value' => get_string('reset', 'openbook'),
                        'class' => 'visibilitysaver btn btn-secondary ' . $marginstartclass,
                ]);

                if ($this->get_instance()->obtainstudentapproval) {
                    $buttons .= html_writer::empty_tag('input', [
                            'type' => 'submit',
                            'name' => 'savevisibility',
                            'value' => get_string('saveapproval', 'openbook'),
                            'class' => 'visibilitysaver btn btn-primary',
                    ]);
                } else {
                    $buttons .= html_writer::empty_tag('input', [
                            'type' => 'submit',
                            'name' => 'savevisibility',
                            'value' => get_string('saveteacherapproval', 'openbook'),
                            'class' => 'visibilitysaver btn btn-primary',
                    ]);
                }
            } else {
                $buttons = '';
            }
            $output .= html_writer::end_div();
        }

        $output .= html_writer::end_div();
        $output .= html_writer::end_tag('fieldset');
        $output .= html_writer::end_tag('form');

        // Mini form for setting user preference.
        $formaction = new moodle_url(
            '/mod/openbook/view.php',
            [
                'id' => $this->coursemodule->id,
                'allfilespage' => $this->allfilespage,
            ]
        );

        $mform = new MoodleQuickForm('optionspref', 'post', $formaction, '', ['class' => 'optionspref']);

        $attributes = [];

        $attributes['onChange'] = "document.querySelector('form.optionspref').submit()";

        $mform->addElement('hidden', 'updatepref');
        $mform->setDefault('updatepref', 1);

        if (has_capability('mod/openbook:approve', $context) && $this->allfilespage) {
            $filteroptions = [
                OPENBOOK_FILTER_NOFILTER => get_string(
                    'filter:' . OPENBOOK_FILTER_NOFILTER,
                    'openbook'
                ),
                OPENBOOK_FILTER_ALLFILES => get_string(
                    'filter:' . OPENBOOK_FILTER_ALLFILES,
                    'openbook'
                ),
            ];
            if ($this->get_instance()->obtainteacherapproval || $this->get_instance()->obtainstudentapproval) {
                $filteroptions += [
                    OPENBOOK_FILTER_APPROVED => get_string(
                        'filter:' . OPENBOOK_FILTER_APPROVED,
                        'openbook'
                    ),
                    OPENBOOK_FILTER_REJECTED => get_string(
                        'filter:' . OPENBOOK_FILTER_REJECTED,
                        'openbook'
                    ),
                    OPENBOOK_FILTER_APPROVALREQUIRED => get_string(
                        'filter:' . OPENBOOK_FILTER_APPROVALREQUIRED,
                        'openbook'
                    ),
                ];
            }
            $filteroptions += [
                OPENBOOK_FILTER_NOFILES => get_string(
                    'filter:' . OPENBOOK_FILTER_NOFILES,
                    'openbook'
                ),
            ];
            $mform->addElement('select', 'filter', get_string('filter', 'openbook'), $filteroptions, $attributes);
            $mform->setDefault('filter', $filter);
        }
        $mform->disable_form_change_checker();

        $output .= $mform->toHtml();
        return $output;
    }

    /**
     * Display form with table containing all files
     */
    public function display_allfilesform() {
        global $CFG, $DB;
        $output = '';

        $cm = $this->coursemodule;
        $context = $this->context;

        $updatepref = optional_param('updatepref', 0, PARAM_BOOL);
        if ($updatepref) {
            $perpage = optional_param('perpage', 10, PARAM_INT);
            $perpage = ($perpage < 0) ? 10 : $perpage;
            set_user_preference('mod-openbook-perpage-' . $this->instance->id, $perpage);
        }

        // Next we get perpage param from database!
        $perpage = get_user_preferences('mod-openbook-perpage-' . $this->instance->id, 10);

        $filter = optional_param('filter', OPENBOOK_FILTER_NOFILTER, PARAM_ALPHANUMEXT);

        $page = optional_param('page', 0, PARAM_INT);

        $formattrs = [];
        $formattrs['action'] = new moodle_url('/mod/openbook/view.php', ['allfilespage' => $this->allfilespage]);
        $formattrs['id'] = 'fastg';
        $formattrs['method'] = 'post';
        $formattrs['class'] = 'mform';

        $output .= html_writer::start_tag('form', $formattrs) .
                html_writer::empty_tag('input', [
                        'type' => 'hidden',
                        'name' => 'id',
                        'value' => $this->get_coursemodule()->id,
                ]) .
                html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'page', 'value' => $page]) .
                html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]) .
                html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'filter', 'value' => $filter]);

        $output .= html_writer::start_tag('fieldset', ['class' => 'clearfix collapsible', 'id' => 'id_allfiles']);
        $allfiles = get_string('allfiles', 'openbook');

        // $publicfiles = get_string('publicfiles', 'openbook');
        // $myownfiles = get_string('myownfiles', 'openbook');
        // $title = (has_capability('mod/openbook:approve', $context)  && $this->allfilespage) ? $allfiles : $publicfiles;
        // $output .= html_writer::tag('legend', $title, ['class' => 'ftoggler h3']);

        $output .= html_writer::start_div('fcontainer clearfix mb-3');

        $f = groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/openbook/view.php?id=' . $cm->id, true);

        /* Download all file submissions button */
        $mf = new mod_openbook_allfiles_form(null, ['form' => $f]);
        $output .= $mf->render();

        $table = $this->get_allfilestable($filter);

        ob_start();
        $table->out($perpage, true); // Print the whole table.
        $tableoutput = ob_get_contents();
        ob_end_clean();

        $norowsfound = $table->get_count() == 0;
        $nofilesfound = $table->get_totalfilescount() == 0;

        /* Download all file submissions button */
        $link = html_writer::link(
            new moodle_url('/mod/openbook/view.php', [
                'id' => $this->coursemodule->id,
                'action' => 'zip',
                'allfilespage' => $this->allfilespage,
            ]),
            get_string('downloadall', 'openbook'),
            ['class' => 'btn btn-secondary mb-2']
        );

        if (!$norowsfound && !$nofilesfound) {
            $output .= html_writer::tag('div', $link, ['class' => 'mod-openbook-download-link']);
        }

        if ($perpage == 0) {
            $output .= '<style> nav.pagination ul.pagination li:only-child { display: none} </style>';
        }

        $output .= $tableoutput;

        $options = [];
        $options['zipusers'] = get_string('zipusers', 'openbook');

        if (has_capability('mod/openbook:approve', $context) && $table->totalfiles() > 0  && $this->allfilespage) {
            if ($this->get_instance()->obtainteacherapproval) {
                $options['approveusers'] = get_string('approveusers', 'openbook');
                $options['rejectusers'] = get_string('rejectusers', 'openbook');
            }

            if ($this->get_instance()->obtainstudentapproval) {
                $options['resetstudentapproval'] = get_string('resetstudentapproval', 'openbook');
            }
        }
        if (has_capability('mod/openbook:grantextension', $this->get_context()) && $this->allfilespage) {
            $options['grantextension'] = get_string('grantextension', 'openbook');
        }

        if (count($options) > 0 && !$norowsfound && !$nofilesfound) {
            $output .= html_writer::start_div('form-row');
            $marginstartclass = "ml-1";
            if ($CFG->version >= 2024041400) {
                $marginstartclass = "ms-1";
            }
            if (has_capability('mod/openbook:approve', $context) && $this->allfilespage) {
                $buttons = html_writer::empty_tag('input', [
                        'type' => 'reset',
                        'name' => 'resetvisibility',
                        'value' => get_string('reset', 'openbook'),
                        'class' => 'visibilitysaver btn btn-secondary ' . $marginstartclass,
                ]);

                if ($this->get_instance()->obtainstudentapproval) {
                    $buttons .= html_writer::empty_tag('input', [
                            'type' => 'submit',
                            'name' => 'savevisibility',
                            'value' => get_string('saveapproval', 'openbook'),
                            'class' => 'visibilitysaver btn btn-primary',
                    ]);
                } else {
                    $buttons .= html_writer::empty_tag('input', [
                            'type' => 'submit',
                            'name' => 'savevisibility',
                            'value' => get_string('saveteacherapproval', 'openbook'),
                            'class' => 'visibilitysaver btn btn-primary',
                    ]);
                }
            } else {
                $buttons = '';
            }

            $output .= html_writer::start_div('withselection col-7 d-flex align-items-center gap-1 mt-3') .
                 html_writer::span(get_string('withselected', 'openbook')) .
                 html_writer::select($options, 'action') .
                 html_writer::empty_tag('input', [
                    'type' => 'submit',
                    'name' => 'submitgo',
                    'value' => get_string('go', 'openbook'),
                    'class' => 'btn btn-primary',
                 ]) . html_writer::end_div() .
                 html_writer::div($buttons, 'col');

            $output .= html_writer::end_div();
        }

        // Select all/none.
        $output .= html_writer::start_tag('div', ['class' => 'checkboxcontroller']) . "
            <script type=\"text/javascript\">
                function toggle_userselection() {
                    var checkboxes = document.getElementsByClassName('userselection');
                    var sel = document.getElementById('selectallnone');

                    if (checkboxes.length > 0) {
                        checkboxes[0].checked = sel.checked;

                        for(var i = 1; i < checkboxes.length;i++) {
                            checkboxes[i].checked = checkboxes[0].checked;
                        }
                    }
                }
            </script>";

        $output .= html_writer::end_div();

        $output .= html_writer::end_div();
        $output .= html_writer::end_tag('fieldset');
        $output .= html_writer::end_tag('form');

        // Mini form for setting user preference.
        $formaction = new moodle_url(
            '/mod/openbook/view.php',
            [
                'id' => $this->coursemodule->id,
                'allfilespage' => $this->allfilespage,
            ]
        );
        $mform = new MoodleQuickForm('optionspref', 'post', $formaction, '', ['class' => 'optionspref']);

        $attributes = [];

        $attributes['onChange'] = "document.querySelector('form.optionspref').submit()";

        $mform->addElement('hidden', 'updatepref');
        $mform->setDefault('updatepref', 1);

        $mform->addElement('header', 'qgprefs', get_string('optionalsettings', 'openbook'));

        $mform->addElement('select', 'perpage', get_string('entiresperpage', 'openbook'), [
            0 => get_string('all'),
            3 => 3,
            10 => 10,
            20 => 20,
            50 => 50,
            100 => 100,
        ], $attributes);
        $mform->setDefault('perpage', $perpage);

        if (has_capability('mod/openbook:approve', $context) && $this->allfilespage) {
            $filteroptions = [
                OPENBOOK_FILTER_NOFILTER => get_string(
                    'filter:' . OPENBOOK_FILTER_NOFILTER,
                    'openbook'
                ),
                OPENBOOK_FILTER_ALLFILES => get_string(
                    'filter:' . OPENBOOK_FILTER_ALLFILES,
                    'openbook'
                ),
            ];
            if ($this->get_instance()->obtainteacherapproval || $this->get_instance()->obtainstudentapproval) {
                $filteroptions += [
                    OPENBOOK_FILTER_APPROVED => get_string(
                        'filter:' . OPENBOOK_FILTER_APPROVED,
                        'openbook'
                    ),
                    OPENBOOK_FILTER_REJECTED => get_string(
                        'filter:' . OPENBOOK_FILTER_REJECTED,
                        'openbook'
                    ),
                    OPENBOOK_FILTER_APPROVALREQUIRED => get_string(
                        'filter:' . OPENBOOK_FILTER_APPROVALREQUIRED,
                        'openbook'
                    ),
                ];
            }
            $filteroptions += [
                OPENBOOK_FILTER_NOFILES => get_string(
                    'filter:' . OPENBOOK_FILTER_NOFILES,
                    'openbook'
                ),
            ];
            $mform->addElement('select', 'filter', get_string('filter', 'openbook'), $filteroptions, $attributes);
            $mform->setDefault('filter', $filter);
        }
        $mform->disable_form_change_checker();

        $output .= $mform->toHtml();
        return $output;
    }

    /**
     * Returns if a user has the permission to view a file
     *
     * @param int $fileid
     * @param number $userid use for custom user, if 0 then if public visible
     * @return boolean
     */
    public function has_filepermission($fileid, $userid = 0) {
        global $DB;

        $conditions = [];
        $conditions['openbook'] = $this->get_instance()->id;
        $conditions['fileid'] = $fileid;

        $filepermissions = $DB->get_record('openbook_file', $conditions);

        $haspermission = false;

        if ($filepermissions) {
            if ($userid != 0) {
                // Everyone is allowed to view their own files.
                $haspermission = true;
            }

            /* TODO: Define if user is not uploader, and files are personal, no permission */

            $obtainteacherapproval = $this->get_instance()->obtainteacherapproval;
            $obtainstudentapproval = $this->get_instance()->obtainstudentapproval;
            $teacherapproval = $filepermissions->teacherapproval;
            $studentapproval = $filepermissions->studentapproval;

            $haspermission = $haspermission ||
                (
                    (!$obtainteacherapproval || $teacherapproval == 1)
                    && (!$obtainstudentapproval || $studentapproval == 1)
                );
            /*
            if ($this->get_instance()->mode == OPENBOOK_MODE_UPLOAD) {
                // Mode upload.
                if ($this->get_instance()->obtainteacherapproval) {
                    // Need teacher approval.
                    if ($filepermissions->teacherapproval == 1) {
                        // Teacher has approved.
                        $haspermission = true;
                    }
                } else {
                    // No need for teacher approval.
                    if (is_null($filepermissions->teacherapproval) || $filepermissions->teacherapproval == 1) {
                        // Teacher only hasnt rejected.
                        $haspermission = true;
                    }
                }
            } else {
                // Mode import.
                if (!$this->get_instance()->obtainstudentapproval && $filepermissions->teacherapproval == 1) {
                    // No need to ask student and teacher has approved.
                    $haspermission = true;
                } else if ($this->get_instance()->obtainstudentapproval &&
                        $filepermissions->teacherapproval == 1 && $filepermissions->studentapproval == 1) {
                    // Student and teacher have approved.
                    $haspermission = true;
                }
            }*/
        }

        return $haspermission;
    }

    /**
     * Sets group approval for the specified user and returns current cumulated group approval!
     *
     * @param null|int $approval 0 if rejected, 1 if approved and 'null' if not set!
     * @param int $pubfileid ID of openbook file entry in DB
     * @param int $userid ID of user to set approval/rejection for
     * @return array cumulated approval for specified file, approving and needed count
     * @throws coding_exception
     * @throws dml_exception
     */
    public function set_group_approval($approval, $pubfileid, $userid) {
        global $DB;

        // Normalize approval value!
        /*if ($approval !== null) {
            $approval = empty($approval) ? 0 : 1;
        }*/

        $approvalforgroupapproval = $approval == 1 ? 1 : 0; // $approval == 2 => $approvalforgroupapproval = 0...

        $record = $DB->get_record('openbook_groupapproval', ['fileid' => $pubfileid, 'userid' => $userid]);
        $filerec = $DB->get_record('openbook_file', ['id' => $pubfileid]);
        if (!empty($record)) {
            if ($record->approval === $approvalforgroupapproval) {
                // Nothing changed, return!
                return $filerec->studentapproval;
            }
            $record->approval = $approvalforgroupapproval;
            $record->timemodified = time();
            $DB->update_record('openbook_groupapproval', $record);
        } else {
            $record = new stdClass();
            $record->fileid = $pubfileid;
            $record->userid = $userid;
            $record->approval = $approvalforgroupapproval;
            $record->timecreated = time();
            $record->timemodified = $record->timecreated;
            $record->id = $DB->insert_record('openbook_groupapproval', $record);
        }

        // Calculate new cumulated studentapproval for caching in file table!
        // Get group members!
        $groupmembers = $this->get_submissionmembers($filerec->userid);
        $stats = [];
        $stats['approving'] = 0;
        $stats['needed'] = count($groupmembers);
        if (!empty($groupmembers)) {
            [$usersql, $userparams] = $DB->get_in_or_equal(array_keys($groupmembers), SQL_PARAMS_NAMED, 'user');
            $select = "fileid = :fileid AND approval = :approval AND userid " . $usersql;
            $params = ['fileid' => $pubfileid, 'approval' => 0] + $userparams;
            if ($DB->record_exists_select('openbook_groupapproval', $select, $params)) {
                // If anyone rejected it's rejected, no matter what!
                $approval = 2; // 2 is rejected...
            } else {
                if ($this->get_instance()->groupapproval == OPENBOOK_APPROVAL_SINGLE) {
                    // If only one has to approve, we check for that!
                    $params['approval'] = 1;
                    if ($DB->record_exists_select('openbook_groupapproval', $select, $params)) {
                        $approval = 1;
                    } else {
                        $approval = 0;
                    }
                } else {
                    // All group members have to approve!
                    $select = "fileid = :fileid AND approval IS NULL AND userid " . $usersql;
                    $params = ['fileid' => $pubfileid] + $userparams;
                    $approving = $DB->count_records_sql("SELECT count(DISTINCT userid)
                                                           FROM {openbook_groupapproval}
                                                          WHERE fileid = :fileid AND approval = 1 AND userid " . $usersql, $params);
                    $stats['approving'] = $approving;
                    $stats['needed'] = count($userparams);
                    if ($approving < count($userparams)) {
                        // Rejected if not every group member has approved the file!
                        $approval = 0;
                    } else {
                        $approval = 1;
                    }
                }
            }
        } else {
            // Group without members, so no one could approve! (Should never happen, never ever)!
            $approval = 2;
        }

        // Update approval value and return it!
        $filerec->studentapproval = $approval;
        $DB->update_record('openbook_file', $filerec);
        $stats['approval'] = $approval;
        return $stats;
    }

    /**
     * Determine and return the teacher's approval status for the given file!
     *
     * @param stored_file $file file to determine approval status for
     * @return int|null teacher's approval status (null pending, 1 approved, all other rejected)
     */
    public function teacher_approval(\stored_file $file) {
        global $DB;

        if (empty($conditions)) {
            static $conditions = [];
            $conditions['openbook'] = $this->get_instance()->id;
        }
        $conditions['fileid'] = $file->get_id();

        $teacherapproval = $DB->get_field('openbook_file', 'teacherapproval', $conditions);
        $obtainteacherapproval = $this->get_instance()->obtainteacherapproval;
        if (!$obtainteacherapproval) {
            return 1;
        }

        return $teacherapproval;
    }

    /**
     * Determine and return the student's approval status for the given file!
     *
     * @param stored_file $file file to determine approval status for
     * @return int|null student's approval status (null/0 = pending, 1 = rejected, 2 = approved)
     */
    public function student_approval(\stored_file $file) {
        global $DB;

        if (empty($conditions)) {
            static $conditions = [];
            $conditions['openbook'] = $this->get_instance()->id;
        }
        $conditions['fileid'] = $file->get_id();

        $studentapproval = $DB->get_field('openbook_file', 'studentapproval', $conditions);

        // $studentapproval = (!is_null($studentapproval)) ? $studentapproval + 1 : null;

        return $studentapproval;
    }


    /**
     * Determine and return the commonteacherfile status for the given file!
     *
     * @param stored_file $file file to determine approval status for
     * @return int|null commonteacherfile status (null/0 = no common teacher file, 1 = common teacher file)
     */
    public function commonteacherfile(\stored_file $file) {
        global $DB;

        if (empty($conditions)) {
            static $conditions = [];
            $conditions['openbook'] = $this->get_instance()->id;
        }
        $conditions['fileid'] = $file->get_id();

        $commonteacherfile = $DB->get_field('openbook_file', 'commonteacherfile', $conditions);

        return $commonteacherfile;
    }

    /**
     * Gets the group members for the specified group. Or users without membership if groupid is 0!
     *
     * @param int $groupid
     * @return stdClass[] Group member's user records.
     */
    public function get_submissionmembers($groupid) {
        global $DB;
        static $availabilityinfo = null;
        if (is_null($availabilityinfo)) {
            $modinfo = get_fast_modinfo($this->course->id);
            $availabilityinfo = new \core_availability\info_module($modinfo->get_cm($this->coursemodule->id));
        }

        if (!empty($groupid)) {
            $groupmembers = groups_get_members($groupid);
        } else if (!$DB->get_field('assign', 'preventsubmissionnotingroup', ['id' => $this->get_instance()->importfrom])) {
            // If groupid == 0, we get all users without group!
            $groupmembers = [];
            $assigncm = get_coursemodule_from_instance('assign', $this->instance->importfrom);
            $context = context_module::instance($assigncm->id);
            $users = get_enrolled_users($context, "mod/assign:submit", 0);
            if (!empty($users)) {
                foreach ($users as $user) {
                    $ugrps = groups_get_user_groups($this->instance->course, $user->id);
                    if (!count($ugrps[0])) {
                        $groupmembers[$user->id] = $user;
                    }
                }
            }
        } else {
            $groupmembers = [];
        }

        if ($this->get_instance()->availabilityrestriction) {
            $groupmembers = $availabilityinfo->filter_user_list($groupmembers);
        }

        return $groupmembers;
    }

    /**
     * Download a single file, returns file content and terminated script.
     *
     * @param int $fileid ID of the submitted file in filespace
     */
    public function download_file($fileid) {
        global $DB, $USER;

        $conditions = [];
        $conditions['openbook'] = $this->get_instance()->id;
        $conditions['fileid'] = $fileid;
        $record = $DB->get_record('openbook_file', $conditions);

        $allowed = false;

        if (has_capability('mod/openbook:approve', $this->get_context())) {
            // Teachers has to see the files to know if they can allow them.
            $allowed = true;
        } else if ($this->has_filepermission($fileid, $USER->id)) {
            // File is publicly viewable or is owned by the user.
            $allowed = true;
        }

        if ($allowed) {
            $fs = get_file_storage();
            $file = $fs->get_file_by_id($fileid);
            $itemid = $file->get_itemid();
            send_file($file, $file->get_filename(), null, 0, false, true, $file->get_mimetype(), false);
            die();
        } else {
            throw new \moodle_exception('You are not allowed to see this file', 'mod_openbook');
        }
    }

    /**
     * Creates a zip of all uploaded files and sends a zip to the browser
     *
     * @param array|bool $uploaders false => empty zip, true all users, array files from uploaders (users/groups) in array
     */
    public function download_zip($uploaders = []) {
        global $CFG, $DB, $USER;
        require_once($CFG->libdir . '/filelib.php');

        $cm = $this->get_coursemodule();

        $canapprove = has_capability('mod/openbook:approve', $this->get_context());
        if ($this->get_instance()->importfrom == -1) {
            $teamsubmission = false;
        } else {
            $teamsubmission = $this->teamsubmission;
        }

        $conditions = [];
        $conditions['openbook'] = $this->get_instance()->id;

        $filesforzipping = [];
        $fs = get_file_storage();

        // Get group name for filename.
        $groupname = '';
        $currentgroup = groups_get_activity_group($cm, true);
        if (!empty($currentgroup)) {
            $groupname = $DB->get_field('groups', 'name', ['id' => $currentgroup]) . '-';
        }

        if (!$teamsubmission) {
            $uploaders = $this->get_users($uploaders);
        } else {
            $uploaders = $this->get_groups(0, $uploaders);
        }

        $filename = str_replace(' ', '_', clean_filename($this->course->shortname . '-' .
                $this->get_instance()->name . '-' . $groupname . $this->get_instance()->id . '.zip')); // Name of new zip file.

        $userfields = \core_user\fields::get_name_fields();
        $userfields['id'] = 'id';
        $userfields['username'] = 'username';
        $userfields = implode(', ', $userfields);

        // Get all files from each user/group.
        foreach ($uploaders as $uploader) {
            $conditions['userid'] = $uploader;
            $records = $DB->get_records('openbook_file', $conditions);

            if (!$teamsubmission) {
                // Get user firstname/lastname.
                $auser = $DB->get_record('user', ['id' => $uploader], $userfields);
                $itemname = fullname($auser);
                $itemunique = $uploader;
            } else {
                if (empty($uploader)) {
                    $itemname = get_string('defaultteam', 'assign');
                } else {
                    $itemname = $DB->get_field('groups', 'name', ['id' => $uploader]);
                }
                $itemunique = '';
            }

            foreach ($records as $record) {
                if ($canapprove || $this->has_filepermission($record->fileid, $USER->id)) {
                    // Is teacher or file is public.

                    $file = $fs->get_file_by_id($record->fileid);

                    // Get files new name.
                    $fileext = strstr($file->get_filename(), '.');
                    $fileoriginal = str_replace($fileext, '', $file->get_filename());
                    $fileforzipname = clean_filename($itemname . '_' . $fileoriginal . '_' . $itemunique . $fileext);
                    if (key_exists($fileforzipname, $filesforzipping)) {
                        throw new coding_exception('Can\'t overwrite ' . $fileforzipname . '!');
                    }
                    // Save file name to array for zipping.
                    $filesforzipping[$fileforzipname] = $file;
                }
            }
        } // End of foreach.

        if ($zipfile = $this->pack_files($filesforzipping)) {
            send_temp_file($zipfile, $filename); // Send file and delete after sending.
        }
    }

    /**
     * Pack files in ZIP
     *
     * @param object[] $filesforzipping Files for zipping
     * @return bool|string zipped files
     */
    private function pack_files($filesforzipping) {
        global $CFG;
        // Create path for new zip file.
        $tempzip = tempnam($CFG->dataroot . '/temp/', 'openbook_');
        // Zip files.
        $zipper = new zip_packer();
        if ($zipper->archive_to_pathname($filesforzipping, $tempzip)) {
            return $tempzip;
        }

        return false;
    }

    /**
     * Update teacher approval for specified userids or groupids
     *
     * @param array $userorgroupids
     * @param string $action
     */
    public function update_users_or_groups_teacherapproval($userorgroupids, $action) {
        global $DB;

        [$usersql, $params] = $DB->get_in_or_equal($userorgroupids, SQL_PARAMS_NAMED, 'user');
        $params['pubid'] = $this->instance->id;
        $select = ' openbook=:pubid AND userid ' . $usersql;
        $records = $DB->get_records_select('openbook_file', $select, $params);
        $files = [];

        foreach ($records as $record) {
            $files[$record->fileid] = $action;
        }
        $this->update_files_teacherapproval($files);
    }

    /**
     * Changes teacher approval for the specified files
     *
     * @param array $files array of fileids and new approval status, fileid => teacher approval status
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function update_files_teacherapproval($files) {
        global $DB, $USER;

        foreach ($files as $fileid => $newfileaction) {
            $x = $DB->get_record(
                'openbook_file',
                ['fileid' => $fileid],
                $fields = "fileid,userid,teacherapproval,id,studentapproval,filename"
            );

            $oldteacherapproval = $x->teacherapproval;
            $oldstudentapproval = $x->studentapproval;

            $resetstudentapproval = false;
            $teacherapprove = false;
            $teacherreject = false;
            $newteacherapproval = 0;
            $newstatus = '';
            $logstatus = '';

            switch ($newfileaction) {
                case '1':
                case 'approveusers':
                    if ($oldteacherapproval == 1) {
                        continue 2;
                    }
                    $teacherapprove = true;
                    $newstatus = '';
                    $logstatus .= 'approved';
                    $newteacherapproval = 1;
                    break;
                case '2':
                case 'rejectusers':
                    if ($oldteacherapproval == 2) {
                        continue 2;
                    }
                    $teacherreject = true;
                    $newstatus = 'not';
                    $logstatus .= 'rejected';
                    $newteacherapproval = 2;
                    break;
                case 'resetstudentapproval':
                    $resetstudentapproval = true;
                    $newstatus = 'revoke';
                    $logstatus .= 'revoked';
                    break;
                default:
                    continue 2;
            }

            $user = $DB->get_record('user', ['id' => $x->userid]);
            $group = false;

            $dataforlog = new stdClass();
            $dataforlog->openbook = $this->instance->id;
            $dataforlog->approval = $logstatus;
            $dataforlog->userid = $USER->id;
            if ($user && !empty($user->id)) {
                $dataforlog->reluser = $user->id;
            } else {
                $dataforlog->reluser = 0;
            }
            $dataforlog->fileid = $fileid;

            try {
                \mod_openbook\event\openbook_approval_changed::approval_changed(
                    $this->coursemodule,
                    $dataforlog
                )->trigger();
            } catch (coding_exception $e) {
                throw new Exception("Coding exception while sending notification: " . $e->getMessage());
            }

            if ($teacherapprove || $teacherreject) {
                $DB->set_field('openbook_file', 'teacherapproval', $newteacherapproval, ['fileid' => $fileid]);
            } else { // Reset student approval.
                $DB->set_field('openbook_file', 'studentapproval', 0, ['fileid' => $fileid]);
            }

            if ($this->instance->notifystatuschange != 0) {
                $cm = $this->coursemodule;
                $cmid = $this->coursemodule->id;
                self::send_notification_statuschange($cm, $USER, $newstatus, $x, $cmid, $this);
            }
        }
    }

    /**
     * Send a notification about the change of the approval status to a student
     * @param stdClass $cm coursemodule
     * @param object $userfrom who cahnged the approval status
     * @param string $newstatus whats the new status
     * @param object $pubfile the openbook-file on which the status change took place
     * @param string $pubid id of the openbook
     * @param null|openbook $openbook the openbook instance
     * @throws coding_exception
     */
    public static function send_notification_statuschange(
        $cm,
        $userfrom,
        $newstatus,
        $pubfile,
        $pubid,
        $openbook = null
    ) {
        global $CFG, $DB;
        $sm = get_string_manager();

        if (!$openbook) {
            $openbook = new openbook($cm);
        }

        $notifyfilechange = $openbook->get_instance()->notifyfilechange;
        $receivers = [];
        if ($notifyfilechange == OPENBOOK_NOTIFY_TEACHER || $notifyfilechange == OPENBOOK_NOTIFY_ALL) {
            $receivers = $openbook->get_graders($userfrom);
        }
        if ($notifyfilechange == OPENBOOK_NOTIFY_STUDENT || $notifyfilechange == OPENBOOK_NOTIFY_ALL) {
            $student = $DB->get_record('user', ['id' => $pubfile->userid]);
            $receivers[$student->id] = $student;
        }
        if (!empty($receivers)) {
            foreach ($receivers as $receiver) {
                $strsubmitted = $sm->get_string('approvalchange', 'openbook', null, $receiver->lang);
                $info = new stdClass();
                $info->username = fullname($userfrom);
                $info->openbook = format_string($cm->name, true);
                $info->url = $CFG->wwwroot . '/mod/openbook/view.php?id=' . $pubid;
                $info->id = $pubid;
                $info->filename = $pubfile->filename;
                $info->apstatus = $sm->get_string(
                    'status:approved' . $newstatus,
                    'mod_openbook',
                    null,
                    $receiver->lang
                );
                $info->dayupdated = userdate(time(), $sm->get_string('strftimedate', 'core_langconfig', null, $receiver->lang));
                $info->timeupdated = userdate(time(), $sm->get_string('strftimetime24', 'core_langconfig', null, $receiver->lang));

                if (!isset(self::$pendingnotifications[OPENBOOK_NOTIFY_STATUSCHANGE])) {
                    self::$pendingnotifications[OPENBOOK_NOTIFY_STATUSCHANGE] = [];
                }
                if (
                    !isset(
                        self::$pendingnotifications[OPENBOOK_NOTIFY_STATUSCHANGE][$cm->id]
                    )
                ) {
                    self::$pendingnotifications[OPENBOOK_NOTIFY_STATUSCHANGE][$cm->id] = [];
                }

                $includeheader = !isset(
                    self::$pendingnotifications[OPENBOOK_NOTIFY_STATUSCHANGE][$cm->id][$receiver->id]
                );
                $postsubject = $strsubmitted . ': ' . $cm->name;
                $posttext = $openbook->email_statuschange_text($info, $receiver->lang, $includeheader);
                $posthtml = $openbook->email_statuschange_html($info, $receiver->lang, $includeheader);

                // TODO maybe add check here is receiver is the same as user from. Unless already checked in get_graders
                if (!isset(self::$pendingnotifications[OPENBOOK_NOTIFY_STATUSCHANGE][$cm->id][$receiver->id])) {
                    $message = new \core\message\message();
                    $message->component = 'mod_openbook';
                    $message->name = 'openbook_updates';
                    $message->courseid = $cm->course;
                    $message->userfrom = core_user::get_noreply_user();
                    $message->userto = $receiver;
                    $message->subject = $postsubject;
                    $message->fullmessage = '';
                    $message->fullmessagehtml = '';
                    $message->fullmessageformat = FORMAT_HTML;
                    $message->smallmessage = $postsubject;
                    $message->notification = 1;
                    $message->contexturl = $info->url;
                    $message->contexturlname = $info->openbook;
                    self::$pendingnotifications[OPENBOOK_NOTIFY_STATUSCHANGE][$cm->id][$receiver->id] = $message;
                }

                self::$pendingnotifications[OPENBOOK_NOTIFY_STATUSCHANGE][$cm->id][$receiver->id]->fullmessage .=
                    $posttext;
                self::$pendingnotifications[OPENBOOK_NOTIFY_STATUSCHANGE][$cm->id][$receiver->id]->fullmessagehtml .=
                    $posthtml;
            }
        }
    }

    /**
     * Sends a notification to assigned grades
     * @param object $cm course module
     * @param object $file the file
     * @param stdClass|null $user the user
     * @param stdClass|null $openbook object the openbook, if available
     * @throws coding_exception
     */
    public static function send_notification_filechange($cm, $file, $user = null, $openbook = null) {
        global $CFG, $USER, $DB;
        $sm = get_string_manager();
        if (!$user) {
            $user = $USER;
        }
        if (!$openbook) {
            $openbook = new openbook($cm);
        }

        $stridentifier = 'filechange_import';

        $notifyfilechange = $openbook->get_instance()->notifyfilechange;
        $receivers = [];
        if ($notifyfilechange == OPENBOOK_NOTIFY_TEACHER || $notifyfilechange == OPENBOOK_NOTIFY_ALL) {
            $receivers = $openbook->get_graders($user);
        }
        if ($notifyfilechange == OPENBOOK_NOTIFY_STUDENT || $notifyfilechange == OPENBOOK_NOTIFY_ALL) {
            $student = $DB->get_record('user', ['id' => $file->userid]);
            $receivers[$student->id] = $student;
        }
        if (!empty($receivers)) {
            foreach ($receivers as $receiver) {
                $strsubmitted = $sm->get_string(
                    'email:' . $stridentifier . ':subject',
                    'openbook',
                    null,
                    $receiver->lang
                );
                $info = new stdClass();
                $info->username = fullname($user);
                $info->openbook = format_string($openbook->get_instance()->name, true);
                $info->url = $CFG->wwwroot . '/mod/openbook/view.php?id=' . $cm->id;
                $info->id = $cm->id;
                $info->filename = $file->filename;
                $info->dayupdated = userdate(time(), $sm->get_string('strftimedate', 'core_langconfig', null, $receiver->lang));
                $info->timeupdated = userdate(time(), $sm->get_string('strftimetime24', 'core_langconfig', null, $receiver->lang));

                if (!isset(self::$pendingnotifications[OPENBOOK_NOTIFY_FILECHANGE])) {
                    self::$pendingnotifications[OPENBOOK_NOTIFY_FILECHANGE] = [];
                }
                if (!isset(self::$pendingnotifications[OPENBOOK_NOTIFY_FILECHANGE][$cm->id])) {
                    self::$pendingnotifications[OPENBOOK_NOTIFY_FILECHANGE][$cm->id] = [];
                }

                $includeheader = !isset(
                    self::$pendingnotifications[OPENBOOK_NOTIFY_FILECHANGE][$cm->id][$receiver->id]
                );
                $postsubject = $strsubmitted . ': ' . $info->openbook;
                $posttext = $openbook->email_filechange_text($info, $receiver->lang, $stridentifier, $includeheader);
                $posthtml = $openbook->email_filechange_html($info, $receiver->lang, $stridentifier, $includeheader);

                // TODO maybe add check here is receiver is the same as user from. Unless already checked in get_graders

                if (!isset(self::$pendingnotifications[OPENBOOK_NOTIFY_FILECHANGE][$cm->id][$receiver->id])) {
                    $message = new \core\message\message();
                    $message->component = 'mod_openbook';
                    $message->name = 'openbook_updates';
                    $message->courseid = $cm->course;
                    $message->userfrom = core_user::get_noreply_user();
                    $message->userto = $receiver;
                    $message->subject = $postsubject;
                    $message->fullmessage = '';
                    $message->fullmessageformat = FORMAT_HTML;
                    $message->fullmessagehtml = '';
                    $message->smallmessage = $postsubject;
                    $message->notification = 1;
                    $message->contexturl = $info->url;
                    $message->contexturlname = $info->openbook;
                    self::$pendingnotifications[OPENBOOK_NOTIFY_FILECHANGE][$cm->id][$receiver->id] = $message;
                }
                self::$pendingnotifications[OPENBOOK_NOTIFY_FILECHANGE][$cm->id][$receiver->id]->fullmessage .=
                    $posttext;
                self::$pendingnotifications[OPENBOOK_NOTIFY_FILECHANGE][$cm->id][$receiver->id]->fullmessagehtml .=
                    $posthtml;

                // message_send($message);
            }
        }
    }

    /**
     * Sends all pending notifications
     */
    public static function send_all_pending_notifications() {
        $sm = get_string_manager();
        foreach (self::$pendingnotifications as $type => $cms) {
            foreach ($cms as $cmid => $users) {
                foreach ($users as $userid => $message) {
                    if ($type == OPENBOOK_NOTIFY_FILECHANGE) {
                        $footertext = $sm->get_string(
                            'email:filechange:footer',
                            'openbook',
                            null,
                            $message->userto->lang
                        );
                        $message->fullmessage .= PHP_EOL . strip_tags($footertext);
                        $message->fullmessagehtml .= $sm->get_string(
                            'email:filechange:footer',
                            'openbook',
                            null,
                            $message->userto->lang
                        );
                    } else {
                        $message->fullmessage .= '';
                        $message->fullmessagehtml .= '</ul>';
                    }
                    try {
                        message_send($message);
                    } catch (coding_exception $e) {
                        throw new Exception("Coding exception while sending notification: " . $e->getMessage());
                    }
                }
            }
        }
    }

    // Allowed file-types have been changed in Moodle 3.3 (and form element will probably change in Moodle 3.4 again)!

    /**
     * Get the type sets configured for this openbook.
     * Adapted from assignsubmission_file!
     *
     * @return array('groupname', 'mime/type', ...)
     */
    public function get_configured_typesets() {
        $typeslist = (string)$this->instance->allowedfiletypes;

        $sets = self::get_typesets($typeslist);

        return $sets;
    }

    /**
     * Get the type sets passed.
     * Adapted from assignsubmission_file!
     *
     * @param string $types The space , ; separated list of types
     * @return array('groupname', 'mime/type', ...)
     */
    public static function get_typesets($types) {
        $sets = [];
        if (!empty($types)) {
            $sets = preg_split('/[\s,;:"\']+/', $types, -1, PREG_SPLIT_NO_EMPTY);
        }

        return $sets;
    }

    /**
     * Return the accepted types list for the file manager component.
     * Adapted from assignsubmission_file!
     *
     * @return array|string
     */
    public function get_accepted_types() {
        $acceptedtypes = $this->get_configured_typesets();

        if (!empty($acceptedtypes)) {
            return $acceptedtypes;
        }

        return '*';
    }

    /**
     * List the nonexistent file types that need to be removed.
     * Adapted from assignsubmission_file!
     *
     * @param string $types space , or ; separated types
     * @return array A list of the nonexistent file types.
     */
    public static function get_nonexistent_file_types($types) {
        $nonexistent = [];
        foreach (self::get_typesets($types) as $type) {
            // If there's no extensions under that group, it doesn't exist.
            $extensions = file_get_typegroup('extension', [$type]);
            if (empty($extensions)) {
                $nonexistent[$type] = true;
            }
        }

        return array_keys($nonexistent);
    }

    /**
     * Returns a list of teachers that should be notified of the file-upload
     *
     * @param object $user
     * @return array Array of users able to grade
     */
    public function get_graders($user) {
        // Get potential graders!
        $potgraders = get_enrolled_users(
            $this->context,
            'mod/openbook:receiveteachernotification',
            0,
            'u.*',
            null,
            0,
            0,
            true
        );
        $graders = [];
        if (groups_get_activity_groupmode($this->coursemodule) == SEPARATEGROUPS) {
            // Separate groups are being used!
            if ($groups = groups_get_all_groups($this->course->id, $user->id)) {
                // Try to find all groups!
                foreach ($groups as $group) {
                    foreach ($potgraders as $t) {
                        if ($t->id == $user->id) {
                            continue; // Do not send self!
                        }
                        if (groups_is_member($group->id, $t->id)) {
                            $graders[$t->id] = $t;
                        }
                    }
                }
            } else {
                // User not in group, try to find graders without group!
                foreach ($potgraders as $t) {
                    if ($t->id == $user->id) {
                        continue; // Do not send to one self!
                    }
                    if (!groups_get_all_groups($this->course->id, $t->id)) { // Ugly hack!
                        $graders[$t->id] = $t;
                    }
                }
            }
        } else {
            foreach ($potgraders as $t) {
                if ($t->id == $user->id) {
                    continue; // Do not send to one self!
                }
                $graders[$t->id] = $t;
            }
        }
        return $graders;
    }

    /**
     * Creates the text content for emails to teachers
     *
     * @param object $info The info used by the 'emailteachermail' language string
     * @param string $lang
     * @param string $stridentifier
     * @param bool $includeheader (optional)
     * @return string Plain-Text snippet to use in messages
     */
    public function email_filechange_text($info, $lang, $stridentifier, $includeheader = true) {
        $sm = get_string_manager();
        $posttext = '';
        if ($includeheader) {
            $posttext .= format_string($this->course->shortname) . ' -> ' .
                $sm->get_string('modulenameplural', 'openbook', null, $lang) . ' -> ' .
                format_string($info->openbook) . "\n";
            $posttext .= strip_tags(
                $sm->get_string(
                    'email:' . $stridentifier . ':header',
                    'openbook',
                    $info,
                    $lang
                )
            ) . "\n";
        }
        $posttext .= $info->filename . "\n";

        return $posttext;
    }

    /**
     * Creates the html content for emails to teachers
     *
     * @param object $info The info used by the 'emailteachermailhtml' language string
     * @param string $lang
     * @param string $stridentifier
     * @param bool $includeheader (optional)
     * @return string HTML snippet to use in messages
     */
    public function email_filechange_html($info, $lang, $stridentifier, $includeheader = true) {
        global $CFG;
        $sm = get_string_manager();
        $posthtml = '';
        if ($includeheader) {
            $posthtml .= '<p><span style="font-family: sans-serif; ">' .
                '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $this->course->id . '">' .
                format_string($this->course->shortname) . '</a> ->' .
                '<a href="' . $CFG->wwwroot . '/mod/openbook/view.php?id=' .
                $info->id . '">' . $sm->get_string('modulenameplural', 'openbook', null, $lang) . '</a> ->' .
                '<a href="' . $CFG->wwwroot . '/mod/openbook/view.php?id=' . $info->id . '">' .
                format_string($info->openbook) . '</a></span></p>';
            $posthtml .= '' . $sm->get_string('email:' . $stridentifier . ':header', 'openbook', $info, $lang) . '';
        }
        $posthtml .= '<li>' . $info->filename . '</li>';

        return $posthtml;
    }

    /**
     * Creates the text content for emails to students
     *
     * @param object $info The info used by the 'emailteachermail' language string
     * @param string $lang
     * @param bool $includeheader (optional)
     * @return string Plain-Text snippet to use in messages
     */
    public function email_statuschange_text($info, $lang, $includeheader = true) {
        $sm = get_string_manager();
        $posttext = '';
        if ($includeheader) {
            $posttext .= format_string($this->course->shortname) . ' -> ' .
                $sm->get_string('modulenameplural', 'openbook', null, $lang) . ' -> ' .
                format_string($info->openbook) . "\n";
            $posttext .= "---------------------------------------------------------------------\n";
            $posttext .= strip_tags($sm->get_string('email:statuschange:header', 'openbook', $info, $lang)) . "\n";
        }
        $posttext .= strip_tags($sm->get_string('email:statuschange:filename', 'openbook', $info, $lang)) . "\n";
        return $posttext;
    }

    /**
     * Creates the html content for emails to students
     *
     * @param object $info The info used by the 'emailstudentsmailhtml' language string
     * @param string $lang
     * @param bool $includeheader (optional)
     * @return string HTML snippet to use in messages
     */
    public function email_statuschange_html($info, $lang, $includeheader = true) {
        global $CFG;
        $sm = get_string_manager();
        $posthtml = '';
        if ($includeheader) {
            $posthtml .= '<p><span style="font-family: sans-serif; ">' .
                '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $this->course->id . '">' .
                format_string($this->course->shortname) . '</a> ->' .
                '<a href="' . $CFG->wwwroot . '/mod/openbook/view.php?id=' .
                $info->id . '">' . $sm->get_string('modulenameplural', 'openbook', null, $lang) . '</a> ->' .
                '<a href="' . $CFG->wwwroot . '/mod/openbook/view.php?id=' . $info->id . '">' .
                format_string($info->openbook) . '</a></span></p>';
            $posthtml .= '' . $sm->get_string('email:statuschange:header', 'openbook', $info, $lang);
        }
        $posthtml .= $sm->get_string('email:statuschange:filename', 'openbook', $info, $lang);

        return $posthtml;
    }

    /**
     * Handles calendar events for openbooks with a due date
     * This will create, update and delete an event when necessary
     */
    public function update_calendar_event() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/calendar/lib.php');

        $instance = $this->get_instance();

        // Check whether the openbook already has a event.
        $result = $DB->get_record('event', ['modulename' => 'openbook', 'instance' => $instance->id]);

        if ($result) {
            // Check whether the openbook still has a due date, if not delete the event.
            if ($instance->duedate == null || $instance->duedate == 0) {
                $DB->delete_records('event', ['modulename' => 'openbook', 'instance' => $instance->id]);
            } else {
                $result->name = $instance->name;
                $result->timestart = $instance->duedate;
                $result->timesort = $instance->duedate;

                $DB->update_record('event', $result);
            }
        } else if ($instance->duedate != null && $instance->duedate != 0) {
            $event = new stdClass();
            $event->eventtype = OPENBOOK_EVENT_TYPE_DUE;
            $event->type = CALENDAR_EVENT_TYPE_ACTION; // Necessary to enable this event in block_myoverview.
            $event->name = $instance->name;
            $event->description = "";
            $event->courseid = $instance->course;
            $event->groupid = 0;
            $event->userid = 0;
            $event->modulename = 'openbook';
            $event->instance = $instance->id;
            $event->visible = instance_is_visible('openbook', $this->instance);
            $event->timestart = $instance->duedate;
            $event->timesort = $instance->duedate; // Necessary for block_myoverview.
            $event->timeduration = 0;

            calendar_event::create($event);
        }
    }

    /**
     * Export overrides for template
     */
    public function overrides_export_for_template() {
        global $DB;
        $context = new stdClass();

        $editurl = new moodle_url('/mod/openbook/overrides_edit.php', ['id' => $this->coursemodule->id]);
        $deleteurl = new moodle_url('/mod/openbook/overrides_delete.php', ['id' => $this->coursemodule->id]);

        $context->newoverrideurl = (new moodle_url($editurl, ['overrideid' => -1]))->out(false);

        $overrides = $DB->get_records('openbook_overrides', ['openbook' => $this->instance->id]);
        $context->overridesempty = count($overrides) == 0;
        $context->overrides = [];

        // phpcs:disable moodle.Commenting.TodoComment
        // TODO: this has to be done in https://github.com/a-camacho/moodle-mod_openbook/issues/11
        $isgroupmode = false;
        $context->isgroupmode = false;

        if (false) {
            $context->usergroupcoltitle = get_string('group');
            $context->addoverridetitle = get_string('override:add:group', 'mod_openbook');
        } else {
            $context->usergroupcoltitle = get_string('user');
            $context->addoverridetitle = get_string('override:add:user', 'mod_openbook');
        }

        $userurl = new moodle_url('/user/view.php', ['course' => $this->course->id]);

        if (!empty($overrides)) {
            foreach ($overrides as $override) {
                if ($isgroupmode) {
                    $group = $DB->get_record('groups', ['id' => $override->groupid]);
                    $override->fullname = $group->name;
                } else {
                    $user = $DB->get_record('user', ['id' => $override->userid]);
                    $override->fullname = fullname($user);
                    $userurl->param('id', $override->userid);
                    $override->userurl = $userurl->out(false);
                }
                $override->editurl = (new moodle_url($editurl, ['overrideid' => $override->id]))->out(false);
                $override->deleteurl = (new moodle_url($deleteurl, ['overrideid' => $override->id]))->out(false);
                $override = $this->override_export_for_template_single($override);
                $context->overrides[] = $override;
            }
        }
        return $context;
    }

    /**
     * Export overrides for single template
     *
     * @param stdClass $override
     */
    public function override_export_for_template_single($override) {
        $override->submissionoverride = null;
        $override->approvaloverride = null;
        $override->securewindowoverride = null;
        if ($override->allowsubmissionsfromdate > 0 || $override->duedate > 0) {
            $fromto = (object)[
                'from' => userdate($override->allowsubmissionsfromdate),
                'to' => userdate($override->duedate),
            ];
            if ($override->allowsubmissionsfromdate > 0 && $override->duedate > 0) {
                $override->submissionoverride = get_string('override:submission:fromto', 'mod_openbook', $fromto);
            } else if ($override->allowsubmissionsfromdate > 0) {
                $override->submissionoverride = get_string('override:submission:from', 'mod_openbook', $fromto);
            } else if ($override->duedate > 0) {
                $override->submissionoverride = get_string('override:submission:to', 'mod_openbook', $fromto);
            }
        }
        if (
            $this->instance->obtainstudentapproval == 1 &&
            ($override->approvalfromdate > 0 || $override->approvaltodate > 0)
        ) {
            $fromto = (object)[
                'from' => userdate($override->approvalfromdate),
                'to' => userdate($override->approvaltodate),
            ];
            if ($override->approvalfromdate > 0 && $override->approvaltodate > 0) {
                $override->approvaloverride = get_string('override:approval:fromto', 'mod_openbook', $fromto);
            } else if ($override->approvalfromdate > 0) {
                $override->approvaloverride = get_string('override:approval:from', 'mod_openbook', $fromto);
            } else if ($override->approvaltodate > 0) {
                $override->approvaloverride = get_string('override:approval:to', 'mod_openbook', $fromto);
            }
        }
        if ($override->securewindowfromdate > 0 || $override->securewindowtodate > 0) {
            $fromto = (object)[
                'from' => userdate($override->securewindowfromdate),
                'to' => userdate($override->securewindowtodate),
            ];
            if ($override->securewindowfromdate > 0 && $override->securewindowtodate > 0) {
                $override->securewindowoverride = get_string('override:securewindow:fromto', 'mod_openbook', $fromto);
            } else if ($override->securewindowfromdate > 0) {
                $override->securewindowoverride = get_string('override:securewindow:from', 'mod_openbook', $fromto);
            } else if ($override->securewindowtodate > 0) {
                $override->securewindowoverride = get_string('override:securewindow:to', 'mod_openbook', $fromto);
            }
        }
        return $override;
    }

    /**
     * Save override
     *
     * @param object $formdata
     */
    public function override_save($formdata) {
        global $DB;
        $overrideresult = new stdClass();
        $overrideresult->overrideid = 0;
        $overrideresult->newoverride = false;
        if (
            (!isset($formdata->allowsubmissionsfromdate) || $formdata->allowsubmissionsfromdate == 0) &&
            (!isset($formdata->duedate) || $formdata->duedate == 0) &&
            (!isset($formdata->approvalfromdate) || $formdata->approvalfromdate == 0) &&
            (!isset($formdata->approvaltodate) || $formdata->approvaltodate == 0) &&
            (!isset($formdata->securewindowfromdate) || $formdata->securewindowfromdate == 0) &&
            (!isset($formdata->securewindowtodate) || $formdata->securewindowtodate == 0)
        ) {
            return null;
        }
        if ($formdata->overrideid != -1) {
            $override = $DB->get_record(
                'openbook_overrides',
                [
                    'id' => $formdata->overrideid,
                    'openbook' => $this->instance->id,
                ]
            );
            unset($formdata->id);
            unset($formdata->overrideid);
            if (!$override) {
                $formdata->openbook = $this->instance->id;
                $overrideresult->overrideid = $DB->insert_record('openbook_overrides', $formdata);
                $overrideresult->newoverride = true;
            } else {
                $formdata->id = $override->id;
                $formdata->openbook = $this->instance->id;
                $DB->update_record('openbook_overrides', $formdata);
                $overrideresult->overrideid = $override->id;
            }
        } else {
            $override = $DB->get_record(
                'openbook_overrides',
                ['openbook' => $this->instance->id,
                                            'userid' => $formdata->userid,
                                            'groupid' => $formdata->groupid]
            );
            unset($formdata->id);
            unset($formdata->overrideid);
            if (!$override) {
                $formdata->openbook = $this->instance->id;
                $overrideresult->overrideid = $DB->insert_record('openbook_overrides', $formdata);
                $overrideresult->newoverride = true;
            } else {
                $formdata->id = $override->id;
                $formdata->openbook = $this->instance->id;
                $DB->update_record('openbook_overrides', $formdata);
                $overrideresult->overrideid = $override->id;
            }
        }
        return $overrideresult;
    }

    /**
     * Get override
     *
     * @param int $overrideid
     */
    public function override_get($overrideid) {
        global $DB;
        $override = $DB->get_record(
            'openbook_overrides',
            [
                'id' => $overrideid,
                'openbook' => $this->instance->id,
            ]
        );
        if ($override) {
            return $this->override_export_for_template_single($override);
        }
        return null;
    }

    /**
     * Delete override
     *
     * @param int $overrideid
     */
    public function override_delete($overrideid) {
        global $DB;
        $override = $DB->get_record(
            'openbook_overrides',
            [
                'id' => $overrideid,
                'openbook' => $this->instance->id,
            ]
        );
        if ($override) {
            $DB->delete_records('openbook_overrides', ['id' => $overrideid]);
            return true;
        }
        return false;
    }

    /**
     * Get form_data for override
     *
     * @param int $overrideid
     */
    public function override_getformdata($overrideid) {
        global $DB;
        if ($overrideid == -1 || $overrideid == 0) {
            $formdata = new stdClass();
            $formdata->overrideid = -1;
            $formdata->id = $this->coursemodule->id;
            return $formdata;
        }
        $override = $DB->get_record(
            'openbook_overrides',
            [
                'id' => $overrideid,
                'openbook' => $this->instance->id,
            ]
        );
        if ($override) {
            $override->overrideid = $override->id;
            $override->id = $this->coursemodule->id;
        }

        return $override;
    }

    /**
     * Get current user of group
     */
    public function override_get_currentuserorgroup() {
        global $DB, $USER;
        $override = null;
        $override = $DB->get_record(
            'openbook_overrides',
            [
                'openbook' => $this->instance->id,
                'userid' => $USER->id,
            ]
        );
        if ($override) {
            return $this->override_export_for_template_single($override);
        }
        return null;
    }

    /**
     * Get current status of openpdffilesinpdfjs
     */
    public function get_openpdffilesinpdfjs_status() {
        return $this->instance->openpdffilesinpdfjs;
    }

    /**
     * Get current status of uselegacyviewer
     */
    public function get_uselegacyviewer_status() {
        return $this->instance->uselegacyviewer;
    }

    /**
     * Get current status of filesarepersonal
     */
    public function get_filesarepersonal_status() {
        return $this->instance->filesarepersonal;
    }

    /**
     * Reset all studentapproval values to 0 for all files in this openbook instance.
     *
     * @return bool Number of records updated.
     */
    public function reset_all_studentapproval() {
        global $DB, $USER;

        $sql = "SELECT fileid, userid, teacherapproval, id, studentapproval, filename
                FROM {openbook_file}
                WHERE openbook = :openbook
                AND studentapproval != 1";
        $params = ['openbook' => $this->instance->id];
        $files = $DB->get_records_sql($sql, $params);

        foreach ($files as $file) {
            $user = $DB->get_record('user', ['id' => $file->userid]);

            $dataforlog = new stdClass();
            $dataforlog->openbook = $this->instance->id;
            $dataforlog->userid = $USER->id;
            if ($user && !empty($user->id)) {
                $dataforlog->reluser = $user->id;
            } else {
                $dataforlog->reluser = 0;
            }
            $dataforlog->fileid = $file->fileid;

            try {
                \mod_openbook\event\openbook_approval_changed::approval_changed(
                    $this->coursemodule,
                    $dataforlog
                )->trigger();
            } catch (coding_exception $e) {
                throw new Exception("Coding exception while sending notification: " . $e->getMessage());
            }

            $DB->set_field(
                'openbook_file',
                'studentapproval',
                0,
                ['id' => $file->id]
            );

            if ($this->instance->notifystatuschange != 0) {
                $cm = $this->coursemodule;
                $cmid = $this->coursemodule->id;
                self::send_notification_statuschange($cm, $USER, 'automatic', $file, $cmid, $this);
            }
        }
        return true;
    }
}
