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

namespace Magestore\Customercredit\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderCancelAfter implements ObserverInterface
{
    /**
     * @var \Magestore\Customercredit\Model\TransactionFactory
     */
    protected $_transaction;
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customercredit;

    /**
     * @param \Magestore\Customercredit\Model\TransactionFactory $transaction
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customercredit
     */
    public function __construct(
        \Magestore\Customercredit\Model\TransactionFactory $transaction,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercredit
    )
    {
        $this->_transaction = $transaction;
        $this->_customercredit = $customercredit;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer['order'];
        $customer_id = $order->getCustomerId();
        $order_id = $order->getId();
        if ((float)(string)$order->getBaseCustomercreditDiscount() > 0) {
            $amount_credit = (float)(string)$order->getBaseCustomercreditDiscount();
            $type_id = \Magestore\Customercredit\Model\TransactionType::TYPE_CANCEL_ORDER;
            $transaction_detail = "Cancel order #" . $order->getIncrementId();
            $this->_transaction->create()->addTransactionHistory($customer_id, $type_id, $transaction_detail, $order_id, $amount_credit);
            $this->_customercredit->create()->addCreditToFriend($amount_credit, $customer_id);
            return $this;
        }
    }
}
