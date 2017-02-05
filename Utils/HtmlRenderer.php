<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Utils;

use Magento\Framework\Validator\Exception as ValidatorException;

/**
 * Class HtmlRenderer
 *
 * @package     Magenerds\PageDesigner\Utils
 * @file        HtmlRenderer.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 * @author      Simon Sippert <s.sippert@techdivision.com>
 */
class HtmlRenderer implements HtmlRendererInterface
{
    /**
     * Bootstrap css class column prefix
     *
     * @type string
     */
    const COLUMN_PREFIX = 'col-';

    /**
     * Bootstrap css class delimiter
     *
     * @type string
     */
    const COLUMN_DELIMITER = '-';

    /**
     * Bootstrap css class for extra small devices (<768px)
     *
     * @type string
     */
    const COLUMN_EXTRA_SMALL = 'xs';

    /**
     * Bootstrap css class for Small devices (≥768px)
     *
     * @type string
     */
    const COLUMN_SMALL_DEVICE = 'sm';

    /**
     * Bootstrap css class for Medium devices (≥992px)
     *
     * @type string
     */
    const COLUMN_MEDIUM_DEVICES = 'md';

    /**
     * Bootstrap css class for Large devices (≥1200px)
     *
     * @type string
     */
    const COLUMN_LARGE_DEVICES = 'lg';

    /**
     * Bootstrap css class for row
     *
     * @type string
     */
    const CLASS_ROW = 'row';

    /**
     * Page designer css class for row
     *
     * @type string
     */
    const CLASS_PD_ROW = 'pd-row';

    /**
     * Page designer css class for column
     *
     * @type string
     */
    const CLASS_PD_COL = 'pd-col';

    /**
     * Row and column index for settings
     *
     * @type string
     */
    const SETTINGS = 'settings';

    /**
     * Default element tag
     *
     * @type string
     */
    const DEFAULT_ELEMENT_TAG = 'div';

    /**
     * Regex to remove columns definitions
     *
     * @type string
     */
    const COLUMN_REGEX = '/(?:^|(?<=[ ]))(' .
    HtmlRenderer::COLUMN_PREFIX . '(' .
    HtmlRenderer::COLUMN_EXTRA_SMALL . '|' .
    HtmlRenderer::COLUMN_SMALL_DEVICE . '|' .
    HtmlRenderer::COLUMN_MEDIUM_DEVICES . '|' .
    HtmlRenderer::COLUMN_MEDIUM_DEVICES . ')' .
    HtmlRenderer::COLUMN_DELIMITER . '\d+)(?=([ ]|$))/';

    /**
     * Regex to remove row definitions
     *
     * @type string
     */
    const CLASS_ROW_REGEX = '/(?:^|(?<=[ ]))(' . HtmlRenderer::CLASS_ROW . '|' . HtmlRenderer::CLASS_PD_ROW . ')(?=([ ]|$))/';

    /**
     * Defines the settings property for css classes
     *
     * @type string
     */
    const JSON_PROPERTY_CSS_CLASS = 'css_class';

    /**
     * Defines the dom property for css classes
     *
     * @type string
     */
    const DOM_PROPERTY_CSS_CLASS = 'class';

    /**
     * Converts a json object to html
     *
     * @param string $json page designer json
     * @return string
     * @throws ValidatorException
     */
    public function toHtml($json)
    {
        // define result
        $result = '';

        // avoid validation exception if not json is present
        if (strlen(trim($json)) === 0) {
            return $result;
        }

        // get json data
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ValidatorException(__('Page Designer detected an invalid form input.'));
        }

        // iterate over rows and render them
        if (isset($data['rows'])) {
            foreach ($data['rows'] as $row) {
                $result .= $this->renderRow($row);
            }
        }

        // return result
        return $result;
    }

    /**
     * Render a row to html
     *
     * @param array $row
     * @return string
     */
    public function renderRow(array $row)
    {
        // merge attributes
        $attributes = $this->mergeAttributes($row, [
            static::DOM_PROPERTY_CSS_CLASS => static::CLASS_PD_ROW . ' ' . static::CLASS_ROW,
        ], [
            static::DOM_PROPERTY_CSS_CLASS => static::CLASS_ROW_REGEX,
        ]);

        // open element tag
        $result = $this->openTag(static::DEFAULT_ELEMENT_TAG, $attributes);

        // if we have columns defined
        if (isset($row['columns'])) {
            // iterate over columns and parse them
            foreach ($row['columns'] as $column) {
                $result .= $this->renderColumn($column);
            }
        }

        // return result with closing element tag
        return $result . $this->closeTag(static::DEFAULT_ELEMENT_TAG);
    }

    /**
     * Parses the settings of a row or column
     *
     * @param array $element
     * @param array [$additionalAttributes]
     * @param array [$attributesCleanUp]
     * @return array
     */
    public function mergeAttributes(array $element, array $additionalAttributes = [], array $attributesCleanUp = [])
    {
        // retrieve settings
        $settings = isset($element[static::SETTINGS]) && is_array($element[static::SETTINGS]) ? $element[static::SETTINGS] : [];

        // define attributes
        $attributes = [];

        // map settings layout to attributes
        foreach ($settings as $key => $value) {
            if ($key === static::JSON_PROPERTY_CSS_CLASS) {
                $attributes[static::DOM_PROPERTY_CSS_CLASS] = $value;
            }
        }

        // clean up attributes by regex
        foreach ($attributesCleanUp as $attribute => $regex) {
            if (isset($attributes[$attribute])) {
                $attributes[$attribute] = preg_replace($regex, '', $attributes[$attribute]);
            }
        }

        // iterate over attributes
        foreach ($attributes as $attribute => &$value) {
            $value = trim($value);

            // append additional attribute if present
            if (isset($additionalAttributes[$attribute])) {
                //append whitespace and additional attributes
                $value .= ' ' . trim($additionalAttributes[$attribute]);

                // unset index for easy merge
                unset($additionalAttributes[$attribute]);
            }
        }

        // merge attributes
        $attributes = array_merge($attributes, $additionalAttributes);

        // remove whitespace and return attributes
        return array_map('trim', $attributes);
    }

    /**
     * Returns a single html with attributes
     *
     * @param string $tag Html tag to generate
     * @param array $attributes Xml attributes to append on the html tag
     * @return string
     */
    public function openTag($tag, array $attributes)
    {
        // define attribute
        $attribute = '';

        // append attributes
        if (!empty($attributes)) {
            foreach ($attributes as $name => $value) {
                $attribute .= sprintf(' %s="%s"', $name, $value);
            }
        }

        // return opening tag
        return sprintf('<%s%s>', $tag, $attribute);
    }

    /**
     * Render a single column to html
     *
     * @param array $column
     * @return string
     */
    public function renderColumn(array $column)
    {
        // merge attributes
        $attributes = $this->mergeAttributes($column, [
            static::DOM_PROPERTY_CSS_CLASS => static::CLASS_PD_COL . ' ' . $this->getGridClasses($column)
        ], [
            static::DOM_PROPERTY_CSS_CLASS => static::COLUMN_REGEX,
        ]);

        // open element
        $result = $this->openTag(static::DEFAULT_ELEMENT_TAG, $attributes);

        // add content
        $result .= isset($column['content']) ? $column['content'] : '';

        // return result with closing element tag
        return $result . $this->closeTag(static::DEFAULT_ELEMENT_TAG);
    }

    /**
     * Returns the grid classes for a column
     *
     * @param array $column
     * @return string grid classes
     */
    public function getGridClasses(array $column)
    {
        // define classes array
        $classes = [];

        // iterate over grid sizes and add classes
        foreach ($column['gridSize'] as $grid => $size) {
            $classes[] = static::COLUMN_PREFIX . $grid . static::COLUMN_DELIMITER . $size;
        }

        // return classes
        return implode(' ', $classes);
    }

    /**
     * Returns a close tag for an html element
     *
     * @param string $tag element tag to close
     * @return string An closing html tag
     */
    public function closeTag($tag)
    {
        return sprintf('</%s>', $tag);
    }
}
