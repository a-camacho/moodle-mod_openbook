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
 * Strings for component 'openbook', language 'en'
 *
 * @package       mod_openbook
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Let codechecker ignore some sniffs for this file as it is perfectly well ordered, just not alphabetically.
// phpcs:disable moodle.Files.LangFilesOrdering.UnexpectedComment
// phpcs:disable moodle.Files.LangFilesOrdering.IncorrectOrder

$string['availabilityrestriction'] = 'Apply availability restrictions to user list';
$string['availabilityrestriction_admin'] = 'Default setting for availability restrictions to user list';
$string['availabilityrestriction_help'] = 'Users who cannot access the activity due to availability restrictions will be removed from the list.<br>This only includes conditions which are marked as being applied to user lists. For example, group conditions are included but date conditions are not included.';
$string['availabilityrestriction_admin_desc'] = 'Users who cannot access the activity due to availability restrictions will be removed from the list.<br>This only includes conditions which are marked as being applied to user lists. For example, group conditions are included but date conditions are not included.';
$string['modulename'] = 'Openbook resource folder';
$string['pluginname'] = 'Openbook resource folder';
$string['modulename_help'] = 'The openbook resource folder offers the following features:<br><ul><li>Students can upload files. The time period can be restricted by the teacher.</li><li>The files will be published automatically or after students and/or teachers approval for publishing. If allowed, students can choose to make the file visible for all participants, otherwise each student will only see her/his uploaded document(s).</li><li>Students and/or teachers will receive a notification when students upload or change a file. Furthermore students and/or teachers will receive a notification about any changes of the openbook folder status.</li><li>PDF files in the openbook resource folder can be chosen to be opened in a PDF.js.</li><li>Teacher can set up a time period in which the opened documents are displayed in a secure window, i.e. a window without Moodle navigation etcetera.</li></li></ul>';

$string['eventopenbookfiledeleted'] = 'Openbook resource folder file deleted';
$string['eventopenbookfileuploaded'] = 'Openbook resource folder file uploaded';
$string['eventopenbookfileimported'] = 'Openbook resource folder file imported';
$string['eventopenbookduedateextended'] = 'Openbook resource folder due-date extended';
$string['eventopenbookapprovalchanged'] = 'Openbook resource folder file approval changed';

$string['modulenameplural'] = 'Openbook resource folders';
$string['pluginadministration'] = 'Openbook resource folder folder administration';
$string['openbook:addinstance'] = 'Add a new Openbook resource folder';
$string['openbook:view'] = 'View Openbook resource folder';
$string['openbook:upload'] = 'Upload files to a Openbook resource folder';
$string['openbook:approve'] = 'Decide if files should be published (visible for all participants)';
$string['openbook:grantextension'] = 'Grant extension';
$string['openbook:manageoverrides'] = 'Manage overrides';
$string['openbook:receiveteachernotification'] = 'Receive notifications for teachers';
$string['search:activity'] = 'Openbook resource folder - activity information';

$string['messageprovider:openbook_updates'] = 'Openbook resource folder notifications';

$string['notifications'] = 'Notifications';
$string['notifyteacher'] = 'Notify teachers about submitted files';
$string['notifystudents'] = 'Notify students about Openbook resource folder changes';
$string['notifyteacher_help'] = 'If enabled, teachers will receive a notification when students upload a file.';
$string['notifystudents_help'] = 'If enabled, students will receive a notification when the Openbook resource folder status of one of their uploaded files changes.';

$string['notify:setting:0'] = 'No notifications';
$string['notify:setting:1'] = 'Teachers only';
$string['notify:setting:2'] = 'Students only';
$string['notify:setting:3'] = 'Both teachers and students';
$string['notify:statuschange'] = 'Notifications about Openbook resource folder status changes';
$string['notify:statuschange_help'] = ' Depends on the setting, if enabled, students and/or teachers will receive a notification when the Openbook resource folder status of one of the files is changed.';
$string['notify:statuschange_admin'] = 'Default notification setting for Openbook resource folder status changes';
$string['notify:filechange'] = 'Notifications about submitted files';
$string['notify:filechange_help'] = 'Depends on the setting, if enabled, students and/or teachers will receive a notification when students upload or change a file, or when a file is imported or updated from an assignment activity.';
$string['notify:filechange_admin'] = 'Default notification setting for file changes - submitted or imported';

$string['email:statuschange:header'] = 'The Openbook resource folder status of the following file(s) for <b>\'{$a->openbook}\'</b> was changed on {$a->dayupdated} at {$a->timeupdated} by <b>{$a->username}</b>:<br /><ul>';
$string['email:statuschange:filename'] = '<li>\'{$a->filename}\' to \'<b>{$a->apstatus}</b>\'</li>';
$string['email:statuschange:footer'] = '</ul>';
$string['email:statuschange:subject'] = 'Openbook resource folder status changed';
$string['email:filechange_upload:header'] = '<b>{$a->username}</b> has uploaded the following file(s) to <b>\'{$a->openbook}\'</b> on {$a->dayupdated} at {$a->timeupdated}:<br /><ul>';
$string['email:filechange_upload:subject'] = 'File(s) uploaded';
$string['email:filechange_import:header'] = 'The following file(s) from Assignment <b>\'{$a->assign}\'</b> was/were imported into <b>\'{$a->openbook}\'</b> on {$a->dayupdated} at {$a->timeupdated}:<br /><ul>';
$string['email:filechange_import:subject'] = 'File(s) imported';
$string['email:filechange:footer'] = '</ul><br />Please check if your permission for Openbook resource folder is required.';

$string['uploaded'] = 'Uploaded';
$string['approvalchange'] = 'Openbook resource folder status changed';

$string['approvalsettings'] = 'Openbook resource folder settings';
$string['name'] = 'Name';
$string['obtainstudentapproval'] = 'Student approval';
$string['obtainstudentapproval_admin'] = 'Default student approval setting';
$string['obtainstudentapproval_admin_desc'] = 'This setting determines the default approval setting for students.';
$string['obtainstudentapproval_help'] = 'This option determines how the Openbook resource folder (visibility) of file submissions by student approval takes place: <br><ul><li><strong>Automatic</strong> - no approval from students is required. In the spirit of copyright law, we ask that you request approval to publish files from students in a separate way.</li><li><strong>Required</strong> - Students need to manually approve the file for Openbook resource folder</li></ul>';
$string['saveapproval'] = 'Save changes';

$string['filesarepersonal'] = 'Files are personal';
$string['filesarepersonal_yes'] = 'Yes (files are personal)';
$string['filesarepersonal_no'] = 'No (files can be shared between students)';
$string['filesarepersonal_admin'] = 'Files are personal';
$string['filesarepersonal_admin_desc'] = 'This setting determines the default visibility setting for student files.';
$string['filesarepersonal_help'] = 'This option determines if submitted files remain private to the owner: <br><ul><li><strong>Yes</strong> - files are private and personal.</li><li><strong>No</strong> - Files can be shared between students, if they are approved by needed parties.</li></ul>';

$string['openpdffilesinpdfjs'] = 'Enable PDF.js';
$string['openpdffilesinpdfjs_yes'] = 'Yes';
$string['openpdffilesinpdfjs_no'] = 'No';
$string['openpdffilesinpdfjs_admin'] = 'PDF files are shown with PDF.js viewer';
$string['openpdffilesinpdfjs_admin_desc'] = 'This setting determines the default setting for how PDF documents are displayed.';
$string['openpdffilesinpdfjs_help'] = 'This option determines whether submitted PDF files should be displayed using the PDF.js library.';

$string['uselegacyviewer'] = 'Use legacy PDF.js viewer';
$string['uselegacyviewer_help'] = 'If enabled, will use the legacy PDF.js viewer. Else, modern PDF.js viewer will be used. The legacy viewer is needed when using old browser versions that have trouble displaying PDF files using the modern PDF.js viewer.';
$string['uselegacyviewer_yes'] = 'Yes';
$string['uselegacyviewer_no'] = 'No';

$string['myownfiles'] = 'My own files';

$string['obtainteacherapproval'] = 'Teacher approval';
$string['obtainteacherapproval_help'] = 'This option determines how the Openbook resource folder (visibility) of file submissions by teachers approval takes place: <br><ul><li><strong>Automatic</strong> - no approval from teachers is required.</li><li><strong>Required</strong> - Teachers need to manually approve the file for Openbook resource folder</li></ul>';
$string['obtainteacherapproval_no'] = 'Automatic';
$string['obtainteacherapproval_yes'] = 'Required';
$string['obtainteacherapproval_admin'] = 'Default teacher approval setting';
$string['obtainteacherapproval_admin_desc'] = 'This setting determines the default approval setting for teachers.';
$string['obtainstudentapproval_no'] = 'Automatic';
$string['obtainstudentapproval_yes'] = 'Required';
$string['obtainapproval_automatic'] = 'Automatic';
$string['obtainapproval_required'] = 'Required';
$string['obtaingroupapproval'] = 'Approval by group';
$string['obtaingroupapproval_help'] = 'This option determines how the publication (visibility) of file submissions by groups takes place: <br /><ul><li><strong>Automatic</strong> - no approval from group members is required. In the spirit of copyright law, we ask that you request approval to publish files from students in a separate way.</li><li><strong>Required from at least ONE member</strong> - at least one group member needs to approve</li><li><strong>Required from ALL members</strong> - all group members need to approve</li></ul>';
$string['obtaingroupapproval_all'] = 'Required from ALL members';
$string['obtaingroupapproval_single'] = 'Required from at least ONE member';
$string['obtaingroupapproval_title'] = 'Group approval';
$string['obtaingroupapproval_admin'] = 'Default group approval setting';
$string['obtaingroupapproval_admin_desc'] = 'This setting determines the default approval setting for group members. This setting becomes relevant <strong>only</strong> when the mode is set to "Import files from an assignment activity" and the assignment has group submission.';

$string['approvalfromdate'] = 'Approval from';
$string['approvalfromdate_help'] = 'Approval status cannot be changed before this date. This setting is only relevant when Student approval is not automatic.';
$string['approvaltodate'] = 'Approval until';
$string['approvaltodate_help'] = 'Approval status cannot be changed after this date. This setting is only relevant when Student approval is not automatic.';
$string['approvaltodatevalidation'] = 'Approval until date must be after the approval from date.';
$string['maxfiles'] = 'Maximum number of attachments';
$string['maxfiles_help'] = 'Each student will be able to upload up to this number of files for their submission.';
$string['configmaxfiles'] = 'Default maximum number of attachments allowed per user.';
$string['maxbytes'] = 'Maximum attachment size';
$string['maxbytes_help'] = 'Files uploaded by students may be up to this size.';
$string['configmaxbytes'] = 'Default maximum size for all files in the student folder.';
$string['uploadnotopen'] = 'File upload is closed!';

$string['reset_userdata'] = 'All data';

// Strings from the file mod_form.
$string['configautoimport'] = 'If you prefer to have student submissions be automatically imported into student folder instances. This feature can be enabled/disabled for each student folder instance separately.';
$string['availability'] = 'Editing period (upload or approval)';
$string['submissionsettings'] = 'Submission settings';
$string['allowsubmissionsfromdate'] = 'Upload from';
$string['allowsubmissionsfromdate_help'] = 'If this option is enabled, participants cannot submit their file submissions before this date. If the option is disabled, participants can start submitting right away.';
$string['allowsubmissionsfromdatesummary'] = 'This assignment will accept submissions from <strong>{$a}</strong>';
$string['allowsubmissionsanddescriptionfromdatesummary'] = 'The assignment details and submission form will be available from <strong>{$a}</strong>';
$string['alwaysshowdescription'] = 'Always show description';
$string['alwaysshowdescription_help'] = 'If disabled, the assignment description above will only become visible to students at the "Upload/Approval from" date.';

$string['duedate'] = 'To';
$string['duedate_help'] = 'If this option is enabled, participants cannot submit their file submissions after this date. If the option is disabled, participants can submit forever.';
$string['duedatevalidation'] = 'Due date must be after the allow submissions from date.';

$string['securewindowsettings'] = 'Secure window settings';
$string['securewindowfromdate'] = 'Start secure window';
$string['securewindowfromdate_help'] = 'If this option is enabled, the activity will open in a secure window that hides navigation and offers no links to other parts of Moodle. So, for example, only links to the allowed files will be visible during the a quiz attempt. Secure window starts at the given date.';
$string['securewindowtodate'] = 'End secure window';
$string['securewindowtodate_help'] = 'If this option is enabled, the activity will open in a secure window that hides navigation and offers no links access to other parts of Moodle. So, for example, only links to the allowed files will be visible during the a quiz attempt. Secure window ends at the given date.';
$string['securewindowtodatevalidation'] = 'End date must be after secure window start date.';

$string['courseuploadlimit'] = 'Course upload limit';
$string['allowedfiletypes'] = 'Accepted file types';
$string['allowedfiletypes_help'] = 'Accepted file types can be restricted by entering a comma-separated list of mimetypes, e.g. \'video/mp4, audio/mp3, image/png, image/jpeg\', or file extensions including a dot, e.g. \'.png, .jpg\'. If the field is left empty, then all file types are allowed.';
$string['allowedfiletypes_err'] = 'Check input! Invalid file extensions or seperators';

$string['currentlynotapproved'] = '* Currently not approved or rejected to publication.';

$string['teacherapproval_help'] = 'Current approval/rejection of files, i.e. whether they are visible to all participants: <br><ul><li><strong>Choose...</strong> - decision pending/no approval given or rejected, these files are not visible.</li><li><strong>Approve</strong> - approval granted, these files are published and therefore visible to all.</li><li><strong>Reject</strong> - no approval given, these files are not published and therefore not visible.</li></ul>';
$string['assignment'] = 'Assignment';
$string['assignment_help'] = 'Choose the assignment to import files from individual or group submissions.';
$string['choose'] = 'Please choose ...';
$string['importfrom_err'] = 'You have to choose an assignment you want to import file submissions from.';
$string['nonexistentfiletypes'] = 'The following file types were not recognised: {$a}';

$string['completionupload'] = 'Student must upload a file';
$string['completiondetail:upload'] = 'Upload a file';

// Strings from the File mod_openbook_grantextension_form.php.
$string['extensionduedate'] = 'Extension due date';
$string['extensionnotafterduedate'] = 'Extension date must be after the due date';
$string['extensionnotafterfromdate'] = 'Extension date must be after the allow submissions from date';

// Strings from the File index.php.
$string['noopenbooksincourse'] = 'There is no student folder instance in this course.';

// Strings from the File view.php.
$string['allowsubmissionsfromdate_upload'] = 'Upload from';
$string['duedate_upload'] = 'Upload until';
$string['cutoffdate_upload'] = 'Last upload to';
$string['cutoffdate_import'] = 'Last approval to';
$string['extensionto'] = 'Extension to';
$string['filedetails'] = 'Details';
$string['assignment_notfound'] = 'The assignment from which files were imported, could no longer be found.';
$string['assignment_notset'] = 'No assignment has been chosen.';
$string['updatefiles'] = 'Update files';
$string['updatefileswarning'] = 'Already imported files will be replaced or deleted if the original files in the assignment were refreshed or deleted. The student\'s settings like the approval for publishing remain as they are.';
$string['myfiles'] = 'Own files';
$string['mygroupfiles'] = 'My group\'s files';
$string['add_uploads'] = 'Add files';
$string['edit_uploads'] = 'Edit/upload files';
$string['edit_timeover'] = 'Files can be edited/uploaded only during the editing period.';
$string['approval_timeover'] = 'You don\'t have any file waiting for sharing approval or approval time is over.';
$string['approval_timeover_description'] = 'Sharing approval';
$string['noentries'] = 'No entries';
$string['nofiles'] = 'No files available';
$string['nothing_to_show_users'] = 'Nothing to display - no students available';
$string['nothing_to_show_groups'] = 'Nothing to display - no groups available';
$string['notice'] = '<strong>Notice: </strong>';
$string['datalogapprovalstudent'] = '(Students {$a->approving} out of {$a->needed}) {$a->approval}';
$string['viewallxsubmissions'] = 'View all {$a} submissions';

$string['notice_upload_filesarepersonal_teacherrequired'] = 'All files you upload here will be personal and visible to you only <strong>after the approval of teachers.</strong> Teachers reserve the right to reject the publication of your files at any time.';
$string['notice_upload_filesarepersonal_teachernotrequired'] = 'All files you upload here will be personal and visible to you <strong>automatically.</strong>';

$string['notice_upload_studentrequired_teacherrequired'] = 'All files you upload here will be visible and accessible only to you, <strong>after teacher approval</strong>. They will only be shared (visible to other students) <strong>after your approval.</strong>';
$string['notice_upload_studentrequired_teachernotrequired'] = 'All files you upload here will be visible and accessible only to you. They will only be shared (visible to other students) <strong>after your approval.</strong>';
$string['notice_upload_studentnotrequired_teacherrequired'] = 'All files you upload here will be published (visible to all students) only <strong>after teacher approval.</strong>';
$string['notice_upload_studentnotrequired_teachernotrequired'] = 'All files you upload here will be published (visible to everyone) <strong>automatically.</strong>';

$string['notice_import_studentrequired_teacherrequired'] = 'The files will be published (will be made visible for everyone) after <strong>your approval and the approval of teachers.</strong> Teachers reserve the right to reject the publication of your files at any time.';
$string['notice_import_studentrequired_teachernotrequired'] = 'The files will be published (will be made visible for everyone) after <strong>your approval.</strong>';
$string['notice_import_studentnotrequired_teacherrequired'] = 'The files will be published (will be made visible for everyone) only <strong>after the approval of teachers.</strong> Teachers reserve the right to reject the publication of your files at any time.';
$string['notice_import_studentnotrequired_teachernotrequired'] = 'The files will be published (will be made visible to everyone) <strong>automatically.</strong>';

$string['notice_group_all_teacherrequired'] = 'The files will only be published for all students with the approval of <strong>ALL group members and the teacher.</strong> Teachers reserve the right to reject the publication of your files at any time.';
$string['notice_group_all_teachernotrequired'] = 'The files will only be published for all students with the approval of <strong>ALL group members.</strong>';
$string['notice_group_one_teacherrequired'] = 'The files will only be published for all students with the approval of <strong>at LEAST ONE group member and the teacher.</strong> Teachers reserve the right to reject the publication of your files at any time.';
$string['notice_group_one_teachernotrequired'] = 'The files will only be published for all students with the approval of <strong>at LEAST ONE group member.</strong>';

$string['notice_files_imported'] = 'Shown files are imported from an assignment activity.';
$string['notice_files_imported_group'] = 'Shown files are from a group submission, imported from an assignment activity.';
$string['notice_changes_possible_in_original'] = 'Changes to existing files are only possible in the original assignment activity.';

// Strings for approval.
$string['notice_obtainteacherapproval_studentsapproval'] = 'In the spirit of copyright law, we ask that you request approval to publish files from participants in a separate way.';

$string['notice_obtainapproval_import_both'] = 'As a teacher, you can reject approval for publication at any time, if a file not meets the defined requirements.';
$string['notice_obtainapproval_import_studentonly'] = 'In the spirit of copyright law, we ask that you request approval to publish files from students in a separate way.<br>As a teacher, you can reject approval for publication at any time, if a file not meets the defined requirements.';
$string['notice_obtainapproval_upload_teacher'] = 'In the spirit of copyright law, we ask that you request approval to publish files from students in a separate way.<br>As a teacher, you can reject approval for publication at any time, if a file does not meet the defined requirements.';
$string['notice_obtainapproval_upload_automatic'] = 'In the spirit of copyright law, we ask that you request approval to publish files from students in a separate way.<br>As a teacher, you can reject approval for publication at any time, if a file does not meet the defined requirements.';

$string['teacher_pending'] = 'Decision from teacher is pending.';
$string['teacher_approved'] = 'Approved by teacher.';
$string['teacher_approved_automatically'] = 'Approved by teacher automatically.';
$string['teacher_rejected'] = 'Rejected by teacher.';
$string['teacher_approve'] = 'Approve';
$string['teacher_reject'] = 'Reject';
$string['approved'] = 'Approved';
$string['show_details'] = 'Show details';
$string['student_approve'] = 'Share';
$string['student_approved'] = 'Sharing approved by student.';
$string['group_approved'] = 'Sharing approved by all members of the group.';
$string['student_approved_automatically'] = 'Sharing approved by student automatically.';
$string['student_pending'] = 'Decision from student is pending.';
$string['pending'] = 'Pending';
$string['student_reject'] = 'Do not share';
$string['student_rejected'] = 'Sharing rejected from student.';
$string['rejected'] = 'Rejected';
$string['visible'] = 'Published';
$string['hidden'] = 'Not published';
$string['status:approved'] = 'Approved';
$string['status:approvedautomatic'] = 'Automatic';
$string['status:approvednot'] = 'Rejected';
$string['status:approvedrevoke'] = 'Revoked';
$string['giveapproval'] = 'Give approval';
$string['overdue'] = 'Deadline of editing/uploading period passed';
$string['approval_required'] = 'Decision pending';
$string['openbookstatus'] = 'Approval';
$string['openbookstatus_help'] = 'The status of the file represents the approval of the teacher: <ul><li><i class="fa fa-check text-success fa-fw"></i><i class="fa fa-share-from-square text-success fa-fw"></i> File is approved, has been shared, and is therefore visible for all participants</li><li><i class="fa fa-check text-success fa-fw"></i><i class="fa fa-user text-success fa-fw"></i> File is approved and is visible for the participant only</li><li><i class="fa fa-times text-danger fa-fw"></i> File is not approved (approval has not yet been given or has been rejected) and therefore not visible</li></ul>';

$string['allfiles'] = 'File submissions';
$string['publicfiles'] = 'Shared files';
$string['downloadall'] = 'Download all file submissions';
$string['optionalsettings'] = 'Options';
$string['entiresperpage'] = 'Participants shown per page';
$string['nothingtodisplay'] = 'No entries to display';
$string['nofilestodisplay'] = 'Currently there are no files available or not yet published.';
$string['nofilestozip'] = 'No files to zip';
$string['status'] = 'Status';
$string['studentapproval'] = 'Approval (students)';
$string['studentapproval_help'] = 'In the column "Approval (students)" the feedback of the students is displayed:<br><ul><li><i class="fa fa-question fa-fw text-warning"></i> - Decision pending</li><li><i class="fa fa-check text-success fa-fw"></i> - Approval given</li><li><i class="fa fa-times text-danger fa-fw"></i> - Approval rejected</li></ul>';
$string['teacherapproval'] = 'Approval';
$string['visibility'] = 'Published';
$string['visibleforstudents'] = 'Published';
$string['visibleforstudents_yes'] = 'This file is published (visible for students).';
$string['visibleforstudents_no'] = 'This file is not published (not visible for students).';
$string['resetstudentapproval'] = 'Revert student approval';
$string['savestudentapprovalwarning'] = 'Are you sure you want to save these changes? The publication status cannot be changed once it is set.';
$string['studentswhosubmitted'] = 'Students who submitted';
$string['submitted'] = 'Submitted';

$string['go'] = 'Go';
$string['withselected'] = 'With selected...';
$string['zipusers'] = 'Download selected file submissions';
$string['approveusers'] = 'Give approval';
$string['rejectusers'] = 'Reject';
$string['grantextension'] = 'Grant extension';
$string['saveteacherapproval'] = 'Save changes';
$string['reset'] = 'Revert';

// Strings from the file upload.php.
$string['filesofthesetypes'] = 'Files of these types may be added:';
$string['guideline'] = 'Openbook resource folder of file submissions';
$string['published_immediately'] = 'Approve automatically';
$string['published_aftercheck'] = 'Approval from teachers required';
$string['save_changes'] = 'Save changes';

$string['overview'] = 'Overview';

// Strings for JS.
$string['total'] = 'Total';
$string['details'] = 'Details';

// Strings for privacy-API.
$string['privacy:metadata:openbookperpage'] = 'How many entries should be displayed on a single table page!';
$string['privacy:path:files'] = 'Files';
$string['privacy:path:resources'] = 'Resources';
$string['privacy:type:upload'] = 'Uploaded file';
$string['privacy:type:import'] = 'Imported file';
$string['privacy:type:onlinetext'] = 'Imported onlinetext';
$string['privacy:metadata:groupapproval'] = 'Stores information about approval or rejection of files by group members, imported from a group submission.';
$string['privacy:metadata:openbookfileexplanation'] = 'Files and converted onlinetext-submissions for this plugin get stored via Moodle\'s file API.';
$string['privacy:metadata:extduedates'] = 'Stores information about overridden/extended due dates for mod_openbook.';
$string['privacy:metadata:files'] = 'Stores information (identifier, whom it belongs, where it came from, hash of content, file name and if approved by teacher and/or student) about the files uploaded/imported into mod_openbook.';
$string['privacy:metadata:fileid'] = 'Identifier of the file.';
$string['privacy:metadata:userid'] = 'Identifier of the user.';
$string['privacy:metadata:timecreated'] = 'The time and date the data record was created.';
$string['privacy:metadata:timemodified'] = 'The most recent time and date the data record got updated/modified.';
$string['privacy:metadata:approval'] = 'Whether the group member has approved or rejected for publication.';
$string['privacy:metadata:studentapproval'] = 'Whether the student has approved or rejected the publication of a file.';
$string['privacy:metadata:teacherapproval'] = 'Whether the teacher has approved or rejected the publication of a file.';
$string['privacy:metadata:type'] = 'Marks the origin of the file (uploaded by student, imported from assignment submission or converted onlinetext from assignment submission).';
$string['privacy:metadata:contenthash'] = 'SHA1 hash of the file\'s content, used to determine if the file changed.';
$string['privacy:metadata:filename'] = 'The file\'s name.';
$string['privacy:metadata:extensionduedate'] = 'The due date effective for students due to the override/extension.';

// Strings for filters.
$string['filter'] = 'Filter';
$string['filter:nofilter'] = 'No filter';
$string['filter:allfiles'] = 'All file submissions';
$string['filter:approved'] = 'Approved file submissions';
$string['filter:rejected'] = 'Rejected file submissions';
$string['filter:approvalrequired'] = 'Decision pending';
$string['filter:nofiles'] = 'No file submission';

// Strings for overrides.
$string['overrides'] = 'Overrides';
$string['eventoverridecreated'] = 'Openbook resource folder override created';
$string['eventoverridedeleted'] = 'Openbook resource folder override deleted';
$string['eventoverrideupdated'] = 'Openbook resource folder override updated';
$string['override:add:group'] = 'Add group override';
$string['override:add:user'] = 'Add user override';
$string['overrides:empty'] = 'No overrides';
$string['override:save:success'] = 'Override saved successfully';
$string['override:invalidid'] = 'Invalid override ID';
$string['override:submission:fromto'] = 'Allow submissions from {$a->from} until {$a->to}';
$string['override:submission:from'] = 'Allow submissions from {$a->from}';
$string['override:submission:to'] = 'Allow submissions until {$a->to}';
$string['override:approval:fromto'] = 'Approval from {$a->from} until {$a->to}';
$string['override:approval:from'] = 'Approval from {$a->from}';
$string['override:approval:to'] = 'Approval until {$a->to}';
$string['override:group:choose'] = 'Choose a group';
$string['override:user:choose'] = 'Choose a user';
$string['override:securewindow:fromto'] = 'Secure window from {$a->from} until {$a->to}';
$string['override:securewindow:from'] = 'Secure window from {$a->from}';
$string['override:securewindow:to'] = 'Secure window until {$a->to}';
$string['override:nothingtochange'] = 'There are no settings that can be overriden with the current activity settings!';
$string['override:delete:ask'] = 'Are you sure you want to delete the override for {$a->userorgroup} {$a->fullname}?';
$string['override:delete:success'] = 'Override deleted successfully!';
