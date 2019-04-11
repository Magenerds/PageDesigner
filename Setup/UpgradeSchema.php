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
 * Class InstallSchema
 *
 * @package     Magenerds\PageDesigner\Setup
 * @file        UpgradeSchema.php
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Mrtin Eisenführer <m.eisenfuehrerb@techdivision.com>
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Add `page_designer_json` to cms_block and cms_page
     *
     * @param SetupInterface|SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.1.0') < 0) {
            foreach (Constants::CONTENT_TABLES as $table) {
                $this->addPageDesignerColumn($setup, $setup->getTable($table));
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
    protected function addPageDesignerColumn(SchemaSetupInterface $setup, $table)
    {
        $column = [
            'type' => Table::TYPE_SMALLINT,
            'nullable' => true,
            'default' => 0,
            'comment' => 'Render without main pagedesigner markup',
        ];

        $connection = $setup->getConnection();
        $connection->addColumn($table, Constants::ATTR_PAGE_DESIGNER_REMOVE, $column);
    }
}
