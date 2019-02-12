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

namespace Magestore\Customercredit\Controller\Checkout;

class AmountPost extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customercredit;
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_customercreditHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customercredit
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercredit,
        \Magestore\Customercredit\Helper\Data $customercreditHelper
    )
    {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->_customercredit = $customercredit;
        $this->_customercreditHelper = $customercreditHelper;
    }

    public function execute()
    {
        $request = $this->getRequest();
        if ($request->getParam('customer_credit') >= 0 && is_numeric($request->getParam('customer_credit'))) {
            $creditAmount = $request->getParam('customer_credit');
            $baseCreditAmount = $this->_customercreditHelper->getConvertedToBaseCustomerCredit($creditAmount);

            $customer = $this->_customercreditHelper->getCustomer();
            $customerId = $customer->getId();
            $customer_credit = $this->_customercredit->create()->load($customerId, 'customer_id');
            $creditBalance = $customer_credit->getCreditBalance();

            $creditAmount = min($baseCreditAmount, $creditBalance);

            /** integration with gift card and reward points */
            /*
            $session = $this->_checkoutSession;
            $quote = $session->getQuote();
            if($quote->getBaseGiftVoucherDiscount()){
                $creditAmount -= $quote->getBaseGiftVoucherDiscount();
            }
            if($quote->getRewardpointsBaseDiscount()){
                $creditAmount -= $quote->getRewardpointsBaseDiscount();
            }
            */
            $this->_checkoutSession->setCustomerCreditAmount($creditAmount);
            return $this->_goBack();
        }

        if (is_numeric($request->getParam('credit_amount')) && $request->getParam('credit_amount') >= 0) {
            $session = $this->_checkoutSession;
            $quote = $session->getQuote();
            $result = array();
            $creditAmount = $request->getParam('credit_amount');
            $baseCreditAmount = $this->_customercreditHelper->getConvertedToBaseCustomerCredit($creditAmount);

            $customer = $this->_customercreditHelper->getCustomer();
            $customerId = $customer->getId();
            $customer_credit = $this->_customercredit->create()->load($customerId, 'customer_id');
            $creditBalance = $customer_credit->getCreditBalance();

            $creditAmount = min($baseCreditAmount, $creditBalance);
            $session->setCustomerCreditAmount($creditAmount);
            $session->setCreditdiscountAmount($creditAmount);

            $result = $this->_objectManager->create('Magestore\Customercredit\Block\Payment\Form')->getCustomercreditData();
            $quote->save();
            $result['credit_discount'] = $quote->getCreditdiscountAmount();
            return $this->getResponse()->setBody(\Zend_Json::encode($result));
        }

        if (!$request->getParam('customer_credit') && !$request->getParam('credit_amount')) {
            $this->_checkoutSession->setCustomerCreditAmount(0);
            return $this->_goBack();
        }

        return $this->_goBack();
    }
}