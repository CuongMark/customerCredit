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

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        $this->_productFactory = $productFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $productIds = $this->getRequest()->getParam('product');
        if (!is_array($productIds)) {
            $this->messageManager->addError(__('Please select product(s).'));
        } else {
            if (!empty($productIds)) {
                try {
                    foreach ($productIds as $productId) {
                        $product = $this->_productFactory->create()->load($productId);
                        $product->delete();
                    }
                    $this->messageManager->addSuccess(
                        __('Total of %d record(s) have been deleted.', count($productIds))
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }
}