<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Utils;

use Magenerds\PageDesigner\Constants;

/**
 * Class PageDesignerUtil
 *
 * @package     Magenerds\PageDesigner\Utils
 * @file        PageDesignerUtil.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
class PageDesignerUtil
{
    /**
     * Creates a valid json for the page designer
     *
     * @param string $html
     * @return string
     */
    public function getPageDesignerJsonFromHtml($html)
    {
        // build html from content
        $encodedHtml = base64_encode($html);
        $content = '{{widget type="%s" content="' . Constants::BASE64_PREFIX . '%s"}}';
        $content = sprintf($content, Constants::WIDGET_TYPE, $encodedHtml);

        // add to page designer object
        $object = [
            "version" => Constants::VERSION,
            "rows" => [
                [
                    "columns" => [
                        [
                            "gridSize" => [
                                "md" => Constants::DEFAULT_GRID_SIZE,
                            ],
                            "content" => $content,
                        ]
                    ]
                ]
            ]
        ];

        // return json
        return json_encode($object);
    }

    /**
     * Check for {@link Constants::ATTR_PAGE_DESIGNER_JSON} is present or not in the given array
     *
     * @param $data
     * @return bool
     */
    public function mustPageDesignerJsonProvided($data)
    {
        // check for existence of attribute
        if (!isset($data[Constants::ATTR_CONTENT])) {
            return false;
        }

        // check if it is not empty
        if (isset($data[Constants::ATTR_PAGE_DESIGNER_JSON])
            && !empty($data[Constants::ATTR_PAGE_DESIGNER_JSON])
        ) {
            return false;
        }

        // it's filled
        return true;
    }
}
