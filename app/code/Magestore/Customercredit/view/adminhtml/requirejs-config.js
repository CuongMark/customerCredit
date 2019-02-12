/*
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

var config = {
    map: {
        '*': {
            'customerCreditForm': 'Magestore_Customercredit/js/order/customercredit',
            'script_colorpicker': 'Magestore_Customercredit/js/script_colorpicker'
        }
    },
    paths: {
        'customerCreditForm': 'Magestore_Customercredit/js/order/customercredit',
        'script_colorpicker': 'Magestore_Customercredit/js/script_colorpicker'
    },
    shim: {
        // 'script_colorpicker': {
        //     deps: ['jquery', 'Magestore_Customercredit/js/jquery/colorpicker']
        // }
    }
};
