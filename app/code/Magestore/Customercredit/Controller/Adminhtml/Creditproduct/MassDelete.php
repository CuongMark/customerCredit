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

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Customercredit::creditproduct');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $productIds = $this->getRequest()->getParam('product');
        $storeId = (int)$this->getRequest()->getParam('store', 0);

        if (!is_array($productIds) || empty($productIds)) {
            $this->messageManager->addError(__('Please select product(s).'));
        } else {
            try {
                foreach ($productIds as $productId) {
                    $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($productId);
                    $product->delete();
                }
                $this->messageManager->addSuccess(
                    sprintf(__('Total of %d record(s) have been deleted.'), count($productIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/', array('store' => $storeId));
    }
}
