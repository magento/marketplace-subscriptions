/**
 * PayPal Post Address Form.
 */

define([
    'jquery',
    'PayPal_Subscription/js/action/address/add-shipping-address',
    'PayPal_Subscription/js/action/address/set-shipping-address'
], function ($, addShippingAddress, setShippingAddress) {
    'use strict';

    var addAddress = function (form, subscriptionId) {
        var values = [];
        var address = {};

        $.each($(form).serializeArray(), function (i, field) {
            values[field.name] = field.value;
        });

        address = {
            address: {
                company: values.company,
                street: [values.street_1, values.street_2, values.street_3],
                city: values.city,
                postcode: values.postcode,
                country_id: values.country_id,
                firstname: values.firstname,
                lastname: values.lastname,
                telephone: values.telephone,
                region: values.region
            }
        };

        return addShippingAddress(subscriptionId, address);
    };

    var updateAddress = function (subscriptionId, addressId) {
        return setShippingAddress(subscriptionId, addressId);
    };

    return function (form, subscriptionId, addressId = false) {
        return addressId ? updateAddress(subscriptionId, addressId) : addAddress(form, subscriptionId);
    };
});
