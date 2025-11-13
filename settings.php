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
 * Settings definitions for mod_openbook
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $CFG;

if ($ADMIN->fulltree) {
    require_once(__DIR__ . '/locallib.php');

    $settings->add(new admin_setting_configtext(
        'openbook/maxfiles',
        get_string('maxfiles', 'openbook'),
        get_string('configmaxfiles', 'openbook'),
        5,
        PARAM_INT
    ));

    $options = [
        '1' => get_string('filesarepersonal_yes', 'openbook'),
        '0' => get_string('filesarepersonal_no', 'openbook'),
    ];

    $settings->add(new admin_setting_configselect(
        'openbook/filesarepersonal',
        get_string('filesarepersonal_admin', 'openbook'),
        get_string('filesarepersonal_admin_desc', 'openbook'),
        1,
        $options
    ));

    $options = [
        '1' => get_string('openpdffilesinpdfjs_yes', 'openbook'),
        '0' => get_string('openpdffilesinpdfjs_no', 'openbook'),
    ];

    $settings->add(new admin_setting_configselect(
        'openbook/openpdffilesinpdfjs',
        get_string('openpdffilesinpdfjs_admin', 'openbook'),
        get_string('openpdffilesinpdfjs_admin_desc', 'openbook'),
        1,
        $options
    ));

    $settings->add(
        new admin_setting_configcheckbox(
            'openbook/uselegacyviewer',
            get_string('uselegacyviewer', 'openbook'),
            get_string('uselegacyviewer_help', 'openbook'),
            1
        )
    );

    $options = [
        '0' => get_string('obtainapproval_automatic', 'openbook'),
        '1' => get_string('obtainapproval_required', 'openbook'),
    ];

    $settings->add(new admin_setting_configselect(
        'openbook/obtainteacherapproval',
        get_string('obtainteacherapproval_admin', 'openbook'),
        get_string('obtainteacherapproval_admin_desc', 'openbook'),
        0,
        $options
    ));

    $settings->add(new admin_setting_configselect(
        'openbook/obtainstudentapproval',
        get_string('obtainstudentapproval_admin', 'openbook'),
        get_string('obtainstudentapproval_admin_desc', 'openbook'),
        0,
        $options
    ));

    $options = [
        OPENBOOK_NOTIFY_NONE => get_string('notify:setting:0', 'openbook'),
        OPENBOOK_NOTIFY_TEACHER => get_string('notify:setting:1', 'openbook'),
        OPENBOOK_NOTIFY_STUDENT => get_string('notify:setting:2', 'openbook'),
        OPENBOOK_NOTIFY_ALL => get_string('notify:setting:3', 'openbook'),
    ];

    $settings->add(new admin_setting_configselect(
        'openbook/notifyfilechange',
        get_string('notify:filechange_admin', 'openbook'),
        get_string('notify:filechange_help', 'openbook'),
        OPENBOOK_NOTIFY_STUDENT,
        $options
    ));

    $settings->add(new admin_setting_configselect(
        'openbook/notifystatuschange',
        get_string('notify:statuschange_admin', 'openbook'),
        get_string('notify:statuschange_help', 'openbook'),
        OPENBOOK_NOTIFY_ALL,
        $options
    ));

    if (isset($CFG->maxbytes)) {
        $settings->add(new admin_setting_configselect(
            'openbook/maxbytes',
            get_string('maxbytes', 'openbook'),
            get_string('configmaxbytes', 'openbook'),
            5242880,
            get_max_upload_sizes($CFG->maxbytes)
        ));
    }

    $settings->add(new admin_setting_configselect(
        'openbook/availabilityrestriction',
        get_string('availabilityrestriction_admin', 'openbook'),
        get_string('availabilityrestriction_admin_desc', 'openbook'),
        1,
        [get_string('no'), get_string('yes')]
    ));
}
