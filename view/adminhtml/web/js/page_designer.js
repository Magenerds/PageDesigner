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
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
define([
    'jquery',
    'Magento_Ui/js/form/element/abstract',
    'Magenerds_PageDesigner/js/pdClass',
    'mage/adminhtml/wysiwyg/widget',
    'mage/adminhtml/wysiwyg/tiny_mce/setup',
    'mage/translate'
], function ($, Abstract, PageDesigner) {
    'use strict'; // NOSONAR

    /**
     * Translator
     * FIXME: does not work for our own strings for some reason
     *
     * @param {string} text
     * @returns {string}
     */
    function mageTranslate(text) {
        // noinspection JSUnresolvedVariable
        return $.mage.__(text);
    }

    /**
     * Widget encoder
     *
     * @param {string} content
     * @returns {string}
     */
    function mageWidgetEncode(content) {
        if (tinymce && tinymce.activeEditor && tinymce.activeEditor.plugins && tinymce.activeEditor.plugins.magentowidget) {
            return tinymce.activeEditor.plugins.magentowidget.encodeWidgets(content);
        }
        return '';
    }

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
            if (!window.wysiwygSetup.prototype.initialize_original) {
                window.wysiwygSetup.prototype.initialize_original = window.wysiwygSetup.prototype.initialize;
            }

            // override original constructor
            window.wysiwygSetup.prototype.initialize = function () {
                // call and set back original constructor
                this.initialize_original.apply(this, arguments);

                // reset function
                window.wysiwygSetup.prototype.initialize = window.wysiwygSetup.prototype.initialize_original;
                delete window.wysiwygSetup.prototype.initialize_original;

                // call import callback
                setTimeout(function () {
                    pd.importPromise.then(function (importCallBack) {
                        importCallBack(this);
                    });
                }, 400); // FIXME: this should only get triggered when the editor has been fully loaded
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
            this.importPromiseResolve(function () {
                this.pageDesigner.importWithPreviews(this.importData);
            }.bind(this));
        },
        /**
         * Builds the page designer instance
         */
        buildPageDesigner: function () { // NOSONAR
            // preserve instance
            var that = this;

            // get $ instance of element
            var jElement = $(this.element);

            // create the page designer instance
            this.pageDesigner = new PageDesigner({ // NOSONAR
                "element": jElement,
                // translations
                "i18n": {
                    "gridMode": {
                        "title": mageTranslate("Switch to responsive grid mode %s")
                    },
                    "row": {
                        "add": {
                            "title": mageTranslate("Add Row")
                        },
                        "move": {
                            "title": mageTranslate("Move Row")
                        },
                        "settings": {
                            "title": mageTranslate("Set settings for row"),
                            "prompt": mageTranslate("Enter the settings for the row.")
                        },
                        "delete": {
                            "title": mageTranslate("Delete row"),
                            "confirmation": mageTranslate("Do you REALLY want to delete the whole row? This will permanently delete all content of the different columns.")
                        }
                    },
                    "column": {
                        "add": {
                            "title": mageTranslate("Add Column")
                        },
                        "move": {
                            "title": mageTranslate("Move Column")
                        },
                        "settings": {
                            "title": mageTranslate("Set settings for column"),
                            "prompt": mageTranslate("Enter the settings for the column.")
                        },
                        "delete": {
                            "title": mageTranslate("Delete column"),
                            "confirmation": mageTranslate("Do you really want to delete this column? You cannot undo this.")
                        },
                        "content": {
                            "title": mageTranslate("Set column content"),
                            "prompt": mageTranslate("What content to set in?"),
                            "copy": {
                                "title": mageTranslate("Copy content")
                            },
                            "paste": {
                                "title": mageTranslate("Paste content")
                            },
                            "clear": {
                                "title": mageTranslate("Clear content"),
                                "confirmation": mageTranslate("Do you really want to clear the column's content?")
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
                        $('<div/>').modal({
                            title: mageTranslate('Column Settings'),
                            type: 'slide',
                            buttons: [],
                            opened: function () {
                                // load form
                                var dialog = $(this).addClass('loading magento-message');
                                // noinspection AmdModulesDependencies
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
                        $('<div/>').modal({
                            title: mageTranslate('Row Settings'),
                            type: 'slide',
                            buttons: [],
                            opened: function () {
                                // load form
                                var dialog = $(this).addClass('loading magento-message');
                                // noinspection AmdModulesDependencies
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

                        // check if we are in edit mode
                        let editMode = !!column.data('pd-content');
                        window.widgetTools.setEditMode(editMode);

                        // preserve original function
                        // noinspection AmdModulesDependencies
                        if (!WysiwygWidget.Widget.prototype.getWysiwygNode_original) {
                            // noinspection AmdModulesDependencies
                            WysiwygWidget.Widget.prototype.getWysiwygNode_original = WysiwygWidget.Widget.prototype.getWysiwygNode;
                        }

                        // pass our widget content to the widget browser
                        // noinspection AmdModulesDependencies
                        WysiwygWidget.Widget.prototype.getWysiwygNode = function () {
                            // reset function
                            // noinspection AmdModulesDependencies
                            this.getWysiwygNode = WysiwygWidget.Widget.prototype.getWysiwygNode = WysiwygWidget.Widget.prototype.getWysiwygNode_original;

                            // override the current update content function to store the generated content inside of page designer
                            this.updateContent = function (preview) {
                                var previewElement = $(preview);

                                // get widget code
                                var code = previewElement.attr('id');
                                if (code) {
                                    // noinspection AmdModulesDependencies
                                    code = Base64.idDecode(code);

                                    // get widget name
                                    let widgetName = code.replace(/.*type_name="([^"]+)".*$/, '$1');
                                    if (widgetName.indexOf('{{') !== -1) {
                                        widgetName = '';
                                    }

                                    // set widget code to content
                                    callback(column, code, preview + widgetName);
                                }
                            };

                            // return element
                            if (editMode) {
                                return column.find('.pd-col-content .pd-col-content-preview img:first-child').get(0);
                            } else {
                                return this.getWysiwygNode();
                            }
                        };

                        // open the widget browser
                        wysControl.find('.action-add-widget').get(0).click();
                    }
                }
            });

            // set custom import function
            this.pageDesigner.importWithPreviews = function (json) {
                // transform to string
                if (typeof json === 'string' && json) {
                    json = JSON.parse(json);
                }
                // check structure
                if (json && json.rows) {
                    // add previews
                    $(json.rows).each(function (ri, row) {
                        $(row.columns).each(function (ci, column) {
                            // call widget encoder of editor plugin
                            if (column.content) {
                                json.rows[ri].columns[ci].preview = mageWidgetEncode(column.content);
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
