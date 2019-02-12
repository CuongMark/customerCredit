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

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magestore\Customercredit\Helper\Account;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magestore\Customercredit\Helper\Account
     */
    protected $_accountHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Account $accountHelper
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Account $accountHelper,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_accountHelper = $accountHelper;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if($this->_scopeConfig->getValue('customercredit/general/enable') == 0){
            return $this->_forward('noroute');
        }

        if (!$this->_accountHelper->isLoggedIn()){
            return $this->_redirect('customer/account/login');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('My Credit'));
        return $resultPage;
    }
}
 