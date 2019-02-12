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

class DiscountAbstract extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    protected $_code = 'creditdiscount';

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Magestore\Customercredit\Helper\Account
     */
    protected $accountHelper;
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $creditHelper;

    /**
     * @var \Magestore\Customercredit\Service\Discount\DiscountService
     */
    protected $discountService;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * DiscountAbstract constructor.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magestore\Customercredit\Helper\Account $accountHelper
     * @param \Magestore\Customercredit\Helper\Data $creditHelper
     * @param \Magestore\Customercredit\Service\Discount\DiscountService $discountService
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magestore\Customercredit\Helper\Account $accountHelper,
        \Magestore\Customercredit\Helper\Data $creditHelper,
        \Magestore\Customercredit\Service\Discount\DiscountService $discountService,
        \Magento\Tax\Model\Config $taxConfig
    )
    {
        $this->priceCurrency = $priceCurrency;
        $this->checkoutSession = $checkoutSession;
        $this->accountHelper = $accountHelper;
        $this->creditHelper = $creditHelper;
        $this->discountService = $discountService;
        $this->taxConfig = $taxConfig;
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
    public function calculateDiscount(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total,
        $isApplyAfterTax = false
    )
    {
        $address = $shippingAssignment->getShipping()->getAddress();

        if ($quote->isVirtual() && $address->getAddressType() == 'shipping') {
            return $this;
        }
        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }

        $items = $shippingAssignment->getItems();

        if (!count($items)) {
            return $this;
        }

        $creditAmountEntered = $this->checkoutSession->getCustomerCreditAmount();
        if ($creditAmountEntered === 0 || !$this->accountHelper->customerGroupCheck()) {
            $this->checkoutSession->setCreditdiscountAmount(null);
            $this->checkoutSession->setBaseCreditdiscountAmount(null);
            return $this;
        }

        $baseDiscountTotal = $discountTotal = 0;
        $store = $quote->getStore();

        $this->discountService->initTotals($items, $isApplyAfterTax);

        $itemsTotal = $this->discountService->getQuoteTotalData();
        if ($itemsTotal['base_items_price'] <= 0) {
            $this->checkoutSession->setCreditdiscountAmount(null);
            $this->checkoutSession->setBaseCreditdiscountAmount(null);
            return $this;
        }

        $baseItemsPrice = 0;
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            $qty = $item->getTotalQty();
            $itemPrice = $this->discountService->getItemPrice($item);
            $baseItemPrice = $this->discountService->getItemBasePrice($item);
            $itemPriceAfterDiscount = $itemPrice * $qty - $item->getDiscountAmount();

            $baseDiscountAmount = $item->getBaseDiscountAmount();
            $baseItemPriceAfterDiscount = $baseItemPrice * $qty - $baseDiscountAmount;
            if ($isApplyAfterTax) {
                $itemPriceAfterDiscount += $item->getTaxAmount();
                $baseItemPriceAfterDiscount += $item->getBaseTaxAmount();
            }

            $baseItemsPrice += $baseItemPriceAfterDiscount;
            if ($baseItemsPrice == $itemsTotal['base_items_price']) {
                $baseItemDiscountAmount = $creditAmountEntered - $baseDiscountTotal;
            } else {
                $discountRate = $baseItemPriceAfterDiscount / $itemsTotal['base_items_price'];
                $baseItemDiscountAmount = $creditAmountEntered * $discountRate;
            }

            $baseItemDiscountAmount = $this->priceCurrency->round($baseItemDiscountAmount);
            $baseItemDiscountAmount = min($baseItemDiscountAmount, $baseItemPriceAfterDiscount);

            $itemDiscountAmount = $this->priceCurrency->convert($baseItemDiscountAmount, $store);
            $itemDiscountAmount = $this->priceCurrency->round($itemDiscountAmount);
            $itemDiscountAmount = min($itemDiscountAmount, $itemPriceAfterDiscount);


            $item->setBaseCustomercreditDiscount($baseItemDiscountAmount)
                ->setCustomercreditDiscount($itemDiscountAmount)
                ->setMagestoreBaseDiscount($item->getMagestoreBaseDiscount() + $baseItemDiscountAmount)
                ->setMagestoreDiscount($item->getMagestoreDiscount() + $itemDiscountAmount)
                ->setBaseDiscountAmount($item->getBaseDiscountAmount() + $baseItemDiscountAmount)
                ->setDiscountAmount($item->getDiscountAmount() + $itemDiscountAmount);

            $baseDiscountTotal += $baseItemDiscountAmount;
            $discountTotal += $itemDiscountAmount;
        }

        if ($creditAmountEntered > $baseDiscountTotal && $this->creditHelper->getSpendConfig('shipping')) {
            $shippingAmount = $address->getShippingAmountForDiscount();
            if ($shippingAmount !== null) {
                $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
            } else {
                $baseShippingAmount = $address->getBaseShippingAmount();
            }
            $baseShippingAmount = $baseShippingAmount - $address->getBaseShippingDiscountAmount();
            if ($isApplyAfterTax) {
                $baseShippingAmount += $address->getBaseShippingTaxAmount();
            }

            $baseDiscountShipping = $creditAmountEntered - $baseDiscountTotal;
            $baseDiscountShipping = min($baseDiscountShipping, $baseShippingAmount);
            $baseDiscountShipping = $this->priceCurrency->round($baseDiscountShipping);

            $discountShipping = $this->priceCurrency->convert($baseDiscountShipping, $quote->getStore());
            $discountShipping = $this->priceCurrency->round($discountShipping);

            $total->setBaseCustomercreditDiscountForShipping($total->getBaseCustomercreditDiscountForShipping() + $baseDiscountShipping);
            $total->setCustomercreditDiscountForShipping($total->getCustomercreditDiscountForShipping() + $discountShipping);
            $total->setMagestoreBaseDiscountForShipping($total->getMagestoreBaseDiscountForShipping() + $baseDiscountShipping);
            $total->setMagestoreDiscountForShipping($total->getMagestoreDiscountForShipping() + $discountShipping);
            $total->setBaseShippingDiscountAmount(max(0, $total->getBaseShippingDiscountAmount() + $baseDiscountShipping));
            $total->setShippingDiscountAmount(max(0, $total->getShippingDiscountAmount() + $discountShipping));

            $baseDiscountTotal += $baseDiscountShipping;
            $discountTotal += $discountShipping;
        }

        $this->checkoutSession->setCreditdiscountAmount($discountTotal);
        $this->checkoutSession->setBaseCreditdiscountAmount($baseDiscountTotal);

        $total->setBaseCustomercreditDiscount($baseDiscountTotal);
        $total->setCustomercreditDiscount($discountTotal);
        $total->setMagestoreBaseDiscount($total->getMagestoreBaseDiscount() + $baseDiscountTotal);
        $total->setMagestoreDiscount($total->getMagestoreDiscount() + $discountTotal);
        $total->setBaseDiscountAmount($total->getBaseDiscountAmount() - $baseDiscountTotal);
        $total->setDiscountAmount($total->getDiscountAmount() - $discountTotal);
        $total->setBaseSubtotalWithDiscount($total->getBaseSubtotalWithDiscount() - $baseDiscountTotal);
        $total->setSubtotalWithDiscount($total->getSubtotalWithDiscount() - $discountTotal);

        $quote->setBaseCustomercreditDiscount($total->getBaseCustomercreditDiscount());
        $quote->setCustomercreditDiscount($total->getCustomercreditDiscount());
        $quote->setMagestoreBaseDiscount($total->getMagestoreBaseDiscount());
        $quote->setMagestoreDiscount($total->getMagestoreDiscount());
        $quote->setBaseCustomercreditDiscountForShipping($total->getBaseCustomercreditDiscountForShipping());
        $quote->setCustomercreditDiscountForShipping($total->getCustomercreditDiscountForShipping());
        $quote->setMagestoreBaseDiscountForShipping($total->getMagestoreBaseDiscountForShipping());
        $quote->setMagestoreDiscountForShipping($total->getMagestoreDiscountForShipping());

        $total->setTotalAmount($this->getCode(), (string) -$baseDiscountTotal);
        $total->setBaseTotalAmount($this->getCode(), (string) -$discountTotal);
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
        $result = [];
        $amount = $total->getCustomercreditDiscount();

        if ($amount != 0) {
            if ($this->getCode() == 'creditdiscount') {
                $result = [
                    'code' => $this->getCode(),
                    'title' => __('Customer Credit'),
                    'value' => -abs($amount)
                ];
            }
        }

        return $result;
    }
}
