/**
 * PayPal Subscriptions
 */

define([
    'jquery',
    'mage/storage',
    'PayPal_Subscription/js/model/url-builder'
], function ($, storage, urlBuilder) {
    'use strict';

    return function (subscriptionId, addressId) {

        var url = urlBuilder.createUrl('/subscription/mine/billing/:subscriptionId/:addressId', {
            subscriptionId: subscriptionId,
            addressId: addressId
        });

        /**
         * Adds error message
         *
         * @param {String} message
         */

        return storage.put(
            url
        ).success(function (response) {

            // Return Response
            return response;
        })
    };
});
