<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Option\ArrayPool;
use Magento\Framework\Registry;
use Magenerds\PageDesigner\Model\Source\CssClass\Column;
use Magenerds\PageDesigner\Model\Source\CssClass\Row;

/**
 * Class SettingsAbstract
 *
 * @package     Magenerds\PageDesigner\Block\Adminhtml
 * @file        SettingsAbstract.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 */
abstract class SettingsAbstract extends Generic
{
    /**
     * Element type used by default if configuration is omitted
     *
     * @var string
     */
    protected $_defaultElementType = 'text';

    /**
     * Holds the source models
     *
     * @var ArrayPool
     */
    protected $_sourceModelPool;

    /**
     * Holds the css class model for columns.
     *
     * @var Column
     */
    protected $_cssClassColumnModel;

    /**
     * Holds the css class model for rows.
     *
     * @var Row
     */
    protected $_cssClassRowModel;

    /**
     * SettingsAbstract constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param ArrayPool $sourceModelPool
     * @param Column $cssClassColumnModel
     * @param Row $cssClassRowModel
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ArrayPool $sourceModelPool,
        Column $cssClassColumnModel,
        Row $cssClassRowModel,
        array $data = []
    )
    {
        $this->_cssClassColumnModel = $cssClassColumnModel;
        $this->_cssClassRowModel = $cssClassRowModel;
        $this->_sourceModelPool = $sourceModelPool;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare Form and values according to specified type
     *
     * @return self
     */
    protected function _prepareForm()
    {
        $this->getForm()->setUseContainer(true);
        $this->addFields();
        return $this;
    }

    /**
     * Form getter/instantiation
     *
     * @return Form
     */
    public function getForm()
    {
        if ($this->_form instanceof Form) {
            return $this->_form;
        }
        /** @var Form $form */
        $form = $this->_formFactory->create();
        $this->setForm($form);
        return $form;
    }

    /**
     * Add fields to main fieldset
     *
     * @return self
     */
    public function addFields()
    {
        return $this;
    }

    /**
     * Add field to Settings form based on parameter configuration
     *
     * @param DataObject $parameter
     * @return AbstractElement
     */
    protected function _addField($parameter)
    {
        $fieldset = $this->getMainFieldset();

        // prepare element data with values (either from request of from default values)
        $fieldName = $parameter->getKey();
        $data = [
            'name' => $fieldName,
            'label' => __($parameter->getLabel()),
            'required' => $parameter->getRequired(),
            'class' => 'option',
            'note' => __($parameter->getDescription()),
        ];

        // check for values
        if ($options = $parameter->getOptions()) {
            $data = array_merge($data, $options);
        }

        // get data
        $data['value'] = $this->_getValue($parameter, $fieldName);

        // prepare element dropdown values
        if ($values = $parameter->getValues()) {
            // dropdown options are specified in configuration
            $data['values'] = [];
            foreach ($values as $option) {
                $data['values'][] = ['label' => __($option['label']), 'value' => $option['value']];
            }
            // otherwise, a source model is specified
        } elseif ($sourceModel = $parameter->getSourceModel()) {
            $data['values'] = $this->_sourceModelPool->get($sourceModel)->toOptionArray();
        }

        // prepare field type or renderer
        $fieldRenderer = null;
        $fieldType = $parameter->getType();

        // render
        if ($fieldType && $this->_isClassName($fieldType)) {
            $fieldRenderer = $this->getLayout()->createBlock($fieldType);
            $fieldType = $this->_defaultElementType;
        }

        // instantiate field and render html
        $field = $fieldset->addField($this->getMainFieldsetHtmlId() . '_' . $fieldName, $fieldType, $data);
        if ($fieldRenderer) {
            $field->setRenderer($fieldRenderer);
        }

        return $field;
    }

    /**
     * Fieldset getter/instantiation
     *
     * @return Fieldset
     */
    public function getMainFieldset()
    {
        if (($fieldset = $this->_getData('main_fieldset')) instanceof Fieldset) {
            return $fieldset;
        }
        $mainFieldsetHtmlId = 'settings_fieldset';
        $this->setMainFieldsetHtmlId($mainFieldsetHtmlId);
        $fieldset = $this->getForm()->addFieldset(
            $mainFieldsetHtmlId,
            ['class' => 'fieldset-wide fieldset-settings']
        );
        $this->setData('main_fieldset', $fieldset);

        return $fieldset;
    }

    /**
     * Gets current value of a string
     *
     * @param $object
     * @param $key
     * @return mixed
     */
    private function _getValue($object, $key)
    {
        // get values
        $values = $this->getSettings();

        // define key
        $settingsKey = 'settings';

        // check if value exist
        if ($values && isset($values[$settingsKey]) && isset($values[$settingsKey][$key])) {
            return $values[$settingsKey][$key];
        }

        // return object's value
        return $object->getValue();
    }

    /**
     * Checks whether $fieldType is a class name of custom renderer, and not just a type of input element
     *
     * @param string $fieldType
     * @return bool
     */
    protected function _isClassName($fieldType)
    {
        return preg_match('/[A-Z]/', $fieldType) > 0;
    }
}
