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

namespace Magestore\Customercredit\Helper;

class Creditproduct extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepo;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Creditproduct constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductRepository $productRepo
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductRepository $productRepo
    )
    {
        $this->_storeManager = $storeManager;
        $this->_productRepo = $productRepo;
        parent::__construct($context);
    }

    public function getGeneralConfig($code, $store = null)
    {
        return $this->scopeConfig->getValue('customercredit/general/' . $code, 'store', $store);
    }

    /**
     * Default action to get price of product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getCreditDataByProduct($product)
    {
        $currentProduct = $this->_productRepo->getById($product->getId());
        $credit_type = $currentProduct->getStorecreditType();
        $currentStore = $this->_storeManager->getStore()->getStoreId();

        // convert Credit value from the old version
        if (!$credit_type) {
            $amountStr = $currentProduct->getCreditAmount();
            $amountStr = trim(str_replace(array(' ', "\r", "\t"), '', $amountStr));
            $creditAmount = $this->getDataForOldVersion($amountStr);

            switch ($creditAmount['type']) {
                case 'range':
                    $product->setStorecreditFrom($creditAmount['from'])
                        ->setStorecreditTo($creditAmount['to'])
                        ->setStorecreditType(\Magestore\Customercredit\Model\Source\Storecredittype::CREDIT_TYPE_RANGE)
                        ->save();
                    $credit_type = \Magestore\Customercredit\Model\Source\Storecredittype::CREDIT_TYPE_RANGE;
                    break;
                case 'dropdown':
                    $product->setStorecreditDropdown($amountStr)
                        ->setStorecreditType(\Magestore\Customercredit\Model\Source\Storecredittype::CREDIT_TYPE_DROPDOWN)
                        ->save();
                    $credit_type = \Magestore\Customercredit\Model\Source\Storecredittype::CREDIT_TYPE_DROPDOWN;
                    break;
                case 'static':
                    $product->setStorecreditValue($creditAmount['value'])
                        ->setStorecreditType(\Magestore\Customercredit\Model\Source\Storecredittype::CREDIT_TYPE_FIX)
                        ->save();
                    $credit_type = \Magestore\Customercredit\Model\Source\Storecredittype::CREDIT_TYPE_FIX;
                    break;
            }
            $this->_storeManager->setCurrentStore($currentStore);
        }

        switch ($credit_type) {
            case \Magestore\Customercredit\Model\Source\Storecredittype::CREDIT_TYPE_FIX:
                $data = array('type' => 'static', 'credit_price' => $currentProduct->getStorecreditValue() * $currentProduct->getStorecreditRate(), 'value' => $currentProduct->getStorecreditValue());
                return $data;
            case \Magestore\Customercredit\Model\Source\Storecredittype::CREDIT_TYPE_RANGE:
                $data = array('type' => 'range', 'from' => $currentProduct->getStorecreditFrom(), 'to' => $currentProduct->getStorecreditTo(), 'storecredit_rate' => $currentProduct->getStorecreditRate());
                return $data;
            case \Magestore\Customercredit\Model\Source\Storecredittype::CREDIT_TYPE_DROPDOWN:
                $options = explode(',',$currentProduct->getStorecreditDropdown());
                foreach ($options as $key => $option) {
                    if (!is_numeric($option) || $option <= 0) {
                        unset($options[$key]);
                    }
                }
                $data = array('type' => 'dropdown', 'options' => $options);
                foreach ($options as $value) {
                    $data['prices'][] = $value * $currentProduct->getStorecreditRate();
                }
                return $data;

            default:
                $creditAmount = $this->getGeneralConfig('amount');
                $options = explode(',', $creditAmount);
                return array('type' => 'dropdown', 'options' => $options, 'prices' => $options);
        }
    }

    public function getDataForOldVersion($amountStr)
    {
        $amountStr = trim(str_replace(array(' ', "\r", "\t"), '', $amountStr));
        if ($amountStr == '' || $amountStr == '-') {
            return array('type' => 'any');
        }

        $values = explode('-', $amountStr);
        if (count($values) == 2) {
            return array('type' => 'range', 'from' => $values[0], 'to' => $values[1]);
        }

        $values = explode(',', $amountStr);
        if (count($values) > 1) {
            return array('type' => 'dropdown', 'options' => $values);
        }

        $value = floatval($amountStr);
        return array('type' => 'static', 'value' => $value);
    }
}
