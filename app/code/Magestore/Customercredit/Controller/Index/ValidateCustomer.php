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

namespace Magestore\Customercredit\Controller\Index;

class ValidateCustomer extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customer;
    /**
     * @var \Magestore\Customercredit\Helper\Account
     */
    protected $_accountHelper;
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_creditHelper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customercredit;
    /**
     * @var \Magestore\Customercredit\Model\CreditcodeFactory
     */
    protected $_creditcode;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customer
     * @param \Magestore\Customercredit\Helper\Account $accountHelper
     * @param \Magestore\Customercredit\Helper\Data $creditHelper
     * @param \Magestore\Customercredit\Model\CreditcodeFactory $creditcode
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magestore\Customercredit\Helper\Account $accountHelper,
        \Magestore\Customercredit\Helper\Data $creditHelper,
        \Magestore\Customercredit\Model\CreditcodeFactory $creditcode,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_customerSession = $customerSession;
        $this->_customer = $customer;
        $this->_accountHelper = $accountHelper;
        $this->_creditHelper = $creditHelper;
        $this->_creditcode = $creditcode;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $session = $this->_customerSession;
        if (!$this->_accountHelper->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }
        if ($validate_config = $this->_creditHelper->getGeneralConfig('validate', null) == 0) {
            $this->_redirect("customercredit/index/share");
        }
        $sender_id = $this->_customerSession->getCustomerId();
        $customer = $this->_customer->create();
        $sender_email = $customer->load($sender_id)->getEmail();
        $recipient_email = $this->getRequest()->getPost('customercredit_email_input');
        $credit_amount = $this->getRequest()->getPost('customercredit_value_input');
        $description = $this->getRequest()->getPost('customer-credit-share-message');
        $is_send_email = $session->getData('is_credit_code');
        $customer_id = $customer->getCollection()->addFieldToFilter('email', $recipient_email)->getFirstItem()->getId();
        $session->setEmail($recipient_email);
        $session->setValue($credit_amount);

        if (isset($description) && $description != "") {
            $session->setDescription($description);
        }

        if ($recipient_email && $credit_amount && !isset($customer_id) && ($is_send_email == 'yes')) {
            $credit_code = $this->_creditcode->create()->addCreditCode($recipient_email, $credit_amount, \Magestore\Customercredit\Model\Source\Status::STATUS_AWAITING_VERIFICATION, $sender_id);
            $credit_code_id = $this->_creditcode->create()->getCollection()
                ->addFieldToFilter('credit_code', $credit_code)
                ->getFirstItem()->getId();
            if (isset($credit_code_id)) {
                $this->getRequest()->setParam('id', $credit_code_id);
                $session->setCreditCodeId($credit_code_id);
            }
        }
        $session->setData("is_credit_code", 'no');
        $session->setVerify(true);
        $this->_redirect('*/*/share');
        $this->messageManager->addSuccess(
            __("A verification code has been sent to <a href=\"mailto: %1\"><b>your email</b></a>. Now, please check your email and verify your credit sending!",$sender_email)
        );
    }
}
 