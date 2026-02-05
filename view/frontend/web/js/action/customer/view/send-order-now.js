define([
    'jquery',
    'mage/storage',
    'PayPal_Subscription/js/model/url-builder'
], function ($, storage, urlBuilder) {
    'use strict';

    return function (subscriptionId, qty) {
        var url = urlBuilder.createUrl('/subscription/mine/sendOrderNow/:subscriptionId', {
            subscriptionId: subscriptionId
        });

        /**
         * Adds error message
         *
         * @param {String} message
         */

        return storage.put(
            url,
            {},
            false
        ).done(function (response) {
            // Return Response
            return response;
        });
    };
});
