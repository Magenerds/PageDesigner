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
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
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
    const VERSION = '3.0.0'; // ONLY(!) change this if the JSON data structure changes

    /**
     * Defines the default grid size
     *
     * @type number
     */
    const DEFAULT_GRID_SIZE = 12;

    /**
     * Attribute name for page designer json
     *
     * @type string
     */
    const ATTR_PAGE_DESIGNER_JSON = 'page_designer_json';

    /**
     * Attribute name for page designer remove flag
     *
     * @type string
     */
    const ATTR_PAGE_DESIGNER_REMOVE = 'page_designer_remove';

    /**
     * Attribute name for content
     *
     * @type string
     */
    const ATTR_CONTENT = 'content';

    /**
     * A list of tables that must have the column page_designer_json
     *
     * @type array
     */
    const CONTENT_TABLES = ['cms_block', 'cms_page'];
}
