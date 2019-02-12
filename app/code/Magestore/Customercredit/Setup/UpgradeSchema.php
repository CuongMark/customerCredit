<?php
/**
 * Copyright © 2017 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Customercredit\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
/**
 * Upgrade the Store Credit module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    const QUOTE_TABLE = 'quote';
    const QUOTE_ITEM_TABLE = 'quote_item';
    const QUOTE_ADDRESS_TABLE = 'quote_address';
    const ORDER_TABLE = 'sales_order';
    const ORDER_ITEM_TABLE = 'sales_order_item';
    const CUSTOMER_CREDIT = 'customer_credit';
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            $this->updateRebuiltDiscount($setup);
        }
        if (version_compare($context->getVersion(), '2.2.1', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::CUSTOMER_CREDIT),
                'updated_at',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'length' => null,
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
                    'comment' => 'Updated At'
                ]
            );
        }
    }
    
    
    public function updateRebuiltDiscount(SchemaSetupInterface $setup){
        /* Add column for quote table*/
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::QUOTE_TABLE), 'magestore_base_discount')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_TABLE),
                'magestore_base_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Magestore Base Discount'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::QUOTE_TABLE), 'magestore_discount')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_TABLE),
                'magestore_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Magestore Discount'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::QUOTE_TABLE), 'base_customercredit_discount')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_TABLE),
                'base_customercredit_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Base Customer Credit Discount'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::QUOTE_TABLE), 'customercredit_discount')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_TABLE),
                'customercredit_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Customer Credit Discount'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::QUOTE_TABLE), 'base_customercredit_discount_for_shipping')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_TABLE),
                'base_customercredit_discount_for_shipping',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Base Customer Credit Discount For Shipping'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::QUOTE_TABLE), 'customercredit_discount_for_shipping')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_TABLE),
                'customercredit_discount_for_shipping',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Customer Credit Discount For Shipping'
                ]
            );
        }

        /* Add column for quote address table*/
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::QUOTE_ADDRESS_TABLE), 'magestore_base_discount')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_ADDRESS_TABLE),
                'magestore_base_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Magestore Base Discount'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::QUOTE_ADDRESS_TABLE), 'magestore_discount')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_ADDRESS_TABLE),
                'magestore_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Magestore Discount'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::QUOTE_ADDRESS_TABLE), 'base_customercredit_discount')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_ADDRESS_TABLE),
                'base_customercredit_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Base Customer Credit Discount'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::QUOTE_ADDRESS_TABLE), 'customercredit_discount')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_ADDRESS_TABLE),
                'customercredit_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Customer Credit Discount'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::QUOTE_ADDRESS_TABLE), 'base_customercredit_discount_for_shipping')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_ADDRESS_TABLE),
                'base_customercredit_discount_for_shipping',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Base Customer Credit Discount For Shipping'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::QUOTE_ADDRESS_TABLE), 'customercredit_discount_for_shipping')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_ADDRESS_TABLE),
                'customercredit_discount_for_shipping',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Customer Credit Discount For Shipping'
                ]
            );
        }


        /* Add column for quote item table*/
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::QUOTE_ITEM_TABLE), 'magestore_base_discount')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_ITEM_TABLE),
                'magestore_base_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Magestore Base Discount'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::QUOTE_ITEM_TABLE), 'magestore_discount')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_ITEM_TABLE),
                'magestore_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Magestore Discount'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::QUOTE_ITEM_TABLE), 'base_customercredit_discount')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_ITEM_TABLE),
                'base_customercredit_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Base Customer Credit Discount'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::QUOTE_ITEM_TABLE), 'customercredit_discount')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::QUOTE_ITEM_TABLE),
                'customercredit_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Customer Credit Discount'
                ]
            );
        }
        
        /* Add column for order table*/
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::ORDER_TABLE), 'magestore_base_discount')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::ORDER_TABLE),
                'magestore_base_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Magestore Base Discount'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::ORDER_TABLE), 'magestore_discount')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::ORDER_TABLE),
                'magestore_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Magestore Discount'
                ]
            );
        }
        
        /* Add column for order item table*/
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::ORDER_ITEM_TABLE), 'magestore_base_discount')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::ORDER_ITEM_TABLE),
                'magestore_base_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Magestore Base Discount'
                ]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable(self::ORDER_ITEM_TABLE), 'magestore_discount')) {
            $setup->getConnection()->addColumn(
                $setup->getTable(self::ORDER_ITEM_TABLE),
                'magestore_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Magestore Discount'
                ]
            );
        }
    }
}
