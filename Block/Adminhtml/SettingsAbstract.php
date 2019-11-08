<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Block\Adminhtml;

use Magenerds\PageDesigner\Model\Source\CssClass\Column;
use Magenerds\PageDesigner\Model\Source\CssClass\Row;
use Magento\Backend\Block\Template\Context;
use /** @noinspection PhpDeprecationInspection */
    Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Option\ArrayPool;
use /** @noinspection PhpDeprecationInspection */
    Magento\Framework\Registry;

/** @noinspection PhpDeprecationInspection */

/**
 * Class SettingsAbstract
 *
 * @package     Magenerds\PageDesigner\Block\Adminhtml
 * @file        SettingsAbstract.php
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 *
 * @method array getSettings()
 * @method $this setSettings(array $settings)
 */
abstract class SettingsAbstract extends Generic
{
    /**
     * Element type used by default if configuration is omitted
     *
     * @var string
     */
    protected $defaultElementType = 'text';

    /**
     * @var ArrayPool
     */
    protected $sourceModelPool;

    /**
     * @var Column
     */
    protected $cssClassColumnModel;

    /**
     * @var Row
     */
    protected $cssClassRowModel;

    /** @noinspection PhpDeprecationInspection */
    /**
     * SettingsAbstract constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param ArrayPool $sourceModelPool
     * @param Column $cssClassColumnModel
     * @param Row $cssClassRowModel
     * @param array $data
     */
    public function __construct(
        /** @noinspection PhpDeprecationInspection */
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ArrayPool $sourceModelPool,
        Column $cssClassColumnModel,
        Row $cssClassRowModel,
        array $data = []
    )
    {
        /** @noinspection PhpDeprecationInspection */
        parent::__construct($context, $registry, $formFactory, $data);
        $this->cssClassColumnModel = $cssClassColumnModel;
        $this->cssClassRowModel = $cssClassRowModel;
        $this->sourceModelPool = $sourceModelPool;
    }

    /**
     * Prepare Form and values according to specified type
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->getForm()->setUseContainer(true);
        $this->addFields();
        return $this;
    }

    /**
     * Form getter/instantiation
     *
     * @return Form
     * @throws LocalizedException
     */
    public function getForm()
    {
        if ($this->_form instanceof Form) {
            return $this->_form;
        }
        /** @var Form $form */
        /** @noinspection PhpUnhandledExceptionInspection */
        $form = $this->_formFactory->create();
        $this->setForm($form);
        return $form;
    }

    /**
     * Add fields to main fieldset
     *
     * @return $this
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
     * @throws LocalizedException
     */
    protected function addField($parameter)
    {
        $fieldset = $this->getMainFieldset();

        // prepare element data with values (either from request of from default values)
        /** @noinspection PhpUndefinedMethodInspection */
        $fieldName = $parameter->getKey();
        /** @noinspection PhpUndefinedMethodInspection */
        $data = [
            'name' => $fieldName,
            'label' => __($parameter->getLabel()),
            'required' => $parameter->getRequired(),
            'class' => 'option',
            'note' => __($parameter->getDescription()),
        ];

        // check for values
        /** @noinspection PhpUndefinedMethodInspection */
        if ($options = $parameter->getOptions()) {
            $data = array_merge($data, $options);
        }

        // get data and prepare it for multiselect
        $data['value'] = explode(' ', $this->getValue($parameter, $fieldName));

        // prepare element dropdown values
        /** @noinspection PhpUndefinedMethodInspection */
        if ($values = $parameter->getValues()) {
            // dropdown options are specified in configuration
            $data['values'] = [];
            foreach ($values as $option) {
                $data['values'][] = ['label' => __($option['label']), 'value' => $option['value']];
            }
            // otherwise, a source model is specified
        } /** @noinspection PhpUndefinedMethodInspection */
        elseif ($sourceModel = $parameter->getSourceModel()) {
            /** @noinspection PhpUndefinedMethodInspection */
            $data['values'] = $this->sourceModelPool->get($sourceModel)->toOptionArray();
        }

        // prepare field type or renderer
        /** @var RendererInterface $fieldRenderer */
        $fieldRenderer = null;
        /** @noinspection PhpUndefinedMethodInspection */
        $fieldType = $parameter->getType();

        // render
        if ($fieldType && $this->isClassName($fieldType)) {
            $fieldRenderer = $this->getLayout()->createBlock($fieldType);
            $fieldType = $this->defaultElementType;
        }

        // instantiate field and render html
        /** @noinspection PhpUndefinedMethodInspection */
        $field = $fieldset->addField($this->getMainFieldsetHtmlId() . '_' . $fieldName, $fieldType, $data);
        if ($fieldRenderer) {
            $field->setRenderer($fieldRenderer);
        }

        return $field;
    }

    /**
     * Fieldset getter/instantiation
     *
     * @return Fieldset|mixed
     * @throws LocalizedException
     */
    public function getMainFieldset()
    {
        if (($fieldset = $this->_getData('main_fieldset')) instanceof Fieldset) {
            return $fieldset;
        }
        $mainFieldsetHtmlId = 'settings_fieldset';
        /** @noinspection PhpUndefinedMethodInspection */
        $this->setMainFieldsetHtmlId($mainFieldsetHtmlId);
        /** @noinspection PhpUnhandledExceptionInspection */
        $fieldset = $this->getForm()->addFieldset(
            $mainFieldsetHtmlId,
            ['class' => 'fieldset-wide fieldset-settings']
        );
        $this->setData('main_fieldset', $fieldset);

        return $fieldset;
    }

    /**
     * Get current value of a string
     *
     * @param $object
     * @param $key
     * @return mixed
     */
    protected function getValue($object, $key)
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
        /** @noinspection PhpUndefinedMethodInspection */
        return $object->getValue();
    }

    /**
     * Check whether $fieldType is a class name of custom renderer, and not just a type of input element
     *
     * @param string $fieldType
     * @return bool
     */
    protected function isClassName($fieldType)
    {
        return preg_match('/[A-Z]/', $fieldType) > 0;
    }
}
