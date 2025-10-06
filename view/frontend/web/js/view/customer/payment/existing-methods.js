/* eslint-disable*/
define([
    'jquery',
    'uiComponent',
    'ko',
    'mage/translate',
    'PayPal_Subscription/js/action/address/set-billing-address',
    'PayPal_Subscription/js/action/address/set-shipping-address',
    'PayPal_Subscription/js/model/address-builder'
], function (
    $,
    Component,
    ko,
    $t,
    setBillingAddress,
    setShippingAddress,
    addressBuilder
) {
    'use strict';

    return Component.extend({

        defaults: {
            message: ko.observable(),
            template: 'PayPal_Subscription/customer/payment/existing-method-select'
        },

        /**
         * @return {exports}
         */
        initialize: function () {
            this._super();
        },

        /**
         * Get Existing Addresses
         */
        getExistingMethods: function () {
            return window.subscriptionPaymentMethods;
        }
    })
});
