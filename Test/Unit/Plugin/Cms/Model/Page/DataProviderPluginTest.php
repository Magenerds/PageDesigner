<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Test\Plugin\Cms\Model\Page;

use Magento\Cms\Model\Page\DataProvider;
use Magenerds\PageDesigner\Plugin\Cms\Model\Page\DataProviderPlugin;
use Magenerds\PageDesigner\Utils\PageDesignerUtil;

/**
 * Class DataProviderPluginTest
 *
 * @package     Magenerds\PageDesigner\Test\Plugin\Cms\Model\Page
 * @file        DataProviderPluginTest.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
class DataProviderPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Defines the page designer util
     *
     * @var PageDesignerUtil|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageDesignerUtil;

    /**
     * @var DataProviderPlugin
     */
    private $plugin;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * Sets up the test
     */
    protected function setUp()
    {
        // setup htmlRender mock to validate that methods are called
        $this->pageDesignerUtil = $this->getMockBuilder(PageDesignerUtil::class)
            ->disableOriginalConstructor()->getMock();

        $this->dataProvider = $this->getMockBuilder(DataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new DataProviderPlugin($this->pageDesignerUtil);
    }

    /**
     * Test for new pages that have null as parameter
     */
    public function testAfterGetDataNewPage()
    {
        // setup mock functions
        $this->pageDesignerUtil->expects(static::never())
            ->method('mustPageDesignerJsonProvided')
            ->withAnyParameters();

        $this->pageDesignerUtil->expects(static::never())
            ->method('getPageDesignerJsonFromHtml')
            ->withAnyParameters();

        // it's an empty array if its new
        $data = null;

        $result = $this->plugin->afterGetData($this->dataProvider, $data);

        static::assertEquals(null, $result);
    }

    /**
     * Test that ensure that page_designer_json is created
     */
    public function testAfterGetDataWithoutPageDesignerJson()
    {
        // setup mock functions
        $this->pageDesignerUtil->expects(static::once())
            ->method('mustPageDesignerJsonProvided')
            ->withAnyParameters()
            ->willReturn(true);

        $this->pageDesignerUtil->expects(static::once())
            ->method('getPageDesignerJsonFromHtml')
            ->withAnyParameters()
            ->willReturn('a valid json string');

        $data = [['content' => '<p>some content<p/>']];

        $result = $this->plugin->afterGetData($this->dataProvider, $data);

        $expected = [['content' => '<p>some content<p/>', 'page_designer_json' => 'a valid json string']];

        static::assertEquals($expected, $result);
    }

    /**
     * Test to ensure that page_designer_json is not touched if its present
     */
    public function testAfterGetDataWithPageDesignerJson()
    {
        // setup mock functions
        $this->pageDesignerUtil->expects(static::once())
            ->method('mustPageDesignerJsonProvided')
            ->withAnyParameters()
            ->willReturn(false);

        $this->pageDesignerUtil->expects(static::never())
            ->method('getPageDesignerJsonFromHtml')
            ->withAnyParameters();

        $data = [['content' => '<p>content<p/>', 'page_designer_json' => '{}']];

        $result = $this->plugin->afterGetData($this->dataProvider, $data);

        $expected = [['content' => '<p>content<p/>', 'page_designer_json' => '{}']];

        static::assertEquals($expected, $result);
    }
}
