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

class Cancel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magestore\Customercredit\Helper\Account
     */
    protected $_accountHelper;
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customercredit;
    /**
     * @var \Magento\Customer\Model\Session $customersesion
     */
    protected $_customersession;
    /**
     * @var \Magestore\Customercredit\Model\CreditcodeFactory
     */
    protected $_creditcode;
    /**
     * @var \Magestore\Customercredit\Model\TransactionFactory
     */
    protected $_transaction;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magestore\Customercredit\Helper\Account $accountHelper
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customercredit
     * @param \Magento\Customer\Model\Session $customersesion
     * @param \Magestore\Customercredit\Model\CreditcodeFactory $creditcode
     * @param \Magestore\Customercredit\Model\TransactionFactory $transaction
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magestore\Customercredit\Helper\Account $accountHelper,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercredit,
        \Magento\Customer\Model\Session $customersesion,
        \Magestore\Customercredit\Model\CreditcodeFactory $creditcode,
        \Magestore\Customercredit\Model\TransactionFactory $transaction
    )
    {
        $this->_accountHelper = $accountHelper;
        $this->_customercredit = $customercredit;
        $this->_customersession = $customersesion;
        $this->_creditcode = $creditcode;
        $this->_transaction = $transaction;
        parent::__construct($context);
    }


    public function execute()
    {
    	if (!$this->_accountHelper->isLoggedIn())
            return $this->_redirect('customer/account/login');
        $credit_code_id = $this->getRequest()->getParam('id');
        $customer_id = $this->_customersession->getCustomerId();
        $credit_code = $this->_creditcode->create()->load($credit_code_id);
        $add_balance = $credit_code->getAmountCredit();
        $credit_code_status = $credit_code->getStatus();
        if($credit_code_status == 2 || $credit_code_status == 3 ){
            $warning = __('Credit code %s has been used.',$credit_code->getCreditCode());
            $this->messageManager->addError($warning);
            return $this->_redirect('*/index/share');             
        }
        $this->_transaction->create()->addTransactionHistory($customer_id, \Magestore\Customercredit\Model\TransactionType::TYPE_CANCEL_SHARE_CREDIT, __("cancel share credit "), "", $add_balance);
        $this->_customercredit->create()->changeCustomerCredit($add_balance);
        $this->_creditcode->create()->changeCodeStatus($credit_code_id, \Magestore\Customercredit\Model\Source\Status::STATUS_CANCELLED);
        $this->messageManager->addSuccess(__('Credit code has been canceled'));
        return $this->_redirect('*/index/share');
    }
}
