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
 * Base class for classes listing all files depending on groups and group mode
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_openbook\local\teacherfilestable;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/openbook/locallib.php');
require_once($CFG->libdir . '/tablelib.php');

/**
 * Base class for tables showing all (public) files
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team *
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class base extends \table_sql {
    /** @var \openbook openbook object */
    protected $openbook = null;
    /** @var \context_module context instance object */
    protected $context;
    /** @var \stdClass coursemodule object */
    protected $cm = null;
    /** @var \file_storage file storage */
    protected $fs = null;
    /** @var \stored_file[] files */
    protected $files = null;
    /** @var \stored_file[] resource-files */
    protected $resources = null;
    /** @var int current itemid for files array */
    protected $itemid = null;
    /** @var int amount of files in table, get's counted during formating of the rows! */
    protected $totalfiles = null;
    /** @var string[] of cached itemnames */
    protected $itemnames = [];
    /** @var int activity's groupmode */
    protected $groupmode = 0;
    /** @var int current group if group mode is active */
    protected $currentgroup = 0;
    /** @var int grouping id for files */
    protected $groupingid = 0;
    /** @var string valid pix-icon */
    protected $valid = '';
    /** @var string questionmark pix-icon */
    protected $questionmark = '';
    /** @var string invalid pix-icon */
    protected $invalid = '';
    /** @var string student visible pix-icon */
    protected $studvisibleyes = '';
    /** @var string student not visible pix-icon */
    protected $studvisibleno = '';
    /** @var string[] select box options */
    protected $options = [];
    /** @var int[] $users */
    protected $users = [];
    /** @var $filter */
    protected $filter = OPENBOOK_FILTER_NOFILTER;
    /** @var bool $allfilespage */
    protected $allfilespage = false;
    /** @var int $obtainteacherapproval */
    protected $obtainteacherapproval;
    /** @var int $obtainstudentapproval */
    protected $obtainstudentapproval;
    /** @var int $filesarepersonal */
    protected $filesarepersonal;
    /** @var int $totalfilescount */
    protected $totalfilescount = 0;

    /**
     * constructor
     *
     * @param string $uniqueid a string identifying this table.Used as a key in session vars.
     *                         It gets set automatically with the helper methods!
     * @param \openbook $openbook openbook object
     * @param string $filter
     */
    public function __construct($uniqueid, \openbook $openbook, $filter) {
        global $CFG, $OUTPUT;

        $this->allfilespage = $openbook->get_allfilespage();
        parent::__construct($uniqueid);

        $this->fs = get_file_storage();
        $this->openbook = $openbook;
        $instance = $openbook->get_instance();

        $this->obtainteacherapproval = $instance->obtainteacherapproval;
        $this->obtainstudentapproval = $instance->obtainstudentapproval;

        $this->cm = get_coursemodule_from_instance(
            'openbook',
            $openbook->get_instance()->id,
            0,
            false,
            MUST_EXIST
        );
        $this->context = \context_module::instance($this->cm->id);
        $this->groupmode = groups_get_activity_groupmode($this->cm);
        $this->currentgroup = groups_get_activity_group($this->cm, true);
        if (!$this->allfilespage) {
            $this->filter = OPENBOOK_FILTER_APPROVED;
        } else {
            $this->filter = $filter;
        }

        [$columns, $headers, $helpicons] = $this->get_columns();
        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->define_help_for_headers($helpicons);

        $this->define_baseurl($CFG->wwwroot . '/mod/openbook/view.php?id=' . $this->cm->id . '&amp;currentgroup=' .
                $this->currentgroup . '&amp;filter=' . $this->filter . '&amp;allfilespage=' . intval($this->allfilespage));

        $this->collapsible(false);
        $this->initialbars(true);

        $this->set_attribute('cellspacing', '0');
        $this->set_attribute('id', 'attempts');
        $this->set_attribute('class', 'openbooks');
        $this->set_attribute('width', '100%');

        $this->no_sorting('filename');
        $this->no_sorting('visibleforstudents');

        $this->init_sql();

        // Save status of table(s) persistent as user preference!
        $this->is_persistent(false);

        $this->valid = self::approval_icon(
            'check',
            'text-success',
            get_string('student_approved', 'openbook')
        );
        $this->questionmark = self::approval_icon(
            'question',
            'text-warning',
            get_string('student_pending', 'openbook')
        );
        $this->invalid = self::approval_icon(
            'times',
            'text-danger',
            get_string('student_rejected', 'openbook')
        );

        $this->studvisibleyes = self::approval_icon(
            'check',
            'text-success',
            get_string('visibleforstudents_yes', 'openbook')
        );
        $this->studvisibleno = self::approval_icon(
            'times',
            'text-danger',
            get_string('visibleforstudents_no', 'openbook')
        );

        $this->options = [];
        $this->options[1] = get_string('teacher_approve', 'openbook');
        $this->options[2] = get_string('teacher_reject', 'openbook');
    }

    /**
     * This function is not part of the public api.
     */
    public function print_nothing_to_display() {
        global $OUTPUT;

        // Render button to allow user to reset table preferences.
        echo $this->render_reset_button();

        $this->print_initials_bar();

        echo $OUTPUT->box(get_string('nofilestodisplay', 'openbook'), 'fst-italic');
    }

    /**
     * Return all columns, column-headers and helpicons for this table
     *
     * @return array Array with column names, column headers and help icons
     */
    protected function get_columns() {

        $columns[] = 'filename';
        $headers[] = get_string('files');
        $helpicons[] = null;

        // Upload tables will enhance this list!
        return [$columns, $headers, $helpicons];
    }

    /**
     * Setter for users property
     *
     * @param int[] $users
     */
    protected function set_users($users) {
        $this->users = $users;
    }

    /**
     * Get count
     */
    public function get_count() {
        global $DB;

        $filearea = 'commonteacherfiles'; // Le nom confirmé par votre debug (ou 'teacherfiles' selon votre code)
        $component = 'mod_openbook';

        $sql = "SELECT COUNT(id)
                  FROM {files}
                 WHERE contextid = :contextid
                   AND component = :component
                   AND filearea = :filearea
                   AND filename != :dirname
                   AND filesize > 0";

        $params = [
            'contextid' => $this->context->id,
            'component' => $component,
            'filearea'  => $filearea,
            'dirname'   => '.'
        ];

        return $DB->count_records_sql($sql, $params);
    }

    /**
     * Sets the predefined SQL for this table
     */
    protected function init_sql() {
        global $DB, $USER;

        $params = [];

        $groups = $this->openbook->get_groups($this->groupingid);

        if (count($groups) > 0) {
            [$sqlgroupids, $groupparams] = $DB->get_in_or_equal($groups, SQL_PARAMS_NAMED, 'group');
            $params = $params + $groupparams + ['openbook' => $this->cm->instance];
        } else {
            $sqlgroupids = " = :group ";
            $params = $params + ['group' => -1, 'openbook' => $this->cm->instance];
        }

        $fields = 'files.id AS id, ' .
          'files.itemid, ' .
          'files.filename, ' .
          'files.timecreated, ' .
          'files.mimetype, ' .
          'files.userid';

        $from = '{files} files';

        $params = [
            'contextid' => $this->context->id,
            'component' => 'mod_openbook',
            'filearea'  => 'commonteacherfiles',
            'dot'       => '.',
        ];

        $where = "files.contextid = :contextid " .
         "AND files.component = :component " .
         "AND files.filearea = :filearea " .
         "AND files.filename != :dot";

        $canviewallgroups = has_capability('moodle/site:accessallgroups', $this->openbook->get_context());

        if ($this->groupmode == 1) {
            if ($canviewallgroups) {
                $where .= "";
            } else {
                $allmembers = [];
                // All other group member's user ids that are in the same group(s)
                // as the current user in this course.
                $currentusergroups = groups_get_user_groups($this->openbook->get_instance()->course, $USER->id);
                foreach ($currentusergroups as $coursegroup) {
                    foreach ($coursegroup as $groupid) {
                        $groupusers = groups_get_members($groupid, 'u.id');
                        // Merge into the main array.
                        $allmembers = array_merge($allmembers, $groupusers);
                    }
                }

                if (!empty($allmembers)) {
                    [$insql, $insqlparams] = $DB->get_in_or_equal(array_column($allmembers, 'id'), SQL_PARAMS_NAMED, 'user');
                    $where .= " AND files.itemid " . $insql;
                    $params = array_merge($params, $insqlparams);
                }
            }
        }

        $groupby = "";

        $this->set_sql($fields, $from, $where, $params, $groupby);
        $this->set_count_sql("SELECT COUNT(files.id) FROM $from WHERE $where", $params);
    }

    /**
     * Set the sql to query the db. Query will be : SELECT $fields FROM $from WHERE $where
     * Of course you can use sub-queries, JOINS etc. by putting them in the appropriate clause of the query.
     *
     * @param string $fields Fields
     * @param string $from From
     * @param string $where Where
     * @param array|null $params Optional Parameters
     * @param string $groupby Optional GroupBy
     */
    public function set_sql($fields, $from, $where, ?array $params = null, string $groupby = '') {
        parent::set_sql($fields, $from, $where, $params);
        $this->sql->groupby = $groupby;
    }

    /**
     * Query the db. Store results in the table object for use by build_table. We had to override, due to group by clause!
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar. The bar
     * will only be used if there is a fullname column defined for the table.
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;
        if (!$this->is_downloading()) {
            if ($this->countsql === null) {
                $this->countsql = 'SELECT COUNT(1) FROM ' . $this->sql->from . ' WHERE ' . $this->sql->where;
                $this->countparams = $this->sql->params;
            }
            $grandtotal = $DB->count_records_sql($this->countsql, $this->countparams);
            if (
                $useinitialsbar && !$this->is_downloading() &&
                empty($this->get_initial_first()) &&
                empty($this->get_initial_last())
            ) {
                $this->initialbars($grandtotal > $pagesize);
            }

            [$wsql, $wparams] = $this->get_sql_where();
            if ($wsql) {
                if (strrpos($this->countsql, ') a') == (strlen($this->countsql) - 3)) {
                    $this->countsql = substr($this->countsql, 0, -3) .  ' AND ' . $wsql . ') a';
                } else if (strpos($this->countsql, 'GROUP BY u.id) a' !== false)) {
                    $this->countsql = str_replace('GROUP BY u.id) a', ' AND ' . $wsql . ' GROUP BY u.id) a', $this->countsql);
                } else {
                    $this->countsql .= ' AND ' . $wsql;
                }
                $this->countparams = array_merge($this->countparams, $wparams);

                $this->sql->where .= ' AND ' . $wsql;
                $this->sql->params = array_merge($this->sql->params, $wparams);

                $total = $DB->count_records_sql($this->countsql, $this->countparams);
            } else {
                $total = $grandtotal;
            }

            $this->pagesize($pagesize, $total);
        }

        // Fetch the attempts!
        $sort = $this->get_sql_sort();
        $sort = preg_replace('/(?<=\W)?(email)(?=\W)/', 'u.\1', $sort);
        if ($sort) {
            $sort = "ORDER BY $sort";
        }
        $sql = "SELECT DISTINCT {$this->sql->fields}
                  FROM {$this->sql->from}
                 WHERE {$this->sql->where}
               " . ($this->sql->groupby ? "GROUP BY {$this->sql->groupby}" : "") . "
               {$sort}";

        if (!$this->is_downloading()) {
            $this->rawdata = $DB->get_records_sql($sql, $this->sql->params, $this->get_page_start(), $this->get_page_size());
        } else {
            $this->rawdata = $DB->get_records_sql($sql, $this->sql->params);
        }
    }

    /**
     * Returns all files to be displayed for this itemid (=userid or groupid)
     *
     * @param int $itemid User or group ID to fetch files for
     * @return array Array with itemid, files-array and resources-array as items
     */
    public function get_files($itemid) {
        global $DB, $USER;

        if (($itemid === $this->itemid) && (($this->files !== null) || ($this->resources !== null))) {
            // We cache just the current files, to use less memory!
            return [$this->itemid, $this->files, $this->resources];
        }

        $contextid = $this->openbook->get_context()->id;
        $filearea = 'commonteacherfiles';

        $this->files = [];
        $this->resources = [];
        $this->itemid = $itemid;

        $files = $this->fs->get_area_files($contextid, 'mod_openbook', $filearea, $this->itemid, 'timemodified', false);

        foreach ($files as $file) {
            if ($file->get_filepath() == '/resources/') {
                $this->resources[] = $file;
            } else {
                $this->files[] = $file;
            }
            $this->totalfilescount++;
        }

        return [$this->itemid, $this->files, $this->resources];
    }

    /**
     * Returns the amount of files displayed in this table!
     */
    public function totalfiles() {
        if ($this->totalfiles !== null) {
            return $this->totalfiles;
        } else {
            return 0;
        }
    }

    /**
     * Returns the count of files displayed in this table!
     */
    public function get_totalfilescount() {
        return $this->totalfilescount;
    }

    /**
     * Method wraps string with span-element including data attributes containing detailed group approval data!
     * Is implemented/overwritten where needed!
     *
     * @param string $symbol string/html-snippet to wrap element around
     * @param \stored_file $file file to fetch details for
     */
    protected function add_details_tooltip(&$symbol, \stored_file $file) {
        // This method does nothing here!
    }

    /**
     * Caches and returns itemnames for given itemids
     *
     * @param int $itemid
     * @return string Itemname
     */
    protected function get_itemname($itemid) {
        global $DB;

        if (!array_key_exists($itemid, $this->itemnames)) {
            $user = $DB->get_record('user', ['id' => $itemid]);
            $this->itemnames[$itemid] = fullname($user);
        }

        return $this->itemnames[$itemid];
    }

    /**
     * This function is called for each data row to allow processing of the
     * XXX value.
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return XXX.
     */
    public function col_selection($values) {
        // If the data is being downloaded than we don't want to show HTML.
        if ($this->is_downloading()) {
            return '';
        } else {
            return \html_writer::checkbox(
                'selecteduser[' . $values->id . ']',
                'selected',
                false,
                null,
                ['class' => 'userselection']
            );
        }
    }

    /**
     * This function is called for each data row to allow processing of the
     * user's name with link and optional extension date.
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return user fullname.
     */
    public function col_fullname($values) {
        global $OUTPUT;
        // Saves DB access in \mod_openbook\local\allfilestable::get_itemname()!
        if (!array_key_exists($values->id, $this->itemnames)) {
            $this->itemnames[$values->id] = fullname($values);
        }

        if ($this->is_downloading()) {
            return strip_tags(parent::col_fullname($values));
        } else {
            // If in secure layout, show only name without picture and link.
            if ($this->openbook->is_securewindow_enforced()) {
                return strip_tags(parent::col_fullname($values));
            } else {
                return  $OUTPUT->user_picture($values) .  parent::col_fullname($values);
            }
        }
    }


    /**
     * This function is called for each data row to allow processing of the
     * group. Also caches group name in itemnames for onlinetext-preview!
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return group's name.
     */
    public function col_groupname($values) {
        // Saves DB access in \mod_openbook\local\allfilestable::get_itemname()!
        if (!array_key_exists($values->id, $this->itemnames)) {
            $this->itemnames[$values->id] = $values->groupname;
        }

        return $values->groupname;
    }


    /**
     * This function is called for each data row to allow processing of the
     * user's groups.
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return user groups.
     */
    public function col_groups($values) {
        $groups = groups_get_all_groups($this->openbook->get_instance()->course, $values->id, 0, 'g.name');
        if (!empty($groups)) {
            $values->groups = '';
            foreach ($groups as $group) {
                if ($values->groups != '') {
                    $values->groups .= ', ';
                }
                $values->groups .= $group->name;
            }
            if ($this->is_downloading()) {
                return $values->groups;
            } else {
                return \html_writer::tag('div', $values->groups, ['id' => 'gr' . $values->id]);
            }
        } else if ($this->is_downloading()) {
            return '';
        } else {
            return \html_writer::tag('div', '-', ['id' => 'gr' . $values->id]);
        }
    }

    /**
     * Displays the file name as a link (PDF.js or download).
     *
     * @param object $values Contains the SQL line (with id, filename, timecreated, etc.).
     * @return string
     */
    public function col_filename($values) {
        global $OUTPUT;

        if (empty($values->filename) || empty($values->id)) {
            return '-';
        }

        $contextid = $this->context->id;
        $filearea = 'commonteacherfiles';
        $itemid = $this->cm->instance;
        $mycmid = $this->cm->id;

        $url = new \moodle_url('/mod/openbook/view.php', ['id' => $mycmid, 'download' => $values->id]);

        if (
            $this->openbook->get_openpdffilesinpdfjs_status() == "1" &&
            $values->filename &&
            pathinfo($values->filename, PATHINFO_EXTENSION) == 'pdf'
        ) {
            $pdfviewer = ($this->openbook->get_uselegacyviewer_status() == "1")
                ? 'pdfjs-5.4.394-legacy-dist'
                : 'pdfjs-5.4.394-dist';

            $pluginfilerawurl = \moodle_url::make_pluginfile_url(
                $contextid,
                'mod_openbook',
                $filearea,
                $values->itemid,
                '/',
                $values->filename,
                true
            );

            $pdfjsurl = new \moodle_url('/mod/openbook/' . $pdfviewer . '/web/viewer.html', [
                'file' => $pluginfilerawurl->out(false),
            ]);

            $url = $pdfjsurl;
        }

        $displayname = $values->filename;
        $maxlen = 65;
        if (strlen($displayname) > $maxlen) {
            $displayname = \core_text::substr($displayname, 0, $maxlen - 3) . '...';
        }

        return \html_writer::link(
            $url,
            $displayname,
            ['target' => '_blank', 'rel' => 'noopener noreferrer', 'title' => $values->filename]
        );
    }

    /**
     * This function is called for each data row to allow processing of the
     * file status.
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return user time of submission.
     */
    public function col_studentapproval($values) {
        [, $files, ] = $this->get_files($values->id);

        $table = new \html_table();
        $table->attributes = ['class' => 'statustable table-reboot'];

        foreach ($files as $file) {
            if (
                has_capability('mod/openbook:approve', $this->context)
                    || $this->openbook->has_filepermission($file->get_id())
            ) {
                switch ($this->openbook->student_approval($file)) {
                    case 1:
                        $symbol = $this->valid;
                        break;
                    case 2:
                        $symbol = $this->invalid;
                        break;
                    default:
                        $symbol = $this->questionmark;
                }
                $this->add_details_tooltip($symbol, $file);
                $table->data[] = [$symbol];
            }
        }

        if (count($table->data) > 0) {
            return \html_writer::table($table);
        } else {
            return '';
        }
    }

    /**
     * This function is called for each data row to allow processing of the
     * file permission.
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return user time of submission.
     */
    public function col_teacherapproval($values) {

        [, $files, ] = $this->get_files($values->id);

        $table = new \html_table();
        $table->attributes = ['class' => 'permissionstable'];

        foreach ($files as $file) {
            if (
                $this->openbook->has_filepermission($file->get_id())
                    || has_capability('mod/openbook:approve', $this->context)
            ) {
                $checked = $this->openbook->teacher_approval($file);
                // Null if none found, DB-entry otherwise!
                // phpcs:disable moodle.Commenting.TodoComment
                // TODO change that conversions and queue the real values! Everywhere!
                $checked = ($checked === false || $checked === null) ? "" : $checked;

                $sel = \html_writer::select($this->options, 'files[' . $file->get_id() . ']', (string)$checked);
                $table->data[] = [$sel];
            }
        }

        if (count($table->data) > 0) {
            return \html_writer::table($table);
        } else {
            return '';
        }
    }

    /**
     * This function is called for each data row to allow processing of the
     * file visibility.
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return user time of submission.
     */
    public function col_visibleforstudents($values) {
        [, $files, ] = $this->get_files($values->id);

        $table = new \html_table();
        $table->attributes = ['class' => 'statustable table-reboot'];

        foreach ($files as $file) {
            if ($this->openbook->has_filepermission($file->get_id())) {
                $table->data[] = [$this->studvisibleyes];
            } else {
                $table->data[] = [$this->studvisibleno];
            }
        }

        // phpcs:disable moodle.Commenting.TodoComment
        // TODO: download without tags?
        if (count($table->data) > 0) {
            return \html_writer::table($table);
        } else {
            return '';
        }
    }

    /**
     * This function is used for generating an HTML table with files
     *
     * @param mixed $values
     */
    public function col_openbookstatus($values) {

        [, $files, ] = $this->get_files($values->id);

        $table = new \html_table();
        $table->attributes = ['class' => 'statustable table-reboot'];

        foreach ($files as $file) {
            $row = [];

            // Student approval!

            // phpcs:disable Squiz.PHP.CommentedOutCode
            /*
            if (!($this instanceof \mod_openbook\local\allfilestable\upload)) {
                if (has_capability('mod/openbook:approve', $this->context)
                    || $this->openbook->has_filepermission($file->get_id())) {
                    switch ($this->openbook->student_approval($file)) {
                        case 2:
                            $symbol = $this->valid;
                            break;
                        case 1:
                            $symbol = $this->invalid;
                            break;
                        default:
                            $symbol = $this->questionmark;
                    }
                    $this->add_details_tooltip($symbol, $file);
                    $row[] = $symbol;

                }
            }
            */

            // Teacher approval!

            if (
                $this->obtainteacherapproval && ($this->openbook->has_filepermission($file->get_id())
                || has_capability('mod/openbook:approve', $this->context))
            ) {
                $checked = $this->openbook->teacher_approval($file);
                // Null if none found, DB-entry otherwise!
                // phpcs:disable moodle.Commenting.TodoComment
                // TODO change that conversions and queue the real values! Everywhere!
                $checked = ($checked === false || $checked === null) ? "" : $checked;

                $sel = \html_writer::select($this->options, 'files[' . $file->get_id() . ']', (string)$checked);
                $row[] = $sel;
            }

            // Visible for students.

            if ($this->openbook->has_filepermission($file->get_id())) {
                $row[] = $this->studvisibleyes;
            } else {
                $row[] = $this->studvisibleno;
            }
            $table->data[] = $row;
        }

        // phpcs:disable moodle.Commenting.TodoComment
        // TODO: download without tags?
        if (count($table->data) > 0) {
            return \html_writer::table($table);
        } else {
            return '';
        }
    }

    /**
     * This function is called for each data row to allow processing of
     * columns which do not have a *_cols function.
     *
     * @param string $colname Name of current column
     * @param object $values Values of the current row
     * @return string return processed value.
     */
    public function other_cols($colname, $values) {
        // Process user identity fields!
        $fields = \core_user\fields::for_identity($this->context, false);
        $useridentity = $fields->get_required_fields();
        if ($colname === 'phone') {
            $colname = 'phone1';
        }
        if (in_array($colname, $useridentity)) {
            if (!empty($values->$colname)) {
                if ($this->is_downloading()) {
                    return $values->$colname;
                } else {
                    return \html_writer::tag('div', $values->$colname, ['id' => 'u' . $colname . $values->id]);
                }
            } else {
                if ($this->is_downloading()) {
                    return '-';
                } else {
                    return \html_writer::tag('div', '-', ['id' => 'u' . $colname . $values->id]);
                }
            }
        }
        return $values->$colname;
    }

    /**
     * This function is called for each data row to allow processing of
     * columns which do not have a *_cols function.
     *
     * @param string $fontawesomeicon
     * @param string $bootsrapcolor
     * @param string $title
     */
    public static function approval_icon($fontawesomeicon, $bootsrapcolor, $title) {
        global $OUTPUT;
        $templatecontext = [
            'fontawesomeicon' => $fontawesomeicon,
            'bootsrapcolor' => $bootsrapcolor,
            'title' => $title,
        ];
        return $OUTPUT->render_from_template('mod_openbook/approval_icon_fontawesome', $templatecontext);
    }

    /**
     * Gets a table uniqueid
     *
     * @param string $instanceid
     */
    public static function get_table_uniqueid($instanceid) {
        return 'mod-openbook-teacherfiles-' . $instanceid;
    }
}
