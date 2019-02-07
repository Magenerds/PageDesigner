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
use Magenerds\WysiwygWidget\Block\Widget\Editor;
use Magenerds\WysiwygWidget\Wysiwyg\Encoder;

/**
 * Class PageDesignerUtil
 *
 * @package     Magenerds\PageDesigner\Utils
 * @file        PageDesignerUtil.php
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
class PageDesignerUtil
{
    /**
     * @var Encoder
     */
    protected $encoder;

    /**
     * PageDesignerUtil constructor.
     *
     * @param Encoder $encoder
     */
    public function __construct(
        Encoder $encoder
    )
    {
        $this->encoder = $encoder;
    }

    /**
     * Creates a valid json for the page designer
     * from an existing html
     *
     * @param string $html
     * @return string
     */
    public function getJsonFromHtml($html)
    {
        return json_encode([
            "version" => Constants::VERSION,
            "rows" => [[
                "columns" => [[
                    "gridSize" => [
                        "md" => Constants::DEFAULT_GRID_SIZE,
                    ],
                    "content" => sprintf('{{widget type="%s" content="%s"}}', Editor::class, $this->encoder->encode($html)),
                ]],
            ]],
        ]);
    }

    /**
     * Check if json is present or not in the given array
     *
     * @param $data
     * @return bool
     */
    public function shouldGenerateJson($data)
    {
        return isset($data[Constants::ATTR_CONTENT]) && (!isset($data[Constants::ATTR_PAGE_DESIGNER_JSON]) || empty($data[Constants::ATTR_PAGE_DESIGNER_JSON]));
    }
}
