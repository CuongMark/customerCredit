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

class Customer extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Customer\Model\CustomerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $data = []
    )
    {
        $this->_customerFactory = $customerFactory;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $customerId = $row->getId();
        $customer = $this->_customerFactory->create()->load($customerId);
        $emailAdrress = $customer->getData('email');

        $action_name = $this->getRequest()->getActionName();
        if($action_name == 'exportCsv' || $action_name == 'exportXml' ){
            return $emailAdrress;
        }

        if ($customer) {
            $href = $this->getUrl('customer/index/edit', ['id' => $customer->getId(), 'active_tab' => 'cart']);
            return '<a href="' . $href . '" target="_blank">' . $emailAdrress . '</a>';
        }
        return $row->getId();
    }

}
