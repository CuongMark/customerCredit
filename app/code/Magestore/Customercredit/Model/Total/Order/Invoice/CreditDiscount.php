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
namespace Magestore\Customercredit\Model\Total\Order\Invoice;

/**
 * Giftvoucher Total Order Invoice Giftvoucher Model
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class CreditDiscount extends \Magento\Sales\Model\Order\Total\AbstractTotal
{
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_creditHelper;

    /**
     * @param \Magestore\Customercredit\Helper\Data $creditHelper
     */
    public function __construct(
        \Magestore\Customercredit\Helper\Data $creditHelper
    )
    {
        $this->_creditHelper = $creditHelper;
    }


    /**
     * Collect invoice giftvoucher
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        if ($order->getCustomercreditDiscount() < 0.0001) {
            return;
        }

        $totalDiscountInvoiced = 0;
        $totalBaseDiscountInvoiced = 0;

        $totalDiscountAmount = 0;
        $totalBaseDiscountAmount = 0;
        $checkAddShipping = true;

        foreach ($order->getInvoiceCollection() as $previousInvoice) {
            if ($previousInvoice->getCustomercreditDiscount()) {
                $checkAddShipping = false;
                $totalBaseDiscountInvoiced += $previousInvoice->getBaseCustomercreditDiscount();
                $totalDiscountInvoiced += $previousInvoice->getCustomercreditDiscount();
            }
        }

        if ($checkAddShipping) {
            $totalBaseDiscountAmount += $order->getBaseCustomercreditDiscountForShipping();
            $totalDiscountAmount += $order->getCustomercreditDiscountForShipping();
        }

        if ($invoice->isLast()) {
            $totalBaseDiscountAmount = $order->getBaseCustomercreditDiscount() - $totalBaseDiscountInvoiced;
            $totalDiscountAmount = $order->getCustomercreditDiscount() - $totalDiscountInvoiced;
        } else {
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }
                $baseOrderItemCustomercreditDiscount = (float)$orderItem->getBaseCustomercreditDiscount();
                $orderItemCustomercreditDiscount = (float)$orderItem->getCustomercreditDiscount();

                $orderItemQty = $orderItem->getQtyOrdered();
                $invoiceItemQty = $item->getQty();

                if ($baseOrderItemCustomercreditDiscount && $orderItemQty) {
                    $totalBaseDiscountAmount += $invoice->roundPrice(
                        $baseOrderItemCustomercreditDiscount / $orderItemQty * $invoiceItemQty,
                        'base',
                        false
                    );
                    $totalDiscountAmount += $invoice->roundPrice(
                        $orderItemCustomercreditDiscount / $orderItemQty * $invoiceItemQty,
                        'regular',
                        false
                    );
                }
            }
        }
        
        $invoice->setBaseCustomercreditDiscount($totalBaseDiscountAmount);
        $invoice->setCustomercreditDiscount($totalDiscountAmount);

        return $this;
    }

}
