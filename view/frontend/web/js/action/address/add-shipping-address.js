/**
 * PayPal Subscriptions
 */

define([
    'jquery',
    'mage/storage',
    'PayPal_Subscription/js/model/url-builder'
], function ($, storage, urlBuilder) {
    'use strict';

    return function (subscriptionId, address) {

        var url = urlBuilder.createUrl('/subscription/mine/shipping/new/:subscriptionId', {
            subscriptionId: subscriptionId
        });

        /**
         * Adds error message
         *
         * @param {String} message
         */

        return storage.put(
            url,
            JSON.stringify(address)
        ).success(function (response) {

            // Return Response
            return response;
        })
    };
});
