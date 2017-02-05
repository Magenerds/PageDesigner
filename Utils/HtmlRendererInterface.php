<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Utils;

use Magento\Framework\Validator\Exception;

/**
 * Interface HtmlRendererInterface
 *
 * @package     Magenerds\PageDesigner\Utils
 * @file        HtmlRendererInterface.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
interface HtmlRendererInterface
{
    /**
     * Converts a json object to html
     *
     * @param string $json
     * @return string
     * @throws Exception
     */
    public function toHtml($json);
}
