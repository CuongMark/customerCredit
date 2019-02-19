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

use Angel\Raffle\Model\Ticket\Status;
use Angel\Raffle\Model\Data\Ticket;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ObserverInterface;
use Magestore\Customercredit\Model\Customercredit;
use Magestore\Customercredit\Model\Transaction;
use Magestore\Customercredit\Model\TransactionType;

class AngelRaffleCreateNewTicket implements ObserverInterface
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
     * TicketUpdateStatusAfter constructor.
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
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Ticket $ticket */
        $ticket = $observer->getTicket();
        /** @var Product $product */
        $product = $observer->getProduct();
        if ($ticket->getStatus() == Status::STATUS_PENDING){
            /** @var Customercredit $customerCredit */
            $customerCredit = $this->_customercredit->create();
            $credit = $customerCredit->getCreditByCustomerId($ticket->getCustomerId());
            if ((float)$credit->getCreditBalance() < $ticket->getPrice()){
                throw new \Exception(__('Don\'t have enough credit'));
            }
            /** @var Transaction $transaction */
            $transaction = $this->_transactionFactory->create();
            $transaction->addTransactionHistory(
                $ticket->getCustomerId(),
                TransactionType::TYPE_PURCHASE_TICKET,
                __('Purchase Ticket %1', $product->getName()),
                null,
                - $ticket->getPrice()
            );
            $customerCredit->changeCustomerCredit( - $ticket->getPrice(), $ticket->getCustomerId());
            $ticket->setTransactionId($transaction->getId())
                ->setStatus(Status::STATUS_PAID);
        }
        return $this;
    }
}
