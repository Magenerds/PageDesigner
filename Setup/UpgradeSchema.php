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
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\SetupInterface;

/**
 * Class UpgradeSchema
 *
 * @package     Magenerds\PageDesigner\Setup
 * @file        UpgradeSchema.php
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Martin Eisenführer <m.eisenfuehrerb@techdivision.com>
 * @author      Simon Sippert <s.sippert@techdivision.com>
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrade schema
     *
     * @param SetupInterface|SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // add remove column to tables
        if (version_compare($context->getVersion(), '2.1.0') < 0) {
            foreach (Constants::CONTENT_TABLES as $table) {
                $this->addPageDesignerRemoveColumn($setup, $setup->getTable($table));
            }
        }

        // fix json column
        if (version_compare($context->getVersion(), '2.1.1') < 0) {
            foreach (Constants::CONTENT_TABLES as $table) {
                $this->fixPageDesignerJsonColumn($setup, $setup->getTable($table));
            }
        }

        $setup->endSetup();
    }

    /**
     * Add the page designer column
     *
     * @param SetupInterface|SchemaSetupInterface $setup
     * @param string $table
     */
    protected function addPageDesignerRemoveColumn(SchemaSetupInterface $setup, $table)
    {
        $column = [
            'type' => Table::TYPE_SMALLINT,
            'nullable' => false,
            'default' => 0,
            'comment' => 'Remove Page Designer Markup',
        ];

        $connection = $setup->getConnection();
        $connection->addColumn($table, Constants::ATTR_PAGE_DESIGNER_REMOVE, $column);
    }


    /**
     * Add the page designer column
     *
     * @param SetupInterface|SchemaSetupInterface $setup
     * @param string $table
     */
    protected function fixPageDesignerJsonColumn(SchemaSetupInterface $setup, $table)
    {
        $column = [
            'type' => Table::TYPE_TEXT,
            'length' => '2M',
            'nullable' => true,
            'comment' => 'Contains the json of a page for the page designer',
        ];

        $connection = $setup->getConnection();
        $connection->changeColumn($table, Constants::ATTR_PAGE_DESIGNER_JSON, Constants::ATTR_PAGE_DESIGNER_JSON, $column);
    }
}
