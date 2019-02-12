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

class History extends \Magento\Framework\View\Element\Template
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
     * @var \Magestore\Customercredit\Model\TransactionFactory
     */
    protected $_transactionFactory;
    /**
     * @var \Magestore\Customercredit\Model\TransactionTypeFactory
     */
    protected $_transactiontypeFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $datetime;
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_helperData;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customerCreditFactory
     * @param \Magestore\Customercredit\Model\TransactionFactory $transactionFactory
     * @param \Magestore\Customercredit\Model\TransactionTypeFactory $transactiontype
     * @param \Magestore\Customercredit\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magestore\Customercredit\Model\CustomercreditFactory $customerCreditFactory,
        \Magestore\Customercredit\Model\TransactionFactory $transactionFactory,
        \Magestore\Customercredit\Model\TransactionTypeFactory $transactiontypeFactory,
        \Magestore\Customercredit\Helper\Data $helperData
    )
    {
        $this->_customerSession = $customerSession;
        $this->_customerCreditFactory = $customerCreditFactory;
        $this->_transactionFactory = $transactionFactory;
        $this->_transactiontypeFactory = $transactiontypeFactory;
        $this->datetime = $context->getLocaleDate();
        $this->_helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * Internal constructor, that is called from real constructor
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $customer_id = $this->_customerSession->getCustomer()->getId();
        $collection = $this->_transactionFactory->create()->getCollection()->addFieldToFilter('customer_id', $customer_id);
        $collection->setOrder('transaction_time', 'DESC');
        $this->setCollection($collection);
    }

    public function getLocaleDateTime()
    {
        return $this->datetime;
    }

    public function _prepareLayout()
    {
        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'customercredit.history.pager')->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getTransactionType($trans_type_id)
    {
        return $this->_transactiontypeFactory->create()->load($trans_type_id)->getTransactionName();
    }

    public function getCurrencyLabel($credit)
    {
//        $credit = $this->_customerCredit->getConvertedFromBaseCustomerCredit($credit); Gin fix multi currentcy
        return $this->_helperData->getFormatAmount($credit);
    }

}
