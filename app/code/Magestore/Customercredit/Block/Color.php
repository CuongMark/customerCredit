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

class Color extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $html = $element->getElementHtml();
        $value = $element->getData('value');

        $html .= '<script type="text/x-magento-init">
        {
            "#'.$element->getHtmlId().'": {
                "script_colorpicker": {
                    "element": "#'. $element->getHtmlId() .'",
                    "value": "'. $value .'"
                }
            }
        }
         </script>';
        return $html;
    }

    public function greeting($name)
    {
        return 'Hello '.$this->escapeHtml($name);
    }
}