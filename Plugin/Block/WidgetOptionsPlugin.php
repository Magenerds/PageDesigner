<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Plugin\Block;

use Magento\Widget\Block\Adminhtml\Widget\Options;
use Magenerds\PageDesigner\Constants;

/**
 * Class WidgetOptionsPlugin
 *
 * @package     Magenerds\PageDesigner\Plugin\Block
 * @file        WidgetOptionsPlugin.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 */
final class WidgetOptionsPlugin
{
    /**
     * Decodes Base64-encoded fields to be output in their normal state
     *
     * @param Options $subject
     */
    public function beforeAddFields(Options $subject)
    {
        // get widget values
        $params = $subject->getWidgetValues();

        // iterate over values
        foreach ($params as &$value) {
            // check if value has been encoded with base64
            if ($value && is_string($value) && strpos($value, Constants::BASE64_PREFIX) === 0) {
                // decode value
                $value = base64_decode(str_replace(Constants::BASE64_PREFIX, '', $value));
            }
        }

        // set decoded values
        $subject->setWidgetValues($params);
    }
}
