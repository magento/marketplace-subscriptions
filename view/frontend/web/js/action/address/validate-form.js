/**
 * PayPal Subscriptions Validate Form
 */

define([
    'jquery',
    'mage/validation'
], function ($) {
    'use strict';

    return function (form) {
        return $(form).validation() && $(form).validation('isValid');
    };
});
