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

namespace Magestore\Customercredit\Block\Order\Invoice;

class Totals extends \Magento\Sales\Block\Adminhtml\Totals
{
    public function initTotals()
    {
        parent::_initTotals();
        $totalsBlock = $this->getParentBlock();
        $invoice = $totalsBlock->getInvoice();
        if ($invoice->getCustomercreditDiscount() > 0) {
            $totalsBlock->addTotal(new \Magento\Framework\DataObject([
                'code' => 'customercredit',
                'label' => __('Customer Credit'),
                'value' => -$invoice->getCustomercreditDiscount(),
                'base_value' => -$invoice->getBaseCustomercreditDiscount(),
            ]), 'subtotal');
        }
        
        /**
         * Get total discount and re-calculate discount value to showing
         */
        $discountTotal = $totalsBlock->getTotal('discount');
        if (!empty($discountTotal) && $discountTotal->getValue() != 0) {
            $discountTotal->setValue($discountTotal->getValue() + $invoice->getCustomercreditDiscount());
            if ($discountTotal->getValue() != 0) {
                $totalsBlock->addTotal($discountTotal);
            } else {
                $totalsBlock->removeTotal($discountTotal->getCode());
            }
        }
    }

}