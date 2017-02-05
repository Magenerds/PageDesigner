<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Component;

use Magento\Ui\Component\Form\Element\AbstractElement;

/**
 * Class PageDesigner
 *
 * @package     Magenerds\PageDesigner\Component
 * @file        PageDesigner.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
class PageDesigner extends AbstractElement
{
    /**
     * Defines the component name
     *
     * @type string
     */
    const NAME = 'page_designer';

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
