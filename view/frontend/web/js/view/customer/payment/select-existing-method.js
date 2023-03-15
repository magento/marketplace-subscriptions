/* eslint-disable*/
define([
    'jquery',
    'uiComponent',
    'ko',
    'PayPal_Subscription/js/model/url-builder'
], function (
    $,
    Component,
    ko,
    urlBuilder
) {
    'use strict';

    return Component.extend({

        defaults: {
            subscriptionId: ko.observable(),
            methods: ko.observable(),
            selectedMethod: ko.observable()
        },

        /**
         * @return {exports}
         */
        initialize: function () {
            this._super();
            this.template = 'PayPal_Subscription/customer/payment/select-existing-method';

            var that = this;

            // Set Current Method
            this.methods.forEach(function (item) {
                if (item['is_current_method']) {
                    that.setCurrentMethod(item.id);
                }
            });
        },

        /**
         *
         * @param publicHash
         */
        updatePaymentMethod: function (publicHash, methodId) {

            var that = this;

            $('body').trigger('processStart');

            $.ajax({
                method: "PUT",
                url: '/rest/V1/subscription/mine/payment/'+that.subscriptionId+'/'+publicHash
            })
                .done(function(response) {
                    $('body').trigger('processStop');
                    that.setCurrentMethod(methodId)
                });
        },

        /**
         *
         * @param methodId
         */
        setCurrentMethod: function (methodId) {
            this.selectedMethod(methodId);
        },

        /**
         *
         * @param methodId
         * @returns {boolean}
         */
        getCurrentMethod: function (methodId) {
            return methodId === this.selectedMethod();
        }
    })
});
