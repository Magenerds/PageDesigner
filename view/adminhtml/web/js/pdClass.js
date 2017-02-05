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
    'Magenerds_PageDesigner/js/vendor/sortableJs',
    'uiClass',
    'jquery/ui'
], function (jQuery, Sortable, Class) {
    'use strict'; // NOSONAR

    return Class.extend({
        /**
         * Meta information used by the module
         *
         * @type {object}
         */
        meta: {
            "version": "1.0.0",
            "gridMode": "md",
            "gridModes": [
                "xs",
                "sm",
                "md",
                "lg"
            ],
            "copiedColumn": null
        },

        /**
         * Default options for settings
         *
         * @type {object}
         */
        defaults: {
            "debug": false, // defines if errors and warnings should get logged
            "element": null, // the element in which we build the page designer
            "importData": null, // data to be imported when initializing
            "defaultGridMode": "md", // default mode of responsive sizes
            "defaultContent": null, // default content for new columns
            "grid": {
                "min": 1, // minimum grid size to resize to
                "max": 12 // maximum grid size to resize to
            },
            "events": {
                /**
                 * onUpdate event: Saves the new generated grid json to our data handler
                 *
                 * @param {object} event
                 * @param {string} data
                 */
                "onUpdate": function (event, data) {
                    return [event, data];
                },
                /**
                 * onColumnContentSet event: Gets called when a user clicks the content button
                 *
                 * @param {object} column
                 * @param {string} currentContent
                 * @param {Function} callback
                 */
                "onColumnContentSet": function (column, currentContent, callback) {
                    // ask for content
                    var content = window.prompt(this.__('column.content.prompt'), currentContent);
                    // check answer
                    if (content !== null && content !== false) {
                        // call out callback function (setColumnContent: function (column, content, preview))
                        callback(column, content, content);
                    }
                },
                /**
                 * onColumnSettingsSet event: Gets called when a user clicks the settings button
                 *
                 * @param {object} column
                 * @param {string} currentSettings
                 * @param {Function} callback
                 */
                "onColumnSettingsSet": function (column, currentSettings, callback) {
                    // ask for settings
                    var settingsText = window.prompt(this.__('column.settings.prompt'), currentSettings ? currentSettings.settings_text : '');
                    // check answer
                    if (settingsText !== null && settingsText !== false) {
                        // call out callback function (setColumnSettings: function (column, settings))
                        callback(column, {"settings_text": settingsText});
                    }
                },
                /**
                 * onRowSettingsSet event: Gets called when a user clicks the settings button
                 *
                 * @param {object} row
                 * @param {string} currentSettings
                 * @param {Function} callback
                 */
                "onRowSettingsSet": function (row, currentSettings, callback) {
                    // ask for settings
                    var settingsText = window.prompt(this.__('row.settings.prompt'), currentSettings ? currentSettings.settings_text : '');
                    // check answer
                    if (settingsText !== null && settingsText !== false) {
                        // call out callback function (setRowSettings: function (row, settings))
                        callback(row, {"settings_text": settingsText});
                    }
                }
            },
            // translations
            "i18n": {
                "gridMode": {
                    "title": "Switch to responsive grid mode %s"
                },
                "row": {
                    "add": {
                        "title": "Add Row"
                    },
                    "move": {
                        "title": "Move Row"
                    },
                    "settings": {
                        "title": "Set settings for row",
                        "prompt": "Enter the settings for the row."
                    },
                    "delete": {
                        "title": "Delete row",
                        "confirmation": "Do you REALLY want to delete the whole row? This will permanently delete all content of the different columns."
                    }
                },
                "column": {
                    "add": {
                        "title": "Add Column"
                    },
                    "move": {
                        "title": "Move Column"
                    },
                    "settings": {
                        "title": "Set settings for column",
                        "prompt": "Enter the settings for the column."
                    },
                    "delete": {
                        "title": "Delete column",
                        "confirmation": "Do you really want to delete this column? You cannot undo this."
                    },
                    "content": {
                        "title": "Set column content",
                        "prompt": "What content to set in?",
                        "copy": {
                            "title": "Copy content"
                        },
                        "paste": {
                            "title": "Paste content"
                        },
                        "clear": {
                            "title": "Clear content",
                            "confirmation": "Do you really want to clear the column's content?"
                        }
                    }
                }
            }
        },

        /**
         * Initializes the module
         *
         * @param {object} [options]
         */
        initialize: function (options) {
            // execute parent function
            this._super(options);

            // build designer
            this._build();
        },

        /**
         * Builds the page designer
         */
        _build: function () {
            // create the top bar
            this.topBar = this.createElement('pd-top-bar pd-grid');

            // create the row set
            this.rowSet = this.createElement('pd-row-set pd-grid');

            // create the bottom bar
            this.bottomBar = this.createElement('pd-bottom-bar pd-grid');

            // append all containers to the page designer element
            this._getElement().append(this.topBar).append(this.rowSet).append(this.bottomBar);

            // preserve instance
            var pd = this;

            // make all rows inside the row set sortable
            Sortable.create(this.rowSet.get(0), {
                draggable: '.pd-row', // draggable are all rows
                handle: '.pd-row-control-move', // only grab by the row controls
                animation: 150,
                /**
                 * Moves a row
                 */
                onMove: function () {
                    pd.updateState('moveRow');
                }
            });

            // build controls
            this._buildControls();

            // add event listeners
            this._addListeners();

            // set default grid mode
            this._setGridMode(this.defaultGridMode);

            // update state
            this.updateState('build');

            // import data
            this.import();
        },

        /**
         * Add the needed controls to our bars
         *
         * @private
         */
        _buildControls: function () {
            // preserve instance
            var pd = this;

            // define grid control container
            var gridControlContainer = this.createElement('pd-grid-mode-row');

            // define grid control buttons
            jQuery(this.meta.gridModes).each(function (i, mode) {
                // create button
                var button = pd.createElement('pd-grid-mode-button', 'a').attr('data-grid-mode', mode).attr('title', pd.__('gridMode.title').replace('%s', mode.toUpperCase())),
                    icon = pd.createIcon('device-' + mode);

                // append button to control container
                gridControlContainer.append(button.append(icon));
            });

            // add controls to top bar
            this.topBar.append(gridControlContainer);

            // add "add row" control
            var addRowButtonRowAbove = this.createElement('pd-row pd-add-row'),
                addRowButton = this.createElement('pd-add-row-button pd-col pd-col-' + this.grid.max, 'a').attr('title', this.__('row.add.title'));
            addRowButton.append(this.createIcon('add')).append(this.createElement('pd-meta', 'span').text(this.__('row.add.title')));
            addRowButtonRowAbove.append(addRowButton);

            // clone it and add classes
            var addRowButtonRowBelow = addRowButtonRowAbove.clone().addClass('pd-add-row-below');
            addRowButtonRowAbove.addClass('pd-add-row-above hidden');

            // add controls to their bars
            this.topBar.append(addRowButtonRowAbove);
            this.bottomBar.append(addRowButtonRowBelow);
        },

        /**
         * Adds event listeners to the ui elements
         *
         * @private
         */
        _addListeners: function () {
            // grid mode
            this._getElement().on('click', '.pd-grid-mode-button', this._eventProxy(function (element) {
                this._setGridMode(element.attr('data-grid-mode'));
            }));

            // add row (above)
            this._getElement().on('click', '.pd-add-row-above', this._eventProxy(function () {
                this.addRow(false, true);
            }));

            // add row (below)
            this._getElement().on('click', '.pd-add-row-below', this._eventProxy(function () {
                this.addRow();
            }));

            // set row settings
            this._getElement().on('click', '.pd-row-control-settings', this._eventProxy(function (element) {
                this.retrieveRowSettings(element.closest('.pd-row'));
            }));

            // remove row
            this._getElement().on('click', '.pd-row-control-remove', this._eventProxy(function (element) {
                this.removeRow(element.closest('.pd-row'));
            }));

            // add column
            this._getElement().on('click', '.pd-row-control-add-col', this._eventProxy(function (element) {
                this.addColumn(element.closest('.pd-row'));
            }));

            // set column content
            this._getElement().on('click', '.pd-col-content-preview', this._eventProxy(function (element) {
                this.retrieveColumnContent(element.closest('.pd-col'));
            }));

            // set column settings
            this._getElement().on('click', '.pd-col-control-settings', this._eventProxy(function (element) {
                this.retrieveColumnSettings(element.closest('.pd-col'));
            }));

            // paste column content
            this._getElement().on('click', '.pd-col-control-paste', this._eventProxy(function (element) {
                this._pasteColumnData(element.closest('.pd-col'));
            }));

            // remove column
            this._getElement().on('click', '.pd-col-control-remove', this._eventProxy(function (element) {
                this.removeCol(element.closest('.pd-col'));
            }));

            // copy column's content
            this._getElement().on('click', '.pd-col-content-control-copy', this._eventProxy(function (element) {
                this._copyColumnData(element.closest('.pd-col'));
            }));

            // remove column's content
            this._getElement().on('click', '.pd-col-content-control-clear', this._eventProxy(function (element) {
                if (window.confirm(this.__('column.content.clear.confirmation'))) {
                    this.setColumnContent(element.closest('.pd-col'), null);
                }
            }));
        },

        /**
         * Sets the current grid mode
         *
         * @param {string} mode
         * @private
         */
        _setGridMode: function (mode) {
            // check if grid mode exists
            if (jQuery.inArray(mode, this.meta.gridModes) === -1) {
                return;
            }

            // set grid operating mode
            this.meta.gridMode = mode;

            // highlight button to active
            this.topBar.find('.pd-grid-mode-button').removeClass('active').filter('[data-grid-mode="' + mode + '"]').addClass('active');

            // preserve instance
            var pd = this;

            // iterate over columns
            this.rowSet.find('.pd-row .pd-col').each(function () {
                // get column
                var column = jQuery(this);

                // set grid class
                pd.setGridSize(column, pd.getGridSizeForDisplay(column), null, true);
            });
        },

        /**
         * Gets the grid size of a column
         *
         * @param {object} column
         * @param {string} [mode]
         * @returns {number|boolean}
         */
        getGridSize: function (column, mode) {
            // defaults to current mode
            if (!mode) {
                mode = this.meta.gridMode;
            }

            // return grid size
            return column.data('pd-grid-' + mode) || false;
        },

        /**
         * Gets the visible grid size of a column
         *
         * @param {object} column
         * @param {string} [mode]
         * @returns {number}
         */
        getGridSizeForDisplay: function (column, mode) {
            // defaults to current mode
            if (!mode) {
                mode = this.meta.gridMode;
            }

            // define current size
            var size = false;

            // define index of current mode
            var currentModeIndex = this.meta.gridModes.indexOf(mode);

            // look for a stored value in smaller grid sizes
            while (currentModeIndex >= 0 && !size) {
                size = this.getGridSize(column, this.meta.gridModes[currentModeIndex]);
                currentModeIndex--;
            }

            // return size if it was found, maximum size otherwise
            return size || this.grid.max;
        },

        /**
         * Imports data to the current grid
         */
        import: function () { // NOSONAR
            // check data
            if (!this.importData) {
                return;
            }

            // force data to be an object
            if (typeof this.importData === 'string') {
                try {
                    this.importData = JSON.parse(this.importData);
                } catch (e) {
                    this.log('Could not decode import data!', 'error');
                    return;
                }
            }

            // get data
            var d = this.importData;

            // check if version is stored inside data
            if (!d.version) {
                this.log('No version information in import data. Will not import data.', 'error');
                return;
            }

            // check version
            if (d.version !== this.meta.version) {
                this.log('Version ' + d.version + ' does not match current version ' + this.meta.version + '! Import may fail.', 'warning');
            }

            // check data structure
            if (!d.rows || !Array.isArray(d.rows)) {
                this.log('Invalid data structure! Will not import data.', 'error');
                return;
            }

            // preserve instance
            var pd = this;

            // reset state
            this.rowSet.find('.pd-row').each(function () {
                pd.removeRow(jQuery(this), true);
            });

            // iterate over rows
            jQuery(d.rows).each(function (ri, row) {
                // check data structure
                if (!Array.isArray(row.columns)) {
                    pd.log('Invalid data structure for row ' + ri + '!', 'warning');
                } else if (row.columns.length) {
                    // add row
                    var rowEl = pd.addRow(true).data('pd-initial-size', false);

                    // set settings
                    pd.setRowSettings(rowEl, row.settings);

                    // iterate over columns
                    jQuery(row.columns).each(function (ci, col) {
                        // add column
                        var colEl = pd.addColumn(rowEl, col.content, col.preview, col.settings);

                        // set grid sizes
                        jQuery(Object.keys(col.gridSize)).each(function (gi, gs) {
                            pd.setGridSize(colEl, col.gridSize[gs], gs);
                        });
                    });
                }
            });

            // update view
            this._setGridMode(this.defaultGridMode);

            // update state
            this.updateState('import');
        },

        /**
         * Exports the current grid to JSON
         *
         * @returns {string}
         */
        export: function () {
            // preserve instance
            var pd = this;

            // define basic json
            var json = {
                "version": this.meta.version,
                "rows": []
            };

            // iterate over all rows
            this.rowSet.find('.pd-row').each(function () {
                // add row json
                json.rows.push(pd.exportRow(jQuery(this)));
            });

            // return data
            return json;
        },

        /**
         * Exports a row
         *
         * @param {object} row
         * @returns {object}
         */
        exportRow: function (row) {
            // preserve instance
            var pd = this;

            // set row json
            var rowJson = {
                "columns": [],
                "settings": row.data('pd-settings') ? row.data('pd-settings') : {}
            };

            // iterate over row's columns
            row.find('.pd-col').each(function () {
                // add column json
                rowJson.columns.push(pd.exportColumn(jQuery(this)));
            });

            // return data
            return rowJson;
        },

        /**
         * Exports a column
         *
         * @param {object}column
         * @returns {object}
         */
        exportColumn: function (column) {
            // preserve instance
            var pd = this;

            // define variables
            var gridSize;

            // get column json
            var colJson = {
                "gridSize": {},
                "content": column.data('pd-content'),
                "settings": column.data('pd-settings') ? column.data('pd-settings') : {}
            };

            // iterate over all grid modes
            jQuery(pd.meta.gridModes).each(function (i, mode) {
                // set grid size
                if (gridSize = pd.getGridSize(column, mode)) {
                    colJson.gridSize[mode] = gridSize;
                }
            });

            // return data
            return colJson;
        },

        /**
         * Updates the page designer's state
         *
         * @param {string} event
         * @returns {*}
         */
        updateState: function (event) {
            if (typeof this.events.onUpdate === 'function') {
                return this.events.onUpdate.bind(this)(event, this.export());
            }
            return false;
        },

        /**
         * Adds a row to the row set
         *
         * @param {boolean} [noDefaultColumn]
         * @param {boolean} [above]
         * @returns {object}
         */
        addRow: function (noDefaultColumn, above) {
            // create row and its drop area
            var row = this.createElement('pd-row').data('pd-initial-size', true),
                dropArea = this.createElement('pd-drop-area');

            // add control bar and controls
            var controlBar = this.createElement('pd-row-controls'),
                moveButton = this.createElement('pd-row-control pd-row-control-move', 'a').attr('title', this.__('row.move.title')).append(this.createIcon('move')),
                addButton = this.createElement('pd-row-control pd-row-control-add-col', 'a').attr('title', this.__('column.add.title')).append(this.createIcon('add')),
                settingsButton = this.createElement('pd-row-control pd-row-control-settings', 'a').attr('title', this.__('row.settings.title')).append(this.createIcon('settings')),
                deleteButton = this.createElement('pd-row-control pd-row-control-remove', 'a').attr('title', this.__('row.delete.title')).append(this.createIcon('remove'));

            // append controls to control bar
            controlBar.append(moveButton).append(addButton).append(settingsButton).append(deleteButton);

            // append elements to row
            row.append(controlBar).append(dropArea);

            // add column
            if (!noDefaultColumn) {
                this.addColumn(row);
            }

            // add the row to the row set
            if (!above) {
                this.rowSet.append(row);
            } else {
                this.rowSet.prepend(row);
            }

            // preserve instance
            var pd = this;

            // make the row's columns sortable between each other and other rows
            Sortable.create(row.find('.pd-drop-area').get(0), {
                group: 'rowColumns', // group name
                draggable: '.pd-col', // draggable are all columns
                handle: '.pd-col-control-move', // only grab by the column controls
                animation: 150,
                /**
                 * Event when a column gets removed from a row
                 *
                 * @param {object} event
                 */
                onRemove: function (event) {
                    // find row the column belongs to
                    var row = jQuery(event.from).closest('.pd-row');

                    // if there are no other columns left, remove the row
                    if (!row.find('.pd-col').length) {
                        pd.removeRow(row, true);
                    }
                },
                /**
                 * Removes a column
                 */
                onMove: function () {
                    pd.updateState('moveCol');
                }
            });

            // show top add row button
            this.topBar.find('.pd-add-row').removeClass('hidden');

            // update state
            this.updateState('addRow');

            // return row
            return row;
        },

        /**
         * Removes a row
         *
         * @param {object} row
         * @param {boolean} [noConfirm]
         */
        removeRow: function (row, noConfirm) {
            // confirm removing
            if (noConfirm || window.confirm(this.__('row.delete.confirmation'))) {
                // remove row
                row.remove();

                // hide top add row button if there are no rows left
                if (!this.rowSet.find('.pd-row').length) {
                    this.topBar.find('.pd-add-row').addClass('hidden');
                }

                // update state
                this.updateState('removeRow');
            }
        },

        /**
         * Removes a column
         *
         * @param {object} column
         * @param {boolean} [noConfirm]
         */
        removeCol: function (column, noConfirm) {
            // confirm removing
            if (noConfirm || window.confirm(this.__('column.delete.confirmation'))) {
                /**
                 * Collects the belonging row
                 *
                 * @type {jQuery}
                 */
                var row = column.closest('.pd-row');

                // if it's the last column, delete the whole row
                if (row.find('.pd-col').length === 1) {
                    this.removeRow(row, true);
                    return;
                } else {
                    column.remove();
                }

                // update state
                this.updateState('removeCol');
            }
        },

        /**
         * Calculates the size for a new column
         *
         * @param {object} row
         * @returns {number}
         * @private
         */
        _calculateNewColumnSize: function (row) {
            // preserve instance
            var size = 0, pd = this;

            // if the row is in its initial state, automatically adjust all columns
            if (row.data('pd-initial-size')) {
                // get columns of the current row
                var rowCols = row.find('.pd-col');

                // calculate new size
                size = (rowCols.length + 1) % this.grid.max;
                size = Math.floor(this.grid.max / (size || this.grid.max));

                // check if our "virtual row" has entries
                var virtualRowLength = rowCols.length % this.grid.max;
                if (virtualRowLength) {
                    // set virtual row columns size
                    rowCols.slice(-virtualRowLength).each(function () {
                        pd.setGridSize(jQuery(this), size);
                    });
                }
            } else {
                // the row has been touched, so only calculate the new item's size
                row.find('.pd-col').each(function () {
                    size += pd.getGridSizeForDisplay(jQuery(this));
                });
                size = this.grid.max - (size % this.grid.max);
            }

            // return size
            return size;
        },

        /**
         * Adds a new column to the given row
         *
         * @param {object} row
         * @param {string} [content]
         * @param {string} [preview]
         * @param {string} [settings]
         */
        addColumn: function (row, content, preview, settings) { // NOSONAR
            // get drop area of row
            var dropArea = row.find('.pd-drop-area');
            if (!dropArea.length) {
                this.log('Drop area not found.', 'error');
                return;
            }

            // preserve instance
            var pd = this;

            // create the column
            var column = this.setGridSize(this.createElement('pd-col'), this._calculateNewColumnSize(row));

            // add control bar and controls
            var controlBar = this.createElement('pd-col-controls'),
                moveButton = this.createElement('pd-col-control pd-col-control-move', 'a').attr('title', this.__('column.move.title')).append(this.createIcon('move')),
                settingsButton = this.createElement('pd-col-control pd-col-control-settings', 'a').attr('title', this.__('column.settings.title')).append(this.createIcon('settings')),
                pasteButton = this.createElement('pd-col-control pd-col-control-paste', 'a').attr('title', this.__('column.content.paste.title')).append(this.createIcon('paste')),
                deleteButton = this.createElement('pd-col-control pd-col-control-remove', 'a').attr('title', this.__('column.delete.title')).append(this.createIcon('remove'));

            // append controls to control bar
            controlBar.append(moveButton).append(settingsButton).append(pasteButton).append(deleteButton);

            // add content area
            var contentArea = this.createElement('pd-col-content'),
                contentPreview = this.createElement('pd-col-content-preview'),
                contentControls = this.createElement('pd-col-content-controls'),
                copyButton = this.createElement('pd-col-content-control pd-col-content-control-copy', 'a').attr('title', this.__('column.content.copy.title')).append(this.createIcon('copy')),
                clearButton = this.createElement('pd-col-content-control pd-col-content-control-clear', 'a').attr('title', this.__('column.content.clear.title')).append(this.createIcon('remove'));
            contentArea.append(contentControls.append(copyButton).append(clearButton)).append(contentPreview);

            // append elements to column
            column.append(controlBar).append(contentArea);

            // set column initial data
            this.setColumnContent(column, content, preview);

            // set columns initial settings
            this.setColumnSettings(column, settings);

            // add column to the drop area
            dropArea.append(column);

            // make column resizeable
            column.resizable({
                containment: 'parent', // stick to the parent element
                handles: 'e', // only resizeable from the right (eastern) handle
                grid: 10, // stick to movements in 10px range (to prevent calling the event too much)
                /**
                 * Resize event
                 *
                 * @param {object} e
                 * @param {object} ui
                 */
                resize: function (e, ui) {
                    // get column
                    var column = jQuery(ui.element);

                    // calculate grid size
                    var gridSize = pd._getGridValueForSize(ui.size.width);

                    // remove initial state
                    column.closest('.pd-row').data('pd-initial-size', false);

                    // set new grid size
                    pd.setGridSize(column.removeAttr('style'), gridSize);
                },
                /**
                 * Gets called when the user finished resizing
                 */
                stop: function () {
                    pd.updateState('resize');
                }
            });

            // update state
            this.updateState('addColumn');

            // return column
            return column;
        },

        /**
         * Retrieves the settings that should be saved to the row
         *
         * @param {object} row
         */
        retrieveRowSettings: function (row) {
            // execute event
            this.events.onRowSettingsSet.bind(this)(row, row.data('pd-settings'), this.setRowSettings.bind(this));
        },

        /**
         * Set row settings
         *
         * @param {object} row
         * @param {object} settings
         */
        setRowSettings: function (row, settings) {
            // check settings
            if (!settings || typeof settings !== 'object') {
                settings = {};
            }

            // set settings
            row.data('pd-settings', settings);

            // update state
            this.updateState('setRowSettings');
        },

        /**
         * Retrieves the settings that should be saved to the column
         *
         * @param {object} column
         */
        retrieveColumnSettings: function (column) {
            // execute event
            this.events.onColumnSettingsSet.bind(this)(column, column.data('pd-settings'), this.setColumnSettings.bind(this));
        },

        /**
         * Set column settings
         *
         * @param {object} column
         * @param {object} settings
         */
        setColumnSettings: function (column, settings) {
            // check settings
            if (!settings || typeof settings !== 'object') {
                settings = {};
            }

            // set settings
            column.data('pd-settings', settings);

            // update state
            this.updateState('setColumnSettings');
        },

        /**
         * Retrieves the content that should be saved to the column
         *
         * @param {object} column
         */
        retrieveColumnContent: function (column) {
            // execute event
            this.events.onColumnContentSet.bind(this)(column, column.data('pd-content'), this.setColumnContent.bind(this));
        },

        /**
         * Set column content
         *
         * @param {object} column
         * @param {string} content
         * @param {string} [preview]
         */
        setColumnContent: function (column, content, preview) {
            // get preview element
            var previewElement = column.find('.pd-col-content .pd-col-content-preview');

            // check for content
            if (!content) {
                // set default content
                content = this.defaultContent;
                // create content add button
                var contentButton = this.createElement('pd-col-add-content', 'a').attr('title', this.__('column.content.title')).append(this.createIcon('add'));
                // remove preview and add content button
                previewElement.empty().append(contentButton);
            } else {
                // check if preview exists
                if (!preview) {
                    preview = content;
                }
                // set preview
                previewElement.html(preview);
            }

            // set content
            column.data('pd-content', content);

            // set classes
            if (content === this.defaultContent) {
                column.addClass('pd-col-no-content').removeClass('pd-col-has-content');
            } else {
                column.removeClass('pd-col-no-content').addClass('pd-col-has-content');
            }

            // update state
            this.updateState('setColumnContent');
        },

        /**
         * Copies a column's content
         *
         * @param {object} column
         * @private
         */
        _copyColumnData: function (column) {
            this.meta.copiedColumn = column;
            this._getElement().addClass('pd-state-column-copy');
        },

        /**
         * Pastes a column's content
         *
         * @param {object} column
         * @private
         */
        _pasteColumnData: function (column) {
            // check for column
            if (this.meta.copiedColumn && this.meta.copiedColumn.length) {
                this.setColumnContent(column, this.meta.copiedColumn.data('pd-content'), this.meta.copiedColumn.find('.pd-col-content-preview').html());
                this.setColumnSettings(column, this.meta.copiedColumn.data('pd-settings'));
            }

            // reset state
            this.meta.copiedColumn = null;
            this._getElement().removeClass('pd-state-column-copy');
        },

        /**
         * Sets the grid size of a column
         *
         * @param {object} column
         * @param {number} size
         * @param {string} [gridMode]
         * @param {boolean} [doNotStore]
         * @returns {object}
         */
        setGridSize: function (column, size, gridMode, doNotStore) {
            if (!gridMode) {
                gridMode = this.meta.gridMode;
            }
            if (!doNotStore) {
                column.data('pd-grid-' + gridMode, size);
            }
            return column.removeClass(this._getGridClasses().join(' ')).addClass('pd-col-' + size);
        },

        /**
         * Gets all available grid classes
         *
         * @returns {Array}
         * @private
         */
        _getGridClasses: function () {
            var classes = [];
            for (var i = this.grid.min; i <= this.grid.max; i++) {
                classes.push('pd-col-' + i);
            }
            return classes;
        },

        /**
         * Calculates the grid size for the current resizeable element
         *
         * @param {number} size
         * @returns {number}
         * @private
         */
        _getGridValueForSize: function (size) {
            // calculate size for the whole grid and extract size for one (1) and minimum grid size
            var maxSize = this.rowSet.width(),
                oneSize = parseInt(maxSize / this.grid.max),
                minSize = this.grid.min * oneSize;

            // limit size to the min and max grid
            if (size > maxSize) {
                return this.grid.max;
            } else if (size < minSize) {
                return this.grid.min;
            }

            // create array to push values of the grid sizes to
            var values = [];

            // push each grid size's size into array
            for (var i = this.grid.min; i <= this.grid.max; i++) {
                values.push(i * oneSize);
            }

            // calculate first difference
            var curr = values[0],
                diff = Math.abs(size - curr);

            // iterate over grid sizes and test them
            for (var test = 0; test < values.length; test++) {
                // check difference
                var newDiff = Math.abs(size - values[test]);

                // if we got the lowest difference, set it as given
                if (newDiff < diff) {
                    diff = newDiff;
                    curr = values[test];
                }
            }

            // return matching grid size
            return values.indexOf(curr) + this.grid.min;
        },

        /**
         * Creates a new DOM element
         *
         * @param {string} cls
         * @param {string} [tag]
         * @returns {object}
         */
        createElement: function (cls, tag) {
            // set div as default tag
            if (!tag) {
                tag = 'div';
            }

            // return element
            return jQuery('<' + tag + '>').addClass(cls);
        },

        /**
         * Creates an icon
         *
         * @param {string} icon
         * @returns {object}
         */
        createIcon: function (icon) {
            return this.createElement('pd-icon pd-icon-' + icon, 'span').html('&nbsp;');
        },

        /**
         * Tries to get a stacked property from an object
         *
         * @param {object} obj
         * @param {string|[]} sel
         * @returns {string|*}
         * @private
         */
        _getPath: function (obj, sel) {
            // force array
            if (typeof sel === 'string') {
                sel = sel.split(".");
            }

            // check if object exists
            if (obj === void 0) {
                return void 0;
            }

            // check if selector
            if (sel.length === 0) {
                return obj;
            }

            // If we still have elements in the path array and the current
            // value is null, stop executing and return undefined
            if (obj === null) {
                return void 0;
            }

            // recursion
            return this._getPath(obj[sel.shift()], sel);
        },

        /**
         * Logs some output to the console
         *
         * @param {string} msg
         * @param {string} [level]
         */
        log: function (msg, level) {
            // check if debugging is enabled
            if (!this.debug) {
                return;
            }

            // validate logging level
            if (jQuery.inArray(level, ['log', 'warn', 'error']) === -1) {
                level = 'log';
            }

            // check for existence
            if (typeof console === 'undefined' || !console.hasOwnProperty(level)) {
                return;
            }

            // log to console
            console[level]('[PageDesigner::' + level.toUpperCase() + '] ' + msg);
        },

        /**
         * Translates a string
         *
         * @param {string} identifier
         *
         * @returns {string|*}
         */
        __: function (identifier) {
            return this._getPath(this.i18n, identifier) || identifier;
        },

        /**
         * Returns a function with proxy to the current class
         *
         * @param {Function} func
         * @returns {Function}
         * @private
         */
        _eventProxy: function (func) {
            var originalBinding = this;
            return function (e) {
                func.bind(originalBinding)(jQuery(this), e);
            };
        },

        /**
         * Gets the page designer main element
         *
         * @returns {element}
         * @private
         */
        _getElement: function () {
            return this.element;
        }
    });
});
