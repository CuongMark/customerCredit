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

namespace Magestore\Customercredit\Pricing\ConfiguredPrice;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;

class ConfigurableProduct extends FinalPrice implements ConfiguredPriceInterface
{
    /**
     * @var ItemInterface
     */
    private $item;

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        $result = 0.;
        /** @var \Magento\Wishlist\Model\Item\Option $customOption */
        $customOption = $this->getProduct()->getCustomOption('credit_price_amount');
        if ($customOption) {
            $result = $this->priceCurrency->convert($customOption->getValue(), false, false);
        }

        if($result == 0){
            /** @var \Magento\Framework\Pricing\PriceInfoInterface $priceInfo */
            $priceInfo = $this->getProduct()->getPriceInfo();
            $result = $this->priceCurrency->convert($priceInfo->getPrice(self::PRICE_CODE)->getValue(), false, false);
        }
        return max(0, $result);
    }

    /**
     * @inheritdoc
     */
    public function setItem(ItemInterface $item)
    {
        $this->item = $item;
        return $this;
    }
}
