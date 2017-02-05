<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Block\Widget;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Magenerds\PageDesigner\Constants;

/**
 * Class Editor
 *
 * @package     Magenerds\PageDesigner\Block\Widget
 * @file        Editor.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 */
class Editor extends Template implements BlockInterface
{
    /**
     * Defines the filter provider
     *
     * @var FilterProvider
     */
    protected $_filterProvider;

    /**
     * Editor constructor.
     *
     * @param FilterProvider $filterProvider
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        FilterProvider $filterProvider,
        Context $context,
        array $data = []
    )
    {
        $this->_filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }

    /**
     * Prepare HTML content
     *
     * @return string
     */
    protected function _toHtml()
    {
        // get content
        $content = $this->getData('content');

        // check if content has been encoded with base64
        if ($content && is_string($content) && strpos($content, Constants::BASE64_PREFIX) === 0) {
            // decode content
            $content = base64_decode(str_replace([Constants::BASE64_PREFIX, ' '], ['', '+'], $content));
        }

        // output content
        return $this->_filterProvider->getPageFilter()->filter($content);
    }
}
