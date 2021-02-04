/**
 * PayPal Subscriptions
 */

define([
    'jquery',
    'mage/storage',
    'PayPal_Subscription/js/model/url-builder'
], function ($, storage, urlBuilder) {
    'use strict';

    return function (subscriptionId, publicHash) {

        var url = urlBuilder.createUrl('/subscription/mine/payment/:subscriptionId/:paymentPublicHash', {
            subscriptionId: subscriptionId,
            paymentPublicHash: publicHash
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
