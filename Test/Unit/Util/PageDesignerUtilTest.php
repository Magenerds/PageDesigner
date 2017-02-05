<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Test\Unit\Utils;

use Magenerds\PageDesigner\Utils\PageDesignerUtil;

/**
 * Class PageDesignerUtilTest
 *
 * @package     Magenerds\PageDesigner\Test\Unit\Utils
 * @file        PageDesignerUtilTest.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
class PageDesignerUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Defines the page designer util
     *
     * @var PageDesignerUtil
     */
    private $pageDesignerUtil;

    /**
     * Sets up the html renderer
     */
    protected function setUp()
    {
        $this->pageDesignerUtil = new PageDesignerUtil();
    }

    /**
     * Test to ensure that html is enclosed with page designer json valid schema
     */
    public function testGetPageDesignerJsonFromHtml()
    {
        $html = '<p>content</p>';

        $json = $this->pageDesignerUtil->getPageDesignerJsonFromHtml($html);

        $expected = '{"version":"1.0.0","rows":[{"columns":[{"gridSize":{"xs":12,"sm":12,"md":12,"lg":12},"content":"{{widget type=\"Magenerds\\\\PageDesigner\\\\Block\\\\Widget\\\\Editor\" content=\">>>BASE64>>>PHA+Y29udGVudDwvcD4=\"}}"}]}]}';

        static::assertEquals($expected, $json);
    }
}
