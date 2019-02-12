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

namespace Magestore\Customercredit\Model;

class Creditcode extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_customercreditHelper;

    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context,
     * @param \Magento\Framework\Registry $registry,
     * @param \Magestore\Customercredit\Helper\Data $customercreditHelper,
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager,
     * @param \Magestore\Customercredit\Model\ResourceModel\Creditcode $resource
     * @param \Magestore\Customercredit\Model\ResourceModel\Creditcode\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\Customercredit\Helper\Data $customercreditHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Customercredit\Model\ResourceModel\Creditcode $resource,
        \Magestore\Customercredit\Model\ResourceModel\Creditcode\Collection $resourceCollection
    )
    {
        $this->_customercreditHelper = $customercreditHelper;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection);
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magestore\Customercredit\Model\ResourceModel\Creditcode');
        $this->setIdFieldName('credit_code_id');
    }

    public function beforeSave()
    {
        if (!$this->getCreditCode())
            $this->setCreditCode('[N.4]-[AN.5]-[A.4]');
        if ($this->_codeIsExpression())
            $this->setCreditCode($this->_getCreditCode());
        return parent::beforeSave();
    }

    protected function _codeIsExpression()
    {
        return $this->_customercreditHelper->isExpression($this->getCreditCode());
    }

    protected function _getCreditCode()
    {
        $code = $this->_customercreditHelper->calcCode($this->getCreditCode());
        $times = 10;
        while ($this->loadByCode($code)->getId() && $times) {
            $code = $this->_customercreditHelper->calcCode($this->getCreditCode());
            $times--;
            if ($times == 0) {
                throw new \Exception(__('Exceeded maximum retries to find available random credit code!'));
            }
        }
        return $code;
    }

    public function loadByCode($code)
    {
        return $this->load($code, 'credit_code');
    }

    public function changeCodeStatus($credit_code_id, $status)
    {
        $credit_code = $this->load($credit_code_id);
        $credit_code->setStatus($status);
        try {
            $credit_code->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
//            echo $e->getMessage();
        }
    }

    public function addCreditCode($friend_email, $credit_amount, $status, $customer_id)
    {
        $store = $this->_storeManager->getStore();
        $currentCurrencyCode = $store->getCurrentCurrency()->getCode();
        if ($status) {
            $this->setStatus($status);
        }
        $this->setRecipientEmail($friend_email)
            ->setDescription('send code to friend')
            ->setTransactionTime(date("Y-m-d H:i:s"))
            ->setAmountCredit($credit_amount)
            ->setCustomerId($customer_id)
            ->setCurrency($currentCurrencyCode);
        try {
            $this->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
//            echo $e->getMessage();
        }
        return $this->getCreditCode();
    }
}