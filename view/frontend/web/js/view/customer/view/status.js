/* eslint-disable*/
define([
    'jquery',
    'uiComponent',
    'ko',
    'mage/translate',
    'PayPal_Subscription/js/action/customer/view/set-subscription-status'
], function (
    $,
    Component,
    ko,
    $t,
    setSubscriptionStatus
) {
    'use strict';

    return Component.extend({

        defaults: {
            subscriptionId: ko.observable(),
            options: ko.observable(),
            status: ko.observable(),
            message: ko.observable(),
            messageClass: ko.observable()
        },

        /**
         * @return {exports}
         */
        initialize: function () {
            this._super();
            this.template = 'PayPal_Subscription/customer/view/status';
        },

        /**
         * Update Status
         * @param item
         * @param event
         */
        updateStatus: function (item, event) {

            var that = this,
                updatedStatus = event.target.value;

            if (event.target.value == 3) {
                if (!confirm('Are you sure you want to cancel your subscription?')) {
                    $('#select-status option[value=1]').attr('selected','selected');
                    return;
                } else {
                    $('#select-status').attr('disabled', '1');
                }
            }

            // Clear Message
            that.clearMessage();

            // Start loader
            $('body').trigger('processStart');

            $.ajax({
                method: "PUT",
                url: '/rest/V1/subscription/mine/status/'+ this.subscriptionId +'/' + updatedStatus
            })
                .done(function(response) {
                    $('body').trigger('processStop');

                    // Add Success Messaging
                    that.message($t('The status successfully updated.'));
                    that.messageClass('message success');
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    $('body').trigger('processStop');
                    // Add error messaging
                    that.message($t('Unable to update the status, please try again.'));
                    that.messageClass('message error');
                });
        },

        /**
         * Set status options on the select
         * @returns {Array}
         */
        setStatusOptions: function () {

            var that = this,
                options = [];

            // Foreach option in frequency_options then set new option
            $.each(that.options, function (key, value) {

                var newOption = {
                    value: key,
                    label: value
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
