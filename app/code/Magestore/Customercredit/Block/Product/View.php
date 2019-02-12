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

namespace Magestore\Customercredit\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class View extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * @var \Magestore\Customercredit\Helper\Creditproduct
     */
    protected $_creditproductHelper;
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_customercreditData;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;
    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;
    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogHelper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magestore\Customercredit\Helper\Creditproduct $creditproductHelper
     * @param \Magestore\Customercredit\Helper\Data $customercreditData
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magestore\Customercredit\Helper\Creditproduct $creditproductHelper,
        \Magestore\Customercredit\Helper\Data $customercreditData,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    )
    {
        $this->_creditproductHelper = $creditproductHelper;
        $this->_customercreditData = $customercreditData;
        $this->_objectManager = $objectManager;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_priceCurrency = $priceCurrency;
        $this->_localeFormat = $localeFormat;
        $this->_customerSession = $customerSession;
        $this->_request = $context->getRequest();
        $this->_catalogHelper = $context->getCatalogHelper();
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context, $arrayUtils, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    public function getCreditAmount($product)
    {
        $data = $this->_creditproductHelper->getCreditDataByProduct($product);
        switch ($data['type']) {
            case 'range':
                $data['from'] = $this->convertPrice($product, $data['from']);
                $data['to'] = $this->convertPrice($product, $data['to']);
                $data['from_txt'] = $this->_priceCurrency->format($data['from']);
                $data['to_txt'] = $this->_priceCurrency->format($data['to']);
                break;
            case 'dropdown':
                $data['options'] = $this->_convertPrices($product, $data['options']);
                $data['prices'] = $this->_convertPrices($product, $data['prices']);
                $data['prices'] = array_combine($data['options'], $data['prices']);
                $data['options_txt'] = $this->_formatPrices($data['options']);
                break;
            case 'static':
                $data['value'] = $this->convertPrice($product, $data['value']);
                $data['value_txt'] = $this->_priceCurrency->format($data['value']);
                $data['price'] = $this->convertPrice($product, $data['credit_price']);
                break;
            default:
                $data['type'] = 'any';
        }

        return $data;
    }

    protected function _convertPrices($product, $basePrices)
    {
        foreach ($basePrices as $key => $price)
            $basePrices[$key] = $this->convertPrice($product, $price);
        return $basePrices;
    }

    public function convertPrice($product, $price)
    {
        $includeTax = ($this->_taxData->getPriceDisplayType() != 1);
        $priceWithTax = $this->_catalogHelper->getTaxPrice($product, $price, $includeTax);
        return $this->_priceCurrency->convert($priceWithTax);
    }

    protected function _formatPrices($prices)
    {
        foreach ($prices as $key => $price) {
            $prices[$key] = $this->_priceCurrency->format($price);
        }
        return $prices;
    }

    public function getFormConfigData()
    {
        $request = $this->_request;
        $action = $request->getFullActionName();
        if ($action == 'checkout_cart_configure' && $request->getParam('product_id')) {
            $options = $this->_objectManager->create('Magento\Quote\Model\Quote\Item\Option')
                ->getCollection()
                ->addFieldToFilter('product_id', $request->getParam('product_id'))
                ->addFieldToFilter('item_id', $request->getParam('id'));
            $formData = array();
            foreach ($options as $option) {
                $formData[$option->getCode()] = $option->getValue();
            }
            return new \Magento\Framework\DataObject($formData);
        } else {
            return new \Magento\Framework\DataObject();
        }
    }

    public function getPriceFormatJs()
    {
        $priceFormat = $this->_localeFormat->getPriceFormat();
        return $this->_jsonEncoder->encode($priceFormat);
    }

    public function allowSendCredit()
    {
        $storeId = $this->_storeManager->getStore()->getStoreId();
        return $this->_customercreditData->getGeneralConfig('enable_send_credit', $storeId);
    }

    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    public function getJsonEncode()
    {
        return $this->_jsonEncoder;
    }

    public function getTaxHelper()
    {
        return $this->_taxData;
    }

    public function getCatalogHelper()
    {
        return $this->_catalogHelper;
    }

    public function getObjectManager()
    {
        return $this->_objectManager;
    }

    public function getPriceCurrency()
    {
        return $this->_priceCurrency;
    }

    public function getCurrentCustomerEmail()
    {
        return $this->_customerSession->getCustomer()->getEmail();
    }

}
