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

namespace Magestore\Customercredit\Model\Total\Quote;

class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    protected $_code = 'creditdiscount';
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_checkoutSession;
    /**
     * @var \Magestore\Customercredit\Helper\Account
     */
    protected $_accountHelper;
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_creditHelper;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\SalesRule\Model\Validator $validator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magestore\Customercredit\Helper\Account $accountHelper,
        \Magestore\Customercredit\Helper\Data $creditHelper
    ) {
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->_checkoutSession = $checkoutSession;
        $this->_accountHelper = $accountHelper;
        $this->_creditHelper = $creditHelper;
    }

    /**
     * Collect address discount amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $shippingAssignment->getItems();

        if (!count($items)) {
            return $this;
        }

        if($this->_creditHelper->getSpendConfig('tax',$quote->getStoreId()) == '0'){
            return $this;
        }

        $creditAmountEntered = $this->_checkoutSession->getCustomerCreditAmount();
        if ($creditAmountEntered === 0 || !$this->_accountHelper->customerGroupCheck()) {
            $this->_checkoutSession->setCreditdiscountAmount(null);
            $this->_checkoutSession->setBaseCreditdiscountAmount(null);
            return $this;
        }
        $baseDiscountTotal = 0;

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if (!$child->isDeleted() && $child->getProduct()->getTypeId() != 'customercredit') {
                        $itemDiscount = $child->getBaseRowTotal() + $child->getBaseTaxAmount() - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                        $baseDiscountTotal += $itemDiscount;
                    }
                }
            } else {
                if (!$item->isDeleted() && $item->getProduct()->getTypeId() != 'customercredit') {
                    $itemDiscount = $item->getBaseRowTotal() + $item->getBaseTaxAmount() - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
                    $baseDiscountTotal += $itemDiscount;
                }
            }
        }

        /** Process shipping amount discount */
        $baseShippingDiscount = 0;
        if ($this->_creditHelper->getSpendConfig('shipping')) {
            $baseShippingDiscount = $address->getBaseShippingAmount() + $address->getBaseShippingTaxAmount() - $address->getBaseShippingDiscountAmount() - $address->getMagestoreBaseDiscountForShipping();
            $baseDiscountTotal += $baseShippingDiscount;
        }
        $baseDiscount = min($creditAmountEntered, $baseDiscountTotal);
        $discount = $this->priceCurrency->convert($baseDiscount);

        $total->addTotalAmount($this->getCode(), -$discount);
        $total->addBaseTotalAmount($this->getCode(), -$baseDiscount);

        $total->setCustomercreditAmount($discount);
        $total->setBaseCustomercreditAmount($baseDiscount);

        $quote->setCreditdiscountAmount($discount);
        $quote->setBaseCreditdiscountAmount($baseDiscount);
        $this->_checkoutSession->setCreditdiscountAmount($discount);
        $this->_checkoutSession->setBaseCreditdiscountAmount($baseDiscount);

        $total->setGrandTotal($total->getGrandTotal() - $discount);
        $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseDiscount);
        if ($baseDiscountTotal != 0)
            $this->_prepareDiscountCreditForItem($address, $quote, $baseDiscount / $baseDiscountTotal, $baseShippingDiscount);

        $this->_checkoutSession->setData('addedcustomitempayment', false);
        return $this;
    }

    /**
     * Add discount total information to address
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        if($this->_creditHelper->getSpendConfig('tax',$quote->getStoreId()) == '0' || !$this->_accountHelper->customerGroupCheck()){
            return $result;
        }
        $amount = $total->getCreditdiscountAmount();
        if (!$amount) {
            $amount = $this->_checkoutSession->getCreditdiscountAmount();
        }

        if ($amount != 0) {
            $result = [
                'code' => $this->getCode(),
                'title' => __('Customer Credit'),
                'value' => -abs($amount)
            ];
        }

        return $result;
    }
}
