<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Model\Source\CssClass;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Column
 *
 * @package     Magenerds\PageDesigner\Model\Source
 * @file        Column.php
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 */
class Column implements ArrayInterface
{
    /**
     * Holds the scope configuration
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Column constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get options for css classes
     *
     * @return array
     */
    public function toOptionArray()
    {
        // define values
        $values = [[
            'label' => __('(no class)'),
            'value' => '',
        ]];

        // get classes configuration
        foreach (explode(',', $this->scopeConfig->getValue('pagedesigner/general/css_classes_column')) as $class) {
            $values[] = [
                'label' => $class,
                'value' => $class,
            ];
        }

        // return values
        return $values;
    }
}
