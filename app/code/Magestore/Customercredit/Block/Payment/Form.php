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

namespace Magestore\Customercredit\Block\Payment;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Form extends \Magento\Payment\Block\Form
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_helperData;
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;
    /**
     * @var  \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * @var \Magestore\Customercredit\Helper\Account
     */
    protected $_helperAccount;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customerCredit;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Customercredit\Helper\Data $helperData
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magestore\Customercredit\Helper\Account $helperAccount
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customerCredit
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Customercredit\Helper\Data $helperData,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Checkout\Model\Session $checkoutSession,
        PriceCurrencyInterface $priceCurrency,
        \Magestore\Customercredit\Helper\Account $helperAccount,
        \Magestore\Customercredit\Model\CustomercreditFactory $customerCredit,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
        $this->_helperData = $helperData;
        $this->_currency = $currency;
        $this->_checkoutSession = $checkoutSession;
        $this->priceCurrency = $priceCurrency;
        $this->_helperAccount = $helperAccount;
        $this->urlInterface = $context->getUrlBuilder();
        $this->_customerCredit = $customerCredit;
    }

    public function getCustomercreditData()
    {
        $quote = $this->_checkoutSession->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
        $result = array();
        $result['isEnable'] = $this->_helperData->getGeneralConfig('enable');
        $result['credit_amount'] = $quote->getCustomercreditDiscount();
        $result['has_credit_item'] = $this->_helperData->hasCustomerCreditItem();
        $result['in_group_credit'] = $this->_helperAccount->customerGroupCheck();
        $result['is_logged_in'] = $this->_helperAccount->isLoggedIn();
        $result['login_link'] = $this->getUrl('customer/account/login');
        $result['getFormatedBalance'] = $this->getFormatedBalance();
        $result['credit_balance'] = $this->_helperData->getCustomerCreditValueLabel();
        $result['credit_item_only'] = $this->_helperData->hasCustomerCreditItemOnly();

        return $result;
    }

    public function getFormatedBalance()
    {
        $balance = $this->_helperData->getCreditBalanceByUser();
        $balance = $this->priceCurrency->convert($balance);
        $quote = $this->_checkoutSession->getQuote();
        if ($quote->getCreditdiscountAmount() > 0) {
            $balance -= $this->priceCurrency->round($quote->getCreditdiscountAmount(), 2);
        }
        return $this->priceCurrency->format($balance, false);
    }

    public function getStoreCredit()
    {
        return $this->priceCurrency->round($this->_checkoutSession->getQuote()->getCreditdiscountAmount(), 2);
    }
}
