/**
 * PayPal Subscriptions
 */

define([
    'jquery',
    'mage/storage',
    'PayPal_Subscription/js/model/url-builder'
], function ($, storage, urlBuilder) {
    'use strict';

    return function (response, subscriptionId) {


        var url = urlBuilder.createUrl('/subscription/mine/payment/paypal/:subscriptionId', {
            subscriptionId: subscriptionId
        });

        /**
         * Return response
         */
        return storage.put(
            url,
            JSON.stringify({nonce: response})
        ).success(function (response) {

            // Return Response
            return response;
        })
    };
});