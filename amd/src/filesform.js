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
 * Resets checked checkboxes after ZIP file was loaded!
 *
 * @module        mod_openbook/filesform
 * @author        University of Geneva, E-Learning Team
 * @author        Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @copyright     2025 University of Geneva {@link http://www.unige.ch}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module mod_openbook/filesform
 */
define(['jquery', 'core/log', 'core/notification', 'core/str'], function($, log, Notification, Str) {

    /**
     * @constructor
     * @alias module:mod_openbook/modform
     */
    var Filesform = function() {
        this.form = $('#fastg');
        this.menuaction = $('#menuaction');
        this.usersel = $('.userselection');
        this.attemptstable = $('table#attempts');
    };

    var instance = new Filesform();

    instance.initializer = function() {
        log.info('Initialize filesform JS!', 'mod_openbook');
        instance.form.on('submit', function() {
            if (instance.menuaction.val() === 'zipusers') {
                setTimeout(function() {
                    instance.usersel.prop('checked', false);
                }, 100);
            }
        });

        // Auto-submit selects tagged by mod_openbook_allfiles_form (group filter, etc.).
        $(document).on('change', 'select[data-mod-openbook="autosubmit-select"]', function() {
            var $form = $(this).closest('form');
            if ($form.length) {
                $form[0].submit();
            }
        });

        // Auto-submit the per-page / filter preferences form.
        $(document).on('change', 'select[data-mod-openbook="optionspref-autosubmit"]', function() {
            $('form.optionspref').first().submit();
        });

        // "Select all/none" master checkbox toggles every .userselection checkbox.
        $(document).on('click change', '#selectallnone', function() {
            $('.userselection').prop('checked', this.checked);
        });

        // Submit buttons that require a confirmation prompt — uses Moodle's core/notification
        // modal instead of window.confirm() (ESLint no-alert) and is CSP-friendly.
        $(document).on('click', '[data-mod-openbook="confirm-submit"]', function(e) {
            var button = this;
            if ($(button).data('mod-openbook-confirmed') === true) {
                // Already confirmed — let the form submit proceed.
                return true;
            }
            var message = $(button).data('mod-openbook-confirm-message') || '';
            if (!message) {
                return true;
            }
            e.preventDefault();
            Str.get_strings([
                {key: 'confirm', component: 'moodle'},
                {key: 'yes', component: 'moodle'},
                {key: 'no', component: 'moodle'},
            ]).then(function(strings) {
                return Notification.confirm(strings[0], message, strings[1], strings[2], function() {
                    $(button).data('mod-openbook-confirmed', true);
                    $(button).closest('form').trigger('submit');
                });
            }).catch(Notification.exception);
            return false;
        });
        if (this.attemptstable.length > 0) {
            var $rows = this.attemptstable.children('tbody').children('tr');
            var needsapprovalcount = 0;
            $rows.each(function() {
                var $this = $(this);
                var $checkbox = $this.find('.permissionstable select.custom-select');
                if ($checkbox.length > 0) {
                    $checkbox.each(function() {
                         var $c = $(this);
                         if ($c.val() === '') {
                             if (!$c.hasClass('needs-approval')) {
                                 $c.addClass('needs-approval');
                                 needsapprovalcount++;
                                 $c.after('<span class="needs-approval-asterisk">*</span>');
                             }
                         }
                    });
                }
            });
            if (needsapprovalcount > 0) {
                $('.needsapproval-legend').removeClass('d-none');
            }
        }

    };

    return instance;
});
