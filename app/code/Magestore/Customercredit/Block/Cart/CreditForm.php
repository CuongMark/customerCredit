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

namespace Magestore\Customercredit\Block\Cart;

class CreditForm extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customerCredit;

    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    public $_creditHelper;

    /**
     * @var \Magestore\Customercredit\Helper\Account
     */
    public $_creditaccountHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customerCredit
     * @param \Magestore\Customercredit\Helper\Data $creditHelper
     * @param \Magestore\Customercredit\Helper\Account $creditaccountHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Customercredit\Model\CustomercreditFactory $customerCredit,
        \Magestore\Customercredit\Helper\Data $creditHelper,
        \Magestore\Customercredit\Helper\Account $creditaccountHelper,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->_customerCredit = $customerCredit;
        $this->_creditHelper = $creditHelper;
        $this->_creditaccountHelper = $creditaccountHelper;
        $this->storeManager = $context->getStoreManager();
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
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

    public function isLoggedIn()
    {
        return $this->_creditaccountHelper->isLoggedIn();
    }

    public function isEnableCredit()
    {
        return $this->_creditHelper->getGeneralConfig('enable');
    }

    public function getCurrentCreditAmount()
    {
        return $this->_checkoutSession->getQuote()->getCustomercreditDiscount();
    }

    public function getCustomerCredit()
    {
        return round($this->_creditHelper->getCreditBalanceByUser(), 3);
    }

    public function getCustomerCreditLabel()
    {
        $balance = round($this->_creditHelper->getCreditBalanceByUser(), 3);
        $amount = $this->getCurrentCreditAmount();
        $balance = $this->_creditHelper->formatPrice($balance - $amount);
        return $balance;
    }

//    public function getAvaiableCustomerCreditLabel()
//    {
//        return $this->_customerCredit->create()->getAvaiableCustomerCreditLabel();
//    }

    public function getCustomerBaseBalance()
    {
//        $customerCreditModel = $this->_customerCredit->create();
        $balance = $balance = round($this->_creditHelper->getCreditBalanceByUser(), 3);
        if (isset($balance) && $balance > 0) {
            return $balance;
        }
        return 0;
    }
}