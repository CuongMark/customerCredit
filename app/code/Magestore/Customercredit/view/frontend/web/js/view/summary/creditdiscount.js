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
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/action/get-payment-information'
    ],
    function ($, Component, quote, getTotalsAction, getPaymentInformationAction) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magestore_Customercredit/summary/creditdiscount'
            },
            totals: quote.getTotals(),
            initialize: function () {
                this._super();
                getPaymentInformationAction();
                // quote.getCalculatedTotal();
                getTotalsAction([]);
                return this;
            },
            isDisplayed: function () {
                return this.isFullMode() && this.getPureValue() != 0;
            },
            getPureValue: function () {
                var price = 0;
                if (this.totals() && this.totals().total_segments) {
                    this.totals().total_segments.forEach(function (item) {

                        if (item.code == 'creditdiscount') {
                            price = parseFloat(item.value);
                        }

                        if (item.code == 'creditdiscountaftertax') {
                            price = parseFloat(item.value);
                        }
                    });
                }

                var base_balance = $('#blc-base').val();
                if(typeof base_balance != 'undefined' && base_balance > 0){
                    var input_enter_credit = $('input[name="customer_credit"]');
                    var current_balance = parseFloat(base_balance) - Math.abs(parseFloat(price));
                    var credit_label = $("#customer-credit-form-code").find(".checkout-cart-credit-amount").find("span.price");
                    input_enter_credit.val(Math.abs(parseFloat(price)));
                    credit_label.text(this.getFormattedPrice(current_balance));
                }
                if(typeof window.customercreditInfo != 'undefined'){
                    var base_balance_checkout_page = window.customercreditInfo.credit_balance_basenumber;
                    if(base_balance_checkout_page > 0){
                        var input_enter_credit_checkout_page = $('.form-discount-credit input#discount-credit');
                        var current_balance_checkout_page = parseFloat(base_balance_checkout_page) - Math.abs(parseFloat(price));
                        var credit_label_checkout_page = $(".form-discount-credit").find("span#credit_balance");
                        input_enter_credit_checkout_page.val(Math.abs(parseFloat(price)));
                        credit_label_checkout_page.text(this.getFormattedPrice(current_balance_checkout_page));
                    }
                }

                return price;
            },
            getValue: function () {
                return this.getFormattedPrice(this.getPureValue());
            }
        });
    }
);
