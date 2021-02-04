/* eslint-disable */
define([
    'ko',
    'underscore'
], function (ko, _) {
    'use strict';

    var billingAddress = ko.observable(false),
        shippingAddress = ko.observable(false),
        existingAddresses = ko.observable(false);

    return {
        billingAddress: billingAddress,
        shippingAddress: shippingAddress,
        existingAddresses: existingAddresses
    }
});
