<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Plugin\Cms\Model\Block;

use Magenerds\PageDesigner\Constants;
use Magenerds\PageDesigner\Utils\HtmlRendererInterface;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\ResourceModel\Block as BlockResource;
use Magento\Framework\Validator\Exception;

/**
 * Class BlockPlugin
 *
 * @package     Magenerds\PageDesigner\Plugin\Cms\Model\Block
 * @file        BlockPlugin.php
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
final class BlockPlugin
{
    /**
     * Defines the html renderer
     *
     * @var HtmlRendererInterface
     */
    protected $htmlRenderer;

    /**
     * BlockPlugin constructor.
     *
     * @param HtmlRendererInterface $htmlRenderer
     */
    public function __construct(HtmlRendererInterface $htmlRenderer)
    {
        $this->htmlRenderer = $htmlRenderer;
    }

    /**
     * Manipulate the Block entity before it is saved
     *
     * @param BlockResource $blockResource
     * @param Block $block
     * @param mixed ...$arguments
     * @throws Exception
     */
    public function beforeSave(
        /** @noinspection PhpUnusedParameterInspection */
        BlockResource $blockResource, // NOSONAR
        Block $block,
        ... $arguments // NOSONAR
    )
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $json = $block->getData(Constants::ATTR_PAGE_DESIGNER_JSON);
        if (is_string($json) && strlen(trim($json)) > 0) {
            $block->setContent($this->htmlRenderer->toHtml($json, $block->getData(Constants::ATTR_PAGE_DESIGNER_REMOVE)));
        }
    }
}
