<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magenerds\PageDesigner\Constants;

/**
 * Class InstallSchema
 *
 * @package     Magenerds\PageDesigner\Setup
 * @file        InstallSchema.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Add `page_designer_json` to cms_block and cms_page
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        foreach (Constants::CONTENT_TABLES as $table) {
            $this->addPageDesignerColumn($setup, $setup->getTable($table));
        }

        $setup->endSetup();
    }

    /**
     * Adds the page designer column
     *
     * @param SchemaSetupInterface $setup
     * @param string $table
     */
    private function addPageDesignerColumn(SchemaSetupInterface $setup, $table)
    {
        $column = [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'nullable' => true,
            'comment' => 'Contains the json of a page for the page designer',
        ];

        $connection = $setup->getConnection();
        $connection->addColumn($table, Constants::ATTR_PAGE_DESIGNER_JSON, $column);
    }
}
