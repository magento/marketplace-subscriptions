/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'mage/translate',
    'PayPal_Subscription/js/action/customer/view/set-subscription-interval',
    'PayPal_Subscription/js/action/customer/view/set-subscription-item-qty',
    'PayPal_Subscription/js/model/date-formatter',
    'Magento_Customer/js/customer-data'
], function ($, ko, Component, $t, setSubscriptionInterval, setSubscriptionItemQty, dateFormatter, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'PayPal_Subscription/customer/list/component/product-controls',
            qty: ko.observable(0)
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super();
            this.qty = ko.observable(parseInt(this.subscription().item.qty()));
            this.qtyInputId = $t('qty_%1').replace('%1', this.subscription().item.id);
            this.availableFrequencies = this.subscription().availableFrequencies;
            this.selectedFrequencyOption = ko.observable(this.subscription().frequency());
            this.selectedFrequencyOption.subscribe(function (newValue) {
                this.updateFrequency(newValue);
            }.bind(this));
            this.qtyInputLabel = $t('Qty');
            this.reduceQuantityLabel = $t('Reduce quantity by one');
            this.increaseQuantityLabel = $t('Increase quantity by one');
            this.frequencyOptionSelectId = $t('paypal-subscription-frequency-option-%1').replace('%1', this.subscription().subscriptionId);
            return this;
        },

        increaseQty: function () {
            var previousQty = this.qty();
            this.qty(previousQty + 1);
            this.updateQty();
        },

        decreaseQty: function () {
            var previousQty = this.qty();
            if (previousQty > 0) {
                this.qty(previousQty - 1);
            }
            this.updateQty();
        },

        updateQty: function () {
            $("body").trigger('processStart');
            setSubscriptionItemQty(
                this.subscription().item.id,
                this.qty()
            ).done(function () {
                customerData.set('messages', {
                    messages: [{
                        text: 'Your subscription quantity has been updated.',
                        type: 'success'
                    }]
                });
            }).fail(function () {
                customerData.set('messages', {
                    messages: [{
                        text: 'Unable to update qty, please try again.',
                        type: 'error'
                    }]
                });
            }).always(function () {
                $("body").trigger('processStop');
            });
        },

        updateFrequency: function(newInterval) {
            var that = this;
            $("body").trigger('processStart');
            setSubscriptionInterval(
                this.subscription().subscriptionId,
                newInterval
            ).done(function (response) {
                var nextReleaseDate = dateFormatter(response['next_release_date']);
                that.subscription().nextReleaseDate(nextReleaseDate);
                customerData.set('messages', {
                    messages: [{
                        text: 'Your subscription frequency has been updated.',
                        type: 'success'
                    }]
                });
            }).fail(function () {
                customerData.set('messages', {
                    messages: [{
                        text: 'Unable to update your subscription frequency. Please try again.',
                        type: 'error'
                    }]
                });
            }).always(function () {
                $("body").trigger('processStop');
            });
        }
    });
});
