<?php
// This file is part of mod_openbook for Moodle - http://moodle.org/
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
 * Privacy class for requesting user data.
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_openbook\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadataprovider;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\plugin\provider as pluginprovider;
use core_privacy\local\request\user_preference_provider as preference_provider;
use core_privacy\local\request\writer;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\helper;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/openbook/locallib.php');

/**
 * Privacy class for requesting user data.
 */
class provider implements core_userlist_provider, metadataprovider, pluginprovider, preference_provider {
    /**
     * Provides meta data that is stored about a user with mod_openbook
     *
     * @param  collection $collection A collection of meta data items to be added to.
     * @return  collection Returns the collection of metadata.
     */
    public static function get_metadata(collection $collection): collection {
        $openbookextduedates = [
                'userid' => 'privacy:metadata:userid',
                'extensionduedate' => 'privacy:metadata:extensionduedate',
        ];
        $openbookfile = [
                'userid' => 'privacy:metadata:userid',
                'timecreated' => 'privacy:metadata:timecreated',
                'fileid' => 'privacy:metadata:fileid',
                'filename' => 'privacy:metadata:filename',
                'contenthash' => 'privacy:metadata:contenthash',
                'type' => 'privacy:metadata:type',
                'teacherapproval' => 'privacy:metadata:teacherapproval',
                'studentapproval' => 'privacy:metadata:studentapproval',
        ];
        $openbookgroupapproval = [
                'fileid' => 'privacy:metadata:fileid',
                'userid' => 'privacy:metadata:userid',
                'approval' => 'privacy:metadata:approval',
                'timemodified' => 'privacy:metadata:timemodified',
        ];

        $collection->add_database_table('openbook_file', $openbookfile, 'privacy:metadata:files');
        $collection->add_database_table(
            'openbook_groupapproval',
            $openbookgroupapproval,
            'privacy:metadata:groupapproval',
        );

        $collection->add_user_preference('openbook_perpage', 'privacy:metadata:openbookperpage');

        // Link to subplugins.
        $collection->add_subsystem_link('core_files', [], 'privacy:metadata:openbookfileexplanation');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid the userid.
     * @return contextlist the list of contexts containing user info for the user.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        // Fetch all openbook files.
        $sql = "SELECT c.id
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {openbook} ob ON ob.id = cm.instance
            INNER JOIN {openbook_file} obf ON obf.openbook = ob.id
                 WHERE obf.userid = :userid";

        $params = [
            'modname'       => 'openbook',
            'contextlevel'  => CONTEXT_MODULE,
            'userid'        => $userid,
        ];
        $contextlist->add_from_sql($sql, $params);

        // Fetch all openbook overrides.
        $sql = "SELECT c.id
                  FROM {context} c
            INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
            INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
            INNER JOIN {openbook} ob ON ob.id = cm.instance
            INNER JOIN {openbook_overrides} oo ON oo.openbook = ob.id
                 WHERE oo.userid = :userid";

        $params = [
            'modname'       => 'openbook',
            'contextlevel'  => CONTEXT_MODULE,
            'userid'        => $userid,
        ];
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $params = [
                'modulename' => 'openbook',
                'contextid' => $context->id,
                'contextlevel' => CONTEXT_MODULE,
                'upload' => OPENBOOK_MODE_UPLOAD,
        ];

        // Get all who uploaded!
        $sql = "SELECT f.userid
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {openbook} p ON p.id = cm.instance
                  JOIN {openbook_file} f ON p.id = f.openbook
                 WHERE ctx.id = :contextid AND ctx.contextlevel = :contextlevel";
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel == CONTEXT_MODULE) {
            // Apparently we can't trust anything that comes via the context.
            // Go go mega query to find out it we have an checkmark context that matches an existing checkmark.
            $sql = "SELECT p.id
                    FROM {openbook} p
                    JOIN {course_modules} cm ON p.id = cm.instance AND p.course = cm.course
                    JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                    JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :contextmodule
                    WHERE ctx.id = :contextid";
            $params = ['modulename' => 'openbook', 'contextmodule' => CONTEXT_MODULE, 'contextid' => $context->id];
            $id = $DB->get_field_sql($sql, $params);
            // If we have an id over zero then we can proceed.
            if ($id > 0) {
                $userids = $userlist->get_userids();
                if (count($userids) <= 0) {
                    return;
                }

                $fs = get_file_storage();

                [$usersql, $userparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'usr');

                // Delete users' files for this openbook!
                $files = $DB->get_records_select(
                    'openbook_file',
                    "openbook = :id AND userid " . $usersql,
                    ['id' => $id] + $userparams
                );

                if ($files) {
                    $fileids = array_keys($files);
                    foreach ($files as $cur) {
                        $file = $fs->get_file_by_id($cur->fileid);
                        $file->delete();
                    }
                    $DB->delete_records_list('openbook_file', 'id', $fileids);
                }
            }
        }
    }


    /**
     * Write out the user data filtered by contexts.
     *
     *
     * @param approved_contextlist $contextlist contexts that we are writing data out from.
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $contexts = $contextlist->get_contexts();

        if (empty($contexts)) {
            return;
        }

        [$contextsql, $contextparams] = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT
                    c.id AS contextid,
                    p.*,
                    cm.id AS cmid
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid
                  JOIN {openbook} p ON p.id = cm.instance
                 WHERE c.id {$contextsql}";

        // Keep a mapping of openbookid to contextid.
        $mappings = [];

        $openbooks = $DB->get_records_sql($sql, $contextparams);

        $user = $contextlist->get_user();

        foreach ($openbooks as $openbook) {
            $context = \context_module::instance($openbook->cmid);
            $mappings[$openbook->id] = $openbook->contextid;

            // Check that the context is a module context.
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $openbookdata = helper::get_context_data($context, $user);
            helper::export_context_files($context, $user);

            $cm = get_coursemodule_from_instance('openbook', $openbook->id);

            $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
            $openbook = new \openbook($cm, $course, $context);

            writer::with_context($context)->export_data([], $openbookdata);

            /* We don't differentiate between roles, if we have data about the user, we give it freely ;) - no sensible
             * information here! */

            static::export_user_preferences($user->id);
            static::export_extensions($context, $openbook, $user);
            static::export_files($context, $openbook, $user, []);
        }
    }

    /**
     * Stores the user preferences related to mod_openbook.
     *
     * @param  int $userid The user ID that we want the preferences for.
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public static function export_user_preferences(int $userid) {
        global $DB;
        $context = \context_system::instance();

        $sql = "SELECT name, value
                  FROM {user_preferences}
                 WHERE userid = :userid AND ";
        $namelike = $DB->sql_like('name', ':name');
        $sql .= $namelike;

        $params = ['userid' => $userid, 'name' => 'mod-openbook-perpage-%'];
        $userprefs = $DB->get_records_sql($sql, $params);
        foreach ($userprefs as $userpref) {
            writer::with_context($context)->export_user_preference(
                'mod_openbook',
                $userpref->name,
                $userpref->value,
                get_string('privacy:metadata:openbookperpage', 'mod_openbook')
            );
        }
        $params['name'] = \mod_openbook\local\allfilestable\base::get_table_uniqueid('%');
        $userprefs = $DB->get_records_sql($sql, $params);
        foreach ($userprefs as $userpref) {
            writer::with_context($context)->export_user_preference(
                'mod_openbook',
                $userpref->name,
                $userpref->value,
                get_string('privacy:metadata:openbookperpage', 'mod_openbook')
            );
        }
    }

    /**
     * Fetches all of the user's files and adds them to the export
     *
     * @param  \context_module $context
     * @param  \openbook $pub
     * @param  \stdClass $user
     * @param  array $path Current directory path that we are exporting to.
     * @throws \dml_exception
     * @throws \coding_exception
     */
    protected static function export_files(\context_module $context, \openbook $pub, \stdClass $user, array $path) {
        global $DB;

        // Imported and uploaded files are saved with user's ID!
        $rs = $DB->get_recordset_sql("SELECT f.*
              FROM {openbook} p
              JOIN {openbook_file} f ON p.id = f.openbook
             WHERE p.id = :openbook AND f.userid = :userid", [
                'openbook' => $pub->get_instance()->id,
                'userid' => $user->id,
        ]);

        foreach ($rs as $cur) {
            $filepath = array_merge($path, [get_string('privacy:path:files', 'mod_openbook'), $cur->filename]);
            static::export_file($context, $cur, $filepath);
        }
    }

    /**
     * Exports an uploaded/imported file!
     *
     * @param \context_module $context
     * @param \stdClass $file
     * @param array $path
     * @throws \coding_exception
     */
    protected static function export_file(\context_module $context, \stdClass $file, array $path) {
        // Export file!
        static $fs = null;

        if ($fs === null) {
            $fs = new \file_storage();
        }

        $fsfile = $fs->get_file_by_id($file->fileid);
        static::export_file_metadata($context, $file, $path);
        writer::with_context($context)->export_custom_file($path, $fsfile->get_filename(), $fsfile->get_content());
    }

    /**
     * Adds the metadata of an imported/uploaded file to the export!
     *
     * @param \context_module $context
     * @param \stdClass $file
     * @param array $path
     * @throws \coding_exception
     */
    protected static function export_file_metadata(\context_module $context, \stdClass $file, array $path) {
        // Export file's metadata!
        $export = (object)[
                'timecreated' => transform::datetime($file->timecreated),
                'filename' => $file->filename,
                'contenthash' => $file->contenthash,
                'teacherapproval' => transform::yesno($file->teacherapproval),
                'studentapproval' => transform::yesno($file->studentapproval),
        ];
        switch ($file->type) {
            case OPENBOOK_MODE_UPLOAD:
                $export->type = get_string('privacy:type:upload', 'openbook');
                break;
        }

        writer::with_context($context)->export_data($path, (object)$export);
    }

    /**
     * Adds an imported onlinetext and resources to export!
     *
     * @param \context_module $context
     * @param \stdClass $file
     * @param array $path
     * @throws \coding_exception
     */
    protected static function export_onlinetext(\context_module $context, \stdClass $file, array $path) {
        // Export file!
        static $fs = null;

        if ($fs === null) {
            $fs = new \file_storage();
        }

        $fsfile = $fs->get_file_by_id($file->fileid);

        static::export_file_metadata($context, $file, $path);
        writer::with_context($context)->export_custom_file($path, $fsfile->get_filename(), $fsfile->get_content());

        /*
         * Export resources!
         * We won't use writer::with_context($context)->export_area_files() due to us only needing a subdirectory!
         */
        $resources = $fs->get_directory_files(
            $context->id,
            'mod_openbook',
            'attachment',
            $fsfile->get_itemid(),
            '/resources/',
            true,
            false
        );
        if (count($resources) > 0) {
            foreach ($resources as $cur) {
                writer::with_context($context)->export_custom_file(array_merge($path, [
                        get_string('privacy:path:resources', 'mod_openbook'),
                ]), $cur->get_filename(), $cur->get_content());
            }
        }
    }

    /**
     * Fetches all of the user's group approvals and adds them to the export
     *
     * @param  \context $context
     * @param  \openbook $pub
     * @param  \stdClass $user
     * @param  array $path Current directory path that we are exporting to.
     * @throws \dml_exception
     */
    protected static function export_groupapprovals(\context $context, \openbook $pub, \stdClass $user, array $path) {
        global $DB;

        // Fetch all approvals!
        $rs = $DB->get_recordset_sql("SELECT ga.id, f.filename, ga.userid, ga.approval, ga.timecreated, ga.timemodified,
                                             f.userid AS groupid
                                        FROM {openbook_groupapproval} ga
                                        JOIN {openbook_file} f ON ga.fileid = f.id
                                       WHERE ga.userid = :userid AND f.openbook = :openbook", [
                'userid' => $user->id,
                'openbook' => $pub->get_instance()->id,
        ]);

        foreach ($rs as $cur) {
            static::export_groupapproval($context, $cur, $path);
        }

        $rs->close();
    }

    /**
     * Formats and then exports the user's approval data.
     *
     * @param  \context $context
     * @param  \stdClass $approval
     * @param  array $path Current directory path that we are exporting to.
     */
    protected static function export_groupapproval(\context $context, \stdClass $approval, array $path) {
        $approvaldata = (object)[
                'filename' => $approval->filename,
                'approval' => transform::yesno($approval->approval),
                'groupid' => $approval->groupid,
                'timecreated' => transform::datetime($approval->timecreated),
                'timemodified' => transform::datetime($approval->timemodified),
        ];

        writer::with_context($context)->export_data($path, $approvaldata);
    }

    /**
     * Delete all use data which matches the specified context.
     *
     * @param \context $context The module context.
     * @throws \dml_exception
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel == CONTEXT_MODULE) {
            $fs = new \file_storage();

            // Apparently we can't trust anything that comes via the context.
            // Go go mega query to find out it we have an assign context that matches an existing assignment.
            $sql = "SELECT p.id
                    FROM {openbook} p
                    JOIN {course_modules} cm ON p.id = cm.instance AND p.course = cm.course
                    JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                    JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :contextmodule
                    WHERE ctx.id = :contextid";
            $params = ['modulename' => 'openbook', 'contextmodule' => CONTEXT_MODULE, 'contextid' => $context->id];
            $id = $DB->get_field_sql($sql, $params);
            // If we have a count over zero then we can proceed.
            if ($id > 0) {
                // Get all openbook files and group approvals to delete them!
                if ($files = $DB->get_records('openbook_file', ['openbook' => $id])) {
                    $fileids = array_keys($files);

                    // Go through all files and delete files and resources in filespace!
                    foreach ($files as $cur) {
                        $fs->delete_area_files($context->id, 'mod_openbook', 'attachment', $cur->userid);
                    }

                    $DB->delete_records_list('openbook_file', 'id', $fileids);
                }
            }
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $user = $contextlist->get_user();
        $fs = new \file_storage();

        $contextids = $contextlist->get_contextids();

        if (empty($contextids) || $contextids === []) {
            return;
        }

        [$ctxsql, $ctxparams] = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED, 'ctx');

        // Apparently we can't trust anything that comes via the context.
        // Go go mega query to find out it we have an assign context that matches an existing assignment.
        $sql = "SELECT ctx.id AS ctxid, p.*
                    FROM {openbook} p
                    JOIN {course_modules} cm ON p.id = cm.instance AND p.course = cm.course
                    JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                    JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :contextmodule
                    WHERE ctx.id " . $ctxsql;
        $params = ['modulename' => 'openbook', 'contextmodule' => CONTEXT_MODULE];

        if (!$records = $DB->get_records_sql($sql, $params + $ctxparams)) {
            return;
        }

        foreach ($contextlist as $context) {
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $pub = $records[$context->id];

            $teams = false;
            $emptygroup = false;

            if ($emptygroup) {
                $files = $DB->get_records('openbook_file', ['openbook' => $pub->id, 'userid' => 0]);
            } else if (!$teams) {
                $files = $DB->get_records('openbook_file', ['openbook' => $pub->id, 'userid' => $user->id]);
            } else {
                $files = [];

                $usergroups = groups_get_all_groups($pub->course, $user->id);
                foreach (array_keys($usergroups) as $grpid) {
                    $files = $files + $DB->get_records('openbook_file', ['openbook' => $pub->id,
                            'userid' => $grpid]);
                }
            }

            if ($files) {
                $fileids = array_keys($files);

                // Go through all files and delete files and resources in filespace!
                foreach ($files as $cur) {
                    if (!$teams) {
                        $fs->delete_area_files($context->id, 'mod_openbook', 'attachment', $cur->userid);
                    } else {
                        groups_remove_member($cur->userid, $user->id);
                    }
                }
                if (!$teams) {
                    $DB->delete_records_list('openbook_file', 'id', $fileids);
                }
            }
        }
    }
}
