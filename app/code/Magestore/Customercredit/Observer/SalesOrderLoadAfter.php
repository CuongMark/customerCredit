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

namespace Magestore\Customercredit\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class SalesOrderLoadAfter implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer['order'];
        if ($order->getCustomercreditDiscount() < 0.0001
            || $order->getGrandTotal() > 0
            || $order->getState() === Order::STATE_CLOSED
            || $order->isCanceled()
            || $order->canUnhold()
        ) {
            return $this;
        }
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItemId())
                continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if (($child->getQtyInvoiced() - $child->getQtyRefunded() - $child->getQtyCanceled()) > 0) {
                        $order->setForcedCanCreditmemo(true);
                        return $this;
                    }
                }
            } elseif ($item->getProduct()) {
                if (($item->getQtyInvoiced() - $item->getQtyRefunded() - $item->getQtyCanceled()) > 0) {
                    $order->setForcedCanCreditmemo(true);
                    return $this;
                }
            }
        }
    }
}
