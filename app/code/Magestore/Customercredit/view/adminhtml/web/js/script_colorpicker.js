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
    'Magestore_Customercredit/js/jquery/colorpicker'
], function ($) {
    'use strict';

    $.widget('magestore.script_colorpicker', {
        _create: function () {
            var self = this;
            var $el = $(this.options.element);
            $el.css("background-color", this.options.value);

            // Attach the color picker
            $el.ColorPicker({
                color: "'"+this.options.value+"'",
                onChange: function (hsb, hex, rgb) {
                    $el.css("background-color", "#" + hex).val("#" + hex);
                }
            });
        }
    });
    return $.magestore.script_colorpicker;
});