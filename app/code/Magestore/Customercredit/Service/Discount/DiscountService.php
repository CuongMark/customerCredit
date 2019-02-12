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

namespace Magestore\Customercredit\Service\Discount;


class DiscountService
{
    /**
     * @var array
     */
    protected $quoteTotalData;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->checkoutSession = $checkoutSession;
    }


    /**
     * Calculate quote totals for each giftCode and save results
     *
     * @param $items
     * @param $giftCodes
     * @param $address
     * @param bool $afterTax
     * @return $this
     */
    public function initTotals($items, $isApplyGiftAfterTax = false)
    {
        $totalItemsPrice = 0;
        $totalBaseItemsPrice = 0;
        $validItemsCount = 0;
        foreach ($items as $item) {
            //Skipping child items to avoid double calculations
            if ($item->getParentItemId() && $item->getProduct()->getTypeId() == 'customercredit') {
                continue;
            }

            $qty = $item->getTotalQty();
            $totalItemsPrice += $this->getItemPrice($item) * $qty - $item->getDiscountAmount();
            $totalBaseItemsPrice += $this->getItemBasePrice($item) * $qty - $item->getBaseDiscountAmount();
            if ($isApplyGiftAfterTax) {
                $totalItemsPrice += $item->getTaxAmount();
                $totalBaseItemsPrice += $item->getBaseTaxAmount();
            }
            $validItemsCount++;
        }

        $this->quoteTotalData = [
            'items_price' => $totalItemsPrice,
            'base_items_price' => $totalBaseItemsPrice,
            'items_count' => $validItemsCount,
        ];
    }

    /**
     * @return array
     */
    public function getQuoteTotalData()
    {
        return $this->quoteTotalData;
    }

    /**
     * Return item base price
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return float
     */
    public function getItemBasePrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        return ($price !== null) ? $price : $item->getBaseCalculationPrice();
    }

    /**
     * Return item price
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return float
     */
    public function getItemPrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        return $price === null ? $item->getCalculationPrice() : $price;
    }
}
