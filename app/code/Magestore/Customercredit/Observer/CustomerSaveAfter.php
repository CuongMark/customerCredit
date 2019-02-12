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

class CustomerSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendAuthSession;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customercreditFactory;
    /**
     * @var \Magestore\Customercredit\Model\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     */
    protected $_responseFactory;

    /**
     * @param \Magento\AdminNotification\Model\FeedFactory $feedFactory
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercreditFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Customercredit\Model\TransactionFactory $transactionFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\ResponseFactory $responseFactory
    )
    {
        $this->_request = $request;
        $this->messageManager = $messageManager;
        $this->_customercreditFactory = $customercreditFactory;
        $this->_transactionFactory = $transactionFactory;
        $this->_objectManager = $objectManager;
        $this->_url = $url;
        $this->_responseFactory = $responseFactory;
    }

    /**
     * Predispath admin action controller
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if (!$customer->getId())
            return $this;

        $customer_id = $customer->getId();

        $credit_value = $this->_request->getParam('credit_value');
        if (strpos($credit_value, ',')!==false) {
            $credit_value = str_replace(',', '.', $credit_value);
        }
        $description = $this->_request->getParam('description');
        $group = $this->_request->getParam('account');
        $customer_group = $group['group_id'];
        $sign = substr($credit_value, 0, 1);

        if (!$credit_value)
            return $this->getReturn();

        $transaction = $this->_transactionFactory->create();
        $customercredit = $this->_customercreditFactory->create()->load($customer_id, 'customer_id');

        if ($sign == "-") {
            $end_credit = $customercredit->getCreditBalance() - substr($credit_value, 1, strlen($credit_value));
            if ($end_credit < 0) {
                $end_credit = 0;
                $credit_value = -$customercredit->getCreditBalance();
            }
        } else {
            $end_credit = $customercredit->getCreditBalance() + $credit_value;
        }

        $transactionCreditData = [
            'customer_id' => $customer_id,
            'type_transaction_id' => 1,
            'detail_transaction' => $description,
            'received_credit' => $credit_value,
            'amount_credit' => $credit_value,
            'end_balance' => $end_credit,
            'transaction_time' => date("Y-m-d H:i:s"),
            'customer_group_ids' => $customer_group
        ];
        $transaction->setData($transactionCreditData);

        try {

            if(!$customercredit->getCustomerId()){
                $customercredit->setCustomerId($customer_id);
            }

            $customercredit->setCreditBalance($end_credit)->save();
            $transaction->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $sendemail = $this->_request->getParam('send_mail');
        if ($sendemail == 1) {
            $email = $customer->getEmail();
            $name = $customer->getLastname();
            $balance = $customercredit->getCreditBalance();
            $message = $this->_request->getParam('description');
            $customercredit->sendNotifytoCustomer($email, $name, $credit_value, $balance, $message);
        }

        return $this->getReturn();
    }

    /* @@TODO fix param to return customer credit page */
    protected function getReturn(){
        $type = $this->_request->getParam('type');
        $redirectBack = $this->_request->getParam('back', false);
        if($type == 'customercredit' && $redirectBack == false) {
            $RedirectUrl = $this->_url->getUrl('customercreditadmin/creditproduct/');
            $this->_responseFactory->create()->setRedirect($RedirectUrl)->sendResponse();
//            die();
        }
        return $this;
    }
}
