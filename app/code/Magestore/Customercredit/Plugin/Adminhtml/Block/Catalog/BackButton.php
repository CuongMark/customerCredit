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

namespace Magestore\Customercredit\Plugin\Adminhtml\Block\Catalog;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;

class BackButton
{
    /**
     * BackButton constructor
     *
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->context = $context;
        $this->registry = $registry;
    }

    /**
     * @return array
     */
    public function afterGetButtonData(\Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Back $button, $result)
    {
        $type = $this->context->getRequestParam('type');
        if($type == 'customercredit'){
            return [
                'label' => __('Back'),
                'on_click' => sprintf("location.href = '%s';", $button->getUrl('customercreditadmin/creditproduct/')),
                'class' => 'back',
                'sort_order' => 10
            ];
        }

        return $result;
    }
}
