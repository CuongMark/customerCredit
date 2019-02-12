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

namespace Magestore\Customercredit\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class Customercreditlist extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_helperImage;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $_categoryHelper;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Helper\Output $helperOutput
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,

        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    )
    {
        $this->_productFactory = $productFactory;
        $this->_helperImage = $context->getImageHelper();
        $this->_objectManager = $objectManager;
        $this->_categoryHelper = $categoryHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getHelperImage()
    {
        return $this->_helperImage;
    }

    public function getBlock($blockname)
    {
        return $this->_objectManager->get($blockname);
    }

    public function getStoreCategories()
    {
        return $this->_categoryHelper->getStoreCategories();
    }

    protected function _getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        //$collection->addCategoriesFilter(['in' => $this->getFilterByCategores()]);

        $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $collection->addFieldToFilter('type_id', 'customercredit');
        $collection->addStoreFilter($this->_storeManager->getStore()->getId());
        return $collection;
    }
    /*
     * Code for multi root categories
     * */
    protected function getFilterByCategores()
    {
        $category_ids = [];
        $rootCategoryId = $this->_storeManager->getStore()->getRootCategoryId();
        if($rootCategoryId){
            $category_ids[] = $rootCategoryId;
        }
        $categories = $this->getStoreCategories();
        foreach($categories as $category){
            $category_ids[] = $category->getId();
            foreach($category->getAllChildNodes() as $child_category){
                $category_ids[] = $child_category->getId();
            }
        }

        return $category_ids;
    }
}
