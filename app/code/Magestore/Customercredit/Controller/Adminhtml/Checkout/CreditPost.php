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

namespace Magestore\Customercredit\Controller\Adminhtml\Checkout;

use Magento\Backend\App\Action\Context;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\View\Result\PageFactory;

class CreditPost extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var PriceCurrency
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $_checkoutSession;

    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_creditModel;

    /**
     * @var \Magento\Backend\Model\Session\Quote $sessionQuote
     */
    protected $_sessionQuote;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_helperJson;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Json\Helper\Data $helperJson
     * @param PriceCurrency $priceCurrency
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customercreditFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Json\Helper\Data $helperJson,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magestore\Customercredit\Model\CustomercreditFactory $customercreditFactory
    )
    {
        parent::__construct($context);
        $this->_sessionQuote = $sessionQuote;
        $this->_checkoutSession = $checkoutSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->_helperJson = $helperJson;
        $this->priceCurrency = $priceCurrency;
        $this->_creditModel = $customercreditFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $request = $this->getRequest();
        $session = $this->_checkoutSession;
        $result = array();

        $customer_id = $this->_sessionQuote->getCustomerId();
        $credit_available = $this->_creditModel->create()->load($customer_id, 'customer_id')->getCreditBalance();

        if ($request->isPost()) {
            $creditvalue = $request->getParam('credit_value');
            if($creditvalue <= $credit_available) {
                $this->_checkoutSession->setData('customer_credit_amount_entered', $creditvalue);
                $creditvalue = $creditvalue / $this->priceCurrency->convert(1, false, false);
                if ($creditvalue < 0.0001)
                    $creditvalue = 0;
                $session->setCustomerCreditAmount($creditvalue);
                $this->_checkoutSession->setUseCustomerCredit(true);
                $result['creditvalue'] = $this->_checkoutSession->getCreditdiscountAmount();
            }
        }
        $this->getResponse()->setBody($this->_helperJson->jsonEncode($result));
    }
}