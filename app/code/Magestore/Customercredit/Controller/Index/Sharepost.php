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

class Sharepost extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magestore\Customercredit\Helper\Account
     */
    protected $_accountHelper;
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_creditHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customercreditFactory;
    /**
     * @var \Magestore\Customercredit\Model\TransactionFactory
     */
    protected $_transactionFactory;
    /**
     * @var \Magestore\Customercredit\Model\CreditcodeFactory
     */
    protected $_creditcodeFactory;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magestore\Customercredit\Helper\Account $accountHelper
     * @param \Magestore\Customercredit\Helper\Data $creditHelper
     * @param \Magento\Checkout\Model\Session $customerSession
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customercreditFactory
     * @param \Magestore\Customercredit\Model\TransactionFactory $transactionFactory,
     * @param \Magestore\Customercredit\Model\CreditcodeFactory $creditcodeFactory,
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magestore\Customercredit\Helper\Account $accountHelper,
        \Magestore\Customercredit\Helper\Data $creditHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercreditFactory,
        \Magestore\Customercredit\Model\TransactionFactory $transactionFactory,
        \Magestore\Customercredit\Model\CreditcodeFactory $creditcodeFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        $this->_accountHelper = $accountHelper;
        $this->_creditHelper = $creditHelper;
        $this->_customerSession = $customerSession;
        $this->_customercreditFactory = $customercreditFactory;
        $this->_transactionFactory = $transactionFactory;
        $this->_creditcodeFactory = $creditcodeFactory;
        $this->_customerFactory = $customerFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        if (!$this->_accountHelper->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }
        $customer = $this->_creditHelper->getCustomer();
        /* @var $customercreditFactory \Magestore\Customercredit\Model\Customercredit */
        /* @var $transactionFactory \Magestore\Customercredit\Model\Transaction */
        $customercreditFactory = $this->_customercreditFactory->create();
        $transactionFactory = $this->_transactionFactory->create();

        $customer_credit = round($this->_creditHelper->getCreditBalanceByUser(), 3);
        if ($customer_credit <= 0) {
            $this->messageManager->addError(__('Your credit amount not enough to share!'));
            return $this->_redirect("customercredit/index/share");
        }
        $customer_id = $customer->getId();
        $customer_name = $customer->getFirstname() . " " . $customer->getLastname();
        $customer_email = $customer->getEmail();
        $credit_code_id = $this->getRequest()->getParam('credit_code_id_hide');
        if ($this->_creditHelper->getGeneralConfig('validate')) {
            if ($this->_customerSession->getData("sentemail") != 'yes') {
                return $this->_redirect("customercredit/index/share");
            }
            $keycode = $this->getRequest()->getParam('customercreditcode');
            $email = $this->getRequest()->getParam('email_hide');
            $amount = $this->getRequest()->getParam('amount_hide');
            $amount = $this->_creditHelper->getConvertedToBaseCustomerCredit($amount);
            $message = $this->getRequest()->getParam('message_hide');

            if ($email == $customer_email) {
                $this->messageManager->addError(__('Invalid email. Please check again!'));
                return $this->_redirect("customercredit/index/share");
            }
            if ($amount < 0 || $amount > $customer_credit) {
                $this->messageManager->addError(__('Invalid amount. Please check again!'));
                return $this->_redirect("customercredit/index/share");
            }

            $friend_account_id = $this->_customerFactory->create()->getCollection()
                ->addFieldToFilter('email', $email)
                ->getFirstItem()
                ->getId();
            if (trim($keycode) == trim($this->_customerSession->getData("emailcode"))) {

                $transactionFactory->addTransactionHistory($customer_id, \Magestore\Customercredit\Model\TransactionType::TYPE_SHARE_CREDIT_TO_FRIENDS, $customer_email . __(" sent ") . $amount . __(" credit to ") . $email, "", -$amount);
                $customercreditFactory->changeCustomerCredit(-$amount);
                if (isset($friend_account_id)) {
                    $transactionFactory->addTransactionHistory($friend_account_id, \Magestore\Customercredit\Model\TransactionType::TYPE_RECEIVE_CREDIT_FROM_FRIENDS, $email . __(" received ") . $amount . __(" credit from ") . $customer_name, "", $amount);
                    $customercreditFactory->addCreditToFriend($amount, $friend_account_id);
                } else {
                    if (isset($credit_code_id)) {
                        $this->_creditcodeFactory->create()->changeCodeStatus($credit_code_id, \Magestore\Customercredit\Model\Source\Status::STATUS_UNUSED);
                        $customercreditFactory->sendCreditToFriendByEmailAfterVerify($credit_code_id, $amount, $email, $message, $customer_id);
                    } else {
                        $customercreditFactory->sendCreditToFriendByEmail($amount, $email, $message, $customer_id);
                    }
                }
                $customercreditFactory->sendSuccessEmail($customer_email, $customer_name, $email, false);
                $this->_customerSession->setData("sentemail", 'no');
                $this->messageManager->addSuccess(__('Credit has been successfully sent to ') . $email);
                $session = $this->_customerSession;
                $session->setVerify(false);
                $session->setEmail(false);
                $session->setValue(false);
                $session->setCreditCodeId(false);
                $session->setDescription(false);
                return $this->_redirect("customercredit/index/share");
            } else {
                $this->messageManager->addError(__('Invalid verify code. Please check again!'));
                return $this->_redirect("customercredit/index/share");
            }
        } else {
            $email = $this->getRequest()->getParam('customercredit_email_input');
            $amount = $this->getRequest()->getParam('customercredit_value_input');
            $amount = $this->_creditHelper->getConvertedToBaseCustomerCredit($amount);
            $message = $this->getRequest()->getParam('customer-credit-share-message');
            $friend_account_id = $this->_customerFactory->create()->getCollection()
                ->addFieldToFilter('email', $email)
                ->getFirstItem()
                ->getId();
            if ($email == $customer_email) {
                $this->messageManager->addError(__('Invalid email. Please check again!'));
                return $this->_redirect("customercredit/index/share");
            }
            if ($amount < 0 || $amount > $customer_credit) {
                $this->messageManager->addError(__('Invalid amount. Please check again!'));
                return $this->_redirect("customercredit/index/share");
            }
            $transactionFactory->addTransactionHistory($customer_id, \Magestore\Customercredit\Model\TransactionType::TYPE_SHARE_CREDIT_TO_FRIENDS, $customer_email . __(" sent ") . $amount . __(" credit to ") . $email, "", -$amount);
            $customercreditFactory->changeCustomerCredit(-$amount);
            if (isset($friend_account_id)) {
                $transactionFactory->addTransactionHistory($friend_account_id, \Magestore\Customercredit\Model\TransactionType::TYPE_RECEIVE_CREDIT_FROM_FRIENDS, $email . __(" received ") . $amount . __(" credit from ") . $customer_name, "", $amount);
                $customercreditFactory->addCreditToFriend($amount, $friend_account_id);
            } else {
                $customercreditFactory->sendCreditToFriendByEmail($amount, $email, $message, $customer_id);
            }
            $customercreditFactory->sendSuccessEmail($customer_email, $customer_name, $email, false);
            $this->_customerSession->setData("sentemail", 'no');
            $this->messageManager->addSuccess(__('Credit has been successfully sent to ') . $email);
            $this->_redirect("customercredit/index/share");
        }
    }
}
