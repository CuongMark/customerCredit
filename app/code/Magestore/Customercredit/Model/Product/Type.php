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

namespace Magestore\Customercredit\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

class Type extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    /**
     * Product type
     */
    const TYPE_CODE = 'customercredit';

    /**
     * @var \Magestore\Customercredit\Helper\Creditproduct
     */
    protected $_creditProductHelper;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $helperData;
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogHelper;
    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Product\Option $catalogProductOption
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magestore\Customercredit\Helper\Creditproduct $creditproductHelper
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magestore\Customercredit\Helper\Data $helperData
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        \Magestore\Customercredit\Helper\Creditproduct $creditproductHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magestore\Customercredit\Helper\Data $helperData,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Catalog\Helper\Data $catalogData
    ) {
        $this->_creditProductHelper = $creditproductHelper;
        $this->priceCurrency = $priceCurrency;
        $this->helperData = $helperData;
        $this->_taxData = $taxHelper;
        $this->_catalogHelper = $catalogData;
        parent::__construct($catalogProductOption, $eavConfig, $catalogProductType, $eventManager, $fileStorageDb, $filesystem, $coreRegistry, $logger, $productRepository);
    }

    /**
     * Delete data specific for Simple product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
    }

    public function isVirtual($product = null)
    {
        return true;
    }

    public function hasRequiredOptions($product = null)
    {
        if($this->_creditProductHelper->getGeneralConfig('enable_send_credit') == '1'){
            return true;
        }
        $storecredit_type = $product->getData('storecredit_type');
        if($storecredit_type == '1'){
                return false;
        }
        return true;
    }

    public function canConfigure($product = null)
    {
        return true;
    }

    /**
     * Initialize product(s) for add to cart process
     *
     * @param \Magento\Framework\Object $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @return array|string
     */
    public function prepareForCart(\Magento\Framework\DataObject $buyRequest, $product)
    {
        if (is_null($product))
            $product = $this->getProduct();
        $result = parent::prepareForCart($buyRequest, $product);
        if (is_string($result))
            return $result;
        reset($result);
        $product = current($result);
        $result = $this->_prepareCustomerCredit($buyRequest, $product);
        return $result;
    }

    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        if (is_null($product))
            $product = $this->getProduct();
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);

        if (is_string($result))
            return $result;

        reset($result);
        $product = current($result);
        $result = $this->_prepareCustomerCredit($buyRequest, $product, $processMode);
        return $result;
    }

    protected function _prepareCustomerCredit(\Magento\Framework\DataObject $buyRequest, $product, $processMode = null)
    {
        $fnPrice = 0;
        $amount = $buyRequest->getAmount();
        if ($amount || !$this->_isStrictProcessMode($processMode)) {
            $creditData = $this->_creditProductHelper->getCreditDataByProduct($product);
            switch ($creditData['type']) {
                case 'range':
                    if ($amount < $this->convertPrice($product, $creditData['from'])) {
                        $amount = $this->convertPrice($product, $creditData['from']) * $creditData['storecredit_rate'];
                    } elseif ($amount > $this->convertPrice($product, $creditData['to'])) {
                        $amount = $this->convertPrice($product, $creditData['to']) * $creditData['storecredit_rate'];
                    } else {
                        if ($amount > 0) {
                            $amount = $amount * $creditData['storecredit_rate'];
                        } else {
                            $amount = 0;
                        }
                    }

                    $fnPrice = $amount / $this->priceCurrency->convert(1, false, false);
                    break;
                case 'dropdown':
                    if (!empty($creditData['options'])) {
                        $check = false;
                        $giftDropdown = array();
                        for ($i = 0; $i < count($creditData['options']); $i++) {
                            $option = $this->convertPrice($product, $creditData['options'][$i]);
                            if ($amount == $option) {
                                $check = true;
                            }
                            $giftDropdown[$i] = 'k_'.$option;
                        }
                        if (!$check) {
                            $amount = $creditData['options'][0];
                        }

                        $fnPrices = array_combine($giftDropdown, $creditData['prices']);
                        $fnPrice = $fnPrices['k_'.$this->convertPrice($product, $amount)];
                    }
                    break;
                case 'static':
                    if ($amount != $this->convertPrice($product, $creditData['value'])) {
                        $amount = $creditData['value'];
                    }
                    $fnPrice = $creditData['credit_price'];
                    break;
                default:
                    return array($product);
                //return __('Please enter Store Credit information.');
            }
        } else {
            return array($product);
            //return __('Please enter Store Credit information.');
        }

        $buyRequest->setAmount($amount);
        $product->addCustomOption('credit_price_amount', $this->priceCurrency->round($fnPrice));
        foreach ($this->helperData->getFullCreditProductOptions() as $key => $label) {
            if ($value = $buyRequest->getData($key)) {
                $product->addCustomOption($key, $value);
            }
        }

        return array($product);
    }

    public function convertPrice($product, $price)
    {
        $includeTax = ($this->_taxData->getPriceDisplayType() != 1);
        $priceWithTax = $this->_catalogHelper->getTaxPrice($product, $price, $includeTax);

        return $this->priceCurrency->convert($priceWithTax);
    }
}
