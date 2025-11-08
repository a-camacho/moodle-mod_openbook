Openbook Resource Folder
=====================
[![Moodle Plugin CI](https://github.com/a-camacho/moodle-mod_openbook/actions/workflows/moodle-plugin-ci.yml/badge.svg?branch=main)](https://github.com/a-camacho/moodle-mod_openbook/actions/workflows/moodle-plugin-ci.yml)
[![Latest Release](https://img.shields.io/github/v/release/a-camacho/moodle-mod_openbook?sort=semver&color=orange)](https://github.com/a-camacho/moodle-mod_openbook/releases)
[![PHP Support](https://img.shields.io/badge/php-8.1--8.4-blue)](https://github.com/a-camacho/moodle-mod_openbook/actions)
[![Moodle Support](https://img.shields.io/badge/Moodle-4.5--5.1+-orange)](https://github.com/a-camacho/moodle-mod_openbook/actions)
[![License GPL-3.0](https://img.shields.io/github/license/a-camacho/moodle-mod_openbook?color=lightgrey)](https://github.com/a-camacho/moodle-mod_openbook/blob/main/LICENSE)
[![GitHub contributors](https://img.shields.io/github/contributors/a-camacho/moodle-mod_openbook)](https://github.com/a-camacho/moodle-mod_openbook/graphs/contributors)

This file is part of the mod_openbook plugin for Moodle - <http://moodle.org/>

*Authors:*    André Camacho (<andre.camacho@unige.ch>), Luca Bösch (<luca.boesch@bfh.ch>)<br>
*Copyright:*  2025 University of Geneva, www.unige.ch<br>
*License:*    [GNU GPL v3 or later](https://www.gnu.org/copyleft/gpl.html)<br>

This plugin has started as a fork of the mod_publication plugin for Moodle

*Author:*    Hannes Laimer, Philipp Hager, Andreas Windbichler, Simeon Naydenov<br>
*Copyright:* [Academic Moodle Cooperation](https://www.academic-moodle-cooperation.org)<br>
*License:*   [GNU GPL v3 or later](https://www.gnu.org/copyleft/gpl.html)

Description
-----------

This plugin is primarily designed to allow students to upload files for later access, for instance during an exam.
Teachers have the ability to approve documents manually, or documents can be automatically approved.
Teachers can choose if files can be shared between students. They can also chose if student sharing approval is needed or automatic.
PDF documents can be set to be opened in PDF.js for better handling. For exam purposes, documents can be set to be opened in
Moodle's secure window.

Usage
-------

A possible teaching scenario could look like this: 

* A teacher opens the Openbook Resource Folder activity for submission. He chooses if submissions are approved automatically.
She also chooses if submissions are private (cannot be shared between students), can be shared between students (with student
approval) or are shared automatically.

* Students submit documents during the submission period. They also choose whether if they want to share the documents with peers
or not (if sharing and student approval are enabled).

* Once submission period ends
  * Teacher approves documents (if needed).
  * Students can access (download) uploaded documents.

* Accessing the documents can be restricted to them being opened in PDF.js.

* Equally, the display of the documents can be set to happen in Moodle 'secure window', so that there is way to access the rest of
the system, making it a safe way to use during an exam.

Installation
------------

* Copy the module code directly to the mod/openbook directory.
* Log into Moodle as administrator.
* Open the administration area (http://your-moodle-site/admin) to start the installation automatically.


Privacy API
--------------

The plugin fully implements the Moodle Privacy API.


Documentation
-------------

You can find documentation for the plugin on :
* [Github](https://github.com/a-camacho/moodle-mod_openbook/issues).
* [Moodle Plugin Repository]()

Bug Reports / Support
---------------------

We try our best to deliver bug-free plugins, but we can not test the plugin for every platform,
database, PHP and Moodle version. If you find any bug please report it on
[GitHub](https://moodle.org/plugins/openbook). Please
provide a detailed bug description, including the plugin and Moodle version and, if applicable, a
screenshot.

You may also post general questions on the plugin on GitHub, but note that we do not have the
resources to provide detailed support.


License
-------

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

The plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License with Moodle. If not, see
<http://www.gnu.org/licenses/>.
