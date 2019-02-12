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

namespace Magestore\Customercredit\Controller\Index;

class Share extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $resultPageFactory;
    /**
     * @var \Magestore\Customercredit\Helper\Account
     */
    protected $_accountHelper;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magestore\Customercredit\Helper\Account $accountHelper
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magestore\Customercredit\Helper\Account $accountHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->_accountHelper = $accountHelper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        if (!$this->_accountHelper->isLoggedIn())
            return $this->_redirect('customer/account/login');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Share Credit'));
        return $resultPage;
    }
}
