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

namespace Magestore\Customercredit\Block;

class Sharecredit extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customerCreditFactory;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_creditHelper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customerCreditFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magestore\Customercredit\Helper\Data $creditHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magestore\Customercredit\Model\CustomercreditFactory $customerCreditFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magestore\Customercredit\Helper\Data $creditHelper
    )
    {
        $this->_customerSession = $customerSession;
        $this->_customerCreditFactory = $customerCreditFactory;
        $this->_customerFactory = $customerFactory;
        $this->_creditHelper = $creditHelper;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function _customerSession()
    {
        return $this->_customerSession;
    }
    public function _creditHelper()
    {
        return $this->_creditHelper;
    }

    public function getFormActionUrl()
    {
        return $this->getUrl('customercredit/index/sharepost');
    }

    public function getVerifyCode()
    {
        $code = $this->getRequest()->getParam('keycode');
        if ($code) {
            return $code;
        }
        return '';
    }

    public function getBalance()
    {
        return round($this->_creditHelper->getCreditBalanceByUser(), 3);
    }

    public function getBalanceLabel()
    {
        return $this->_creditHelper->getCustomerCreditValueLabel();
    }

    public function getBackUrl()
    {
        return $this->getUrl('customercredit/index/index');
    }

    public function getVerifyEnable()
    {
        return $this->_creditHelper->getGeneralConfig('validate');
    }

    public function getCustomerEmail()
    {
        $customer_id = $this->_customerSession->getCustomerId();
        $customer_email = $this->_customerFactory->create()->load($customer_id)->getEmail();
        return $customer_email;
    }

    public function enableSendCredit()
    {
        return $this->_creditHelper->getGeneralConfig('enable_send_credit');
    }

    public function getValidateUrl()
    {
        return $this->getUrl('customercredit/index/validateCustomer');
    }

    public function getStore()
    {
        return $this->storeManager->getStore();
    }

}
