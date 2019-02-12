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

class Redeemcredit extends \Magento\Framework\View\Element\Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getActionUrl()
    {
        return $this->getUrl('customercredit/index/redeempost');
    }

    public function getCode()
    {
        return $this->getRequest()->getParam('code');
    }

}
