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
    'mage/translate',
    'mage/adminhtml/wysiwyg/widget',
    'mage/adminhtml/wysiwyg/tiny_mce/setup'
], function ($, Abstract, PageDesigner, $t) {
    'use strict'; // NOSONAR

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
        return content;
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
            window.wysiwygSetup.prototype.initialize = function (htmlId, config) {
                // call and set back original constructor
                this.initialize_original.apply(this, arguments);

                // reset function
                window.wysiwygSetup.prototype.initialize = window.wysiwygSetup.prototype.initialize_original;
                delete window.wysiwygSetup.prototype.initialize_original;

                // check for editor
                var interval = setInterval(function () {
                    if (tinymce && tinymce.activeEditor && tinymce.activeEditor.plugins && tinymce.activeEditor.plugins.magentowidget) {
                        // resolve promise
                        pd.importPromise.then(function (importCallBack) {
                            importCallBack(this);
                        });
                        clearInterval(interval);
                    }
                }, 100);
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
                        "title": $t("Switch to responsive grid mode %s")
                    },
                    "row": {
                        "add": {
                            "title": $t("Add Row")
                        },
                        "move": {
                            "title": $t("Move Row")
                        },
                        "settings": {
                            "title": $t("Set settings for row"),
                            "prompt": $t("Enter the settings for the row.")
                        },
                        "delete": {
                            "title": $t("Delete row"),
                            "confirmation": $t("Do you REALLY want to delete the whole row? This will permanently delete all content of the different columns.")
                        }
                    },
                    "column": {
                        "add": {
                            "title": $t("Add Column")
                        },
                        "move": {
                            "title": $t("Move Column")
                        },
                        "settings": {
                            "title": $t("Set settings for column"),
                            "prompt": $t("Enter the settings for the column.")
                        },
                        "delete": {
                            "title": $t("Delete column"),
                            "confirmation": $t("Do you really want to delete this column? You cannot undo this.")
                        },
                        "content": {
                            "title": $t("Set column content"),
                            "prompt": $t("What content to set in?"),
                            "copy": {
                                "title": $t("Copy content")
                            },
                            "paste": {
                                "title": $t("Paste content")
                            },
                            "clear": {
                                "title": $t("Clear content"),
                                "confirmation": $t("Do you really want to clear the column's content?")
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
                            title: $t('Column Settings'),
                            type: 'slide',
                            buttons: [],
                            opened: function () {
                                // load form
                                var dialog = $(this).addClass('loading magento-message');
                                dialog.load(window.pageDesignerConfig.columnSettingsUrl, {object: JSON.stringify(pd.exportColumn(column))}, function () {
                                    dialog.removeClass('loading');
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
                            title: $t('Row Settings'),
                            type: 'slide',
                            buttons: [],
                            opened: function () {
                                // load form
                                var dialog = $(this).addClass('loading magento-message');
                                dialog.load(window.pageDesignerConfig.rowSettingsUrl, {object: JSON.stringify(pd.exportRow(row))}, function () {
                                    dialog.removeClass('loading');
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
                        // remove old editors
                        $.each(tinyMCE.editors, function (i, editor) {
                            if (editor && !document.getElementById(editor.id)) {
                                editor.remove();
                            }
                        });

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
                        window.widgetTools.setActiveSelectedNode(null);

                        // preserve original function
                        // noinspection AmdModulesDependencies
                        if (!WysiwygWidget.Widget.prototype.insertWidget_original) {
                            // noinspection AmdModulesDependencies
                            WysiwygWidget.Widget.prototype.insertWidget_original = WysiwygWidget.Widget.prototype.insertWidget;
                        }

                        // clear selected node
                        // noinspection AmdModulesDependencies
                        WysiwygWidget.Widget.prototype.insertWidget = function () { // reset function
                            // noinspection AmdModulesDependencies
                            this.insertWidget = WysiwygWidget.Widget.prototype.insertWidget = WysiwygWidget.Widget.prototype.insertWidget_original;

                            // get current form
                            let form = $('#' + this.formEl);

                            // clear active node if we got a wysiwyg widget
                            if ($.inArray(form.find('select[name="widget_type"]').val(), window.pageDesignerConfig.wysiwygWidgetTypes) !== -1) {
                                window.widgetTools.setActiveSelectedNode(null);
                            }

                            // call parent function
                            return this.insertWidget();
                        };

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
