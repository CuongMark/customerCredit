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

class ConvertQuoteToOrder implements ObserverInterface
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
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magestore\Customercredit\Model\TransactionFactory $transaction
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customercredit
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magestore\Customercredit\Model\TransactionFactory $transaction,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercredit
    )
    {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_transaction = $transaction;
        $this->_customercredit = $customercredit;
    }

    /**
     * Predispath admin action controller
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $session = $this->_checkoutSession;
        if ($quote->getCustomercreditDiscount()) {
            $order->setCustomercreditDiscount($quote->getCustomercreditDiscount());
            $order->setBaseCustomercreditDiscount($quote->getBaseCustomercreditDiscount());
            $order->setBaseCustomercreditDiscountForShipping($quote->getBaseCustomercreditDiscountForShipping());
            $order->setCustomercreditDiscountForShipping($quote->getCustomercreditDiscountForShipping());
            $order->setMagestoreBaseDiscount($quote->getMagestoreBaseDiscount());
            $order->setMagestoreDiscount($quote->getMagestoreDiscount());
            $order->setMagestoreBaseDiscountForShipping($quote->getMagestoreBaseDiscountForShipping());
            $order->setMagestoreDiscountForShipping($quote->getMagestoreDiscountForShipping());
        }

        $customer_id = $this->_customerSession->getCustomerId();
        if (!$customer_id) {
            $customer_id = $order->getCustomerId();
        }
        $amount = $quote->getBaseCustomercreditDiscount();
        if ($amount && !$session->getHasCustomerCreditItem()) {
            $this->_transaction->create()->addTransactionHistory(
                $customer_id,
                \Magestore\Customercredit\Model\TransactionType::TYPE_CHECK_OUT_BY_CREDIT,
                __('check out by credit for order #') . $order->getIncrementId(),
                $order->getId(),
                -$amount
            );
            $this->_customercredit->create()->changeCustomerCredit(-$amount, $customer_id);
        }
        if ($session->getUseCustomerCredit()) {
            $session->setCustomerCreditAmount(null)
                ->setCreditdiscountAmount(null)
                ->setBaseCreditdiscountAmount(null)
                ->setUseCustomerCredit(false);
        } else {
            $session->setCustomerCreditAmount(null)
                ->setCreditdiscountAmount(null)
                ->setBaseCreditdiscountAmount(null);
        }
    }
}
