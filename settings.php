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
 * Settings definitions for mod_privatestudentfolder
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $CFG;

if ($ADMIN->fulltree) {

    require_once(__DIR__ . '/locallib.php');

    $settings->add(new admin_setting_configtext('privatestudentfolder/maxfiles', get_string('maxfiles', 'privatestudentfolder'),
            get_string('configmaxfiles', 'privatestudentfolder'), 5, PARAM_INT));

    $options = [
        '1' => get_string('filesarepersonal_yes', 'privatestudentfolder'),
        '0' => get_string('filesarepersonal_no', 'privatestudentfolder'),
    ];

    $settings->add(new admin_setting_configselect('privatestudentfolder/filesarepersonal', get_string('filesarepersonal_admin', 'privatestudentfolder'),
    get_string('filesarepersonal_admin_desc', 'privatestudentfolder'), 1, $options));

    $options = [
        '0' => get_string('obtainapproval_automatic', 'privatestudentfolder'),
        '1' => get_string('obtainapproval_required', 'privatestudentfolder'),
    ];

    $settings->add(new admin_setting_configselect('privatestudentfolder/obtainteacherapproval', get_string('obtainteacherapproval_admin', 'privatestudentfolder'),
            get_string('obtainteacherapproval_admin_desc', 'privatestudentfolder'), 0, $options));

    $settings->add(new admin_setting_configselect('privatestudentfolder/obtainstudentapproval', get_string('obtainstudentapproval_admin', 'privatestudentfolder'),
            get_string('obtainstudentapproval_admin_desc', 'privatestudentfolder'), 0, $options));

    $options = [
        PRIVATESTUDENTFOLDER_APPROVAL_GROUPAUTOMATIC => get_string('obtainapproval_automatic', 'privatestudentfolder'),
        PRIVATESTUDENTFOLDER_APPROVAL_SINGLE => get_string('obtaingroupapproval_single', 'privatestudentfolder'),
        PRIVATESTUDENTFOLDER_APPROVAL_ALL => get_string('obtaingroupapproval_all', 'privatestudentfolder'),
    ];

    $settings->add(new admin_setting_configselect('privatestudentfolder/obtaingroupapproval', get_string('obtaingroupapproval_admin', 'privatestudentfolder'),
        get_string('obtaingroupapproval_admin_desc', 'privatestudentfolder'), 0, $options));


    $options = [
        PRIVATESTUDENTFOLDER_NOTIFY_NONE => get_string('notify:setting:0', 'privatestudentfolder'),
        PRIVATESTUDENTFOLDER_NOTIFY_TEACHER => get_string('notify:setting:1', 'privatestudentfolder'),
        PRIVATESTUDENTFOLDER_NOTIFY_STUDENT => get_string('notify:setting:2', 'privatestudentfolder'),
        PRIVATESTUDENTFOLDER_NOTIFY_ALL => get_string('notify:setting:3', 'privatestudentfolder'),
    ];

    $settings->add(new admin_setting_configselect('privatestudentfolder/notifyfilechange', get_string('notify:filechange_admin', 'privatestudentfolder'),
        get_string('notify:filechange_help', 'privatestudentfolder'), PRIVATESTUDENTFOLDER_NOTIFY_STUDENT, $options));

    $settings->add(new admin_setting_configselect('privatestudentfolder/notifystatuschange', get_string('notify:statuschange_admin', 'privatestudentfolder'),
        get_string('notify:statuschange_help', 'privatestudentfolder'), PRIVATESTUDENTFOLDER_NOTIFY_ALL, $options));

    if (isset($CFG->maxbytes)) {
        $settings->add(new admin_setting_configselect('privatestudentfolder/maxbytes', get_string('maxbytes', 'privatestudentfolder'),
                get_string('configmaxbytes', 'privatestudentfolder'), 5242880, get_max_upload_sizes($CFG->maxbytes)));
    }

    $settings->add(new admin_setting_configselect('privatestudentfolder/availabilityrestriction', get_string('availabilityrestriction_admin', 'privatestudentfolder'),
            get_string('availabilityrestriction_admin_desc', 'privatestudentfolder'), 1, [get_string('no'), get_string('yes')]));
}
