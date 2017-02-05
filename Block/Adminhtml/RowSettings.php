<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Block\Adminhtml;

use Magento\Framework\DataObject;

/**
 * Class RowSettings
 *
 * @package     Magenerds\PageDesigner\Block\Adminhtml
 * @file        RowSettings.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 */
class RowSettings extends SettingsAbstract
{
    /**
     * Add fields to main fieldset
     *
     * @return self
     */
    public function addFields()
    {
        // add field
        $this->_addField(new DataObject([
            'key' => 'css_class',
            'type' => 'select',
            'values' => $this->_cssClassRowModel->toOptionArray(),
            'label' => __('CSS Class'),
        ]));

        // add save button
        $this->_addField(new DataObject([
            'key' => 'save',
            'type' => 'note',
            'label' => ' ',
            'options' => [
                'text' => $this->getButtonHtml(
                    __('Save'),
                    'window.pageDesignerConfig.setSettings.bind(this)()',
                    'action-primary save-settings'
                ),
            ],
        ]));

        return $this;
    }
}
