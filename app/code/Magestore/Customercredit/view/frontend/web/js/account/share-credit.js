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
    'mage/mage',
    'mage/translate'
], function ($) {
    'use strict';

    $.widget('magestore.shareCredit', {

        _create: function () {

            var self = this;
            var dataForm = $('#customercredit-form-content');
            dataForm.mage('validation', {});

            $('#customercredit_value_input').on('change keyup keypress', function(e) {
                if(e.type == 'change') {
                    self.checkValidNumber(this);
                    return true;
                }
                if(e.type == 'keyup' || e.type == 'keypress'){
                    var keyCode = e.keyCode || e.which;
                    if (keyCode === 13) {
                        e.preventDefault();
                        self.checkValidNumber(this);
                        return false;
                    }
                }
            });

            $('#customercredit_email_input').on('change keyup keypress', function(e) {
                var my_email = self.options.checkEmailExisted.my_email;
                var url = self.options.checkEmailExisted.url;

                if(e.type == 'change'){
                    self.checkEmailExisted(my_email, url);
                    return true;
                }
                if(e.type == 'keyup' || e.type == 'keypress'){
                    var keyCode = e.keyCode || e.which;
                    if (keyCode === 13) {
                        e.preventDefault();
                        self.checkEmailExisted(my_email, url);
                        return false;
                    }
                }
            });

            $('#customercredit_send_credit_button').on('click', function(e) {
                var hide = self.options.changeSendStatus.hide;
                var my_email = self.options.changeSendStatus.my_email;
                var url = self.options.changeSendStatus.url;
                var verify_enable = self.options.changeSendStatus.verify_enable
                if(verify_enable == 1){
                    self.changeSendStatus(hide, my_email, url);
                }else{
                    $('#verify-code-form').submit();
                }
            });
        },
        checkValidNumber: function(){
            var current_value = this.options.checkValidNumber.balance;
            var value = $('#customercredit_value_input').val();
            if(value - current_value > 0) {
                $('#advice-validate-max-number').show();
                $('#customercredit_send_credit_button').attr('type', 'button');
            } else {
                $('#advice-validate-max-number').hide();
            }
        },
        checkEmailExisted: function(my_email, url) {
            $('#advice-your-email').hide();
            $('#invalid-email').hide();
            var email = $('#customercredit_email_input').val();
            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

            if (re.test(email)) {
                $('#customercredit_show_alert').hide();
                $('#customercredit_show_success').hide();

                if (my_email != email) {
                    $('#customercredit_show_loading').show();
                    url += "checkemail/email/" + email;
                    $.get(url)
                        .done(function( response ) {
                            $('#customercredit_show_loading').hide();
                            if (JSON.parse(response)) {
                                var res = JSON.parse(response);
                                if (res.existed == 1) {
                                    $('#customercredit_show_success').show();
                                }
                                else {
                                    $('#customercredit_show_alert').show();
                                }
                            }
                        });
                } else {
                    $('#advice-your-email').show();
                }
            }else{
                $('#invalid-email').show();
            }
        },
        changeSendStatus: function (hide, my_email, url) {
            var email = $('#customercredit_email_input').val();
            var value = $('#customercredit_value_input').val();
            var message = $('#customercredit_message_textarea').val();
            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

            if (re.test(email) && this.checkValue(hide, value) && email != my_email) {
                $('#customercredit_show_loading_p').show();
                $('#customercredit_send_credit_button').hide();
                $('#customercredit_cancel_button').hide();

                $.post( url, {'email': email, 'value': value, 'message': message})
                    .done(function( response ) {
                        $('#customercredit_show_loading_p').hide();
                        $('#customercredit_send_credit_button').show();
                        $('#customercredit_cancel_button').show();
                        if (JSON.parse(response)) {
                            var res = JSON.parse(response);
                            if (res.success == 1) {
                                $('#customercredit_show_loading_p').hide();
                                $('#customercredit-form-content').submit();
                            }
                        }
                        $('#customercredit_send_credit_button').show();
                        $('#customercredit_cancel_button').show();
                    });
            }
        },
        checkValue: function (hide, val) {
            if (val - hide > 0 && val != null) {
                $('#advice-validate-max-number').show();
                return false;
            }
            else {
                $('#advice-validate-max-number').hide();
                return true;
            }
        }
    });

    return $.magestore.shareCredit;
});