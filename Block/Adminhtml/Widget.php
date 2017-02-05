<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Class Widget
 *
 * @package     Magenerds\PageDesigner\Block\Adminhtml
 * @file        Widget.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 */
class Widget extends \Magento\Widget\Block\Adminhtml\Widget
{
    /**
     * Holds the registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Widget constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Sub-Constructor
     */
    protected function _construct()
    {
        // construct
        parent::_construct();

        // generate widget key
        $widgetKey = 'widget_' . uniqid();

        // register widget key
        $this->registry->unregister('widget_form_key');
        $this->registry->register('widget_form_key', $widgetKey);

        // set button id and action
        $this->buttonList->update('save', 'id', 'insert_button_' . $widgetKey);
        $this->buttonList->update('save', 'onclick', $widgetKey . '.insertWidget()');

        // remove last form script
        array_pop($this->_formScripts);

        // add new form script
        $this->_formScripts[] = sprintf('
            require(["mage/adminhtml/wysiwyg/widget"], function() {
                wWidget = %s = new WysiwygWidget.Widget(%s, %s, %s, %s, %s);
            });
        ',
            $widgetKey,
            json_encode('widget_options_form_' . $widgetKey),
            json_encode('select_widget_type_' . $widgetKey),
            json_encode('widget_options_' . $widgetKey),
            json_encode($this->getUrl('adminhtml/*/loadOptions')),
            json_encode($this->getRequest()->getParam('widget_target_id'))
        );
    }
}
