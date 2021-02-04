/**
 * PayPal Subscriptions
 */

define([
    'jquery',
    'mage/storage',
    'PayPal_Subscription/js/model/url-builder'
], function ($, storage, urlBuilder) {
    'use strict';

    return function (subscriptionId, frequency) {

        var url = urlBuilder.createUrl('/subscription/mine/frequency/:subscriptionId/:qty', {
            subscriptionId: subscriptionId,
            qty: frequency
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
