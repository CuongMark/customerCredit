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

use Angel\RaffleClient\Model\Ticket;
use Angel\RaffleClient\Model\TicketRepository;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magestore\Customercredit\Model\TransactionType;

class TicketUpdateStatusAfter implements ObserverInterface
{
    /**
     * @var TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var TicketRepository
     */
    protected $_ticketRespository;

    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customercredit;

    /**
     * TicketUpdateStatusAfter constructor.
     * @param \Magestore\Customercredit\Model\TransactionFactory $transactionFactory
     * @param TicketRepository $ticketRepository
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customercredit
     */
    public function __construct(
        \Magestore\Customercredit\Model\TransactionFactory $transactionFactory,
        TicketRepository $ticketRepository,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercredit
    ){
        $this->_transactionFactory = $transactionFactory;
        $this->_ticketRespository = $ticketRepository;
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
        if ($ticket->getStatus() == Ticket::WON){
            /** @var Transaction $transaction */
            $transaction = $this->_transactionFactory->create();
            $transaction->addTransactionHistory(
                $ticket->getCustomerId(),
                TransactionType::TYPE_WIN_RAFFLE,
                __('Winning Ticket #%1', $ticket->getId()),
                null,
                $ticket->getWinningPrice()
            );
            $ticket->setStatus(Ticket::PAID)->save();
            $this->_customercredit->create()->changeCustomerCredit($ticket->getWinningPrice(), $ticket->getCustomerId());
        }
        return $this;
    }
}
