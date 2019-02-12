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

class VerifySender extends \Magento\Framework\App\Action\Action
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
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customer
     * @param \Magestore\Customercredit\Helper\Account $accountHelper
     * @param \Magestore\Customercredit\Helper\Data $creditHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customercredit
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magestore\Customercredit\Helper\Account $accountHelper,
        \Magestore\Customercredit\Helper\Data $creditHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercredit
    )
    {
        $this->_customerSession = $customerSession;
        $this->_customer = $customer;
        $this->_accountHelper = $accountHelper;
        $this->_creditHelper = $creditHelper;
        $this->_storeManager = $storeManager;
        $this->_customercredit = $customercredit;
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
        $id = $this->getRequest()->getParam('id');
        $email = $this->getRequest()->getParam('customercredit_email_input');
        $value = $this->getRequest()->getParam('customercredit_value_input');
        $description = $this->getRequest()->getParam('customer-credit-share-message');
        $session->setEmail($email);
        $session->setValue($value);
        $session->setCreditCodeId($id);
        $session->setDescription($description);
        if (isset($id) && ($email) && isset($value)) {
            $session->setData("sentemail", 'yes');
            $ran_num = rand(1, 1000000);
            $keycode = md5(md5(md5($ran_num)));
            $session->setData("emailcode", $keycode);
            $this->_customercredit->create()->sendVerifyEmail($email, $value, null, $keycode);
        }
        $session->setVerify(true);
        $this->_redirect('*/*/share');
        $this->messageManager->addSuccess(
            __("A verification code has been sent to <a href=\"mailto: %1\"><b>your email</b></a>. Now, please check your email and verify your credit sending!",$sender_email)
        );
    }
}
 