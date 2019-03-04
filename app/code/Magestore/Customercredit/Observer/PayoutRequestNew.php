<?php
/**
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

use Angel\Payout\Model\Data\Payout;
use Angel\Payout\Model\Payout\Status;
use Magento\Framework\Event\ObserverInterface;
use Magestore\Customercredit\Model\Customercredit;
use Magestore\Customercredit\Model\Transaction;
use Magestore\Customercredit\Model\TransactionType;

class PayoutRequestNew implements ObserverInterface
{
    /**
     * @var TransactionFactory
     */
    protected $_transactionFactory;


    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customercredit;

    /**
     * PayoutUpdateStatusAfter constructor.
     * @param \Magestore\Customercredit\Model\TransactionFactory $transactionFactory
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customercredit
     */
    public function __construct(
        \Magestore\Customercredit\Model\TransactionFactory $transactionFactory,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercredit
    ){
        $this->_transactionFactory = $transactionFactory;
        $this->_customercredit = $customercredit;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Payout $payout */
        $payout = $observer->getPayout();
        if ($payout->getStatus() == Status::STATUS_PENDING){
            /** @var Customercredit $customerCredit */
            $customerCredit = $this->_customercredit->create();
            $credit = $customerCredit->getCreditByCustomerId($payout->getCustomerId());
            if ((float)$credit->getCreditBalance() < $payout->getAmount()){
                throw new \Exception(__('Don\'t have enough credit'));
            }
            /** @var Transaction $transaction */
            $transaction = $this->_transactionFactory->create();
            $transaction->addTransactionHistory(
                $payout->getCustomerId(),
                TransactionType::TYPE_PAYOUT_REQUEST,
                __('Create Payout request %1'), $payout->getPayoutId(),
                null,
                - $payout->getAmount()
            );
            $customerCredit->changeCustomerCredit( - $payout->getAmount(), $payout->getCustomerId());
            $payout->setCreditTransactionId($transaction->getId());
        }
    }
}
