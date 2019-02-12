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

namespace Magestore\Customercredit\Model\ResourceModel;

class Transaction extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;
    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        $connectionName = null
    )
    {
       $this->_priceCurrency = $priceCurrency;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('credit_transaction', 'transaction_id');
    }

    public function getCreditUsed()
    {
        $table = $this->getMainTable();
        $select = $this->getConnection()->select()->from($table)->reset('columns')->columns(new \Zend_Db_Expr('SUM(spent_credit)'));
        $spent_credit = $this->getConnection()->fetchOne($select);
        return $this->_priceCurrency->convertAndFormat($spent_credit);
    }
}