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

namespace Magestore\Customercredit\Block\Adminhtml;

class Statisticscredit extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customercreditFactory;
    /**
     * @var \Magestore\Customercredit\Model\ResourceModel\TransactionFactory
     */
    protected $_transactionFactory;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customercreditFactory
     * @param \Magestore\Customercredit\Model\ResourceModel\TransactionFactory $transactionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercreditFactory,
        \Magestore\Customercredit\Model\ResourceModel\TransactionFactory $transactionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    )
    {
        $this->_customercreditFactory = $customercreditFactory;
        $this->_transactionFactory = $transactionFactory;
        $this->_customerFactory = $customerFactory;
        $this->_priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Magestore_Customercredit::customercredit/statisticscredit.phtml');
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getTotalCredit()
    {
        $collections = $this->_customercreditFactory->create()->getCollection();
        $totalCredit = 0;
        foreach ($collections as $item) {
            if ($item->getCreditBalance()) {
                $totalCredit += $item->getCreditBalance();
            }
        }
        return $this->_priceCurrency->convertAndFormat($totalCredit);
    }

    public function getCreditUsed()
    {
        return $this->_transactionFactory->create()->getCreditUsed();
    }

    public function getCustomerWithCredit()
    {
        $collections = $this->_customercreditFactory->create()->getCollection()->addFieldToFilter('credit_balance', array('gt' => 0.00));
        $numCustomer = count($collections);
        return $numCustomer;
    }

    public function percentCredit()
    {
        $collections = $this->_customerFactory->create()->getCollection();
        $totalCustomer = count($collections);
        $percent = ($this->getCustomerWithCredit() / $totalCustomer) * 100;
        return round($percent, 2);
    }
}
