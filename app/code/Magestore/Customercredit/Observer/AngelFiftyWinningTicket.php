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

use Angel\Fifty\Model\Ticket;
use Angel\Fifty\Model\Ticket\Status;
use Magento\Framework\Event\ObserverInterface;
use Magestore\Customercredit\Model\Customercredit;
use Magestore\Customercredit\Model\Transaction;
use Magestore\Customercredit\Model\TransactionType;

class AngelFiftyWinningTicket implements ObserverInterface
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
        $ticketData = $ticket->getData();
        if ($ticketData['status'] == Status::STATUS_WINNING){
            /** @var Customercredit $customerCredit */
            $customerCredit = $this->_customercredit->create();
            /** @var Transaction $transaction */
            $transaction = $this->_transactionFactory->create();
            $transaction->addTransactionHistory(
                $ticketData['customer_id'],
                TransactionType::TYPE_WIN_RAFFLE,
                __('Winning 50/50 raffle %1', $ticketData['product_name']),
                null,
                $ticketData['winning_prize']
            );
            $customerCredit->changeCustomerCredit($ticketData['winning_prize'], $ticketData['customer_id']);
            $ticketData['status'] = Status::STATUS_PAID;
            $ticket->setData($ticketData);
        }
        return $this;
    }
}
