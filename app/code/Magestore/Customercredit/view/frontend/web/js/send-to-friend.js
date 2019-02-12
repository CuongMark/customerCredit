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
    'mage/translate',
    "mage/mage",
    'jquery/validate'
], function ($) {
    'use strict';

    $.widget('magestore.sendCreditToFriend', {

        _create: function () {
            var self = this;

            $('#send_friend').on('change', function() {
                var receiver = $('#customercredit-receiver');
                if (this.checked) {
                    if (receiver)
                        receiver.show();
                } else {
                    if (receiver)
                        receiver.hide();
                }
            });

            $.validator.addMethod(
                'validate-same-email', function (value) {
                    return (value !== '<?php echo $block->getCurrentCustomerEmail() ?>');
                }, $.mage.__('You cannot send credit to yourself.')
            );
        }
    });

    return $.magestore.sendCreditToFriend;
});