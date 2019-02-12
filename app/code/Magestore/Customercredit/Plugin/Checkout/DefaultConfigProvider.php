<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Customercredit\Plugin\Checkout;
/**
 * Class DefaultConfigProvider
 * @package Magestore\Customercredit\Plugin\Checkout
 */
class DefaultConfigProvider
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * DefaultConfigProvider constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $configProvider
     * @param $output
     * @return mixed
     */
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $configProvider,
        $output
    )
    {
        if (isset($output['totalsData'])) {
            $totalData = $output['totalsData'];
            if (isset($totalData['discount_amount']) && $totalData['discount_amount'] != 0) {
                $quote = $this->checkoutSession->getQuote();
                if ($quote->getCustomercreditDiscount() != 0) {
                    $totalData['discount_amount'] = $totalData['discount_amount'] + $quote->getCustomercreditDiscount();
                    $totalData['base_discount_amount'] = $totalData['base_discount_amount'] + $quote->getBaseCustomercreditDiscount();
                    $output['totalsData'] = $totalData;
                }
            }
        }
        return $output;
    }
}