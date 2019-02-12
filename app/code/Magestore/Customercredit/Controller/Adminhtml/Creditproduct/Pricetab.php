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

namespace Magestore\Customercredit\Controller\Adminhtml\Creditproduct;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Registry;

class Pricetab extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        ProductFactory $productFactory,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->productFactory = $productFactory;
        $this->registry = $registry;
    }

    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('id');
        $product = $this->productFactory->create()->load($productId);
        $this->registry->register('product', $product);
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}