/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

/**
 * Widget Element
 *
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 */
define([
    '$',
    'uiClass',
    'mage/adminhtml/wysiwyg/widget'
], function ($, Class) {
    // preserve original function
    // noinspection AmdModulesDependencies
    if (!widgetTools.openDialog_original) {
        // noinspection AmdModulesDependencies
        widgetTools.openDialog_original = widgetTools.openDialog;
    }

    // override widget dialog opener, magento doesn't allow opening 2+ instances per default
    // noinspection AmdModulesDependencies
    widgetTools.openDialog = function () {
        // fake dialog to be not open yet
        this.dialogOpened = false;

        // open another instance
        this.openDialog_original.apply(this, arguments);

        // set close function to only close current window
        this.dialogWindow = {
            modal: function () {
                $('.modals-wrapper .modal-slide._show .action-close').last().trigger('click');
            }
        };
    };

    // preserve original function
    // noinspection AmdModulesDependencies
    if (!MediabrowserUtility.openDialog_original) {
        // noinspection AmdModulesDependencies
        MediabrowserUtility.openDialog_original = MediabrowserUtility.openDialog;
    }

    // fix modal open function of media browser to not override the active window
    // noinspection AmdModulesDependencies
    MediabrowserUtility.openDialog = function () {
        this.modal = null;
        this.openDialog_original.apply(this, arguments);
    };

    return Class.extend({
        /**
         * Initializes the Widget object
         *
         * @returns {exports}
         */
        initialize: function () {
            // noinspection JSUnresolvedFunction
            this._super();
            this.initWidget.apply(this, arguments);
            return this;
        },

        /**
         * Initializes the magento widget chooser
         */
        initWidget: function () {
            // get params
            // noinspection JSUnresolvedFunction
            let params = Array.prototype.slice.call(arguments);

            // set global variable name
            let widgetInstance = params[0];

            // build widget
            // noinspection AmdModulesDependencies
            window[widgetInstance] = new (WysiwygWidget.Widget.bind.apply(WysiwygWidget.Widget, params));

            /**
             * Inserts the widget into the parent element
             */
            window[widgetInstance].insertWidget = window[widgetInstance].insertWidget.wrap(function (proceed) {
                // get current form
                let form = $('#' + this.formEl);

                // define control name
                let wysiwygControlName = '.admin__control-wysiwig';

                // get all wysiwyg editors
                let wysiwyg = form.find(wysiwygControlName).find('textarea').filter('textarea:hidden');

                // iterate over editors
                wysiwyg.each(function () {
                    // close them before saving
                    $(this).parent(wysiwygControlName).find('.action-show-hide').click();
                });

                // execute original function
                proceed();
            });

            /**
             * Inserts the widget into the parent element
             */
            window[widgetInstance].validateField = window[widgetInstance].validateField.wrap(function (proceed) {
                proceed();
                $('[id=insert_button]').removeClass('disabled');
            });
        }
    });
});
