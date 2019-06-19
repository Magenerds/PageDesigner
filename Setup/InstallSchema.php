<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Setup;

use Magenerds\PageDesigner\Constants;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\SetupInterface;

/**
 * Class InstallSchema
 *
 * @package     Magenerds\PageDesigner\Setup
 * @file        InstallSchema.php
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julian Schlarb <j.schlarb@techdivision.com>
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Install schema
     *
     * @param SetupInterface|SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // add json column to tables
        foreach (Constants::CONTENT_TABLES as $table) {
            $this->addPageDesignerJsonColumn($setup, $setup->getTable($table));
        }

        $setup->endSetup();
    }

    /**
     * Add page designer json column
     *
     * @param SetupInterface|SchemaSetupInterface $setup
     * @param string $table
     */
    protected function addPageDesignerJsonColumn(SchemaSetupInterface $setup, $table)
    {
        $column = [
            'type' => Table::TYPE_TEXT,
            'nullable' => true,
            'comment' => 'Contains the json of a page for the page designer',
        ];

        $connection = $setup->getConnection();
        $connection->addColumn($table, Constants::ATTR_PAGE_DESIGNER_JSON, $column);
    }
}
