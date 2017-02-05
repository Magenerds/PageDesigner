<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Model;

/**
 * Class ValidationState
 *
 * @package     Magenerds\PageDesigner\Model
 * @file        ValidatonState.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
class ValidationState extends \Magento\Framework\App\Arguments\ValidationState
{
    /**
     * Retrieve current validation state
     *
     * @return boolean
     */
    public function isValidationRequired()
    {
        // avoid xml schema validation
        return false;
    }
}
