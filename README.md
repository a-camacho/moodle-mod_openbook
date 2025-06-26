Private Student Folder 
=====================

This file is part of the mod_privatestudentfolder plugin for Moodle - <http://moodle.org/>

*Author:*     André Camacho, Benjamin Abraham
*Copyright:*  [University of Geneva](https://www.unige.ch)
*License:*    [GNU GPL v3 or later](https://www.gnu.org/copyleft/gpl.html)

This plugin has started as a fork of the mod_publication plugin for Moodle

*Author:*    Hannes Laimer, Philipp Hager, Andreas Windbichler, Simeon Naydenov
*Copyright:* [Academic Moodle Cooperation](https://www.academic-moodle-cooperation.org)
*License:*   [GNU GPL v3 or later](https://www.gnu.org/copyleft/gpl.html)

Description
-----------

This plugin is primarily designed to allow students to upload files for later access, for instance during an exam.
Teachers have the ability to aprove documents manually, or documents can be automatically aproved.
Teachers can choose if files can be shared between students. They can also chose if student sharing aproval is needed or automatic.

Usage
-------

A possible teaching scenario could look like this: 

* A teacher opens the Private Student Folder activity for submission. He chooses if submissions are aproved automatically. He also chooses if submissions are private (cannot be shared between students), can be shared between students (with student aproval) or are shared automatically.

* Students submit documents during the submission period. They also choose wether if they want to share the documents with peers or not (if sharing and student approval are enabled).

* Once submission period ends
  * Teacher aproves documents (if needed).
  * Students can access (download) uploaded documents.


Installation
------------

* Copy the module code directly to the mod/privatestudentfolder directory.
* Log into Moodle as administrator.
* Open the administration area (http://your-moodle-site/admin) to start the installation automatically.


Privacy API
--------------

The plugin fully implements the Moodle Privacy API.


Documentation
-------------

You can find documentation for the plugin on :
* [Github](https://github.com/acamacho-unige/moodle-mod_privatestudentfolder).
* [Moodle Plugin Repository](https://moodle.org/plugins/privatestudentfolder)

Bug Reports / Support
---------------------

We try our best to deliver bug-free plugins, but we can not test the plugin for every platform,
database, PHP and Moodle version. If you find any bug please report it on
[GitHub](https://github.com/acamacho-unige/moodle-mod_privatestudentfolder). Please
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
