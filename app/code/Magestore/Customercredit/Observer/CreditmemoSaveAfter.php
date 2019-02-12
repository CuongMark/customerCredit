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

class CreditmemoSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customer;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_order;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_product;
    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $_item;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magestore\Customercredit\Model\TransactionFactory
     */
    protected $_transaction;
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customercredit;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Customer\Model\CustomerFactory $customer
     * @param \Magento\Sales\Model\OrderFactory $order
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Magento\Sales\Model\Order\ItemFactory $item
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magestore\Customercredit\Model\TransactionFactory $transaction
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customercredit
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Sales\Model\OrderFactory $order,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Sales\Model\Order\ItemFactory $item,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magestore\Customercredit\Model\TransactionFactory $transaction,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercredit,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_request = $request;
        $this->customer = $customer;
        $this->_order = $order;
        $this->_product = $product;
        $this->_item = $item;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_transaction = $transaction;
        $this->_customercredit = $customercredit;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //declare variables
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $data = $this->_request->getPost('creditmemo');
        $order_id = $creditmemo->getOrderId();
        $order = $this->_order->create()->load($order_id);
        $baseCurrency = $this->_storeManager->getStore($order->getStoreId())->getBaseCurrency();

        $amount_credit = 0;
        $customer_id = $this->getCustomerId($creditmemo);
        if(!$customer_id){
            return $this;
        }
        $customer = $this->_customercredit->create()->getCollection()->addFieldToFilter('customer_id', $customer_id)->getFirstItem();
        $product_credit_value = 0;

//        $maxcredit = $creditmemo->getGrandTotal();
//        if ($rate = $baseCurrency->getRate($creditmemo->getOrderCurrencyCode())) {
//            $maxcredit = $maxcredit * $rate;
//        }

//        if (isset($data['refund_creditbalance_return'])) {
//            if (round((float)$data['refund_creditbalance_return'], 3) > round($maxcredit, 3)) {
//                throw new \Exception(__('Credit amount cannot exceed order amount.'));
//            }
//        }

        $transaction_detail = __("Refund order #") . $order->getIncrementId();
        $type_id = \Magestore\Customercredit\Model\TransactionType::TYPE_REFUND_ORDER_INTO_CREDIT;

        foreach ($creditmemo->getAllItems() as $item) {
            if (!$item->getParentItemId()) {
                $orderItem = $item->getOrderItem();
                $type = $orderItem->getProductType();
                if ($type == 'customercredit') {
                    $options = $orderItem->getProductOptions();
                    $buyRequest = $options['info_buyRequest'];
                    $product_credit_value += ((float)$buyRequest['amount']) * ((float)$item->getQty());
                }
            }
        }

        if ($rate = $baseCurrency->getRate($creditmemo->getOrderCurrencyCode())) {
            $product_credit_value = $product_credit_value / $rate;
        }

        $creditBalance = $customer->getCreditBalance();
        if ($product_credit_value > $creditBalance) {
            throw new \Exception(__('Credit balance is not enough to refund.'));
        }
        if ($product_credit_value > 0) {
            $type_id = \Magestore\Customercredit\Model\TransactionType::TYPE_REFUND_CREDIT_PRODUCT;
            $amount_credit -= $product_credit_value;
        }
        if (isset($data['refund_creditbalance_return'])) {
            if ($data['refund_creditbalance_return_enable'] && $data['refund_creditbalance_return'] > 0) {
                $transaction_detail = __("Refund order #") . $order->getIncrementId() . __(" into customer credit");
                $amount_credit = $data['refund_creditbalance_return'];
            }
        }

        if ($creditmemo->getCustomercreditDiscount()) {
            $amount_credit += $creditmemo->getCustomercreditDiscount();
        }

        if ($amount_credit) {
            if ($rate = $baseCurrency->getRate($creditmemo->getOrderCurrencyCode())) {
                $amount_credit = $amount_credit / $rate;
            }
            $this->_transaction->create()->addTransactionHistory($customer_id, $type_id, $transaction_detail, $order_id, $amount_credit);
            $this->_customercredit->create()->changeCustomerCredit($amount_credit, $customer_id);
        }

        return $this;
    }

    /**
     * get customer id to add/substract amount ( sender or receiver )
     * @param $creditmemo
     * @return mixed
     */
    public function getCustomerId($creditmemo)
    {
        $orderId = $creditmemo->getOrderId();
        $collection = $this->_transaction->create()->getCollection()
            ->addFieldToFilter('order_increment_id', $orderId)
            ->addFieldToFilter('type_transaction_id', \Magestore\Customercredit\Model\TransactionType::TYPE_RECEIVE_CREDIT_FROM_FRIENDS);
        if ($collection->getSize()) {
            $transaction = $collection->getFirstItem();
            return $transaction->getCustomerId();
        }
        return $creditmemo->getCustomerId();
    }
}
