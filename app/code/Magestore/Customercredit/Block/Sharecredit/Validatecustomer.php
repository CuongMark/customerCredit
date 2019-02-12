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

namespace Magestore\Customercredit\Block\Sharecredit;

class Validatecustomer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Customercredit\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Customercredit\Helper\Data $helper
    )
    {
        $this->_helper = $helper;
        parent::__construct($context);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getVerifyEnable()
    {
        return $this->_helper->getGeneralConfig('validate');
    }

    public function getFormActionUrl()
    {
        return $this->getUrl('customercredit/index/sharepost');
    }

    public function getVerifyCode()
    {
        $code = $this->_request->getParam('keycode');
        if ($code) {
            return $code;
        }
        return "";
    }

}
