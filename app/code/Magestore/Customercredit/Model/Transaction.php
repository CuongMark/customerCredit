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

namespace Magestore\Customercredit\Model;

use Magento\Customer\Model\GroupManagement;
use Magestore\Customercredit\Model\TransactionType;

class Transaction extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customercreditModel;

    /**
     * @param \Magento\Framework\Model\Context $context,
     * @param \Magento\Framework\Registry $registry,
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory,
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customercreditFactory,
     * @param \Magestore\Customercredit\Model\ResourceModel\Transaction $resource
     * @param \Magestore\Customercredit\Model\ResourceModel\Transaction\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercreditFactory,
        \Magestore\Customercredit\Model\ResourceModel\Transaction $resource,
        \Magestore\Customercredit\Model\ResourceModel\Transaction\Collection $resourceCollection,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->customerFactory = $customerFactory;
        $this->_customercreditModel = $customercreditFactory;
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magestore\Customercredit\Model\ResourceModel\Transaction');
        $this->setIdFieldName('transaction_id');
    }

    public function beforeSave()
    {
        $this->setTransactionTime(date("Y-m-d H:i:s"));
        if (!$this->getStatus()) {
            $this->setStatus('Completed');
        }
        return parent::beforeSave();
    }

    public function addTransactionHistory($customer_id, $transaction_type_id, $transaction_detail, $order_id, $amount_credit)
    {
        $spent_credit = 0;
        $received_credit = 0;

        if ($transaction_type_id == TransactionType::TYPE_CANCEL_ORDER
            || $transaction_type_id == TransactionType::TYPE_REFUND_ORDER_INTO_CREDIT
        ) {
            $spent_credit = ($amount_credit < 0) ? $amount_credit : -$amount_credit;
        }

        if ($transaction_type_id == TransactionType::TYPE_CHECK_OUT_BY_CREDIT) {
            $spent_credit = ($amount_credit > 0) ? $amount_credit : -$amount_credit;
        } elseif ($transaction_type_id == TransactionType::TYPE_REFUND_ORDER_INTO_CREDIT) {
            $received_credit = ($amount_credit > 0) ? $amount_credit : -$amount_credit;
        }

        if ($transaction_type_id == TransactionType::TYPE_BUY_CREDIT) {
            $received_credit = ($amount_credit > 0) ? $amount_credit : -$amount_credit;
        }

        // guest checkout
        if (!$customer_id) {
            $customer_group_id = GroupManagement::NOT_LOGGED_IN_ID;
            $customer_id = 0;
            $begin_balance = 0;
            $end_balance = 0;
        } else {
            $customer = $this->customerFactory->create()->load($customer_id);
            $customer_group_id = (float)$customer->getGroupId();
            $begin_balance = $this->_customercreditModel->create()->load($customer_id,'customer_id')->getCreditBalance();
            $end_balance = $begin_balance + $amount_credit;
        }
        if ($end_balance < 0) {
            $end_balance = 0;
        }
        try {
            $this->setTransactionId()
                ->setCustomerId($customer_id)
                ->setTypeTransactionId($transaction_type_id)
                ->setDetailTransaction($transaction_detail)
                ->setOrderIncrementId($order_id)
                ->setAmountCredit($amount_credit)
                ->setBeginBalance($begin_balance)
                ->setEndBalance($end_balance)
                ->setCutomerGroupIds($customer_group_id)
                ->setSpentCredit($spent_credit)
                ->setReceivedCredit($received_credit);
            $this->save();
        } catch (\Exception $ex) {
//            \Zend_Debug::dump($ex->getMessage());die();
        }
    }

//    public function getTransactionByOrderId($order_id)
//    {
//        $transactions = $this->getCollection()->addFieldToFilter('order_increment_id', $order_id);
//        return $transactions;
//    }
//
//    public function getTransactionCreditMemo($order_id, $type_id)
//    {
//        $transactions = $this->getCollection()
//            ->addFieldToFilter('order_increment_id', $order_id)
//            ->addFieldToFilter('type_transaction_id', $type_id)
//            ->addFieldToFilter('amount_credit', array('gt' => 0));
//        return $transactions->getSize();
//    }

}