define([
    'jquery',
    'ko',
    './subscription'
], function ($, ko, Subscription) {
    'use strict';

    return {

        /**
         * @return {Array}
         */
        getActiveSubscriptions: function () {
            var items = [],
                subscriptionsData = window.subscriptionsData;
            if (Object.keys(subscriptionsData).length) {
                $.each(subscriptionsData.subscriptions, function (key, item) {
                    if (item.status == 1) {
                        items.push(new Subscription(item));
                    }
                });
            }
            return items;
        },

        /**
         * @return {Array}
         */
        getCancelledSubscriptions: function () {
            var items = [],
                subscriptionsData = window.subscriptionsData;
            if (Object.keys(subscriptionsData).length) {
                $.each(subscriptionsData.subscriptions, function (key, item) {
                    if (item.status != 1) {
                        items.push(new Subscription(item));
                    }
                });
            }
            return items;
        },
    };
});
