<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Block\Adminhtml\Widget;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory;
use Magenerds\PageDesigner\Constants;

/**
 * Class Editor
 *
 * @package     Magenerds\PageDesigner\Block\Adminhtml\Widget
 * @file        Editor.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Philipp Steinkopff <p.steinkopff@techdivision.com>
 * @author      Simon Sippert <s.sippert@techdivision.com>
 */
class Editor extends Element
{
    /**
     * Holds the wysiwyg configuration
     *
     * @var Config
     */
    protected $_wysiwygConfig;

    /**
     * Holds the factory element
     *
     * @var Factory
     */
    protected $_factoryElement;

    /**
     * Editor constructor.
     *
     * @param Context $context
     * @param Factory $factoryElement
     * @param Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $factoryElement,
        Config $wysiwygConfig,
        $data = []
    )
    {
        $this->_factoryElement = $factoryElement;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $data);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param AbstractElement $element Form Element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        // create editor
        $editor = $this->_factoryElement->create('editor', ['data' => $element->getData()])
            ->setLabel('')
            ->setForm($element->getForm())
            ->setWysiwyg(true)
            ->setConfig($this->_wysiwygConfig->getConfig([
                'skip_widgets' => [Constants::WIDGET_TYPE]
            ]));

        // add required class
        if ($element->getRequired()) {
            $editor->addClass('required-entry');
        }

        // set element html
        $element->setData(
            'after_element_html',
            $this->_getAfterElementHtml() .
            str_replace('jQuery(window).on("load", ', 'jQuery(', $editor->getElementHtml())
        );

        // return element
        return $element;
    }

    /**
     * Gets element's after html
     *
     * @return string
     */
    protected function _getAfterElementHtml()
    {
        return <<<HTML
    <style>
        .admin__field-control.control .control-value {
            display: none !important;
        }
    </style>
HTML;
    }
}
