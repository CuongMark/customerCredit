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
use Magestore\Customercredit\Model\TransactionType;

class InvoiceSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;
    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $_orderItemFactory;
    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \Magestore\Customercredit\Model\TransactionFactory
     */
    protected $_transactionFactory;
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customercreditFactory;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactoryr
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magestore\Customercredit\Model\TransactionFactory $transactionFactory
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customercreditFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magestore\Customercredit\Model\TransactionFactory $transactionFactory,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercreditFactory,
        array $data = []
    )
    {
        $this->_orderFactory = $orderFactory;
        $this->_productRepository = $productRepository;
        $this->_orderItemFactory = $orderItemFactory;
        $this->currencyFactory = $currencyFactory;
        $this->customerFactory = $customerFactory;
        $this->_transactionFactory = $transactionFactory;
        $this->_customercreditFactory = $customercreditFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $orderId = $invoice->getOrderId();

        /* @var $order \Magento\Sales\Model\Order */
        $order = $this->_orderFactory->create()->load($orderId);
        $customer_id = $order->getCustomerId();

        $customer_name = $order->getCustomerName();
        $customer_email = $order->getCustomerEmail();
        $product_credit_value = 0;

        foreach ($invoice->getAllItems() as $item) {
            if (!$item->getParentItemId()) {
                /* @var $orderItem \Magento\Sales\Model\Order\Item */
                $orderItem = $item->getOrderItem();
                $type = $orderItem->getProductType();
                if ($type == 'customercredit') {
                    $options = $orderItem->getProductOptions();
                    $buyRequest = $options['info_buyRequest'];

                    /* @var $baseCurrency \Magento\Directory\Model\Currency */
                    $baseCurrency = $this->currencyFactory->create()->load($order->getBaseCurrencyCode());
                    if ($rate = $baseCurrency->getRate($order->getOrderCurrencyCode()) && isset($buyRequest['amount'])) {
                        $baseAmount = $buyRequest['amount'] / $rate;
                    } else {
                        $baseAmount = $buyRequest['amount'];
                    }

                    if (isset($buyRequest['send_friend']) &&
                        isset($buyRequest['recipient_email']) &&
                        $buyRequest['send_friend'] &&
                        $customer_email != $buyRequest['recipient_email']
                    ) {
                        $sender_name = $buyRequest['customer_name'];
                        if(trim($sender_name) == ""){
                            $sender_name = $customer_name;
                        }
                        $email = $buyRequest['recipient_email'];
                        $amount = $baseAmount * $item->getQty();
                        $message = $buyRequest['message'];
                        /* @var $customerCredit \Magestore\Customercredit\Model\Customercredit */
                        $customerCredit = $this->_customercreditFactory->create();
                        /* @var $transaction \Magestore\Customercredit\Model\Transaction */
                        $transaction = $this->_transactionFactory->create();

                        $friend_account_id = $this->customerFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('email', $email)
                            ->getFirstItem()
                            ->getId();

                        $transaction->addTransactionHistory(
                            $customer_id,
                            TransactionType::TYPE_SHARE_CREDIT_TO_FRIENDS,
                            $customer_email . " sent " . $amount . " credit to " . $email,
                            $order->getIncrementId(),
                            0
                        );

                        if (isset($friend_account_id)) {
                            $transaction->addTransactionHistory(
                                $friend_account_id,
                                TransactionType::TYPE_RECEIVE_CREDIT_FROM_FRIENDS,
                                $email . " received " . $amount . " credit from " . $customer_name,
                                $order->getIncrementId(),
                                $amount
                            );
                            $customerCredit->addCreditToFriend($amount, $friend_account_id);
                        } else {
                            $customerCredit->sendCreditToFriendByEmail($amount, $email, $message, $customer_id, $sender_name);
                        }
                        $customerCredit->sendSuccessEmail($customer_email, $sender_name, $email, true);
                    } else {
                        $product_credit_value += ((float)$baseAmount) * ((float)$item->getQty());
                    }
                }
            }
        }

        if ($product_credit_value > 0) {
            $this->_transactionFactory->create()->addTransactionHistory(
                $order->getCustomerId(),
                TransactionType::TYPE_BUY_CREDIT,
                "buy credit " . $product_credit_value . " from store ",
                $order->getId(),
                $product_credit_value
            );
            if($customer_id){
                $this->_customercreditFactory->create()->addCreditToFriend($product_credit_value, $customer_id);
            }else{
                $this->_customercreditFactory->create()->sendCreditToFriendByEmail($product_credit_value, $customer_email, $message = null, $customer_id = null, $customer_email = null);
            }
        }
    }

}
