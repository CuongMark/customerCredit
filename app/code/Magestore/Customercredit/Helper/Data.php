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

namespace Magestore\Customercredit\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Area;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Data extends AbstractHelper
{
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customercreditFactory;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var  \Magento\Framework\App\State
     */
    protected $_appState;
    /**
     * @var  \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;
    /**
     * @var  \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;
    /**
     * @var  \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $random;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customercreditFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\State $appState,
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote,
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory,
     * @param \Magento\Customer\Model\Session $customerSession,
     * @param \Magento\Framework\Math\Random $random,
     * @param \Magento\Checkout\Model\Session $checkoutSession,
     * @param \Magento\Sales\Model\OrderFactory $orderFactory,
     * @param \Magento\Framework\View\Asset\Repository $assetRepo,
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercreditFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $appState,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Math\Random $random,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    )
    {
        $this->_customercreditFactory = $customercreditFactory;
        $this->_pricingHelper = $pricingHelper;
        $this->_storeManager = $storeManager;
        $this->_appState = $appState;
        $this->_sessionQuote = $sessionQuote;
        $this->_customerFactory = $customerFactory;
        $this->_customerSession = $customerSession;
        $this->random = $random;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_assetRepo = $assetRepo;
        $this->_priceCurrency = $priceCurrency;
        parent::__construct($context);
    }

    function calc($a, $b)
    {
        return $a + $b;
    }

    public function topFiveCustomerMaxCredit()
    {
        $collection = $this->_customercreditFactory->create()->getCollection()
            ->addFieldToFilter('credit_balance', array('gt' => 0.00))
            ->setOrder('credit_balance', 'DESC');
        $collection->getSelect()->limit(5);
        return $collection->getData();
    }

    public function getCustomercreditLabelAccount()
    {
        $customercredit = $this->getCreditBalanceByUser();
        $moneyText = $this->_pricingHelper->currency($customercredit, true, false);
        return __('My Credit %1 ', $moneyText);
    }

    public function getCustomercreditLabel()
    {
        $icon = '<img src="'.$this->_assetRepo->getUrlWithParams('Magestore_Customercredit::images/point.png', []).'" style="vertical-align: middle" />';
        $customercredit = $this->getCreditBalanceByUser();
        $moneyText = $this->_pricingHelper->currency($customercredit, true, false);
        return __('%2 My Credit %1', $moneyText, $icon);
    }
    public function sendCredit()
    {
        $sendCredit = $this->scopeConfig->getValue('customercredit/general/enable_send_credit', 'store');
        if ($sendCredit == 0) {
            return 1;
        }
        return 0;
    }

    public function getStyleConfig($code, $store = null)
    {
        return $this->scopeConfig->getValue('customercredit/style_management/' . $code, 'store', $store);
    }

    public function getCustomer()
    {
        if ($this->_appState->getAreaCode() != Area::AREA_FRONTEND) {
            $customer_id = $this->_sessionQuote->getCustomerId();
            $customer = $this->_customerFactory->create()->load($customer_id);
            return $customer;
        } else {
            return $this->_customerSession->getCustomer();
        }
    }
    public function getCustomerName($customerId)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        return $customer->getFirstname() . ' ' . $customer->getLastname();
    }


    public function getReportConfig($code, $store = null)
    {
        return $this->scopeConfig->getValue('customercredit/report/' . $code, $store);
    }
    public function getGeneralConfig($code, $store = null)
    {
        return $this->scopeConfig->getValue('customercredit/general/' . $code, 'store', $store);
    }
    public function getEmailConfig($code, $store = null)
    {
        return $this->scopeConfig->getValue('customercredit/email/' . $code, 'store', $store);
    }
    public function getSpendConfig($code, $store = null)
    {
        return $this->scopeConfig->getValue('customercredit/spend/' . $code, 'store', $store);
    }

    /**
     * Get the full Ccredit product options
     *
     * @return array
     */
    public function getFullCreditProductOptions()
    {
        return array(
            'customer_name' => __('Sender Name'),
            'send_friend' => __('Send credit to friend'),
            'recipient_name' => __('Recipient name'),
            'recipient_email' => __('Recipient email'),
            'message' => __('Custom message'),
            'amount' => __('Amount')
        );
    }


    public function isExpression($string)
    {
        return preg_match('#\[([AN]{1,2})\.([0-9]+)\]#', $string);
    }

    public function calcCode($expression)
    {
        if ($this->isExpression($expression)) {
            return preg_replace_callback('#\[([AN]{1,2})\.([0-9]+)\]#', array($this, 'convertExpression'), $expression);
        } else {
            return $expression;
        }
    }

    public function convertExpression($param)
    {
        $alphabet = (strpos($param[1], 'A')) === false ? '' : 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphabet .= (strpos($param[1], 'N')) === false ? '' : '0123456789';
        return $this->random->getRandomString($param[2], $alphabet);
    }

    public function getNameCustomerByEmail($email)
    {
        $collecions = $this->_customerFactory->create()->getCollection()
            ->addFieldToFilter('email', $email);

        $name = $email;
        foreach ($collecions as $customer) {
            $lastname = $customer->getLastname();
            $firstName = $customer->getFirstname();
            $name = $firstName . " " . $lastname;
        }

        return $name;
    }

    public function getHiddenCode($code)
    {
        $prefix = 4;
        $prefixCode = substr($code, 0, $prefix);
        $suffixCode = substr($code, $prefix);
        if ($suffixCode) {
            $hiddenChar = 'X';
            if (!$hiddenChar)
                $hiddenChar = 'X';
            else
                $hiddenChar = substr($hiddenChar, 0, 1);
            $suffixCode = preg_replace('#([A-Z,0-9]{1})#', $hiddenChar, $suffixCode);
        }
        return $prefixCode . $suffixCode;
    }

    public function hasCustomerCreditItem()
    {
        $quote = $this->_checkoutSession->getQuote();
        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductType() == 'customercredit') {
                return true;
            }
        }
        return false;
    }

    public function hasCustomerCreditItemOnly()
    {
        $quote = $this->_checkoutSession->getQuote();
        $hasOnly = false;
        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductType() == 'customercredit') {
                $hasOnly = true;
            } else {
                $hasOnly = false;
                break;
            }
        }
        return $hasOnly;
    }

    public function isBuyCreditProduct($order_id)
    {
        $order = $this->_orderFactory->create();
        $order->load($order_id);
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() == 'customercredit') {
                return true;
            }
        }
        return false;
    }

    public function getConvertedToBaseCustomerCredit($credit_amount)
    {
        $rate = $this->_priceCurrency->convert(1);
        return $credit_amount / $rate;
    }

    public function getConvertedFromBaseCustomerCredit($credit_amount)
    {
        return $this->_priceCurrency->convert($credit_amount);
    }

    public function getCreditBalanceByUser()
    {
        $customer = $this->getCustomer();
        $customerId = $customer->getId();
        $baseCustomerCredit = $this->_customercreditFactory->create()->load($customerId, 'customer_id')->getCreditBalance();
        return $baseCustomerCredit;
    }

    public function getFormatAmount($amount)
    {
        return $this->_pricingHelper->currency($amount, true, false);
    }

    public function formatPrice($value)
    {
        return $this->_priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->_storeManager->getStore()
        );
    }

    function getCustomerCreditValueLabel()
    {
        $balance = $this->getCreditBalanceByUser();
        return $this->getFormatAmount($balance);
    }

}
