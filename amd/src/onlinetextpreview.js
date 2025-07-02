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
 * JS showing detailed infos about user's approval status for group approvals in a modal window
 *
 * @module        mod_privatestudentfolder/onlinetextpreview
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module mod_privatestudentfolder/onlinetextpreview
 */
define(['jquery', 'core/modal_factory', 'core/str', 'core/ajax', 'core/log', 'core/notification'], function($,
        ModalFactory, str, ajax, log, notification) {

    /**
     * @constructor
     * @alias module:mod_privatestudentfolder/Onlinetextpreview
     */
    var Onlinetextpreview = function() {
        this.cmid = '';
    };

    var instance = new Onlinetextpreview();

    /**
     * Initialises the JavaScript for privatestudentfolder's group approval status tooltips
     *
     *
     * @param {Object} config The configuration
     */
    instance.initializer = function(config) {
        instance.cmid = config.cmid;

        log.info('Initialize onlinetextpreview JS!', 'mod_privatestudentfolder');

        // Prepare modal object!
        if (!instance.modal) {
            instance.modalpromise = ModalFactory.create({
                type: ModalFactory.types.DEFAULT,
                large: true
            });
        }

        str.get_strings([
            {key: 'preview', component: 'core'},
            {key: 'onlinetextfilename', component: 'assignsubmission_onlinetext'},
            {key: 'from', component: 'core'}
        ]).done(function(s) {
            log.info('Done loading strings...', 'mod_privatestudentfolder');
            instance.modalpromise.done(function(modal) {
                log.info('Done preparing modal', 'mod_privatestudentfolder');
                instance.modal = modal;
                $('.path-mod-privatestudentfolder table.privatestudentfolders .onlinetextpreview *').click(function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    var element = $(e.target);

                    var dataelement = element.parent();

                    var itemid;
                    try {
                        itemid = dataelement.data('itemid');
                    } catch (ex) {
                        notification.exception(ex);
                    }

                    ajax.call([
                        {
                            methodname: 'mod_privatestudentfolder_get_onlinetextpreview',
                            args: {itemid: itemid, cmid: instance.cmid},
                            done: function(data) {
                                var itemname = '';
                                if (dataelement.data('itemname').length) {
                                    itemname = ' ' + s[2].toLowerCase() + ' ' + dataelement.data('itemname');
                                }
                                instance.modal.setTitle(s[0] + ' ' + s[1] + itemname);
                                instance.modal.setBody(data);
                                instance.modal.show();
                            },
                            fail: function(ex) {
                                notification.exception(ex);
                            }
                        }
                    ]);
                });
            });
        }).fail(function(ex) {
            log.error('Error getting strings: ' + ex, 'mod_privatestudentfolder');
        });
    };

    return instance;
});
