<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Test\Unit\Util;

use Magento\Framework\Validator\Exception;
use Magenerds\PageDesigner\Utils\HtmlRenderer;

/**
 * Class HtmlRendererTest
 *
 * @package     Magenerds\PageDesigner\Test\Unit\Util
 * @file        HtmlRendererTest.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
class HtmlRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Defines the html renderer
     *
     * @var HtmlRenderer
     */
    private $htmlRenderer;

    /**
     * Sets up the html renderer
     */
    protected function setUp()
    {
        $this->htmlRenderer = new HtmlRenderer();
    }

    /**
     * Tests invalid json
     *
     * @expectedException Exception
     */
    public function testToHtmlThrowExceptionOnInvalidJson()
    {
        $this->htmlRenderer->toHtml('no json');
    }

    /**
     * Tests empty string
     */
    public function testToHtmlEmptyString()
    {
        $html = $this->htmlRenderer->toHtml('');
        $expectedHtml = '';

        static::assertEquals($expectedHtml, $html);
    }

    /**
     * Tests empty json
     */
    public function testToHtmlEmptyJson()
    {
        $html = $this->htmlRenderer->toHtml('{}');

        $expectedHtml = '';

        static::assertEquals($expectedHtml, $html);
    }

    /**
     * Tests empty rows
     */
    public function testToHtmlEmptyRows()
    {
        $html = $this->htmlRenderer->toHtml('{"rows": null}');

        $expectedHtml = '';

        static::assertEquals($expectedHtml, $html);
    }

    /**
     * Tests valid data
     */
    public function testToHtmlWithRowsAndColumn()
    {
        $html = $this->htmlRenderer->toHtml('{"version":"test","rows":[{"columns":[{"gridSize":{"xs":4,"sm":4,"md":4,"lg":4},"content":"Dummy"},{"gridSize":{"xs":4,"sm":4,"md":4,"lg":4},"content":"Dummy"}]},{"columns":[{"gridSize":{"xs":4,"sm":4,"md":4,"lg":4},"content":"Dummy"}]}]}');

        $expectedHtml = '<div class="pd-row row"><div class="pd-col col-xs-4 col-sm-4 col-md-4 col-lg-4">Dummy</div><div class="pd-col col-xs-4 col-sm-4 col-md-4 col-lg-4">Dummy</div></div><div class="pd-row row"><div class="pd-col col-xs-4 col-sm-4 col-md-4 col-lg-4">Dummy</div></div>';

        static::assertEquals($expectedHtml, $html);
    }

    /**
     * Test grid classes
     */
    public function testGetGridClassesForAllSizes()
    {
        // define column
        $column = [
            'settings' => ['class' => 'mt-50 mb-100'],
            'gridSize' => [
                HtmlRenderer::COLUMN_EXTRA_SMALL => 1,
                HtmlRenderer::COLUMN_SMALL_DEVICE => 10,
                HtmlRenderer::COLUMN_MEDIUM_DEVICES => 12,
                HtmlRenderer::COLUMN_LARGE_DEVICES => 7,
            ]
        ];

        // check classes
        $gridClasses = $this->htmlRenderer->getGridClasses($column);
        $expectedClass = 'col-xs-1 col-sm-10 col-md-12 col-lg-7';

        static::assertEquals($expectedClass, $gridClasses);
    }

    /**
     * Test grid classes
     */
    public function testGetGridClassesForMediumDevices()
    {
        // define column
        $column = [
            'settings' => ['class' => 'mt-50 mb-100'],
            'gridSize' => [
                HtmlRenderer::COLUMN_MEDIUM_DEVICES => 12,
            ]
        ];

        // check classes
        $gridClasses = $this->htmlRenderer->getGridClasses($column);
        $expectedClass = 'col-md-12';

        static::assertEquals($expectedClass, $gridClasses);
    }

    /**
     * Test close tag
     */
    public function testCloseTag()
    {
        // define column
        $tag = 'div';

        // check tag
        $html = $this->htmlRenderer->closeTag($tag);

        $expectedHtml = '</div>';

        static::assertEquals($expectedHtml, $expectedHtml);
    }

    /**
     * Test open tag without any attributes
     */
    public function testOpenTagWithoutAttributes()
    {
        // define column
        $tag = 'div';

        // check tag
        $html = $this->htmlRenderer->openTag($tag, []);

        $expectedHtml = '<div>';

        static::assertEquals($expectedHtml, $html);
    }

    /**
     * Test open tag with a attributes
     */
    public function testOpenTagWitAttributes()
    {
        // define column
        $tag = 'div';
        $attributes = ['class' => 'mt-10 caseSensitive', 'style' => 'display: none;'];

        // check tag
        $html = $this->htmlRenderer->openTag($tag, $attributes);

        $expectedHtml = '<div class="mt-10 caseSensitive" style="display: none;">';

        static::assertEquals($expectedHtml, $html);
    }

    /**
     * Test open tag with a attributes
     */
    public function testRenderColumnWithAdditionalAttributes()
    {

        // define column
        $column = [
            'settings' => [],
            'gridSize' => [
                HtmlRenderer::COLUMN_MEDIUM_DEVICES => 12,
                HtmlRenderer::COLUMN_LARGE_DEVICES => 7,
            ]
        ];
        $tag = 'div';
        $attributes = ['class' => 'mt-10', 'style' => 'display: none;'];

        // check tag
        $html = $this->htmlRenderer->openTag($tag, $attributes);

        $expectedHtml = '<div class="mt-10" style="display: none;">';

        static::assertEquals($expectedHtml, $html);
    }

    /**
     * Test open tag with a attributes
     */
    public function testRenderColumnEmpty()
    {
        $tag = 'div';
        $attributes = ['class' => 'mt-10', 'style' => 'display: none;'];

        // check tag
        $html = $this->htmlRenderer->openTag($tag, $attributes);

        $expectedHtml = '<div class="mt-10" style="display: none;">';

        static::assertEquals($expectedHtml, $html);
    }

    /**
     * Test to check that empty arrays and objects not throw a notice
     */
    public function testMergeAttributesWithEmptyObject()
    {
        $object = [];
        $additionalAttributes = [];
        $cleanUpAttributes = [];

        $result = $this->htmlRenderer->mergeAttributes($object, $additionalAttributes, $cleanUpAttributes);

        $expectedArray = [];

        static::assertEquals($expectedArray, $result);
    }

    /**
     * Test to check that settings merged with empty additional attributes
     */
    public function testMergeAttributesWithStyles()
    {
        $object = [
            'settings' => ['style' => ' display: block; ']
        ];
        $additionalAttributes = [];
        $cleanUpAttributes = [];

        $result = $this->htmlRenderer->mergeAttributes($object, $additionalAttributes, $cleanUpAttributes);

        $expectedArray = ['style' => 'display: block;'];

        static::assertEquals($expectedArray, $result);
    }

    /**
     * Test for row regex that blacklisted classes are removed and everything else merged as expected
     */
    public function testMergeAttributesWithRowRegex()
    {
        $object = [
            'settings' => [
                'style' => ' display: block; ',
                'class' => ' row row-border row-row fancy-row row mt-10 pt-10'
            ]
        ];
        $additionalAttributes = [
            HtmlRenderer::DOM_PROPERTY_CSS_CLASS => 'pd-row row',
        ];
        $cleanUpAttributes = [
            HtmlRenderer::DOM_PROPERTY_CSS_CLASS => HtmlRenderer::ROW_REGEX
        ];

        $result = $this->htmlRenderer->mergeAttributes($object, $additionalAttributes, $cleanUpAttributes);

        $expectedArray = [
            'style' => 'display: block;',
            'class' => 'row-border row-row fancy-row  mt-10 pt-10 pd-row row'
        ];

        static::assertEquals($expectedArray, $result);
    }

    /**
     * Test for column regex that blacklisted classes are removed and everything else merged as expected
     */
    public function testMergeAttributesWithColumnRegex()
    {
        $object = [
            'settings' => [
                'style' => ' display: block; ',
                'class' => 'col-md-102222 fancy-col-md-102222 col-border-12 col-md-12 col-md-12 '
            ]
        ];
        $additionalAttributes = [
            HtmlRenderer::DOM_PROPERTY_CSS_CLASS => 'col-md-2 col-xs-1',
        ];
        $cleanUpAttributes = [
            HtmlRenderer::DOM_PROPERTY_CSS_CLASS => HtmlRenderer::COLUMN_REGEX
        ];

        $result = $this->htmlRenderer->mergeAttributes($object, $additionalAttributes, $cleanUpAttributes);

        $expectedArray = [
            'style' => 'display: block;',
            'class' => 'fancy-col-md-102222 col-border-12 col-md-2 col-xs-1'
        ];

        static::assertEquals($expectedArray, $result);
    }

    /**
     * Test that additional attributes are append as expected
     */
    public function testMergeAttributesWithoutCssClass()
    {
        $object = [
            'settings' => ['style' => ' display: block; ']
        ];
        $additionalAttributes = [
            HtmlRenderer::DOM_PROPERTY_CSS_CLASS => 'pd-row row',
        ];
        $cleanUpAttributes = [
            HtmlRenderer::DOM_PROPERTY_CSS_CLASS => HtmlRenderer::ROW_REGEX
        ];

        $result = $this->htmlRenderer->mergeAttributes($object, $additionalAttributes, $cleanUpAttributes);

        $expectedArray = ['style' => 'display: block;', 'class' => 'pd-row row'];

        static::assertEquals($expectedArray, $result);
    }

    /**
     * Test for rending a single row without columns
     */
    public function testRenderRow()
    {
        $row = [
            'settings' => ['style' => ' display: block; ']
        ];

        $html = $this->htmlRenderer->renderRow($row);

        $expectedHtml = '<div style="display: block;" class="pd-row row"></div>';

        static::assertEquals($expectedHtml, $html);
    }
}
