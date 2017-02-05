<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magenerds\PageDesigner\Constants;

/**
 * Class Uninstall
 *
 * @package     Magenerds\PageDesigner\Setup
 * @file        Uninstall.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
class Uninstall implements UninstallInterface
{
    /**
     * Invoked when remove-data flag is set during module uninstall.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context) //NOSONAR
    {
        $setup->startSetup();

        foreach (Constants::CONTENT_TABLES as $table) {
            $this->dropPageDesignerColumn($setup, $setup->getTable($table));
        }

        $setup->endSetup();
    }

    /**
     * Drop the page designer column
     *
     * @param SchemaSetupInterface $setup
     * @param string $table
     */
    private function dropPageDesignerColumn(SchemaSetupInterface $setup, $table)
    {
        $connection = $setup->getConnection();
        $connection->dropColumn($table, Constants::ATTR_PAGE_DESIGNER_JSON);
    }
}
