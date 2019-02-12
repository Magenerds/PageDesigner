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
use Magenerds\PageDesigner\Utils\PageDesignerUtil;
use Magento\Cms\Model\Page\DataProvider;

/**
 * Class DataProviderPlugin
 *
 * @package     Magenerds\PageDesigner\Plugin\Cms\Model\Page
 * @file        DataProviderPlugin.php
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
final class DataProviderPlugin
{
    /**
     * @var PageDesignerUtil
     */
    protected $pageDesignerUtil;

    /**
     * DataProviderPlugin constructor.
     *
     * @param PageDesignerUtil $pageDesignerUtil
     */
    public function __construct(PageDesignerUtil $pageDesignerUtil)
    {
        $this->pageDesignerUtil = $pageDesignerUtil;
    }

    /**
     * Manipulate return value of getData
     *
     * @param DataProvider $dataProvider
     * @param array $result
     * @return array
     */
    public function afterGetData(
        /** @noinspection PhpUnusedParameterInspection */
        DataProvider $dataProvider, // NOSONAR
        $result
    )
    {
        if (is_array($result)) {
            foreach ($result as &$data) {
                if ($this->pageDesignerUtil->shouldGenerateJson($data)) {
                    $data[Constants::ATTR_PAGE_DESIGNER_JSON] = $this->pageDesignerUtil->getJsonFromHtml($data[Constants::ATTR_CONTENT]);
                }
            }
        }

        return $result;
    }
}
