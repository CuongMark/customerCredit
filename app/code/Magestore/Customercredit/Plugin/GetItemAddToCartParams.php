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

namespace Magestore\Customercredit\Plugin;

class getItemAddToCartParams
{
    /**
     * @param $subject
     * @param $result
     * @return string
     */
    public function afterGetItemAddToCartParams($subject, $result)
    {
        $item = $subject->getItem();
        $product = $item->getProduct();
        if($product->getTypeId() == "customercredit"){
            return json_encode([ 'action' => $product->getProductUrl() ]);
        }
        return $result;
    }
}