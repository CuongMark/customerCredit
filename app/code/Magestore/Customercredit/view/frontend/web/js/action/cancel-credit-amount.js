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

/**
 * Customer store credit(balance) application
 */
/*global define,alert*/
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/error-processor',
        'Magento_SalesRule/js/model/payment/discount-messages',
        'mage/storage',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/totals',
        'mage/translate',
        'Magestore_Customercredit/js/action/reload-shipping-method'
    ],
    function ($, quote, urlManager, errorProcessor, messageContainer, storage, getPaymentInformationAction, totals, $t, reloadShippingMethod) {
        'use strict';

        return function (amount, isApplied, isLoading) {
            var credit_amount = 0;
            var url = 'customercredit/checkout/amountpost/credit_amount/' + credit_amount;

            return storage.put(
                url,
                false
            ).done(
                function (response) {
                    var res = JSON.parse(response);
                    amount(res.credit_amount);
                    $('#discount-credit').val(0);
                    $('#credit_balance').text(res.credit_balance);
                    var deferred = $.Deferred();
                    totals.isLoading(true);
                    getPaymentInformationAction(deferred);
                    reloadShippingMethod();
                    $.when(deferred).done(function () {
                        isApplied(false);
                        totals.isLoading(false);
                    });
                }
            ).fail(
                function (response) {
                    totals.isLoading(false);
                    errorProcessor.process(response, messageContainer);
                }
            ).always(
                function () {
                    isLoading(false);
                    totals.isLoading(false);
                }
            );
        };
    }
);
