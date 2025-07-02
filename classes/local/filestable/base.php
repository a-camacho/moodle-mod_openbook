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
 * Base class for tables showing files related to me (uploaded by me, imported from me or my group and options to approve them)
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_privatestudentfolder\local\filestable;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/privatestudentfolder/locallib.php');
require_once($CFG->libdir . '/tablelib.php');

/**
 * Base class for tables showing my files or group files (upload or import)
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class base extends \html_table {
    /** @var \privatestudentfolder privatestudentfolder object */
    protected $privatestudentfolder = null;
    /** @var \file_storage file storage object */
    protected $fs = null;
    /** @var \stored_file[] array of stored_file objects */
    protected $files = null;
    /** @var \stored_file[] array of stored_file objects used in onlinetexts */
    protected $resources = null;
    /** @var bool whether or not changes of approval are still possible */
    protected $changepossible = false;
    /** @var string[] select options */
    protected $options = [];
    /** @var string valid icon string */
    protected $valid = '';
    /** @var string questionmark icon string */
    protected $questionmark = '';
    /** @var string invalid icon string */
    protected $invalid = '';
    /** @var string share icon string */
    protected $share = '';
    /** @var int timestamp with modification time */
    public $lastmodified = 0;

    /**
     * constructor
     *
     * @param \privatestudentfolder $privatestudentfolder privatestudentfolder object
     */
    public function __construct(\privatestudentfolder $privatestudentfolder) {
        global $OUTPUT;

        parent::__construct();

        $this->privatestudentfolder = $privatestudentfolder;

        $this->fs = get_file_storage();

        $this->valid = \mod_privatestudentfolder\local\allfilestable\base::approval_icon('check', 'text-success', false);
        $this->questionmark = \mod_privatestudentfolder\local\allfilestable\base::approval_icon('question', 'text-warning', false);
        $this->invalid = \mod_privatestudentfolder\local\allfilestable\base::approval_icon('times', 'text-danger', false);
        $this->share = \mod_privatestudentfolder\local\allfilestable\base::approval_icon('share-from-square', 'text-success', false);
    }

    /**
     * Initialize the table (get the files, style table, prepare options used for approval-selects, add files to table, etc.)
     *
     * @return int amount of files in table
     */
    public function init() {
        $files = $this->get_files();

        if ((!$files || count($files) == 0) && has_capability('mod/privatestudentfolder:upload', $this->privatestudentfolder->get_context())) {
            return 0;
        }

        if (!isset($this->attributes)) {
            $this->attributes = ['class' => 'coloredrows'];
        } else if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = 'coloredrows';
        } else {
            $this->attributes['class'] .= ' coloredrows';
        }

        $this->options = [];
        $this->options[1] = get_string('student_approve', 'privatestudentfolder');
        $this->options[2] = get_string('student_reject', 'privatestudentfolder');

        if (empty($files) || count($files) == 0) {
            return 0;
        }

        foreach ($files as $file) {
            $this->data[] = $this->add_file($file);
        }

        return count($this->data);
    }

    /**
     * Returns a boolean with file approve status
     *
     * @param \stored_file $file Stored file instance
     * @return string
     */
    public function is_file_approved($file) {
        global $OUTPUT;
        $templatecontext = new \stdClass;
        // Now add the specific data to the table!
        $teacherapproval = $this->privatestudentfolder->teacher_approval($file);
        $obtainteacherapproval = $this->privatestudentfolder->get_instance()->obtainteacherapproval;

        $teacherapproved = false;
        $teacherdenied = false;
        $teacherpending = false;

        if ($obtainteacherapproval == 1) {
            if ($teacherapproval == 1) {
                $teacherapproved = true;
            } else if ($teacherapproval == 2) {
                $teacherdenied = true;
            } else {
                $teacherpending = true;
            }
        } else {
            $teacherapproved = true;
        }

        return $teacherapproved;

    }

    /**
     * Returns a string with file approval status
     *
     * @param \stored_file $file Stored file instance
     * @return string
     */
    public function get_approval_status_for_file($file) {
        global $OUTPUT;
        $templatecontext = new \stdClass;
        // Now add the specific data to the table!
        $teacherapproval = $this->privatestudentfolder->teacher_approval($file);
        $studentapproval = $this->privatestudentfolder->student_approval($file);

        $obtainteacherapproval = $this->privatestudentfolder->get_instance()->obtainteacherapproval;
        $obtainstudentapproval = $this->privatestudentfolder->get_instance()->obtainstudentapproval;

        $studentapproved = false;
        $studentdenied = false;
        $studentpending = false;
        $hint = '';
        if ($obtainstudentapproval == 1) {
            if ($studentapproval == 1) {
                $studentapproved = true;
                $hint = get_string('student_approved', 'privatestudentfolder');
            } else if ($studentapproval == 2) {
                $studentdenied = true;
                $hint = get_string('student_rejected', 'privatestudentfolder');
            } else {
                if ($this->privatestudentfolder->is_approval_open()) {
                    $this->changepossible = true;
                    return \html_writer::select($this->options, 'studentapproval[' . $file->get_id() . ']', $studentapproval);
                }
                $studentpending = true;
                $hint = get_string('student_pending', 'privatestudentfolder');
            }
        } else {
            $studentapproved = true;
            $hint = get_string('student_approved_automatically', 'privatestudentfolder');
        }

        $hint .= ' ';

        $teacherapproved = false;
        $teacherdenied = false;
        $teacherpending = false;

        if ($obtainteacherapproval == 1) {
            if ($teacherapproval == 1) {
                $teacherapproved = true;
                $hint .= get_string('teacher_approved', 'privatestudentfolder');
            } else if ($teacherapproval == 2) {
                $teacherdenied = true;
                $hint .= get_string('teacher_rejected', 'privatestudentfolder');
            } else {
                $teacherpending = true;
                $hint .= get_string('teacher_pending', 'privatestudentfolder');
            }
        } else {
            $teacherapproved = true;
            $hint .= get_string('teacher_approved_automatically', 'privatestudentfolder');
        }

        if ($teacherpending) {
            $templatecontext->icon = $this->questionmark;
        } else if (!$teacherapproved) {
            $templatecontext->icon = $this->invalid;
        } else {
            if ($studentapproved) {
                $templatecontext->icon = $this->share;
            } else {
                $templatecontext->icon = $this->valid;
            }
        }

        $templatecontext->hint = $hint;
        return $OUTPUT->render_from_template('mod_privatestudentfolder/approval_icon', $templatecontext);

        /*
            if ($teacherapproval && $this->privatestudentfolder->get_instance()->obtainstudentapproval) {
                $studentapproval = $this->privatestudentfolder->student_approval($file);
                if ($this->privatestudentfolder->is_open() && $studentapproval == 0) {
                    $this->changepossible = true;
                    $data[] = \html_writer::select($this->options, 'studentapproval[' . $file->get_id() . ']', $studentapproval);
                    $templatecontext = false;
                } else {
                    switch ($studentapproval) {
                        case 2:
                            $templatecontext->icon = $this->valid;
                            $templatecontext->hint = get_string('student_approved', 'privatestudentfolder');
                            break;
                        case 1:
                            $templatecontext->icon = $this->invalid;
                            $templatecontext->hint = get_string('student_rejected', 'privatestudentfolder');
                            break;
                        default:
                            $templatecontext->icon = $this->questionmark;
                            $templatecontext->hint = get_string('student_pending', 'privatestudentfolder');
                    }
                }
            } else {
                switch ($teacherapproval) {
                    case 1:
                        $templatecontext->icon = $this->valid;
                        $templatecontext->hint = get_string('teacher_approved', 'privatestudentfolder');
                        break;
                    case 3:
                        $templatecontext->icon = $this->questionmark;
                        $templatecontext->hint = get_string('hidden', 'privatestudentfolder') . ' (' . get_string('teacher_pending', 'privatestudentfolder') . ')';
                        break;
                    default:
                        $templatecontext->icon = $this->questionmark;
                        $templatecontext->hint = get_string('student_pending', 'privatestudentfolder');
                }
            }

            if ($templatecontext) {
                return $OUTPUT->render_from_template('mod_privatestudentfolder/approval_icon', $templatecontext);
            }
            return '';
        */

    }

    /**
     * Get all files, in which the current user is involved
     *
     * @return \stored_file[] array of stored_files indexed by pathanmehash
     */
    public function get_files() {
        global $USER;

        if ($this->files !== null) {
            return $this->files;
        }

        $contextid = $this->privatestudentfolder->get_context()->id;
        $filearea = 'attachment';
        // User ID for regular instances, group id for assignments with teamsubmission!
        $itemid = $USER->id;

        $files = $this->fs->get_area_files($contextid, 'mod_privatestudentfolder', $filearea, $itemid, 'timemodified', false);

        foreach ($files as $file) {
            if ($file->get_filepath() == '/resources/') {
                $this->resources[] = $file;
            } else {
                $this->files[] = $file;
            }
            if ($this->lastmodified < $file->get_timemodified()) {
                $this->lastmodified = $file->get_timemodified();
            }
        }

        return $this->files;
    }

    /**
     * Returns if it's possible to change the approval
     *
     * @return bool
     */
    public function changepossible() {
        $result = ($this->changepossible ? true : false) && has_capability('mod/privatestudentfolder:upload',
                        $this->privatestudentfolder->get_context());
        return $result;
    }

    /**
     * Add a single file to the table
     *
     * @param \stored_file $file Stored file instance
     * @return string[] Array of table cell contents
     */
    public function add_file(\stored_file $file) {
        global $OUTPUT;

        $data = [];
        $data[] = $OUTPUT->pix_icon(file_file_icon($file), get_mimetype_description($file));

        if ( $this->is_file_approved($file) ) {
            $dlurl = new \moodle_url('/mod/privatestudentfolder/view.php', [
                    'id' => $this->privatestudentfolder->get_coursemodule()->id,
                    'download' => $file->get_id(),
            ]);
            $data[] = \html_writer::link($dlurl, $file->get_filename());
        } else {
            /* TODO : Make it better, show that link cannot be clicked */
            $data[] = $file->get_filename();
        }

        $data[] = $this->get_approval_status_for_file($file);

        // The specific data will be added in the child-classes!

        return $data;
    }

}
