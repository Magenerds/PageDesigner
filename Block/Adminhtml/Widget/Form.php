<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Block\Adminhtml\Widget;

use Magenerds\PageDesigner\Block\Adminhtml\Widget;
use Magento\Framework\Data\Form as DataForm;
use Magento\Widget\Block\Adminhtml\Widget\Form as BaseForm;

/**
 * Class Form
 *
 * @package     Magenerds\PageDesigner\Block\Adminhtml\Widget
 * @file        Form.php
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 */
class Form extends BaseForm
{
    /**
     * Form with widget to select
     */
    protected function _prepareForm()
    {
        /** @var DataForm $form */
        /** @noinspection PhpUnhandledExceptionInspection */
        $form = $this->_formFactory->create();

        // define field set
        $fieldSet = $form->addFieldset('base_fieldset', ['legend' => __('Widget')]);

        // retrieve widget key
        $widgetKey = $this->_coreRegistry->registry(Widget::REGISTRY_KEY_WIDGET_FORM_KEY);

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
        /** @noinspection PhpUndefinedMethodInspection */
        $form->setUseContainer(true);
        /** @noinspection PhpUndefinedMethodInspection */
        $form->setId('widget_options_form' . '_' . $widgetKey);
        /** @noinspection PhpUndefinedMethodInspection */
        $form->setMethod('post');
        /** @noinspection PhpUndefinedMethodInspection */
        $form->setAction($this->getUrl('adminhtml/*/buildWidget'));
        $this->setForm($form);
    }
}
