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

/*jshint browser:true*/
/*global alert*/
define([
    'jquery',
    'jquery/ui',
    'prototype',
    'mage/translate',
    "mage/mage",
    'jquery/validate'
], function ($) {
    'use strict';

    $.widget('magestore.customerCreditForm', {

        _create: function () {
            var self = this;
            // console.log(this.options);
            Event.observe(window, 'load', function () {
                if (order) {
                    console.log('reload order');
                    // order.loadArea(['data'], true, {});
                }
            });

            if($("#product_composite_configure_input_qty").val() == ""){
                $("#product_composite_configure_input_qty").val(1);
            }

            $('#btn-apply-credit').on('click', function() {
                self.applyCreditForm();
            });

            $('#btn-cancel-credit').on('click', function() {
                self.cancelCreditForm();
            });

            $('#customercredit_input').on('change', function() {
                self.checkoutCartCreditAmount();
            });
            $('#amount_dropdown').on('change', function() {
                self.setAmountDropDown();
            });
            $('#amount_range').on('change', function() {
                self.validateInputRange();
            });

            if($('#send_friend').is(":checked")){
                $('#customercredit-receiver').show();
            }
            $('#send_friend').on('click', function() {
                self.sendCreditToFriend(this);
            });

        },
        cancelCreditForm: function () {
            var url = this.options.applyCreditForm.url;
            var data = {};
            var allShipping = $('input[name="order[shipping_method]"]');
            for (var index = 0; index < allShipping.length; ++index) {
                var shippingElement = allShipping[index];
                if (shippingElement.checked) {
                    data['order[shipping_method]'] = shippingElement.value;
                }
            }
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: {'credit_value': 0},
                showLoader: true //use for display loader
            }).done(function (response) {
                if (order) {
                    order.loadArea(['items', 'shipping_method', 'totals', 'billing_method'], true, data);
                }
            });
        },
        applyCreditForm: function () {
            var url = this.options.applyCreditForm.url;
            var current_credit = this.options.applyCreditForm.current_credit;

            var credit_value = $('#customercredit_input').val();
            var data = {};
            var allShipping = $('input[name="order[shipping_method]"]');

            if(parseFloat(credit_value) <= parseFloat(current_credit)) {
                for (var index = 0; index < allShipping.length; ++index) {
                    var shippingElement = allShipping[index];
                    if (shippingElement.checked) {
                        data['order[shipping_method]'] = shippingElement.value;
                    }
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: {'credit_value': credit_value},
                    showLoader: true //use for display loader
                }).done(function (response) {
                    if (order) {
                        order.loadArea(['items', 'shipping_method', 'totals', 'billing_method'], true, data);
                    }
                });
            }else{
                $('#advice-validate-number-customer_credit').show();
                $('#customercredit_input').val(0);
                $('#btn-apply-credit').prop('disabled', true);
            }
        },
        checkoutCartCreditAmount: function () {

            var current_credit = this.options.applyCreditForm.current_credit;
            var credit_value = $('#customercredit_input').val();

            $('#advice-validate-number-customer_credit').hide();

            if(isNaN(credit_value)){
                $('#advice-validate-number-customer_credit').show();
                $('#customercredit_input').val(0);
                $('#btn-apply-credit').prop('disabled', true);
            }else if (parseFloat(credit_value) < 0) {
                $('#advice-validate-number-customer_credit').show();
                $('#customercredit_input').val(0);
                $('#btn-apply-credit').prop('disabled', true);
            }else if(parseFloat(credit_value) > parseFloat(current_credit)){
                $('#advice-validate-number-customer_credit').show();
                $('#customercredit_input').val(0);
                $('#btn-apply-credit').prop('disabled', true);
            } else {
                $('#btn-apply-credit').prop('disabled', false);
            }
        },
        setAmountDropDown: function() {
            var result = [];
            var priceFormat = this.options.setAmountDropDown.priceFormat;
            var creditRate = this.options.setAmountDropDown.creditRate;
            var price = priceFormat.match('1.000.00')[0];
            var validateValue = $('#amount_dropdown').val().replace(/\s/g, '');
            result['decimalSymbol'] = price.charAt(5);
            result['groupSymbol'] = price.charAt(1);
            if (validateValue.search(result.groupSymbol) != -1)
                validateValue = validateValue.replace(result.groupSymbol, '');

            $('#amount_dropdown').val(validateValue.replace(result.decimalSymbol, '.'));
            price = $('#amount_dropdown').val() * creditRate;

            var qty = $("#product_composite_configure_input_qty").val();
            var input_hidden = '<input id="hidden_price" type="hidden" value="'+qty+'" price="'+price+'" qtyid="product_composite_configure_input_qty">';
            $("#hidden_price").remove();
            $("#catalog_product_composite_configure_fields_customercredit").append(input_hidden);
        },
        validateInputRange: function() {
            var result = [];
            var priceFormat = this.options.validateInputRange.priceFormat;
            var creditRate = this.options.validateInputRange.creditRate;

            var price = priceFormat.match('1.000.00')[0];
            result['decimalSymbol'] = price.charAt(5);
            result['groupSymbol'] = price.charAt(1);
            var amount_min = this.options.validateInputRange.from;
            var amount_max = this.options.validateInputRange.to;

            var validateValue = $("#amount_range").val().replace(/\s/g, '');
            if (validateValue.search(result.groupSymbol) != -1)
                validateValue = validateValue.replace(result.groupSymbol, '');

            $("#amount_range").val(validateValue.replace(result.decimalSymbol, '.'));

            if ($("#amount_range").val() < amount_min){
                $("#amount_range").val(amount_min);
            }
            if ($("#amount_range").val() > amount_max){
                $("#amount_range").val(amount_max);
            }
            price = $("#amount_range").val() * creditRate;
            jQuery("#product_composite_configure_input_qty").attr('price', price);

        },
        sendCreditToFriend: function(el) {
            if (!el)
                return;
            var receivercredit = $('#customercredit-receiver');
            if (el.checked) {
                if (receivercredit) {
                    receivercredit.show();
                    if ($('#recipient_name'))
                        $('#recipient_name').addClass('required-entry');
                    if ($('#recipient_email')) {
                        $('#recipient_email').addClass('required-entry');
                        $('#recipient_email').addClass('validate-email');
                        $('#recipient_email').addClass('validate-same-email');
                    }
                }
            } else {
                if (receivercredit) {
                    receivercredit.hide();
                    if ($('#recipient_name'))
                        $('#recipient_name').removeClass('required-entry');
                    if ($('#recipient_email')) {
                        $('#recipient_email').removeClass('required-entry');
                        $('#recipient_email').removeClass('validate-email');
                        $('#recipient_email').removeClass('validate-same-email');
                    }
                }
            }
        }

    });
    return $.magestore.customerCreditForm;
});