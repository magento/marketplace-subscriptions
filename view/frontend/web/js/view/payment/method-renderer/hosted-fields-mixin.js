define([], function () {
    'use strict';

    /**
     * Quote Item Data
     * @type {Window.checkoutConfig.quoteItemData}
     */
    var quoteItemData = window.checkoutConfig.quoteItemData ?? [];

    return function (originalHostedFields) {
        return originalHostedFields.extend({
            defaults: {
                template: 'PayPal_Subscription/payment/form'
            },

            /**
             * @returns {Bool}
             */
            isVaultEnabled: function () {
                // If cart contains any subscription item, vault must be enabled.
                return this.cartContainsSubscriptions() === true ? true : this.vaultEnabler.isVaultEnabled();
            },

            /**
             * @returns {Bool}
             */
            cartContainsSubscriptions: function () {
                return quoteItemData.some(item => item.is_subscription === "1");
            }
        });
    };
});
