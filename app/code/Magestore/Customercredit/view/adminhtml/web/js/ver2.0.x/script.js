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
require([
    'jquery'
], function ($) {
    'use strict';

    $(document).ready(function(){
        $('div#product_info_tabs-basic li[data-ui-id="product-tabs-tab-item-credit-prices-settings"]').remove();
        $('div#product_info_tabs_credit-prices-settings_content').remove();
    })

});