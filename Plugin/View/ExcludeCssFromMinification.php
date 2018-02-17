<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Plugin\View;

use Magento\Framework\View\Asset\Minification;

/**
 * Class ExcludeCssFromMinification
 *
 * @package     Magenerds\PageDesigner\Plugin\View
 * @file        ExcludeCssFromMinification.php
 */
class ExcludeCssFromMinification
{
    /**
     * Adds css files to the minify_exclude config setting to avoid bug:
     * https://github.com/magento/magento2/issues/8552
     *
     * @param Minification $subject
     * @param callable $proceed
     * @param string $contentType
     * @return string[]
     */
    public function aroundGetExcludes(Minification $subject, callable $proceed, $contentType)
    {
        $result = $proceed($contentType);

        if ($contentType !== 'css') {
            return $result;
        }

        $result[] = '/Magenerds_PageDesigner/css/pd-ui.css';

        return $result;
    }
}
