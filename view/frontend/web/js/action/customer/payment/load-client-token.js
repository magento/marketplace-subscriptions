/**
 * PayPal Subscriptions
 */

define([
    'jquery',
    'mage/storage',
    'PayPal_Subscription/js/model/url-builder'
], function ($, storage, urlBuilder) {
    'use strict';

    return function () {
        var url = urlBuilder.createUrl('/subscription/braintree/token/client', {});

        /**
         * Adds error message
         *
         * @param {String} message
         */

        return storage.get(
            url
        ).success(function (response) {

            // Return Response
            return response;
        })
    };
});