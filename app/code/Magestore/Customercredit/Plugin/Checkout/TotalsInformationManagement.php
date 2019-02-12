<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Customercredit\Plugin\Checkout;
/**
 * Class TotalsInformationManagement
 * @package Magestore\Customercredit\Plugin\Checkout
 */
class TotalsInformationManagement
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * PaymentInformationManagement constructor.
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    )
    {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\Checkout\Model\TotalsInformationManagement $totalsInformationManagement
     * @param $totals
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
     * @return mixed
     */
    public function aroundCalculate(
        \Magento\Checkout\Model\TotalsInformationManagement $totalsInformationManagement,
        \Closure $proceed,
        $cartId,
        \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
    )
    {
        $totals = $proceed($cartId, $addressInformation);
        if ($totals && $totals->getBaseDiscountAmount()) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->quoteRepository->getActive($cartId);
            $baseCustomercreditDiscount = $quote->getBaseCustomercreditDiscount();
            if ($baseCustomercreditDiscount) {
                $totals->setBaseDiscountAmount($totals->getBaseDiscountAmount() + $baseCustomercreditDiscount);
                $totals->setDiscountAmount($totals->getDiscountAmount() + $quote->getCustomercreditDiscount());
            }
        }
        return $totals;
    }
}