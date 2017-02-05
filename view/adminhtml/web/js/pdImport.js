/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

/**
 * Page Designer Import
 *
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 */
define([
    'jquery',
    'Magento_Ui/js/form/element/select'
], function (jQuery, Component) {
    'use strict'; // NOSONAR

    // build component
    return Component.extend({
        defaults: {
            listens: {
                'value': 'onValueChange'
            },
            elementTmpl: 'Magenerds_PageDesigner/page_designer/import'
        },
        /**
         * Gets called when the element is rendered
         *
         * @param {object} element
         */
        onElementRender: function (element) {
            this.element = element;
        },
        /**
         * Sets the import data when the user changes the value
         *
         * @param {string} value
         */
        onValueChange: function (value) {
            // if value has been given
            if (value) {
                // confirm
                if (confirm('Do you really want to import the selected block? Your changes will be lost!')) {
                    // get page designer instance
                    var pdElement = jQuery(this.element).closest('fieldset').find('.page-designer'),
                        pdInstance = pdElement.data('pd-instance');

                    // import data
                    pdInstance.importWithPreviews(value);
                }

                // clear value
                this.clear();
            }
        }
    });
});
