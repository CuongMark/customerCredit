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

namespace Magestore\Customercredit\Block\Adminhtml\Customer\Renderer;

class Customerprice extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_helperCore;
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customercreditFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercreditFactory,
        \Magento\Framework\Pricing\Helper\Data $helperCore,
        array $data = []
    )
    {
        $this->_customercreditFactory = $customercreditFactory;
        $this->_helperCore = $helperCore;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $customerId = $row->getId();
        $customer = $this->_customercreditFactory->create()->load($customerId, 'customer_id');
        $price = $customer->getCreditBalance();

        if ($price == NULL) {
            $price = 0.00;
        }
        return $this->_helperCore->currency($price, true, false);
    }

}
