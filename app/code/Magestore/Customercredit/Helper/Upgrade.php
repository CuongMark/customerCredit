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

use \Magento\Framework\App\Helper\AbstractHelper;

class Upgrade extends AbstractHelper
{
    const MAGENTO_EE = 'Enterprise';
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\State $state
    )
    {
        $this->productMetadata = $productMetadata;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productFactory = $productFactory;
        try{
            $state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        } catch(\Exception $e) {
            $state->getAreaCode();
        }
        parent::__construct($context);
    }

    public function getProductData()
    {
        $data = [];
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->addFilter('type_id', 'customercredit');
        foreach ($collection->getItems() as $item){
            $id = $item->getEntityId();
            $product = $this->productFactory->create()->load($id);

            $data[$id]['storecredit_rate'] = $product->getCreditRate();
            $data[$id]['storecredit_value'] = $product->getStorecreditValue();
            $data[$id]['storecredit_from'] = $product->getStorecreditFrom();
            $data[$id]['storecredit_to'] = $product->getStorecreditTo();
            $data[$id]['storecredit_dropdown'] = $product->getStorecreditDropdown();
            $data[$id]['storecredit_type'] = $product->getStorecreditType();
        }
        return $data;
    }

    public function setProductData($data)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->addFilter('type_id', 'customercredit');
        foreach ($collection->getItems() as $item){
            $id = $item->getEntityId();
            $data_set = $data[$id];

            /* @var $product \Magento\Catalog\Model\Product */
            $product = $this->productFactory->create()->load($id);

            $attributes = $item->getAttributes();
            foreach ($attributes as $attribute) {
                if($attribute->getAttributeCode() == 'storecredit_type'){
                    $this->xlog(__LINE__.' '.__METHOD__);
                    $this->xlog($attribute->getAttributeCode());
                    $this->xlog($attribute->getAttributeId());
                }
            }

            $product->addData($data_set);
            $product->save();
            $this->xlog(__LINE__.' '.__METHOD__);
            $this->xlog($product->getData('storecredit_type'));
        }

        return $this;
    }

    /*
     * @param  $message string|array
     * @return void
     */
    public function xlog($message = 'null')
    {
        $log = print_r($message, true);
        \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Psr\Log\LoggerInterface')
            ->debug($log);
    }
    /**
     *
     * @return string
     */
    public function checkMagentoEE() {
        $edition = $this->productMetadata->getEdition();
        if ($edition == self::MAGENTO_EE) {
            return true;
        } else {
            return false;
        }
    }
}