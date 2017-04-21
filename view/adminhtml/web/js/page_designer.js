/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

/**
 * Page Designer
 *
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
define([
    'jquery',
    'Magento_Ui/js/form/element/abstract',
    'Magenerds_PageDesigner/js/pdClass',
    'mage/adminhtml/wysiwyg/widget',
    'mage/adminhtml/wysiwyg/tiny_mce/setup'
], function (jQuery, Abstract, PageDesigner) {
    'use strict'; // NOSONAR

    // preserve original function
    if (!widgetTools.openDialog_original) {
        widgetTools.openDialog_original = widgetTools.openDialog;
    }

    // override widget dialog opener, magento doesn't allow opening 2+ instances per default
    widgetTools.openDialog = function () {
        // fake dialog to be not open yet
        this.dialogOpened = false;

        // open another instance
        this.openDialog_original.apply(this, arguments);

        // set close function to only close current window
        this.dialogWindow = {
            modal: function () {
                jQuery('.modals-wrapper .modal-slide._show .action-close').last().trigger('click');
            }
        };
    };

    // override TinyMCE 'setup' function for fix PageDesigner and WYSIWYG editor bug
    tinyMceWysiwygSetup.prototype.setup = function (mode) {
        if (this.config.widget_plugin_src) {
            tinyMCE.PluginManager.load('magentowidget', this.config.widget_plugin_src);
        }

        if (this.config.plugins) {
            this.config.plugins.each(function (plugin) {
                tinyMCE.PluginManager.load(plugin.name, plugin.src);
            });
        }

        /**
         * Magento 2 Core Bug
         * Load TinyMce Editor in dom
         * BugFix for PageDesigner
         */
        if (jQuery.isReady) {
            tinyMCE.dom.Event.domLoaded = true;
        }

        tinyMCE.init(this.getSettings(mode));
    };

    // preserve original function
    if (!MediabrowserUtility.openDialog_original) {
        MediabrowserUtility.openDialog_original = MediabrowserUtility.openDialog;
    }

    // fix modal open function of media browser to not override the active window
    MediabrowserUtility.openDialog = function () {
        this.modal = null;
        this.openDialog_original.apply(this, arguments);
    };

    // generate class
    return Abstract.extend({
        defaults: {
            elementSelector: 'textarea',
            value: '',
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            },
            template: 'Magenerds_PageDesigner/page_designer/page_designer',
            elementTmpl: 'Magenerds_PageDesigner/page_designer/page_designer',
            content: '',
            showSpinner: false,
            loading: false,
            listens: {
                'value': 'onValueChange'
            }
        },
        /**
         * Initializes the ui element
         */
        initialize: function () {
            // preserve instance
            var pd = this;

            // set promise
            this.importPromise = new Promise(function (resolve) {
                pd.importPromiseResolve = resolve;
            });

            // preserve editor setup constructor
            if (!tinyMceWysiwygSetup.prototype.initialize_original) {
                tinyMceWysiwygSetup.prototype.initialize_original = tinyMceWysiwygSetup.prototype.initialize;
            }

            // override original constructor
            tinyMceWysiwygSetup.prototype.initialize = function () {
                // call and set back original constructor
                this.initialize_original.apply(this, arguments);

                // reset function
                tinyMceWysiwygSetup.prototype.initialize = tinyMceWysiwygSetup.prototype.initialize_original;
                delete tinyMceWysiwygSetup.prototype.initialize_original;

                // call import callback
                pd.importPromise.then(function (importCallBack) {
                    importCallBack(this);
                }.bind(this));
            };

            // call parent function
            this._super();
        },
        /**
         * Gets called when the element is rendered
         *
         * @param {object} element
         */
        onElementRender: function (element) {
            // set element
            this.element = element;

            // build page designer
            this.buildPageDesigner();

            // import on promise
            this.importPromiseResolve(function (editorInstance) {
                this.pageDesigner.editorInstance = editorInstance;
                this.pageDesigner.importWithPreviews(this.importData);
            }.bind(this));
        },
        /**
         * Builds the page designer instance
         */
        buildPageDesigner: function () { // NOSONAR
            // preserve instance
            var that = this;

            // get jQuery instance of element
            var jElement = jQuery(this.element);

            // create the page designer instance
            this.pageDesigner = new PageDesigner({ // NOSONAR
                "element": jElement,
                // translations
                "i18n": {
                    "gridMode": {
                        "title": jQuery.mage.__("Switch to responsive grid mode %s")
                    },
                    "row": {
                        "add": {
                            "title": jQuery.mage.__("Add Row")
                        },
                        "move": {
                            "title": jQuery.mage.__("Move Row")
                        },
                        "delete": {
                            "title": jQuery.mage.__("Delete row"),
                            "confirmation": jQuery.mage.__("Do you REALLY want to delete the whole row? This will permanently delete all content of the different columns.")
                        }
                    },
                    "column": {
                        "add": {
                            "title": jQuery.mage.__("Add Column")
                        },
                        "move": {
                            "title": jQuery.mage.__("Move Column")
                        },
                        "settings": {
                            "title": jQuery.mage.__("Set settings for column"),
                            "prompt": jQuery.mage.__("Enter the settings for the column.")
                        },
                        "delete": {
                            "title": jQuery.mage.__("Delete column"),
                            "confirmation": jQuery.mage.__("Do you really want to delete this column? You cannot undo this.")
                        },
                        "content": {
                            "title": jQuery.mage.__("Set column content"),
                            "prompt": jQuery.mage.__("What content to set in?"),
                            "copy": {
                                "title": jQuery.mage.__("Copy content")
                            },
                            "paste": {
                                "title": jQuery.mage.__("Paste content")
                            },
                            "clear": {
                                "title": jQuery.mage.__("Clear content"),
                                "confirmation": jQuery.mage.__("Do you really want to clear the column's content?")
                            }
                        }
                    }
                },
                "events": {
                    /**
                     * onUpdate event: Saves the new generated grid json to our data handler
                     *
                     * @param {object} event
                     * @param {string} data
                     */
                    "onUpdate": function (event, data) {
                        that.value(JSON.stringify(data));
                    },
                    /**
                     * onColumnSettingsSet event: Gets called when a user clicks the settings button
                     *
                     * @param {object} column
                     * @param {string} currentSettings
                     * @param {Function} callback
                     */
                    "onColumnSettingsSet": function (column, currentSettings, callback) {
                        // preserve instance
                        var pd = this;

                        // set callback
                        window.pageDesignerConfig.settingsCallback = function (settings) {
                            callback(column, settings);
                        };

                        // open modal
                        jQuery('<div/>').modal({
                            title: jQuery.mage.__('Column Settings'),
                            type: 'slide',
                            buttons: [],
                            opened: function () {
                                // load form
                                var dialog = jQuery(this).addClass('loading magento-message');
                                new Ajax.Updater($(this), window.pageDesignerConfig.columnSettingsUrl, { // NOSONAR
                                    parameters: {object: JSON.stringify(pd.exportColumn(column))},
                                    evalScripts: true, onComplete: function () {
                                        dialog.removeClass('loading');
                                    }
                                });
                            },
                            closed: function (e, modal) {
                                modal.modal.remove();
                            }
                        }).modal('openModal');
                    },
                    /**
                     * onRowSettingsSet event: Gets called when a user clicks the settings button
                     *
                     * @param {object} row
                     * @param {string} currentSettings
                     * @param {Function} callback
                     */
                    "onRowSettingsSet": function (row, currentSettings, callback) {
                        // preserve instance
                        var pd = this;

                        // set callback
                        window.pageDesignerConfig.settingsCallback = function (settings) {
                            callback(row, settings);
                        };

                        // open modal
                        jQuery('<div/>').modal({
                            title: jQuery.mage.__('Row Settings'),
                            type: 'slide',
                            buttons: [],
                            opened: function () {
                                // load form
                                var dialog = jQuery(this).addClass('loading magento-message');
                                new Ajax.Updater($(this), window.pageDesignerConfig.rowSettingsUrl, { // NOSONAR
                                    parameters: {object: JSON.stringify(pd.exportRow(row))},
                                    evalScripts: true, onComplete: function () {
                                        dialog.removeClass('loading');
                                    }
                                });
                            },
                            closed: function (e, modal) {
                                modal.modal.remove();
                            }
                        }).modal('openModal');
                    },
                    /**
                     * onColumnContentSet event: Gets called when a user clicks the content button
                     *
                     * @param {object} column
                     * @param {string} currentContent
                     * @param {Function} callback
                     */
                    "onColumnContentSet": function (column, currentContent, callback) {
                        // set editor as a block element to be able to access it
                        var wysControl = jElement.parent().find('.admin__control-wysiwig').parent();
                        
                        if (wysControl.is(':hidden')) {
                            wysControl.css({
                                display: 'block',
                                visibility: 'hidden',
                                height: 0
                            });
                        }

                        // preserve original function
                        if (!WysiwygWidget.Widget.prototype.getWysiwygNode_original) {
                            WysiwygWidget.Widget.prototype.getWysiwygNode_original = WysiwygWidget.Widget.prototype.getWysiwygNode;
                        }

                        // pass our widget content to the widget browser
                        WysiwygWidget.Widget.prototype.getWysiwygNode = function () {
                            // reset function
                            this.getWysiwygNode = WysiwygWidget.Widget.prototype.getWysiwygNode = WysiwygWidget.Widget.prototype.getWysiwygNode_original;

                            // override the current update content function to store the generated content inside of page designer
                            this.updateContent = function (preview) {
                                preview = jQuery(preview);

                                // get widget code
                                var code = preview.attr('id');
                                if (code) {
                                    // set widget code to content
                                    callback(column, code != '' ? Base64.idDecode(code) : '', preview);
                                }
                            };

                            // return element
                            if (!!column.data('pd-content')) {
                                return column.find('.pd-col-content .pd-col-content-preview img:first-child').get(0);
                            } else {
                                return null;
                            }
                        };

                        // open the widget browser
                        wysControl.find('.mceButton.mce_magentowidget').get(0).click();
                    }
                }
            });

            // set custom import function
            this.pageDesigner.importWithPreviews = function (json) {
                // preserve instance
                var that = this;

                // transform to string
                if (typeof json === 'string' && json) {
                    json = JSON.parse(json);
                }
                // check structure
                if (json && json.rows) {
                    // add previews
                    jQuery(json.rows).each(function (ri, row) {
                        jQuery(row.columns).each(function (ci, column) {
                            // call widget encoder of editor plugin
                            if (column.content) {
                                json.rows[ri].columns[ci].preview = that.editorInstance.encodeWidgets(column.content);
                            }
                        });
                    });
                    // import data
                    this.importData = json;
                    this.import();
                }
            };

            // set instance to element
            jElement.data('pd-instance', this.pageDesigner);
        },
        /**
         * Prepares and sets the import data
         *
         * @param {String} value
         */
        onValueChange: function (value) {
            if (!this.imported) {
                this.importData = value;
                this.imported = true;
            }
        },
        /**
         * Initializes the observer
         *
         * @returns {exports}
         */
        initObservable: function () {
            this._super().observe('value');
            return this;
        }
    });
});
