/* eslint-disable*/
define([
    'jquery',
    'uiComponent',
    'ko',
    'mage/translate',
    'PayPal_Subscription/js/action/customer/view/set-subscription-interval'
], function (
    $,
    Component,
    ko,
    $t,
    setSubscriptionInterval
) {
    'use strict';

    return Component.extend({

        defaults: {
            subscriptionId: ko.observable(),
            options: ko.observable(),
            value: ko.observable(),
            message: ko.observable(),
            messageClass: ko.observable()
        },

        /**
         * @return {exports}
         */
        initialize: function () {
            this._super();
            this.template = 'PayPal_Subscription/customer/view/details';
        },

        /**
         * Update Interval
         * @param item
         * @param event
         */
        updateInterval: function (item, event) {

            var that = this,
                updatedFrequency = event.target.value;

            // Clear Message
            that.clearMessage();

            // Start loader
            $('body').trigger('processStart');

            // Set item frequency
            setSubscriptionInterval(this.subscriptionId, updatedFrequency).error(function () {

                // Stop loader
                $('body').trigger('processStop');

                // Add error messaging
                that.message($t('Unable to update frequency, please try again.'));
                that.messageClass('message error');
            }).success(function () {

                // Stop loader
                $('body').trigger('processStop');

                // Add Success Messaging
                that.message($t('The delivery has been updated.'));
                that.messageClass('message success');
            });
        },

        /**
         * Set intervals on the select
         * @returns {Array}
         */
        setIntervalValues: function () {

            var that = this,
                options = [];

            // Foreach option in frequency_options then set new option
            $.each(that.options, function (key, value) {

                var newOption = {
                    value: value.interval,
                    label: value.name
                };

                options.push(newOption);

            });

            return options;
        },

        /**
         * Clear Error / Success Message
         */
        clearMessage: function () {
            this.message('');
            this.messageClass(false);
        }
    })
});