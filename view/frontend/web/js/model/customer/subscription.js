define(['underscore', 'ko'], function (_, ko) {
    'use strict';

    /**
     * Returns new subscription object.
     *
     * @param {Object} subscriptionData
     * @return {Object}
     */
    return function (subscriptionData) {
        return {
            subscriptionId: subscriptionData.id,
            customerId: ko.observable(subscriptionData['customer_id']),
            orderId: ko.observable(subscriptionData['original_order_id']),
            status: ko.observable(subscriptionData.status),
            previousReleaseDate: ko.observable(subscriptionData['previous_release_date']),
            nextReleaseDate: ko.observable(subscriptionData['next_release_date']),
            frequencyProfileId: ko.observable(subscriptionData['frequency_profile_id']),
            frequency: ko.observable(subscriptionData.frequency),
            billingAddress: ko.observable(subscriptionData['billing_address']),
            shippingAddress: ko.observable(subscriptionData['shipping_address']),
            shippingAddressHtml: ko.observable(subscriptionData['shipping_address_html']),
            shippingPrice: ko.observable(subscriptionData['shipping_price']),
            shippingMethod: ko.observable(subscriptionData['shipping_method']),
            paymentMethod: ko.observable(subscriptionData['payment_method']),
            paymentData: ko.observable(subscriptionData['payment_data']),
            paymentDetails: {
                "masked_card_number": ko.observable(subscriptionData['payment_details']['masked_card_number']),
                "expiry": ko.observable(subscriptionData['payment_details']['expiry']),
                "card_type": ko.observable(subscriptionData['payment_details']['card_type']),
                "type": ko.observable(subscriptionData['payment_details']['type'])
            },
            createdAt: ko.observable(subscriptionData['created_at']),
            updatedAt: ko.observable(subscriptionData['updated_at']),
            product: subscriptionData.product,
            frequencyLabel: subscriptionData['frequency_label'],
            item: {
                'created_at': subscriptionData.item['created_at'],
                'id': subscriptionData.item.id,
                'price': subscriptionData.item.price,
                'product_id': subscriptionData.item['sproduct_id'],
                'qty': ko.observable(subscriptionData.item.qty),
                'sku': subscriptionData.item.sku,
                'subscription_id': subscriptionData.item['subscription_id'],
                'updated_at': subscriptionData.item['updated_at'],
            },
            availableFrequencies: subscriptionData['available_frequencies'],
            totals: subscriptionData['totals'],

            /**
             * @return {String}
             */
            getKey: function () {
                return `customer-subscription-${this.subscriptionId}`;
            },

            /**
             * @return {String}
             */
            getCacheKey: function () {
                return this.getKey();
            }
        };
    };
});
