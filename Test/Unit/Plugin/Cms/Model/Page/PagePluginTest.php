<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Test\Unit\Plugin\Cms\Model\Page;

use Magento\Cms\Api\Data\PageInterface;
use Magenerds\PageDesigner\Plugin\Cms\Model\Page\PagePlugin;
use Magenerds\PageDesigner\Utils\HtmlRendererInterface;

/**
 * Class PagePluginTest
 *
 * @package     Magenerds\PageDesigner\Test\Unit\Plugin\Cms\Model\Page
 * @file        PagePluginTest.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
class PagePluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Holds the PagePlugin
     *
     * @var PagePlugin
     */
    protected $plugin;

    /**
     * Defines the html renderer
     *
     * @var HtmlRendererInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $htmlRenderer;

    /**
     * Holds the page interface
     *
     * @var PageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entity;

    /**
     * Sets up the test
     */
    protected function setUp()
    {
        // setup htmlRender mock to validate that methods are called
        $this->htmlRenderer = $this->getMockBuilder(HtmlRendererInterface::class)
            ->getMock();

        // setup entity mock with an additional method to validate that methods are called
        $this->entity = $this->getMockBuilder(PageInterface::class)
            ->setMethods(['getPageDesignerJson', 'setContent'])
            ->getMockForAbstractClass();

        $this->plugin = new PagePlugin($this->htmlRenderer);
    }

    /**
     * Tests html rendering
     */
    public function testBeforeSaveToEnsureJsonIsRenderToHtml()
    {
        // setup mock functions
        $this->entity->expects(static::once())
            ->method('getPageDesignerJson')
            ->willReturn('{}');

        $this->entity->expects(static::once())
            ->method('setContent')
            ->withAnyParameters();

        $this->htmlRenderer->expects(static::once())
            ->method('toHtml')
            ->withAnyParameters();

        // call method
        $this->plugin->beforeSave($this->entity);
    }

    /**
     * Tests case for setup script
     */
    public function testBeforeSaveSetupScript()
    {
        // setup mock functions
        $this->entity->expects(static::once())
            ->method('getPageDesignerJson')
            ->willReturn(null);

        $this->entity->expects(static::never())
            ->method('setContent')
            ->withAnyParameters();

        $this->htmlRenderer->expects(static::never())
            ->method('toHtml')
            ->withAnyParameters();

        // call method
        $this->plugin->beforeSave($this->entity);
    }
}
