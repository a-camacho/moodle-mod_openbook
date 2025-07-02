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
 * Strings for component 'privatestudentfolder', language 'en'
 *
 * @package       mod_privatestudentfolder
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 $string['availabilityrestriction'] = 'Appliquer des restrictions de disponibilité à la liste des utilisateurs';
 $string['availabilityrestriction_admin'] = 'Paramètre par défaut pour les restrictions de disponibilité sur la liste des utilisateurs';
 $string['availabilityrestriction_help'] = 'Les utilisateurs qui ne peuvent pas accéder à l\'activité en raison de restrictions de disponibilité seront retirés de la liste.<br> Cela inclut uniquement les conditions marquées comme appliquées aux listes d\'utilisateurs. Par exemple, les conditions de groupe sont incluses, mais les conditions de date ne le sont pas.';
 $string['availabilityrestriction_admin_desc'] = 'Les utilisateurs qui ne peuvent pas accéder à l\'activité en raison de restrictions de disponibilité seront retirés de la liste.<br> Cela inclut uniquement les conditions marquées comme appliquées aux listes d\'utilisateurs. Par exemple, les conditions de groupe sont incluses, mais les conditions de date ne le sont pas.';
 $string['modulename'] = 'Private Student Folder';
 $string['pluginname'] = 'Private Student Folder';
 $string['modulename_help'] = 'Le Private Student Folder offre les fonctionnalités suivantes :<br><ul><li>Les étudiants peuvent téléverser des fichiers.</li><li>Les fichiers seront disponibles pour l\'étudiant lui-même (ou également pour les autres étudiants, si le partage est autorisé) automatiquement ou après approbation des enseignants.</li><li>Les étudiants et/ou les enseignants recevront une notification lorsque des fichiers sont téléversés ou modifiés par les étudiants ou lorsqu\'un fichier est importé ou mis à jour depuis une activité de devoir. De plus, les étudiants et/ou les enseignants recevront une notification concernant tout changement de statut du Private Student Folder.</li></ul>';

$string['eventprivatestudentfolderfiledeleted'] = 'Private Student Folder file delete';
$string['eventprivatestudentfolderfileuploaded'] = 'Private Student Folder file upload';
$string['eventprivatestudentfolderfileimported'] = 'Private Student Folder file import';
$string['eventprivatestudentfolderduedateextended'] = 'Private Student Folder due-date extended';
$string['eventprivatestudentfolderapprovalchanged'] = 'Private Student Folder file approval changed';

$string['modulenameplural'] = 'Private Student Folders';
$string['pluginadministration'] = 'Administration du Private Student Folder';
$string['privatestudentfolder:addinstance'] = 'Ajouter un nouveau Private Student Folder';
$string['privatestudentfolder:view'] = 'Voir le Private Student Folder';
$string['privatestudentfolder:upload'] = 'Téléverser des fichiers dans un Private Student Folder';
$string['privatestudentfolder:approve'] = 'Décider si les fichiers doivent être approuvés (visibles pour son propriétaire (et autres étudiants))';
$string['privatestudentfolder:grantextension'] = 'Accorder une prolongation';
$string['privatestudentfolder:manageoverrides'] = 'Gérer les dérogations';
$string['privatestudentfolder:receiveteachernotification'] = 'Recevoir des notifications pour les enseignants';
$string['search:activity'] = 'Private Student Folder - informations sur l\'activité';

$string['messageprovider:privatestudentfolder_updates'] = 'Notifications Private Student Folder';

$string['notifications'] = 'Notifications';
$string['notifyteacher'] = 'Notifier les enseignants des fichiers soumis';
$string['notifystudents'] = 'Notifier les étudiants des changements dans un Private Student Folder';
$string['notifyteacher_help'] = 'Si activé, les enseignants recevront une notification lorsque des étudiants téléversent un fichier.';
$string['notifystudents_help'] = 'Si activé, les étudiants recevront une notification lorsque le statut de leur fichier téléveversé dans un Private Student Folder change.';

$string['notify:setting:0'] = 'Aucune notification';
$string['notify:setting:1'] = 'Enseignants uniquement';
$string['notify:setting:2'] = 'Étudiants uniquement';
$string['notify:setting:3'] = 'Enseignants et étudiants';
$string['notify:statuschange'] = 'Notifications des changements de statut du Private Student Folder';
$string['notify:statuschange_help'] = 'Dépend de la configuration, si activé, les étudiants et/ou les enseignants recevront une notification lorsque le statut du Private Student Folder d\'un des fichiers est modifié.';
$string['notify:statuschange_admin'] = 'Paramètre de notification par défaut pour les changements de statut du Private Student Folder';
$string['notify:filechange'] = 'Notifications des fichiers soumis ou importés';
$string['notify:filechange_help'] = 'Dépend de la configuration, si activé, les étudiants et/ou les enseignants recevront une notification lorsque des étudiants téléversent ou modifient un fichier, ou lorsqu\'un fichier est importé ou mis à jour depuis une activité de devoir.';
$string['notify:filechange_admin'] = 'Paramètre de notification par défaut pour les changements de fichiers - soumis ou importés';

$string['email:statuschange:header'] = 'Le status du/des fichiers suivant(s), dans un Private Student Folder, <b>\'{$a->privatestudentfolder}\'</b> a changé le {$a->dayupdated} à {$a->timeupdated} par <b>{$a->username}</b>:<br /><ul>';
$string['email:statuschange:filename'] = '<li>\'{$a->filename}\' à \'<b>{$a->apstatus}</b>\'</li>';
$string['email:statuschange:footer'] = '</ul>';
$string['email:statuschange:subject'] = 'Le status du Private Student Folder a changé';
$string['email:filechange_upload:header'] = '<b>{$a->username}</b> a téléversé le(s) fichier(s) suivant(s) dans <b>\'{$a->privatestudentfolder}\'</b> le {$a->dayupdated} à {$a->timeupdated}:<br /><ul>';
$string['email:filechange_upload:subject'] = 'Fichier(s) téléversés';
$string['email:filechange_import:header'] = 'Le(s) fichier(s) suivants du devoir <b>\'{$a->assign}\'</b> a/ont été importés dans <b>\'{$a->privatestudentfolder}\'</b> le {$a->dayupdated} à {$a->timeupdated}:<br /><ul>';
$string['email:filechange_import:subject'] = 'Fichier(s) importé';
$string['email:filechange:footer'] = '</ul><br />Merci de vérifier si votre permission pour Private Student Folder est requise.';

$string['uploaded'] = 'Téléversé';
$string['approvalchange'] = 'Le status du Private Student Folder a changé';

$string['approvalsettings'] = 'Paramètres d\'approbation';
$string['name'] = 'Nom';
$string['obtainstudentapproval'] = 'Approbation de l\'étudiant';
$string['obtainstudentapproval_admin'] = 'Paramètre par défaut pour l\'approbation des étudaints.';
$string['obtainstudentapproval_admin_desc'] = 'Ce paramètre détermine le paramètre d\'approbation par défaut pour les étudiants.';
$string['obtainstudentapproval_help'] = 'Cette option détermine comment les fichiers téléversés par les étudiants sont partagés (si l\'option est activée) : <ul><li><strong>Automatique</strong> - les fichiers soumis sont automatiquement partagés.</li><li>Nécessaire : les étudiants doivent manuellement approuver partager un/des fichier(s) aux autres étudiants.</li></ul>';

$string['filesarepersonal'] = 'Les fichiers sont privés/personels';
$string['filesarepersonal_yes'] = 'Oui (les fichiers ne sont disponibles qu\'à leur propriétaire)';
$string['filesarepersonal_no'] = 'Non (les fichiers sont ou peuvent être partagés avec les autres étudiants)';
$string['filesarepersonal_admin'] = 'Les fichiers sont privés/personels';
$string['filesarepersonal_admin_desc'] = 'Ce paramètre détermine le paramètre par défaut de visibilité pour les fichiers des étudiants.';
$string['filesarepersonal_help'] = 'Cette option détermine si les fichiers soumis restent privés pour leur propriétaire :<br><ul><li><strong>Oui</strong> - les fichiers sont privés et personnels.</li><li><strong>Non</strong> - les fichiers peuvent être partagés entre étudiants, s\'ils sont approuvés par les parties concernées si nécessaire.</li></ul>';

$string['myownfiles'] = 'Mes fichiers';

$string['obtainteacherapproval'] = 'Approbation de l\'enseignant';
$string['obtainteacherapproval_help'] = 'Cette option détermine comment la visibilité des fichiers soumis est gérée, dans le Private Student Folder, selon l\'approbation de l\'enseignant :<br><ul><li><strong>Automatique</strong> - aucune approbation de la part des enseignants n\'est requise.</li><li><strong>Requise</strong> - les enseignants doivent approuver manuellement le fichier pour qu\'il puisse être disponible à quiconque.</li></ul>';
$string['obtainteacherapproval_no'] = 'Automatique';
$string['obtainteacherapproval_yes'] = 'Requise';
$string['obtainteacherapproval_admin'] = 'Paramètre d\'approbation par défaut des enseignants';
$string['obtainteacherapproval_admin_desc'] = 'Ce paramètre détermine le paramètre d\'approbation par défaut des enseignants.';
$string['obtainstudentapproval_no'] = 'Automatique';
$string['obtainstudentapproval_yes'] = 'Requise';
$string['obtainapproval_automatic'] = 'Automatique';
$string['obtainapproval_required'] = 'Requise';
$string['obtaingroupapproval'] = 'Approbation par le groupe';
$string['obtaingroupapproval_help'] = 'Cette option détermine comment la visibilité des fichiers sourmis par le groupe est gérée :<br /><ul><li><strong>Automatique</strong> - aucune approbation des membres du groupe n\'est requise.</li><li><strong>Requise d\'au moins UN membre</strong> - au moins un membre du groupe doit approuver</li><li><strong>Requise de TOUS les membres</strong> - tous les membres du groupe doivent approuver</li></ul>';
$string['obtaingroupapproval_all'] = 'Requise de TOUS les membres';
$string['obtaingroupapproval_single'] = 'Requise d\'au moins UN membre';
$string['obtaingroupapproval_title'] = 'Approbation de groupe';
$string['obtaingroupapproval_admin'] = 'Paramètre d\'approbation par défaut des groupes';
$string['obtaingroupapproval_admin_desc'] = 'Ce paramètre détermine le paramètre d\'approbation par défaut pour les membres du groupe. Ce paramètre devient pertinent <strong>uniquement</strong> lorsque le mode est défini sur "Importer des fichiers depuis une activité de devoir" et que le devoir utilise les remises en groupe.';

$string['approvalfromdate'] = 'Approbation à partir du';
$string['approvalfromdate_help'] = 'Le statut d\'approbation ne peut pas être modifié avant cette date. Ce paramètre est uniquement pertinent lorsque l\'approbation par les étudiants ou le groupe n\'est pas automatique.';
$string['approvaltodate'] = 'Approbation jusqu\'au';
$string['approvaltodate_help'] = 'Le statut d\'approbation ne peut pas être modifié après cette date. Ce paramètre est uniquement pertinent lorsque l\'approbation par les étudiants ou le groupe n\'est pas automatique.';
$string['approvaltodatevalidation'] = 'La date "Approbation jusqu\'au" doit être postérieure à la date "Approbation à partir du".';
$string['maxfiles'] = 'Nombre maximum de fichiers joints';
$string['maxfiles_help'] = 'Chaque étudiant pourra téléverser jusqu\'à ce nombre de fichiers pour sa remise.';
$string['configmaxfiles'] = 'Nombre maximum de fichiers joints autorisés par défaut par utilisateur.';
$string['maxbytes'] = 'Taille maximale des fichiers joints';
$string['maxbytes_help'] = 'Les fichiers téléversés par les étudiants peuvent avoir une taille maximale correspondant à cette valeur.';
$string['configmaxbytes'] = 'Taille maximale par défaut pour tous les fichiers dans le Private Student Folder.';
$string['uploadnotopen'] = 'Le téléversement de fichiers est fermé !';

$string['reset_userdata'] = 'Toutes les données';

// Strings from the file mod_form
$string['configautoimport'] = 'Si vous souhaitez que les remises des étudiants soient automatiquement importées dans les instances de Private Student Folder. Cette fonctionnalité peut être activée/désactivée séparément pour chaque instance de Private Student Folder.';
$string['availability'] = 'Période d\'édition (téléversement ou approbation)';
$string['submissionsettings'] = 'Paramètres de remise';
$string['allowsubmissionsfromdate'] = 'À partir du';
$string['allowsubmissionsfromdate_help'] = 'Si cette option est activée, les participants ne pourront pas soumettre leurs fichiers avant cette date. Si l\'option est désactivée, les participants pourront commencer à soumettre immédiatement.';
$string['allowsubmissionsfromdatesummary'] = 'Cette activité acceptera les remises à partir du <strong>{$a}</strong>';
$string['allowsubmissionsanddescriptionfromdatesummary'] = 'Les détails de l\'activité et le formulaire de remise seront disponibles à partir du <strong>{$a}</strong>';
$string['alwaysshowdescription'] = 'Toujours afficher la description';
$string['alwaysshowdescription_help'] = 'Si désactivée, la description de l\'activité ci-dessus ne sera visible par les étudiants qu\'à partir de la date "Téléversement/Approbation à partir du".';

$string['duedate'] = 'Jusqu\'à';
$string['duedate_help'] = 'Si cette option est activée, les participants ne pourront pas soumettre leurs fichiers après cette date. Si l\'option est désactivée, les participants peuvent soumettre indéfiniment.';
$string['duedatevalidation'] = 'La date limite doit être postérieure à la date de début des remises autorisées.';

$string['cutoffdate'] = 'Date de fin';
$string['cutoffdate_help'] = 'Si définie, l\'activité n\'acceptera pas de remises après cette date sans prolongation.';
$string['cutoffdatevalidation'] = 'La date de fin ne peut pas être antérieure à la date limite.';
$string['cutoffdatefromdatevalidation'] = 'La date de fin doit être postérieure à la date de début des remises autorisées.';

$string['mode'] = 'Mode';
$string['mode_help'] = 'Choisissez si les étudiants peuvent téléverser directement leurs documents ici ou si leurs remises d\'une activité doivent être importées.';
$string['modeupload'] = 'Téléverser les fichiers directement dans cette activité';
$string['modeimport'] = 'Importer les fichiers depuis une activité de remise';

$string['courseuploadlimit'] = 'Limite de téléversement du cours';
$string['allowedfiletypes'] = 'Types de fichiers acceptés';
$string['allowedfiletypes_help'] = 'Les types de fichiers acceptés peuvent être limités en saisissant une liste de types MIME séparés par des virgules, par exemple \'video/mp4, audio/mp3, image/png, image/jpeg\', ou des extensions de fichiers avec un point, par exemple \'.png, .jpg\'. Si le champ est vide, tous les types de fichiers sont autorisés.';
$string['allowedfiletypes_err'] = 'Vérifiez la saisie ! Extensions de fichiers ou séparateurs invalides.';

$string['currentlynotapproved'] = '* Actuellement non approuvé ou rejeté pour Private Student Folder.';

$string['teacherapproval_help'] = 'Approbation ou rejet actuel des fichiers, c’est-à-dire s’ils sont visibles par tous les participants : <br><ul><li><strong>Choisir…</strong> - décision en attente / aucune approbation donnée ou rejeté, ces fichiers ne sont pas visibles.</li><li><strong>Approuver</strong> - approbation accordée, ces fichiers sont publiés et donc visibles par tous.</li><li><strong>Rejeter</strong> - aucune approbation donnée, ces fichiers ne sont pas publiés et donc non visibles.</li></ul>';
$string['assignment'] = 'Activité';
$string['assignment_help'] = 'Choisissez l\'activité depuis laquelle importer les fichiers des remises individuelles ou de groupe.';
$string['choose'] = 'Veuillez choisir ...';
$string['importfrom_err'] = 'Vous devez choisir une activité depuis laquelle importer les remises de fichiers.';
$string['nonexistentfiletypes'] = 'Les types de fichiers suivants n\'ont pas été reconnus : {$a}';

$string['completionupload'] = 'L’étudiant doit téléverser un fichier';
$string['completiondetail:upload'] = 'Téléverser un fichier';

// Strings from file mod_privatestudentfolder_grantextension_form.php
$string['extensionduedate'] = 'Date limite de prolongation';
$string['extensionnotafterduedate'] = 'La date de prolongation doit être postérieure à la date limite';
$string['extensionnotafterfromdate'] = 'La date de prolongation doit être postérieure à la date de début des remises autorisées';

// Strings from file index.php
$string['noprivatestudentfoldersincourse'] = 'Il n\'y a aucune instance de Private Student Folder dans ce cours.';

// Strings from file view.php
$string['allowsubmissionsfromdate_upload'] = 'Téléversement à partir de';
$string['allowsubmissionsfromdate_import'] = 'Approbation à partir de';
$string['duedate_upload'] = 'Téléversement jusqu\'à';
$string['duedate_import'] = 'Approbation jusqu\'à';
$string['cutoffdate_upload'] = 'Dernier téléversement jusqu\'à';
$string['cutoffdate_import'] = 'Dernière approbation jusqu\'à';
$string['extensionto'] = 'Prolongation jusqu\'à';
$string['filedetails'] = 'Détails';
$string['assignment_notfound'] = 'L\'activité depuis laquelle les fichiers ont été importés est introuvable.';
$string['assignment_notset'] = 'Aucune activité n\'a été choisie.';
$string['updatefiles'] = 'Mettre à jour les fichiers';
$string['updatefileswarning'] = 'Les fichiers déjà importés seront remplacés ou supprimés si les fichiers originaux dans l\'activité ont été actualisés ou supprimés. Les paramètres de l\'étudiant, comme l\'approbation pour publication, restent inchangés.';
$string['myfiles'] = 'Mes fichiers';
$string['mygroupfiles'] = 'Fichiers de mon groupe';
$string['add_uploads'] = 'Ajouter des fichiers';
$string['edit_uploads'] = 'Modifier/téléverser des fichiers';
$string['edit_timeover'] = 'Les fichiers ne peuvent être modifiés que pendant la période d\'édition.';
$string['approval_timeover'] = 'Vous ne pouvez modifier votre consentement que pendant la période d\'édition.';
$string['noentries'] = 'Aucune entrée';
$string['nofiles'] = 'Aucun fichier disponible';
$string['nothing_to_show_users'] = 'Rien à afficher - aucun étudiant disponible';
$string['nothing_to_show_groups'] = 'Rien à afficher - aucun groupe disponible';
$string['notice'] = '<strong>Note : </strong>';

$string['notice_upload_filesarepersonal_teacherrequired'] = 'Tous les fichiers que vous téléversez ici seront personnels et visibles uniquement par vous <strong>après l\'approbation des enseignants.</strong> Les enseignants se réservent le droit de rejeter la publication de vos fichiers à tout moment.';
$string['notice_upload_filesarepersonal_teachernotrequired'] = 'Tous les fichiers que vous téléversez ici seront personnels et visibles automatiquement par vous <strong>automatiquement.</strong>';

$string['notice_upload_studentrequired_teacherrequired'] = 'Tous les fichiers que vous téléversez ici seront publiés (visibles par tous) <strong>après votre approbation et celle des enseignants.</strong> Les enseignants se réservent le droit de rejeter la publication de vos fichiers à tout moment.';
$string['notice_upload_studentrequired_teachernotrequired'] = 'Tous les fichiers que vous téléversez ici seront publiés (visibles par tous) <strong>après votre approbation.</strong>';
$string['notice_upload_studentnotrequired_teacherrequired'] = 'Tous les fichiers que vous téléversez ici seront publiés (visibles par tous) uniquement <strong>après l\'approbation des enseignants.</strong> Les enseignants se réservent le droit de rejeter la publication de vos fichiers à tout moment.';
$string['notice_upload_studentnotrequired_teachernotrequired'] = 'Tous les fichiers que vous téléversez ici seront publiés (visibles par tous) <strong>automatiquement.</strong>';

$string['notice_import_studentrequired_teacherrequired'] = 'Les fichiers seront publiés (visibles par tous) après <strong>votre approbation et celle des enseignants.</strong> Les enseignants se réservent le droit de rejeter la publication de vos fichiers à tout moment.';
$string['notice_import_studentrequired_teachernotrequired'] = 'Les fichiers seront publiés (visibles par tous) après <strong>votre approbation.</strong>';
$string['notice_import_studentnotrequired_teacherrequired'] = 'Les fichiers seront publiés (visibles par tous) uniquement <strong>après l\'approbation des enseignants.</strong> Les enseignants se réservent le droit de rejeter la publication de vos fichiers à tout moment.';
$string['notice_import_studentnotrequired_teachernotrequired'] = 'Les fichiers seront publiés (visibles par tous) <strong>automatiquement.</strong>';

$string['notice_group_all_teacherrequired'] = 'Les fichiers ne seront publiés pour tous les étudiants qu\'avec l\'approbation de <strong>TOUS les membres du groupe et de l\'enseignant.</strong> Les enseignants se réservent le droit de rejeter la publication de vos fichiers à tout moment.';
$string['notice_group_all_teachernotrequired'] = 'Les fichiers ne seront publiés pour tous les étudiants qu\'avec l\'approbation de <strong>TOUS les membres du groupe.</strong>';
$string['notice_group_one_teacherrequired'] = 'Les fichiers ne seront publiés pour tous les étudiants qu\'avec l\'approbation d\'<strong>AU MOINS UN membre du groupe et de l\'enseignant.</strong> Les enseignants se réservent le droit de rejeter la publication de vos fichiers à tout moment.';
$string['notice_group_one_teachernotrequired'] = 'Les fichiers ne seront publiés pour tous les étudiants qu\'avec l\'approbation d\'<strong>AU MOINS UN membre du groupe.</strong>';

$string['notice_files_imported'] = 'Les fichiers affichés sont importés depuis une activité de devoir.';
$string['notice_files_imported_group'] = 'Les fichiers affichés proviennent d\'une remise de groupe, importée depuis une activité de devoir.';
$string['notice_changes_possible_in_original'] = 'Les modifications des fichiers existants ne sont possibles que dans l\'activité de devoir originale.';

// Strings for approval
$string['notice_obtainteacherapproval_studentsapproval'] = 'Dans l\'esprit de la législation sur le droit d\'auteur, nous vous demandons de solliciter l\'approbation des participants pour publier des fichiers par un autre moyen.';

$string['notice_obtainapproval_import_both'] = 'En tant qu\'enseignant, vous pouvez rejeter l\'approbation pour le privatestudentfolder à tout moment si un fichier ne respecte pas les exigences définies.';
$string['notice_obtainapproval_import_studentonly'] = 'Dans l\'esprit de la législation sur le droit d\'auteur, nous vous demandons de solliciter l\'approbation des étudiants pour publier des fichiers par un autre moyen.<br>En tant qu\'enseignant, vous pouvez rejeter l\'approbation pour le privatestudentfolder à tout moment si un fichier ne respecte pas les exigences définies.';
$string['notice_obtainapproval_upload_teacher'] = 'Dans l\'esprit de la législation sur le droit d\'auteur, nous vous demandons de solliciter l\'approbation des étudiants pour publier des fichiers par un autre moyen.<br>En tant qu\'enseignant, vous pouvez rejeter l\'approbation pour le privatestudentfolder à tout moment si un fichier ne respecte pas les exigences définies.';
$string['notice_obtainapproval_upload_automatic'] = 'Dans l\'esprit de la législation sur le droit d\'auteur, nous vous demandons de solliciter l\'approbation des étudiants pour publier des fichiers par un autre moyen.<br>En tant qu\'enseignant, vous pouvez rejeter l\'approbation pour le privatestudentfolder à tout moment si un fichier ne respecte pas les exigences définies.';

$string['teacher_pending'] = 'Décision de l\'enseignant en attente.';
$string['teacher_approved'] = 'Approuvé par l\'enseignant.';
$string['teacher_approved_automatically'] = 'Approuvé automatiquement par l\'enseignant.';
$string['teacher_rejected'] = 'Non publié (rejeté).';
$string['teacher_approve'] = 'Approuver';
$string['teacher_reject'] = 'Rejeter';
$string['approved'] = 'Approuvé';
$string['show_details'] = 'Afficher les détails';
$string['student_approve'] = 'Partager';
$string['student_approved'] = 'Approuvé pour partage par l\'étudiant.';
$string['group_approved'] = 'Approuvé par tous les membres du groupe.';
$string['student_approved_automatically'] = 'Approuvé pour partage automatiquement par l\'étudiant.';
$string['student_pending'] = 'Décision de l\'étudiant en attente.';
$string['pending'] = 'En attente';
$string['student_reject'] = 'Rejeter';
$string['student_rejected'] = 'Rejeté par l\'étudiant.';
$string['rejected'] = 'Rejeté';
$string['visible'] = 'Publié';
$string['hidden'] = 'Non publié';
$string['status:approved'] = 'Approuvé';
$string['status:approvednot'] = 'Rejeté';
$string['status:approvedrevoke'] = 'Révoqué';
$string['giveapproval'] = 'Donner l\'approbation';
$string['overdue'] = 'Délai de modification dépassé';
$string['approval_required'] = 'Décision en attente';
$string['privatestudentfolderstatus'] = 'Publication';
$string['privatestudentfolderstatus_help'] = 'Le statut de publication représente l\'approbation de l\'enseignant et le statut final de la publication : <ul><li><i class="fa fa-check text-success fa-fw"></i> Le fichier est publié et donc visible pour son auteur et éventuellement pour d\'autres participants</li><li><i class="fa fa-times text-danger fa-fw"></i> Le fichier n\'est pas publié (approbation non encore donnée ou rejetée) et donc non visible</li></ul>';

$string['allfiles'] = 'Soumissions de fichiers';
$string['publicfiles'] = 'Fichiers publiés';
$string['downloadall'] = 'Télécharger toutes les soumissions de fichiers';
$string['optionalsettings'] = 'Options';
$string['entiresperpage'] = 'Participants affichés par page';
$string['nothingtodisplay'] = 'Aucune entrée à afficher';
$string['nofilestodisplay'] = 'Il n\'y a actuellement aucun fichier disponible ou publié.';
$string['nofilestozip'] = 'Aucun fichier à compresser';
$string['status'] = 'Statut';
$string['studentapproval'] = 'Approbation (étudiants)';
$string['studentapproval_help'] = 'Dans la colonne « Approbation (étudiants) », le retour des étudiants est affiché :<br><ul><li><i class="fa fa-question fa-fw text-warning"></i> - Décision en attente</li><li><i class="fa fa-check text-success fa-fw"></i> - Approbation donnée</li><li><i class="fa fa-times text-danger fa-fw"></i> - Approbation refusée</li></ul>';
$string['teacherapproval'] = 'Approbation';
$string['visibility'] = 'Publication';
$string['visibleforstudents'] = 'Publié';
$string['visibleforstudents_yes'] = 'Ce fichier est publié (visible pour les étudiants).';
$string['visibleforstudents_no'] = 'Ce fichier n\'est pas publié (non visible pour les étudiants).';
$string['resetstudentapproval'] = 'Annuler l\'approbation des étudiants';
$string['savestudentapprovalwarning'] = 'Êtes-vous sûr de vouloir enregistrer ces modifications ? Le statut du privatestudentfolder ne peut pas être modifié une fois défini.';

$string['go'] = 'Valider';
$string['withselected'] = 'Avec la sélection...';
$string['zipusers'] = 'Télécharger les soumissions de fichiers sélectionnées';
$string['approveusers'] = 'Donner l\'approbation';
$string['rejectusers'] = 'Rejeter';
$string['grantextension'] = 'Accorder une prolongation';
$string['saveteacherapproval'] = 'Enregistrer les modifications';
$string['reset'] = 'Annuler';

// Strings from the file upload.php
$string['filesofthesetypes'] = 'Les fichiers de ces types peuvent être ajoutés :';
$string['guideline'] = 'Dossier privé étudiant des fichiers soumis';
$string['published_immediately'] = 'Approbation automatique';
$string['published_aftercheck'] = 'Approbation requise des enseignants';
$string['save_changes'] = 'Enregistrer les modifications';

$string['overview'] = 'Aperçu';

// Strings for JS
$string['total'] = 'Total';
$string['details'] = 'Détails';

// Strings for privacy-API
$string['privacy:metadata:privatestudentfolderperpage'] = 'Nombre d’entrées affichées par page dans un tableau.';
$string['privacy:path:files'] = 'Fichiers';
$string['privacy:path:resources'] = 'Ressources';
$string['privacy:type:upload'] = 'Fichier téléchargé';
$string['privacy:type:import'] = 'Fichier importé';
$string['privacy:type:onlinetext'] = 'Texte en ligne importé';
$string['privacy:metadata:groupapproval'] = 'Stocke les informations sur l’approbation ou le rejet des fichiers par les membres du groupe, importées d’une soumission de groupe.';
$string['privacy:metadata:privatestudentfolderfileexplanation'] = 'Les fichiers et les soumissions de texte en ligne converties pour ce plugin sont stockés via l’API des fichiers de Moodle.';
$string['privacy:metadata:extduedates'] = 'Stocke les informations sur les dates d’échéance modifiées ou prolongées pour mod_privatestudentfolder.';
$string['privacy:metadata:files'] = 'Stocke des informations (identifiant, propriétaire, origine, hash du contenu, nom du fichier, et si approuvé par l’enseignant et/ou l’étudiant) sur les fichiers téléchargés/importés dans mod_privatestudentfolder.';
$string['privacy:metadata:fileid'] = 'Identifiant du fichier.';
$string['privacy:metadata:userid'] = 'Identifiant de l’utilisateur.';
$string['privacy:metadata:timecreated'] = 'Date et heure de création de l’enregistrement de données.';
$string['privacy:metadata:timemodified'] = 'Date et heure de la dernière mise à jour/modification de l’enregistrement de données.';
$string['privacy:metadata:approval'] = 'Indique si le membre du groupe a approuvé ou rejeté pour Private Student Folder.';
$string['privacy:metadata:studentapproval'] = 'Indique si l’étudiant a approuvé ou rejeté le Private Student Folder d’un fichier.';
$string['privacy:metadata:teacherapproval'] = 'Indique si l’enseignant a approuvé ou rejeté le Private Student Folder d’un fichier.';
$string['privacy:metadata:type'] = 'Origine du fichier (téléchargé par l’étudiant, importé d’une soumission d’assignement ou texte en ligne converti d’une soumission).';
$string['privacy:metadata:contenthash'] = 'Hash SHA1 du contenu du fichier, utilisé pour déterminer si le fichier a changé.';
$string['privacy:metadata:filename'] = 'Nom du fichier.';
$string['privacy:metadata:extensionduedate'] = 'Date d’échéance effective pour les étudiants suite à une prolongation/modification.';

// Strings for filters
$string['filter'] = 'Filtrer';
$string['filter:nofilter'] = 'Sans filtre';
$string['filter:allfiles'] = 'Toutes les soumissions de fichiers';
$string['filter:approved'] = 'Soumissions de fichiers approuvées';
$string['filter:rejected'] = 'Soumissions de fichiers rejetées';
$string['filter:approvalrequired'] = 'Décision en attente';
$string['filter:nofiles'] = 'Aucune soumission de fichier';

// Strings for overrides
$string['eventoverridecreated'] = 'Prolongation du dossier étudiant personnel créée';
$string['eventoverridedeleted'] = 'Prolongation du dossier étudiant personnel supprimée';
$string['eventoverrideupdated'] = 'Prolongation du dossier étudiant personnel mise à jour';
$string['override:add:group'] = 'Ajouter une prolongation de groupe';
$string['override:add:user'] = 'Ajouter une prolongation utilisateur';
$string['overrides:empty'] = 'Aucune prolongation';
$string['override:save:success'] = 'Prolongation enregistrée avec succès';
$string['override:invalidid'] = 'ID de prolongation invalide';
$string['override:submission:fromto'] = 'Autoriser les soumissions du {$a->from} au {$a->to}';
$string['override:submission:from'] = 'Autoriser les soumissions à partir du {$a->from}';
$string['override:submission:to'] = 'Autoriser les soumissions jusqu’au {$a->to}';
$string['override:approval:fromto'] = 'Approbation du {$a->from} au {$a->to}';
$string['override:approval:from'] = 'Approbation à partir du {$a->from}';
$string['override:approval:to'] = 'Approbation jusqu’au {$a->to}';
$string['override:group:choose'] = 'Choisissez un groupe';
$string['override:user:choose'] = 'Choisissez un utilisateur';
$string['override:nothingtochange'] = 'Aucun paramètre ne peut être modifié avec les réglages actuels de l’activité !';
$string['override:delete:ask'] = 'Êtes-vous sûr de vouloir supprimer la prolongation pour {$a->userorgroup} {$a->fullname} ?';
$string['override:delete:success'] = 'Prolongation supprimée avec succès !';
