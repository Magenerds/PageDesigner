<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Plugin\Cms\Model\Block;

use Magento\Cms\Api\Data\BlockInterface;
use Magenerds\PageDesigner\Utils\HtmlRendererInterface;

/**
 * Class BlockPlugin
 *
 * @package     Magenerds\PageDesigner\Plugin\Cms\Model\Block
 * @file        BlockPlugin.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
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
    private $htmlRenderer;

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
     * Manipulates the Block entity before it is saved
     *
     * @param BlockInterface $block
     * @param array $arguments
     */
    public function beforeSave(BlockInterface $block, ... $arguments) // NOSONAR
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $json = $block->getPageDesignerJson();

        if (strlen(trim($json)) > 0) {
            $block->setContent($this->htmlRenderer->toHtml($json));
        }
    }
}
