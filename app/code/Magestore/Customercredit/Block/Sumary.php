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

class Sumary extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customerCreditFactory;

    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_creditHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customerCreditFactory
     * @param \Magestore\Customercredit\Helper\Data $creditHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Customercredit\Model\CustomercreditFactory $customerCreditFactory,
        \Magestore\Customercredit\Helper\Data $creditHelper
    )
    {
        $this->_customerCreditFactory = $customerCreditFactory;
        $this->_creditHelper = $creditHelper;
        parent::__construct($context);
    }

    public function getBalanceLabel()
    {
        return $this->_creditHelper->getCustomerCreditValueLabel();
    }

}
