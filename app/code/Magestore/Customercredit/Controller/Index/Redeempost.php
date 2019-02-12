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

class Redeempost extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magestore\Customercredit\Model\CreditcodeFactory
     */
    protected $_creditcodeFactory;
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customercreditFactory;
    /**
     * @var \Magestore\Customercredit\Model\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magestore\Customercredit\Model\CreditcodeFactory $creditcodeFactory,
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customercreditFactory,
     * @param \Magestore\Customercredit\Model\TransactionFactory $transactionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magestore\Customercredit\Model\CreditcodeFactory $creditcodeFactory,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercreditFactory,
        \Magestore\Customercredit\Model\TransactionFactory $transactionFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->_creditcodeFactory = $creditcodeFactory;
        $this->_customercreditFactory = $customercreditFactory;
        $this->_transactionFactory = $transactionFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        $customer_id = $this->_customerSession->getCustomerId();
        $credit_code = $this->getRequest()->getParam('redeem_credit_code');
        $credit = $this->_creditcodeFactory->create()->getCollection()->addFieldToFilter('credit_code', $credit_code);
        if ($credit->getSize() == 0) {
            $this->messageManager->addError(__('Code is invalid. Please check again!'));
            $this->_redirect('customercredit/index/redeem');
        } elseif ($credit->getFirstItem()->getStatus() != 1) {
            $this->messageManager->addError('Code was canceled.');
            $this->_redirect('customercredit/index/redeem');
        } else {
            $this->_creditcodeFactory->create()->changeCodeStatus($credit->getFirstItem()->getId(), \Magestore\Customercredit\Model\Source\Status::STATUS_USED);
            $credit_amount = $credit->getFirstItem()->getAmountCredit();
            $this->_transactionFactory->create()->addTransactionHistory($customer_id, \Magestore\Customercredit\Model\TransactionType::TYPE_REDEEM_CREDIT, __("redeem credit by code '") . $credit_code . "'", "", $credit_amount);
            $this->_customercreditFactory->create()->changeCustomerCredit($credit_amount);
            $this->messageManager->addSuccess(__('Redeem success!'));
            $this->_redirect('customercredit/index/index');
        }
    }
}
