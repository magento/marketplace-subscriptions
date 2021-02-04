/* eslint-disable */
define([
    'mage/storage',
    'Magento_Customer/js/customer-data'
], function (storage, customerData) {
    'use strict';

    return function (item, updatedFrequency) {
        var url,
            urlParams = {},
            quoteItemId = item['item_id'],
            cartData = customerData.get('cart');

        // Set url dependant on if customer is logged in
        var customer = customerData.get('customer');

        if (customer().firstname === undefined || customer().firstname.length === 0) {

            // Customer Not Logged In
            url = 'rest/V1/subscription/carts/frequency/' + quoteItemId +'/' + updatedFrequency;
            urlParams = {
                "cart_id": cartData().guest_masked_id
            };
        } else {

            // Customer Logged In
            url = 'rest/V1/subscription/carts/mine/frequency/' + quoteItemId +'/' + updatedFrequency;
        }

        return storage.put(
            url,
            JSON.stringify(urlParams)
        ).success(
            function (response) {
                customerData.reload(['cart'], false);
                // Return Response
                return response;
            }
        )
    };
});
