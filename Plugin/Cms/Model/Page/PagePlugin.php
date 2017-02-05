<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Plugin\Cms\Model\Page;

use Magento\Cms\Api\Data\PageInterface;
use Magenerds\PageDesigner\Utils\HtmlRendererInterface;

/**
 * Class PagePlugin
 *
 * @package     Magenerds\PageDesigner\Plugin\Cms\Model\Page
 * @file        PagePlugin.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
final class PagePlugin
{
    /**
     * Defines the html renderer
     *
     * @var HtmlRendererInterface
     */
    private $htmlRenderer;

    /**
     * CmsPagePlugin constructor.
     *
     * @param HtmlRendererInterface $htmlRenderer
     */
    public function __construct(HtmlRendererInterface $htmlRenderer)
    {
        $this->htmlRenderer = $htmlRenderer;
    }

    /**
     * Manipulates the Page entity before it is saved
     *
     * @param PageInterface $page
     * @param array $arguments
     */
    public function beforeSave(PageInterface $page, ... $arguments) // NOSONAR
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $json = $page->getPageDesignerJson();

        if (strlen(trim($json)) > 0) {
            $page->setContent($this->htmlRenderer->toHtml($json));
        }
    }
}
