<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Block\Adminhtml\Widget;

/**
 * Class Form
 *
 * @package     Magenerds\PageDesigner\Block\Adminhtml\Widget
 * @file        Form.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 */
class Form extends \Magento\Widget\Block\Adminhtml\Widget\Form
{
    /**
     * Form with widget to select
     *
     * @return void
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        // define field set
        $fieldSet = $form->addFieldset('base_fieldset', ['legend' => __('Widget')]);

        // retrieve widget key
        $widgetKey = $this->_coreRegistry->registry('widget_form_key');

        // add new field
        $fieldSet->addField(
            'select_widget_type_' . $widgetKey,
            'select',
            [
                'label' => __('Widget Type'),
                'title' => __('Widget Type'),
                'name' => 'widget_type',
                'required' => true,
                'onchange' => "$widgetKey.validateField()",
                'options' => $this->_getWidgetSelectOptions(),
                'after_element_html' => $this->_getWidgetSelectAfterHtml()
            ]
        );

        // set form information
        $form->setUseContainer(true);
        $form->setId('widget_options_form' . '_' . $widgetKey);
        $form->setMethod('post');
        $form->setAction($this->getUrl('adminhtml/*/buildWidget'));
        $this->setForm($form);
    }
}
