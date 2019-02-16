<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @copyright   Copyright (c) 2017 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

namespace Magestore\Customercredit\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getTable('credit_transaction');
        $installer->getConnection()->dropTable($installer->getTable('credit_transaction'));
        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'transaction_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Transaction ID'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Customer ID'
            )
            ->addColumn(
                'type_transaction_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Type Transaction ID'
            )
            ->addColumn(
                'detail_transaction',
                Table::TYPE_TEXT,
                250,
                ['nullable' => false, 'default' => ''],
                'Detail Transaction'
            )
            ->addColumn(
                'order_increment_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Order Increment ID'
            )
            ->addColumn(
                'amount_credit',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0'],
                'Amount Credit'
            )
            ->addColumn(
                'begin_balance',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0'],
                'Begin Balance'
            )
            ->addColumn(
                'end_balance',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0'],
                'End Balance'
            )
            ->addColumn(
                'transaction_time',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Transaction Time'
            )
            ->addColumn(
                'customer_group_ids',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Customer Group ID'
            )
            ->addColumn(
                'status',
                Table::TYPE_TEXT,
                250,
                ['nullable' => false, 'default' => ''],
                'Status'
            )
            ->addColumn(
                'spent_credit',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0'],
                'Spend Credit'
            )
            ->addColumn(
                'received_credit',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0'],
                'Receiving Credit'
            )
            ->addIndex(
                $installer->getIdxName('credit_transaction', ['customer_id']),
                ['customer_id']
            )
            ->setComment('Credit Transaction')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $installer->getConnection()->createTable($table);

        $tableName = $installer->getTable('credit_code');
        $installer->getConnection()->dropTable($installer->getTable('credit_code'));
        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'credit_code_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Credit Code ID'
            )
            ->addColumn(
                'credit_code',
                Table::TYPE_TEXT,
                250,
                ['nullable' => false],
                'Credit Code'
            )
            ->addColumn(
                'currency',
                Table::TYPE_TEXT,
                250,
                ['nullable' => false],
                'Currency'
            )
            ->addColumn(
                'description',
                Table::TYPE_TEXT,
                250,
                ['nullable' => false],
                'Description'
            )
            ->addColumn(
                'transaction_time',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Transaction Time'
            )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Status'
            )
            ->addColumn(
                'amount_credit',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0'],
                'Amount Credit'
            )
            ->addColumn(
                'recipient_email',
                Table::TYPE_TEXT,
                250,
                ['nullable' => false],
                'Recipient Email'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Customer Id'
            )
            ->setComment('Credit Code')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $installer->getConnection()->createTable($table);

        $tableName = $installer->getTable('type_transaction');
        $installer->getConnection()->dropTable($installer->getTable('type_transaction'));
        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'type_transaction_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Type Transaction ID'
            )
            ->addColumn(
                'transaction_name',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Transaction Name'
            )
            ->setComment('Type Transaction')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $installer->getConnection()->createTable($table);

        $tableName = $installer->getTable('customer_credit');
        $installer->getConnection()->dropTable($installer->getTable('customer_credit'));
        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'credit_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Credit ID'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Customer ID'
            )
            ->addColumn(
                'credit_balance',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false],
                'Credit Balance'
            )
            ->addIndex(
                $installer->getIdxName('customer_credit', ['customer_id']),
                ['customer_id']
            )
            ->addForeignKey(
                $installer->getFkName('customer_credit', 'customer_id', 'customer_entity', 'entity_id'),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Customer Credit')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $installer->getConnection()->createTable($table);

        $data = array();
        $data['0']['transaction_name'] = 'Changed by admin';
        $data['1']['transaction_name'] = 'Send credit to friends';
        $data['2']['transaction_name'] = 'Receive Credit from Friends';
        $data['3']['transaction_name'] = 'Redeem Credit';
        $data['4']['transaction_name'] = 'Receive order refund by credit';
        $data['5']['transaction_name'] = 'Check Out by Credit';
        $data['6']['transaction_name'] = 'Cancel sending credit';
        $data['7']['transaction_name'] = 'Customer Buy Credit';
        $data['8']['transaction_name'] = 'Cancel Order';
        $data['9']['transaction_name'] = 'Refund Credit Product';
        $data['10']['transaction_name'] = 'Purchase Ticket';
        $data['11']['transaction_name'] = 'Winning Ticket';
        $installer->getConnection()->insertMultiple($setup->getTable('type_transaction'), $data);

        $installer->getConnection()->addColumn($installer->getTable('sales_order'), 'customercredit_discount', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);
        $installer->getConnection()->addColumn($installer->getTable('sales_order'), 'base_customercredit_discount', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);
        $installer->getConnection()->addColumn($installer->getTable('sales_order'), 'base_customercredit_discount_for_shipping', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);
        $installer->getConnection()->addColumn($installer->getTable('sales_order'), 'customercredit_discount_for_shipping', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);
        $installer->getConnection()->addColumn($installer->getTable('sales_order'), 'base_customercredit_hidden_tax', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);
        $installer->getConnection()->addColumn($installer->getTable('sales_order'), 'customercredit_hidden_tax', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);
        $installer->getConnection()->addColumn($installer->getTable('sales_order'), 'base_customercredit_shipping_hidden_tax', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);
        $installer->getConnection()->addColumn($installer->getTable('sales_order'), 'customercredit_shipping_hidden_tax', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);

        $installer->getConnection()->addColumn($installer->getTable('sales_order_item'), 'customercredit_discount', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);
        $installer->getConnection()->addColumn($installer->getTable('sales_order_item'), 'base_customercredit_discount', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);
        $installer->getConnection()->addColumn($installer->getTable('sales_order_item'), 'base_customercredit_hidden_tax', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);
        $installer->getConnection()->addColumn($installer->getTable('sales_order_item'), 'customercredit_hidden_tax', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);

        $installer->getConnection()->addColumn($installer->getTable('sales_invoice'), 'customercredit_discount', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);
        $installer->getConnection()->addColumn($installer->getTable('sales_invoice'), 'base_customercredit_discount', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);
        $installer->getConnection()->addColumn($installer->getTable('sales_invoice'), 'base_customercredit_hidden_tax', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);
        $installer->getConnection()->addColumn($installer->getTable('sales_invoice'), 'customercredit_hidden_tax', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);

        $installer->getConnection()->addColumn($installer->getTable('sales_creditmemo'), 'customercredit_discount', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);
        $installer->getConnection()->addColumn($installer->getTable('sales_creditmemo'), 'base_customercredit_discount', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);
        $installer->getConnection()->addColumn($installer->getTable('sales_creditmemo'), 'base_customercredit_hidden_tax', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);
        $installer->getConnection()->addColumn($installer->getTable('sales_creditmemo'), 'customercredit_hidden_tax', ['type'=>Table::TYPE_DECIMAL, 'size'=>'12,4', 'nullable' => true,'comment' =>'customer credit']);

        $installer->endSetup();
    }
}