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

namespace Magestore\Customercredit\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use Magestore\Customercredit\Model\Source\Storecredittype;

class ProductSaveBefore implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $_request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $type = $this->_request->getParam('type');
        if($type == 'customercredit'){
            $params = $this->_request->getParam('product');
            $credit_type = ( isset($params["storecredit_type"]) ? $params["storecredit_type"] : Storecredittype::CREDIT_TYPE_NONE );

            switch ($credit_type) {
                case Storecredittype::CREDIT_TYPE_FIX:
                    $this->validFixValue($params);
                    break;
                case Storecredittype::CREDIT_TYPE_RANGE:
                    $this->validRangeValue($params);
                    break;
                case Storecredittype::CREDIT_TYPE_DROPDOWN:
                    $this->validDropdownValue($params);
                    break;
                case Storecredittype::CREDIT_TYPE_NONE:
                    $this->validTypeValue($params);
                    break;
                default:
            }
        }
        return $this;
    }

    protected function validTypeValue($params) {
        if(isset($params["storecredit_value"])) {
            $val = $this->format($params['storecredit_type']);
            if ($val == 0) {
                throw new \Exception(__("Please choose type of store credit value."));
            }
        }
    }

    protected function validFixValue($params) {
        $val = $this->format($params['storecredit_value']);
        if(!is_numeric($val)){
            throw new \Exception(__("Store Credit Value is wrong, please try again."));
        }
    }
    protected function validRangeValue($params) {
        $from = $this->format($params['storecredit_from']);
        $to = $this->format($params['storecredit_to']);
        if(!is_numeric($from) || !is_numeric($to)){
            throw new \Exception(__("Store Credit Values is wrong, please try again."));
        }
        if(floatval($from) > floatval($to)){
            throw new \Exception(__("Minimum Credit value must be lower than maximum Credit value."));
        }
    }

    protected function validDropdownValue($params) {
        $value = $params['storecredit_dropdown'];
        $values = explode(',',$value);
        if(count($values) > 0){
            foreach ($values as $data){
                if(!is_numeric($data)){
                    throw new \Exception(__("Store Credit Values is wrong, please try again."));
                }
            }
        }else{
            if(!is_numeric($value)){
                throw new \Exception(__("Store Credit Values is wrong, please try again."));
            }
        }
    }
    /*
     * Format to base number
     *
     * Ex: 1,000.00 to 1000.00 @@TODO add code for dot or comma
     * */
    protected function format($num){
        $result = str_replace(",", "", $num);
        return $result;
    }

}
