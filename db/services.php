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
 * Webservice definition
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$services = [
        'mod_openbook_onlinetextpreview' => [ // The name of the web service.
           'functions' => ['mod_openbook_get_onlinetextpreview'], // Web service functions of this service.
           'requiredcapability' => '', /* If set, the web service user need this capability to access any function of this
                                        * service. For example: 'some/capability:specified'.*/
           'restrictedusers' => 0, /* If enabled, the Moodle administrator must link some user to this service into the
                                    * administration.*/
           'enabled' => 1, // If enabled, the service can be reachable on a default installation.
        ],
];

$functions = [
        'mod_openbook_get_onlinetextpreview' => [ // Web service function name.
           'classname' => 'mod_openbook_external', // Class containing the external function.
           'methodname' => 'get_onlinetextpreview', // External function name.
           'classpath' => 'mod/openbook/externallib.php', // File containing the class/external function.
           'description' => 'Fetches HTML snippet to preview onlinetext.', /* Human readable description of the web service
                                                                            * function.*/
           'type' => 'read', // Database rights of the WS-function (read, write).
           'ajax' => true,
        ],
];
