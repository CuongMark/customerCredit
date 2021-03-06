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

namespace Magestore\Customercredit\Plugin\Adminhtml\Items;

class GetPriceDataObject
{
    public function afterGetPriceDataObject(\Magento\Sales\Block\Adminhtml\Items\AbstractItems $subject, $result)
    {
        if($result->getProductType() == 'customercredit'){
            $result->setOriginalPrice($result->getPrice());
            $result->setBaseOriginalPrice($result->getBasePrice());
        }

        return $result;
    }
}
