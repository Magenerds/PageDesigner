<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner;

/**
 * Class Constants
 *
 * @package     Magenerds\PageDesigner
 * @file        Constants.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
interface Constants
{
    /**
     * Defines the current version
     *
     * @type string
     */
    const VERSION = '1.0.0';

    /**
     * Defines the default grid size
     *
     * @type number
     */
    const DEFAULT_GRID_SIZE = 12;

    /**
     * This prefix gets added to base64 encoded content
     *
     * @type string
     */
    const BASE64_PREFIX = '>>>BASE64>>>';

    /**
     * Attribute name for page designer json
     *
     * @type string
     */
    const ATTR_PAGE_DESIGNER_JSON = 'page_designer_json';

    /**
     * Attribute name for content
     *
     * @type string
     */
    const ATTR_CONTENT = 'content';

    /**
     * Alias for PageDesigner wysiwyg widget
     *
     * @type string
     */
    const WIDGET_TYPE = 'Magenerds\PageDesigner\Block\Widget\Editor';

    /**
     * A list of tables that must have the column page_designer_json
     *
     * @type array
     */
    const CONTENT_TABLES = ['cms_block', 'cms_page'];
}
