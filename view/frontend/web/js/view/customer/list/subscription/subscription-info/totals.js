define([
    'jquery',
    'ko',
    'uiComponent',
    'mage/translate'
], function ($, ko, Component, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'PayPal_Subscription/customer/list/component/totals'
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super();
            return this;
        }
    });
});
