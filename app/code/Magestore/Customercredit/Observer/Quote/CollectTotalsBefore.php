<?php
/**
 * Copyright © 2017 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Customercredit\Observer\Quote;

/**
 * Class CollectTotalsBefore
 * @package Magestore\Customercredit\Observer\Quote
 */
class CollectTotalsBefore implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Set Quote information about Gift Card discount
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $quote->setBaseCustomercreditDiscount(0);
        $quote->setCustomercreditDiscount(0);
        $quote->setBaseCustomercreditDiscountForShipping(0);
        $quote->setCustomercreditDiscountForShipping(0);
        $quote->setMagestoreBaseDiscount(0);
        $quote->setMagestoreDiscount(0);
        $quote->setMagestoreBaseDiscountForShipping(0);
        $quote->setMagestoreDiscountForShipping(0);

        foreach ($quote->getAllAddresses() as $address) {
            $address->setBaseCustomercreditDiscount(0);
            $address->setCustomercreditDiscount(0);
            $address->setBaseCustomercreditDiscountForShipping(0);
            $address->setCustomercreditDiscountForShipping(0);
            $address->setMagestoreBaseDiscount(0);
            $address->setMagestoreDiscount(0);
            $address->setMagestoreBaseDiscountForShipping(0);
            $address->setMagestoreDiscountForShipping(0);

            foreach ($address->getAllItems() as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                $item->setBaseCustomercreditDiscount(0);
                $item->setCustomercreditDiscount(0);
                $item->setMagestoreBaseDiscount(0);
                $item->setMagestoreDiscount(0);
            }
        }
    }
}
