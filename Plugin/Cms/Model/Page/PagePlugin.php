<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Plugin\Cms\Model\Page;

use Magenerds\PageDesigner\Constants;
use Magenerds\PageDesigner\Utils\HtmlRendererInterface;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Framework\Validator\Exception;

/**
 * Class PagePlugin
 *
 * @package     Magenerds\PageDesigner\Plugin\Cms\Model\Page
 * @file        PagePlugin.php
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
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
    protected $htmlRenderer;

    /**
     * PagePlugin constructor.
     *
     * @param HtmlRendererInterface $htmlRenderer
     */
    public function __construct(HtmlRendererInterface $htmlRenderer)
    {
        $this->htmlRenderer = $htmlRenderer;
    }

    /**
     * Manipulate the Page entity before it is saved
     *
     * @param PageResource $pageResource
     * @param Page $page
     * @param mixed ...$arguments
     * @throws Exception
     */
    public function beforeSave(
        /** @noinspection PhpUnusedParameterInspection */
        PageResource $pageResource, // NOSONAR
        Page $page,
        ... $arguments // NOSONAR
    )
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $json = $page->getData(Constants::ATTR_PAGE_DESIGNER_JSON);
        if (is_string($json) && strlen(trim($json)) > 0) {
            $page->setContent($this->htmlRenderer->toHtml($json, $page->getData(Constants::ATTR_PAGE_DESIGNER_REMOVE)));
        }
    }
}
