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
            'productCredit': 'Magestore_Customercredit/js/product-credit',
            'sendCreditToFriend': 'Magestore_Customercredit/js/send-to-friend',
            'shareCredit': 'Magestore_Customercredit/js/account/share-credit'
        }
    },
    paths: {
        'productCredit': 'Magestore_Customercredit/js/product-credit',
        'sendCreditToFriend': 'Magestore_Customercredit/js/send-to-friend',
        'shareCredit': 'Magestore_Customercredit/js/account/share-credit'
    },
    shim: {
        // 'productCredit': {
        //     deps: ['jquery']
        // }
    }
};
