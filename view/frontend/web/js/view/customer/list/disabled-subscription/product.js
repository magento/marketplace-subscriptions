define([
    'ko',
    'uiComponent',
    'mageUtils',
    'uiLayout'
], function (ko, Component, utils, layout) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'PayPal_Subscription/customer/list/component/product'
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super();
            this.imageUrl = ko.observable(this.subscription().product.image);
            this.imageAlt = ko.observable(this.subscription().product.name);
            this.frequencyLabel = ko.observable(this.subscription().frequencyLabel);
            return this;
        }
    });
});
