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
            addresses: ko.observable(),
            message: ko.observable()
        },

        /**
         * @return {exports}
         */
        initialize: function () {
            this._super();
            this.template = 'PayPal_Subscription/customer/addresses/existing-addresses';

            // Set Existing Addresses
            addressBuilder.existingAddresses(this.addresses);
        },

        /**
         * Set Billing address
         * @param parent
         * @param addressId
         * @param subscriptionId
         */
        setBillingAddress: function (addressId, subscriptionId) {

            var that = this;

            // Clear Message
            this.message();

            $('body').trigger('processStart');

            setBillingAddress(subscriptionId, addressId).success(function (response) {

                $('body').trigger('processStop');
                var address = Object.values(JSON.parse(response.billing_address));
                addressBuilder.billingAddress(address);

            }).error(function () {

                $('body').trigger('processStop');
                that.message($t('Unable to update billing address, please try again.'));
            });
        },

        /**
         * Set Shipping Address
         * @param addressId
         * @param subscriptionId
         */
        setShippingAddress: function(addressId, subscriptionId) {

            var that = this;

            // Clear Message
            this.message();

            $('body').trigger('processStart');

            setShippingAddress(subscriptionId, addressId).success(function (response) {

                $('body').trigger('processStop');
                var address  = Object.values(JSON.parse(response.shipping_address));
                addressBuilder.shippingAddress(address);

            }).error(function () {

                $('body').trigger('processStop');
                that.message($t('Unable to update shipping address, please try again.'));
            });
        },

        /**
         * Get Existing Addresses
         */
        getExistingAddresses: function () {
            return addressBuilder.existingAddresses();
        },

        /**
         * Format address
         * @param element
         * @returns {*}
         */
        formatAddress: function(address) {

            // Remove Null fields
            var filteredAddress = address.filter(function (addressLine) {
                return addressLine !== null;
            });

            // Return formatted addresses
            return filteredAddress.join(',<br>');
        }
    })
});