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
    'mage/translate'
], function ($) {
    'use strict';

    $.widget('magestore.productCredit', {

        _create: function () {
            var self = this;

            Event.observe(window, 'load', function () {
                if ($$('.product-shop')[0] && $$('.product-shop')[0].down('.price-info'))
                    $$('.product-shop')[0].down('.price-info').setStyle({'display': 'none'});
            });
            if(this.options.type == 'static'){
                this.typeStatic();
            }
            if(this.options.type == 'range'){
                this.typeRange();
            }
            if(this.options.type == 'dropdown'){
                this.typeDropdown();
            }
            if(this.options.type == 'any'){
                this.typeAny();
            }

            $('#credit_amount_dropdown').on('change', function() {
                self.typeDropdown();
            });
            $('.credit_type_any_amount').on('change', function() {
                self.typeAny();
            });
            this.credit_amount_range.on('change', function() {
                self.validateInputRange(this);
            });
            this.credit_amount_range.on('keyup keypress', function(e) {
                var keyCode = e.keyCode || e.which;
                if (keyCode === 13) {
                    e.preventDefault();
                    self.validateInputRange(this);
                    return false;
                }
            });
        },
        credit_amount_range: $('#credit_amount_range'),
        product_price: $('#storecredit-product-price span.price'),
        typeStatic: function(){
            $('#credit_price_amount').val(this.options.price);
            this.product_price.text(formatCurrency(this.options.price, this.options.priceFormatJs));
        },
        typeDropdown: function(){
            var value = $('#credit_amount_dropdown').val();
            $('#credit_price_amount').val(this.options.prices[value]);
            this.product_price.text(formatCurrency(this.options.prices[value], this.options.priceFormatJs));
        },
        typeAny: function(){
            var rate = this.options.rate;
            var amount = this.options.amount;
            var price = rate * amount;
            $('.credit_type_any_value').text(formatCurrency(price, this.options.priceFormatJs));

        },
        typeRange: function(){
            var value = this.credit_amount_range.val();
            if(!value){
                value = this.options.credit_amount_min;
                this.credit_amount_range.val(value);
            }
            var rate = this.options.storecredit_rate;
            var rate_tax = this.options.rate_tax;
            var price_amount = value * rate * rate_tax;

            $('#credit_price_amount').val(price_amount);
            this.product_price.text(formatCurrency(price_amount, this.options.priceFormatJs));

        },
        validateInputRange: function (el){
            var credit_amount_min = this.options.credit_amount_min;
            var credit_amount_max = this.options.credit_amount_max;
            var validateValue = el.value.replace(/\s/g, '');

            var priceFormat = this.options.priceFormat;
            var price = priceFormat.match('1.000.00')[0];
            var result = [];

            result['decimalSymbol'] = price.charAt(5);
            result['groupSymbol'] = price.charAt(1);

            if (validateValue.search(result.groupSymbol) != -1)
                validateValue = validateValue.replace(result.groupSymbol, '');
            el.value = validateValue.replace(result.decimalSymbol, '.');

            if(isNaN(el.value)){
                el.value = credit_amount_min;
            }
            //$('#credit_amount_range').val(el.value);

            if (el.value < credit_amount_min)
                el.value = credit_amount_min;
            if (el.value > credit_amount_max)
                el.value = credit_amount_max;

            this.typeRange();
        }
    });

    return $.magestore.productCredit;
});