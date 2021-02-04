/* eslint-disable*/
define([
    'jquery',
    'uiComponent',
    'ko',
    'PayPal_Subscription/js/model/address-builder'
], function (
    $,
    Component,
    ko,
    addressBuilder
) {
    'use strict';

    return Component.extend({

        defaults: {
            billingAddress: ko.observable(),
            shippingAddress: ko.observable()
        },

        /**
         * @return {exports}
         */
        initialize: function () {
            this._super();
            this.template = 'PayPal_Subscription/customer/addresses/default';

            // Set Billing & Shipping Address
            addressBuilder.billingAddress(this.createAddressFromString(this.billingAddress));
            addressBuilder.shippingAddress(this.createAddressFromString(this.shippingAddress));
        },

        /**
         *
         * @param address
         * @returns {*}
         */
        createAddressFromString: function (address) {
            return address.split(', ');
        },

        /**
         * Format address
         * @param element
         * @returns {*}
         */
        formatAddress: function(address) {

            // Remove Null fields
            var filteredAddress = address.filter(function (addressLine) {
                return addressLine != null;
            });

            // Return formatted addresses
            return filteredAddress.join(',<br>');
        },

        /**
         * Return Billing Address
         * @returns {*}
         */
        getBillingAddress: function () {
            return this.formatAddress(addressBuilder.billingAddress());
        },

        /**
         * Return Shipping Address
         * @returns {*}
         */
        getShippingAddress: function () {
            return this.formatAddress(addressBuilder.shippingAddress());
        }

    })
});