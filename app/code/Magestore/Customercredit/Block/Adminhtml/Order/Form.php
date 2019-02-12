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

namespace Magestore\Customercredit\Block\Adminhtml\Order;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_creditHelper;
    /**
     * @var \Magestore\Customercredit\Helper\Account
     */
    protected $_accountHelper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_creditModel;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magestore\Customercredit\Helper\Data $creditHelper
     * @param \Magestore\Customercredit\Helper\Account $creditaccountHelper
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $creditModel
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magestore\Customercredit\Helper\Data $creditHelper,
        \Magestore\Customercredit\Helper\Account $creditaccountHelper,
        \Magestore\Customercredit\Model\CustomercreditFactory $creditModel,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        $this->_currencyFactory = $currencyFactory;
        $this->_localeCurrency = $localeCurrency;
        $this->_creditHelper = $creditHelper;
        $this->_accountHelper = $creditaccountHelper;
        $this->_storeManager = $context->getStoreManager();
        $this->_creditModel = $creditModel;
        $this->_checkoutSession = $checkoutSession;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
    }

    public function getCreditHelper()
    {
        return $this->_creditHelper;
    }

    public function getAccountHelper()
    {
        return $this->_accountHelper;
    }

    public function getDefaultCustomerCredit()
    {
        $customer_id = $this->_sessionQuote->getCustomerId();
        $credit = $this->_creditModel->create()->load($customer_id, 'customer_id')->getCreditBalance();
        return $credit;
    }

    public function getAppliedCreditAmount(){
        if ($this->_checkoutSession->getCustomerCreditAmountEntered()) {
            $creditAmount = $this->_checkoutSession->getCreditdiscountAmount();
        } else {
            $creditAmount = $this->_checkoutSession->getCustomerCreditAmount();
        }
        return $creditAmount;
    }

    public function getCustomerCredit()
    {
        $credit = $this->getDefaultCustomerCredit();
        if ($amount = $this->getAppliedCreditAmount())
            $credit -= $amount;
        return $credit;
    }

    public function isAssignCredit()
    {
        $data = explode(",", $this->_creditHelper->getGeneralConfig('assign_credit'));
        $customer_id = $this->_sessionQuote->getCustomerId();
        $customer = $this->_objectManager->create(\Magento\Customer\Model\Customer)->load($customer_id);
        foreach ($data as $group) {
            if ($customer->getGroupId() == $group) {
                return true;
            }
        }
        return false;
    }

    public function hasCustomerCreditItem()
    {
        $quote = $this->_sessionQuote->getQuote();
        foreach ($quote->getAllItems() as $item) {
            if ($item->getProduct()->getTypeId() == 'customercredit') {
                return true;
            }
        }
        return false;
    }

    public function hasCustomerCreditItemOnly()
    {
        $quote = $this->_sessionQuote->getQuote();
        foreach ($quote->getAllItems() as $item) {
            if ($item->getProduct()->getTypeId() != 'customercredit') {
                return false;
            }
        }
        return true;
    }
}
