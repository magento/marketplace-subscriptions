/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'ko',
    './subscriptions'
], function (ko, subscriptionProvider) {
    'use strict';

    var activeSubscriptions = null;
    var cancelledSubscriptions = null;

    return {
        getActiveSubscriptions: function () {
            if (activeSubscriptions === null) {
                activeSubscriptions = ko.observableArray(subscriptionProvider.getActiveSubscriptions());
            }
            return activeSubscriptions;
        },

        getCancelledSubscriptions: function () {
            if (cancelledSubscriptions === null) {
                cancelledSubscriptions = ko.observableArray(subscriptionProvider.getCancelledSubscriptions());
            }
            return cancelledSubscriptions;
        },

        setActiveSubscriptions: function (newActiveSubscriptions) {
            activeSubscriptions(newActiveSubscriptions);
        },

        setCancelledSubscriptions: function (newCancelledSubscriptions) {
            cancelledSubscriptions(newCancelledSubscriptions);
        }
    };
});
