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
 * Contains class for files table listing all files
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_openbook\local\allfilestable;

/**
 * Table showing my group files
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group extends base {
    /** @var int grouping id for files */
    protected $groupingid = 0;
    /** @var bool if a group membership is required the openbook */
    protected $requiregroup = 0;

    /**
     * Sets the predefined SQL for this table
     */
    protected function init_sql() {
        global $DB;

        $params = [];

        $fields = "g.id, g.name AS groupname, NULL AS groupmembers, COUNT(*) AS filecount,
                   SUM(files.studentapproval) AS studentapproval, SUM(files.teacherapproval) AS teacherapproval, " .
                  "MAX(files.timecreated) AS timemodified ";

        $groups = $this->openbook->get_groups($this->groupingid);
        if (count($groups) > 0) {
            [$sqlgroupids, $groupparams] = $DB->get_in_or_equal($groups, SQL_PARAMS_NAMED, 'group');
            $params = $params + $groupparams + ['openbook' => $this->cm->instance];
        } else {
            $sqlgroupids = " = :group ";
            $params = $params + ['group' => -1, 'openbook' => $this->cm->instance];
        }

        if ($this->requiregroup || !count($this->openbook->get_submissionmembers(0))) {
            $grouptable = '{groups} g ';
        } else {
            $grouptable = " ( SELECT 0 AS id, :stdname AS name
                           UNION ALL
                              SELECT {groups}.id, {groups}.name AS name
                                FROM {groups}) AS g ";
            $params['stdname'] = get_string('defaultteam', 'assign');
        }

        $having = '';
        if ($this->filter == OPENBOOK_FILTER_NOFILTER) {
            $from = $grouptable . " LEFT JOIN {openbook_file} files ON g.id = files.userid AND " .
                "files.openbook = :openbook ";
        } else if ($this->filter == OPENBOOK_FILTER_ALLFILES) {
            $from = $grouptable . " JOIN {openbook_file} files ON g.id = files.userid AND " .
                "files.openbook = :openbook ";
        } else if ($this->filter == OPENBOOK_FILTER_APPROVED) {
            $from = $grouptable . " JOIN {openbook_file} files ON g.id = files.userid AND " .
                "files.openbook = :openbook ";
            if ($this->obtainteacherapproval) {
                $from .= ' AND files.teacherapproval = 1 ';
            }
            if ($this->obtainstudentapproval) {
                $from .= ' AND files.studentapproval = 1 ';
            }
        } else if ($this->filter == OPENBOOK_FILTER_REJECTED) {
            $from = $grouptable . " LEFT JOIN {openbook_file} files ON g.id = files.userid AND " .
                "files.openbook = :openbook " .
                "AND files.teacherapproval = 2 ";
        } else if ($this->filter == OPENBOOK_FILTER_APPROVALREQUIRED) {
            $from = $grouptable . " LEFT JOIN {openbook_file} files ON g.id = files.userid AND " .
                "files.openbook = :openbook " .
                "AND (files.teacherapproval = 3 OR files.teacherapproval IS NULL OR files.teacherapproval = 0) ";
        } else if ($this->filter == OPENBOOK_FILTER_NOFILES) {
            $from = $grouptable . " LEFT JOIN {openbook_file} files ON g.id = files.userid AND " .
                "files.openbook = :openbook ";
            $having = ' HAVING timemodified IS NULL ';
        }

        $where = "g.id " . $sqlgroupids;
        $groupby = " g.id, g.name " . $having;

        $this->set_sql($fields, $from, $where, $params, $groupby);

        if ($this->filter != OPENBOOK_FILTER_NOFILES) {
            $this->set_count_sql("SELECT COUNT(a.gid) FROM (SELECT DISTINCT g.id AS gid  FROM " . $from .
                " WHERE " . $where . ') a', $params);
        } else {
            $this->set_count_sql("SELECT
            COUNT(a.gid) FROM
            (SELECT g.id AS gid, MAX(files.timecreated) AS timemodified FROM $from WHERE $where GROUP BY g.id) a
                         WHERE a.timemodified IS NULL", $params);
        }
    }

    /**
     * Constructor function
     *
     * @param string $uniqueid a string identifying this table. Used as a key in session vars.
     * It gets set automatically with the helper methods!
     * @param \openbook $openbook openbook object
     * @param string $filter filters
     */
    public function __construct($uniqueid, \openbook $openbook, $filter) {
        global $DB, $PAGE;

        $this->groupingid = $DB->get_field('assign', 'teamsubmissiongroupingid', ['id' => $assignid]);
        $this->requiregroup = $openbook->requiregroup();

        parent::__construct($uniqueid, $openbook, $filter);

        $this->sortable(true, 'groupname'); // Sorted by group by default.
        $this->no_sorting('groupmembers');

        $params = new \stdClass();
        $cm = get_coursemodule_from_instance('openbook', $openbook->get_instance()->id);
        $params->cmid = $cm->id;
        $PAGE->requires->js_call_amd('mod_openbook/onlinetextpreview', 'initializer', [$params]);
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
        $selectallnone = \html_writer::checkbox('selectallnone', false, false, '', [
                'id' => 'selectallnone',
                'onClick' => 'toggle_userselection()',
        ]);

        $columns = ['selection', 'groupname', 'groupmembers', 'timemodified', 'files'];
        $headers = [
            $selectallnone,
            get_string('group'),
            get_string('groupmembers'),
            get_string('lastmodified'),
            get_string('files'),
        ];
        $helpicons = [null, null, null, null, null];

        if (has_capability('mod/openbook:approve', $this->context) && $this->allfilespage) {
            if ($this->openbook->get_instance()->obtainstudentapproval) {
                $columns[] = 'studentapproval';
                $headers[] = get_string('studentapproval', 'openbook');
                $helpicons[] = new \help_icon('studentapproval', 'openbook');
            }

            // phpcs:disable Squiz.PHP.CommentedOutCode
            /*
            $columns[] = 'teacherapproval';
            if ($this->openbook->get_instance()->obtainstudentapproval) {
                $headers[] = get_string('obtainstudentapproval', 'openbook');
            } else {
                $headers[] = get_string('teacherapproval', 'openbook');
            }
            $helpicons[] = new \help_icon('teacherapproval', 'openbook');

            $columns[] = 'visibleforstudents';
            $headers[] = get_string('visibleforstudents', 'openbook');
            $helpicons[] = null;*/

            $columns[] = 'openbookstatus';
            $headers[] = get_string('openbookstatus', 'openbook');
            $helpicons[] = new \help_icon('openbookstatus', 'openbook');
        }

        return [$columns, $headers, $helpicons];
    }

    /**
     * Display members of the group
     *
     * @param object $values Contains object with all the values of record.
     * @return string Return groups members.
     */
    public function col_groupmembers($values) {
        $cell = '';

        $groupmembers = $this->openbook->get_submissionmembers($values->id);

        if (!count($groupmembers)) {
            return $cell;
        }

        foreach ($groupmembers as $cur) {
            $cell .= \html_writer::tag('div', parent::col_fullname($cur));
        }

        return $cell;
    }

    /**
     * Method wraps string with span-element including data attributes containing detailed group approval data!
     *
     * @param string $symbol string/html-snippet to wrap element around
     * @param \stored_file $file file to fetch details for
     */
    protected function add_details_tooltip(&$symbol, \stored_file $file) {
        global $DB, $OUTPUT;

        $pubfileid = $DB->get_field('openbook_file', 'id', [
                'openbook' => $this->openbook->get_instance()->id,
                'fileid' => $file->get_id(),
        ]);
        [, $approvaldetails] = $this->openbook->group_approval($pubfileid);

        $approved = [];
        $rejected = [];
        $pending = [];
        foreach ($approvaldetails as $cur) {
            if (empty($cur->approvaltime)) {
                $cur->approvaltime = '-';
            } else {
                $cur->approvaltime = userdate($cur->approvaltime, get_string('strftimedatetime'));
            }
            if ($cur->approval === null) {
                $pending[] = ['name' => fullname($cur), 'time' => '-'];
                ;
            } else if ($cur->approval == 0) {
                $rejected[] = ['name' => fullname($cur), 'time' => $cur->approvaltime];
            } else if ($cur->approval == 1) {
                $approved[] = ['name' => fullname($cur), 'time' => $cur->approvaltime];
            }
        }

        $status = new \stdClass();
        $status->approved = false;
        $status->rejected = false;
        $status->pending = false;
        switch ($this->openbook->student_approval($file)) {
            case 1:
                $status->approved = true;
                break;
            case 2:
                $status->rejected = true;
                break;
            default:
                $status->pending = true;
        }

        $detailsattr = [
                'class' => 'approvaldetails',
                'data-pending' => json_encode($pending),
                'data-approved' => json_encode($approved),
                'data-rejected' => json_encode($rejected),
                'data-filename' => '<span style="word-breaK: break-all;">' . $file->get_filename() . '</span>',
                'data-status' => json_encode($status),
        ];

        $symbol = $symbol . \html_writer::tag(
            'span',
            $OUTPUT->pix_icon('i/preview', get_string('show_details', 'openbook')),
            $detailsattr
        );
    }

    /**
     * Caches and returns itemnames for given itemids
     *
     * @param int $itemid
     * @return string Itemname
     */
    protected function get_itemname($itemid) {
        if (!array_key_exists($itemid, $this->itemnames)) {
            $this->itemnames[$itemid] = groups_get_group_name($itemid);
        }

        return $this->itemnames[$itemid];
    }
}
