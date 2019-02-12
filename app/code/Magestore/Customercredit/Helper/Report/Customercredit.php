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

namespace Magestore\Customercredit\Helper\Report;

class Customercredit extends \Magento\Backend\Helper\Dashboard\AbstractDashboard
{
    /**
     * @var \Magestore\Customercredit\Model\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magestore\Customercredit\Model\TransactionFactory $transactionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magestore\Customercredit\Model\TransactionFactory $transactionFactory
    )
    {
        $this->_transactionFactory = $transactionFactory;
        parent::__construct($context);
    }

    protected function _initCollection()
    {
        $this->_collection = $this->_transactionFactory->create()->getCollection()->prepareCustomercredit($this->getParam('period'), 0, 0);
        $this->_collection->load();
    }
}
