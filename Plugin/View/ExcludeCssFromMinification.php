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
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Pieter Hoste
 */
class ExcludeCssFromMinification
{
    /**
     * Add css file to the minify_exclude config setting to avoid a bug with minification of this css file
     * Only occurs with tubalmartin/cssmin < v2.4.8-p7
     *
     * Ref:
     * - https://github.com/magento/magento2/issues/8552
     * - https://github.com/magento/magento2/pull/9027
     * - https://github.com/tubalmartin/YUI-CSS-compressor-PHP-port/commit/59e5e8b0b8225244aea90da3cd5f81b1ba575da2#diff-80f11f7052c350cef68c3041a5479789R588
     *
     * @param Minification $subject
     * @param callable $proceed
     * @param string $contentType
     * @return string[]
     */
    public function aroundGetExcludes(
        /** @noinspection PhpUnusedParameterInspection */
        Minification $subject, // NOSONAR
        callable $proceed,
        $contentType
    )
    {
        $result = $proceed($contentType);
        if ($contentType !== 'css' || !$this->isCssminLibraryOlderThanVersion3()) {
            return $result;
        }

        $result[] = '/Magenerds_PageDesigner/css/pd-ui.css';
        return $result;
    }

    /**
     * Check if the current version of tubalmartin/cssmin can cause problems with minifying
     * Strictly speaking, not all versions 2.x.x of the library caused issues (since it got fixed in v2.4.8-p7),
     * but here we check to see if version 2 is being used, where the class was called 'CSSmin' and existed in the global namespace,
     * from version 3 on out, the class name was changed and put in a namespace: 'tubalmartin\CssMin\Minifier'
     * So if we detect version 2 of the library, we assume it is broken
     *
     * Ref:
     * - https://github.com/tubalmartin/YUI-CSS-compressor-PHP-port/compare/v2.4.8-p10...v3.0.0#diff-411bfa1ca68f3e86ffb0276bcffd838dL22
     *
     * @return bool
     */
    protected function isCssminLibraryOlderThanVersion3()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return class_exists(\CSSmin::class);
    }
}
