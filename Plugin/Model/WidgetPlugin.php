<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Plugin\Model;

use Closure;
use Magento\Widget\Model\Widget;
use Magenerds\PageDesigner\Constants;

/**
 * Class WidgetPlugin
 *
 * @package     Magenerds\PageDesigner\Plugin\Model
 * @file        WidgetPlugin.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 */
final class WidgetPlugin
{
    /**
     * Encodes values with Base64 that cannot be saved in normal state because of quotes in them etc.
     *
     * @param Widget $subject
     * @param Closure $proceed
     * @param $type
     * @param array $params
     * @param bool $asIs
     * @return mixed
     */
    public function aroundGetWidgetDeclaration(
        Widget $subject,
        Closure $proceed,
        $type,
        $params = [],
        $asIs = true
    )
    {
        // check for editor widget
        if ($type === Constants::WIDGET_TYPE) {
            // iterate over values
            foreach ($params as $name => &$value) {
                // check if value is a string
                if ($value && is_string($value) && $name === 'content') {
                    // encode value
                    $value = Constants::BASE64_PREFIX . base64_encode($value);
                }
            }
        }

        // proceed with function
        return $proceed($type, $params, $asIs);
    }
}
