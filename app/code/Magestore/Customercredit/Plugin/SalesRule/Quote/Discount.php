<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Customercredit\Plugin\SalesRule\Quote;

/**
 * Class Discount
 * @package Magestore\Customercredit\Plugin\SalesRule\Quote
 */
class Discount
{
    /**
     * @param \Magento\SalesRule\Model\Quote\Discount $discount
     * @param null|array $result
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return null|array
     */
    public function aroundFetch(
        \Magento\SalesRule\Model\Quote\Discount $discount,
        \Closure $proceed,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
        $result = $proceed($quote, $total);
        if (is_array($result)) {
            if ($total->getCustomercreditDiscount() && $result['value'] != 0) {
                $result['value'] = min(0, $result['value'] + $total->getCustomercreditDiscount());
            }
        }
        return $result;
    }
}