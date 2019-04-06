/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/form',
    'Magento_Customer/js/customer-data',
    'Magento_Catalog/js/price-utils',
    'mage/translate'
], function ($, ko, Component, customerData, priceUtils, $t) {
    'use strict';

    return Component.extend({
        priceFormat : window.checkoutConfig?window.checkoutConfig.priceFormat:{"pattern":"$%s","precision":2,"requiredPrecision":2,"decimalSymbol":".","groupSymbol":",","groupLength":3,"integerRequired":false},
        defaults: {
            template: 'Magestore_Customercredit/current-credit-balance'
        },

        /**
         * Init
         */
        initialize: function () {
            var self = this;
            this.customer = customerData.get('customer');
            this.suggestCreditBalance = ko.computed(function(){
                var balance = priceUtils.formatPrice(self.customer().creditBalance, self.priceFormat);
                if (!self.customer().creditBalance){
                    return $t('Please buy credit to purchase');
                }
                return $t('You have got ') + balance + $t(' credits');
            });
            this._super();
        },
    });
});
