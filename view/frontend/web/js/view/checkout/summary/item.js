/**
 * PayPal Subscriptions Checkout Summary Item
 */
define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'ko',
    'mage/translate'
    ], function (Component, ko, $t) {
    'use strict';

    /**
     * Quote Item Data
     * @type {Window.checkoutConfig.quoteItemData}
     */
    var quoteItemData = window.checkoutConfig.quoteItemData;

    return Component.extend({
        defaults: {
            template: 'PayPal_Subscription/checkout/summary/item'
        },
        displayArea: 'item_message',
        quoteItemData: quoteItemData,
        item: ko.observableArray(),


        /**
         * Return bool if the product is a subscription product or not
         * @returns {*}
         */
        isSubscription: function(quoteItem) {

            // Set Item
            this.item = this.getItem(quoteItem.item_id);
            return this.item['is_subscription'];
        },

        /**
         * Return Selected Frequency Interval in Days
         * @returns {*}
         */
        getFrequencyInterval: function() {
            return this.item['frequency_option_interval'];
        },

        /**
         * Return Selected Frequency Interval in Days
         * @returns {*}
         */
        getFrequencyIntervalInDays: function() {
            return $t('%1 days').replace('%1', this.getFrequencyInterval());
        },

        /**
         * Return Selected Frequency Interval Label
         * @returns {*}
         */
        getFrequencyIntervalLabel: function() {
            return this.item['frequency_option_interval_label'];
        },

        /**
         * Return Final Price
         * @returns {*}
         */
        getItemPrice: function() {
            return this.getFormattedPrice(this.item['row_total']);
        },

        /**
         * @TODO for some reason, on page-load this works fine. Upon continuing to payment screen, it fails due to the strict check.
         * Get Item
         * @param item_id
         * @returns {*}
         */
        getItem: function(item_id) {
            return this.quoteItemData.filter(function(element) {
                return (element.item_id == item_id) ? element : false;
            })[0];
        }
    });
});
